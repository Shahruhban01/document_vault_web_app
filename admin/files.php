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
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$files = $adminManager->getAllFiles($page, 20, $search);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Management - Admin Panel</title>
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
                        <h2 class="table-title">File Management</h2>
                        <div class="table-actions">
                            <input type="text" class="search-box" placeholder="Search files..." 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   onkeyup="searchFiles(this.value)">
                        </div>
                    </div>
                    
                    <?php if ($files['success'] && count($files['files']) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>File Name</th>
                                <th>Owner</th>
                                <th>Size</th>
                                <th>Type</th>
                                <th>Uploaded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files['files'] as $file): ?>
                            <tr>
                                <td><?php echo $file['id']; ?></td>
                                <td title="<?php echo htmlspecialchars($file['original_name']); ?>">
                                    <?php echo truncateString(htmlspecialchars($file['original_name']), 30); ?>
                                </td>
                                <td><?php echo htmlspecialchars($file['username']); ?></td>
                                <td><?php echo formatBytes($file['file_size']); ?></td>
                                <td><?php echo htmlspecialchars($file['mime_type']); ?></td>
                                <td><?php echo formatDate($file['created_at']); ?></td>
                                <td class="table-actions-cell">
                                    <button class="btn btn-secondary action-btn-small" 
                                            onclick="viewFile(<?php echo $file['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-primary action-btn-small" 
                                            onclick="downloadFile(<?php echo $file['id']; ?>)">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-danger action-btn-small" 
                                            onclick="deleteFile(<?php echo $file['id']; ?>, '<?php echo htmlspecialchars($file['original_name']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file"></i>
                        <p>No files found</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div id="notificationContainer"></div>
    
    <script src="js/admin.js"></script>
    <script>
        function searchFiles(query) {
            const url = new URL(window.location);
            if (query) {
                url.searchParams.set('search', query);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }
        
        async function deleteFile(fileId, fileName) {
            if (!confirm(`Are you sure you want to delete "${fileName}"?`)) {
                return;
            }
            
            try {
                const response = await fetch(`api/manage_files.php?action=delete&id=${fileId}`, {
                    method: 'DELETE'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('File deleted successfully', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                showNotification('Error deleting file', 'error');
            }
        }
        
        function downloadFile(fileId) {
            window.open(`../api/download.php?id=${fileId}`, '_blank');
        }
        
        function viewFile(fileId) {
            showNotification('File preview feature coming soon', 'info');
        }
    </script>
</body>
</html>
