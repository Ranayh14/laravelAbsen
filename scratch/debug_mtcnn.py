"""
Debug: Check if MTCNN is actually processing different images differently.
"""
import sys, os
import numpy as np

SCRIPTS_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'scripts')
sys.path.insert(0, SCRIPTS_DIR)
sys.path.insert(0, os.path.join(SCRIPTS_DIR, 'facenet-master'))

from PIL import Image
import torch
from facenet_pytorch import InceptionResnetV1, MTCNN, fixed_image_standardization

import facenet_config as config
import mysql.connector, json

conn = mysql.connector.connect(host=config.DB_HOST, database=config.DB_NAME, user=config.DB_USER, password=config.DB_PASS)
cur = conn.cursor()
cur.execute("SELECT id, nama, foto_base64 FROM users WHERE foto_base64 IS NOT NULL AND foto_base64 != '' ORDER BY id LIMIT 4")
users = cur.fetchall()
cur.close()
conn.close()

storage_path = os.path.join(SCRIPTS_DIR, '..', 'storage', 'app', 'public', 'users')

print("Initializing MTCNN + model...")
mtcnn = MTCNN(image_size=160, margin=32, keep_all=False, device='cpu')
model = InceptionResnetV1(pretrained='vggface2').eval()
print("Done.\n")

tensors = []
for uid, name, foto in users:
    photo_path = os.path.join(storage_path, foto)
    img = Image.open(photo_path)
    print(f"Image size for {name}: {img.size}")
    
    face_tensor = mtcnn(img)
    if face_tensor is None:
        print(f"  -> No face detected!")
        continue
    
    print(f"  -> Face tensor shape: {face_tensor.shape}, mean pixel: {face_tensor.mean():.4f}")
    tensors.append((name, face_tensor))

print("\n--- Are face tensors identical? ---")
for i in range(len(tensors)):
    for j in range(i+1, len(tensors)):
        a_name, a_t = tensors[i]
        b_name, b_t = tensors[j]
        diff = (a_t - b_t).abs().max().item()
        print(f"{a_name} vs {b_name}: max_pixel_diff={diff:.6f}")

print("\n--- Computing embeddings ---")
for name, ft in tensors:
    std = fixed_image_standardization(ft)
    with torch.no_grad():
        emb = model(std.unsqueeze(0))
    emb = torch.nn.functional.normalize(emb, p=2, dim=1).squeeze().cpu().numpy()
    print(f"{name}: first5={emb[:5].tolist()}")
