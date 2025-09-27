<?php
require_once '../config/database.php';
require_once 'AdminAuth.php';

class AdminManager {
    private $conn;
    private $auth;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->auth = new AdminAuth();
    }
    
    // User Management
    public function getAllUsers($page = 1, $limit = 20, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            $search_term = '%' . $search . '%';
            
            $count_query = "SELECT COUNT(*) as total FROM users WHERE username LIKE :search OR email LIKE :search";
            $count_stmt = $this->conn->prepare($count_query);
            $count_stmt->bindParam(':search', $search_term);
            $count_stmt->execute();
            $total = $count_stmt->fetch()['total'];
            
            $query = "SELECT u.*, 
                            COUNT(f.id) as file_count,
                            SUM(f.file_size) as total_storage
                     FROM users u 
                     LEFT JOIN files f ON u.id = f.user_id 
                     WHERE u.username LIKE :search OR u.email LIKE :search
                     GROUP BY u.id
                     ORDER BY u.created_at DESC 
                     LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $search_term);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'success' => true,
                'users' => $stmt->fetchAll(),
                'total' => $total,
                'pages' => ceil($total / $limit)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to fetch users'];
        }
    }
    
    public function deleteUser($user_id) {
        try {
            if (!$this->auth->hasPermission('admin')) {
                return ['success' => false, 'message' => 'Insufficient permissions'];
            }
            
            // Get user data before deletion
            $query = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Delete user files from filesystem
            $files_query = "SELECT file_path FROM files WHERE user_id = :user_id";
            $files_stmt = $this->conn->prepare($files_query);
            $files_stmt->bindParam(':user_id', $user_id);
            $files_stmt->execute();
            
            while ($file = $files_stmt->fetch()) {
                if (file_exists($file['file_path'])) {
                    unlink($file['file_path']);
                }
            }
            
            // Delete user (cascade will handle files, folders, etc.)
            $delete_query = "DELETE FROM users WHERE id = :id";
            $delete_stmt = $this->conn->prepare($delete_query);
            $delete_stmt->bindParam(':id', $user_id);
            
            if ($delete_stmt->execute()) {
                $this->auth->logAction('delete_user', 'users', $user_id, $user);
                return ['success' => true, 'message' => 'User deleted successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to delete user'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()];
        }
    }
    
    // File Management
    public function getAllFiles($page = 1, $limit = 20, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            $search_term = '%' . $search . '%';
            
            $query = "SELECT f.*, u.username, u.email 
                     FROM files f 
                     JOIN users u ON f.user_id = u.id 
                     WHERE f.original_name LIKE :search OR u.username LIKE :search
                     ORDER BY f.created_at DESC 
                     LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $search_term);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return ['success' => true, 'files' => $stmt->fetchAll()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to fetch files'];
        }
    }
    
    public function deleteFile($file_id) {
        try {
            if (!$this->auth->hasPermission('admin')) {
                return ['success' => false, 'message' => 'Insufficient permissions'];
            }
            
            // Get file data
            $query = "SELECT * FROM files WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $file_id);
            $stmt->execute();
            $file = $stmt->fetch();
            
            if (!$file) {
                return ['success' => false, 'message' => 'File not found'];
            }
            
            // Delete from database
            $delete_query = "DELETE FROM files WHERE id = :id";
            $delete_stmt = $this->conn->prepare($delete_query);
            $delete_stmt->bindParam(':id', $file_id);
            
            if ($delete_stmt->execute()) {
                // Delete physical file
                if (file_exists($file['file_path'])) {
                    unlink($file['file_path']);
                }
                
                $this->auth->logAction('admin_delete_file', 'files', $file_id, $file);
                return ['success' => true, 'message' => 'File deleted successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to delete file'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Delete failed'];
        }
    }
    
    // System Statistics
    public function getSystemStats() {
        try {
            $stats = [];
            
            // User stats
            $user_query = "SELECT COUNT(*) as total_users FROM users";
            $user_stmt = $this->conn->prepare($user_query);
            $user_stmt->execute();
            $stats['total_users'] = $user_stmt->fetch()['total_users'];
            
            // File stats
            $file_query = "SELECT COUNT(*) as total_files, SUM(file_size) as total_storage FROM files";
            $file_stmt = $this->conn->prepare($file_query);
            $file_stmt->execute();
            $file_result = $file_stmt->fetch();
            $stats['total_files'] = $file_result['total_files'];
            $stats['total_storage'] = $file_result['total_storage'];
            
            // Recent activity
            $activity_query = "SELECT COUNT(*) as recent_uploads FROM files WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $activity_stmt = $this->conn->prepare($activity_query);
            $activity_stmt->execute();
            $stats['recent_uploads'] = $activity_stmt->fetch()['recent_uploads'];
            
            // Storage by file type
            $type_query = "SELECT mime_type, COUNT(*) as count, SUM(file_size) as size 
                          FROM files 
                          GROUP BY mime_type 
                          ORDER BY count DESC";
            $type_stmt = $this->conn->prepare($type_query);
            $type_stmt->execute();
            $stats['file_types'] = $type_stmt->fetchAll();
            
            return ['success' => true, 'stats' => $stats];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to fetch statistics'];
        }
    }

    public function getUserById($userId) {
    try {
        $query = "SELECT u.*, 
                        COUNT(f.id) as file_count,
                        SUM(f.file_size) as total_storage
                 FROM users u 
                 LEFT JOIN files f ON u.id = f.user_id 
                 WHERE u.id = :id
                 GROUP BY u.id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'user' => $stmt->fetch()];
        }
        
        return ['success' => false, 'message' => 'User not found'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to fetch user'];
    }
}

public function updateUser($userId, $data) {
    try {
        if (!$this->auth->hasPermission('admin')) {
            return ['success' => false, 'message' => 'Insufficient permissions'];
        }
        
        $allowedFields = ['username', 'email'];
        $updateFields = [];
        $params = ['id' => $userId];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        
        $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        if ($stmt->execute()) {
            $this->auth->logAction('update_user', 'users', $userId, null, $data);
            return ['success' => true, 'message' => 'User updated successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to update user'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Update failed'];
    }
}

public function getAllFolders($page = 1, $limit = 20, $search = '') {
    try {
        $offset = ($page - 1) * $limit;
        $search_term = '%' . $search . '%';
        
        $query = "SELECT f.*, u.username, pf.name as parent_name
                 FROM folders f 
                 JOIN users u ON f.user_id = u.id 
                 LEFT JOIN folders pf ON f.parent_id = pf.id
                 WHERE f.name LIKE :search OR u.username LIKE :search
                 ORDER BY f.created_at DESC 
                 LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':search', $search_term);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return ['success' => true, 'folders' => $stmt->fetchAll()];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to fetch folders'];
    }
}

public function deleteFolder($folderId) {
    try {
        if (!$this->auth->hasPermission('admin')) {
            return ['success' => false, 'message' => 'Insufficient permissions'];
        }
        
        // Get folder data before deletion
        $query = "SELECT * FROM folders WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $folderId);
        $stmt->execute();
        $folder = $stmt->fetch();
        
        if (!$folder) {
            return ['success' => false, 'message' => 'Folder not found'];
        }
        
        // Delete folder (cascade will handle files)
        $delete_query = "DELETE FROM folders WHERE id = :id";
        $delete_stmt = $this->conn->prepare($delete_query);
        $delete_stmt->bindParam(':id', $folderId);
        
        if ($delete_stmt->execute()) {
            $this->auth->logAction('admin_delete_folder', 'folders', $folderId, $folder);
            return ['success' => true, 'message' => 'Folder deleted successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to delete folder'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Delete failed'];
    }
}

public function getSystemLogs($page = 1, $limit = 50) {
    try {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT sl.*, au.username as admin_username
                 FROM system_logs sl 
                 LEFT JOIN admin_users au ON sl.admin_id = au.id
                 ORDER BY sl.created_at DESC 
                 LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return ['success' => true, 'logs' => $stmt->fetchAll()];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to fetch logs'];
    }
}

public function clearSystemLogs() {
    try {
        if (!$this->auth->hasPermission('super_admin')) {
            return ['success' => false, 'message' => 'Insufficient permissions'];
        }
        
        $query = "DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute()) {
            $this->auth->logAction('clear_logs', 'system_logs');
            return ['success' => true, 'message' => 'Old logs cleared successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to clear logs'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Clear failed'];
    }
}

public function getDetailedReports() {
    try {
        $reports = [];
        
        // User storage report
        $storage_query = "SELECT u.username, COUNT(f.id) as file_count, SUM(f.file_size) as total_storage
                         FROM users u 
                         LEFT JOIN files f ON u.id = f.user_id
                         GROUP BY u.id
                         ORDER BY total_storage DESC
                         LIMIT 10";
        $storage_stmt = $this->conn->prepare($storage_query);
        $storage_stmt->execute();
        $reports['user_storage'] = $storage_stmt->fetchAll();
        
        // Upload activity report
        $activity_query = "SELECT DATE(created_at) as date, COUNT(*) as uploads
                          FROM files 
                          WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                          GROUP BY DATE(created_at)
                          ORDER BY date DESC
                          LIMIT 10";
        $activity_stmt = $this->conn->prepare($activity_query);
        $activity_stmt->execute();
        $reports['upload_activity'] = $activity_stmt->fetchAll();
        
        return ['success' => true] + $reports;
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to generate reports'];
    }
}

    
    // System Settings
    public function getSettings() {
        try {
            $query = "SELECT * FROM system_settings ORDER BY setting_key";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return ['success' => true, 'settings' => $stmt->fetchAll()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to fetch settings'];
        }
    }
    
    public function updateSetting($key, $value, $type = 'string') {
        try {
            if (!$this->auth->hasPermission('super_admin')) {
                return ['success' => false, 'message' => 'Insufficient permissions'];
            }
            
            // Get old value for logging
            $old_query = "SELECT setting_value FROM system_settings WHERE setting_key = :key";
            $old_stmt = $this->conn->prepare($old_query);
            $old_stmt->bindParam(':key', $key);
            $old_stmt->execute();
            $old_value = $old_stmt->fetch()['setting_value'] ?? null;
            
            $query = "INSERT INTO system_settings (setting_key, setting_value, setting_type) 
                     VALUES (:key, :value, :type)
                     ON DUPLICATE KEY UPDATE 
                     setting_value = :value, setting_type = :type, updated_at = NOW()";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':key', $key);
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':type', $type);
            
            if ($stmt->execute()) {
                $this->auth->logAction('update_setting', 'system_settings', null, 
                    ['key' => $key, 'old_value' => $old_value], 
                    ['key' => $key, 'new_value' => $value]);
                return ['success' => true, 'message' => 'Setting updated successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to update setting'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Update failed'];
        }
    }
}
?>
