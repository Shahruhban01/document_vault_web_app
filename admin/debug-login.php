<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Admin Login Debug Tool</h1>";

// Test database connection
echo "<h2>Testing Database Connection...</h2>";
try {
    require_once '../config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    echo "<p style='color: green;'>✓ Database Connection: SUCCESS</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database Connection: FAILED</p>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    exit;
}

// Check if admin_users table exists
echo "<h2>Checking Admin Users Table...</h2>";
try {
    $query = "SHOW TABLES LIKE 'admin_users'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ admin_users table exists</p>";
        
        // Count admin users
        $count_query = "SELECT COUNT(*) as count FROM admin_users";
        $count_stmt = $conn->prepare($count_query);
        $count_stmt->execute();
        $count = $count_stmt->fetch()['count'];
        echo "<p>Found $count admin users</p>";
        
    } else {
        echo "<p style='color: red;'>✗ admin_users table does not exist</p>";
        echo "<p>Please create the admin_users table first!</p>";
        
        // Show create table SQL
        echo "<h3>Run this SQL to create the table:</h3>";
        echo "<pre style='background: #f4f4f4; padding: 10px;'>";
        echo htmlspecialchars("
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);");
        echo "</pre>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Table check failed: " . $e->getMessage() . "</p>";
    exit;
}

// List all admin users
echo "<h2>Current Admin Users:</h2>";
try {
    $query = "SELECT id, username, email, role, is_active, created_at, last_login FROM admin_users";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Active</th><th>Created</th><th>Last Login</th></tr>";
        foreach ($users as $user) {
            $active_color = $user['is_active'] ? 'green' : 'red';
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "<td style='color: $active_color;'>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . date('Y-m-d H:i', strtotime($user['created_at'])) . "</td>";
            echo "<td>" . ($user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No admin users found. Create one below.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error fetching users: " . $e->getMessage() . "</p>";
}

// Test login functionality
if (isset($_POST['test_login'])) {
    echo "<h2>Login Test Results:</h2>";
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        // Test the exact same query as AdminAuth
        $query = "SELECT id, username, email, password, role, is_active FROM admin_users WHERE (username = ? OR email = ?) AND is_active = 1";
        $stmt = $conn->prepare($query);
        $stmt->execute([$username, $username]);
        
        if ($stmt->rowCount() > 0) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p style='color: green;'>✓ User found: " . htmlspecialchars($admin['username']) . "</p>";
            echo "<p>Role: " . $admin['role'] . "</p>";
            echo "<p>Active: " . ($admin['is_active'] ? 'Yes' : 'No') . "</p>";
            
            if (password_verify($password, $admin['password'])) {
                echo "<p style='color: green; font-weight: bold;'>✓ PASSWORD VERIFICATION: SUCCESS</p>";
                echo "<p>Login should work! Try logging in normally.</p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>✗ PASSWORD VERIFICATION: FAILED</p>";
                echo "<p>The password you entered doesn't match the stored hash.</p>";
                echo "<p>Stored hash: <code>" . $admin['password'] . "</code></p>";
            }
        } else {
            echo "<p style='color: red; font-weight: bold;'>✗ USER NOT FOUND</p>";
            echo "<p>No active admin user found with username/email: " . htmlspecialchars($username) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Login test error: " . $e->getMessage() . "</p>";
    }
}

// Create admin user
if (isset($_POST['create_admin'])) {
    echo "<h2>Creating Admin User...</h2>";
    
    $username = $_POST['new_username'];
    $email = $_POST['new_email'];
    $password = $_POST['new_password'];
    
    try {
        // Check if username or email already exists
        $check_query = "SELECT COUNT(*) as count FROM admin_users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->execute([$username, $email]);
        
        if ($check_stmt->fetch()['count'] > 0) {
            echo "<p style='color: red;'>✗ Username or email already exists</p>";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert admin user
            $insert_query = "INSERT INTO admin_users (username, email, password, role, is_active) VALUES (?, ?, ?, 'super_admin', 1)";
            $insert_stmt = $conn->prepare($insert_query);
            
            if ($insert_stmt->execute([$username, $email, $hashed_password])) {
                echo "<p style='color: green; font-weight: bold;'>✓ Admin user created successfully!</p>";
                echo "<p><strong>Username:</strong> " . htmlspecialchars($username) . "</p>";
                echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
                echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
                echo "<p><a href='login.php' style='color: blue; text-decoration: underline;'>Go to Login Page</a></p>";
            } else {
                echo "<p style='color: red;'>✗ Failed to create admin user</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Create user error: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login Debug</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            line-height: 1.6;
        }
        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin: 10px 0;
        }
        th, td { 
            padding: 8px 12px; 
            text-align: left; 
            border: 1px solid #ddd;
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
        }
        .form-section { 
            margin-top: 30px; 
            padding: 20px; 
            border: 2px solid #ccc; 
            border-radius: 5px;
            background: #f9f9f9;
        }
        .form-section h3 {
            margin-top: 0;
            color: #333;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 300px;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background: #0056b3;
        }
        .create-btn {
            background: #28a745;
        }
        .create-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    
    <div class="form-section">
        <h3>🔍 Test Login</h3>
        <form method="POST">
            <p>
                <label><strong>Username or Email:</strong></label><br>
                <input type="text" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </p>
            <p>
                <label><strong>Password:</strong></label><br>
                <input type="password" name="password" required>
            </p>
            <p>
                <button type="submit" name="test_login">🧪 Test Login</button>
            </p>
        </form>
    </div>
    
    <div class="form-section">
        <h3>➕ Create New Admin User</h3>
        <form method="POST">
            <p>
                <label><strong>Username:</strong></label><br>
                <input type="text" name="new_username" required>
            </p>
            <p>
                <label><strong>Email:</strong></label><br>
                <input type="email" name="new_email" required>
            </p>
            <p>
                <label><strong>Password:</strong></label><br>
                <input type="password" name="new_password" required minlength="6">
            </p>
            <p>
                <button type="submit" name="create_admin" class="create-btn">➕ Create Admin User</button>
            </p>
        </form>
    </div>
    
    <div style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-left: 4px solid #007bff;">
        <h3>🛠️ Quick Fix Steps:</h3>
        <ol>
            <li>First, create an admin user using the form above</li>
            <li>Test the login with the debug form</li>
            <li>If successful, try logging in normally at <a href="login.php">login.php</a></li>
            <li>If issues persist, check your database configuration in <code>config/database.php</code></li>
        </ol>
    </div>
    
</body>
</html>
