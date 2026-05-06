#!/usr/bin/env python3
"""
FaceNet Advanced Features Analysis

This module provides advanced facial feature analysis for improved face recognition accuracy.
It analyzes facial geometry, landmarks, and detailed features like face width, forehead width,
face shape, nose shape, and other distinguishing characteristics.
"""

import cv2
import numpy as np
import math
from typing import Dict, List, Tuple, Optional
import json
import logging

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

class FacialGeometryAnalyzer:
    """Analyzes facial geometry and proportions."""
    
    def __init__(self):
        """Initialize the facial geometry analyzer."""
        self.face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
        self.eye_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_eye.xml')
        self.nose_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_mcs_nose.xml')
        self.mouth_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_mcs_mouth.xml')
        
        # Initialize face landmark predictor (dlib)
        try:
            import dlib
            self.predictor = dlib.shape_predictor('shape_predictor_68_face_landmarks.dat')
            self.detector = dlib.get_frontal_face_detector()
            self.dlib_available = True
        except ImportError:
            logger.warning("dlib not available, using OpenCV for landmark detection")
            self.dlib_available = False
    
    def detect_face_landmarks(self, image: np.ndarray) -> Optional[np.ndarray]:
        """Detect 68 facial landmarks using dlib or OpenCV."""
        try:
            if self.dlib_available:
                # Use dlib for accurate landmark detection
                gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
                faces = self.detector(gray)
                
                if len(faces) > 0:
                    face = faces[0]  # Use first detected face
                    landmarks = self.predictor(gray, face)
                    return np.array([[p.x, p.y] for p in landmarks.parts()])
            else:
                # Fallback to OpenCV-based landmark detection
                return self._detect_landmarks_opencv(image)
            
            return None
        except Exception as e:
            logger.error(f"Error detecting landmarks: {e}")
            return None
    
    def _detect_landmarks_opencv(self, image: np.ndarray) -> Optional[np.ndarray]:
        """Fallback landmark detection using OpenCV."""
        try:
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
            faces = self.face_cascade.detectMultiScale(gray, 1.1, 4)
            
            if len(faces) > 0:
                x, y, w, h = faces[0]
                face_roi = gray[y:y+h, x:x+w]
                
                # Detect eyes, nose, mouth
                eyes = self.eye_cascade.detectMultiScale(face_roi)
                noses = self.nose_cascade.detectMultiScale(face_roi)
                mouths = self.mouth_cascade.detectMultiScale(face_roi)
                
                # Create simplified landmarks
                landmarks = []
                
                # Face outline (simplified)
                landmarks.extend([
                    [x, y], [x + w//2, y], [x + w, y],  # Top
                    [x + w, y + h//3], [x + w, y + 2*h//3], [x + w, y + h],  # Right
                    [x + w//2, y + h], [x, y + h],  # Bottom
                    [x, y + 2*h//3], [x, y + h//3]  # Left
                ])
                
                # Eyes
                if len(eyes) >= 2:
                    for eye in eyes[:2]:
                        ex, ey, ew, eh = eye
                        landmarks.extend([
                            [x + ex, y + ey],  # Left eye corner
                            [x + ex + ew//2, y + ey + eh//2],  # Eye center
                            [x + ex + ew, y + ey]  # Right eye corner
                        ])
                
                # Nose
                if len(noses) > 0:
                    nx, ny, nw, nh = noses[0]
                    landmarks.extend([
                        [x + nx, y + ny],  # Nose left
                        [x + nx + nw//2, y + ny],  # Nose top
                        [x + nx + nw, y + ny],  # Nose right
                        [x + nx + nw//2, y + ny + nh]  # Nose bottom
                    ])
                
                # Mouth
                if len(mouths) > 0:
                    mx, my, mw, mh = mouths[0]
                    landmarks.extend([
                        [x + mx, y + my],  # Mouth left
                        [x + mx + mw//2, y + my],  # Mouth top
                        [x + mx + mw, y + my],  # Mouth right
                        [x + mx + mw//2, y + my + mh]  # Mouth bottom
                    ])
                
                return np.array(landmarks)
            
            return None
        except Exception as e:
            logger.error(f"Error in OpenCV landmark detection: {e}")
            return None
    
    def analyze_face_geometry(self, image: np.ndarray) -> Dict:
        """Analyze comprehensive facial geometry."""
        try:
            landmarks = self.detect_face_landmarks(image)
            if landmarks is None:
                return {}
            
            # Calculate face measurements
            face_width = self._calculate_face_width(landmarks)
            face_height = self._calculate_face_height(landmarks)
            forehead_width = self._calculate_forehead_width(landmarks)
            
            # Calculate ratios and proportions
            face_ratio = face_width / face_height if face_height > 0 else 0
            forehead_ratio = forehead_width / face_width if face_width > 0 else 0
            
            # Analyze face shape
            face_shape = self._analyze_face_shape(landmarks)
            
            # Analyze individual features
            eye_analysis = self._analyze_eyes(landmarks)
            nose_analysis = self._analyze_nose(landmarks)
            mouth_analysis = self._analyze_mouth(landmarks)
            jaw_analysis = self._analyze_jaw(landmarks)
            
            # Calculate symmetry
            symmetry_score = self._calculate_symmetry(landmarks)
            
            # Calculate angles
            face_angle = self._calculate_face_angle(landmarks)
            
            geometry_data = {
                'face_width': float(face_width),
                'face_height': float(face_height),
                'forehead_width': float(forehead_width),
                'face_ratio': float(face_ratio),
                'forehead_ratio': float(forehead_ratio),
                'face_shape': face_shape,
                'eye_analysis': eye_analysis,
                'nose_analysis': nose_analysis,
                'mouth_analysis': mouth_analysis,
                'jaw_analysis': jaw_analysis,
                'symmetry_score': float(symmetry_score),
                'face_angle': float(face_angle),
                'landmarks_count': len(landmarks)
            }
            
            return geometry_data
            
        except Exception as e:
            logger.error(f"Error analyzing face geometry: {e}")
            return {}
    
    def _calculate_face_width(self, landmarks: np.ndarray) -> float:
        """Calculate face width at the widest point."""
        try:
            if len(landmarks) < 2:
                return 0.0
            
            # Find leftmost and rightmost points
            leftmost = np.min(landmarks[:, 0])
            rightmost = np.max(landmarks[:, 0])
            
            return float(rightmost - leftmost)
        except Exception as e:
            logger.error(f"Error calculating face width: {e}")
            return 0.0
    
    def _calculate_face_height(self, landmarks: np.ndarray) -> float:
        """Calculate face height from chin to forehead."""
        try:
            if len(landmarks) < 2:
                return 0.0
            
            # Find topmost and bottommost points
            topmost = np.min(landmarks[:, 1])
            bottommost = np.max(landmarks[:, 1])
            
            return float(bottommost - topmost)
        except Exception as e:
            logger.error(f"Error calculating face height: {e}")
            return 0.0
    
    def _calculate_forehead_width(self, landmarks: np.ndarray) -> float:
        """Calculate forehead width."""
        try:
            if len(landmarks) < 10:
                return 0.0
            
            # Use top portion of landmarks for forehead
            top_landmarks = landmarks[landmarks[:, 1] < np.percentile(landmarks[:, 1], 30)]
            
            if len(top_landmarks) < 2:
                return 0.0
            
            leftmost = np.min(top_landmarks[:, 0])
            rightmost = np.max(top_landmarks[:, 0])
            
            return float(rightmost - leftmost)
        except Exception as e:
            logger.error(f"Error calculating forehead width: {e}")
            return 0.0
    
    def _analyze_face_shape(self, landmarks: np.ndarray) -> str:
        """Analyze and classify face shape."""
        try:
            if len(landmarks) < 10:
                return "unknown"
            
            face_width = self._calculate_face_width(landmarks)
            face_height = self._calculate_face_height(landmarks)
            jaw_width = self._calculate_jaw_width(landmarks)
            cheekbone_width = self._calculate_cheekbone_width(landmarks)
            
            # Calculate ratios
            width_height_ratio = face_width / face_height if face_height > 0 else 0
            jaw_ratio = jaw_width / face_width if face_width > 0 else 0
            cheekbone_ratio = cheekbone_width / face_width if face_width > 0 else 0
            
            # Classify face shape based on ratios
            if width_height_ratio > 0.85:
                if jaw_ratio > 0.9:
                    return "round"
                elif cheekbone_ratio > 0.85:
                    return "square"
                else:
                    return "oval"
            elif width_height_ratio < 0.7:
                if jaw_ratio < 0.7:
                    return "heart"
                else:
                    return "diamond"
            else:
                if jaw_ratio > 0.85:
                    return "square"
                elif jaw_ratio < 0.7:
                    return "heart"
                else:
                    return "oval"
                    
        except Exception as e:
            logger.error(f"Error analyzing face shape: {e}")
            return "unknown"
    
    def _calculate_jaw_width(self, landmarks: np.ndarray) -> float:
        """Calculate jaw width."""
        try:
            if len(landmarks) < 10:
                return 0.0
            
            # Use bottom portion of landmarks for jaw
            bottom_landmarks = landmarks[landmarks[:, 1] > np.percentile(landmarks[:, 1], 70)]
            
            if len(bottom_landmarks) < 2:
                return 0.0
            
            leftmost = np.min(bottom_landmarks[:, 0])
            rightmost = np.max(bottom_landmarks[:, 0])
            
            return float(rightmost - leftmost)
        except Exception as e:
            logger.error(f"Error calculating jaw width: {e}")
            return 0.0
    
    def _calculate_cheekbone_width(self, landmarks: np.ndarray) -> float:
        """Calculate cheekbone width."""
        try:
            if len(landmarks) < 10:
                return 0.0
            
            # Use middle portion of landmarks for cheekbones
            middle_landmarks = landmarks[
                (landmarks[:, 1] > np.percentile(landmarks[:, 1], 30)) &
                (landmarks[:, 1] < np.percentile(landmarks[:, 1], 70))
            ]
            
            if len(middle_landmarks) < 2:
                return 0.0
            
            leftmost = np.min(middle_landmarks[:, 0])
            rightmost = np.max(middle_landmarks[:, 0])
            
            return float(rightmost - leftmost)
        except Exception as e:
            logger.error(f"Error calculating cheekbone width: {e}")
            return 0.0
    
    def _analyze_eyes(self, landmarks: np.ndarray) -> Dict:
        """Analyze eye features."""
        try:
            if len(landmarks) < 20:
                return {}
            
            # Find eye landmarks (simplified)
            eye_landmarks = landmarks[landmarks[:, 1] < np.percentile(landmarks[:, 1], 50)]
            
            if len(eye_landmarks) < 4:
                return {}
            
            # Calculate eye measurements
            eye_width = np.max(eye_landmarks[:, 0]) - np.min(eye_landmarks[:, 0])
            eye_height = np.max(eye_landmarks[:, 1]) - np.min(eye_landmarks[:, 1])
            eye_ratio = eye_width / eye_height if eye_height > 0 else 0
            
            # Calculate eye spacing
            left_eye_center = np.mean(eye_landmarks[eye_landmarks[:, 0] < np.median(eye_landmarks[:, 0])], axis=0)
            right_eye_center = np.mean(eye_landmarks[eye_landmarks[:, 0] > np.median(eye_landmarks[:, 0])], axis=0)
            eye_spacing = np.linalg.norm(right_eye_center - left_eye_center) if len(left_eye_center) > 0 and len(right_eye_center) > 0 else 0
            
            return {
                'eye_width': float(eye_width),
                'eye_height': float(eye_height),
                'eye_ratio': float(eye_ratio),
                'eye_spacing': float(eye_spacing),
                'eye_shape': 'almond' if eye_ratio > 2.5 else 'round' if eye_ratio < 1.5 else 'oval'
            }
        except Exception as e:
            logger.error(f"Error analyzing eyes: {e}")
            return {}
    
    def _analyze_nose(self, landmarks: np.ndarray) -> Dict:
        """Analyze nose features."""
        try:
            if len(landmarks) < 15:
                return {}
            
            # Find nose landmarks (middle portion)
            nose_landmarks = landmarks[
                (landmarks[:, 1] > np.percentile(landmarks[:, 1], 40)) &
                (landmarks[:, 1] < np.percentile(landmarks[:, 1], 70))
            ]
            
            if len(nose_landmarks) < 3:
                return {}
            
            # Calculate nose measurements
            nose_width = np.max(nose_landmarks[:, 0]) - np.min(nose_landmarks[:, 0])
            nose_height = np.max(nose_landmarks[:, 1]) - np.min(nose_landmarks[:, 1])
            nose_ratio = nose_width / nose_height if nose_height > 0 else 0
            
            # Analyze nose shape
            if nose_ratio > 0.8:
                nose_shape = 'wide'
            elif nose_ratio < 0.4:
                nose_shape = 'narrow'
            else:
                nose_shape = 'medium'
            
            return {
                'nose_width': float(nose_width),
                'nose_height': float(nose_height),
                'nose_ratio': float(nose_ratio),
                'nose_shape': nose_shape
            }
        except Exception as e:
            logger.error(f"Error analyzing nose: {e}")
            return {}
    
    def _analyze_mouth(self, landmarks: np.ndarray) -> Dict:
        """Analyze mouth features."""
        try:
            if len(landmarks) < 20:
                return {}
            
            # Find mouth landmarks (bottom portion)
            mouth_landmarks = landmarks[landmarks[:, 1] > np.percentile(landmarks[:, 1], 60)]
            
            if len(mouth_landmarks) < 3:
                return {}
            
            # Calculate mouth measurements
            mouth_width = np.max(mouth_landmarks[:, 0]) - np.min(mouth_landmarks[:, 0])
            mouth_height = np.max(mouth_landmarks[:, 1]) - np.min(mouth_landmarks[:, 1])
            mouth_ratio = mouth_width / mouth_height if mouth_height > 0 else 0
            
            # Analyze mouth shape
            if mouth_ratio > 3.0:
                mouth_shape = 'wide'
            elif mouth_ratio < 1.5:
                mouth_shape = 'narrow'
            else:
                mouth_shape = 'medium'
            
            return {
                'mouth_width': float(mouth_width),
                'mouth_height': float(mouth_height),
                'mouth_ratio': float(mouth_ratio),
                'mouth_shape': mouth_shape
            }
        except Exception as e:
            logger.error(f"Error analyzing mouth: {e}")
            return {}
    
    def _analyze_jaw(self, landmarks: np.ndarray) -> Dict:
        """Analyze jaw features."""
        try:
            if len(landmarks) < 10:
                return {}
            
            # Find jaw landmarks (bottom portion)
            jaw_landmarks = landmarks[landmarks[:, 1] > np.percentile(landmarks[:, 1], 80)]
            
            if len(jaw_landmarks) < 3:
                return {}
            
            # Calculate jaw measurements
            jaw_width = np.max(jaw_landmarks[:, 0]) - np.min(jaw_landmarks[:, 0])
            jaw_height = np.max(jaw_landmarks[:, 1]) - np.min(jaw_landmarks[:, 1])
            jaw_ratio = jaw_width / jaw_height if jaw_height > 0 else 0
            
            # Analyze jaw shape
            if jaw_ratio > 2.0:
                jaw_shape = 'square'
            elif jaw_ratio < 1.0:
                jaw_shape = 'pointed'
            else:
                jaw_shape = 'rounded'
            
            return {
                'jaw_width': float(jaw_width),
                'jaw_height': float(jaw_height),
                'jaw_ratio': float(jaw_ratio),
                'jaw_shape': jaw_shape
            }
        except Exception as e:
            logger.error(f"Error analyzing jaw: {e}")
            return {}
    
    def _calculate_symmetry(self, landmarks: np.ndarray) -> float:
        """Calculate facial symmetry score."""
        try:
            if len(landmarks) < 10:
                return 0.0
            
            # Find face center
            face_center_x = np.mean(landmarks[:, 0])
            
            # Calculate symmetry for each landmark
            symmetry_scores = []
            for landmark in landmarks:
                # Find corresponding point on other side
                distance_from_center = abs(landmark[0] - face_center_x)
                # This is a simplified symmetry calculation
                symmetry_scores.append(1.0 - (distance_from_center / face_center_x))
            
            return float(np.mean(symmetry_scores))
        except Exception as e:
            logger.error(f"Error calculating symmetry: {e}")
            return 0.0
    
    def _calculate_face_angle(self, landmarks: np.ndarray) -> float:
        """Calculate face rotation angle."""
        try:
            if len(landmarks) < 10:
                return 0.0
            
            # Use eye landmarks to calculate angle
            eye_landmarks = landmarks[landmarks[:, 1] < np.percentile(landmarks[:, 1], 50)]
            
            if len(eye_landmarks) < 4:
                return 0.0
            
            # Find left and right eye centers
            left_eye_center = np.mean(eye_landmarks[eye_landmarks[:, 0] < np.median(eye_landmarks[:, 0])], axis=0)
            right_eye_center = np.mean(eye_landmarks[eye_landmarks[:, 0] > np.median(eye_landmarks[:, 0])], axis=0)
            
            if len(left_eye_center) > 0 and len(right_eye_center) > 0:
                # Calculate angle between eyes
                angle = math.atan2(right_eye_center[1] - left_eye_center[1], 
                                 right_eye_center[0] - left_eye_center[0])
                return float(math.degrees(angle))
            
            return 0.0
        except Exception as e:
            logger.error(f"Error calculating face angle: {e}")
            return 0.0

class AdvancedFaceRecognition:
    """Advanced face recognition with detailed feature analysis."""
    
    def __init__(self):
        """Initialize the advanced face recognition system."""
        self.geometry_analyzer = FacialGeometryAnalyzer()
        self.feature_weights = {
            'face_embedding': 0.4,  # FaceNet embedding weight
            'face_geometry': 0.3,   # Geometric features weight
            'facial_features': 0.2, # Individual feature analysis weight
            'symmetry': 0.1         # Symmetry score weight
        }
    
    def extract_advanced_features(self, image: np.ndarray) -> Dict:
        """Extract comprehensive facial features."""
        try:
            # Analyze face geometry
            geometry_data = self.geometry_analyzer.analyze_face_geometry(image)
            
            # Create feature vector
            features = {
                'geometry': geometry_data,
                'feature_vector': self._create_feature_vector(geometry_data),
                'timestamp': time.time()
            }
            
            return features
            
        except Exception as e:
            logger.error(f"Error extracting advanced features: {e}")
            return {}
    
    def _create_feature_vector(self, geometry_data: Dict) -> List[float]:
        """Create a numerical feature vector from geometry data."""
        try:
            feature_vector = []
            
            # Basic measurements
            feature_vector.extend([
                geometry_data.get('face_width', 0),
                geometry_data.get('face_height', 0),
                geometry_data.get('forehead_width', 0),
                geometry_data.get('face_ratio', 0),
                geometry_data.get('forehead_ratio', 0),
                geometry_data.get('symmetry_score', 0),
                geometry_data.get('face_angle', 0)
            ])
            
            # Eye features
            eye_analysis = geometry_data.get('eye_analysis', {})
            feature_vector.extend([
                eye_analysis.get('eye_width', 0),
                eye_analysis.get('eye_height', 0),
                eye_analysis.get('eye_ratio', 0),
                eye_analysis.get('eye_spacing', 0)
            ])
            
            # Nose features
            nose_analysis = geometry_data.get('nose_analysis', {})
            feature_vector.extend([
                nose_analysis.get('nose_width', 0),
                nose_analysis.get('nose_height', 0),
                nose_analysis.get('nose_ratio', 0)
            ])
            
            # Mouth features
            mouth_analysis = geometry_data.get('mouth_analysis', {})
            feature_vector.extend([
                mouth_analysis.get('mouth_width', 0),
                mouth_analysis.get('mouth_height', 0),
                mouth_analysis.get('mouth_ratio', 0)
            ])
            
            # Jaw features
            jaw_analysis = geometry_data.get('jaw_analysis', {})
            feature_vector.extend([
                jaw_analysis.get('jaw_width', 0),
                jaw_analysis.get('jaw_height', 0),
                jaw_analysis.get('jaw_ratio', 0)
            ])
            
            # Categorical features (encoded)
            feature_vector.extend(self._encode_categorical_features(geometry_data))
            
            return feature_vector
            
        except Exception as e:
            logger.error(f"Error creating feature vector: {e}")
            return []
    
    def _encode_categorical_features(self, geometry_data: Dict) -> List[float]:
        """Encode categorical features to numerical values."""
        try:
            encoded = []
            
            # Face shape encoding
            face_shapes = ['round', 'square', 'oval', 'heart', 'diamond', 'unknown']
            face_shape = geometry_data.get('face_shape', 'unknown')
            encoded.extend([1.0 if shape == face_shape else 0.0 for shape in face_shapes])
            
            # Eye shape encoding
            eye_shapes = ['almond', 'round', 'oval']
            eye_shape = geometry_data.get('eye_analysis', {}).get('eye_shape', 'oval')
            encoded.extend([1.0 if shape == eye_shape else 0.0 for shape in eye_shapes])
            
            # Nose shape encoding
            nose_shapes = ['wide', 'medium', 'narrow']
            nose_shape = geometry_data.get('nose_analysis', {}).get('nose_shape', 'medium')
            encoded.extend([1.0 if shape == nose_shape else 0.0 for shape in nose_shapes])
            
            # Mouth shape encoding
            mouth_shapes = ['wide', 'medium', 'narrow']
            mouth_shape = geometry_data.get('mouth_analysis', {}).get('mouth_shape', 'medium')
            encoded.extend([1.0 if shape == mouth_shape else 0.0 for shape in mouth_shapes])
            
            # Jaw shape encoding
            jaw_shapes = ['square', 'rounded', 'pointed']
            jaw_shape = geometry_data.get('jaw_analysis', {}).get('jaw_shape', 'rounded')
            encoded.extend([1.0 if shape == jaw_shape else 0.0 for shape in jaw_shapes])
            
            return encoded
            
        except Exception as e:
            logger.error(f"Error encoding categorical features: {e}")
            return []
    
    def compare_advanced_features(self, features1: Dict, features2: Dict) -> Dict:
        """Compare two sets of advanced features."""
        try:
            vector1 = features1.get('feature_vector', [])
            vector2 = features2.get('feature_vector', [])
            
            if not vector1 or not vector2:
                return {'similarity': 0.0, 'confidence': 0.0}
            
            # Ensure vectors have same length
            min_length = min(len(vector1), len(vector2))
            vector1 = vector1[:min_length]
            vector2 = vector2[:min_length]
            
            # Calculate Euclidean distance
            distance = np.linalg.norm(np.array(vector1) - np.array(vector2))
            
            # Calculate similarity (inverse of distance, normalized)
            max_distance = np.sqrt(len(vector1))  # Maximum possible distance
            similarity = max(0.0, 1.0 - (distance / max_distance))
            
            # Calculate confidence based on feature quality
            confidence = self._calculate_confidence(features1, features2)
            
            return {
                'similarity': float(similarity),
                'confidence': float(confidence),
                'distance': float(distance),
                'feature_count': min_length
            }
            
        except Exception as e:
            logger.error(f"Error comparing advanced features: {e}")
            return {'similarity': 0.0, 'confidence': 0.0}
    
    def _calculate_confidence(self, features1: Dict, features2: Dict) -> float:
        """Calculate confidence score based on feature quality."""
        try:
            geometry1 = features1.get('geometry', {})
            geometry2 = features2.get('geometry', {})
            
            # Check landmark count
            landmarks1 = geometry1.get('landmarks_count', 0)
            landmarks2 = geometry2.get('landmarks_count', 0)
            landmark_confidence = min(landmarks1, landmarks2) / 68.0  # Assuming 68 landmarks max
            
            # Check symmetry scores
            symmetry1 = geometry1.get('symmetry_score', 0)
            symmetry2 = geometry2.get('symmetry_score', 0)
            symmetry_confidence = (symmetry1 + symmetry2) / 2.0
            
            # Check feature completeness
            feature_completeness = self._check_feature_completeness(geometry1, geometry2)
            
            # Combine confidence factors
            confidence = (landmark_confidence * 0.4 + 
                         symmetry_confidence * 0.3 + 
                         feature_completeness * 0.3)
            
            return min(1.0, max(0.0, confidence))
            
        except Exception as e:
            logger.error(f"Error calculating confidence: {e}")
            return 0.0
    
    def _check_feature_completeness(self, geometry1: Dict, geometry2: Dict) -> float:
        """Check completeness of extracted features."""
        try:
            required_features = [
                'face_width', 'face_height', 'forehead_width',
                'eye_analysis', 'nose_analysis', 'mouth_analysis', 'jaw_analysis'
            ]
            
            completeness1 = sum(1 for feature in required_features if geometry1.get(feature) is not None) / len(required_features)
            completeness2 = sum(1 for feature in required_features if geometry2.get(feature) is not None) / len(required_features)
            
            return (completeness1 + completeness2) / 2.0
            
        except Exception as e:
            logger.error(f"Error checking feature completeness: {e}")
            return 0.0

# Global instance
advanced_face_recognition = AdvancedFaceRecognition()

def analyze_facial_features(base64_image: str) -> Dict:
    """Analyze facial features from base64 image."""
    try:
        if not validate_base64_image(base64_image):
            return {'error': 'Invalid image format'}
        
        image = base64_to_image(base64_image)
        if image is None:
            return {'error': 'Failed to process image'}
        
        features = advanced_face_recognition.extract_advanced_features(image)
        
        if DEBUG and SAVE_DEBUG_IMAGES:
            from facenet_utils import save_debug_image
            save_debug_image(image, f"advanced_features_{int(time.time())}.jpg", DEBUG_IMAGE_PATH)
        
        return features
        
    except Exception as e:
        logger.error(f"Error analyzing facial features: {e}")
        return {'error': str(e)}

def compare_facial_features(features1: Dict, features2: Dict) -> Dict:
    """Compare two sets of facial features."""
    try:
        return advanced_face_recognition.compare_advanced_features(features1, features2)
    except Exception as e:
        logger.error(f"Error comparing facial features: {e}")
        return {'similarity': 0.0, 'confidence': 0.0}

if __name__ == '__main__':
    # Test the advanced features
    print("FaceNet Advanced Features Analysis")
    print("=" * 50)
    
    # This would be used in integration with the main FaceNet service
    print("Advanced facial feature analysis module loaded successfully.")
