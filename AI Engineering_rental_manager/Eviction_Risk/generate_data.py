"""
generate_data.py
-----------------
Creates a synthetic dataset simulating tenant payment/complaint history,
matching FR-6.1: "System shall analyze payment history and complaint
frequency per agreement."
"""

import numpy as np
import pandas as pd

np.random.seed(42)  # makes results reproducible - same "random" data every run

N = 600  # number of fake tenant-agreement records to generate

# --- Step 1: generate raw features ---
late_payments_count = np.random.poisson(lam=2, size=N)
avg_days_late = np.random.exponential(scale=5, size=N)
complaints_filed = np.random.poisson(lam=1, size=N)
complaints_against = np.random.poisson(lam=1.2, size=N)
months_as_tenant = np.random.randint(1, 48, size=N)
missed_payments_count = np.random.poisson(lam=0.5, size=N)

# --- Step 2: compute a "true risk score" using a formula WE define ---
risk_score = (
    late_payments_count * 1.5
    + avg_days_late * 0.4
    + complaints_against * 2.0
    - complaints_filed * 0.3
    + missed_payments_count * 4.0
    - months_as_tenant * 0.05
)

risk_score += np.random.normal(0, 2, size=N)

# --- Step 3: convert numeric risk_score into Low / Medium / High labels ---
def to_label(score):
    if score < 5:
        return "Low"
    elif score < 12:
        return "Medium"
    else:
        return "High"

risk_level = [to_label(s) for s in risk_score]

# --- Step 4: assemble into a DataFrame (table) ---
df = pd.DataFrame({
    "late_payments_count": late_payments_count,
    "avg_days_late": np.round(avg_days_late, 1),
    "complaints_filed": complaints_filed,
    "complaints_against": complaints_against,
    "months_as_tenant": months_as_tenant,
    "missed_payments_count": missed_payments_count,
    "risk_level": risk_level,
})

df.to_csv("tenant_risk_data.csv", index=False)
print("Saved tenant_risk_data.csv")
print(df.head(10))
print("\nLabel distribution:")
print(df["risk_level"].value_counts())