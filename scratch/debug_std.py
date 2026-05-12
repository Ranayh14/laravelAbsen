"""
Debug: Check if fixed_image_standardization is causing the identical outputs.
Compare raw face tensor vs standardized tensor for two different users.
"""
import sys, os
import numpy as np
import torch

SCRIPTS_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'scripts')
sys.path.insert(0, SCRIPTS_DIR)
sys.path.insert(0, os.path.join(SCRIPTS_DIR, 'facenet-master'))

from PIL import Image
from facenet_pytorch import InceptionResnetV1, MTCNN, fixed_image_standardization
import facenet_config as config
import mysql.connector

conn = mysql.connector.connect(host=config.DB_HOST, database=config.DB_NAME, user=config.DB_USER, password=config.DB_PASS)
cur = conn.cursor()
cur.execute("SELECT id, nama, foto_base64 FROM users WHERE foto_base64 IS NOT NULL ORDER BY id LIMIT 2")
users = cur.fetchall()
cur.close(); conn.close()

storage_path = os.path.join(SCRIPTS_DIR, '..', 'storage', 'app', 'public', 'users')
mtcnn = MTCNN(image_size=160, margin=32, keep_all=False, device='cpu')
model = InceptionResnetV1(pretrained='vggface2').eval()

face_tensors = []
for uid, name, foto in users:
    img = Image.open(os.path.join(storage_path, foto))
    ft = mtcnn(img)
    print(f"\n{name}:")
    print(f"  Raw face tensor: shape={ft.shape}, min={ft.min():.4f}, max={ft.max():.4f}, mean={ft.mean():.4f}")
    
    std = fixed_image_standardization(ft)
    print(f"  Standardized: min={std.min():.4f}, max={std.max():.4f}, mean={std.mean():.4f}")
    print(f"  Standardized first5 pixels: {std[0,0,0,:5].tolist()}")
    face_tensors.append((name, ft, std))

a_name, a_ft, a_std = face_tensors[0]
b_name, b_ft, b_std = face_tensors[1]
print(f"\nRaw tensor diff ({a_name} vs {b_name}): max={( a_ft-b_ft).abs().max():.4f}")
print(f"Std tensor diff ({a_name} vs {b_name}): max={(a_std-b_std).abs().max():.4f}")

# Compute embeddings WITHOUT standardization
with torch.no_grad():
    emb_a_nostd = model(a_ft.unsqueeze(0))
    emb_b_nostd = model(b_ft.unsqueeze(0))
emb_a_nostd = torch.nn.functional.normalize(emb_a_nostd, p=2, dim=1).squeeze().cpu().numpy()
emb_b_nostd = torch.nn.functional.normalize(emb_b_nostd, p=2, dim=1).squeeze().cpu().numpy()
print(f"\nWithout standardization:")
print(f"  {a_name}: first5={emb_a_nostd[:5].tolist()}")
print(f"  {b_name}: first5={emb_b_nostd[:5].tolist()}")
print(f"  Distance: {np.linalg.norm(emb_a_nostd - emb_b_nostd):.4f}")

# Compute embeddings WITH standardization
with torch.no_grad():
    emb_a_std = model(a_std.unsqueeze(0))
    emb_b_std = model(b_std.unsqueeze(0))
emb_a_std = torch.nn.functional.normalize(emb_a_std, p=2, dim=1).squeeze().cpu().numpy()
emb_b_std = torch.nn.functional.normalize(emb_b_std, p=2, dim=1).squeeze().cpu().numpy()
print(f"\nWith standardization:")
print(f"  {a_name}: first5={emb_a_std[:5].tolist()}")
print(f"  {b_name}: first5={emb_b_std[:5].tolist()}")
print(f"  Distance: {np.linalg.norm(emb_a_std - emb_b_std):.4f}")
