"""
test_api.py
------------
Quick script to test the fixture detection API by uploading an image.
"""

import requests

url = "http://127.0.0.1:5002/detect-issue"
image_path = "fixture_dataset/Electrical/electrical_000.jpg"

with open(image_path, "rb") as f:
    response = requests.post(url, files={"photo": f})

print(response.json())