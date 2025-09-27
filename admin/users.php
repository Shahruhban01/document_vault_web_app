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
$users = $adminManager->getAllUsers($page, 20, $search);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
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
                        <h2 class="table-title">User Management</h2>
                        <div class="table-actions">
                            <input type="text" class="search-box" placeholder="Search users..." 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   onkeyup="searchUsers(this.value)">
                            <button class="btn btn-primary" onclick="showAddUserModal()">
                                <i class="fas fa-plus"></i> Add User
                            </button>
                        </div>
                    </div>
                    
                    <?php if ($users['success'] && count($users['users']) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Files</th>
                                <th>Storage Used</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users['users'] as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo number_format($user['file_count']); ?></td>
                                <td><?php echo formatBytes($user['total_storage'] ?: 0); ?></td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                                <td class="table-actions-cell">
                                    <button class="btn btn-secondary action-btn-small" 
                                            onclick="viewUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-primary action-btn-small" 
                                            onclick="editUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger action-btn-small" 
                                            onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if (isset($users['pages']) && $users['pages'] > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $users['pages']; $i++): ?>
                        <button class="<?php echo $i == $page ? 'active' : ''; ?>" 
                                onclick="changePage(<?php echo $i; ?>)">
                            <?php echo $i; ?>
                        </button>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <p>No users found</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- User Details Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>User Details</h2>
                <span class="close" onclick="closeModal('userModal')">&times;</span>
            </div>
            <div class="modal-body" id="userModalBody">
                <!-- User details will be loaded here -->
            </div>
        </div>
    </div>
    
    <div id="notificationContainer"></div>
    
    <script src="js/admin.js"></script>
    <script>
        function searchUsers(query) {
            const url = new URL(window.location);
            if (query) {
                url.searchParams.set('search', query);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }
        
        function changePage(page) {
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            window.location.href = url.toString();
        }
        
        async function deleteUser(userId, username) {
            if (!confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone.`)) {
                return;
            }
            
            try {
                const response = await fetch(`api/manage_users.php?action=delete&id=${userId}`, {
                    method: 'DELETE'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('User deleted successfully', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                showNotification('Error deleting user', 'error');
            }
        }
        
        async function viewUser(userId) {
            try {
                const response = await fetch(`api/manage_users.php?action=view&id=${userId}`);
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('userModalBody').innerHTML = `
                        <div class="user-details">
                            <h3>${result.user.username}</h3>
                            <p><strong>Email:</strong> ${result.user.email}</p>
                            <p><strong>Files:</strong> ${result.user.file_count}</p>
                            <p><strong>Storage Used:</strong> ${formatBytes(result.user.total_storage || 0)}</p>
                            <p><strong>Joined:</strong> ${new Date(result.user.created_at).toLocaleDateString()}</p>
                            <p><strong>Last Updated:</strong> ${result.user.updated_at ? new Date(result.user.updated_at).toLocaleDateString() : 'Never'}</p>
                        </div>
                    `;
                    document.getElementById('userModal').style.display = 'block';
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                showNotification('Error loading user details', 'error');
            }
        }
        
        function editUser(userId) {
            showNotification('Edit user feature coming soon', 'info');
        }
        
        function showAddUserModal() {
            showNotification('Add user feature coming soon', 'info');
        }
        
        // Helper function for formatting bytes in JavaScript
        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    </script>
</body>
</html>
