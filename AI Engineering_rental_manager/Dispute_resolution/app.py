"""
app.py
-------
Flask API wrapping the dispute resolution model. Accepts complaint
text + agreement terms, returns predicted category + suggested
resolution. Implements the integration layer for FR-3.3.
"""

from flask import Flask, request, jsonify
import joblib

app = Flask(__name__)

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


@app.route("/suggest-resolution", methods=["POST"])
def suggest_resolution():
    data = request.get_json()
    complaint_text = data.get("complaint_text", "")
    agreement_terms = data.get("agreement_terms", {})

    complaint_vec = vectorizer.transform([complaint_text])
    predicted_category = model.predict(complaint_vec)[0]

    probabilities = model.predict_proba(complaint_vec)[0]
    confidence = float(max(probabilities))

    template = RESOLUTION_TEMPLATES[predicted_category]
    try:
        suggestion = template.format(**agreement_terms)
    except KeyError:
        suggestion = template

    return jsonify({
        "category": predicted_category,
        "confidence": round(confidence, 3),
        "suggested_resolution": suggestion,
    })


@app.route("/", methods=["GET"])
def home():
    return "Dispute Resolution API is running. POST to /suggest-resolution"


if __name__ == "__main__":
    app.run(debug=True, port=5004)