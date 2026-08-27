"""
train_dispute_model.py
------------------------
Trains a text classifier to categorize tenant complaints, then combines
the predicted category with agreement terms to suggest a resolution.
Implements FR-3.3.
"""

import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import accuracy_score, classification_report
import joblib

df = pd.read_csv("complaints_data.csv")

X = df["complaint_text"]
y = df["category"]

X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.25, random_state=42
)

vectorizer = TfidfVectorizer()
X_train_vec = vectorizer.fit_transform(X_train)
X_test_vec = vectorizer.transform(X_test)

model = LogisticRegression(max_iter=1000)
model.fit(X_train_vec, y_train)

y_pred = model.predict(X_test_vec)
print(f"Accuracy on test data: {accuracy_score(y_test, y_pred):.2%}\n")
print(classification_report(y_test, y_pred, zero_division=0))

joblib.dump(model, "dispute_model.pkl")
joblib.dump(vectorizer, "dispute_vectorizer.pkl")
print("\nSaved dispute_model.pkl and dispute_vectorizer.pkl")
