<?php

/**
 * PWA Analytics API
 * Tracks PWA usage and performance metrics
 */

header('Content-Type: application/json');

session_start();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Basic validation
if (!$input || !isset($input['event'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

// Log analytics event (you can save to database if needed)
$event = [
    'user_id' => $_SESSION['user_id'] ?? null,
    'event' => $input['event'],
    'data' => $input['data'] ?? [],
    'timestamp' => time(),
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
];

// For now, just return success
// In production, you'd save this to a database
echo json_encode([
    'status' => 'success',
    'message' => 'Analytics event recorded',
    'event' => $input['event']
]);
