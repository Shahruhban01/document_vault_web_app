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
$reports = $adminManager->getDetailedReports();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Panel</title>
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
                    <h1>Reports & Analytics</h1>
                    <p>Detailed system reports and usage analytics</p>
                </div>
                
                <div class="reports-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Storage Usage by User</h2>
                        </div>
                        <div class="card-content">
                            <div class="report-list">
                                <?php if ($reports['success'] && isset($reports['user_storage']) && count($reports['user_storage']) > 0): ?>
                                    <?php foreach ($reports['user_storage'] as $user): ?>
                                    <div class="report-item">
                                        <div class="report-info">
                                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                            <small><?php echo number_format($user['file_count']); ?> files</small>
                                        </div>
                                        <div class="report-value">
                                            <?php echo formatBytes($user['total_storage'] ?: 0); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No data available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Upload Activity (Last 30 Days)</h2>
                        </div>
                        <div class="card-content">
                            <div class="report-list">
                                <?php if ($reports['success'] && isset($reports['upload_activity']) && count($reports['upload_activity']) > 0): ?>
                                    <?php foreach ($reports['upload_activity'] as $activity): ?>
                                    <div class="report-item">
                                        <div class="report-info">
                                            <strong><?php echo formatDate($activity['date']); ?></strong>
                                        </div>
                                        <div class="report-value">
                                            <?php echo number_format($activity['uploads']); ?> uploads
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No data available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>File Type Distribution</h2>
                        </div>
                        <div class="card-content">
                            <div class="report-list">
                                <?php if ($stats['success'] && isset($stats['stats']['file_types']) && count($stats['stats']['file_types']) > 0): ?>
                                    <?php foreach ($stats['stats']['file_types'] as $type): ?>
                                    <div class="report-item">
                                        <div class="report-info">
                                            <strong><?php echo htmlspecialchars($type['mime_type']); ?></strong>
                                            <small><?php echo number_format($type['count']); ?> files</small>
                                        </div>
                                        <div class="report-value">
                                            <?php echo formatBytes($type['size']); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No data available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>System Health</h2>
                        </div>
                        <div class="card-content">
                            <div class="health-metrics">
                                <div class="health-item">
                                    <span class="health-label">Disk Usage:</span>
                                    <span class="health-value">
                                        <?php echo formatBytes($stats['stats']['total_storage'] ?? 0); ?>
                                    </span>
                                </div>
                                <div class="health-item">
                                    <span class="health-label">Total Files:</span>
                                    <span class="health-value">
                                        <?php echo number_format($stats['stats']['total_files'] ?? 0); ?>
                                    </span>
                                </div>
                                <div class="health-item">
                                    <span class="health-label">Active Users:</span>
                                    <span class="health-value">
                                        <?php echo number_format($stats['stats']['total_users'] ?? 0); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="report-actions">
                    <button class="btn btn-primary" onclick="exportReports()">
                        <i class="fas fa-download"></i> Export Reports
                    </button>
                    <button class="btn btn-secondary" onclick="refreshReports()">
                        <i class="fas fa-refresh"></i> Refresh Data
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div id="notificationContainer"></div>
    
    <script src="js/admin.js"></script>
    <script>
        function exportReports() {
            showNotification('Export feature coming soon', 'info');
        }
        
        function refreshReports() {
            location.reload();
        }
    </script>
    
    <style>
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .report-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .report-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .report-item:last-child {
            border-bottom: none;
        }
        
        .report-info strong {
            display: block;
            color: #333;
        }
        
        .report-info small {
            color: #666;
            font-size: 12px;
        }
        
        .report-value {
            font-weight: 600;
            color: #007bff;
        }
        
        .health-metrics {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .health-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .health-item:last-child {
            border-bottom: none;
        }
        
        .health-label {
            color: #666;
        }
        
        .health-value {
            font-weight: 600;
            color: #333;
        }
        
        .report-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
    </style>
</body>
</html>
