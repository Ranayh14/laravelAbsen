"""
Direct diagnostic: compare Dini's photo against Rana's stored embedding.
Run from the project root.
"""
import sys, os, json
import numpy as np
import mysql.connector

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..', 'scripts'))
sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..', 'scripts', 'facenet-master'))

from facenet_service import FaceNetService
import facenet_config as config

db_conn = mysql.connector.connect(
    host=config.DB_HOST, database=config.DB_NAME,
    user=config.DB_USER, password=config.DB_PASS
)

cur = db_conn.cursor()

# Get Rana's stored embedding
cur.execute("SELECT nama, face_embedding FROM users WHERE id = 2")
rana = cur.fetchone()
print(f"Rana: {rana[0]}, Embedding dim: {len(json.loads(rana[1]))}")

# Get Dini's photo path
cur.execute("SELECT nama, foto_base64 FROM users WHERE nama LIKE '%Dini%' LIMIT 1")
dini = cur.fetchone()
print(f"Dini: {dini[0]}, Photo: {dini[1]}")
cur.close()
db_conn.close()

dini_photo = os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'public', 'users', dini[1])
print(f"Dini photo path: {dini_photo}")
print(f"Dini photo exists: {os.path.exists(dini_photo)}")

print("\n--- Initializing FaceNet service (takes ~30s) ---")
svc = FaceNetService()

print("\n--- Running verify_face(Dini's photo, Rana's ID=2) ---")
result = svc.verify_face(dini_photo, 2, threshold=0.5)
print(f"Result: {result}")

# Also check with low threshold
result2 = svc.verify_face(dini_photo, 2, threshold=0.99)
print(f"Result (threshold=0.99): {result2}")
