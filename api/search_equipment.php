<?php
/**
 * API Endpoint - Search Equipment
 * Returns equipment matching search query
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
$query = $input['query'] ?? '';

if (empty($query)) {
    echo json_encode(['results' => []]);
    exit;
}

// Search equipment
$results = searchEquipment($query);

echo json_encode([
    'success' => true,
    'results' => $results,
    'count' => count($results)
]);

