<?php
/**
 * API Endpoint - Get Equipment by ID
 * Returns detailed equipment information
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

// Get equipment ID
$equipmentId = $_GET['id'] ?? 0;

if (empty($equipmentId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Equipment ID is required']);
    exit;
}

// Get equipment data
$equipment = getEquipmentById($equipmentId);

if (!$equipment) {
    http_response_code(404);
    echo json_encode(['error' => 'Equipment not found']);
    exit;
}

echo json_encode([
    'success' => true,
    'equipment' => $equipment
]);

