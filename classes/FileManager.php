<?php
require_once '../config/database.php';

class FileManager {
    private $conn;
    private $upload_dir;
    private $max_file_size = 50 * 1024 * 1024; // 50MB
    private $allowed_types = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf', 'text/plain', 'text/csv',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/zip', 'application/x-zip-compressed'
    ];
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->upload_dir = __DIR__ . '/../uploads/';
        
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }
    
    public function uploadFile($file, $folder_id = null, $user_id = null) {
        try {
            if (!$user_id) {
                return ['success' => false, 'message' => 'User not authenticated'];
            }
            
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            
            // Generate unique filename
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
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
                $stmt->bindParam(':user_id', $user_id);
                
                if ($stmt->execute()) {
                    return [
                        'success' => true, 
                        'message' => 'File uploaded successfully',
                        'file_id' => $this->conn->lastInsertId()
                    ];
                } else {
                    unlink($file_path); // Delete file if database insert fails
                    return ['success' => false, 'message' => 'Database error'];
                }
            }
            
            return ['success' => false, 'message' => 'File upload failed'];
        } catch (Exception $e) {
            error_log("Upload error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Upload failed'];
        }
    }
    
    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'Upload error occurred'];
        }
        
        // Check file size
        if ($file['size'] > $this->max_file_size) {
            return ['valid' => false, 'message' => 'File too large (max 50MB)'];
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $this->allowed_types)) {
            return ['valid' => false, 'message' => 'File type not allowed'];
        }
        
        // Check file extension
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'txt', 'csv', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            return ['valid' => false, 'message' => 'File extension not allowed'];
        }
        
        return ['valid' => true, 'message' => 'File is valid'];
    }
    
    public function getFiles($user_id, $folder_id = null) {
        try {
            $query = "SELECT * FROM files WHERE user_id = :user_id";
            $params = ['user_id' => $user_id];
            
            if ($folder_id) {
                $query .= " AND folder_id = :folder_id";
                $params['folder_id'] = $folder_id;
            } else {
                $query .= " AND folder_id IS NULL";
            }
            
            $query .= " ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->execute();
            
            return ['success' => true, 'files' => $stmt->fetchAll()];
        } catch (Exception $e) {
            error_log("Get files error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to retrieve files'];
        }
    }
    
    public function deleteFile($file_id, $user_id) {
        try {
            // Get file info first
            $query = "SELECT file_path FROM files WHERE id = :id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $file_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $file = $stmt->fetch();
                
                // Delete from database
                $delete_query = "DELETE FROM files WHERE id = :id AND user_id = :user_id";
                $delete_stmt = $this->conn->prepare($delete_query);
                $delete_stmt->bindParam(':id', $file_id);
                $delete_stmt->bindParam(':user_id', $user_id);
                
                if ($delete_stmt->execute()) {
                    // Delete physical file
                    if (file_exists($file['file_path'])) {
                        unlink($file['file_path']);
                    }
                    return ['success' => true, 'message' => 'File deleted successfully'];
                }
            }
            
            return ['success' => false, 'message' => 'File not found or access denied'];
        } catch (Exception $e) {
            error_log("Delete file error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Delete failed'];
        }
    }
    
    public function downloadFile($file_id, $user_id) {
        try {
            $query = "SELECT * FROM files WHERE id = :id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $file_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $file = $stmt->fetch();
                if (file_exists($file['file_path'])) {
                    return ['success' => true, 'file' => $file];
                }
            }
            
            return ['success' => false, 'message' => 'File not found'];
        } catch (Exception $e) {
            error_log("Download file error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Download failed'];
        }
    }
}
?>
