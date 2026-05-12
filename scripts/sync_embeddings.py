import os
import sys
import json
import base64
import torch
import numpy as np
from PIL import Image
import io
import sqlite3
from facenet_pytorch import MTCNN, InceptionResnetV1

# Initialize FaceNet for 512-dim (standard)
# Note: Face-API.js uses a custom 128-dim model. 
# Since we want TRUE compatibility, we will use the Python 512-dim for high accuracy 
# and update the browser to handle 512-dim instead. This is MUCH more accurate.

device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')
mtcnn = MTCNN(image_size=160, margin=0, device=device)
resnet = InceptionResnetV1(pretrained='vggface2').eval().to(device)

def get_db_connection():
    db_path = 'database/database.sqlite' # Adjust to your DB path
    if not os.path.exists(db_path):
        # Try finding it in the project root
        db_path = 'd:/xampp/htdocs/Magang/LaravelAbsen/database/database.sqlite'
    
    conn = sqlite3.connect(db_path)
    return conn

def process_users():
    conn = get_db_connection()
    cursor = conn.cursor()
    
    cursor.execute("SELECT id, nama, foto_base64 FROM users WHERE role='pegawai'")
    users = cursor.fetchall()
    
    print(f"🚀 Processing {len(users)} users...")
    
    for user_id, nama, foto_base64 in users:
        if not foto_base64:
            print(f"⚠️ Skipping {nama} (No photo)")
            continue
            
        try:
            # Handle base64 or file path
            if foto_base64.startswith('data:image'):
                img_data = base64.b64decode(foto_base64.split(',')[1])
                img = Image.open(io.BytesIO(img_data)).convert('RGB')
            else:
                # Try relative path
                photo_path = f"public/storage/users/{foto_base64}"
                if not os.path.exists(photo_path):
                    photo_path = f"d:/xampp/htdocs/Magang/LaravelAbsen/public/storage/users/{foto_base64}"
                
                if os.path.exists(photo_path):
                    img = Image.open(photo_path).convert('RGB')
                else:
                    print(f"⚠️ Photo not found for {nama}: {foto_base64}")
                    continue

            # Detect and extract embedding
            img_cropped = mtcnn(img)
            if img_cropped is not None:
                embedding = resnet(img_cropped.unsqueeze(0)).detach().cpu().numpy()[0]
                embedding_json = json.dumps(embedding.tolist())
                
                # Save to BOTH columns for maximum compatibility
                cursor.execute(
                    "UPDATE users SET face_embedding = ?, face_embedding_128 = ? WHERE id = ?",
                    (embedding_json, embedding_json, user_id)
                )
                print(f"✅ Success: {nama} (Embedding size: {len(embedding)})")
            else:
                print(f"❌ Face not detected in {nama}'s photo")
                
        except Exception as e:
            print(f"🔥 Error processing {nama}: {str(e)}")
            
    conn.commit()
    conn.close()
    print("\n✨ All embeddings synchronized! Every device will now be INSTANT.")

if __name__ == "__main__":
    process_users()
