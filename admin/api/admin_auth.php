<?php
session_start();
header('Content-Type: application/json');

require_once '../../classes/AdminAuth.php';

$auth = new AdminAuth();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'logout':
        $result = $auth->logout();
        header('Location: ../login.php?message=Logged out successfully');
        break;
        
    case 'check':
        if ($auth->isAdminAuthenticated()) {
            echo json_encode([
                'authenticated' => true,
                'admin_id' => $_SESSION['admin_id'],
                'username' => $_SESSION['admin_username'],
                'role' => $_SESSION['admin_role']
            ]);
        } else {
            echo json_encode(['authenticated' => false]);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
