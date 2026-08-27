"""
app.py
-------
Flask API wrapping Whisper for voice complaint transcription.
Accepts an uploaded audio file, returns the transcribed text.
Implements the integration layer for FR-3.2.
"""

from flask import Flask, request, jsonify
import whisper
import os

app = Flask(__name__)

model = whisper.load_model("small")


@app.route("/transcribe", methods=["POST"])
def transcribe():
    if "audio" not in request.files:
        return jsonify({"error": "No audio uploaded. Send file as 'audio'."}), 400

    audio_file = request.files["audio"]
    temp_path = "temp_audio.mp3"
    audio_file.save(temp_path)

    # language="ur" forces Urdu, since that's the SRS's target language.
    # Remove this argument if you want auto-detection instead.
    result = model.transcribe(temp_path, language="ur")

    os.remove(temp_path)

    return jsonify({
        "transcript": result["text"],
        "language": result["language"],
    })


@app.route("/", methods=["GET"])
def home():
    return "Voice Transcription API is running. POST audio to /transcribe"


if __name__ == "__main__":
    app.run(debug=True, port=5003)