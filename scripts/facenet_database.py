#!/usr/bin/env python3
"""
FaceNet Database Manager

This module handles database operations for FaceNet face embeddings.
"""

import os
import sys
import json
import numpy as np
from typing import List, Dict, Optional, Tuple
import mysql.connector
from mysql.connector import Error
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class FaceNetDatabase:
    """Database manager for FaceNet face embeddings."""
    
    def __init__(self, host: str = 'localhost', database: str = 'absen_db', 
                 user: str = 'root', password: str = ''):
        """Initialize database connection."""
        self.host = host
        self.database = database
        self.user = user
        self.password = password
        self.connection = None
        self.connect()
    
    def connect(self) -> bool:
        """Connect to the database."""
        try:
            self.connection = mysql.connector.connect(
                host=self.host,
                database=self.database,
                user=self.user,
                password=self.password
            )
            if self.connection.is_connected():
                logger.info("Connected to MySQL database")
                return True
        except Error as e:
            logger.error(f"Error connecting to MySQL: {e}")
            return False
        return False
    
    def disconnect(self) -> None:
        """Disconnect from the database."""
        if self.connection and self.connection.is_connected():
            self.connection.close()
            logger.info("MySQL connection closed")
    
    def is_connected(self) -> bool:
        """Check if database is connected."""
        try:
            return self.connection and self.connection.is_connected()
        except:
            return False
    
    def reconnect(self) -> bool:
        """Reconnect to the database."""
        self.disconnect()
        return self.connect()
    
    def get_user_embedding(self, user_id: int) -> Optional[np.ndarray]:
        """Get face embedding for a user."""
        try:
            if not self.is_connected():
                if not self.reconnect():
                    return None
            
            cursor = self.connection.cursor()
            query = "SELECT face_embedding FROM users WHERE id = %s AND face_embedding IS NOT NULL"
            cursor.execute(query, (user_id,))
            result = cursor.fetchone()
            cursor.close()
            
            if result and result[0]:
                embedding_data = json.loads(result[0])
                return np.array(embedding_data)
            
            return None
        except Error as e:
            logger.error(f"Error getting user embedding: {e}")
            return None
    
    def save_user_embedding(self, user_id: int, embedding: np.ndarray) -> bool:
        """Save face embedding for a user."""
        try:
            if not self.is_connected():
                if not self.reconnect():
                    return False
            
            cursor = self.connection.cursor()
            query = """
                UPDATE users 
                SET face_embedding = %s, face_embedding_updated = NOW() 
                WHERE id = %s
            """
            embedding_json = json.dumps(embedding.tolist())
            cursor.execute(query, (embedding_json, user_id))
            self.connection.commit()
            cursor.close()
            
            logger.info(f"Saved face embedding for user {user_id}")
            return True
        except Error as e:
            logger.error(f"Error saving user embedding: {e}")
            return False
    
    def get_all_embeddings(self) -> Dict[int, np.ndarray]:
        """Get all face embeddings from the database."""
        try:
            if not self.is_connected():
                if not self.reconnect():
                    return {}
            
            cursor = self.connection.cursor()
            query = """
                SELECT id, nim, nama, face_embedding 
                FROM users 
                WHERE role = 'pegawai' AND face_embedding IS NOT NULL
            """
            cursor.execute(query)
            results = cursor.fetchall()
            cursor.close()
            
            embeddings = {}
            for row in results:
                user_id, nim, nama, embedding_data = row
                if embedding_data:
                    try:
                        embedding = np.array(json.loads(embedding_data))
                        embeddings[user_id] = {
                            'embedding': embedding,
                            'nim': nim,
                            'nama': nama
                        }
                    except (json.JSONDecodeError, ValueError) as e:
                        logger.warning(f"Invalid embedding data for user {user_id}: {e}")
                        continue
            
            logger.info(f"Loaded {len(embeddings)} face embeddings")
            return embeddings
        except Error as e:
            logger.error(f"Error getting all embeddings: {e}")
            return {}
    
    def get_embeddings_by_nim(self) -> Dict[str, np.ndarray]:
        """Get face embeddings indexed by NIM."""
        try:
            if not self.is_connected():
                if not self.reconnect():
                    return {}
            
            cursor = self.connection.cursor()
            query = """
                SELECT nim, face_embedding 
                FROM users 
                WHERE role = 'pegawai' AND face_embedding IS NOT NULL AND nim IS NOT NULL
            """
            cursor.execute(query)
            results = cursor.fetchall()
            cursor.close()
            
            embeddings = {}
            for row in results:
                nim, embedding_data = row
                if embedding_data and nim:
                    try:
                        embedding = np.array(json.loads(embedding_data))
                        embeddings[nim] = embedding
                    except (json.JSONDecodeError, ValueError) as e:
                        logger.warning(f"Invalid embedding data for NIM {nim}: {e}")
                        continue
            
            logger.info(f"Loaded {len(embeddings)} face embeddings by NIM")
            return embeddings
        except Error as e:
            logger.error(f"Error getting embeddings by NIM: {e}")
            return {}
    
    def delete_user_embedding(self, user_id: int) -> bool:
        """Delete face embedding for a user."""
        try:
            if not self.is_connected():
                if not self.reconnect():
                    return False
            
            cursor = self.connection.cursor()
            query = "UPDATE users SET face_embedding = NULL, face_embedding_updated = NULL WHERE id = %s"
            cursor.execute(query, (user_id,))
            self.connection.commit()
            cursor.close()
            
            logger.info(f"Deleted face embedding for user {user_id}")
            return True
        except Error as e:
            logger.error(f"Error deleting user embedding: {e}")
            return False
    
    def get_user_info(self, user_id: int) -> Optional[Dict]:
        """Get user information."""
        try:
            if not self.is_connected():
                if not self.reconnect():
                    return None
            
            cursor = self.connection.cursor()
            query = "SELECT id, nim, nama, email, role FROM users WHERE id = %s"
            cursor.execute(query, (user_id,))
            result = cursor.fetchone()
            cursor.close()
            
            if result:
                return {
                    'id': result[0],
                    'nim': result[1],
                    'nama': result[2],
                    'email': result[3],
                    'role': result[4]
                }
            
            return None
        except Error as e:
            logger.error(f"Error getting user info: {e}")
            return None
    
    def get_user_by_nim(self, nim: str) -> Optional[Dict]:
        """Get user information by NIM."""
        try:
            if not self.is_connected():
                if not self.reconnect():
                    return None
            
            cursor = self.connection.cursor()
            query = "SELECT id, nim, nama, email, role FROM users WHERE nim = %s"
            cursor.execute(query, (nim,))
            result = cursor.fetchone()
            cursor.close()
            
            if result:
                return {
                    'id': result[0],
                    'nim': result[1],
                    'nama': result[2],
                    'email': result[3],
                    'role': result[4]
                }
            
            return None
        except Error as e:
            logger.error(f"Error getting user by NIM: {e}")
            return None
    
    def update_embedding_timestamp(self, user_id: int) -> bool:
        """Update the timestamp when embedding was last updated."""
        try:
            if not self.is_connected():
                if not self.reconnect():
                    return False
            
            cursor = self.connection.cursor()
            query = "UPDATE users SET face_embedding_updated = NOW() WHERE id = %s"
            cursor.execute(query, (user_id,))
            self.connection.commit()
            cursor.close()
            
            return True
        except Error as e:
            logger.error(f"Error updating embedding timestamp: {e}")
            return False
    
    def get_embedding_stats(self) -> Dict:
        """Get statistics about face embeddings."""
        try:
            if not self.is_connected():
                if not self.reconnect():
                    return {}
            
            cursor = self.connection.cursor()
            
            # Total users
            cursor.execute("SELECT COUNT(*) FROM users WHERE role = 'pegawai'")
            total_users = cursor.fetchone()[0]
            
            # Users with embeddings
            cursor.execute("SELECT COUNT(*) FROM users WHERE role = 'pegawai' AND face_embedding IS NOT NULL")
            users_with_embeddings = cursor.fetchone()[0]
            
            # Users without embeddings
            users_without_embeddings = total_users - users_with_embeddings
            
            # Recent updates (last 7 days)
            cursor.execute("""
                SELECT COUNT(*) FROM users 
                WHERE role = 'pegawai' 
                AND face_embedding_updated >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            """)
            recent_updates = cursor.fetchone()[0]
            
            cursor.close()
            
            return {
                'total_users': total_users,
                'users_with_embeddings': users_with_embeddings,
                'users_without_embeddings': users_without_embeddings,
                'recent_updates': recent_updates,
                'coverage_percentage': (users_with_embeddings / total_users * 100) if total_users > 0 else 0
            }
        except Error as e:
            logger.error(f"Error getting embedding stats: {e}")
            return {}
    
    def save_user_advanced_features(self, user_id: int, advanced_features: dict) -> bool:
        """Save user advanced facial features to database."""
        try:
            if not self.is_connected():
                if not self.reconnect():
                    return False
            
            # Convert features to JSON string
            features_json = json.dumps(advanced_features)
            geometry_json = json.dumps(advanced_features.get('geometry', {}))
            vector_json = json.dumps(advanced_features.get('feature_vector', []))
            
            # Update user record
            cursor = self.connection.cursor()
            query = """
                UPDATE users 
                SET advanced_features = %s, facial_geometry = %s, feature_vector = %s, face_embedding_updated = NOW() 
                WHERE id = %s
            """
            
            cursor.execute(query, (features_json, geometry_json, vector_json, user_id))
            self.connection.commit()
            cursor.close()
            
            return True
            
        except Error as e:
            logger.error(f"Error saving user advanced features: {e}")
            return False
    
    def get_user_advanced_features(self, nim: str) -> dict:
        """Get user advanced features by NIM."""
        try:
            if not self.is_connected():
                if not self.reconnect():
                    return {}
            
            cursor = self.connection.cursor()
            query = """
                SELECT advanced_features, facial_geometry, feature_vector 
                FROM users 
                WHERE nim = %s AND advanced_features IS NOT NULL
            """
            
            cursor.execute(query, (nim,))
            result = cursor.fetchone()
            cursor.close()
            
            if result:
                advanced_features = json.loads(result[0]) if result[0] else {}
                facial_geometry = json.loads(result[1]) if result[1] else {}
                feature_vector = json.loads(result[2]) if result[2] else []
                
                return {
                    'advanced_features': advanced_features,
                    'geometry': facial_geometry,
                    'feature_vector': feature_vector
                }
            
            return {}
            
        except Error as e:
            logger.error(f"Error getting user advanced features: {e}")
            return {}
    
    def cleanup_old_embeddings(self, days: int = 30) -> int:
        """Clean up old embeddings that haven't been updated."""
        try:
            if not self.is_connected():
                if not self.reconnect():
                    return 0
            
            cursor = self.connection.cursor()
            query = """
                UPDATE users 
                SET face_embedding = NULL, face_embedding_updated = NULL 
                WHERE role = 'pegawai' 
                AND face_embedding_updated < DATE_SUB(NOW(), INTERVAL %s DAY)
            """
            cursor.execute(query, (days,))
            affected_rows = cursor.rowcount
            self.connection.commit()
            cursor.close()
            
            logger.info(f"Cleaned up {affected_rows} old embeddings")
            return affected_rows
        except Error as e:
            logger.error(f"Error cleaning up old embeddings: {e}")
            return 0
    
    def backup_embeddings(self, backup_file: str) -> bool:
        """Backup all face embeddings to a file."""
        try:
            embeddings = self.get_all_embeddings()
            
            backup_data = {}
            for user_id, data in embeddings.items():
                backup_data[user_id] = {
                    'nim': data['nim'],
                    'nama': data['nama'],
                    'embedding': data['embedding'].tolist()
                }
            
            with open(backup_file, 'w') as f:
                json.dump(backup_data, f, indent=2)
            
            logger.info(f"Backed up {len(embeddings)} embeddings to {backup_file}")
            return True
        except Exception as e:
            logger.error(f"Error backing up embeddings: {e}")
            return False
    
    def restore_embeddings(self, backup_file: str) -> bool:
        """Restore face embeddings from a backup file."""
        try:
            with open(backup_file, 'r') as f:
                backup_data = json.load(f)
            
            restored_count = 0
            for user_id, data in backup_data.items():
                embedding = np.array(data['embedding'])
                if self.save_user_embedding(int(user_id), embedding):
                    restored_count += 1
            
            logger.info(f"Restored {restored_count} embeddings from {backup_file}")
            return True
        except Exception as e:
            logger.error(f"Error restoring embeddings: {e}")
            return False

# Global database instance
db = FaceNetDatabase()

if __name__ == '__main__':
    # Test database operations
    print("Testing FaceNet database operations...")
    
    # Test connection
    if db.is_connected():
        print("✓ Database connection successful")
        
        # Test getting stats
        stats = db.get_embedding_stats()
        print(f"Embedding stats: {stats}")
        
        # Test getting all embeddings
        embeddings = db.get_all_embeddings()
        print(f"Loaded {len(embeddings)} embeddings")
        
    else:
        print("✗ Database connection failed")
    
    # Disconnect
    db.disconnect()
    print("Database test completed!")
