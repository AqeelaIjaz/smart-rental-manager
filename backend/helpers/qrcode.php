<?php
/**
 * =====================================================================
 * QR CODE HELPER (Member 5)
 * =====================================================================
 * PHP has no built-in QR generator and installing a Composer library
 * on a plain XAMPP setup is unnecessary overhead for a hackathon
 * prototype. Instead, this calls a public QR image API
 * (api.qrserver.com) to render the PNG, then saves it locally under
 * backend/uploads/receipts/ so the app still "owns" the file the same
 * way it does for agreement/repair uploads.
 *
 * If the server has no internet access (offline demo), generateQrReceipt()
 * fails gracefully and returns success=false — the caller should not
 * crash the whole request, just leave qr_receipt as null.
 */

if (!function_exists('generateQrReceipt')) {
    /**
     * Generates a QR code PNG encoding $qrContent and saves it to
     * backend/uploads/receipts/. Returns the relative path to store in
     * payments.qr_receipt.
     *
     * @param int    $paymentId
     * @param string $qrContent  Text/URL to encode in the QR code
     * @return array{success:bool, message:string, filename?:string, path?:string}
     */
    function generateQrReceipt(int $paymentId, string $qrContent): array
    {
        $baseUploadDir = realpath(__DIR__ . '/../uploads');
        if ($baseUploadDir === false) {
            return ['success' => false, 'message' => 'Upload directory is not configured correctly.'];
        }

        $targetDir = $baseUploadDir . DIRECTORY_SEPARATOR . 'receipts';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $encoded = urlencode($qrContent);
        $apiUrl  = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={$encoded}";

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $imageData = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($imageData === false || $httpCode !== 200 || empty($imageData)) {
            error_log("QR GENERATION ERROR: HTTP {$httpCode} {$curlError}");
            return ['success' => false, 'message' => 'Failed to generate QR code image.'];
        }

        $filename    = 'receipt_' . $paymentId . '_' . bin2hex(random_bytes(6)) . '.png';
        $destination = $targetDir . DIRECTORY_SEPARATOR . $filename;

        if (file_put_contents($destination, $imageData) === false) {
            return ['success' => false, 'message' => 'Failed to save QR code image.'];
        }

        return [
            'success'  => true,
            'message'  => 'QR code generated successfully.',
            'filename' => $filename,
            'path'     => 'backend/uploads/receipts/' . $filename,
        ];
    }
}

if (!function_exists('buildReceiptQrText')) {
    /**
     * Builds the plain-text payload encoded inside the QR code.
     * Kept as simple readable text (not a URL) so it verifies even
     * without a live public domain during the hackathon demo.
     */
    function buildReceiptQrText(int $paymentId, string $txnReference, $amount, string $paymentDate): string
    {
        return "SMART-RENTAL-MANAGER RECEIPT\n"
            . "Payment ID: {$paymentId}\n"
            . "Txn Ref: {$txnReference}\n"
            . "Amount: Rs. {$amount}\n"
            . "Date: {$paymentDate}\n"
            . "Status: PAID";
    }
}
