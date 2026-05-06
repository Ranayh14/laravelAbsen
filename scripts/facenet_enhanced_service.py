#!/usr/bin/env python3
"""
FaceNet Enhanced Service

This service combines the original FaceNet embedding with advanced facial feature analysis
for improved recognition accuracy. It analyzes facial geometry, landmarks, and detailed
features like face width, forehead width, face shape, nose shape, and other characteristics.
"""

import sys
import os
import json
import time
import logging
import traceback
import numpy as np
from typing import Dict, List, Optional

# Add the facenet-master directory to Python path
sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'facenet-master'))

try:
    from facenet_service import FaceNetService
    from facenet_advanced_features import analyze_facial_features, compare_facial_features
    from facenet_utils import base64_to_image, validate_base64_image, normalize_embedding
    from facenet_config import (
        DEFAULT_THRESHOLD, NORMALIZE_EMBEDDINGS, RECOGNITION_METHOD,
        DEBUG, SAVE_DEBUG_IMAGES, DEBUG_IMAGE_PATH
    )
    import facenet_database as db
except ImportError as e:
    print(f"Import error: {e}", file=sys.stderr)
    sys.exit(1)

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class EnhancedFaceNetService:
    """Enhanced FaceNet service with advanced facial feature analysis."""
    
    def __init__(self):
        """Initialize the enhanced FaceNet service."""
        try:
            # Initialize base FaceNet service
            self.base_service = FaceNetService()
            
            # Feature weights for combining different recognition methods
            self.feature_weights = {
                'facenet_embedding': 0.5,      # Original FaceNet embedding
                'facial_geometry': 0.3,        # Geometric features
                'facial_features': 0.2         # Individual feature analysis
            }
            
            # Load known faces with enhanced features
            self.known_faces = self.load_enhanced_known_faces()
            
            logger.info("Enhanced FaceNet service initialized successfully.")
            
        except Exception as e:
            logger.error(f"Failed to initialize Enhanced FaceNet service: {e}")
            raise
    
    def load_enhanced_known_faces(self) -> Dict:
        """Load known faces with enhanced features from database."""
        try:
            # Load embeddings from database
            embeddings = db.get_embeddings_by_nim()
            
            # Convert to enhanced format
            enhanced_faces = {}
            for nim, embedding in embeddings.items():
                if NORMALIZE_EMBEDDINGS:
                    embedding = normalize_embedding(embedding)
                
                # Load additional features if available
                additional_features = db.get_user_advanced_features(nim)
                
                enhanced_faces[nim] = {
                    'embedding': embedding,
                    'advanced_features': additional_features,
                    'last_updated': time.time()
                }
            
            logger.info(f"Loaded {len(enhanced_faces)} enhanced known faces from database")
            return enhanced_faces
            
        except Exception as e:
            logger.error(f"Error loading enhanced known faces: {e}")
            return {}
    
    def generate_enhanced_embedding(self, base64_image: str) -> Dict:
        """Generate enhanced embedding with advanced features."""
        try:
            # Validate base64 image
            if not validate_base64_image(base64_image):
                logger.error("Invalid image format")
                return None
            
            # Convert base64 to image
            image = base64_to_image(base64_image)
            if image is None:
                logger.error("Failed to process image")
                return None
            
            # Save debug image if enabled
            if DEBUG and SAVE_DEBUG_IMAGES:
                from facenet_utils import save_debug_image
                save_debug_image(image, f"enhanced_embedding_{int(time.time())}.jpg", DEBUG_IMAGE_PATH)
            
            # Generate base FaceNet embedding
            base_embedding = self.base_service.generate_embedding(base64_image)
            if base_embedding is None:
                logger.error("Failed to generate base embedding")
                return None
            
            # Analyze advanced facial features
            advanced_features = analyze_facial_features(base64_image)
            if not advanced_features:
                logger.warning("Failed to extract advanced features")
                advanced_features = {}
            
            # Create enhanced embedding
            enhanced_embedding = {
                'base_embedding': base_embedding,
                'advanced_features': advanced_features,
                'timestamp': time.time(),
                'feature_count': len(advanced_features.get('feature_vector', []))
            }
            
            logger.info(f"Generated enhanced embedding with {enhanced_embedding['feature_count']} features")
            return enhanced_embedding
            
        except Exception as e:
            logger.error(f"Error generating enhanced embedding: {e}")
            if DEBUG:
                logger.error(traceback.format_exc())
            return None
    
    def recognize_enhanced_face(self, base64_image: str, threshold: float = None) -> Dict:
        """Recognize a face using enhanced features."""
        try:
            if threshold is None:
                threshold = DEFAULT_THRESHOLD
            
            # Validate base64 image
            if not validate_base64_image(base64_image):
                return {
                    'recognized': False,
                    'message': 'Invalid image format'
                }
            
            # Convert base64 to image
            image = base64_to_image(base64_image)
            if image is None:
                return {
                    'recognized': False,
                    'message': 'Failed to process image'
                }
            
            # Save debug image if enabled
            if DEBUG and SAVE_DEBUG_IMAGES:
                from facenet_utils import save_debug_image
                save_debug_image(image, f"enhanced_recognition_{int(time.time())}.jpg", DEBUG_IMAGE_PATH)
            
            # Generate enhanced embedding for input image
            input_embedding = self.generate_enhanced_embedding(base64_image)
            if input_embedding is None:
                return {
                    'recognized': False,
                    'message': 'Failed to generate enhanced embedding'
                }
            
            # Compare with known faces
            best_match = None
            best_score = 0.0
            best_nim = None
            comparison_details = []
            
            for nim, known_face in self.known_faces.items():
                # Calculate combined similarity score
                similarity_score = self.calculate_enhanced_similarity(
                    input_embedding, known_face, comparison_details
                )
                
                if similarity_score > best_score:
                    best_score = similarity_score
                    best_match = known_face
                    best_nim = nim
            
            # Check if best score meets threshold
            if best_match and best_score >= threshold:
                # Get user information from database
                user_info = db.get_user_by_nim(best_nim)
                
                if user_info:
                    return {
                        'recognized': True,
                        'user_id': user_info['id'],
                        'nim': best_nim,
                        'nama': user_info['nama'],
                        'email': user_info['email'],
                        'similarity_score': float(best_score),
                        'confidence': float(best_score),
                        'threshold': threshold,
                        'comparison_details': comparison_details[-1] if comparison_details else {},
                        'feature_analysis': input_embedding.get('advanced_features', {})
                    }
                else:
                    return {
                        'recognized': False,
                        'message': 'User information not found',
                        'similarity_score': float(best_score)
                    }
            else:
                return {
                    'recognized': False,
                    'message': 'Face not recognized',
                    'similarity_score': float(best_score) if best_match else 0.0,
                    'threshold': threshold,
                    'feature_analysis': input_embedding.get('advanced_features', {})
                }
                
        except Exception as e:
            logger.error(f"Error recognizing enhanced face: {e}")
            if DEBUG:
                logger.error(traceback.format_exc())
            return {
                'recognized': False,
                'message': f'Recognition error: {str(e)}'
            }
    
    def calculate_enhanced_similarity(self, input_embedding: Dict, known_face: Dict, 
                                    comparison_details: List) -> float:
        """Calculate enhanced similarity score combining multiple features."""
        try:
            # Extract components
            input_base = input_embedding.get('base_embedding', [])
            input_advanced = input_embedding.get('advanced_features', {})
            known_base = known_face.get('embedding', [])
            known_advanced = known_face.get('advanced_features', {})
            
            # Calculate FaceNet embedding similarity
            facenet_similarity = 0.0
            if input_base and known_base:
                from facenet_utils import calculate_embedding_distance
                distance = calculate_embedding_distance(
                    np.array(input_base), 
                    np.array(known_base), 
                    RECOGNITION_METHOD
                )
                facenet_similarity = max(0.0, 1.0 - distance)
            
            # Calculate advanced features similarity
            advanced_similarity = 0.0
            if input_advanced and known_advanced:
                comparison_result = compare_facial_features(input_advanced, known_advanced)
                advanced_similarity = comparison_result.get('similarity', 0.0)
            
            # Calculate geometric similarity
            geometric_similarity = 0.0
            if input_advanced and known_advanced:
                geometric_similarity = self.calculate_geometric_similarity(
                    input_advanced, known_advanced
                )
            
            # Combine similarities with weights
            combined_score = (
                facenet_similarity * self.feature_weights['facenet_embedding'] +
                advanced_similarity * self.feature_weights['facial_features'] +
                geometric_similarity * self.feature_weights['facial_geometry']
            )
            
            # Store comparison details
            comparison_details.append({
                'facenet_similarity': float(facenet_similarity),
                'advanced_similarity': float(advanced_similarity),
                'geometric_similarity': float(geometric_similarity),
                'combined_score': float(combined_score),
                'weights': self.feature_weights
            })
            
            return combined_score
            
        except Exception as e:
            logger.error(f"Error calculating enhanced similarity: {e}")
            return 0.0
    
    def calculate_geometric_similarity(self, features1: Dict, features2: Dict) -> float:
        """Calculate geometric similarity between two feature sets."""
        try:
            geometry1 = features1.get('geometry', {})
            geometry2 = features2.get('geometry', {})
            
            if not geometry1 or not geometry2:
                return 0.0
            
            # Compare key geometric features
            geometric_features = [
                'face_ratio', 'forehead_ratio', 'symmetry_score', 'face_angle'
            ]
            
            similarities = []
            for feature in geometric_features:
                val1 = geometry1.get(feature, 0)
                val2 = geometry2.get(feature, 0)
                
                if val1 == 0 and val2 == 0:
                    similarities.append(1.0)
                elif val1 == 0 or val2 == 0:
                    similarities.append(0.0)
                else:
                    # Calculate similarity as inverse of relative difference
                    diff = abs(val1 - val2) / max(abs(val1), abs(val2))
                    similarity = max(0.0, 1.0 - diff)
                    similarities.append(similarity)
            
            # Compare categorical features
            categorical_features = ['face_shape', 'eye_shape', 'nose_shape', 'mouth_shape', 'jaw_shape']
            for feature in categorical_features:
                val1 = geometry1.get(feature, 'unknown')
                val2 = geometry2.get(feature, 'unknown')
                similarities.append(1.0 if val1 == val2 else 0.0)
            
            return float(np.mean(similarities))
            
        except Exception as e:
            logger.error(f"Error calculating geometric similarity: {e}")
            return 0.0
    
    def save_enhanced_embedding_to_database(self, user_id: int, enhanced_embedding: Dict) -> bool:
        """Save enhanced embedding to database."""
        try:
            # Save base embedding
            base_embedding = enhanced_embedding.get('base_embedding', [])
            if base_embedding:
                success = db.save_user_embedding(user_id, np.array(base_embedding))
                if not success:
                    logger.error(f"Failed to save base embedding for user {user_id}")
                    return False
            
            # Save advanced features
            advanced_features = enhanced_embedding.get('advanced_features', {})
            if advanced_features:
                success = db.save_user_advanced_features(user_id, advanced_features)
                if not success:
                    logger.error(f"Failed to save advanced features for user {user_id}")
                    return False
            
            # Reload known faces
            self.known_faces = self.load_enhanced_known_faces()
            
            logger.info(f"Saved enhanced embedding for user {user_id}")
            return True
            
        except Exception as e:
            logger.error(f"Error saving enhanced embedding to database: {e}")
            return False
    
    def process_enhanced_attendance(self, base64_image: str, threshold: float = None) -> Dict:
        """Process attendance using enhanced face recognition."""
        try:
            if threshold is None:
                threshold = DEFAULT_THRESHOLD
            
            # Recognize face with enhanced features
            recognition_result = self.recognize_enhanced_face(base64_image, threshold)
            
            if recognition_result is None:
                return {
                    'success': False,
                    'error': 'Enhanced face recognition failed'
                }
            
            if not recognition_result['recognized']:
                return {
                    'success': False,
                    'error': recognition_result['message'],
                    'data': recognition_result
                }
            
            # Log successful recognition
            logger.info(f"Enhanced face recognized: {recognition_result['nama']} (NIM: {recognition_result['nim']})")
            
            # Return successful recognition result
            return {
                'success': True,
                'data': recognition_result
            }
            
        except Exception as e:
            logger.error(f"Error processing enhanced attendance: {e}")
            if DEBUG:
                logger.error(traceback.format_exc())
            return {
                'success': False,
                'error': f'Unexpected error: {str(e)}'
            }
    
    def get_enhanced_embedding_stats(self) -> Dict:
        """Get statistics about enhanced embeddings."""
        try:
            stats = {
                'total_faces': len(self.known_faces),
                'faces_with_advanced_features': 0,
                'average_feature_count': 0,
                'feature_distribution': {}
            }
            
            total_features = 0
            for nim, face_data in self.known_faces.items():
                advanced_features = face_data.get('advanced_features', {})
                if advanced_features:
                    stats['faces_with_advanced_features'] += 1
                    feature_count = len(advanced_features.get('feature_vector', []))
                    total_features += feature_count
            
            if stats['faces_with_advanced_features'] > 0:
                stats['average_feature_count'] = total_features / stats['faces_with_advanced_features']
            
            return stats
            
        except Exception as e:
            logger.error(f"Error getting enhanced embedding stats: {e}")
            return {}

# Global instance
enhanced_service = EnhancedFaceNetService()

def main():
    """Main function for testing."""
    print("FaceNet Enhanced Service")
    print("=" * 50)
    
    # Test the enhanced service
    stats = enhanced_service.get_enhanced_embedding_stats()
    print(f"Enhanced service stats: {json.dumps(stats, indent=2)}")

if __name__ == '__main__':
    main()
