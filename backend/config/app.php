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