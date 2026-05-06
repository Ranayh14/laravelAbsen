#!/usr/bin/env python3
"""
Face Alignment and Normalization for Enhanced Accuracy

This module provides advanced face alignment and normalization techniques
to improve face recognition accuracy similar to iPhone Face ID.
"""

import cv2
import numpy as np
import math
from typing import Dict, List, Tuple, Optional
import logging

logger = logging.getLogger(__name__)

class AdvancedFaceAligner:
    """Advanced face alignment for maximum recognition accuracy."""
    
    def __init__(self):
        """Initialize the face aligner."""
        # Standard face template (normalized face positions)
        self.face_template = np.array([
            [0.31556875000000000, 0.4615741071428571],
            [0.68262291666666670, 0.4615741071428571],
            [0.50026249999999990, 0.6405053571428571],
            [0.34947187500000004, 0.8246919642857142],
            [0.65343645833333330, 0.8246919642857142]
        ], dtype=np.float32)
        
        # Face template size
        self.template_size = (112, 112)
        
        # Eye cascade for eye detection
        self.eye_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_eye.xml')
        
    def align_face_advanced(self, image: np.ndarray, landmarks: np.ndarray) -> Optional[np.ndarray]:
        """Advanced face alignment using facial landmarks."""
        try:
            if landmarks is None or len(landmarks) < 5:
                return self._align_face_fallback(image)
            
            # Convert landmarks to numpy array
            if isinstance(landmarks, list):
                landmarks = np.array(landmarks, dtype=np.float32)
            
            # Ensure we have 5 key points (2 eyes, nose, 2 mouth corners)
            if landmarks.shape[0] < 5:
                return self._align_face_fallback(image)
            
            # Select 5 key points for alignment
            key_points = landmarks[:5]
            
            # Scale landmarks to template size
            scaled_landmarks = key_points * np.array([self.template_size[0], self.template_size[1]])
            
            # Calculate transformation matrix
            transform_matrix = cv2.getAffineTransform(
                scaled_landmarks[:3].astype(np.float32),
                self.face_template[:3] * np.array([self.template_size[0], self.template_size[1]])
            )
            
            # Apply transformation
            aligned_face = cv2.warpAffine(
                image, 
                transform_matrix, 
                self.template_size,
                flags=cv2.INTER_LINEAR,
                borderMode=cv2.BORDER_REFLECT_101
            )
            
            # Additional normalization
            aligned_face = self._normalize_face(aligned_face)
            
            return aligned_face
            
        except Exception as e:
            logger.error(f"Error in advanced face alignment: {e}")
            return self._align_face_fallback(image)
    
    def _align_face_fallback(self, image: np.ndarray) -> np.ndarray:
        """Fallback face alignment using eye detection."""
        try:
            # Convert to grayscale
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
            
            # Detect eyes
            eyes = self.eye_cascade.detectMultiScale(gray, 1.1, 3)
            
            if len(eyes) >= 2:
                # Sort eyes by x-coordinate
                eyes = sorted(eyes, key=lambda x: x[0])
                
                # Get eye centers
                eye1_center = (eyes[0][0] + eyes[0][2]//2, eyes[0][1] + eyes[0][3]//2)
                eye2_center = (eyes[1][0] + eyes[1][2]//2, eyes[1][1] + eyes[1][3]//2)
                
                # Calculate angle between eyes
                dx = eye2_center[0] - eye1_center[0]
                dy = eye2_center[1] - eye1_center[1]
                angle = math.degrees(math.atan2(dy, dx))
                
                # Rotate image to align eyes horizontally
                center = (image.shape[1]//2, image.shape[0]//2)
                rotation_matrix = cv2.getRotationMatrix2D(center, angle, 1.0)
                rotated = cv2.warpAffine(image, rotation_matrix, (image.shape[1], image.shape[0]))
                
                # Resize to standard size
                aligned_face = cv2.resize(rotated, self.template_size)
                
                # Normalize
                aligned_face = self._normalize_face(aligned_face)
                
                return aligned_face
            else:
                # No eyes detected, just resize and normalize
                aligned_face = cv2.resize(image, self.template_size)
                return self._normalize_face(aligned_face)
                
        except Exception as e:
            logger.error(f"Error in fallback alignment: {e}")
            # Last resort: just resize and normalize
            aligned_face = cv2.resize(image, self.template_size)
            return self._normalize_face(aligned_face)
    
    def _normalize_face(self, face: np.ndarray) -> np.ndarray:
        """Normalize face for optimal recognition."""
        try:
            # Convert to float32
            face_float = face.astype(np.float32)
            
            # Normalize to [0, 1]
            face_normalized = face_float / 255.0
            
            # Apply histogram equalization for better contrast
            if len(face_normalized.shape) == 3:
                # Color image
                face_yuv = cv2.cvtColor((face_normalized * 255).astype(np.uint8), cv2.COLOR_BGR2YUV)
                face_yuv[:,:,0] = cv2.equalizeHist(face_yuv[:,:,0])
                face_normalized = cv2.cvtColor(face_yuv, cv2.COLOR_YUV2BGR).astype(np.float32) / 255.0
            else:
                # Grayscale image
                face_normalized = cv2.equalizeHist((face_normalized * 255).astype(np.uint8)).astype(np.float32) / 255.0
            
            # Apply gamma correction for better lighting
            gamma = 1.2
            face_normalized = np.power(face_normalized, 1.0/gamma)
            
            # Ensure values are in [0, 1] range
            face_normalized = np.clip(face_normalized, 0, 1)
            
            return face_normalized
            
        except Exception as e:
            logger.error(f"Error normalizing face: {e}")
            return face.astype(np.float32) / 255.0

class FaceQualityEnhancer:
    """Enhance face quality for better recognition."""
    
    def __init__(self):
        """Initialize the face quality enhancer."""
        self.aligner = AdvancedFaceAligner()
        
    def enhance_face_quality(self, image: np.ndarray, landmarks: np.ndarray = None) -> np.ndarray:
        """Enhance face quality for optimal recognition."""
        try:
            # Step 1: Align face
            aligned_face = self.aligner.align_face_advanced(image, landmarks)
            
            if aligned_face is None:
                return image
            
            # Step 2: Apply additional enhancements
            enhanced_face = self._apply_enhancements(aligned_face)
            
            return enhanced_face
            
        except Exception as e:
            logger.error(f"Error enhancing face quality: {e}")
            return image
    
    def _apply_enhancements(self, face: np.ndarray) -> np.ndarray:
        """Apply quality enhancements to face."""
        try:
            # Convert to uint8 for processing
            face_uint8 = (face * 255).astype(np.uint8)
            
            # Apply bilateral filter for noise reduction while preserving edges
            face_filtered = cv2.bilateralFilter(face_uint8, 9, 75, 75)
            
            # Apply unsharp masking for sharpening
            gaussian = cv2.GaussianBlur(face_filtered, (0, 0), 2.0)
            face_sharpened = cv2.addWeighted(face_filtered, 1.5, gaussian, -0.5, 0)
            
            # Convert back to float32
            enhanced_face = face_sharpened.astype(np.float32) / 255.0
            
            return enhanced_face
            
        except Exception as e:
            logger.error(f"Error applying enhancements: {e}")
            return face

class FacePreprocessor:
    """Comprehensive face preprocessing for maximum accuracy."""
    
    def __init__(self):
        """Initialize the face preprocessor."""
        self.quality_enhancer = FaceQualityEnhancer()
        
    def preprocess_face(self, image: np.ndarray, face_info: Dict) -> np.ndarray:
        """Comprehensive face preprocessing."""
        try:
            # Extract face region
            x1, y1, x2, y2 = face_info['bbox']
            face_region = image[y1:y2, x1:x2]
            
            # Get landmarks if available
            landmarks = face_info.get('landmarks')
            
            # Enhance face quality
            enhanced_face = self.quality_enhancer.enhance_face_quality(face_region, landmarks)
            
            # Resize to standard size for FaceNet
            final_face = cv2.resize(enhanced_face, (160, 160))
            
            return final_face
            
        except Exception as e:
            logger.error(f"Error preprocessing face: {e}")
            # Fallback: just resize the face region
            x1, y1, x2, y2 = face_info['bbox']
            face_region = image[y1:y2, x1:x2]
            return cv2.resize(face_region, (160, 160)).astype(np.float32) / 255.0

# Global instances
face_aligner = AdvancedFaceAligner()
quality_enhancer = FaceQualityEnhancer()
face_preprocessor = FacePreprocessor()

def align_face_advanced(image: np.ndarray, landmarks: np.ndarray) -> Optional[np.ndarray]:
    """Advanced face alignment."""
    return face_aligner.align_face_advanced(image, landmarks)

def enhance_face_quality(image: np.ndarray, landmarks: np.ndarray = None) -> np.ndarray:
    """Enhance face quality."""
    return quality_enhancer.enhance_face_quality(image, landmarks)

def preprocess_face(image: np.ndarray, face_info: Dict) -> np.ndarray:
    """Comprehensive face preprocessing."""
    return face_preprocessor.preprocess_face(image, face_info)

if __name__ == '__main__':
    # Test the face alignment system
    print("Face Alignment and Quality Enhancement System")
    print("=" * 50)
    print("Advanced face alignment and normalization loaded successfully.")
    print("Ready for enhanced face recognition operations.")
