Smart Rental Manager — Backend
Member 2 — Backend Developer
Backend API for the Smart Rental Manager, a rental-management application built with PHP, MySQL, PDO, and XAMPP.
The backend is developed independently from the frontend. Frontend developers can connect to these JSON APIs later using JavaScript fetch().
Frontend (HTML/CSS/JavaScript)
              │
              ▼
        JavaScript Fetch API
              │
              ▼
          PHP REST APIs
              │
              ▼
             MySQL

1. Technologies
• PHP 8+
• MySQL
• PDO
• XAMPP / Apache
• phpMyAdmin
• Postman
• JSON APIs
• PHP Sessions for authentication

2. Features
The backend provides:
• Tenant and landlord registration
• Tenant/landlord login and logout
• Separate admin authentication
• Admin account management
• Forgot-password OTP system
• Password reset
• Rental agreement management
• Agreement document uploads
• Complaint management
• Voice complaint uploads
• AI suggestion write-back
• Repair management
• Repair photo uploads
• Payment records
• Payment status management
• Payment receipt data
• In-app notifications
• AI risk-score storage and retrieval
• Automatic rent-reminder logic
• Secure file-upload validation
• Session-based authentication
• Role and ownership authorization
• Urdu and Roman Urdu text support

3. Project Structure
smart-rental-manager/
│
├── backend/
│   │
│   ├── config/
│   │   ├── database.php
│   │   └── app.php
│   │
│   ├── middleware/
│   │   └── auth.php
│   │
│   ├── helpers/
│   │   ├── response.php
│   │   ├── validation.php
│   │   ├── upload.php
│   │   └── mailer.php
│   │
│   ├── auth/
│   │   ├── signup.php
│   │   ├── login.php
│   │   ├── logout.php
│   │   ├── admin-login.php
│   │   ├── forgot-password.php
│   │   └── reset-password.php
│   │
│   ├── admin/
│   │   ├── create-admin.php
│   │   ├── list-admins.php
│   │   └── update-status.php
│   │
│   ├── agreements/
│   │   ├── create.php
│   │   ├── upload.php
│   │   ├── list.php
│   │   ├── get.php
│   │   └── update.php
│   │
│   ├── complaints/
│   │   ├── create.php
│   │   ├── list.php
│   │   ├── get.php
│   │   └── update.php
│   │
│   ├── repairs/
│   │   ├── create.php
│   │   ├── upload.php
│   │   ├── list.php
│   │   ├── get.php
│   │   └── update.php
│   │
│   ├── payments/
│   │   ├── create.php
│   │   ├── list.php
│   │   ├── get.php
│   │   ├── receipt.php
│   │   └── payment-status.php
│   │
│   ├── notifications/
│   │   ├── create.php
│   │   ├── list.php
│   │   └── mark-read.php
│   │
│   ├── risk/
│   │   ├── update.php
│   │   └── get.php
│   │
│   ├── cron/
│   │   └── rent-reminder.php
│   │
│   ├── uploads/
│   │   ├── agreements/
│   │   ├── complaints/
│   │   ├── repairs/
│   │   ├── voice/
│   │   └── .htaccess
│   │
│   ├── demo_seed.php
│   └── .htaccess
│
├── database/
│   └── smart-rental-manager.sql
│
├── postman/
│   └── Smart-Rental-Manager-API.json
│
├── index.php
└── README.md

4. Installation
Step 1 — Install XAMPP
Install XAMPP with:
• Apache
• MySQL
• PHP
• phpMyAdmin
Open the XAMPP Control Panel and start:
Apache
MySQL

Step 2 — Copy the Project
Copy the complete project folder to:
C:\xampp\htdocs\smart-rental-manager

Step 3 — Create the Database
Open:
http://localhost/phpmyadmin
Create a database named:
smart_rental_manager
Use:
utf8mb4
for proper support of English, Urdu, and Roman Urdu text.

Step 4 — Import the SQL File
Select:
smart_rental_manager
Then:
Import → Choose File
Select:
database/smart-rental-manager.sql
Then click Import.
The database contains 9 tables:
1. users
2. admins
3. password_resets
4. agreements
5. complaints
6. repairs
7. payments
8. risk_scores
9. notifications

Step 5 — Configure Database Connection
Open:
backend/config/database.php
Check the database settings.
Typical XAMPP settings are:
Host: localhost
Database: smart_rental_manager
Username: root
Password: empty
If your MySQL password is different, update the configuration accordingly.

5. Run the Backend
Open:
http://localhost/smart-rental-manager/
The backend root should return JSON confirming that the backend is running.
Example:
{
    "project": "Smart Rental Manager",
    "status": "Backend running"
}

6. Demo Accounts
All demo accounts use:
Password: Test12345
Admin
Email: admin@example.com
Password: Test12345
Landlords
ali.landlord@example.com
sara.landlord@example.com
Tenants
bilal.tenant@example.com
ayesha.tenant@example.com
usman.tenant@example.com
These accounts are for development/testing only.
Do not use these credentials in production.

7. Demo Password Seeder
If the demo passwords do not work after importing the database, run:
http://localhost/smart-rental-manager/backend/demo_seed.php
The seeder regenerates the demo password hashes using PHP's:
password_hash()
After running it, the demo accounts use:
Test12345

8. Authentication
The backend uses PHP sessions and cookies.
After successful login, PHP creates a session containing the authenticated user's information.
For browser-based frontend requests, use:
credentials: 'include'
Example:
fetch('http://localhost/smart-rental-manager/backend/auth/login.php', {
    method: 'POST',
    credentials: 'include',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        email: 'bilal.tenant@example.com',
        password: 'Test12345'
    })
});

9. API Endpoints
All APIs return JSON.
Successful response:
{
    "success": true,
    "message": "Operation successful.",
    "data": {}
}
Error response:
{
    "success": false,
    "message": "Something went wrong.",
    "data": {}
}
Authentication
MethodEndpointPurposePOST/backend/auth/signup.phpRegister tenant/landlordPOST/backend/auth/login.phpLogin tenant/landlordPOST/backend/auth/logout.phpLogoutPOST/backend/auth/admin-login.phpAdmin loginPOST/backend/auth/forgot-password.phpRequest OTPPOST/backend/auth/reset-password.phpReset password
Admin Management
MethodEndpointPurposePOST/backend/admin/create-admin.phpCreate adminGET/backend/admin/list-admins.phpList adminsPOST/backend/admin/update-status.phpActivate/deactivate adminAdmin-management endpoints require an authenticated admin.

Agreements
MethodEndpointPurposePOST/backend/agreements/create.phpCreate agreementPOST/backend/agreements/upload.phpUpload agreement documentGET/backend/agreements/list.phpList agreementsGET/backend/agreements/get.php?id=Get agreementPOST/backend/agreements/update.phpUpdate agreementSupported agreement documents:
PDF
DOC
DOCX

Complaints
MethodEndpointPurposePOST/backend/complaints/create.phpCreate complaintGET/backend/complaints/list.phpList complaintsGET/backend/complaints/get.php?id=Get complaintPOST/backend/complaints/update.phpUpdate complaint/AI suggestionComplaints can contain:
• Text
• Optional voice recording
• AI-generated suggestion
• Status

Repairs
MethodEndpointPurposePOST/backend/repairs/create.phpCreate repairPOST/backend/repairs/upload.phpUpload repair photoGET/backend/repairs/list.phpList repairsGET/backend/repairs/get.php?id=Get repairPOST/backend/repairs/update.phpUpdate repairSupported repair photos:
JPG
JPEG
PNG
WEBP

Payments
MethodEndpointPurposePOST/backend/payments/create.phpRecord paymentGET/backend/payments/list.phpList paymentsGET/backend/payments/get.php?id=Get paymentGET/backend/payments/receipt.php?id=Get receipt dataPOST/backend/payments/payment-status.phpUpdate payment statusThe current system is a prototype and does not connect to a real payment gateway.

Notifications
MethodEndpointPurposePOST/backend/notifications/create.phpCreate notificationGET/backend/notifications/list.phpList current user's notificationsPOST/backend/notifications/mark-read.phpMark notification as read
Risk Scores
MethodEndpointPurposePOST/backend/risk/update.phpStore AI risk scoreGET/backend/risk/get.phpRetrieve risk scoreThe backend does not calculate the risk score.
Member 3's AI system calculates:
risk_level
score
reason
and sends the result to:
POST /backend/risk/update.php

Rent Reminder
MethodEndpointPurposeGET/backend/cron/rent-reminder.phpCheck upcoming rent due datesThe script checks active agreements and creates a notification when rent is 5 days away.
It also prevents duplicate reminders.
The script can be executed manually for testing or scheduled using Windows Task Scheduler.

10. File Upload Security
Uploaded files are handled through:
backend/helpers/upload.php
The system:
• Checks upload errors
• Checks file size
• Uses finfo to detect the actual MIME type
• Does not trust the client-provided MIME type
• Uses a whitelist of allowed file types
• Generates random filenames
• Does not reuse the original filename
• Prevents path traversal
• Stores files only inside approved upload directories
Upload directories contain:
backend/uploads/
├── agreements/
├── complaints/
├── repairs/
└── voice/
The uploads directory also contains an .htaccess file that prevents execution of PHP and other server-side script files.
Directory listing is disabled.

11. Password Security
Passwords are never stored as plain text.
The backend uses:
password_hash()
for storing passwords and:
password_verify()
for login verification.
Sessions are regenerated after successful login using:
session_regenerate_id(true);
This helps prevent session fixation.

12. Admin System
Admins are stored separately from tenants and landlords.
users
 ├── tenant
 └── landlord

admins
 └── admin
An existing administrator can:
• Create another admin
• View all admins
• Activate an admin
• Deactivate an admin
An admin cannot deactivate their own account.
Inactive admins are blocked from logging in.

13. Password Reset / OTP
The password-reset system supports:
Tenant
Landlord
Admin
The OTP information is stored in:
password_resets
The reset flow is:
Forgot Password
       ↓
Generate OTP
       ↓
Verify OTP
       ↓
Set New Password
       ↓
Login With New Password
Email status
The current mailer.php is an integration point for real email delivery.
It does not currently send real Gmail/SMTP emails.
During development, the OTP can be exposed through the development reset flow so the complete reset process can be tested without configuring an external email service.
Before real deployment, a real SMTP/PHPMailer configuration should be added and development OTP exposure should be disabled.

14. Database
The database schema is located at:
database/smart-rental-manager.sql
The database contains these 9 tables:
users
Stores:
• Tenants
• Landlords
The role column distinguishes between:
tenant
landlord
Admins are stored separately in the admins table.
admins
Stores administrator accounts and their status.
password_resets
Stores password-reset OTP requests for users and admins.
agreements
Stores rental agreements between landlords and tenants.
complaints
Stores complaints, optional voice files, and AI suggestions.
repairs
Stores repair requests, priority, status, estimated cost, and optional photos.
payments
Stores payment records and payment status.
risk_scores
Stores AI-generated risk assessments.
notifications
Stores in-app notifications.

15. Database Relationships
users
 │
 ├───────────────► agreements
 │                     │
 │                     ├──► complaints
 │                     ├──► repairs
 │                     ├──► payments
 │                     └──► risk_scores
 │
 ├───────────────► complaints
 ├───────────────► repairs
 ├───────────────► payments
 ├───────────────► risk_scores
 └───────────────► notifications

admins
 │
 └── Independent admin authentication/management

password_resets
 │
 └── Supports users and admins
Foreign keys and indexes are defined in the SQL schema.
The database uses:
InnoDB
utf8mb4
utf8mb4_unicode_ci
to support multilingual text including Urdu and Roman Urdu.

16. Postman Testing
A complete Postman collection is provided:
postman/Smart-Rental-Manager-API.json
Import it into Postman:
Postman
   ↓
Import
   ↓
Smart-Rental-Manager-API.json
The default base URL is:
http://localhost/smart-rental-manager
Postman's cookie jar stores the PHP session cookie after login and reuses it for authenticated requests.
Important
The Postman collection is provided for:
• API testing
• Demonstration
• Documentation
• Repeatable manual testing
It is not required by the backend at runtime.
The PHP API files inside backend/ are the actual backend implementation.

17. Frontend Integration
The frontend can communicate with the backend using JavaScript Fetch API.
Example:
fetch('http://localhost/smart-rental-manager/backend/agreements/list.php', {
    credentials: 'include'
})
.then(response => response.json())
.then(data => console.log(data));
For JSON POST requests:
fetch('http://localhost/smart-rental-manager/backend/auth/login.php', {
    method: 'POST',
    credentials: 'include',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        email: 'bilal.tenant@example.com',
        password: 'Test12345'
    })
})
.then(response => response.json())
.then(data => console.log(data));
For file uploads, use FormData and do not manually set the Content-Type header.

18. AI Integration — Member 3
The backend provides storage endpoints for AI-generated results.
Complaint AI Suggestion
Member 3 can send the generated suggestion to:
POST /backend/complaints/update.php
Example:
{
    "complaint_id": 5,
    "ai_suggestion": "Schedule a technician visit.",
    "status": "in_review"
}
Risk Prediction
Member 3 can send the prediction to:
POST /backend/risk/update.php
Example:
{
    "user_id": 4,
    "agreement_id": 1,
    "risk_level": "low",
    "score": 12.5,
    "reason": "Consistent payment history."
}
The backend only stores the result.
It does not generate or simulate AI predictions.
OCR
The agreements.extracted_text column is reserved for agreement OCR/text-extraction results.

19. Payment / Notification Integration — Member 5
The backend provides the basic payment and notification infrastructure.
Payment
Member 5 can use:
/backend/payments/payment-status.php
to update payment status.
Supported statuses:
pending
paid
failed
QR Receipt
The payments.qr_receipt column is reserved for the QR receipt image/path.
Notifications
In-app notifications can be created using:
/backend/notifications/create.php
Rent Reminder
The reusable reminder function is located in:
/backend/cron/rent-reminder.php
It creates rent_reminder notifications five days before the projected monthly due date.

20. Backend Testing Status
The backend was tested using a live XAMPP + PHP + MySQL environment.
Tested areas include:
• Database connection
• Signup
• Login
• Logout
• Session authentication
• Admin login
• Admin creation
• Admin listing
• Admin activation/deactivation
• Self-deactivation protection
• Forgot-password flow
• OTP validation
• OTP reuse protection
• Password reset
• Agreement CRUD
• Agreement ownership checks
• Agreement file upload
• Complaint CRUD
• Voice complaint upload
• AI suggestion write-back
• Repair CRUD
• Repair photo upload
• Payment CRUD
• Payment status
• Payment receipt
• Notifications
• Risk score update
• Risk score retrieval
• Rent reminder logic
• Role restrictions
• Ownership restrictions
• File MIME validation
• Urdu/Roman Urdu text handling
The API responses were verified through Postman during development.

21. Security
The backend includes several security measures:
SQL Injection Protection
Database queries use PDO prepared statements.
Password Protection
Passwords use:
password_hash()
password_verify()
Session Security
Sessions use HTTP-only cookies and session ID regeneration after login.
Authorization
Endpoints check:
• Authentication
• User role
• Agreement ownership
• Agreement participation
• Admin privileges
File Upload Security
Uploaded files are:
• MIME validated
• Size checked
• Renamed randomly
• Stored in controlled directories
• Protected using .htaccess
Error Handling
Internal database errors are logged server-side while users receive generic error messages.

22. CORS
During local development, the backend may allow cross-origin requests for frontend testing.
Before production deployment, CORS should be restricted to the actual frontend origin.
The backend should also be deployed over HTTPS in a production environment.

23. Project Responsibilities
Member 1 — Frontend
Responsible for:
• HTML
• CSS
• JavaScript
• UI screens
• Fetch API integration
Member 2 — Backend
Responsible for:
• PHP APIs
• Authentication
• Sessions
• Authorization
• CRUD operations
• File uploads
• Notifications
• Risk storage
• Rent reminders
• API testing
Member 3 — AI/NLP
Responsible for:
• Speech-to-text
• OCR
• AI complaint suggestions
• Risk prediction
Member 4 — Database
Responsible for:
• Database review
• ERD
• Database refinements
• Index/relationship improvements
Member 5 — Payments/Notifications
Responsible for:
• QR functionality
• Payment-related integration
• External notification services
Member 6 — Design/QA/Deployment
Responsible for:
• UI/UX
• Broader QA
• Deployment
• Production configuration

24. Known Limitations
This is a university prototype. The following are intentionally not implemented as production services:
• No real payment gateway
• No real Gmail/SMTP email delivery yet
• No JWT authentication
• No API-key authentication for AI services
• No automated CI/CD test suite
• No rate limiting
• No CAPTCHA
• No production hosting configuration
These can be added during the integration and deployment stage.

25. Important Project Files
FilePurposebackend/config/database.phpDatabase connectionbackend/config/app.phpApplication/OTP configurationbackend/middleware/auth.phpAuthentication and authorizationbackend/helpers/response.phpJSON responsesbackend/helpers/validation.phpInput validationbackend/helpers/upload.phpSecure file uploadsbackend/helpers/mailer.phpEmail integration pointbackend/demo_seed.phpDemo password seedingbackend/cron/rent-reminder.phpRent reminder systemdatabase/smart-rental-manager.sqlDatabase schema and demo datapostman/Smart-Rental-Manager-API.jsonAPI testing collectionindex.phpBackend status/landing endpointREADME.mdProject documentation
26. Quick Start
For a fresh XAMPP installation:
1. Start Apache and MySQL.
2. Copy smart-rental-manager into C:\xampp\htdocs\
3. Open phpMyAdmin.
4. Create smart_rental_manager database.
5. Import database/smart-rental-manager.sql.
6. Check backend/config/database.php.
7. Run backend/demo_seed.php if demo passwords need to be regenerated.
8. Open http://localhost/smart-rental-manager/
9. Import the Postman collection.
10. Login and test the APIs.

27. Final Backend Status
The Smart Rental Manager backend is independently implemented and tested.
Backend API          ✓
Database             ✓
Authentication       ✓
Admin System         ✓
Password Reset       ✓
Agreements           ✓
Complaints           ✓
Repairs              ✓
Payments             ✓
Notifications        ✓
Risk Storage         ✓
Rent Reminders       ✓
File Upload Security ✓
Postman Collection   ✓
Documentation        ✓
The backend is ready for integration with the frontend and other team members' modules.

