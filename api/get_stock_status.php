<?php
/**
 * API Endpoint - Get Stock Status
 * Returns current stock status for dashboard widgets
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

// Get dashboard statistics
$stats = getDashboardStats();
$lowStock = getLowStockItems();

echo json_encode([
    'success' => true,
    'stats' => $stats,
    'low_stock_items' => $lowStock,
    'timestamp' => date('Y-m-d H:i:s')
]);

