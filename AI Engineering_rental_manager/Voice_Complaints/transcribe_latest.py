"""
transcribe_latest.py
"""

import whisper
import os
import glob

AUDIO_EXTENSIONS = ("*.mp3", "*.wav", "*.m4a", "*.ogg", "*.webm")


def find_latest_audio_file():
    all_audio_files = []
    for pattern in AUDIO_EXTENSIONS:
        all_audio_files.extend(glob.glob(pattern))

    if not all_audio_files:
        return None

    latest_file = max(all_audio_files, key=os.path.getmtime)
    return latest_file


def main():
    audio_file = find_latest_audio_file()

    if audio_file is None:
        print("No audio files found in this folder. Add a .mp3/.wav/.m4a/.ogg file first.")
        return

    print(f"Using most recent audio file: {audio_file}")

    model = whisper.load_model("small")

    print(f"Transcribing {audio_file} ...")
    result = model.transcribe(audio_file)  # normal auto-detect, no forcing

    detected_language = result["language"]
    if detected_language == "hi":
        print("Detected as Hindi, but this is likely Urdu (common mix-up) - retrying as Urdu...")
        result = model.transcribe(audio_file, language="ur")
        detected_language = "ur"

    print("\n--- Transcription ---")
    print(result["text"])

    print("\n--- Detected language ---")
    print(detected_language)


if __name__ == "__main__":
    main()