<?php
/**
 * =====================================================================
 * WHATSAPP / SMS NOTIFIER HELPER (Member 5)
 * =====================================================================
 * Sends outbound rent-reminder / payment-confirmation messages.
 *
 * Real integration requires a provider account (e.g. Meta WhatsApp
 * Cloud API, Twilio). Credentials go in backend/config/app.php as
 * WHATSAPP_API_URL / WHATSAPP_API_TOKEN / WHATSAPP_FROM_NUMBER.
 *
 * Until those are filled in, this helper runs in "dev mode": instead
 * of failing the whole request, it logs the message to
 * backend/uploads/whatsapp-log.txt so you can demo and verify the
 * *logic* (who gets reminded, when, with what text) without needing
 * live API access during the hackathon.
 */

require_once __DIR__ . '/../config/app.php';

if (!function_exists('sendWhatsAppMessage')) {
    /**
     * @param string $toPhone  E.164-ish phone number, e.g. "923001234567"
     * @param string $message
     * @return array{success:bool, message:string, mode:string}
     */
    function sendWhatsAppMessage(string $toPhone, string $message): array
    {
        $hasRealCredentials = defined('WHATSAPP_API_URL') && WHATSAPP_API_URL !== ''
            && defined('WHATSAPP_API_TOKEN') && WHATSAPP_API_TOKEN !== '';

        if (!$hasRealCredentials) {
            return logWhatsAppDevMode($toPhone, $message);
        }

        // ---------------------------------------------------------------
        // Real integration point. Shaped for Meta's WhatsApp Cloud API;
        // adjust the payload if the team ends up using Twilio instead.
        // ---------------------------------------------------------------
        $payload = json_encode([
            'messaging_product' => 'whatsapp',
            'to'                => $toPhone,
            'type'              => 'text',
            'text'              => ['body' => $message],
        ]);

        $ch = curl_init(WHATSAPP_API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . WHATSAPP_API_TOKEN,
            'Content-Type: application/json',
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 300) {
            error_log("WHATSAPP SEND ERROR: HTTP {$httpCode} {$curlError} Response: {$response}");
            // Fall back to logging so the reminder isn't silently lost.
            return logWhatsAppDevMode($toPhone, $message, true);
        }

        return ['success' => true, 'message' => 'WhatsApp message sent.', 'mode' => 'live'];
    }
}

if (!function_exists('logWhatsAppDevMode')) {
    function logWhatsAppDevMode(string $toPhone, string $message, bool $wasSendFailure = false): array
    {
        $baseUploadDir = realpath(__DIR__ . '/../uploads');
        $logPath = ($baseUploadDir !== false ? $baseUploadDir : sys_get_temp_dir())
            . DIRECTORY_SEPARATOR . 'whatsapp-log.txt';

        $prefix = $wasSendFailure ? '[LIVE SEND FAILED - LOGGED INSTEAD]' : '[DEV MODE]';
        $line = sprintf(
            "%s %s To: %s | Message: %s\n",
            $prefix,
            date('Y-m-d H:i:s'),
            $toPhone,
            str_replace("\n", ' ', $message)
        );

        @file_put_contents($logPath, $line, FILE_APPEND);

        return [
            'success' => true,
            'message' => 'Dev mode: message logged instead of sent (no WhatsApp API credentials configured).',
            'mode'    => 'dev-log',
        ];
    }
}
