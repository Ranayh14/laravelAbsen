#!/usr/bin/env python3
"""
FaceNet High Accuracy Service

This service provides high-accuracy face recognition with strict quality validation
and confidence thresholds to ensure only reliable recognitions are accepted.
"""

import sys
import os
import json
import logging
import time
from typing import Dict, List, Optional, Tuple
import numpy as np

# Add the facenet-master directory to Python path
sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'facenet-master'))

try:
    from facenet_utils import base64_to_image, validate_base64_image
    from facenet_config import DEBUG, SAVE_DEBUG_IMAGES, DEBUG_IMAGE_PATH
    from facenet_quality_validator import validate_face_quality, verify_face_recognition, multi_verifier
    from facenet_enhanced_service import EnhancedFaceNetService
    from facenet_database import FaceNetDatabase
except ImportError as e:
    print(f"Import error: {e}")
    DEBUG = False
    SAVE_DEBUG_IMAGES = False
    DEBUG_IMAGE_PATH = '/tmp'

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class HighAccuracyFaceNetService:
    """High-accuracy FaceNet service with strict validation."""
    
    def __init__(self):
        """Initialize the high-accuracy service."""
        self.enhanced_service = EnhancedFaceNetService()
        self.database = FaceNetDatabase()
        
        # High-accuracy thresholds
        self.thresholds = {
            'min_confidence': 0.90,          # 90% minimum confidence
            'min_quality_score': 0.80,       # 80% minimum quality score
            'min_consistency_ratio': 0.85,   # 85% consistency across attempts
            'max_attempts_per_minute': 3,    # Maximum attempts per minute
            'cooldown_period': 60,           # Cooldown period in seconds
            'verification_timeout': 30       # Verification timeout in seconds
        }
        
        # Performance tracking
        self.performance_stats = {
            'total_attempts': 0,
            'successful_recognitions': 0,
            'quality_rejections': 0,
            'confidence_rejections': 0,
            'consistency_rejections': 0,
            'cooldown_rejections': 0,
            'average_processing_time': 0.0
        }
        
        logger.info("High-accuracy FaceNet service initialized")
    
    def process_high_accuracy_attendance(self, base64_image: str, user_id: Optional[int] = None) -> Dict:
        """Process attendance with high-accuracy validation."""
        start_time = time.time()
        
        try:
            self.performance_stats['total_attempts'] += 1
            
            # Step 1: Validate input
            if not validate_base64_image(base64_image):
                return self._create_error_response('Invalid image format')
            
            # Step 2: Check rate limiting
            rate_limit_check = self._check_rate_limiting(user_id)
            if not rate_limit_check['allowed']:
                self.performance_stats['cooldown_rejections'] += 1
                return self._create_error_response(
                    f"Rate limit exceeded: {rate_limit_check['reason']}",
                    error_code='RATE_LIMIT'
                )
            
            # Step 3: Perform face recognition
            recognition_result = self.enhanced_service.recognize_face(base64_image, threshold=0.7)
            
            if not recognition_result.get('recognized', False):
                return self._create_error_response(
                    'Face not recognized',
                    error_code='FACE_NOT_RECOGNIZED'
                )
            
            # Step 4: Verify recognition with quality checks
            verification_result = verify_face_recognition(recognition_result, base64_image)
            
            if not verification_result.get('is_verified', False):
                # Track rejection reason
                if verification_result.get('confidence', 0) < self.thresholds['min_confidence']:
                    self.performance_stats['confidence_rejections'] += 1
                elif verification_result.get('quality_score', 0) < self.thresholds['min_quality_score']:
                    self.performance_stats['quality_rejections'] += 1
                else:
                    self.performance_stats['consistency_rejections'] += 1
                
                return self._create_error_response(
                    verification_result.get('verification_reason', 'Verification failed'),
                    error_code='VERIFICATION_FAILED',
                    details=verification_result
                )
            
            # Step 5: Additional security checks
            security_check = self._perform_security_checks(recognition_result, verification_result)
            if not security_check['passed']:
                return self._create_error_response(
                    security_check['reason'],
                    error_code='SECURITY_CHECK_FAILED'
                )
            
            # Step 6: Record attendance
            attendance_result = self._record_attendance(recognition_result, verification_result)
            
            if attendance_result['success']:
                self.performance_stats['successful_recognitions'] += 1
                
                # Update performance stats
                processing_time = time.time() - start_time
                self._update_performance_stats(processing_time)
                
                return {
                    'success': True,
                    'message': 'Attendance recorded successfully',
                    'data': {
                        'nim': recognition_result.get('nim'),
                        'nama': recognition_result.get('nama'),
                        'confidence': verification_result.get('confidence', 0),
                        'quality_score': verification_result.get('quality_score', 0),
                        'verification_reason': verification_result.get('verification_reason', ''),
                        'timestamp': time.time(),
                        'processing_time': processing_time
                    }
                }
            else:
                return self._create_error_response(
                    attendance_result.get('error', 'Failed to record attendance'),
                    error_code='ATTENDANCE_RECORDING_FAILED'
                )
                
        except Exception as e:
            logger.error(f"Error in high-accuracy attendance processing: {e}")
            return self._create_error_response(f'Processing error: {str(e)}')
    
    def generate_high_accuracy_embedding(self, base64_image: str, user_id: int) -> Dict:
        """Generate face embedding with quality validation."""
        try:
            # Step 1: Validate input
            if not validate_base64_image(base64_image):
                return self._create_error_response('Invalid image format')
            
            # Step 2: Quality validation
            quality_analysis = validate_face_quality(base64_image)
            
            if not quality_analysis.get('is_valid', False):
                return self._create_error_response(
                    f'Face quality insufficient: {quality_analysis.get("error", "Unknown error")}',
                    error_code='QUALITY_INSUFFICIENT',
                    details=quality_analysis
                )
            
            quality_score = quality_analysis.get('quality_score', 0)
            if quality_score < self.thresholds['min_quality_score']:
                return self._create_error_response(
                    f'Quality score too low: {quality_score:.1%} < {self.thresholds["min_quality_score"]:.1%}',
                    error_code='QUALITY_TOO_LOW',
                    details=quality_analysis
                )
            
            # Step 3: Generate enhanced embedding
            embedding_result = self.enhanced_service.generate_enhanced_embedding(base64_image)
            
            if not embedding_result.get('success', False):
                return self._create_error_response(
                    embedding_result.get('error', 'Failed to generate embedding'),
                    error_code='EMBEDDING_GENERATION_FAILED'
                )
            
            # Step 4: Save to database
            save_result = self.database.save_user_enhanced_features(
                user_id, 
                embedding_result['data']
            )
            
            if not save_result:
                return self._create_error_response(
                    'Failed to save embedding to database',
                    error_code='DATABASE_SAVE_FAILED'
                )
            
            return {
                'success': True,
                'message': 'High-quality face embedding generated and saved',
                'data': {
                    'user_id': user_id,
                    'quality_score': quality_score,
                    'quality_metrics': quality_analysis.get('metrics', {}),
                    'embedding_generated': True,
                    'timestamp': time.time()
                }
            }
            
        except Exception as e:
            logger.error(f"Error generating high-accuracy embedding: {e}")
            return self._create_error_response(f'Embedding generation error: {str(e)}')
    
    def _check_rate_limiting(self, user_id: Optional[int]) -> Dict:
        """Check if user is within rate limits."""
        try:
            if not user_id:
                return {'allowed': True, 'reason': 'No user ID provided'}
            
            # Get recent attempts for this user
            recent_attempts = multi_verifier.verification_history
            user_attempts = [
                entry for entry in recent_attempts
                if entry.get('user_id') == user_id and 
                (time.time() - entry.get('timestamp', 0)) < 60  # Last minute
            ]
            
            if len(user_attempts) >= self.thresholds['max_attempts_per_minute']:
                return {
                    'allowed': False,
                    'reason': f'Too many attempts: {len(user_attempts)}/{self.thresholds["max_attempts_per_minute"]}'
                }
            
            return {'allowed': True, 'reason': 'Within rate limits'}
            
        except Exception as e:
            logger.error(f"Error checking rate limiting: {e}")
            return {'allowed': True, 'reason': 'Rate limit check failed'}
    
    def _perform_security_checks(self, recognition_result: Dict, verification_result: Dict) -> Dict:
        """Perform additional security checks."""
        try:
            # Check 1: Confidence consistency
            recognition_confidence = recognition_result.get('confidence', 0)
            verification_confidence = verification_result.get('confidence', 0)
            
            confidence_diff = abs(recognition_confidence - verification_confidence)
            if confidence_diff > 0.1:  # 10% difference threshold
                return {
                    'passed': False,
                    'reason': f'Confidence inconsistency: {confidence_diff:.1%} difference'
                }
            
            # Check 2: Quality score consistency
            quality_score = verification_result.get('quality_score', 0)
            if quality_score < self.thresholds['min_quality_score']:
                return {
                    'passed': False,
                    'reason': f'Quality score too low: {quality_score:.1%}'
                }
            
            # Check 3: Face size validation
            quality_analysis = verification_result.get('quality_analysis', {})
            metrics = quality_analysis.get('metrics', {})
            
            if not metrics.get('is_size_valid', True):
                return {
                    'passed': False,
                    'reason': 'Face size not suitable for recognition'
                }
            
            # Check 4: Multiple face detection
            if quality_analysis.get('face_count', 1) > 1:
                return {
                    'passed': False,
                    'reason': 'Multiple faces detected in image'
                }
            
            return {'passed': True, 'reason': 'All security checks passed'}
            
        except Exception as e:
            logger.error(f"Error in security checks: {e}")
            return {'passed': False, 'reason': f'Security check error: {str(e)}'}
    
    def _record_attendance(self, recognition_result: Dict, verification_result: Dict) -> Dict:
        """Record attendance with high-accuracy validation."""
        try:
            nim = recognition_result.get('nim')
            if not nim:
                return {'success': False, 'error': 'No NIM found in recognition result'}
            
            # Get user information
            user_info = self.database.get_user_by_nim(nim)
            if not user_info:
                return {'success': False, 'error': 'User not found in database'}
            
            # Check if attendance already recorded today
            existing_attendance = self.database.get_today_attendance(user_info['id'])
            if existing_attendance:
                return {
                    'success': False, 
                    'error': 'Attendance already recorded today',
                    'existing_attendance': existing_attendance
                }
            
            # Record attendance
            attendance_data = {
                'user_id': user_info['id'],
                'nim': nim,
                'nama': user_info['nama'],
                'confidence': verification_result.get('confidence', 0),
                'quality_score': verification_result.get('quality_score', 0),
                'verification_reason': verification_result.get('verification_reason', ''),
                'timestamp': time.time()
            }
            
            success = self.database.record_attendance(attendance_data)
            
            if success:
                return {'success': True, 'data': attendance_data}
            else:
                return {'success': False, 'error': 'Failed to save attendance to database'}
                
        except Exception as e:
            logger.error(f"Error recording attendance: {e}")
            return {'success': False, 'error': f'Attendance recording error: {str(e)}'}
    
    def _update_performance_stats(self, processing_time: float):
        """Update performance statistics."""
        try:
            # Update average processing time
            total_attempts = self.performance_stats['total_attempts']
            current_avg = self.performance_stats['average_processing_time']
            
            new_avg = ((current_avg * (total_attempts - 1)) + processing_time) / total_attempts
            self.performance_stats['average_processing_time'] = new_avg
            
        except Exception as e:
            logger.error(f"Error updating performance stats: {e}")
    
    def _create_error_response(self, message: str, error_code: str = 'UNKNOWN_ERROR', details: Optional[Dict] = None) -> Dict:
        """Create standardized error response."""
        response = {
            'success': False,
            'error': message,
            'error_code': error_code,
            'timestamp': time.time()
        }
        
        if details:
            response['details'] = details
        
        return response
    
    def get_performance_stats(self) -> Dict:
        """Get performance statistics."""
        try:
            total_attempts = self.performance_stats['total_attempts']
            successful_recognitions = self.performance_stats['successful_recognitions']
            
            success_rate = successful_recognitions / total_attempts if total_attempts > 0 else 0
            
            return {
                'performance_stats': self.performance_stats.copy(),
                'success_rate': success_rate,
                'thresholds': self.thresholds.copy(),
                'verification_stats': multi_verifier.get_verification_stats()
            }
            
        except Exception as e:
            logger.error(f"Error getting performance stats: {e}")
            return {'error': str(e)}
    
    def update_thresholds(self, new_thresholds: Dict) -> Dict:
        """Update recognition thresholds."""
        try:
            # Validate new thresholds
            for key, value in new_thresholds.items():
                if key in self.thresholds:
                    if isinstance(value, (int, float)) and 0 <= value <= 1:
                        self.thresholds[key] = value
                    else:
                        return {'success': False, 'error': f'Invalid threshold value for {key}'}
            
            return {
                'success': True,
                'message': 'Thresholds updated successfully',
                'new_thresholds': self.thresholds.copy()
            }
            
        except Exception as e:
            logger.error(f"Error updating thresholds: {e}")
            return {'success': False, 'error': str(e)}

# Global service instance
high_accuracy_service = HighAccuracyFaceNetService()

def process_high_accuracy_attendance(base64_image: str, user_id: Optional[int] = None) -> Dict:
    """Process attendance with high-accuracy validation."""
    return high_accuracy_service.process_high_accuracy_attendance(base64_image, user_id)

def generate_high_accuracy_embedding(base64_image: str, user_id: int) -> Dict:
    """Generate face embedding with quality validation."""
    return high_accuracy_service.generate_high_accuracy_embedding(base64_image, user_id)

def get_high_accuracy_performance_stats() -> Dict:
    """Get high-accuracy service performance statistics."""
    return high_accuracy_service.get_performance_stats()

def update_high_accuracy_thresholds(new_thresholds: Dict) -> Dict:
    """Update high-accuracy recognition thresholds."""
    return high_accuracy_service.update_thresholds(new_thresholds)

if __name__ == '__main__':
    # Test the high-accuracy service
    print("FaceNet High Accuracy Service")
    print("=" * 50)
    
    # Display current thresholds
    print("Current Thresholds:")
    for key, value in high_accuracy_service.thresholds.items():
        print(f"  {key}: {value}")
    
    print("\nService initialized successfully.")
    print("Ready for high-accuracy face recognition operations.")
