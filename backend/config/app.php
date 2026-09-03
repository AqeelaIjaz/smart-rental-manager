<?php

date_default_timezone_set('Asia/Karachi');

/**
 * =====================================================================
 * APP CONFIG
 * =====================================================================
 * General application constants — separate from database.php so
 * non-DB settings (environment, OTP policy) are easy to find and edit.
 */

if (!defined('APP_ENV')) {
    define('APP_ENV', 'development');
}

if (!defined('OTP_LENGTH')) {
    define('OTP_LENGTH', 6);
}

if (!defined('OTP_EXPIRY_MINUTES')) {
    define('OTP_EXPIRY_MINUTES', 10);
}
/*
 * =====================================================================
 * ADD THIS BLOCK to the END of backend/config/app.php (Member 5)
 * =====================================================================
 * Leave the values empty to run in dev-mode (messages get logged to
 * backend/uploads/whatsapp-log.txt instead of actually sending).
 * Fill them in once you have real WhatsApp Cloud API / Twilio credentials.
 */

if (!defined('WHATSAPP_API_URL')) {
    define('WHATSAPP_API_URL', ''); // e.g. 'https://graph.facebook.com/v20.0/<phone-number-id>/messages'
}

if (!defined('WHATSAPP_API_TOKEN')) {
    define('WHATSAPP_API_TOKEN', ''); // e.g. your Meta/Twilio access token
}

if (!defined('WHATSAPP_FROM_NUMBER')) {
    define('WHATSAPP_FROM_NUMBER', ''); // your registered sending number, if the provider needs it
}



