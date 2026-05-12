import os
import json
import numpy as np
import cv2
import logging
import torch
from facenet_pytorch import InceptionResnetV1, MTCNN
from PIL import Image
from facenet_database import db

# Konfigurasi Logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("FaceNetService")

class FaceNetService:
    def __init__(self):
        # Path ke model .pth yang ditemukan user
        self.model_path = r'd:\xampp\htdocs\Magang\LaravelAbsen\scripts\facenet-master\models\facenet_20180402_114759_vggface2.pth'
        
        logger.info("Initializing FaceNet (PyTorch version)...")
        
        # Inisialisasi MTCNN untuk deteksi wajah
        self.mtcnn = MTCNN(image_size=160, margin=32, keep_all=False, device='cpu')
        
        # Initialize Resnet V1 (FaceNet architecture)
        # Fallback to 'vggface2' pretrained if local file is missing
        try:
            if os.path.exists(self.model_path):
                logger.info(f"Loading weights from {self.model_path}")
                self.model = InceptionResnetV1(pretrained=None).eval()
                state_dict = torch.load(self.model_path, map_location='cpu')
                
                # Filter out classification layer if it exists (we only need embeddings)
                keys_to_remove = ["logits.weight", "logits.bias", "last_linear.weight", "last_linear.bias", "last_bn.weight", "last_bn.bias"]
                for key in keys_to_remove:
                    if key in state_dict:
                        del state_dict[key]
                        
                self.model.load_state_dict(state_dict, strict=False)
                logger.info("Model weights loaded successfully from local file.")
            else:
                logger.warning(f"Model file not found at {self.model_path}. Falling back to 'vggface2' pretrained weights.")
                self.model = InceptionResnetV1(pretrained='vggface2').eval()
                logger.info("Loaded built-in 'vggface2' weights successfully.")
        except Exception as e:
            logger.error(f"Error loading model weights: {str(e)}")
            # Last resort fallback
            self.model = InceptionResnetV1(pretrained='vggface2').eval()

    def generate_embedding(self, image_path):
        """Menghasilkan embedding (sidik jari wajah) dari gambar."""
        try:
            # Load image
            img = Image.open(image_path)
            
            # Deteksi dan potong wajah (Crop)
            # mtcnn(img) mengembalikan tensor wajah yang sudah dinormalisasi
            face_tensor = self.mtcnn(img)
            
            if face_tensor is None:
                logger.warning(f"No face detected in {image_path}")
                return None
            
            # face_tensor is already normalized to [-1, 1] by MTCNN
            # Do NOT apply fixed_image_standardization — it causes value collapse
            face_tensor = face_tensor.unsqueeze(0)
            
            # Generate embedding
            with torch.no_grad():
                embedding = self.model(face_tensor)
            
            # L2 Normalization (PENTING untuk konsistensi Euclidean Distance)
            embedding = torch.nn.functional.normalize(embedding, p=2, dim=1)
            
            # Convert to list
            return embedding.squeeze().cpu().numpy().tolist()
            
        except Exception as e:
            logger.error(f"Error generating embedding: {str(e)}")
            return None

    def verify_face(self, image_path, target_user_id, threshold=0.6):
        """Verifikasi wajah 1-ke-1 khusus untuk target_user_id (Paling AMAN)."""
        try:
            target_user_id = int(target_user_id)
            known_emb = db.get_user_embedding(target_user_id)
            
            if known_emb is None:
                logger.warning(f"No embedding found for user {target_user_id}")
                return {'error': 'User tidak memiliki data wajah terdaftar.'}

            img = Image.open(image_path)
            face_tensor = self.mtcnn(img)
            
            if face_tensor is None:
                return {'error': 'Wajah tidak terdeteksi pada gambar.'}
                
            # face_tensor already normalized by MTCNN
            with torch.no_grad():
                embedding = self.model(face_tensor.unsqueeze(0))
            embedding = torch.nn.functional.normalize(embedding, p=2, dim=1).squeeze().cpu().numpy()
            
            # Mirror check for better accuracy
            face_tensor_mirrored = torch.flip(face_tensor, [2])
            with torch.no_grad():
                embedding_mirrored = self.model(face_tensor_mirrored.unsqueeze(0))
            embedding_mirrored = torch.nn.functional.normalize(embedding_mirrored, p=2, dim=1).squeeze().cpu().numpy()

            dist_orig = np.linalg.norm(embedding - known_emb)
            dist_mirr = np.linalg.norm(embedding_mirrored - known_emb)
            dist = min(dist_orig, dist_mirr)
            
            is_match = bool(dist <= threshold)
            return {
                'match': is_match,
                'distance': float(dist),
                'confidence': float(max(0, 1 - dist))
            }
            
        except Exception as e:
            logger.error(f"Error in verify_face: {str(e)}")
            return {'error': str(e)}

    def recognize_face(self, image_path, threshold=0.6):
        """Mencocokkan wajah dari gambar dengan database (termasuk cek mirroring)."""
        try:
            img = Image.open(image_path)
            face_tensor = self.mtcnn(img)
            
            if face_tensor is None:
                logger.warning(f"No face detected in {image_path}")
                return None
                
            # face_tensor already normalized by MTCNN — no extra standardization needed
            with torch.no_grad():
                embedding = self.model(face_tensor.unsqueeze(0))
            embedding = torch.nn.functional.normalize(embedding, p=2, dim=1).squeeze().cpu().numpy()
            
            # Mirror embedding — flip face tensor horizontally
            face_tensor_mirrored = torch.flip(face_tensor, [2])
            with torch.no_grad():
                embedding_mirrored = self.model(face_tensor_mirrored.unsqueeze(0))
            embedding_mirrored = torch.nn.functional.normalize(embedding_mirrored, p=2, dim=1).squeeze().cpu().numpy()

            known_faces = db.get_all_embeddings()
            if not known_faces:
                return None

            best_match = None
            min_dist = float('inf')

            for user_id, face_data in known_faces.items():
                known_emb = np.array(face_data['embedding'])
                
                # Cek jarak versi asli
                dist_orig = np.linalg.norm(embedding - known_emb)
                # Cek jarak versi mirrored
                dist_mirr = np.linalg.norm(embedding_mirrored - known_emb)
                
                # Ambil yang terkecil di antara keduanya
                dist = min(dist_orig, dist_mirr)
                
                if dist < min_dist:
                    min_dist = dist
                    best_match = {
                        'user_id': user_id,
                        'nama': face_data.get('nama'),
                        'nim': face_data.get('nim')
                    }

            logger.info(f"Identity check: best distance {min_dist:.4f}")

            if best_match and min_dist <= threshold:
                return {
                    'user_id': best_match['user_id'],
                    'nama': best_match['nama'],
                    'nim': best_match['nim'],
                    'confidence': float(max(0, 1 - min_dist)),
                    'distance': float(min_dist)
                }
            
            return {'distance': float(min_dist)} if best_match else None
            
        except Exception as e:
            logger.error(f"Error in recognize_face: {str(e)}")
            return None

# Singleton instance
service = FaceNetService()
