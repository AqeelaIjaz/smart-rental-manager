"""
create_test_repair.py
------------------------
One-time helper: creates a test repair record WITH a photo attached,
so fixture_integration.py has something real to process.
"""

import requests
import os

BASE_URL = "http://localhost/smart-rental-manager"
TENANT_EMAIL = "bilal.tenant@example.com"
TENANT_PASSWORD = "Test12345"

AGREEMENT_ID = 1
TEST_PHOTO_PATH = "fixture_dataset/Electrical/electrical_000.jpg"


def main():
    if not os.path.exists(TEST_PHOTO_PATH):
        print(f"Could not find {TEST_PHOTO_PATH}. Make sure this script is in "
              f"your Fixture_Detection folder (same place as fixture_dataset/).")
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

    with open(TEST_PHOTO_PATH, "rb") as photo_file:
        resp = session.post(
            f"{BASE_URL}/backend/repairs/create.php",
            data={
                "agreement_id": AGREEMENT_ID,
                "issue_description": "Sparking sound from the kitchen socket.",
                "priority": "medium",
            },
            files={"photo": photo_file},
        )

    result = resp.json()
    if result.get("success"):
        print("Test repair created:", result["data"])
    else:
        print("FAILED to create repair:", result.get("message"))


if __name__ == "__main__":
    main()