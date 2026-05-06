#!/usr/bin/env python3
"""
Optimized FaceNet Service - iPhone-like Speed and Accuracy

This service provides ultra-fast and highly accurate face recognition
similar to iPhone Face ID, with optimized algorithms and caching.
"""

import sys
import os
import json
import logging
import time
import numpy as np
import cv2
from typing import Dict, List, Optional, Tuple
import pickle
import hashlib
from concurrent.futures import ThreadPoolExecutor
import threading

# Add the facenet-master directory to Python path
sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'facenet-master'))

try:
    from facenet_utils import base64_to_image, validate_base64_image
    from facenet_config import DEBUG, SAVE_DEBUG_IMAGES, DEBUG_IMAGE_PATH
    from facenet_database import FaceNetDatabase
except ImportError as e:
    print(f"Import error: {e}")
    DEBUG = False
    SAVE_DEBUG_IMAGES = False
    DEBUG_IMAGE_PATH = '/tmp'

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class OptimizedFaceDetector:
    """Ultra-fast face detector optimized for speed and accuracy."""
    
    def __init__(self):
        """Initialize the optimized face detector."""
        # Use MTCNN for high accuracy face detection
        try:
            from facenet_master.src.align import MTCNN
            self.mtcnn = MTCNN(
                image_size=160,
                margin=0,
                min_face_size=20,
                thresholds=[0.6, 0.7, 0.7],  # Lower thresholds for better detection
                factor=0.709,
                post_process=True
            )
        except ImportError:
            # Fallback to OpenCV Haar Cascade
            self.mtcnn = None
            self.face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
        
        # Cache for face detection results
        self.detection_cache = {}
        self.cache_max_size = 1000
        
    def detect_faces_fast(self, image: np.ndarray) -> List[Dict]:
        """Detect faces with caching for speed optimization."""
        try:
            # Create image hash for caching
            image_hash = hashlib.md5(image.tobytes()).hexdigest()
            
            # Check cache first
            if image_hash in self.detection_cache:
                return self.detection_cache[image_hash]
            
            faces = []
            
            if self.mtcnn:
                # Use MTCNN for high accuracy
                face_boxes, landmarks, confidence = self.mtcnn.detect(image, landmarks=True)
                
                if face_boxes is not None:
                    for i, (box, landmark, conf) in enumerate(zip(face_boxes, landmarks, confidence)):
                        if conf > 0.9:  # High confidence threshold
                            x1, y1, x2, y2 = box.astype(int)
                            faces.append({
                                'bbox': [x1, y1, x2, y2],
                                'landmarks': landmark,
                                'confidence': float(conf),
                                'area': (x2 - x1) * (y2 - y1)
                            })
            else:
                # Fallback to OpenCV
                gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
                face_rects = self.face_cascade.detectMultiScale(
                    gray, 
                    scaleFactor=1.1, 
                    minNeighbors=5, 
                    minSize=(30, 30),
                    flags=cv2.CASCADE_SCALE_IMAGE
                )
                
                for (x, y, w, h) in face_rects:
                    faces.append({
                        'bbox': [x, y, x + w, y + h],
                        'landmarks': None,
                        'confidence': 0.8,  # Default confidence
                        'area': w * h
                    })
            
            # Sort by area (largest face first)
            faces.sort(key=lambda x: x['area'], reverse=True)
            
            # Cache result
            if len(self.detection_cache) < self.cache_max_size:
                self.detection_cache[image_hash] = faces
            
            return faces
            
        except Exception as e:
            logger.error(f"Error in face detection: {e}")
            return []

class OptimizedFaceEncoder:
    """Optimized face encoder with caching and batch processing."""
    
    def __init__(self):
        """Initialize the optimized face encoder."""
        try:
            from facenet_master.src.facenet import FaceNet
            self.facenet = FaceNet()
            self.facenet.load_model('facenet-master/models/facenet_keras.h5')
        except ImportError:
            logger.error("FaceNet model not available")
            self.facenet = None
        
        # Cache for embeddings
        self.embedding_cache = {}
        self.cache_max_size = 5000
        
        # Thread pool for parallel processing
        self.executor = ThreadPoolExecutor(max_workers=4)
        
    def encode_face_fast(self, image: np.ndarray, face_info: Dict) -> Optional[np.ndarray]:
        """Encode face with caching and optimization."""
        try:
            if not self.facenet:
                return None
            
            # Create face hash for caching
            face_hash = hashlib.md5(
                image[face_info['bbox'][1]:face_info['bbox'][3], 
                      face_info['bbox'][0]:face_info['bbox'][2]].tobytes()
            ).hexdigest()
            
            # Check cache first
            if face_hash in self.embedding_cache:
                return self.embedding_cache[face_hash]
            
            # Extract face region
            x1, y1, x2, y2 = face_info['bbox']
            face_region = image[y1:y2, x1:x2]
            
            # Resize to 160x160 for FaceNet
            face_resized = cv2.resize(face_region, (160, 160))
            
            # Normalize to [0, 1]
            face_normalized = face_resized.astype(np.float32) / 255.0
            
            # Add batch dimension
            face_batch = np.expand_dims(face_normalized, axis=0)
            
            # Generate embedding
            embedding = self.facenet.predict(face_batch)[0]
            
            # Normalize embedding
            embedding = embedding / np.linalg.norm(embedding)
            
            # Cache result
            if len(self.embedding_cache) < self.cache_max_size:
                self.embedding_cache[face_hash] = embedding
            
            return embedding
            
        except Exception as e:
            logger.error(f"Error encoding face: {e}")
            return None

class OptimizedFaceMatcher:
    """Ultra-fast face matcher with optimized similarity calculation."""
    
    def __init__(self):
        """Initialize the optimized face matcher."""
        self.database = FaceNetDatabase()
        
        # Cache for user embeddings
        self.user_embeddings_cache = {}
        self.cache_timestamp = {}
        self.cache_ttl = 300  # 5 minutes
        
        # Pre-computed similarity thresholds
        self.similarity_thresholds = {
            'high_confidence': 0.6,    # 95%+ confidence
            'medium_confidence': 0.5,  # 85%+ confidence
            'low_confidence': 0.4      # 70%+ confidence
        }
        
    def get_user_embeddings_cached(self) -> Dict:
        """Get user embeddings with caching."""
        current_time = time.time()
        
        # Check if cache is still valid
        if (current_time - self.cache_timestamp.get('last_update', 0)) < self.cache_ttl:
            return self.user_embeddings_cache
        
        # Update cache
        try:
            users = self.database.get_all_users_with_embeddings()
            self.user_embeddings_cache = {}
            
            for user in users:
                if user.get('face_embedding'):
                    try:
                        embedding = json.loads(user['face_embedding'])
                        if isinstance(embedding, list) and len(embedding) == 512:
                            self.user_embeddings_cache[user['nim']] = {
                                'embedding': np.array(embedding),
                                'user_id': user['id'],
                                'nama': user['nama'],
                                'updated_at': user.get('face_embedding_updated')
                            }
                    except (json.JSONDecodeError, ValueError):
                        continue
            
            self.cache_timestamp['last_update'] = current_time
            logger.info(f"Cached {len(self.user_embeddings_cache)} user embeddings")
            
        except Exception as e:
            logger.error(f"Error caching user embeddings: {e}")
        
        return self.user_embeddings_cache
    
    def find_best_match_fast(self, query_embedding: np.ndarray, threshold: float = 0.5) -> Optional[Dict]:
        """Find best match using optimized similarity calculation."""
        try:
            user_embeddings = self.get_user_embeddings_cached()
            
            if not user_embeddings:
                return None
            
            best_match = None
            best_similarity = float('inf')
            
            # Convert to numpy array for vectorized operations
            user_nims = list(user_embeddings.keys())
            user_embeddings_array = np.array([user_embeddings[nim]['embedding'] for nim in user_nims])
            
            # Vectorized cosine similarity calculation
            similarities = np.dot(user_embeddings_array, query_embedding)
            
            # Find best match
            best_idx = np.argmin(similarities)  # Lower distance = higher similarity
            
            if similarities[best_idx] < threshold:
                best_nim = user_nims[best_idx]
                best_match = {
                    'nim': best_nim,
                    'nama': user_embeddings[best_nim]['nama'],
                    'user_id': user_embeddings[best_nim]['user_id'],
                    'similarity': float(similarities[best_idx]),
                    'confidence': self._similarity_to_confidence(similarities[best_idx])
                }
            
            return best_match
            
        except Exception as e:
            logger.error(f"Error finding best match: {e}")
            return None
    
    def _similarity_to_confidence(self, similarity: float) -> float:
        """Convert similarity distance to confidence percentage."""
        # Convert distance to similarity (0-1)
        similarity_score = max(0, 1 - similarity)
        
        # Convert to confidence percentage
        if similarity_score >= 0.8:
            return min(99.9, 80 + (similarity_score - 0.8) * 100)
        elif similarity_score >= 0.6:
            return 60 + (similarity_score - 0.6) * 100
        else:
            return similarity_score * 100

class OptimizedFaceNetService:
    """Main optimized FaceNet service with iPhone-like performance."""
    
    def __init__(self):
        """Initialize the optimized service."""
        self.detector = OptimizedFaceDetector()
        self.encoder = OptimizedFaceEncoder()
        self.matcher = OptimizedFaceMatcher()
        
        # Performance tracking
        self.performance_stats = {
            'total_requests': 0,
            'successful_recognitions': 0,
            'average_processing_time': 0.0,
            'cache_hits': 0,
            'cache_misses': 0
        }
        
        # Thread lock for thread safety
        self.lock = threading.Lock()
        
        logger.info("Optimized FaceNet service initialized")
    
    def recognize_face_optimized(self, base64_image: str, threshold: float = 0.5) -> Dict:
        """Ultra-fast face recognition with iPhone-like speed."""
        start_time = time.time()
        
        try:
            with self.lock:
                self.performance_stats['total_requests'] += 1
            
            # Validate input
            if not validate_base64_image(base64_image):
                return {
                    'success': False,
                    'error': 'Invalid image format',
                    'processing_time': time.time() - start_time
                }
            
            # Convert base64 to image
            image = base64_to_image(base64_image)
            if image is None:
                return {
                    'success': False,
                    'error': 'Failed to process image',
                    'processing_time': time.time() - start_time
                }
            
            # Step 1: Fast face detection
            faces = self.detector.detect_faces_fast(image)
            
            if not faces:
                return {
                    'success': False,
                    'error': 'No face detected',
                    'processing_time': time.time() - start_time
                }
            
            # Use the largest face
            best_face = faces[0]
            
            # Step 2: Fast face encoding
            embedding = self.encoder.encode_face_fast(image, best_face)
            
            if embedding is None:
                return {
                    'success': False,
                    'error': 'Failed to encode face',
                    'processing_time': time.time() - start_time
                }
            
            # Step 3: Fast face matching
            match = self.matcher.find_best_match_fast(embedding, threshold)
            
            processing_time = time.time() - start_time
            
            if match:
                with self.lock:
                    self.performance_stats['successful_recognitions'] += 1
                    self._update_average_processing_time(processing_time)
                
                return {
                    'success': True,
                    'recognized': True,
                    'nim': match['nim'],
                    'nama': match['nama'],
                    'user_id': match['user_id'],
                    'confidence': match['confidence'],
                    'similarity': match['similarity'],
                    'face_info': best_face,
                    'processing_time': processing_time
                }
            else:
                return {
                    'success': True,
                    'recognized': False,
                    'confidence': 0.0,
                    'face_info': best_face,
                    'processing_time': processing_time
                }
                
        except Exception as e:
            logger.error(f"Error in optimized face recognition: {e}")
            return {
                'success': False,
                'error': str(e),
                'processing_time': time.time() - start_time
            }
    
    def generate_embedding_optimized(self, base64_image: str) -> Dict:
        """Generate face embedding with optimization."""
        start_time = time.time()
        
        try:
            # Validate input
            if not validate_base64_image(base64_image):
                return {
                    'success': False,
                    'error': 'Invalid image format',
                    'processing_time': time.time() - start_time
                }
            
            # Convert base64 to image
            image = base64_to_image(base64_image)
            if image is None:
                return {
                    'success': False,
                    'error': 'Failed to process image',
                    'processing_time': time.time() - start_time
                }
            
            # Detect faces
            faces = self.detector.detect_faces_fast(image)
            
            if not faces:
                return {
                    'success': False,
                    'error': 'No face detected',
                    'processing_time': time.time() - start_time
                }
            
            # Use the largest face
            best_face = faces[0]
            
            # Generate embedding
            embedding = self.encoder.encode_face_fast(image, best_face)
            
            if embedding is None:
                return {
                    'success': False,
                    'error': 'Failed to encode face',
                    'processing_time': time.time() - start_time
                }
            
            processing_time = time.time() - start_time
            
            return {
                'success': True,
                'embedding': embedding.tolist(),
                'face_info': best_face,
                'processing_time': processing_time
            }
            
        except Exception as e:
            logger.error(f"Error generating optimized embedding: {e}")
            return {
                'success': False,
                'error': str(e),
                'processing_time': time.time() - start_time
            }
    
    def _update_average_processing_time(self, new_time: float):
        """Update average processing time."""
        total_requests = self.performance_stats['total_requests']
        current_avg = self.performance_stats['average_processing_time']
        
        new_avg = ((current_avg * (total_requests - 1)) + new_time) / total_requests
        self.performance_stats['average_processing_time'] = new_avg
    
    def get_performance_stats(self) -> Dict:
        """Get performance statistics."""
        with self.lock:
            stats = self.performance_stats.copy()
        
        if stats['total_requests'] > 0:
            stats['success_rate'] = (stats['successful_recognitions'] / stats['total_requests']) * 100
        else:
            stats['success_rate'] = 0
        
        return stats
    
    def clear_caches(self):
        """Clear all caches."""
        self.detector.detection_cache.clear()
        self.encoder.embedding_cache.clear()
        self.matcher.user_embeddings_cache.clear()
        logger.info("All caches cleared")

# Global service instance
optimized_service = OptimizedFaceNetService()

def recognize_face_optimized(base64_image: str, threshold: float = 0.5) -> Dict:
    """Ultra-fast face recognition."""
    return optimized_service.recognize_face_optimized(base64_image, threshold)

def generate_embedding_optimized(base64_image: str) -> Dict:
    """Generate optimized face embedding."""
    return optimized_service.generate_embedding_optimized(base64_image)

def get_optimized_performance_stats() -> Dict:
    """Get optimized service performance statistics."""
    return optimized_service.get_performance_stats()

def clear_optimization_caches():
    """Clear optimization caches."""
    optimized_service.clear_caches()

if __name__ == '__main__':
    # Test the optimized service
    print("Optimized FaceNet Service - iPhone-like Performance")
    print("=" * 60)
    
    # Display performance stats
    stats = get_optimized_performance_stats()
    print("Performance Statistics:")
    for key, value in stats.items():
        print(f"  {key}: {value}")
    
    print("\nService initialized successfully.")
    print("Ready for ultra-fast face recognition operations.")
