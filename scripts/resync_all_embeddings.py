"""
Correct resync: process ALL users in a single Python process.
Uses the FaceNetService class to ensure consistent embedding generation.
Run: C:\Python313\python.exe scripts\resync_all_embeddings.py
"""
import sys, os, json
import numpy as np
import mysql.connector

# Add scripts to path
SCRIPTS_DIR = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, SCRIPTS_DIR)
sys.path.insert(0, os.path.join(SCRIPTS_DIR, 'facenet-master'))

import facenet_config as config
from facenet_service import FaceNetService

print("Initializing FaceNet Service (this takes ~30s)...")
service = FaceNetService()
print("Service initialized.\n")

conn = mysql.connector.connect(
    host=config.DB_HOST, database=config.DB_NAME,
    user=config.DB_USER, password=config.DB_PASS
)
cur = conn.cursor()

# Get all users with photos
cur.execute("SELECT id, nama, foto_base64 FROM users WHERE foto_base64 IS NOT NULL AND foto_base64 != ''")
users = cur.fetchall()
print(f"Found {len(users)} users with photos.\n")

storage_path = os.path.join(SCRIPTS_DIR, '..', 'storage', 'app', 'public', 'users')
success_count = 0
fail_count = 0

for i, (uid, name, foto) in enumerate(users):
    num = i + 1
    total = len(users)
    print(f"[{num}/{total}] Processing {name}...", end=' ', flush=True)
    
    photo_path = os.path.join(storage_path, foto)
    if not os.path.exists(photo_path):
        print(f"SKIP (photo not found: {photo_path})")
        fail_count += 1
        continue
    
    try:
        # Generate embedding using the service (which now has the fix)
        emb_list = service.generate_embedding(photo_path)
        
        if emb_list is None:
            print("SKIP (embedding generation failed)")
            fail_count += 1
            continue
        
        # Save to DB
        update_cur = conn.cursor()
        update_cur.execute(
            "UPDATE users SET face_embedding = %s WHERE id = %s",
            (json.dumps(emb_list), uid)
        )
        conn.commit()
        update_cur.close()
        
        print(f"OK (dim={len(emb_list)}, first3={emb_list[:3]})")
        success_count += 1
        
    except Exception as e:
        print(f"ERROR: {e}")
        fail_count += 1

cur.close()
conn.close()

print(f"\n=== Resync Complete: {success_count} success, {fail_count} failed ===")
