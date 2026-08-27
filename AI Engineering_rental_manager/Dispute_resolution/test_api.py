"""
test_api.py
------------
Quick script to test the dispute resolution API.
"""

import requests

url = "http://127.0.0.1:5004/suggest-resolution"

payload = {
    "complaint_text": "The kitchen tap is leaking and needs urgent repair",
    "agreement_terms": {
        "rent_amount": "Rs. 35,000",
        "due_date": "5th",
        "penalty_clause": "7 days written notice required before eviction",
    },
}

response = requests.post(url, json=payload)
print(response.json())