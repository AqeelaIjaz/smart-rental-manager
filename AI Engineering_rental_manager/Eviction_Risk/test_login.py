"""
test_login.py
---------------
Quick sanity check: confirms we can log into the real backend and get
a valid session before running the full risk integration script.
"""

import requests

BASE_URL = "http://localhost/smart-rental-manager"

session = requests.Session()

response = session.post(f"{BASE_URL}/backend/auth/login.php", json={
    "email": "admin@example.com",
    "password": "Test12345",
})

print("Status code:", response.status_code)
print("Response:", response.json())