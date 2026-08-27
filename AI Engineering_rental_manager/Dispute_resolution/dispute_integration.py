"""
dispute_integration.py
------------------------
Real integration script: connects to the actual Smart Rental Manager
backend, finds complaints that need an AI suggestion, runs our trained
model, and writes the result back via POST /backend/complaints/update.php.
"""

import requests
import joblib

BASE_URL = "http://localhost/smart-rental-manager"
ADMIN_EMAIL = "admin@example.com"
ADMIN_PASSWORD = "Test12345"

model = joblib.load("dispute_model.pkl")
vectorizer = joblib.load("dispute_vectorizer.pkl")

RESOLUTION_TEMPLATES = {
    "Rent Issue": (
        "Based on your agreement, the rent amount on file is {rent_amount} "
        "due on the {due_date} of each month. Please compare this with what "
        "was charged. If there is a mismatch, this can be escalated to the "
        "landlord for correction, or to a mediator if unresolved."
    ),
    "Repair Issue": (
        "This has been logged as a maintenance/repair issue. Under most "
        "rental agreements, the landlord is responsible for essential "
        "repairs. The landlord has been notified; if not addressed within "
        "a reasonable time, you may escalate to a mediator."
    ),
    "Noise Complaint": (
        "This has been logged as a noise disturbance complaint. We "
        "recommend first raising this directly with the landlord or "
        "building management. If it continues, this can be escalated "
        "further."
    ),
    "Eviction Threat": (
        "This is a serious matter. Under standard tenancy terms, "
        "landlords must provide proper written notice before eviction. "
        "This complaint has been flagged as high priority and escalated "
        "to a verified mediator/lawyer for review, per your agreement's "
        "penalty clause: {penalty_clause}."
    ),
    "Other": (
        "Your request has been logged. For general questions like this, "
        "please refer to your signed agreement or contact the landlord "
        "directly."
    ),
}


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


def get_complaints_needing_suggestion(session):
    resp = session.get(f"{BASE_URL}/backend/complaints/list.php")
    data = resp.json()
    if not data.get("success"):
        print("Failed to fetch complaints:", data.get("message"))
        return []

    pending = [
        c for c in data["data"]
        if c.get("complaint_text") and not c.get("ai_suggestion")
    ]
    return pending


def get_agreement_terms(session, agreement_id):
    resp = session.get(f"{BASE_URL}/backend/agreements/get.php", params={"id": agreement_id})
    data = resp.json()
    if not data.get("success"):
        return {}
    agreement = data["data"]
    return {
        "rent_amount": f"Rs. {agreement.get('rent_amount', 'N/A')}",
        "due_date": agreement.get("due_date", "N/A"),
        "penalty_clause": f"Penalty: Rs. {agreement.get('penalty', 0)}",
    }


def predict_suggestion(complaint_text, agreement_terms):
    complaint_vec = vectorizer.transform([complaint_text])
    predicted_category = model.predict(complaint_vec)[0]

    probabilities = model.predict_proba(complaint_vec)[0]
    confidence = float(max(probabilities))

    template = RESOLUTION_TEMPLATES[predicted_category]
    try:
        resolution_text = template.format(**agreement_terms)
    except KeyError:
        resolution_text = template

    combined = f"[{predicted_category}] {resolution_text}"
    return combined, confidence


def send_suggestion(session, complaint_id, ai_suggestion):
    resp = session.post(f"{BASE_URL}/backend/complaints/update.php", json={
        "complaint_id": complaint_id,
        "ai_suggestion": ai_suggestion,
        "status": "in_review",
    })
    data = resp.json()
    if data.get("success"):
        print(f"  -> Saved suggestion for complaint #{complaint_id}")
    else:
        print(f"  -> FAILED to save: {data.get('message')}")


def main():
    session = requests.Session()

    if not login(session):
        return

    complaints = get_complaints_needing_suggestion(session)
    print(f"\nFound {len(complaints)} complaint(s) needing an AI suggestion.\n")

    for complaint in complaints:
        complaint_id = complaint["id"]
        complaint_text = complaint["complaint_text"]
        agreement_id = complaint["agreement_id"]

        print(f"Complaint #{complaint_id}: \"{complaint_text}\"")

        agreement_terms = get_agreement_terms(session, agreement_id)
        suggestion, confidence = predict_suggestion(complaint_text, agreement_terms)

        print(f"  Predicted (confidence {confidence:.2f}): {suggestion}")
        send_suggestion(session, complaint_id, suggestion)

    print("\nDone.")


if __name__ == "__main__":
    main()