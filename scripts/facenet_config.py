#!/usr/bin/env python3
"""
FaceNet Configuration

This file contains configuration settings for the FaceNet service.
"""

import os

# Model paths
FACENET_MODEL_PATH = os.path.join('facenet-master', 'models', '20180402-114759')
MTCNN_MODEL_PATH = os.path.join('facenet-master', 'models', 'mtcnn_weights')

# Face detection settings - Optimized for MAXIMUM ACCURACY
FACE_CROP_SIZE = 160
FACE_CROP_MARGIN = 32
MIN_FACE_SIZE = 40  # Increased from 30 to 40 - reject small/blurry faces
FACE_THRESHOLDS = [0.7, 0.8, 0.8]  # Stricter MTCNN thresholds for better accuracy

# Recognition settings - Optimized for MAXIMUM ACCURACY (preventing false positives)
DEFAULT_THRESHOLD = 0.6  # Increased from 0.4 to 0.6 - more strict to prevent false matches
MAX_FACES_PER_IMAGE = 1  # Limit to 1 face for better performance

# Image processing settings
IMAGE_SIZE = (224, 224)
IMAGE_FORMAT = 'JPEG'
IMAGE_QUALITY = 95

# Database settings
DB_HOST = 'localhost'
DB_NAME = 'absen_db'
DB_USER = 'root'
DB_PASS = ''

# API settings
API_TIMEOUT = 30
MAX_IMAGE_SIZE = 10 * 1024 * 1024  # 10MB

# Logging settings
LOG_LEVEL = 'INFO'
LOG_FILE = 'facenet.log'

# Performance settings
BATCH_SIZE = 1
USE_GPU = False
GPU_MEMORY_FRACTION = 0.5

# Security settings
ALLOWED_IMAGE_FORMATS = ['JPEG', 'PNG', 'WEBP']
MAX_IMAGE_DIMENSION = 4096

# Debug settings
DEBUG = False
SAVE_DEBUG_IMAGES = False
DEBUG_IMAGE_PATH = 'debug_images'

# Model settings
MODEL_BACKEND = 'tensorflow'  # 'tensorflow' or 'keras'
MODEL_VERSION = '1.0'

# Face recognition settings - Optimized for MAXIMUM ACCURACY
RECOGNITION_METHOD = 'euclidean'  # 'euclidean' or 'cosine'
NORMALIZE_EMBEDDINGS = True
GENDER_VALIDATION = True  # ENABLED - Prevent cross-gender false matches
MULTI_ATTEMPT_VALIDATION = True  # ENABLED - Multiple validation for higher confidence
STRICT_MODE = True  # ENABLED - Reject ambiguous matches

# Cache settings
ENABLE_CACHE = True
CACHE_SIZE = 1000
CACHE_TTL = 3600  # 1 hour

# Error handling
MAX_RETRIES = 3
RETRY_DELAY = 1  # seconds

# Validation settings
VALIDATE_IMAGE_SIZE = True
VALIDATE_IMAGE_FORMAT = True
VALIDATE_FACE_COUNT = True

# Output settings
RETURN_CONFIDENCE = True
RETURN_DISTANCE = True
RETURN_BOUNDING_BOX = True

# Development settings
DEVELOPMENT_MODE = False
MOCK_RECOGNITION = False
MOCK_EMBEDDING = False

# Configuration validation
def validate_config():
    """Validate the configuration settings."""
    errors = []
    
    # Check model paths
    if not os.path.exists(FACENET_MODEL_PATH):
        errors.append(f"FaceNet model path not found: {FACENET_MODEL_PATH}")
    
    if not os.path.exists(MTCNN_MODEL_PATH):
        errors.append(f"MTCNN model path not found: {MTCNN_MODEL_PATH}")
    
    # Check image settings
    if IMAGE_SIZE[0] <= 0 or IMAGE_SIZE[1] <= 0:
        errors.append("Invalid image size")
    
    if IMAGE_QUALITY < 1 or IMAGE_QUALITY > 100:
        errors.append("Invalid image quality")
    
    # Check threshold settings
    if DEFAULT_THRESHOLD < 0:
        errors.append("Invalid default threshold")
    
    # Check batch size
    if BATCH_SIZE < 1:
        errors.append("Invalid batch size")
    
    return errors

# Get configuration as dictionary
def get_config():
    """Get configuration as dictionary."""
    return {
        'model_paths': {
            'facenet': FACENET_MODEL_PATH,
            'mtcnn': MTCNN_MODEL_PATH
        },
        'face_detection': {
            'crop_size': FACE_CROP_SIZE,
            'crop_margin': FACE_CROP_MARGIN,
            'min_face_size': MIN_FACE_SIZE,
            'thresholds': FACE_THRESHOLDS
        },
        'recognition': {
            'default_threshold': DEFAULT_THRESHOLD,
            'max_faces': MAX_FACES_PER_IMAGE,
            'method': RECOGNITION_METHOD,
            'normalize': NORMALIZE_EMBEDDINGS
        },
        'image_processing': {
            'size': IMAGE_SIZE,
            'format': IMAGE_FORMAT,
            'quality': IMAGE_QUALITY,
            'max_size': MAX_IMAGE_SIZE,
            'max_dimension': MAX_IMAGE_DIMENSION
        },
        'performance': {
            'batch_size': BATCH_SIZE,
            'use_gpu': USE_GPU,
            'gpu_memory_fraction': GPU_MEMORY_FRACTION
        },
        'security': {
            'allowed_formats': ALLOWED_IMAGE_FORMATS,
            'max_image_size': MAX_IMAGE_SIZE
        },
        'debug': {
            'enabled': DEBUG,
            'save_images': SAVE_DEBUG_IMAGES,
            'image_path': DEBUG_IMAGE_PATH
        },
        'cache': {
            'enabled': ENABLE_CACHE,
            'size': CACHE_SIZE,
            'ttl': CACHE_TTL
        }
    }

if __name__ == '__main__':
    # Validate configuration
    errors = validate_config()
    if errors:
        print("Configuration errors:")
        for error in errors:
            print(f"  - {error}")
    else:
        print("Configuration is valid")
    
    # Print configuration
    print("\nCurrent configuration:")
    config = get_config()
    for section, settings in config.items():
        print(f"\n{section.upper()}:")
        for key, value in settings.items():
            print(f"  {key}: {value}")
