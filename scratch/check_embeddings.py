"""
Check: Are Rana's and Dini's stored embeddings actually different?
"""
import sys, os, json, numpy as np
import mysql.connector

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..', 'scripts'))
import facenet_config as config

conn = mysql.connector.connect(
    host=config.DB_HOST, database=config.DB_NAME,
    user=config.DB_USER, password=config.DB_PASS
)
cur = conn.cursor()

cur.execute("SELECT id, nama, face_embedding FROM users WHERE face_embedding IS NOT NULL ORDER BY id LIMIT 5")
rows = cur.fetchall()
embeddings = {}
for uid, name, emb_json in rows:
    arr = np.array(json.loads(emb_json))
    embeddings[uid] = (name, arr)
    print(f"ID={uid} {name}: dim={len(arr)}, first5={arr[:5].tolist()}")

print("\n--- Pairwise distances ---")
ids = list(embeddings.keys())
for i in range(len(ids)):
    for j in range(i+1, len(ids)):
        a_id, b_id = ids[i], ids[j]
        a_name, a_emb = embeddings[a_id]
        b_name, b_emb = embeddings[b_id]
        dist = np.linalg.norm(a_emb - b_emb)
        print(f"{a_name} vs {b_name}: dist={dist:.4f}")

cur.close()
conn.close()
