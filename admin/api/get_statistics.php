<?php
session_start();
header('Content-Type: application/json');

require_once '../../classes/AdminAuth.php';
require_once '../../classes/AdminManager.php';

$auth = new AdminAuth();
if (!$auth->isAdminAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$adminManager = new AdminManager();

try {
    $stats = $adminManager->getSystemStats();
    echo json_encode($stats);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch statistics']);
}
?>
