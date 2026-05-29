# Admin Panel - Transport Booking System

## Overview

A fully functional admin dashboard for managing the Transport Booking System. The admin panel provides complete control over bookings, users, vehicles, routes, and system settings.

## Features

### 1. **Dashboard** (`index.php`)
- Real-time statistics showing:
  - Total Bookings
  - Pending Bookings
  - Total Users
  - Total Vehicles
- Quick action buttons
- Recent bookings preview
- Database integration

### 2. **Bookings Management** (`bookings.php`)
- View all bookings with details
- Update booking status (Pending → Confirmed → Completed/Cancelled)
- Filter bookings by status
- View user and vehicle information
- Modal-based status updates

### 3. **Users Management** (`users.php`)
- View all registered users
- Add new users with credentials
- Delete users
- View user details (username, email, phone, join date)
- User creation form in modal

### 4. **Vehicles Management** (`vehicles.php`)
- View all vehicles
- Add new vehicles with specifications
- Update vehicle status (Available, Maintenance, Unavailable)
- Track vehicle capacity and rate per km
- Delete vehicles
- Status management modal

### 5. **Routes Management** (`routes.php`)
- View all transport routes
- Add new routes with locations and distance
- Delete routes
- Track route details (start/end locations, distance)

### 6. **Reports** (`reports.php`)
- **Bookings Report**: All bookings with dates and amounts
- **Revenue Report**: Revenue summary by date
- **Vehicles Report**: Vehicle performance and usage statistics
- Print functionality for reports

### 7. **Settings** (`settings.php`)
- **Profile Settings**: Update username, email, phone
- **Password Management**: Change admin password with validation
- **System Information**: Display admin account details
- Session-based security

### 8. **Navigation & Components**
- **Sidebar**: Fixed navigation with active page highlighting
- **Navbar**: User dropdown menu with profile and logout options
- **Styling**: Custom CSS with Bootstrap 5 integration

## File Structure

```
admin/
├── index.php                 # Dashboard
├── bookings.php             # Bookings management
├── users.php                # Users management
├── vehicles.php             # Vehicles management
├── routes.php               # Routes management
├── reports.php              # Reports & analytics
├── settings.php             # Admin settings
├── logout.php               # Logout handler
├── includes/
│   ├── sidebar.php          # Navigation sidebar
│   └── navbar.php           # Top navigation bar
└── assets/
    └── css/
        └── admin-style.css  # Custom styling
```

## Setup & Installation

### Requirements
- PHP 7.4+
- MySQL 5.7+
- Bootstrap 5.3.0
- Font Awesome 6.4.0

### Configuration

1. **Database Configuration** (`config/database.php`)
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'transport_booking_system');
define('DB_USER', 'root');
define('DB_PASS', '');
```

2. **Database Tables Required**
   - `users` (with role='admin' for admin users)
   - `bookings`
   - `vehicles`
   - `routes`

### Access

1. Login with admin credentials
2. Navigate to: `http://localhost/transport_booking_system/admin/`
3. Dashboard loads automatically

## Security Features

✅ **Session-based authentication**
✅ **Password hashing (bcrypt)**
✅ **Role-based access control**
✅ **SQL injection prevention (prepared statements)**
✅ **CSRF protection ready**
✅ **XSS prevention (htmlspecialchars)**

## Usage Guide

### Managing Bookings
1. Go to **Bookings** page
2. View all bookings in table format
3. Click **Edit** to update booking status
4. Select new status and confirm

### Managing Users
1. Go to **Users** page
2. Click **Add New User** button
3. Fill in user details (username, email, phone, password)
4. Click **Add User**
5. Delete users with **Delete** button

### Managing Vehicles
1. Go to **Vehicles** page
2. Click **Add New Vehicle** button
3. Enter vehicle details (name, type, license plate, capacity, rate)
4. Click **Add Vehicle**
5. Update vehicle status with **Status** button
6. Delete vehicles with **Delete** button

### Managing Routes
1. Go to **Routes** page
2. Click **Add New Route** button
3. Enter route details (name, start location, end location, distance)
4. Click **Add Route**
5. Delete routes with **Delete** button

### Viewing Reports
1. Go to **Reports** page
2. Choose report type:
   - **Bookings Report**: All bookings
   - **Revenue Report**: Daily revenue summary
   - **Vehicles Report**: Vehicle performance
3. Click **Print** to generate PDF

### Admin Settings
1. Go to **Settings** page
2. **Update Profile**: Edit username, email, phone
3. **Change Password**: Enter current and new password
4. View system information

## Styling & Customization

### Color Scheme
- **Primary**: #667eea (Purple-blue)
- **Secondary**: #764ba2 (Dark purple)
- **Success**: #48bb78 (Green)
- **Warning**: #f6ad55 (Orange)
- **Danger**: #f56565 (Red)

### Responsive Design
- Desktop: Full sidebar + main content
- Tablet: Responsive tables and cards
- Mobile: Stacked layout with hamburger menu

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255),
    role ENUM('admin', 'user'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Bookings Table
```sql
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    vehicle_id INT,
    booking_date DATETIME,
    pickup_location VARCHAR(255),
    dropoff_location VARCHAR(255),
    total_amount DECIMAL(10,2),
    status ENUM('pending', 'confirmed', 'completed', 'cancelled'),
    created_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);
```

### Vehicles Table
```sql
CREATE TABLE vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    type VARCHAR(50),
    license_plate VARCHAR(20) UNIQUE,
    capacity INT,
    rate_per_km DECIMAL(8,2),
    status ENUM('available', 'maintenance', 'unavailable'),
    created_at TIMESTAMP
);
```

### Routes Table
```sql
CREATE TABLE routes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    start_location VARCHAR(255),
    end_location VARCHAR(255),
    distance DECIMAL(8,2),
    created_at TIMESTAMP
);
```

## Troubleshooting

### Issue: Cannot access admin panel
**Solution**: Verify login credentials and ensure user role is 'admin'

### Issue: Database connection error
**Solution**: Check `config/database.php` credentials and MySQL service status

### Issue: Styling not loading
**Solution**: Verify Bootstrap and Font Awesome CDN links are accessible

### Issue: Form submission fails
**Solution**: Check database connection and ensure required fields are filled

## Best Practices

1. **Security**
   - Change default admin password immediately
   - Use strong passwords (12+ characters)
   - Regularly backup database
   - Keep software updated

2. **Performance**
   - Add database indexes on frequently queried columns
   - Implement pagination for large datasets
   - Cache reports data

3. **Maintenance**
   - Monitor database size
   - Archive old bookings
   - Review logs regularly

## Future Enhancements

- [ ] Multi-user admin support
- [ ] Advanced reporting with charts
- [ ] Email notifications
- [ ] SMS integration
- [ ] Two-factor authentication
- [ ] Activity logs
- [ ] Export to CSV/Excel
- [ ] Dashboard customization
- [ ] API integration
- [ ] Real-time notifications

## Support

For issues or feature requests, contact the development team or create an issue in the repository.

---

**Version**: 1.0.0  
**Last Updated**: 2026-05-29  
**License**: MIT
