<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

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
        case 'view':
            $userId = $_GET['id'] ?? null;
            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'User ID required']);
                exit;
            }
            
            $result = $adminManager->getUserById($userId);
            echo json_encode($result);
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            $userId = $_GET['id'] ?? null;
            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'User ID required']);
                exit;
            }
            
            $result = $adminManager->deleteUser($userId);
            echo json_encode($result);
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $userId = $_GET['id'] ?? null;
            
            if (!$userId || !$input) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'User ID and data required']);
                exit;
            }
            
            $result = $adminManager->updateUser($userId, $input);
            echo json_encode($result);
            break;
            
        default:
            $page = $_GET['page'] ?? 1;
            $search = $_GET['search'] ?? '';
            $result = $adminManager->getAllUsers($page, 20, $search);
            echo json_encode($result);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
