"""
test_api.py
------------
Quick script to test the voice transcription API by uploading an audio file.
"""

import requests

url = "http://127.0.0.1:5003/transcribe"
audio_path = "test_audio1.mp3"  # your Urdu recording

with open(audio_path, "rb") as f:
    response = requests.post(url, files={"audio": f})

print(response.json())