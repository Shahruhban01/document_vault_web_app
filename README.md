# Document Vault System

A complete web-based document management system built with HTML, CSS, JavaScript, PHP, and MySQL. This system provides Google Drive-like functionality for file upload, storage, organization, and sharing with secure user authentication and comprehensive admin panel.

## Features

### User Features
- **User Authentication**: Secure login and registration system with password hashing
- **File Upload**: Drag-and-drop file upload with progress tracking
- **File Management**: Upload, download, delete, and organize files
- **Folder Organization**: Create and manage folder structures
- **File Sharing**: Share files with other users (basic implementation)
- **Responsive Design**: Mobile-friendly interface
- **Security**: Input validation, file type restrictions, and secure file storage
- **Search Functionality**: Find files and folders quickly
- **Multiple View Options**: Grid and list view for files

### Admin Features
- **Admin Dashboard**: Comprehensive overview with system statistics
- **User Management**: View, edit, and delete user accounts
- **File Management**: Monitor and manage all files across the system
- **Folder Management**: Oversee folder structures and organization
- **System Settings**: Configure file size limits, allowed types, and system preferences
- **Activity Logs**: Track all user and admin activities with detailed audit trails
- **Reports & Analytics**: Detailed reports on storage usage, file types, and user activity
- **Role-Based Access**: Support for multiple admin roles (super_admin, admin, moderator)
- **Security Monitoring**: Real-time monitoring of system health and security

## Requirements

### Server Requirements
- **PHP 7.4+** with MySQLi/PDO extensions enabled
- **MySQL 5.7+** or **MariaDB 10.2+**
- **Apache/Nginx** web server with mod_rewrite enabled
- **SSL Certificate** recommended for production (HTTPS)

### Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Installation

### 1. Download and Setup Files

Create the following directory structure in your web server's document root:

```
document_vault/
├── index.html
├── login.html
├── register.html
├── css/
│   └── styles.css
├── js/
│   └── app.js
├── api/
│   ├── check_auth.php
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── upload.php
│   ├── get_files.php
│   ├── download.php
│   ├── delete.php
│   ├── delete_folder.php
│   ├── create_folder.php
│   └── search.php
├── config/
│   └── database.php
├── classes/
│   ├── User.php
│   ├── FileManager.php
│   ├── FolderManager.php
│   ├── AdminAuth.php
│   └── AdminManager.php
├── admin/
│   ├── index.php
│   ├── login.php
│   ├── signup.php
│   ├── dashboard.php
│   ├── users.php
│   ├── files.php
│   ├── folders.php
│   ├── settings.php
│   ├── logs.php
│   ├── reports.php
│   ├── debug_login.php
│   ├── create_admin.php
│   ├── css/
│   │   └── admin.css
│   ├── js/
│   │   └── admin.js
│   ├── includes/
│   │   ├── sidebar.php
│   │   ├── header.php
│   │   └── functions.php
│   └── api/
│       ├── admin_auth.php
│       ├── manage_users.php
│       ├── manage_files.php
│       ├── manage_folders.php
│       ├── system_settings.php
│       └── get_statistics.php
├── uploads/
└── sql/
    ├── database.sql
    └── admin_tables.sql
```

### 2. Database Setup

1. Create a MySQL database named `document_vault`
2. Import the SQL files:
   - First run `sql/database.sql` for main tables
   - Then run `sql/admin_tables.sql` for admin functionality
3. Update database credentials in `config/database.php`:

```php
private $host = 'localhost';
private $db_name = 'document_vault';
private $username = 'your_username';
private $password = 'your_password';
```

### 3. File Permissions

Set proper permissions for the uploads directory:

```bash
chmod 755 uploads/
```

Ensure the web server has write permissions to the `uploads/` directory.

### 4. Admin Setup

1. Navigate to `http://your-domain.com/document_vault/admin/debug_login.php`
2. Use the debug tool to create your first admin account
3. Or run the admin signup at `http://your-domain.com/document_vault/admin/signup.php`
4. Login to the admin panel at `http://your-domain.com/document_vault/admin/login.php`

### 5. Configuration

1. Update the database configuration in `config/database.php`
2. Ensure the `uploads/` directory exists and is writable
3. Configure your web server to serve the application
4. Access admin panel to configure system settings

## Usage

### User Interface

1. Navigate to `http://your-domain.com/document_vault/login.html`
2. Register a new account or login with existing credentials
3. Start uploading and organizing your files

### Admin Interface

1. Navigate to `http://your-domain.com/document_vault/admin/login.php`
2. Login with admin credentials
3. Access dashboard for system overview
4. Manage users, files, and system settings

### File Upload

- **Drag & Drop**: Drag files directly onto the upload area
- **Click Upload**: Click the upload button and select files
- **Progress Tracking**: Monitor upload progress with the progress bar
- **File Types**: Supports images, documents, spreadsheets, presentations, and archives

### File Organization

- **Create Folders**: Use the "New Folder" button to organize files
- **Navigate**: Click on folders to browse contents
- **Breadcrumb**: Use the breadcrumb navigation to move between folders

### File Management

- **Download**: Click the download icon to save files locally
- **Delete**: Click the trash icon to remove files (confirmation required)
- **View Options**: Switch between grid and list view

## Security Features

- **Password Hashing**: User passwords are hashed using PHP's password_hash()
- **File Validation**: MIME type and file extension validation
- **Size Limits**: Maximum file size of 50MB (configurable via admin panel)
- **Secure Storage**: Files stored outside web-accessible directory
- **Session Management**: Secure PHP session handling
- **Input Sanitization**: All user inputs are validated and sanitized
- **Role-Based Access Control**: Different permission levels for admin users
- **Activity Logging**: Comprehensive audit trail of all system activities
- **Admin Authentication**: Separate secure authentication system for administrators

## File Type Support

### Supported File Types
- **Images**: JPEG, PNG, GIF, WebP
- **Documents**: PDF, TXT, DOC, DOCX
- **Spreadsheets**: XLS, XLSX, CSV
- **Presentations**: PPT, PPTX
- **Archives**: ZIP

### File Size Limits
- Maximum individual file size: 50MB (configurable via admin panel)
- Total storage: Limited by server disk space

## API Endpoints

### User API Endpoints
- `POST /api/login.php` - User authentication
- `POST /api/register.php` - User registration
- `POST /api/logout.php` - User logout
- `GET /api/check_auth.php` - Authentication status
- `POST /api/upload.php` - File upload
- `GET /api/get_files.php` - Retrieve files/folders
- `GET /api/download.php` - Download files
- `DELETE /api/delete.php` - Delete files
- `POST /api/create_folder.php` - Create folders

### Admin API Endpoints
- `GET /admin/api/admin_auth.php` - Admin authentication check
- `GET/POST/DELETE /admin/api/manage_users.php` - User management
- `GET/DELETE /admin/api/manage_files.php` - File management
- `GET/DELETE /admin/api/manage_folders.php` - Folder management
- `GET/POST /admin/api/system_settings.php` - System configuration
- `GET /admin/api/get_statistics.php` - System statistics

## Admin Panel Features

### Dashboard
- Real-time system statistics
- Storage usage overview
- Recent activity monitoring
- Quick action buttons

### User Management
- View all registered users
- Monitor user storage usage
- Delete user accounts
- Search and filter users

### File Management
- View all files across the system
- Delete files from admin panel
- Monitor file types and sizes
- Search files by name or owner

### System Settings
- Configure file size limits
- Manage allowed file types
- Enable/disable user registration
- Set maintenance mode

### Logs & Reports
- View detailed activity logs
- Generate usage reports
- Monitor system health
- Export data for analysis

## Troubleshooting

### Common Issues

**404 Errors for API endpoints**
- Ensure all API files are in the `api/` and `admin/api/` directories
- Check file permissions
- Verify web server configuration

**Admin Login Issues**
- Use the debug tool at `/admin/debug_login.php`
- Create admin account via `/admin/signup.php`
- Check database tables exist (run admin_tables.sql)

**File Upload Failures**
- Check PHP upload limits in php.ini:
  ```ini
  upload_max_filesize = 50M
  post_max_size = 50M
  max_execution_time = 300
  ```
- Ensure uploads directory is writable
- Check admin panel system settings

**Database Connection Issues**
- Verify database credentials in `config/database.php`
- Ensure MySQL service is running
- Check database exists and tables are created
- Run both SQL files (database.sql and admin_tables.sql)

**Function Redeclaration Errors**
- Ensure you're including `admin/includes/functions.php` only once
- Clear any duplicate function definitions

## Customization

### Changing File Size Limits

Use the admin panel Settings page or edit the database directly:

```sql
UPDATE system_settings SET setting_value = '104857600' WHERE setting_key = 'max_file_size';
```

### Adding File Types

Use the admin panel Settings page or update the allowed_file_types setting in the database.

### Styling Customization

Modify CSS files to change appearance:
- `css/styles.css` for user interface
- `admin/css/admin.css` for admin panel
- Both support responsive design and modern styling

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly (both user and admin interfaces)
5. Submit a pull request

## Security Considerations

- Always use HTTPS in production
- Regularly update dependencies
- Monitor file upload activity via admin panel
- Implement rate limiting for production use
- Consider adding two-factor authentication
- Regular security audits recommended
- Monitor system logs for suspicious activity
- Keep admin access restricted to authorized personnel

## License

This project is open source. Feel free to modify and distribute according to your needs.

## Support

For issues and questions:
1. Check the troubleshooting section
2. Use the admin debug tools (`/admin/debug_login.php`)
3. Review server error logs
4. Check admin panel system health
5. Ensure all requirements are met
6. Verify file permissions and database connectivity

## Version History

- **v2.0.0** - Major update with admin panel
  - Comprehensive admin dashboard
  - User and file management
  - System settings and configuration
  - Activity logging and reporting
  - Role-based access control
  - Debug tools for troubleshooting

- **v1.0.0** - Initial release with core functionality
  - User authentication
  - File upload/download
  - Folder management
  - Responsive design

## Demo Credentials

### User Account
- Register a new account or create via admin panel

### Admin Account
- Create via `/admin/signup.php` or `/admin/debug_login.php`
- First admin user gets super_admin privileges automatically

***

**Note**: This system is production-ready but should undergo additional security testing and configuration for enterprise deployments. The admin panel provides comprehensive control over all system aspects and should be secured appropriately.