<div class="admin-header">
    <div class="header-left">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <h1><?php echo ucfirst(str_replace('.php', '', basename($_SERVER['PHP_SELF']))); ?></h1>
    </div>
    
    <div class="header-right">
        <div class="admin-menu">
            <div class="admin-info">
                <span class="admin-name"><?php echo $_SESSION['admin_username']; ?></span>
                <span class="admin-role"><?php echo ucfirst(str_replace('_', ' ', $_SESSION['admin_role'])); ?></span>
            </div>
            <div class="admin-actions">
                <a href="../index.html" target="_blank" class="btn btn-secondary">
                    <i class="fas fa-external-link-alt"></i> View Site
                </a>
                <a href="api/admin_auth.php?action=logout" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>
