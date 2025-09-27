<?php
require_once '../config/database.php';

class FolderManager {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function createFolder($name, $parent_id, $user_id) {
        try {
            $query = "INSERT INTO folders (name, parent_id, user_id) VALUES (:name, :parent_id, :user_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':parent_id', $parent_id);
            $stmt->bindParam(':user_id', $user_id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true, 
                    'message' => 'Folder created successfully',
                    'folder_id' => $this->conn->lastInsertId()
                ];
            }
            return ['success' => false, 'message' => 'Failed to create folder'];
        } catch (Exception $e) {
            error_log("Create folder error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create folder'];
        }
    }
    
    public function getFolders($user_id, $parent_id = null) {
        try {
            $query = "SELECT * FROM folders WHERE user_id = :user_id";
            $params = ['user_id' => $user_id];
            
            if ($parent_id) {
                $query .= " AND parent_id = :parent_id";
                $params['parent_id'] = $parent_id;
            } else {
                $query .= " AND parent_id IS NULL";
            }
            
            $query .= " ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->execute();
            
            return ['success' => true, 'folders' => $stmt->fetchAll()];
        } catch (Exception $e) {
            error_log("Get folders error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to retrieve folders'];
        }
    }
}
?>
