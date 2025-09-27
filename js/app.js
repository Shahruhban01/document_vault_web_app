class DocumentVault {
    constructor() {
        this.currentFolder = null;
        this.files = [];
        this.folders = [];
        this.currentView = 'grid';
        this.init();
    }
    
    init() {
        this.checkAuth();
        this.setupEventListeners();
        this.loadFiles();
    }
    
    async checkAuth() {
        try {
            const response = await fetch('api/check_auth.php');
            const data = await response.json();
            
            if (!data.authenticated) {
                window.location.href = 'login.html';
            } else {
                document.getElementById('username').textContent = data.username;
            }
        } catch (error) {
            console.error('Auth check failed:', error);
            window.location.href = 'login.html';
        }
    }
    
    setupEventListeners() {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        
        // Drag and drop functionality
        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', this.handleDragOver.bind(this));
        dropZone.addEventListener('dragleave', this.handleDragLeave.bind(this));
        dropZone.addEventListener('drop', this.handleDrop.bind(this));
        
        fileInput.addEventListener('change', (e) => {
            this.uploadFiles(e.target.files);
        });
        
        // Modal close on outside click
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeAllModals();
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });
    }
    
    handleDragOver(e) {
        e.preventDefault();
        e.currentTarget.classList.add('dragover');
    }
    
    handleDragLeave(e) {
        e.preventDefault();
        e.currentTarget.classList.remove('dragover');
    }
    
    handleDrop(e) {
        e.preventDefault();
        e.currentTarget.classList.remove('dragover');
        const files = e.dataTransfer.files;
        this.uploadFiles(files);
    }
    
    async uploadFiles(files) {
        if (files.length === 0) return;
        
        const progressContainer = document.getElementById('progressContainer');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');
        
        progressContainer.style.display = 'block';
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const formData = new FormData();
            formData.append('file', file);
            formData.append('folder_id', this.currentFolder);
            
            try {
                const response = await fetch('api/upload.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.showNotification(`${file.name} uploaded successfully`, 'success');
                } else {
                    this.showNotification(`Failed to upload ${file.name}: ${result.message}`, 'error');
                }
                
                // Update progress
                const progress = ((i + 1) / files.length) * 100;
                progressFill.style.width = progress + '%';
                progressText.textContent = Math.round(progress) + '%';
                
            } catch (error) {
                this.showNotification(`Upload failed: ${error.message}`, 'error');
            }
        }
        
        setTimeout(() => {
            progressContainer.style.display = 'none';
            this.closeUploadModal();
            this.loadFiles();
        }, 1000);
    }
    
    async loadFiles() {
        try {
            const url = `api/get_files.php${this.currentFolder ? '?folder_id=' + this.currentFolder : ''}`;
            
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.success) {
                this.files = data.files || [];
                this.folders = data.folders || [];
                this.renderFiles();
                this.updateBreadcrumb();
            } else {
                this.showNotification('Error loading files: ' + data.message, 'error');
            }
        } catch (error) {
            this.showNotification('Error loading files: ' + error.message, 'error');
        }
    }
    
    renderFiles() {
        const fileGrid = document.getElementById('fileGrid');
        fileGrid.innerHTML = '';
        
        if (this.folders.length === 0 && this.files.length === 0) {
            fileGrid.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>No files or folders yet</p>
                    <p>Upload some files to get started</p>
                </div>
            `;
            return;
        }
        
        // Render folders first
        this.folders.forEach(folder => {
            const folderElement = this.createFolderElement(folder);
            fileGrid.appendChild(folderElement);
        });
        
        // Render files
        this.files.forEach(file => {
            const fileElement = this.createFileElement(file);
            fileGrid.appendChild(fileElement);
        });
    }
    
    createFolderElement(folder) {
        const div = document.createElement('div');
        div.className = 'file-item folder-item';
        div.innerHTML = `
            <div class="file-icon">
                <i class="fas fa-folder" style="color: #ffd700;"></i>
            </div>
            <div class="file-name">${folder.name}</div>
            <div class="file-actions">
                <button onclick="app.navigateToFolder(${folder.id})" title="Open Folder">
                    <i class="fas fa-folder-open"></i>
                </button>
                <button onclick="app.deleteFolder(${folder.id})" title="Delete Folder">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        // Double click to open folder
        div.addEventListener('dblclick', () => {
            this.navigateToFolder(folder.id);
        });
        
        return div;
    }
    
    createFileElement(file) {
        const div = document.createElement('div');
        div.className = 'file-item';
        div.innerHTML = `
            <div class="file-icon">${this.getFileIcon(file.mime_type)}</div>
            <div class="file-name" title="${file.original_name}">${file.original_name}</div>
            <div class="file-size">${this.formatFileSize(file.file_size)}</div>
            <div class="file-date">${this.formatDate(file.created_at)}</div>
            <div class="file-actions">
                <button onclick="app.downloadFile(${file.id})" title="Download">
                    <i class="fas fa-download"></i>
                </button>
                <button onclick="app.shareFile(${file.id})" title="Share">
                    <i class="fas fa-share"></i>
                </button>
                <button onclick="app.deleteFile(${file.id})" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        return div;
    }
    
    getFileIcon(mimeType) {
        const iconMap = {
            'application/pdf': '<i class="fas fa-file-pdf" style="color: #e74c3c;"></i>',
            'image/jpeg': '<i class="fas fa-file-image" style="color: #3498db;"></i>',
            'image/jpg': '<i class="fas fa-file-image" style="color: #3498db;"></i>',
            'image/png': '<i class="fas fa-file-image" style="color: #3498db;"></i>',
            'image/gif': '<i class="fas fa-file-image" style="color: #3498db;"></i>',
            'image/webp': '<i class="fas fa-file-image" style="color: #3498db;"></i>',
            'text/plain': '<i class="fas fa-file-alt" style="color: #95a5a6;"></i>',
            'text/csv': '<i class="fas fa-file-csv" style="color: #27ae60;"></i>',
            'application/msword': '<i class="fas fa-file-word" style="color: #2980b9;"></i>',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': '<i class="fas fa-file-word" style="color: #2980b9;"></i>',
            'application/vnd.ms-excel': '<i class="fas fa-file-excel" style="color: #27ae60;"></i>',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': '<i class="fas fa-file-excel" style="color: #27ae60;"></i>',
            'application/vnd.ms-powerpoint': '<i class="fas fa-file-powerpoint" style="color: #e67e22;"></i>',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation': '<i class="fas fa-file-powerpoint" style="color: #e67e22;"></i>',
            'application/zip': '<i class="fas fa-file-archive" style="color: #9b59b6;"></i>',
            'application/x-zip-compressed': '<i class="fas fa-file-archive" style="color: #9b59b6;"></i>',
        };
        
        return iconMap[mimeType] || '<i class="fas fa-file" style="color: #95a5a6;"></i>';
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    }
    
    updateBreadcrumb() {
        const breadcrumb = document.getElementById('breadcrumb');
        breadcrumb.innerHTML = '<span onclick="app.navigateToFolder(null)">Home</span>';
        
        if (this.currentFolder) {
            breadcrumb.innerHTML += ' / <span>Current Folder</span>';
        }
    }
    
    navigateToFolder(folderId) {
        this.currentFolder = folderId;
        this.loadFiles();
    }
    
    async deleteFile(fileId) {
        if (!confirm('Are you sure you want to delete this file?')) {
            return;
        }
        
        try {
            const response = await fetch(`api/delete.php?id=${fileId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('File deleted successfully', 'success');
                this.loadFiles();
            } else {
                this.showNotification('Error deleting file: ' + result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Error deleting file: ' + error.message, 'error');
        }
    }
    
    async deleteFolder(folderId) {
        if (!confirm('Are you sure you want to delete this folder and all its contents?')) {
            return;
        }
        
        try {
            const response = await fetch(`api/delete_folder.php?id=${folderId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Folder deleted successfully', 'success');
                this.loadFiles();
            } else {
                this.showNotification('Error deleting folder: ' + result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Error deleting folder: ' + error.message, 'error');
        }
    }
    
    downloadFile(fileId) {
        window.open(`api/download.php?id=${fileId}`, '_blank');
    }
    
    shareFile(fileId) {
        this.showNotification('File sharing feature coming soon!', 'info');
    }
    
    toggleView(viewType) {
        this.currentView = viewType;
        const fileGrid = document.getElementById('fileGrid');
        const viewButtons = document.querySelectorAll('.view-btn');
        
        viewButtons.forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        if (viewType === 'list') {
            fileGrid.classList.add('list-view');
        } else {
            fileGrid.classList.remove('list-view');
        }
    }
    
    showNotification(message, type = 'info') {
        const container = document.getElementById('notificationContainer') || this.createNotificationContainer();
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        
        const iconMap = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };
        
        notification.innerHTML = `
            <i class="fas ${iconMap[type] || iconMap.info}"></i>
            <span>${message}</span>
            <button class="notification-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        container.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
    
    createNotificationContainer() {
        const container = document.createElement('div');
        container.id = 'notificationContainer';
        container.className = 'notification-container';
        document.body.appendChild(container);
        return container;
    }
    
    closeAllModals() {
        this.closeUploadModal();
        this.closeCreateFolderModal();
    }
    
    closeUploadModal() {
        const modal = document.getElementById('uploadModal');
        modal.style.display = 'none';
        
        // Reset form
        const fileInput = document.getElementById('fileInput');
        fileInput.value = '';
        
        const progressContainer = document.getElementById('progressContainer');
        progressContainer.style.display = 'none';
    }
    
    closeCreateFolderModal() {
        const modal = document.getElementById('createFolderModal');
        modal.style.display = 'none';
        
        // Reset form
        const folderNameInput = document.getElementById('folderName');
        folderNameInput.value = '';
    }
}

// Global functions for HTML onclick events
function showUploadModal() {
    document.getElementById('uploadModal').style.display = 'block';
}

function showCreateFolderModal() {
    document.getElementById('createFolderModal').style.display = 'block';
}

function closeUploadModal() {
    app.closeUploadModal();
}

function closeCreateFolderModal() {
    app.closeCreateFolderModal();
}

function navigateToFolder(folderId) {
    app.navigateToFolder(folderId);
}

function toggleView(viewType) {
    app.toggleView(viewType);
}

async function createFolder() {
    const folderName = document.getElementById('folderName').value.trim();
    
    if (!folderName) {
        app.showNotification('Please enter a folder name', 'warning');
        return;
    }
    
    try {
        const response = await fetch('api/create_folder.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: folderName,
                parent_id: app.currentFolder
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            app.showNotification('Folder created successfully', 'success');
            app.closeCreateFolderModal();
            app.loadFiles();
        } else {
            app.showNotification('Error creating folder: ' + result.message, 'error');
        }
    } catch (error) {
        app.showNotification('Error creating folder: ' + error.message, 'error');
    }
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        fetch('api/logout.php', { method: 'POST' })
            .then(() => {
                window.location.href = 'login.html';
            })
            .catch(() => {
                window.location.href = 'login.html';
            });
    }
}

// Initialize app when DOM is loaded
let app;
document.addEventListener('DOMContentLoaded', function() {
    app = new DocumentVault();
});

// Handle enter key in folder name input
document.addEventListener('DOMContentLoaded', function() {
    const folderNameInput = document.getElementById('folderName');
    if (folderNameInput) {
        folderNameInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                createFolder();
            }
        });
    }
});
