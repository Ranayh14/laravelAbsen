#!/usr/bin/env python3
"""
FaceNet CLI Interface

This script serves as a command-line interface for the FaceNet service.
It receives JSON arguments and calls the appropriate FaceNet functions.
"""

import sys
import json
import os
import traceback

# Add the facenet-master directory to Python path
sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'facenet-master'))

try:
    from facenet_service import FaceNetService
except ImportError as e:
    print(json.dumps({
        'success': False,
        'error': f'Failed to import FaceNet service: {str(e)}'
    }))
    sys.exit(1)

def main():
    if len(sys.argv) != 2:
        print(json.dumps({
            'success': False,
            'error': 'Usage: python facenet_cli.py <json_args>'
        }))
        sys.exit(1)
    
    try:
        # Parse JSON arguments
        args = json.loads(sys.argv[1])
        action = args.get('action')
        image = args.get('image')
        threshold = args.get('threshold', 1.0)
        
        if not action or not image:
            print(json.dumps({
                'success': False,
                'error': 'Action and image are required'
            }))
            sys.exit(1)
        
        # Initialize FaceNet service
        service = FaceNetService()
        
        # Execute the requested action
        if action == 'generate_embedding':
            result = service.generate_embedding(image)
            if result:
                print(json.dumps({
                    'success': True,
                    'data': {
                        'embedding': result
                    }
                }))
            else:
                print(json.dumps({
                    'success': False,
                    'error': 'Failed to generate embedding'
                }))
        
        elif action == 'save_embedding':
            user_id = args.get('user_id')
            if not user_id:
                print(json.dumps({
                    'success': False,
                    'error': 'User ID is required for saving embedding'
                }))
                sys.exit(1)
            
            # Generate embedding first
            embedding = service.generate_embedding(image)
            if embedding:
                # Save to database
                success = service.save_embedding_to_database(int(user_id), embedding)
                if success:
                    print(json.dumps({
                        'success': True,
                        'message': 'Embedding generated and saved successfully'
                    }))
                else:
                    print(json.dumps({
                        'success': False,
                        'error': 'Failed to save embedding to database'
                    }))
            else:
                print(json.dumps({
                    'success': False,
                    'error': 'Failed to generate embedding'
                }))
        
        elif action == 'recognize_face':
            result = service.recognize_face(image, threshold)
            if result:
                print(json.dumps({
                    'success': True,
                    'data': result
                }))
            else:
                print(json.dumps({
                    'success': False,
                    'error': 'Failed to recognize face'
                }))
        
        elif action == 'process_attendance':
            result = service.process_attendance(image, threshold)
            if result:
                print(json.dumps({
                    'success': True,
                    'data': result
                }))
            else:
                print(json.dumps({
                    'success': False,
                    'error': 'Failed to process attendance'
                }))
        
        else:
            print(json.dumps({
                'success': False,
                'error': f'Unknown action: {action}'
            }))
    
    except json.JSONDecodeError as e:
        print(json.dumps({
            'success': False,
            'error': f'Invalid JSON: {str(e)}'
        }))
        sys.exit(1)
    
    except Exception as e:
        print(json.dumps({
            'success': False,
            'error': f'Unexpected error: {str(e)}',
            'traceback': traceback.format_exc()
        }))
        sys.exit(1)

if __name__ == '__main__':
    main()