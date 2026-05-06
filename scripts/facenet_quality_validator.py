#!/usr/bin/env python3
"""
FaceNet Quality Validator

This module provides advanced quality validation for face recognition
to ensure only high-confidence, high-quality recognitions are accepted
for attendance recording.
"""

import cv2
import numpy as np
import math
from typing import Dict, List, Tuple, Optional
import json
import logging
import time

# Add the facenet-master directory to Python path
import sys
import os
sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'facenet-master'))

try:
    from facenet_utils import base64_to_image, validate_base64_image
    from facenet_config import DEBUG, SAVE_DEBUG_IMAGES, DEBUG_IMAGE_PATH
except ImportError:
    # Fallback if modules not available
    DEBUG = False
    SAVE_DEBUG_IMAGES = False
    DEBUG_IMAGE_PATH = '/tmp'

logger = logging.getLogger(__name__)

class FaceQualityAnalyzer:
    """Analyzes face image quality for recognition validation."""
    
    def __init__(self):
        """Initialize the face quality analyzer."""
        self.face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
        self.eye_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_eye.xml')
        
        # Quality thresholds
        self.quality_thresholds = {
            'min_face_size': 100,           # Minimum face size in pixels
            'max_face_size': 500,           # Maximum face size in pixels
            'min_blur_threshold': 100,      # Laplacian variance threshold
            'min_brightness': 50,           # Minimum brightness
            'max_brightness': 200,          # Maximum brightness
            'min_contrast': 30,             # Minimum contrast
            'max_angle_deviation': 15,      # Maximum face angle deviation in degrees
            'min_eye_visibility': 0.8,      # Minimum eye visibility ratio
            'min_face_visibility': 0.7,     # Minimum face visibility ratio
            'max_occlusion_ratio': 0.3      # Maximum occlusion ratio
        }
    
    def analyze_face_quality(self, image: np.ndarray) -> Dict:
        """Comprehensive face quality analysis."""
        try:
            quality_metrics = {}
            
            # Convert to grayscale for analysis
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
            
            # Detect face
            faces = self.face_cascade.detectMultiScale(gray, 1.1, 4)
            
            if len(faces) == 0:
                return {
                    'quality_score': 0.0,
                    'is_valid': False,
                    'error': 'No face detected',
                    'metrics': {}
                }
            
            # Use the largest face
            face = max(faces, key=lambda x: x[2] * x[3])
            x, y, w, h = face
            
            # Extract face region
            face_roi = gray[y:y+h, x:x+w]
            
            # Calculate quality metrics
            quality_metrics.update(self._analyze_blur(face_roi))
            quality_metrics.update(self._analyze_lighting(face_roi))
            quality_metrics.update(self._analyze_contrast(face_roi))
            quality_metrics.update(self._analyze_face_size(w, h))
            quality_metrics.update(self._analyze_face_angle(face_roi))
            quality_metrics.update(self._analyze_eye_visibility(gray, face))
            quality_metrics.update(self._analyze_occlusion(gray, face))
            quality_metrics.update(self._analyze_face_position(image, face))
            
            # Calculate overall quality score
            quality_score = self._calculate_quality_score(quality_metrics)
            
            # Determine if face is valid for recognition
            is_valid = self._validate_face_quality(quality_metrics, quality_score)
            
            return {
                'quality_score': quality_score,
                'is_valid': is_valid,
                'metrics': quality_metrics,
                'face_region': face.tolist()
            }
            
        except Exception as e:
            logger.error(f"Error analyzing face quality: {e}")
            return {
                'quality_score': 0.0,
                'is_valid': False,
                'error': str(e),
                'metrics': {}
            }
    
    def _analyze_blur(self, face_roi: np.ndarray) -> Dict:
        """Analyze image blur using Laplacian variance."""
        try:
            laplacian_var = cv2.Laplacian(face_roi, cv2.CV_64F).var()
            
            # Normalize blur score (higher is better)
            blur_score = min(1.0, laplacian_var / self.quality_thresholds['min_blur_threshold'])
            
            return {
                'blur_score': float(blur_score),
                'laplacian_variance': float(laplacian_var),
                'is_blur_valid': laplacian_var >= self.quality_thresholds['min_blur_threshold']
            }
        except Exception as e:
            logger.error(f"Error analyzing blur: {e}")
            return {
                'blur_score': 0.0,
                'laplacian_variance': 0.0,
                'is_blur_valid': False
            }
    
    def _analyze_lighting(self, face_roi: np.ndarray) -> Dict:
        """Analyze lighting conditions."""
        try:
            mean_brightness = np.mean(face_roi)
            std_brightness = np.std(face_roi)
            
            # Calculate lighting score
            brightness_score = 1.0
            if mean_brightness < self.quality_thresholds['min_brightness']:
                brightness_score = mean_brightness / self.quality_thresholds['min_brightness']
            elif mean_brightness > self.quality_thresholds['max_brightness']:
                brightness_score = self.quality_thresholds['max_brightness'] / mean_brightness
            
            # Check for even lighting
            lighting_evenness = 1.0 - (std_brightness / 128.0)
            lighting_evenness = max(0.0, min(1.0, lighting_evenness))
            
            is_lighting_valid = (
                self.quality_thresholds['min_brightness'] <= mean_brightness <= self.quality_thresholds['max_brightness']
            )
            
            return {
                'brightness_score': float(brightness_score),
                'lighting_evenness': float(lighting_evenness),
                'mean_brightness': float(mean_brightness),
                'std_brightness': float(std_brightness),
                'is_lighting_valid': is_lighting_valid
            }
        except Exception as e:
            logger.error(f"Error analyzing lighting: {e}")
            return {
                'brightness_score': 0.0,
                'lighting_evenness': 0.0,
                'mean_brightness': 0.0,
                'std_brightness': 0.0,
                'is_lighting_valid': False
            }
    
    def _analyze_contrast(self, face_roi: np.ndarray) -> Dict:
        """Analyze image contrast."""
        try:
            # Calculate contrast using standard deviation
            contrast = np.std(face_roi)
            
            # Normalize contrast score
            contrast_score = min(1.0, contrast / self.quality_thresholds['min_contrast'])
            
            is_contrast_valid = contrast >= self.quality_thresholds['min_contrast']
            
            return {
                'contrast_score': float(contrast_score),
                'contrast_value': float(contrast),
                'is_contrast_valid': is_contrast_valid
            }
        except Exception as e:
            logger.error(f"Error analyzing contrast: {e}")
            return {
                'contrast_score': 0.0,
                'contrast_value': 0.0,
                'is_contrast_valid': False
            }
    
    def _analyze_face_size(self, width: int, height: int) -> Dict:
        """Analyze face size."""
        try:
            face_area = width * height
            
            # Calculate size score
            if face_area < self.quality_thresholds['min_face_size'] ** 2:
                size_score = face_area / (self.quality_thresholds['min_face_size'] ** 2)
            elif face_area > self.quality_thresholds['max_face_size'] ** 2:
                size_score = (self.quality_thresholds['max_face_size'] ** 2) / face_area
            else:
                size_score = 1.0
            
            is_size_valid = (
                self.quality_thresholds['min_face_size'] <= min(width, height) <= self.quality_thresholds['max_face_size']
            )
            
            return {
                'size_score': float(size_score),
                'face_area': int(face_area),
                'face_width': int(width),
                'face_height': int(height),
                'is_size_valid': is_size_valid
            }
        except Exception as e:
            logger.error(f"Error analyzing face size: {e}")
            return {
                'size_score': 0.0,
                'face_area': 0,
                'face_width': 0,
                'face_height': 0,
                'is_size_valid': False
            }
    
    def _analyze_face_angle(self, face_roi: np.ndarray) -> Dict:
        """Analyze face angle/rotation."""
        try:
            # Use Hough lines to detect face orientation
            edges = cv2.Canny(face_roi, 50, 150)
            lines = cv2.HoughLines(edges, 1, np.pi/180, threshold=50)
            
            if lines is not None and len(lines) > 0:
                # Calculate average angle
                angles = []
                for line in lines:
                    rho, theta = line[0]
                    angle = math.degrees(theta)
                    if angle > 90:
                        angle -= 180
                    angles.append(angle)
                
                avg_angle = np.mean(angles)
                angle_deviation = abs(avg_angle)
                
                # Calculate angle score
                angle_score = max(0.0, 1.0 - (angle_deviation / self.quality_thresholds['max_angle_deviation']))
                
                is_angle_valid = angle_deviation <= self.quality_thresholds['max_angle_deviation']
            else:
                avg_angle = 0.0
                angle_deviation = 0.0
                angle_score = 1.0
                is_angle_valid = True
            
            return {
                'angle_score': float(angle_score),
                'face_angle': float(avg_angle),
                'angle_deviation': float(angle_deviation),
                'is_angle_valid': is_angle_valid
            }
        except Exception as e:
            logger.error(f"Error analyzing face angle: {e}")
            return {
                'angle_score': 1.0,
                'face_angle': 0.0,
                'angle_deviation': 0.0,
                'is_angle_valid': True
            }
    
    def _analyze_eye_visibility(self, gray: np.ndarray, face: Tuple) -> Dict:
        """Analyze eye visibility."""
        try:
            x, y, w, h = face
            face_roi = gray[y:y+h, x:x+w]
            
            # Detect eyes in face region
            eyes = self.eye_cascade.detectMultiScale(face_roi, 1.1, 3)
            
            # Calculate eye visibility ratio
            eye_visibility = len(eyes) / 2.0  # Expect 2 eyes
            eye_visibility = min(1.0, eye_visibility)
            
            is_eye_valid = eye_visibility >= self.quality_thresholds['min_eye_visibility']
            
            return {
                'eye_visibility': float(eye_visibility),
                'eyes_detected': len(eyes),
                'is_eye_valid': is_eye_valid
            }
        except Exception as e:
            logger.error(f"Error analyzing eye visibility: {e}")
            return {
                'eye_visibility': 0.0,
                'eyes_detected': 0,
                'is_eye_valid': False
            }
    
    def _analyze_occlusion(self, gray: np.ndarray, face: Tuple) -> Dict:
        """Analyze face occlusion."""
        try:
            x, y, w, h = face
            face_roi = gray[y:y+h, x:x+w]
            
            # Use edge detection to find potential occlusions
            edges = cv2.Canny(face_roi, 50, 150)
            
            # Calculate edge density in different regions
            h_third = h // 3
            w_third = w // 3
            
            # Check center region (should have fewer edges for clear face)
            center_region = edges[h_third:2*h_third, w_third:2*w_third]
            center_edge_density = np.sum(center_region > 0) / center_region.size
            
            # Check outer regions (should have more edges for face boundaries)
            outer_regions = [
                edges[0:h_third, :],  # Top
                edges[2*h_third:h, :],  # Bottom
                edges[:, 0:w_third],  # Left
                edges[:, 2*w_third:w]   # Right
            ]
            
            outer_edge_density = np.mean([np.sum(region > 0) / region.size for region in outer_regions])
            
            # Calculate occlusion ratio
            occlusion_ratio = center_edge_density / (outer_edge_density + 1e-6)
            occlusion_ratio = min(1.0, occlusion_ratio)
            
            is_occlusion_valid = occlusion_ratio <= self.quality_thresholds['max_occlusion_ratio']
            
            return {
                'occlusion_ratio': float(occlusion_ratio),
                'center_edge_density': float(center_edge_density),
                'outer_edge_density': float(outer_edge_density),
                'is_occlusion_valid': is_occlusion_valid
            }
        except Exception as e:
            logger.error(f"Error analyzing occlusion: {e}")
            return {
                'occlusion_ratio': 0.0,
                'center_edge_density': 0.0,
                'outer_edge_density': 0.0,
                'is_occlusion_valid': True
            }
    
    def _analyze_face_position(self, image: np.ndarray, face: Tuple) -> Dict:
        """Analyze face position in image."""
        try:
            x, y, w, h = face
            img_h, img_w = image.shape[:2]
            
            # Calculate face position ratios
            center_x = x + w // 2
            center_y = y + h // 2
            
            x_ratio = center_x / img_w
            y_ratio = center_y / img_h
            
            # Ideal position is center of image
            ideal_x_ratio = 0.5
            ideal_y_ratio = 0.5
            
            # Calculate position score
            x_deviation = abs(x_ratio - ideal_x_ratio)
            y_deviation = abs(y_ratio - ideal_y_ratio)
            
            position_score = 1.0 - (x_deviation + y_deviation) / 2.0
            position_score = max(0.0, position_score)
            
            # Check if face is reasonably centered
            is_position_valid = (x_deviation < 0.3 and y_deviation < 0.3)
            
            return {
                'position_score': float(position_score),
                'x_ratio': float(x_ratio),
                'y_ratio': float(y_ratio),
                'x_deviation': float(x_deviation),
                'y_deviation': float(y_deviation),
                'is_position_valid': is_position_valid
            }
        except Exception as e:
            logger.error(f"Error analyzing face position: {e}")
            return {
                'position_score': 0.0,
                'x_ratio': 0.0,
                'y_ratio': 0.0,
                'x_deviation': 1.0,
                'y_deviation': 1.0,
                'is_position_valid': False
            }
    
    def _calculate_quality_score(self, metrics: Dict) -> float:
        """Calculate overall quality score from metrics."""
        try:
            # Weighted average of quality metrics
            weights = {
                'blur_score': 0.20,
                'brightness_score': 0.15,
                'lighting_evenness': 0.10,
                'contrast_score': 0.15,
                'size_score': 0.15,
                'angle_score': 0.10,
                'eye_visibility': 0.10,
                'position_score': 0.05
            }
            
            total_score = 0.0
            total_weight = 0.0
            
            for metric, weight in weights.items():
                if metric in metrics:
                    total_score += metrics[metric] * weight
                    total_weight += weight
            
            if total_weight > 0:
                return total_score / total_weight
            else:
                return 0.0
                
        except Exception as e:
            logger.error(f"Error calculating quality score: {e}")
            return 0.0
    
    def _validate_face_quality(self, metrics: Dict, quality_score: float) -> bool:
        """Validate if face meets quality requirements."""
        try:
            # Check individual quality requirements
            quality_checks = [
                metrics.get('is_blur_valid', False),
                metrics.get('is_lighting_valid', False),
                metrics.get('is_contrast_valid', False),
                metrics.get('is_size_valid', False),
                metrics.get('is_angle_valid', False),
                metrics.get('is_eye_valid', False),
                metrics.get('is_occlusion_valid', False),
                metrics.get('is_position_valid', False)
            ]
            
            # At least 75% of quality checks must pass
            passed_checks = sum(quality_checks)
            total_checks = len(quality_checks)
            check_ratio = passed_checks / total_checks if total_checks > 0 else 0
            
            # Overall quality score must be above 0.7
            quality_valid = quality_score >= 0.7
            
            # Check ratio must be above 0.75
            check_valid = check_ratio >= 0.75
            
            return quality_valid and check_valid
            
        except Exception as e:
            logger.error(f"Error validating face quality: {e}")
            return False

class MultiVerificationSystem:
    """Multi-verification system for high-accuracy face recognition."""
    
    def __init__(self):
        """Initialize the multi-verification system."""
        self.quality_analyzer = FaceQualityAnalyzer()
        self.verification_history = []
        self.max_history_size = 10
        
        # Verification thresholds
        self.verification_thresholds = {
            'min_confidence': 0.90,          # 90% minimum confidence
            'min_quality_score': 0.80,       # 80% minimum quality score
            'min_consistency_ratio': 0.80,   # 80% consistency across attempts
            'max_attempts': 3,               # Maximum verification attempts
            'cooldown_period': 30            # Cooldown period in seconds
        }
    
    def verify_face_recognition(self, recognition_result: Dict, image: np.ndarray) -> Dict:
        """Comprehensive face recognition verification."""
        try:
            verification_result = {
                'is_verified': False,
                'confidence': 0.0,
                'quality_score': 0.0,
                'verification_reason': '',
                'recommendations': []
            }
            
            # Step 1: Check basic recognition result
            if not recognition_result.get('recognized', False):
                verification_result['verification_reason'] = 'Face not recognized'
                verification_result['recommendations'].append('Ensure face is clearly visible')
                return verification_result
            
            # Step 2: Check confidence threshold
            confidence = recognition_result.get('confidence', 0.0)
            if confidence < self.verification_thresholds['min_confidence']:
                verification_result['verification_reason'] = f'Confidence too low: {confidence:.1%} < {self.verification_thresholds["min_confidence"]:.1%}'
                verification_result['recommendations'].append('Improve lighting and face positioning')
                verification_result['confidence'] = confidence
                return verification_result
            
            # Step 3: Analyze face quality
            quality_analysis = self.quality_analyzer.analyze_face_quality(image)
            quality_score = quality_analysis.get('quality_score', 0.0)
            
            if quality_score < self.verification_thresholds['min_quality_score']:
                verification_result['verification_reason'] = f'Face quality too low: {quality_score:.1%} < {self.verification_thresholds["min_quality_score"]:.1%}'
                verification_result['recommendations'].extend(self._get_quality_recommendations(quality_analysis))
                verification_result['quality_score'] = quality_score
                return verification_result
            
            # Step 4: Check consistency with previous attempts
            consistency_check = self._check_consistency(recognition_result)
            if not consistency_check['is_consistent']:
                verification_result['verification_reason'] = consistency_check['reason']
                verification_result['recommendations'].append('Try again with better positioning')
                return verification_result
            
            # Step 5: Check cooldown period
            if self._is_in_cooldown(recognition_result.get('nim')):
                verification_result['verification_reason'] = 'Too many recent attempts'
                verification_result['recommendations'].append('Wait before trying again')
                return verification_result
            
            # All checks passed
            verification_result.update({
                'is_verified': True,
                'confidence': confidence,
                'quality_score': quality_score,
                'verification_reason': 'All verification checks passed',
                'quality_analysis': quality_analysis,
                'consistency_check': consistency_check
            })
            
            # Add to verification history
            self._add_to_history(recognition_result, quality_analysis)
            
            return verification_result
            
        except Exception as e:
            logger.error(f"Error in face verification: {e}")
            return {
                'is_verified': False,
                'confidence': 0.0,
                'quality_score': 0.0,
                'verification_reason': f'Verification error: {str(e)}',
                'recommendations': ['Try again']
            }
    
    def _check_consistency(self, recognition_result: Dict) -> Dict:
        """Check consistency with previous recognition attempts."""
        try:
            nim = recognition_result.get('nim')
            if not nim:
                return {'is_consistent': True, 'reason': 'No NIM to check'}
            
            # Get recent attempts for this user
            recent_attempts = [
                entry for entry in self.verification_history
                if entry.get('nim') == nim and 
                (time.time() - entry.get('timestamp', 0)) < 300  # Last 5 minutes
            ]
            
            if len(recent_attempts) < 2:
                return {'is_consistent': True, 'reason': 'Insufficient history'}
            
            # Check if recent attempts are consistent
            consistent_attempts = 0
            for attempt in recent_attempts:
                if attempt.get('recognized', False) and attempt.get('nim') == nim:
                    consistent_attempts += 1
            
            consistency_ratio = consistent_attempts / len(recent_attempts)
            
            if consistency_ratio < self.verification_thresholds['min_consistency_ratio']:
                return {
                    'is_consistent': False,
                    'reason': f'Inconsistent recognition: {consistency_ratio:.1%} < {self.verification_thresholds["min_consistency_ratio"]:.1%}'
                }
            
            return {
                'is_consistent': True,
                'reason': f'Consistent recognition: {consistency_ratio:.1%}',
                'consistency_ratio': consistency_ratio
            }
            
        except Exception as e:
            logger.error(f"Error checking consistency: {e}")
            return {'is_consistent': True, 'reason': 'Consistency check failed'}
    
    def _is_in_cooldown(self, nim: str) -> bool:
        """Check if user is in cooldown period."""
        try:
            if not nim:
                return False
            
            # Get recent attempts for this user
            recent_attempts = [
                entry for entry in self.verification_history
                if entry.get('nim') == nim and 
                (time.time() - entry.get('timestamp', 0)) < self.verification_thresholds['cooldown_period']
            ]
            
            return len(recent_attempts) >= self.verification_thresholds['max_attempts']
            
        except Exception as e:
            logger.error(f"Error checking cooldown: {e}")
            return False
    
    def _add_to_history(self, recognition_result: Dict, quality_analysis: Dict):
        """Add verification attempt to history."""
        try:
            history_entry = {
                'timestamp': time.time(),
                'nim': recognition_result.get('nim'),
                'recognized': recognition_result.get('recognized', False),
                'confidence': recognition_result.get('confidence', 0.0),
                'quality_score': quality_analysis.get('quality_score', 0.0),
                'is_verified': True
            }
            
            self.verification_history.append(history_entry)
            
            # Keep only recent history
            if len(self.verification_history) > self.max_history_size:
                self.verification_history = self.verification_history[-self.max_history_size:]
                
        except Exception as e:
            logger.error(f"Error adding to history: {e}")
    
    def _get_quality_recommendations(self, quality_analysis: Dict) -> List[str]:
        """Get recommendations based on quality analysis."""
        recommendations = []
        metrics = quality_analysis.get('metrics', {})
        
        if not metrics.get('is_blur_valid', True):
            recommendations.append('Reduce camera shake or improve focus')
        
        if not metrics.get('is_lighting_valid', True):
            recommendations.append('Improve lighting conditions')
        
        if not metrics.get('is_contrast_valid', True):
            recommendations.append('Adjust lighting for better contrast')
        
        if not metrics.get('is_size_valid', True):
            recommendations.append('Move closer or further from camera')
        
        if not metrics.get('is_angle_valid', True):
            recommendations.append('Face the camera directly')
        
        if not metrics.get('is_eye_valid', True):
            recommendations.append('Ensure eyes are clearly visible')
        
        if not metrics.get('is_occlusion_valid', True):
            recommendations.append('Remove any obstructions from face')
        
        if not metrics.get('is_position_valid', True):
            recommendations.append('Center your face in the frame')
        
        return recommendations
    
    def get_verification_stats(self) -> Dict:
        """Get verification statistics."""
        try:
            total_attempts = len(self.verification_history)
            verified_attempts = sum(1 for entry in self.verification_history if entry.get('is_verified', False))
            verification_rate = verified_attempts / total_attempts if total_attempts > 0 else 0
            
            avg_confidence = np.mean([entry.get('confidence', 0) for entry in self.verification_history]) if self.verification_history else 0
            avg_quality = np.mean([entry.get('quality_score', 0) for entry in self.verification_history]) if self.verification_history else 0
            
            return {
                'total_attempts': total_attempts,
                'verified_attempts': verified_attempts,
                'verification_rate': verification_rate,
                'average_confidence': avg_confidence,
                'average_quality': avg_quality,
                'thresholds': self.verification_thresholds
            }
        except Exception as e:
            logger.error(f"Error getting verification stats: {e}")
            return {}

# Global instances
quality_analyzer = FaceQualityAnalyzer()
multi_verifier = MultiVerificationSystem()

def validate_face_quality(base64_image: str) -> Dict:
    """Validate face quality from base64 image."""
    try:
        if not validate_base64_image(base64_image):
            return {'is_valid': False, 'error': 'Invalid image format'}
        
        image = base64_to_image(base64_image)
        if image is None:
            return {'is_valid': False, 'error': 'Failed to process image'}
        
        quality_analysis = quality_analyzer.analyze_face_quality(image)
        
        if DEBUG and SAVE_DEBUG_IMAGES:
            from facenet_utils import save_debug_image
            save_debug_image(image, f"quality_analysis_{int(time.time())}.jpg", DEBUG_IMAGE_PATH)
        
        return quality_analysis
        
    except Exception as e:
        logger.error(f"Error validating face quality: {e}")
        return {'is_valid': False, 'error': str(e)}

def verify_face_recognition(recognition_result: Dict, base64_image: str) -> Dict:
    """Verify face recognition result with quality checks."""
    try:
        if not validate_base64_image(base64_image):
            return {'is_verified': False, 'error': 'Invalid image format'}
        
        image = base64_to_image(base64_image)
        if image is None:
            return {'is_verified': False, 'error': 'Failed to process image'}
        
        verification_result = multi_verifier.verify_face_recognition(recognition_result, image)
        
        return verification_result
        
    except Exception as e:
        logger.error(f"Error verifying face recognition: {e}")
        return {'is_verified': False, 'error': str(e)}

if __name__ == '__main__':
    # Test the quality validator
    print("FaceNet Quality Validator")
    print("=" * 50)
    
    # This would be used in integration with the main FaceNet service
    print("Quality validation module loaded successfully.")
