"""
predict_dispute.py
--------------------
Takes a NEW complaint plus the tenant's agreement terms, predicts the
complaint category, and generates a suggested resolution. Implements
FR-3.3.
"""

import joblib

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


def suggest_resolution(complaint_text, agreement_terms):
    complaint_vec = vectorizer.transform([complaint_text])
    predicted_category = model.predict(complaint_vec)[0]

    probabilities = model.predict_proba(complaint_vec)[0]
    confidence = float(max(probabilities))

    template = RESOLUTION_TEMPLATES[predicted_category]
    try:
        suggestion = template.format(**agreement_terms)
    except KeyError:
        suggestion = template

    return {
        "category": predicted_category,
        "confidence": round(confidence, 3),
        "suggested_resolution": suggestion,
    }


if __name__ == "__main__":
    sample_agreement = {
        "rent_amount": "Rs. 35,000",
        "due_date": "5th",
        "penalty_clause": "7 days written notice required before eviction",
    }

    test_complaints = [
        "The kitchen tap is leaking and needs urgent repair",
        "Landlord is asking for more rent than what we agreed",
        "I was told to leave the house within two days",
    ]

    for complaint in test_complaints:
        result = suggest_resolution(complaint, sample_agreement)
        print(f"Complaint: {complaint}")
        print(f"-> {result}\n")