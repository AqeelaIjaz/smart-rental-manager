"""
predict_image.py
------------------
Loads the trained fixture detection model and predicts issue type +
priority for a NEW photo. Implements FR-4.2.
"""

import json
import numpy as np
import tensorflow as tf
from tensorflow.keras.preprocessing import image as keras_image

IMG_SIZE = 224
CATEGORIES = ["Plumbing", "Electrical", "Structural", "Other"]

model = tf.keras.models.load_model("fixture_model.keras")

with open("category_priority.json") as f:
    CATEGORY_PRIORITY = json.load(f)


def predict_fixture_issue(image_path):
    """
    Takes a path to a fixture photo, returns predicted issue type,
    priority level, and confidence score.
    """
    img = keras_image.load_img(image_path, target_size=(IMG_SIZE, IMG_SIZE))
    img_array = keras_image.img_to_array(img) / 255.0
    img_array = np.expand_dims(img_array, axis=0)

    predictions = model.predict(img_array, verbose=0)[0]
    predicted_index = np.argmax(predictions)
    predicted_category = CATEGORIES[predicted_index]
    confidence = float(predictions[predicted_index])

    return {
        "issue_type": predicted_category,
        "priority": CATEGORY_PRIORITY[predicted_category],
        "confidence": round(confidence, 3),
    }


if __name__ == "__main__":
    import os
    for category in CATEGORIES:
        folder = os.path.join("fixture_dataset", category)
        sample_file = os.listdir(folder)[0]
        sample_path = os.path.join(folder, sample_file)
        result = predict_fixture_issue(sample_path)
        print(f"True category: {category:12s} -> Predicted: {result}")