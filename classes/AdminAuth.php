<?php
require_once '../config/database.php';

class AdminAuth {
    private $conn;
    
    public function __construct() {
        try {
            $database = new Database();
            $this->conn = $database->getConnection();
        } catch (Exception $e) {
            error_log("AdminAuth database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    public function login($username, $password) {
        try {
            $query = "SELECT id, username, email, password, role, is_active FROM admin_users WHERE (username = ? OR email = ?) AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$username, $username]);
            
            if ($stmt->rowCount() > 0) {
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($password, $admin['password'])) {
                    // Update last login
                    $update_query = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
                    $update_stmt = $this->conn->prepare($update_query);
                    $update_stmt->execute([$admin['id']]);
                    
                    // Set admin session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['admin_role'] = $admin['role'];
                    
                    $this->logAction('admin_login', 'admin_users', $admin['id']);
                    
                    return ['success' => true, 'admin' => $admin];
                } else {
                    return ['success' => false, 'message' => 'Invalid password'];
                }
            } else {
                return ['success' => false, 'message' => 'User not found or account disabled'];
            }
        } catch (Exception $e) {
            error_log("Admin login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed: Database error'];
        }
    }
    
    public function isAdminAuthenticated() {
        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }
    
    public function getCurrentAdminId() {
        return $_SESSION['admin_id'] ?? null;
    }
    
    public function hasPermission($required_role = 'admin') {
        if (!$this->isAdminAuthenticated()) {
            return false;
        }
        
        $role_hierarchy = ['moderator' => 1, 'admin' => 2, 'super_admin' => 3];
        $current_role = $_SESSION['admin_role'] ?? 'moderator';
        
        return ($role_hierarchy[$current_role] ?? 0) >= ($role_hierarchy[$required_role] ?? 999);
    }
    
    public function logAction($action, $table = null, $record_id = null, $old_values = null, $new_values = null) {
        try {
            // Only log if we have an admin session
            if (!$this->isAdminAuthenticated()) {
                return;
            }
            
            $query = "INSERT INTO system_logs (admin_id, action, table_affected, record_id, old_values, new_values, ip_address, user_agent) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $_SESSION['admin_id'] ?? null,
                $action,
                $table,
                $record_id,
                json_encode($old_values),
                json_encode($new_values),
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            error_log("Log action error: " . $e->getMessage());
        }
    }
    
    public function logout() {
        if ($this->isAdminAuthenticated()) {
            $this->logAction('admin_logout', 'admin_users', $_SESSION['admin_id']);
        }
        session_destroy();
        return ['success' => true];
    }
}
?>
