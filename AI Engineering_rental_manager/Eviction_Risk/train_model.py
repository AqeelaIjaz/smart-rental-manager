"""
train_model.py
---------------
Trains a classifier to predict eviction risk (Low/Medium/High) from
tenant payment & complaint history. Implements FR-6.1 and FR-6.2.
"""

import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.tree import DecisionTreeClassifier
from sklearn.metrics import accuracy_score, classification_report, confusion_matrix
import joblib  # for saving the trained model to a file

# --- Step 1: Load the data ---
df = pd.read_csv("tenant_risk_data.csv")

# --- Step 2: Split into FEATURES (X) and LABEL (y) ---
feature_columns = [
    "late_payments_count",
    "avg_days_late",
    "complaints_filed",
    "complaints_against",
    "months_as_tenant",
    "missed_payments_count",
]
X = df[feature_columns]
y = df["risk_level"]

# --- Step 3: Train/Test split ---
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

print(f"Training on {len(X_train)} records, testing on {len(X_test)} records\n")

# --- Step 4: Train the model ---
model = DecisionTreeClassifier(max_depth=4, random_state=42)
model.fit(X_train, y_train)

# --- Step 5: Evaluate on the TEST set (data the model has never seen) ---
y_pred = model.predict(X_test)

accuracy = accuracy_score(y_test, y_pred)
print(f"Accuracy on test data: {accuracy:.2%}\n")

print("Detailed report (precision/recall per class):")
print(classification_report(y_test, y_pred))

print("Confusion matrix (rows=actual, columns=predicted):")
print(confusion_matrix(y_test, y_pred, labels=["Low", "Medium", "High"]))

# --- Step 6: Look at which features mattered most ---
importances = pd.Series(model.feature_importances_, index=feature_columns)
print("\nFeature importance (higher = more influence on prediction):")
print(importances.sort_values(ascending=False))

# --- Step 7: Save the trained model to a file ---
joblib.dump(model, "eviction_risk_model.pkl")
print("\nModel saved to eviction_risk_model.pkl")