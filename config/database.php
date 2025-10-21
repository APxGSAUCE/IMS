<?php
/**
 * Database Configuration and Connection
 * Uses PDO for secure database operations
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ims_db');

/**
 * Get database connection
 * @return PDO Database connection instance
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true, // Connection pooling
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log error (in production, log to file instead of displaying)
            error_log("Database Connection Error: " . $e->getMessage());
            
            // Display user-friendly error
            die("Database connection failed. Please check your configuration.");
        }
    }
    
    return $pdo;
}

/**
 * Execute a query and return results
 * @param string $query SQL query
 * @param array $params Parameters for prepared statement
 * @return array Query results
 */
function executeQuery($query, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Execute an update/insert/delete query
 * @param string $query SQL query
 * @param array $params Parameters for prepared statement
 * @return bool Success status
 */
function executeUpdate($query, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($query);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Update Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get the last inserted ID
 * @return string Last insert ID
 */
function getLastInsertId() {
    $pdo = getDBConnection();
    return $pdo->lastInsertId();
}

/**
 * Begin a transaction
 */
function beginTransaction() {
    $pdo = getDBConnection();
    return $pdo->beginTransaction();
}

/**
 * Commit a transaction
 */
function commit() {
    $pdo = getDBConnection();
    return $pdo->commit();
}

/**
 * Rollback a transaction
 */
function rollback() {
    $pdo = getDBConnection();
    return $pdo->rollBack();
}

