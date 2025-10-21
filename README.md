# Equipment Inventory Management System (IMS)

A comprehensive web-based inventory management system for tracking equipment, managing stock levels, and recording transactions.

## Features

- **Dashboard**: Real-time overview of inventory status
- **Equipment Management**: Add, edit, view, and track all equipment
- **Transaction History**: Complete audit trail of all equipment movements
- **Stock Management**: Monitor stock levels with low-stock alerts
- **User Authentication**: Secure login system with role-based access
- **Search & Filter**: Advanced search and filtering capabilities
- **Export Data**: Export reports to CSV format
- **Responsive Design**: Mobile-friendly interface using Tailwind CSS

## System Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- PDO and PDO_MySQL extensions

## Installation

1. **Copy files to web server**
   ```
   Copy all files to your web root directory (e.g., C:\xampp\htdocs\IMS)
   ```

2. **Configure database**
   - Edit `config/database.php` if you need to change database credentials
   - Default settings:
     - Host: localhost
     - Username: root
     - Password: (empty)
     - Database: ims_db

3. **Run installation**
   - Navigate to `http://localhost/IMS/install.php` in your browser
   - Click "Install System" button
   - The installer will:
     - Create the database
     - Create all necessary tables
     - Insert sample data
     - Create default admin user

4. **Login**
   - Navigate to `http://localhost/IMS/login.php`
   - Default credentials:
     - Username: `admin`
     - Password: `admin123`
   - **Important**: Change the password after first login!

## Database Schema

### Tables

1. **users** - System users with authentication
2. **categories** - Equipment categories
3. **equipment** - Main equipment inventory
4. **equipment_transactions** - Transaction history

### Transaction Types

- **In Use**: Equipment checked out for use
- **Returned**: Equipment returned to inventory
- **Replacement**: Equipment sent for repair/replacement
- **Added**: New stock added to inventory
- **Removed**: Stock permanently removed

## File Structure

```
IMS/
├── api/                      # AJAX API endpoints
│   ├── search_equipment.php
│   ├── validate_serial.php
│   ├── get_equipment.php
│   └── get_stock_status.php
├── assets/
│   └── js/
│       └── app.js           # Main JavaScript file
├── config/
│   └── database.php         # Database configuration
├── includes/
│   ├── auth_check.php       # Authentication middleware
│   ├── footer.php           # Page footer
│   ├── functions.php        # Helper functions
│   ├── header.php           # Page header
│   └── navigation.php       # Navigation bar
├── add_equipment.php        # Add new equipment
├── edit_equipment.php       # Edit equipment
├── equipment_details.php    # View equipment details
├── index.php                # Dashboard
├── install.php              # Installation script
├── login.php                # Login page
├── logout.php               # Logout handler
├── stock.php                # Stock management
├── transactions.php         # Transaction history
└── view_equipment.php       # Equipment list
```

## Usage Guide

### Adding Equipment

1. Navigate to Equipment → Add Equipment
2. Fill in required fields:
   - Equipment Name
   - Category
   - Serial Number (must be unique)
   - Total Quantity
3. Optional fields:
   - Purchase Date
   - Purchase Price
   - Description
4. Click "Add Equipment"

### Recording Transactions

1. Go to Equipment Details page
2. Use "Quick Actions" sidebar
3. Select transaction type
4. Enter quantity and details
5. Click "Record Transaction"

### Stock Management

1. Navigate to Stock page
2. View inventory levels and availability
3. Monitor low-stock alerts
4. Export reports as needed

### Filtering & Search

- Use search bars to find specific equipment
- Filter by category, status, date range
- Sort results by various columns

## Security Features

- CSRF token protection on all forms
- Password hashing using bcrypt
- Session management with periodic regeneration
- SQL injection prevention using prepared statements
- XSS protection through input sanitization
- Authentication required for all pages

## Default Sample Data

The installation includes:

- 8 sample equipment categories
- 8 sample equipment items
- 5 sample transactions
- 1 admin user account

## Customization

### Adding Categories

Categories are managed in the database. To add new categories:

```sql
INSERT INTO categories (name, description) 
VALUES ('Category Name', 'Description');
```

### Changing Low Stock Threshold

Edit the threshold in `includes/functions.php`:

```php
// Current threshold is 5
WHERE available_quantity < 5
```

## Troubleshooting

### Database Connection Error

- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database user has proper permissions

### Installation Fails

- Check PHP version (must be 8.0+)
- Verify PDO extensions are installed
- Check Apache error logs

### Login Issues

- Clear browser cache and cookies
- Try using incognito/private mode
- Check session configuration in php.ini

## Browser Compatibility

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Opera

## Technologies Used

- **Backend**: PHP 8.0+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **CSS Framework**: Tailwind CSS (CDN)
- **Icons**: Heroicons (inline SVG)

## License

This project is provided as-is for educational and commercial use.

## Support

For issues and questions:
1. Check the troubleshooting section
2. Review Apache/PHP error logs
3. Verify all system requirements are met

## Future Enhancements

Potential features for future versions:
- User management interface
- Advanced reporting and analytics
- Email notifications for low stock
- Equipment maintenance scheduling
- Barcode/QR code scanning
- Multi-location support
- Image uploads for equipment
- API for mobile apps

## Version History

**v1.0.0** - Initial Release
- Complete inventory management system
- User authentication
- Transaction tracking
- Stock management
- Responsive design

---

**Developed with PHP & MySQL**

**Last Updated**: October 2025

