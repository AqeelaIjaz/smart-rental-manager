"""
predict.py
----------
Loads the trained model and predicts risk level for a NEW tenant record.
This is the function Member 2 (backend) will call later - wrap it in a
Flask/FastAPI endpoint (e.g. POST /predict-risk) during integration.
"""

import joblib
import pandas as pd

model = joblib.load("eviction_risk_model.pkl")

FEATURE_COLUMNS = [
    "late_payments_count",
    "avg_days_late",
    "complaints_filed",
    "complaints_against",
    "months_as_tenant",
    "missed_payments_count",
]


def predict_risk(late_payments_count, avg_days_late, complaints_filed,
                  complaints_against, months_as_tenant, missed_payments_count):
    """
    Takes a tenant's stats and returns predicted risk level + confidence.
    Matches FR-6.2: "generate a risk level (Low/Medium/High) for
    each active agreement."
    """
    input_df = pd.DataFrame([{
        "late_payments_count": late_payments_count,
        "avg_days_late": avg_days_late,
        "complaints_filed": complaints_filed,
        "complaints_against": complaints_against,
        "months_as_tenant": months_as_tenant,
        "missed_payments_count": missed_payments_count,
    }])[FEATURE_COLUMNS]

    prediction = model.predict(input_df)[0]

    # confidence scores for each class - useful for FR-6.3
    # ("notify when risk crosses a warning threshold")
    probabilities = model.predict_proba(input_df)[0]
    confidence = dict(zip(model.classes_, probabilities))

    return {
        "risk_level": prediction,
        "confidence": {k: round(float(v), 3) for k, v in confidence.items()},
    }


if __name__ == "__main__":
    # Example 1: a tenant with a clean history
    result1 = predict_risk(
        late_payments_count=0, avg_days_late=0, complaints_filed=1,
        complaints_against=0, months_as_tenant=18, missed_payments_count=0
    )
    print("Clean history tenant ->", result1)

    # Example 2: a tenant with a troubling history
    result2 = predict_risk(
        late_payments_count=5, avg_days_late=15, complaints_filed=0,
        complaints_against=4, months_as_tenant=3, missed_payments_count=2
    )
    print("Troubling history tenant ->", result2)