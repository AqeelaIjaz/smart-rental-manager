"""
app.py
-------
Flask API wrapping the eviction risk prediction model, so Member 2's
backend can call it over HTTP. Implements the integration layer for
FR-6.1-FR-6.3.

Run this file, then it stays running and listens for requests.
"""

from flask import Flask, request, jsonify
import joblib
import pandas as pd

app = Flask(__name__)

model = joblib.load("eviction_risk_model.pkl")

FEATURE_COLUMNS = [
    "late_payments_count",
    "avg_days_late",
    "complaints_filed",
    "complaints_against",
    "months_as_tenant",
    "missed_payments_count",
]


@app.route("/predict-risk", methods=["POST"])
def predict_risk():
    data = request.get_json()  # the backend will send tenant stats as JSON

    input_df = pd.DataFrame([{
        col: data.get(col, 0) for col in FEATURE_COLUMNS
    }])[FEATURE_COLUMNS]

    prediction = model.predict(input_df)[0]
    probabilities = model.predict_proba(input_df)[0]
    confidence = dict(zip(model.classes_, probabilities))

    return jsonify({
        "risk_level": prediction,
        "confidence": {k: round(float(v), 3) for k, v in confidence.items()},
    })


@app.route("/", methods=["GET"])
def home():
    return "Eviction Risk Prediction API is running. POST to /predict-risk"


if __name__ == "__main__":
    app.run(debug=True, port=5001)