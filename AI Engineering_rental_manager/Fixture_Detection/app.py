"""
app.py
-------
Flask API wrapping the fixture issue detection model. Accepts an
uploaded photo, returns issue type + priority. Implements the
integration layer for FR-4.2.
"""

from flask import Flask, request, jsonify
import json
import numpy as np
import tensorflow as tf
from tensorflow.keras.preprocessing import image as keras_image
import os

app = Flask(__name__)

IMG_SIZE = 224
CATEGORIES = ["Plumbing", "Electrical", "Structural", "Other"]

model = tf.keras.models.load_model("fixture_model.keras")

with open("category_priority.json") as f:
    CATEGORY_PRIORITY = json.load(f)


@app.route("/detect-issue", methods=["POST"])
def detect_issue():
    if "photo" not in request.files:
        return jsonify({"error": "No photo uploaded. Send file as 'photo'."}), 400

    photo = request.files["photo"]
    temp_path = "temp_upload.jpg"
    photo.save(temp_path)

    img = keras_image.load_img(temp_path, target_size=(IMG_SIZE, IMG_SIZE))
    img_array = keras_image.img_to_array(img) / 255.0
    img_array = np.expand_dims(img_array, axis=0)

    predictions = model.predict(img_array, verbose=0)[0]
    predicted_index = np.argmax(predictions)
    predicted_category = CATEGORIES[predicted_index]
    confidence = float(predictions[predicted_index])

    os.remove(temp_path)  # clean up the temp file

    return jsonify({
        "issue_type": predicted_category,
        "priority": CATEGORY_PRIORITY[predicted_category],
        "confidence": round(confidence, 3),
    })


@app.route("/", methods=["GET"])
def home():
    return "Fixture Issue Detection API is running. POST a photo to /detect-issue"


if __name__ == "__main__":
    app.run(debug=True, port=5002)