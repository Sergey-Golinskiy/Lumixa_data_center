# Lumixa Manufacturing System (LMS)

A comprehensive web-based manufacturing management system for Lumixa lamp production. Built with PHP and MySQL, designed for easy deployment without Docker.

## Features

### Core Modules

1. **Authentication & Security**
   - User authentication with session management
   - Role-based access control (RBAC)
   - CSRF protection
   - Rate limiting for login attempts
   - Password policies and forced password change

2. **Admin Panel (No-code customization)**
   - User management
   - Role and permission management
   - Custom fields for entities
   - UI template configuration
   - Workflow status management
   - Audit log viewing
   - Backup and restore

3. **Warehouse & Inventory**
   - Items (SKU) management with types: Material, Component, Part, Consumable, Packaging
   - Lot/Batch tracking (especially for materials like plastic filament)
   - Multi-warehouse support
   - Stock levels: on_hand, reserved, available
   - Document-based accounting: Receipt, Issue, Transfer, Stocktake
   - Posting engine with transaction log
   - Partner (supplier/customer) management

4. **Product Catalog**
   - Products (lamp models)
   - Variants (color/size/version combinations)
   - Custom attributes per variant

5. **Bill of Materials (BOM)**
   - Revision management (Draft, Active, Archived)
   - Component lines with quantities and waste factors
   - Support for all material types

6. **Routing (Operation Cards)**
   - Revision management
   - Operation steps with instructions
   - Checklists for quality control
   - Time estimates (setup and run time)
   - File attachments for work instructions

7. **Production**
   - Production orders with status tracking
   - Task generation from routing
   - Task assignment and execution
   - Time tracking

8. **Print Queue**
   - Print job management
   - Material reservation system
   - Actual consumption tracking
   - Integration with warehouse for automatic stock updates

9. **Costing**
   - Planned cost calculation from BOM and routing
   - Actual cost tracking from production
   - Cost comparison and variance analysis

10. **Backup & Restore**
    - Database backup
    - File backup (uploads)
    - Restore functionality
    - Backup retention management

## Installation

### Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher / MariaDB 10.3+
- Apache with mod_rewrite or Nginx
- PHP Extensions: PDO, PDO_MySQL, JSON, ZIP, mbstring

### Setup Steps

1. **Upload files to server**
   ```
   Upload all files to your web hosting
   ```

2. **Configure web server**
   - Set document root to `/public` directory
   - Ensure `.htaccess` is processed (Apache) or configure Nginx appropriately

3. **Set permissions**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 public/uploads/
   ```

4. **Run Setup Wizard**
   - Navigate to `https://your-domain.com/setup`
   - Enter database credentials
   - Create admin user
   - Complete installation

### Nginx Configuration Example

```nginx
server {
    listen 80;
    server_name lms.lumixa.io;
    root /var/www/lms/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(ht|git) {
        deny all;
    }
}
```

## Directory Structure

```
lms/
├── app/
│   ├── Controllers/       # HTTP controllers
│   ├── Core/             # Framework core classes
│   ├── Helpers/          # Helper functions
│   ├── Middleware/       # HTTP middleware
│   ├── Models/           # Data models
│   ├── Repositories/     # Data access layer
│   ├── Services/         # Business logic services
│   ├── bootstrap.php     # Application bootstrap
│   └── routes.php        # Route definitions
├── config/
│   └── app.php           # Application configuration (generated)
├── database/
│   ├── migrations/       # SQL migration files
│   └── seeds/            # Initial data seeds
├── public/
│   ├── assets/           # CSS, JS, images
│   ├── uploads/          # User uploaded files
│   ├── .htaccess         # Apache rewrite rules
│   └── index.php         # Entry point
├── storage/
│   ├── backups/          # Backup files
│   ├── cache/            # Application cache
│   └── logs/             # Log files
├── views/
│   ├── layouts/          # Page layouts
│   ├── partials/         # Reusable view parts
│   └── [module]/         # Module-specific views
├── .gitignore
└── README.md
```

## User Roles

| Role | Description |
|------|-------------|
| Admin | Full system access including user management and backups |
| Accountant/Warehouse | Warehouse operations, documents, stock management |
| Manager | Products, BOM, routing, production planning, costing |
| Worker | Execute tasks, work with print queue |
| Viewer | Read-only access |

## API

The system provides internal API endpoints for AJAX operations:

- `GET /api/stock/check` - Check stock availability
- `GET /api/stock/available` - Get available stock
- `GET /api/items/search` - Search items
- `GET /api/variants/{id}/cost` - Get variant cost breakdown

## Security Features

- Prepared statements for all database queries (SQL injection prevention)
- CSRF token validation for all forms
- XSS protection through output escaping
- Secure session configuration
- Rate limiting for login attempts
- Audit logging for critical actions

## Development

### Code Standards

- PSR-4 autoloading
- Thin controllers, fat services pattern
- Repository pattern for data access
- Transaction support for critical operations

### Adding Custom Fields

Custom fields can be added through Admin > Custom Fields for:
- Items (SKU)
- Lots
- Products
- Variants
- BOM Lines
- Operations
- Documents
- Tasks
- Print Jobs
- Partners

## Support

For issues and feature requests, please create an issue in the repository.

## License

Proprietary - Lumixa
