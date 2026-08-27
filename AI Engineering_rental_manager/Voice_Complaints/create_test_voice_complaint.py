"""
create_test_voice_complaint.py
---------------------------------
One-time helper: creates a test complaint WITH a voice recording
attached, so voice_dispute_integration.py has something real to
process.
"""

import requests
import os

BASE_URL = "http://localhost/smart-rental-manager"
TENANT_EMAIL = "bilal.tenant@example.com"
TENANT_PASSWORD = "Test12345"

AGREEMENT_ID = 1
# Point this at one of your recorded audio files from the Voice_Complaints folder.
# Copy that file into this folder first, or give its full path here.
TEST_AUDIO_PATH = "test_audio1.mp3"


def main():
    if not os.path.exists(TEST_AUDIO_PATH):
        print(f"Could not find {TEST_AUDIO_PATH}. Copy your test audio file "
              f"into this folder first (or update the path).")
        return

    session = requests.Session()

    resp = session.post(f"{BASE_URL}/backend/auth/login.php", json={
        "email": TENANT_EMAIL,
        "password": TENANT_PASSWORD,
    })
    data = resp.json()
    if not data.get("success"):
        print("LOGIN FAILED:", data.get("message"))
        return
    print(f"Logged in as {data['data']['name']} (role: {data['data']['role']})")

    with open(TEST_AUDIO_PATH, "rb") as audio_file:
        resp = session.post(
            f"{BASE_URL}/backend/complaints/create.php",
            data={
                "agreement_id": AGREEMENT_ID,
                "complaint_text": "Voice complaint attached",
            },
            files={"voice_file": audio_file},
        )

    result = resp.json()
    if result.get("success"):
        print("Test voice complaint created:", result["data"])
    else:
        print("FAILED to create complaint:", result.get("message"))


if __name__ == "__main__":
    main()