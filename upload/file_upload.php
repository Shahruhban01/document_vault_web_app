<?php
// upload/file_upload.php
session_start();
require_once '../config/database.php';

class FileManager {
    private $conn;
    private $upload_dir = '../uploads/';
    private $max_file_size = 50 * 1024 * 1024; // 50MB
    private $allowed_types = [
        'image/jpeg', 'image/png', 'image/gif',
        'application/pdf', 'text/plain',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }
    
    public function uploadFile($file, $folder_id = null) {
        // Security validations
        if (!$this->validateFile($file)) {
            return ['status' => 'error', 'message' => 'Invalid file'];
        }
        
        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid() . '.' . $file_extension;
        $file_path = $this->upload_dir . $unique_filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Save to database
            $query = "INSERT INTO files (filename, original_name, file_path, file_size, mime_type, folder_id, user_id) 
                      VALUES (:filename, :original_name, :file_path, :file_size, :mime_type, :folder_id, :user_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':filename', $unique_filename);
            $stmt->bindParam(':original_name', $file['name']);
            $stmt->bindParam(':file_path', $file_path);
            $stmt->bindParam(':file_size', $file['size']);
            $stmt->bindParam(':mime_type', $file['type']);
            $stmt->bindParam(':folder_id', $folder_id);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                return ['status' => 'success', 'message' => 'File uploaded successfully'];
            }
        }
        
        return ['status' => 'error', 'message' => 'Upload failed'];
    }
    
    private function validateFile($file) {
        // Check file size
        if ($file['size'] > $this->max_file_size) {
            return false;
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $this->allowed_types)) {
            return false;
        }
        
        // Check file extension
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'doc', 'docx', 'xls', 'xlsx'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        return in_array($file_extension, $allowed_extensions);
    }
}
?>
