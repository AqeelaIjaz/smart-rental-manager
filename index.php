<?php
/**
 * Smart Rental Manager — root landing page.
 * This is NOT the frontend (no frontend exists yet).
 * It confirms the backend is reachable and lists available API groups.
 */

header('Content-Type: application/json');

echo json_encode([
    'project' => 'Smart Rental Manager',
    'status'  => 'Backend running',
    'note'    => 'No frontend is implemented yet. See README.md for the full API reference and Postman collection.',

    'api_groups' => [
        'auth'          => '/backend/auth/',
        'admin'         => '/backend/admin/',
        'agreements'    => '/backend/agreements/',
        'complaints'    => '/backend/complaints/',
        'repairs'       => '/backend/repairs/',
        'payments'      => '/backend/payments/',
        'notifications' => '/backend/notifications/',
        'risk'          => '/backend/risk/',
        'cron'          => '/backend/cron/',
    ],
], JSON_PRETTY_PRINT);