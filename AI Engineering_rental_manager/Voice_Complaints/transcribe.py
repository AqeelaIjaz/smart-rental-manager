"""
transcribe.py
--------------
Converts a voice complaint audio file into text using Whisper.
Implements FR-3.2.
"""

import whisper

model = whisper.load_model("small")

AUDIO_FILE = "test_audio1.mp3"

print(f"Transcribing {AUDIO_FILE} ...")
result = model.transcribe(AUDIO_FILE, language="ur")  # force Urdu

print("\n--- Transcription ---")
print(result["text"])

print("\n--- Detected language ---")
print(result["language"])