"""
Diagnose: Is the model producing non-trivial output?
"""
import sys, os
import numpy as np
import torch

SCRIPTS_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'scripts')
sys.path.insert(0, SCRIPTS_DIR)
sys.path.insert(0, os.path.join(SCRIPTS_DIR, 'facenet-master'))

from facenet_pytorch import InceptionResnetV1

model = InceptionResnetV1(pretrained='vggface2').eval()

print("Model output variance test:")
for i in range(3):
    # Completely random input each time
    x = torch.randn(1, 3, 160, 160)
    with torch.no_grad():
        out = model(x)
    out_norm = torch.nn.functional.normalize(out, p=2, dim=1).squeeze()
    print(f"  Random input {i+1}: first5={out_norm[:5].tolist()}")

print("\nConclusion: if all outputs are identical -> model is broken/frozen")

# Check if model parameters are all zero or constant
params = list(model.parameters())
print(f"\nTotal param tensors: {len(params)}")
print(f"First param sum: {params[0].sum().item():.4f}")
print(f"First param std: {params[0].std().item():.4f}")
print(f"Last param sum: {params[-1].sum().item():.4f}")

# Check cached pretrained file
import torch.utils.model_zoo as zoo
cache_dir = os.path.expanduser('~/.cache/torch/hub/checkpoints/')
print(f"\nCached model files:")
if os.path.exists(cache_dir):
    for f in os.listdir(cache_dir):
        fp = os.path.join(cache_dir, f)
        print(f"  {f} ({os.path.getsize(fp) // 1024} KB)")
else:
    print("  (cache dir not found)")
