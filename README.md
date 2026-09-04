# Smart Rental Manager

An AI-free, rule-based rental management web app built for Pakistan — helps
landlords and tenants manage rent agreements, complaints, repairs, payments,
and reminders, with Urdu/English voice support.

## Team

| Member  | Role                                  |
|---------|----------------------------------------|
| Aana    | Frontend / UI Development              |
| Meeral  | Backend Development                    |
| Kashaf  | Complaint & Risk Logic (voice, dispute rules, risk scoring) |
| Fatima  | Database Design                        |
| Manahil | Payments, QR & Notifications           |
| Aqeela  | Design (Figma), QA, Admin Panel & Deployment |

## Features

- Urdu/English rent agreement management
- Voice complaints (Web Speech API — no AI/ML)
- Fixture repair reporting with fixed-price cost lookup
- Rent reminders with QR receipts
- Rule-based eviction/dispute risk scoring
- Admin panel for verifying users and managing disputes

## Tech Stack

- **Frontend:** HTML, CSS, vanilla JavaScript
- **Backend:** PHP (PDO/MySQL)
- **Database:** MySQL (phpMyAdmin / XAMPP)
- **Voice-to-text:** Browser's built-in Web Speech API

## Project Structure

```
rental-app/
├── frontend/       # Tenant/landlord UI (HTML, CSS, JS)
├── backend/        # PHP API (auth, agreements, complaints, repairs, payments, risk)
├── admin/          # Admin panel
├── database/        # SQL schema
├── AI Engineering_rental_manager/   # See note below
├── postman/         # API testing collection
└── backups/
```

> **Note on `AI Engineering_rental_manager/`:** This folder contains early
> experimentation with ML models (Whisper for voice, Keras for image
> classification, scikit-learn for risk/dispute scoring). The team later
> decided to remove all AI/ML from the final product to keep the app simple
> and dependency-free. The live app uses rule-based logic and the browser's
> Web Speech API instead. This folder is kept for research reference only
> and is **not** used by the running application.

## Setup

1. Clone the repo into your XAMPP `htdocs` folder.
2. Import `database/smart_rental_manager.sql` into phpMyAdmin.
3. Copy `.env.example` to `.env` and fill in your local DB credentials.
4. Start Apache + MySQL via XAMPP.
5. Open `http://localhost/rental-app/frontend/pages/login.html` in your browser.

## Python (optional, for AI Engineering folder only)

```
pip install -r requirements.txt
```

## License

Built as a university/FYP hackathon project.
