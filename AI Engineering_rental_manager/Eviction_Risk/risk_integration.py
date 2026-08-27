"""
risk_integration.py
---------------------
Real integration script: connects to the actual Smart Rental Manager
backend, computes eviction risk for every active agreement using our
trained model, and writes the results back via POST /backend/risk/update.php.
"""

import requests
import joblib
import pandas as pd
from datetime import datetime

BASE_URL = "http://localhost/smart-rental-manager"
ADMIN_EMAIL = "admin@example.com"
ADMIN_PASSWORD = "Test12345"

FEATURE_COLUMNS = [
    "late_payments_count",
    "avg_days_late",
    "complaints_filed",
    "complaints_against",
    "months_as_tenant",
    "missed_payments_count",
]

model = joblib.load("eviction_risk_model.pkl")


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


def get_agreements(session):
    resp = session.get(f"{BASE_URL}/backend/agreements/list.php")
    data = resp.json()
    if not data.get("success"):
        print("Failed to fetch agreements:", data.get("message"))
        return []
    return data["data"]


def get_payments(session, agreement_id):
    resp = session.get(f"{BASE_URL}/backend/payments/list.php", params={"agreement_id": agreement_id})
    data = resp.json()
    return data.get("data", []) if data.get("success") else []


def get_complaints(session, agreement_id):
    resp = session.get(f"{BASE_URL}/backend/complaints/list.php", params={"agreement_id": agreement_id})
    data = resp.json()
    return data.get("data", []) if data.get("success") else []


def compute_features(agreement, payments, complaints):
    due_day = int(agreement["due_date"].split("-")[2])

    late_count = 0
    days_late_list = []
    for p in payments:
        if p["status"] != "paid":
            continue
        payment_day = int(p["payment_date"].split(" ")[0].split("-")[2])
        delay = payment_day - due_day
        if delay > 0:
            late_count += 1
            days_late_list.append(delay)

    avg_days_late = sum(days_late_list) / len(days_late_list) if days_late_list else 0

    missed_count = sum(1 for p in payments if p["status"] == "failed")

    tenant_id = agreement["tenant_id"]
    complaints_filed = sum(1 for c in complaints if str(c["user_id"]) == str(tenant_id))
    complaints_against = sum(1 for c in complaints if str(c["user_id"]) != str(tenant_id))

    created = datetime.strptime(agreement["created_at"], "%Y-%m-%d %H:%M:%S")
    months_as_tenant = max(1, (datetime.now() - created).days // 30)

    return {
        "late_payments_count": late_count,
        "avg_days_late": avg_days_late,
        "complaints_filed": complaints_filed,
        "complaints_against": complaints_against,
        "months_as_tenant": months_as_tenant,
        "missed_payments_count": missed_count,
    }


def predict_and_format(features):
    input_df = pd.DataFrame([features])[FEATURE_COLUMNS]

    prediction = model.predict(input_df)[0]
    probabilities = model.predict_proba(input_df)[0]
    confidence = dict(zip(model.classes_, probabilities))

    band_points = {"Low": 15, "Medium": 50, "High": 85}
    score = sum(confidence.get(cls, 0) * pts for cls, pts in band_points.items())

    reason_parts = []
    if features["late_payments_count"] > 0:
        reason_parts.append(f"{features['late_payments_count']} late payment(s), averaging {features['avg_days_late']:.1f} day(s) late")
    if features["missed_payments_count"] > 0:
        reason_parts.append(f"{features['missed_payments_count']} failed payment attempt(s)")
    if features["complaints_against"] > 0:
        reason_parts.append(f"{features['complaints_against']} complaint(s) filed against the tenant")
    if not reason_parts:
        reason_parts.append("consistent, on-time payment history with no complaints")

    reason = "Risk assessed based on: " + "; ".join(reason_parts) + "."

    return {
        "risk_level": prediction.lower(),
        "score": round(float(score), 2),
        "reason": reason,
    }


def send_risk_score(session, user_id, agreement_id, result):
    resp = session.post(f"{BASE_URL}/backend/risk/update.php", json={
        "user_id": user_id,
        "agreement_id": agreement_id,
        "risk_level": result["risk_level"],
        "score": result["score"],
        "reason": result["reason"],
    })
    data = resp.json()
    if data.get("success"):
        print(f"  -> Saved: {result['risk_level']} (score {result['score']}) - {result['reason']}")
    else:
        print(f"  -> FAILED to save: {data.get('message')}")


def main():
    session = requests.Session()

    if not login(session):
        return

    agreements = get_agreements(session)
    print(f"\nFound {len(agreements)} agreement(s) to process.\n")

    for agreement in agreements:
        agreement_id = agreement["id"]
        tenant_id = agreement["tenant_id"]
        print(f"Agreement #{agreement_id} (tenant #{tenant_id}):")

        payments = get_payments(session, agreement_id)
        complaints = get_complaints(session, agreement_id)

        features = compute_features(agreement, payments, complaints)
        result = predict_and_format(features)

        send_risk_score(session, tenant_id, agreement_id, result)

    print("\nDone.")


if __name__ == "__main__":
    main()