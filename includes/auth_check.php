<?php
/**
 * Authentication Check
 * Include this file at the top of protected pages
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Store the current page to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    redirect('login.php');
}

// Regenerate session ID periodically to prevent session fixation
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // Every 5 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

