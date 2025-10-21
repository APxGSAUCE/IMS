<?php
/**
 * Helper Functions for Equipment Inventory Management System
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize input data to prevent XSS attacks
 * @param mixed $data Input data to sanitize
 * @return mixed Sanitized data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool Verification result
 */
function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get equipment by ID
 * @param int $id Equipment ID
 * @return array|null Equipment data or null if not found
 */
function getEquipmentById($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/**
 * Update equipment quantities based on current transactions
 * @param int $equipment_id Equipment ID
 * @return bool Success status
 */
function updateEquipmentQuantities($equipment_id) {
    try {
        $pdo = getDBConnection();
        
        // Calculate quantities from transactions
        $stmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN transaction_type = 'in_use' THEN quantity ELSE 0 END) -
                SUM(CASE WHEN transaction_type = 'returned' THEN quantity ELSE 0 END) as in_use,
                SUM(CASE WHEN transaction_type = 'replacement' THEN quantity ELSE 0 END) as in_replacement,
                SUM(CASE WHEN transaction_type = 'added' THEN quantity ELSE 0 END) -
                SUM(CASE WHEN transaction_type = 'removed' THEN quantity ELSE 0 END) as total
            FROM equipment_transactions
            WHERE equipment_id = ?
        ");
        $stmt->execute([$equipment_id]);
        $result = $stmt->fetch();
        
        $in_use = max(0, (int)($result['in_use'] ?? 0));
        $in_replacement = max(0, (int)($result['in_replacement'] ?? 0));
        $total = max(0, (int)($result['total'] ?? 0));
        $available = max(0, $total - $in_use - $in_replacement);
        
        // Update equipment record
        $updateStmt = $pdo->prepare("
            UPDATE equipment 
            SET in_use_quantity = ?,
                in_replacement_quantity = ?,
                total_quantity = ?,
                available_quantity = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        
        return $updateStmt->execute([
            $in_use,
            $in_replacement,
            $total,
            $available,
            $equipment_id
        ]);
        
    } catch (PDOException $e) {
        error_log("Update quantities error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a new transaction
 * @param array $data Transaction data
 * @return int|false Transaction ID or false on failure
 */
function createTransaction($data) {
    try {
        $pdo = getDBConnection();
        
        // Validate required fields
        $required = ['equipment_id', 'transaction_type', 'quantity', 'created_by'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }
        
        // Get current equipment state
        $equipment = getEquipmentById($data['equipment_id']);
        if (!$equipment) {
            return false;
        }
        
        // Validate quantity based on transaction type
        $quantity = (int)$data['quantity'];
        if ($quantity <= 0) {
            return false;
        }
        
        // Check if we have enough stock for the transaction
        if (in_array($data['transaction_type'], ['in_use', 'replacement', 'removed'])) {
            if ($data['transaction_type'] === 'in_use' || $data['transaction_type'] === 'replacement') {
                if ($quantity > $equipment['available_quantity']) {
                    return false; // Not enough available stock
                }
            } elseif ($data['transaction_type'] === 'removed') {
                if ($quantity > $equipment['total_quantity']) {
                    return false; // Cannot remove more than total
                }
            }
        }
        
        // Insert transaction
        $stmt = $pdo->prepare("
            INSERT INTO equipment_transactions 
            (equipment_id, transaction_type, quantity, assigned_to, notes, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $data['equipment_id'],
            $data['transaction_type'],
            $quantity,
            $data['assigned_to'] ?? null,
            $data['notes'] ?? null,
            $data['created_by']
        ]);
        
        if ($result) {
            $transactionId = $pdo->lastInsertId();
            
            // Update equipment quantities
            updateEquipmentQuantities($data['equipment_id']);
            
            return $transactionId;
        }
        
        return false;
        
    } catch (PDOException $e) {
        error_log("Create transaction error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get recent transactions
 * @param int $limit Number of transactions to retrieve
 * @return array Array of transactions
 */
function getRecentTransactions($limit = 10) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            e.name as equipment_name,
            e.serial_number,
            e.category
        FROM equipment_transactions t
        INNER JOIN equipment e ON t.equipment_id = e.id
        ORDER BY t.transaction_date DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Get low stock items (available quantity < 5)
 * @return array Array of low stock equipment
 */
function getLowStockItems() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("
        SELECT * FROM equipment 
        WHERE available_quantity < 5 
        AND status = 'active'
        ORDER BY available_quantity ASC
    ");
    return $stmt->fetchAll();
}

/**
 * Search equipment by query string
 * @param string $query Search query
 * @return array Array of matching equipment
 */
function searchEquipment($query) {
    $pdo = getDBConnection();
    $searchTerm = "%{$query}%";
    
    $stmt = $pdo->prepare("
        SELECT * FROM equipment 
        WHERE (name LIKE ? OR 
               serial_number LIKE ? OR 
               category LIKE ? OR 
               description LIKE ?)
        AND status = 'active'
        ORDER BY name ASC
    ");
    
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    return $stmt->fetchAll();
}

/**
 * Validate serial number uniqueness
 * @param string $serial Serial number to validate
 * @param int|null $excludeId Equipment ID to exclude (for updates)
 * @return bool True if valid (unique), false if already exists
 */
function validateSerialNumber($serial, $excludeId = null) {
    $pdo = getDBConnection();
    
    if ($excludeId) {
        $stmt = $pdo->prepare("SELECT id FROM equipment WHERE serial_number = ? AND id != ?");
        $stmt->execute([$serial, $excludeId]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM equipment WHERE serial_number = ?");
        $stmt->execute([$serial]);
    }
    
    return $stmt->fetch() === false; // True if no record found (unique)
}

/**
 * Get all categories
 * @return array Array of categories
 */
function getAllCategories() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    return $stmt->fetchAll();
}

/**
 * Get dashboard statistics
 * @return array Dashboard stats
 */
function getDashboardStats() {
    $pdo = getDBConnection();
    
    $stats = [];
    
    // Total equipment items
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM equipment WHERE status = 'active'");
    $stats['total_items'] = $stmt->fetch()['total'];
    
    // Total in use
    $stmt = $pdo->query("SELECT SUM(in_use_quantity) as total FROM equipment WHERE status = 'active'");
    $stats['in_use'] = $stmt->fetch()['total'] ?? 0;
    
    // Total in replacement
    $stmt = $pdo->query("SELECT SUM(in_replacement_quantity) as total FROM equipment WHERE status = 'active'");
    $stats['in_replacement'] = $stmt->fetch()['total'] ?? 0;
    
    // Total available
    $stmt = $pdo->query("SELECT SUM(available_quantity) as total FROM equipment WHERE status = 'active'");
    $stats['available'] = $stmt->fetch()['total'] ?? 0;
    
    // Low stock count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM equipment WHERE available_quantity < 5 AND status = 'active'");
    $stats['low_stock'] = $stmt->fetch()['total'];
    
    return $stats;
}

/**
 * Format currency
 * @param float $amount Amount to format
 * @return string Formatted currency string
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Format date
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Get transaction type badge HTML
 * @param string $type Transaction type
 * @return string HTML for badge
 */
function getTransactionTypeBadge($type) {
    $badges = [
        'in_use' => '<span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">In Use</span>',
        'returned' => '<span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">Returned</span>',
        'replacement' => '<span class="px-2 py-1 text-xs font-semibold rounded bg-red-100 text-red-800">Replacement</span>',
        'added' => '<span class="px-2 py-1 text-xs font-semibold rounded bg-purple-100 text-purple-800">Added</span>',
        'removed' => '<span class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800">Removed</span>'
    ];
    
    return $badges[$type] ?? '<span class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800">' . ucfirst($type) . '</span>';
}

/**
 * Get status badge HTML
 * @param string $status Status
 * @return string HTML for badge
 */
function getStatusBadge($status) {
    if ($status === 'active') {
        return '<span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">Active</span>';
    } else {
        return '<span class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800">Discontinued</span>';
    }
}

/**
 * Check if user is logged in
 * @return bool Login status
 */
function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Get current user
 * @return array|null User data or null
 */
function getCurrentUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'] ?? 'staff'
    ];
}

/**
 * Check if user is admin
 * @return bool Admin status
 */
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

/**
 * Check if user is super admin
 * @return bool Super admin status
 */
function isSuperAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'super_admin';
}

/**
 * Redirect to a page
 * @param string $page Page to redirect to
 */
function redirect($page) {
    header("Location: $page");
    exit;
}

/**
 * Set flash message
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message content
 */
function setFlashMessage($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 * @return array|null Flash message or null
 */
function getFlashMessage() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    
    return null;
}

