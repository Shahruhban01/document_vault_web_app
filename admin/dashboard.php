<?php
session_start();
require_once '../classes/AdminAuth.php';
require_once '../classes/AdminManager.php';
require_once 'includes/functions.php'; // Include shared functions

$auth = new AdminAuth();
if (!$auth->isAdminAuthenticated()) {
    header('Location: login.php');
    exit;
}

$adminManager = new AdminManager();
$stats = $adminManager->getSystemStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Document Vault</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="admin-content">
                <div class="dashboard-header">
                    <h1>Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
                </div>
                
                <?php if ($stats['success']): ?>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($stats['stats']['total_users']); ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-file"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($stats['stats']['total_files']); ?></h3>
                            <p>Total Files</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-hdd"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo formatBytes($stats['stats']['total_storage']); ?></h3>
                            <p>Total Storage</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-upload"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($stats['stats']['recent_uploads']); ?></h3>
                            <p>Recent Uploads (7 days)</p>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>File Types Distribution</h2>
                        </div>
                        <div class="card-content">
                            <div class="file-types-list">
                                <?php if (isset($stats['stats']['file_types']) && count($stats['stats']['file_types']) > 0): ?>
                                    <?php foreach ($stats['stats']['file_types'] as $type): ?>
                                    <div class="file-type-item">
                                        <span class="file-type"><?php echo htmlspecialchars($type['mime_type']); ?></span>
                                        <span class="file-count"><?php echo number_format($type['count']); ?> files</span>
                                        <span class="file-size"><?php echo formatBytes($type['size']); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No file types found</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Quick Actions</h2>
                        </div>
                        <div class="card-content">
                            <div class="quick-actions">
                                <a href="users.php" class="action-btn">
                                    <i class="fas fa-users"></i>
                                    Manage Users
                                </a>
                                <a href="files.php" class="action-btn">
                                    <i class="fas fa-file"></i>
                                    Manage Files
                                </a>
                                <a href="settings.php" class="action-btn">
                                    <i class="fas fa-cog"></i>
                                    System Settings
                                </a>
                                <a href="logs.php" class="action-btn">
                                    <i class="fas fa-list"></i>
                                    View Logs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="dashboard-card">
                    <div class="card-content">
                        <div class="empty-state">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Failed to load dashboard statistics</p>
                            <p>Please check your database connection</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="js/admin.js"></script>
</body>
</html>
