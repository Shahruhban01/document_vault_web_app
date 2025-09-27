<?php
session_start();
require_once '../classes/AdminAuth.php';
require_once '../classes/AdminManager.php';

$auth = new AdminAuth();
if (!$auth->isAdminAuthenticated()) {
    header('Location: login.php');
    exit;
}

if (!$auth->hasPermission('super_admin')) {
    header('Location: index.php?error=insufficient_permissions');
    exit;
}

$adminManager = new AdminManager();
$settings = $adminManager->getSettings();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if ($key !== 'submit') {
            $type = 'string';
            if (is_numeric($value)) {
                $type = 'integer';
            } elseif (in_array(strtolower($value), ['true', 'false'])) {
                $type = 'boolean';
            } elseif (is_array(json_decode($value, true))) {
                $type = 'json';
            }
            
            $result = $adminManager->updateSetting($key, $value, $type);
        }
    }
    
    header('Location: settings.php?notification=Settings updated successfully&type=success');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Admin Panel</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="admin-content">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>System Settings</h2>
                    </div>
                    
                    <?php if ($settings['success']): ?>
                    <form method="POST" class="settings-form">
                        <div class="settings-grid">
                            <?php foreach ($settings['settings'] as $setting): ?>
                            <div class="setting-item">
                                <label for="<?php echo $setting['setting_key']; ?>">
                                    <?php echo ucwords(str_replace('_', ' ', $setting['setting_key'])); ?>
                                </label>
                                
                                <?php if ($setting['setting_type'] === 'boolean'): ?>
                                    <select name="<?php echo $setting['setting_key']; ?>" id="<?php echo $setting['setting_key']; ?>">
                                        <option value="true" <?php echo $setting['setting_value'] === 'true' ? 'selected' : ''; ?>>Enabled</option>
                                        <option value="false" <?php echo $setting['setting_value'] === 'false' ? 'selected' : ''; ?>>Disabled</option>
                                    </select>
                                <?php elseif ($setting['setting_type'] === 'json'): ?>
                                    <textarea name="<?php echo $setting['setting_key']; ?>" 
                                              id="<?php echo $setting['setting_key']; ?>" 
                                              rows="4"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                                <?php else: ?>
                                    <input type="<?php echo $setting['setting_type'] === 'integer' ? 'number' : 'text'; ?>" 
                                           name="<?php echo $setting['setting_key']; ?>" 
                                           id="<?php echo $setting['setting_key']; ?>"
                                           value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                <?php endif; ?>
                                
                                <?php if ($setting['description']): ?>
                                    <small class="setting-description"><?php echo htmlspecialchars($setting['description']); ?></small>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-cog"></i>
                        <p>Failed to load settings</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div id="notificationContainer"></div>
    
    <script src="js/admin.js"></script>
    <style>
        .settings-form {
            padding: 25px;
        }
        
        .settings-grid {
            display: grid;
            gap: 25px;
        }
        
        .setting-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .setting-item label {
            font-weight: 600;
            color: #333;
        }
        
        .setting-item input,
        .setting-item select,
        .setting-item textarea {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .setting-item textarea {
            resize: vertical;
            font-family: monospace;
        }
        
        .setting-description {
            color: #666;
            font-size: 12px;
            font-style: italic;
        }
        
        .form-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
        }
    </style>
</body>
</html>
