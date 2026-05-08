import torch
import os
import numpy as np
from facenet_pytorch import InceptionResnetV1, MTCNN, fixed_image_standardization
from PIL import Image

def inspect_model():
    model_path = r'd:\xampp\htdocs\Magang\LaravelAbsen\scripts\facenet-master\models\facenet_20180402_114759_vggface2.pth'
    print(f"Inspecting: {model_path}")
    
    model = InceptionResnetV1(pretrained=None).eval()
    model_keys = set(model.state_dict().keys())
    
    try:
        state_dict = torch.load(model_path, map_location='cpu')
        sd_keys = set(state_dict.keys())
        
        common_keys = model_keys.intersection(sd_keys)
        missing_keys = model_keys - sd_keys
        unexpected_keys = sd_keys - model_keys
        
        print(f"Total model keys: {len(model_keys)}")
        print(f"Total state_dict keys: {len(sd_keys)}")
        print(f"Common keys: {len(common_keys)}")
        print(f"Missing keys: {len(missing_keys)}")
        
        if len(common_keys) == 0:
            print("CRITICAL: ZERO keys match! Weights are NOT being loaded.")
            print(f"Example model key: {list(model_keys)[0]}")
            print(f"Example state_dict key: {list(sd_keys)[0]}")
        
    except Exception as e:
        print(f"Error: {str(e)}")

def self_test_ai():
    # Gunakan gambar yang sama untuk membandingkan embedding
    model_path = r'd:\xampp\htdocs\Magang\LaravelAbsen\scripts\facenet-master\models\facenet_20180402_114759_vggface2.pth'
    
    # Inisialisasi model
    model = InceptionResnetV1(pretrained=None).eval()
    state_dict = torch.load(model_path, map_location='cpu')
    
    # Bersihkan keys seperti di facenet_service.py
    keys_to_remove = ["logits.weight", "logits.bias", "last_linear.weight", "last_linear.bias", "last_bn.weight", "last_bn.bias"]
    for key in keys_to_remove:
        if key in state_dict:
            del state_dict[key]
    
    model.load_state_dict(state_dict, strict=False)
    
    # Buat tensor acak sebagai simulasi gambar wajah
    dummy_face = torch.randn(1, 3, 160, 160)
    
    with torch.no_grad():
        emb1 = model(dummy_face)
        emb2 = model(dummy_face) # Harus identik
        
    # Normalisasi
    emb1 = torch.nn.functional.normalize(emb1, p=2, dim=1)
    emb2 = torch.nn.functional.normalize(emb2, p=2, dim=1)
    
    dist = torch.dist(emb1, emb2).item()
    print(f"Self-test distance (same tensor): {dist:.4f}")
    
    if dist > 0.001:
        print("CRITICAL ERROR: AI model is non-deterministic or broken!")
    else:
        print("✓ AI model engine is deterministic.")

if __name__ == "__main__":
    inspect_model()
    self_test_ai()
