# Document Vault System

A complete web-based document management system built with HTML, CSS, JavaScript, PHP, and MySQL. This system provides Google Drive-like functionality for file upload, storage, organization, and sharing with secure user authentication.

## Features

- **User Authentication**: Secure login and registration system with password hashing
- **File Upload**: Drag-and-drop file upload with progress tracking
- **File Management**: Upload, download, delete, and organize files
- **Folder Organization**: Create and manage folder structures
- **File Sharing**: Share files with other users (basic implementation)
- **Responsive Design**: Mobile-friendly interface
- **Security**: Input validation, file type restrictions, and secure file storage
- **Search Functionality**: Find files and folders quickly
- **Multiple View Options**: Grid and list view for files

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
│   └── FolderManager.php
├── uploads/
└── sql/
    └── database.sql
```

### 2. Database Setup

1. Create a MySQL database named `document_vault`
2. Import the SQL file or run the SQL commands from `sql/database.sql`
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

### 4. Configuration

1. Update the database configuration in `config/database.php`
2. Ensure the `uploads/` directory exists and is writable
3. Configure your web server to serve the application

### 5. Default User

The system includes a default admin user:
- **Email**: admin@example.com
- **Password**: admin123

## Usage

### Getting Started

1. Navigate to `http://your-domain.com/document_vault/login.html`
2. Login with the default admin credentials or register a new account
3. Start uploading and organizing your files

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
- **Size Limits**: Maximum file size of 50MB (configurable)
- **Secure Storage**: Files stored outside web-accessible directory
- **Session Management**: Secure PHP session handling
- **Input Sanitization**: All user inputs are validated and sanitized

## File Type Support

### Supported File Types
- **Images**: JPEG, PNG, GIF, WebP
- **Documents**: PDF, TXT, DOC, DOCX
- **Spreadsheets**: XLS, XLSX, CSV
- **Presentations**: PPT, PPTX
- **Archives**: ZIP

### File Size Limits
- Maximum individual file size: 50MB
- Total storage: Limited by server disk space

## API Endpoints

The system includes RESTful API endpoints:

- `POST /api/login.php` - User authentication
- `POST /api/register.php` - User registration
- `POST /api/logout.php` - User logout
- `GET /api/check_auth.php` - Authentication status
- `POST /api/upload.php` - File upload
- `GET /api/get_files.php` - Retrieve files/folders
- `GET /api/download.php` - Download files
- `DELETE /api/delete.php` - Delete files
- `POST /api/create_folder.php` - Create folders

## Troubleshooting

### Common Issues

**404 Errors for API endpoints**
- Ensure all API files are in the `api/` directory
- Check file permissions
- Verify web server configuration

**File Upload Failures**
- Check PHP upload limits in php.ini:
  ```ini
  upload_max_filesize = 50M
  post_max_size = 50M
  max_execution_time = 300
  ```
- Ensure uploads directory is writable

**Database Connection Issues**
- Verify database credentials in `config/database.php`
- Ensure MySQL service is running
- Check database exists and tables are created

**Login Issues**
- Clear browser cache and cookies
- Check session configuration
- Verify database connection

## Customization

### Changing File Size Limits

Edit the `$max_file_size` property in `classes/FileManager.php`:

```php
private $max_file_size = 100 * 1024 * 1024; // 100MB
```

### Adding File Types

Update the `$allowed_types` array in `classes/FileManager.php`:

```php
private $allowed_types = [
    'image/jpeg',
    'application/pdf',
    // Add more MIME types
];
```

### Styling Customization

Modify `css/styles.css` to change the appearance:
- Colors and themes
- Layout and spacing
- Responsive breakpoints

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Security Considerations

- Always use HTTPS in production
- Regularly update dependencies
- Monitor file upload activity
- Implement rate limiting for production use
- Consider adding two-factor authentication
- Regular security audits recommended

## License

This project is open source. Feel free to modify and distribute according to your needs.

## Support

For issues and questions:
1. Check the troubleshooting section
2. Review server error logs
3. Ensure all requirements are met
4. Verify file permissions and database connectivity

## Version History

- **v1.0.0** - Initial release with core functionality
  - User authentication
  - File upload/download
  - Folder management
  - Responsive design

***
