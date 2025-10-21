<?php
/**
 * API Endpoint - Validate Serial Number
 * Checks if serial number is unique
 */

header('Content-Type: application/json');

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Verify CSRF token
$headers = getallheaders();
$csrfToken = $headers['X-CSRF-Token'] ?? $headers['X-Csrf-Token'] ?? '';

if (!verifyCSRFToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$serial = $input['serial'] ?? '';
$excludeId = $input['exclude_id'] ?? null;

if (empty($serial)) {
    echo json_encode(['valid' => false, 'message' => 'Serial number is required']);
    exit;
}

// Validate serial number
$isValid = validateSerialNumber($serial, $excludeId);

echo json_encode([
    'valid' => $isValid,
    'message' => $isValid ? 'Serial number is available' : 'Serial number already exists'
]);

