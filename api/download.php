<?php
require_once '../classes/FileManager.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Method not allowed');
}

try {
    if (!isAuthenticated()) {
        http_response_code(401);
        exit('Authentication required');
    }
    
    if (!isset($_GET['id'])) {
        http_response_code(400);
        exit('File ID required');
    }
    
    $file_id = $_GET['id'];
    $user_id = getCurrentUserId();
    
    $fileManager = new FileManager();
    $result = $fileManager->downloadFile($file_id, $user_id);
    
    if ($result['success']) {
        $file = $result['file'];
        
        // Set headers for file download
        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
        header('Content-Length: ' . $file['file_size']);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');
        
        // Output file
        readfile($file['file_path']);
        exit;
    } else {
        http_response_code(404);
        exit('File not found');
    }
} catch (Exception $e) {
    http_response_code(500);
    exit('Server error');
}
?>
