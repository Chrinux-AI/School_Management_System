<?php

/**
 * Session Keep-Alive API
 * Prevents session timeout during active usage
 */

header('Content-Type: application/json');

session_start();

// Check if session is active
if (isset($_SESSION['user_id'])) {
    // Update last activity time
    $_SESSION['last_activity'] = time();

    echo json_encode([
        'status' => 'success',
        'active' => true,
        'user_id' => $_SESSION['user_id'],
        'role' => $_SESSION['role'] ?? 'unknown',
        'timestamp' => time()
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'active' => false,
        'message' => 'Session expired'
    ]);
}
