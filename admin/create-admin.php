<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert admin user
        $query = "INSERT INTO admin_users (username, email, password, role, is_active) VALUES (:username, :email, :password, 'super_admin', 1)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        
        if ($stmt->execute()) {
            echo "<h2>Admin user created successfully!</h2>";
            echo "<p>Username: " . htmlspecialchars($username) . "</p>";
            echo "<p>Email: " . htmlspecialchars($email) . "</p>";
            echo "<p>Password: " . htmlspecialchars($password) . "</p>";
            echo "<p><a href='login.php'>Go to Login</a></p>";
        } else {
            echo "<h2>Failed to create admin user</h2>";
        }
    } catch (Exception $e) {
        echo "<h2>Error: " . $e->getMessage() . "</h2>";
    }
} else {
    header('Location: debug_login.php');
}
?>
