<?php
/**
 * =====================================================================
 * RESPONSE HELPER
 * =====================================================================
 * Provides consistent JSON success/error responses across every
 * endpoint, plus a couple of small request helpers.
 */

if (!function_exists('sendResponse')) {
    /**
     * Sends a JSON response and terminates the script.
     *
     * @param bool   $success
     * @param string $message
     * @param mixed  $data
     * @param int    $httpCode
     */
    function sendResponse(bool $success, string $message, $data = null, int $httpCode = 200): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');

        $response = [
            'success' => $success,
            'message' => $message,
        ];

        // Only include "data" key when relevant, but always include it as
        // an object/array (never null) to keep frontend consumption simple.
        $response['data'] = $data ?? new stdClass();

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('sendSuccess')) {
    function sendSuccess(string $message, $data = null, int $httpCode = 200): void
    {
        sendResponse(true, $message, $data, $httpCode);
    }
}

if (!function_exists('sendError')) {
    function sendError(string $message, int $httpCode = 400, $data = null): void
    {
        sendResponse(false, $message, $data, $httpCode);
    }
}

if (!function_exists('getJsonInput')) {
    /**
     * Reads and decodes JSON body from the request.
     * Returns an empty array if body is missing or invalid.
     */
    function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return [];
        }
        return $decoded;
    }
}

if (!function_exists('requireMethod')) {
    /**
     * Ensures the request uses the expected HTTP method, else 405.
     */
    function requireMethod(string $method): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            sendError('Method not allowed. Expected ' . $method . '.', 405);
        }
    }
}
