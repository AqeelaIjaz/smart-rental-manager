"""
generate_complaints.py
------------------------
Creates a synthetic dataset of tenant complaint texts labeled by
category. Matches FR-3.3: "System shall analyze the complaint against
agreement terms and suggest a resolution."
"""

import pandas as pd

DATA = {
    "Rent Issue": [
        "The landlord is asking for more rent than agreed in the contract",
        "I paid my rent but the landlord says he never received it",
        "Landlord increased my rent without notice",
        "I was charged a late fee even though I paid on time",
        "The rent amount charged does not match my agreement",
        "Landlord is demanding rent for a month I already paid",
        "I am being asked to pay extra maintenance charges not in my contract",
        "The security deposit was not returned after I moved out",
    ],
    "Repair Issue": [
        "The bathroom tap has been leaking for two weeks and no one fixed it",
        "There is no electricity in my kitchen since yesterday",
        "The ceiling is leaking water during rain",
        "My door lock is broken and I cannot secure my apartment",
        "The water heater stopped working and landlord is not responding",
        "There is a gas leak smell in the kitchen",
        "The window glass is broken and rain is coming inside",
        "The air conditioner has not worked for a month despite complaints",
    ],
    "Noise Complaint": [
        "My neighbor plays loud music every night until 3am",
        "There is constant noise from construction next door during night hours",
        "The upstairs tenant is making loud banging noises daily",
        "Other tenants are having loud parties every weekend disturbing sleep",
    ],
    "Eviction Threat": [
        "Landlord is threatening to evict me without proper notice",
        "I was told to vacate the house within two days with no reason given",
        "Landlord changed the locks while I was away and I could not enter",
        "I am being forced out even though my lease has six months remaining",
    ],
    "Other": [
        "I want to know the process for renewing my lease agreement",
        "Can I get a copy of my signed rental agreement",
        "I want to add a family member to my rental agreement",
        "How do I update my contact information on file",
    ],
}

rows = []
for category, examples in DATA.items():
    for text in examples:
        rows.append({"complaint_text": text, "category": category})

df = pd.DataFrame(rows)
df.to_csv("complaints_data.csv", index=False)

print(f"Saved complaints_data.csv with {len(df)} examples")
print(df["category"].value_counts())