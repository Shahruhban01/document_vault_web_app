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
$folders = $adminManager->getAllFolders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Folder Management - Admin Panel</title>
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
                        <h2 class="table-title">Folder Management</h2>
                    </div>
                    
                    <?php if ($folders['success'] && count($folders['folders']) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Folder Name</th>
                                <th>Owner</th>
                                <th>Parent Folder</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($folders['folders'] as $folder): ?>
                            <tr>
                                <td><?php echo $folder['id']; ?></td>
                                <td><?php echo htmlspecialchars($folder['name']); ?></td>
                                <td><?php echo htmlspecialchars($folder['username']); ?></td>
                                <td><?php echo $folder['parent_name'] ? htmlspecialchars($folder['parent_name']) : 'Root'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($folder['created_at'])); ?></td>
                                <td class="table-actions-cell">
                                    <button class="btn btn-danger action-btn-small" 
                                            onclick="deleteFolder(<?php echo $folder['id']; ?>, '<?php echo htmlspecialchars($folder['name']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-folder"></i>
                        <p>No folders found</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div id="notificationContainer"></div>
    
    <script src="js/admin.js"></script>
    <script>
        async function deleteFolder(folderId, folderName) {
            if (!confirm(`Are you sure you want to delete folder "${folderName}" and all its contents?`)) {
                return;
            }
            
            try {
                const response = await fetch(`api/manage_folders.php?action=delete&id=${folderId}`, {
                    method: 'DELETE'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Folder deleted successfully', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                showNotification('Error deleting folder', 'error');
            }
        }
    </script>
</body>
</html>
