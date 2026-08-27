"""
fixture_integration.py
-------------------------
Real integration script: connects to the actual Smart Rental Manager
backend, finds repair reports that have a photo attached, runs our
trained image model on that photo, and updates the repair's priority
level via POST /backend/repairs/update.php.
"""

import requests
import json
import numpy as np
import tensorflow as tf
from tensorflow.keras.preprocessing import image as keras_image
import os

BASE_URL = "http://localhost/smart-rental-manager"
ADMIN_EMAIL = "admin@example.com"
ADMIN_PASSWORD = "Test12345"

IMG_SIZE = 224
CATEGORIES = ["Plumbing", "Electrical", "Structural", "Other"]

model = tf.keras.models.load_model("fixture_model.keras")

with open("category_priority.json") as f:
    CATEGORY_PRIORITY = json.load(f)


def login(session):
    resp = session.post(f"{BASE_URL}/backend/auth/login.php", json={
        "email": ADMIN_EMAIL,
        "password": ADMIN_PASSWORD,
    })
    data = resp.json()
    if not data.get("success"):
        print("LOGIN FAILED:", data.get("message"))
        return False
    print(f"Logged in as {data['data']['name']} (role: {data['data']['role']})")
    return True


def get_repairs_with_photos(session):
    resp = session.get(f"{BASE_URL}/backend/repairs/list.php")
    data = resp.json()
    if not data.get("success"):
        print("Failed to fetch repairs:", data.get("message"))
        return []
    return [r for r in data["data"] if r.get("photo")]


def download_photo(session, filename):
    photo_url = f"{BASE_URL}/backend/uploads/repairs/{filename}"
    resp = session.get(photo_url)
    if resp.status_code != 200:
        return None
    local_path = "temp_repair_photo.jpg"
    with open(local_path, "wb") as f:
        f.write(resp.content)
    return local_path


def detect_issue(photo_path):
    img = keras_image.load_img(photo_path, target_size=(IMG_SIZE, IMG_SIZE))
    img_array = keras_image.img_to_array(img) / 255.0
    img_array = np.expand_dims(img_array, axis=0)

    predictions = model.predict(img_array, verbose=0)[0]
    predicted_index = np.argmax(predictions)
    predicted_category = CATEGORIES[predicted_index]
    confidence = float(predictions[predicted_index])

    priority = CATEGORY_PRIORITY[predicted_category].lower()
    if predicted_category in ("Electrical", "Structural") and confidence > 0.9:
        priority = "urgent"

    return predicted_category, priority, confidence


def update_repair_priority(session, repair_id, priority):
    resp = session.post(f"{BASE_URL}/backend/repairs/update.php", json={
        "id": repair_id,
        "priority": priority,
    })
    data = resp.json()
    if data.get("success"):
        print(f"  -> Updated repair #{repair_id} priority to '{priority}'")
    else:
        print(f"  -> FAILED to update: {data.get('message')}")


def main():
    session = requests.Session()

    if not login(session):
        return

    repairs = get_repairs_with_photos(session)
    print(f"\nFound {len(repairs)} repair(s) with a photo attached.\n")

    for repair in repairs:
        repair_id = repair["id"]
        photo_filename = repair["photo"]
        print(f"Repair #{repair_id} (photo: {photo_filename}):")

        local_path = download_photo(session, photo_filename)
        if not local_path:
            print("  -> Could not download photo, skipping.")
            continue

        category, priority, confidence = detect_issue(local_path)
        print(f"  Detected: {category} (confidence {confidence:.2f}) -> priority: {priority}")

        update_repair_priority(session, repair_id, priority)

        os.remove(local_path)

    print("\nDone.")


if __name__ == "__main__":
    main()