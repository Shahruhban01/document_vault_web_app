<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, DELETE');

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
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            $folderId = $_GET['id'] ?? null;
            if (!$folderId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Folder ID required']);
                exit;
            }
            
            $result = $adminManager->deleteFolder($folderId);
            echo json_encode($result);
            break;
            
        default:
            $result = $adminManager->getAllFolders();
            echo json_encode($result);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
