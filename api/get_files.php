<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../classes/FileManager.php';
require_once '../classes/FolderManager.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    if (!isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
    
    $folder_id = isset($_GET['folder_id']) && $_GET['folder_id'] !== 'null' ? $_GET['folder_id'] : null;
    $user_id = getCurrentUserId();
    
    $fileManager = new FileManager();
    $folderManager = new FolderManager();
    
    $files_result = $fileManager->getFiles($user_id, $folder_id);
    $folders_result = $folderManager->getFolders($user_id, $folder_id);
    
    if ($files_result['success'] && $folders_result['success']) {
        echo json_encode([
            'success' => true,
            'files' => $files_result['files'],
            'folders' => $folders_result['folders']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to retrieve data']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
