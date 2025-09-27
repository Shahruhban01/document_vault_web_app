<?php
session_start();
require_once '../classes/AdminAuth.php';
require_once '../classes/AdminManager.php';

$auth = new AdminAuth();
if (!$auth->isAdminAuthenticated()) {
    header('Location: login.php');
    exit;
}

$adminManager = new AdminManager();
$page = $_GET['page'] ?? 1;
$logs = $adminManager->getSystemLogs($page, 50);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - Admin Panel</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="admin-content">
                <div class="data-table">
                    <div class="table-header">
                        <h2 class="table-title">System Logs</h2>
                        <div class="table-actions">
                            <button class="btn btn-danger" onclick="clearLogs()">
                                <i class="fas fa-trash"></i> Clear Logs
                            </button>
                        </div>
                    </div>
                    
                    <?php if ($logs['success'] && count($logs['logs']) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Table</th>
                                <th>Record ID</th>
                                <th>IP Address</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs['logs'] as $log): ?>
                            <tr onclick="viewLogDetails(<?php echo $log['id']; ?>)" style="cursor: pointer;">
                                <td><?php echo $log['id']; ?></td>
                                <td><?php echo htmlspecialchars($log['admin_username'] ?? 'System'); ?></td>
                                <td>
                                    <span class="action-badge action-<?php echo str_replace(['_', ' '], '-', strtolower($log['action'])); ?>">
                                        <?php echo htmlspecialchars($log['action']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['table_affected'] ?? 'N/A'); ?></td>
                                <td><?php echo $log['record_id'] ?? 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-list"></i>
                        <p>No logs found</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div id="notificationContainer"></div>
    
    <script src="js/admin.js"></script>
    <script>
        function viewLogDetails(logId) {
            showNotification('Log details feature coming soon', 'info');
        }
        
        async function clearLogs() {
            if (!confirm('Are you sure you want to clear all system logs? This action cannot be undone.')) {
                return;
            }
            
            try {
                const response = await fetch('api/system_settings.php?action=clear_logs', {
                    method: 'DELETE'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Logs cleared successfully', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                showNotification('Error clearing logs', 'error');
            }
        }
    </script>
    
    <style>
        .action-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .action-login,
        .action-admin-login {
            background: #d4edda;
            color: #155724;
        }
        
        .action-logout,
        .action-admin-logout {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-delete,
        .action-admin-delete-file {
            background: #f5c6cb;
            color: #721c24;
        }
        
        .action-update,
        .action-update-setting {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .action-create {
            background: #d4edda;
            color: #155724;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
    </style>
</body>
</html>
