<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, DELETE');

require_once '../../classes/AdminAuth.php';
require_once '../../classes/AdminManager.php';

$auth = new AdminAuth();
if (!$auth->isAdminAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$adminManager = new AdminManager();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'clear_logs':
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            if (!$auth->hasPermission('super_admin')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
                exit;
            }
            
            $result = $adminManager->clearSystemLogs();
            echo json_encode($result);
            break;
            
        default:
            $result = $adminManager->getSettings();
            echo json_encode($result);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
