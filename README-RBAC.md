# TechHive Role-Based Access Control System

## Overview
A complete role-based access control system for TechHive e-commerce website using JSON files as the database. The system supports 4 distinct user roles with specialized dashboards and permissions.

## System Architecture

### File Structure
```
TechHive/
├── Data/                       # JSON data (capital D)
│   ├── users.json              # Team accounts (RBAC)
│   ├── products.json           # Store catalog
│   ├── categories.json         # Categories
│   ├── brands.json             # Brands
│   ├── customers.json          # Customer accounts
│   └── orders.json             # Orders
├── api/
│   └── get-data.php            # Serves JSON to frontend (products/categories/brands)
├── auth/
│   ├── login.php               # RBAC login (admins/devs)
│   └── logout.php              # RBAC logout
├── customer/                   # Customer-facing auth
│   ├── login.php               # Customer login
│   ├── register.php            # Customer registration
│   └── dashboard.php           # Customer account & order history
├── dashboards/
│   ├── admin/                  # Admin dashboard
│   ├── php-developer/          # PHP developer dashboard
│   ├── frontend-developer/     # Frontend developer dashboard
│   └── database-manager/       # Database manager dashboard
├── includes/
│   ├── session.php             # Session management
│   ├── auth-functions.php      # RBAC auth helpers
│   └── cart-manager.php        # Server-side cart/checkout helpers (JSON storage)
├── assets/
│   ├── css/
│   │   ├── admin.css           # Admin interface styles
│   │   └── developer.css       # Developer dashboard styles
│   ├── images/
│   │   └── team/               # Our Team photos (admin.jpg, php-dev.jpg, ...)
│   └── js/
│       ├── auth.js             # Authentication scripts
│       └── dashboard.js        # Dashboard functionality
├── cart.php                    # Cart page (localStorage UI)
├── checkout.php                # Checkout (bridges client cart → JSON order)
├── order-confirmation.php      # Post-checkout receipt page
├── index.html                  # Storefront (Products, Categories, Our Team, Contact)
└── public/
    └── admin.php               # Main RBAC portal
```

## User Roles & Permissions

### 1. Administrator (Admin)
- **Full System Access**: Complete control over all system functions
- **User Management**: Add, edit, delete team members
- **Role Assignments**: Assign and modify user roles
- **System Configuration**: Modify system settings
- **Dashboard**: Comprehensive admin panel with user management
- **Color Theme**: Primary Purple (#4A088C)

### 2. PHP Developer
- **Backend Access**: API endpoint management and JSON data processing
- **Server-side Functionality**: Backend security and data validation
- **Error Handling**: Access to error logs and debugging tools
- **Dashboard**: API testing tools and JSON data management
- **Color Theme**: Secondary Dark Blue (#120540)

### 3. Frontend Developer
- **UI/UX Access**: HTML/CSS/JavaScript development tools
- **Design System**: Component library and responsive design tools
- **User Experience**: Customer-facing page management
- **Dashboard**: UI component preview and design tools
- **Color Theme**: Light Purple (#AEA7D9)

### 4. Database Manager
- **Data Management**: JSON data editing and validation
- **Content Management**: Product catalog and inventory management
- **Data Quality**: Data validation and quality control
- **Dashboard**: Data management tools and content editing
- **Color Theme**: Accent Blue-Gray (#433C73)

## Security Features

### Authentication
- **Password Hashing**: Secure password storage using PHP's password_hash()
- **Session Management**: Automatic session timeout and activity tracking
- **CSRF Protection**: Cross-site request forgery protection
- **Input Validation**: Comprehensive input sanitization

### Authorization
- **Role-Based Access**: Granular permissions based on user roles
- **Page-Level Security**: Protected routes and dashboard access
- **Permission Checking**: Real-time permission validation
- **Activity Logging**: User action tracking and audit trails

## Demo Credentials

| Role | Username | Password | Dashboard Access |
|------|----------|----------|------------------|
| Administrator | admin | password | Full system control |
| PHP Developer | phpdev | password | Backend tools & API |
| Frontend Developer | frontenddev | password | UI/UX development |
| Database Manager | dbmanager | password | Data management |

## Key Features

### 1. JSON-Based Database
- **No SQL Required**: All data stored in JSON files
- **Easy Migration**: Simple data structure for FileMaker conversion
- **Portable**: Easy to backup and restore

### 2. Role-Specific Dashboards
- **Customized Interfaces**: Each role has specialized tools and features
- **Color-Coded Themes**: Visual distinction between roles
- **Responsive Design**: Mobile-friendly interfaces

### 3. User Management
- **Admin-Only Creation**: Only administrators can create new users
- **Role Assignment**: Flexible role assignment and modification
- **Permission Management**: Granular permission control

### 4. Security Implementation
- **Session Timeout**: Automatic logout after inactivity
- **Input Validation**: Comprehensive data validation
- **CSRF Protection**: Secure form submissions
- **Activity Logging**: Track user actions and system events

## Usage Instructions

### 1. Access the System
1. Navigate to `public/admin.php` for the main portal
2. Select your role or click "Login to System"
3. Use the provided demo credentials

### 2. Role-Specific Workflows

#### Administrator
- Manage users and roles
- Configure system settings
- Monitor team activity
- Access all dashboard areas

#### PHP Developer
- Test API endpoints
- Process JSON data
- Debug backend issues
- Manage data processing

#### Frontend Developer
- Edit UI components
- Test responsive design
- Manage design system
- Preview customer pages

#### Database Manager
- Edit product data
- Manage inventory
- Validate data quality
- Process orders

### 3. Navigation
- **Role-Based Menus**: Each dashboard shows relevant tools
- **Quick Actions**: Common tasks easily accessible
- **Statistics**: Real-time data and metrics
- **Notifications**: System alerts and updates

## Technical Implementation

### PHP Backend
- **Session Management**: Secure user sessions
- **Authentication**: User login and role verification
- **Permission System**: Granular access control
- **Data Validation**: Input sanitization and validation

### JavaScript Frontend
- **Interactive Dashboards**: Dynamic user interfaces
- **Form Handling**: Client-side validation
- **Notifications**: Real-time user feedback
- **Responsive Design**: Mobile-optimized interfaces

### CSS Styling
- **Role Themes**: Distinct color schemes per role
- **Responsive Design**: Mobile-first approach
- **Modern UI**: Clean, professional interfaces
- **Accessibility**: WCAG compliant design

## Future Enhancements

### Planned Features
- **API Integration**: RESTful API endpoints
- **Real-time Updates**: WebSocket connections
- **Advanced Analytics**: Detailed reporting
- **Mobile App**: Native mobile application
- **Multi-language**: Internationalization support

### Scalability
- **Database Migration**: Easy transition to SQL databases
- **Cloud Deployment**: AWS/Azure compatibility
- **Microservices**: Service-oriented architecture
- **Caching**: Redis/Memcached integration

## Support & Maintenance

### Regular Tasks
- **Data Backup**: Automated JSON file backups
- **Security Updates**: Regular security patches
- **User Management**: Monitor user activity
- **Performance**: Optimize system performance

### Troubleshooting
- **Session Issues**: Clear browser cache and cookies
- **Permission Errors**: Verify user role assignments
- **Data Corruption**: Restore from backup files
- **Login Problems**: Check user credentials and status

## Conclusion

The TechHive Role-Based Access Control System provides a complete solution for team collaboration with specialized dashboards, secure authentication, and granular permissions. The JSON-based approach ensures easy data management while maintaining security and scalability.

For technical support or feature requests, please contact the development team.

---

## New Frontend Sections

### Our Team (with photos)
- The storefront now includes an "Our Team" section that highlights the 4 roles: Admin, PHP Developer, Frontend Developer, and Database Manager.
- Photos are optional. Place team images here:
  - `assets/images/team/admin.jpg`
  - `assets/images/team/php-dev.jpg`
  - `assets/images/team/frontend-dev.jpg`
  - `assets/images/team/db-manager.jpg`
- Each card shows an emoji avatar by default; when a photo is present, it loads as a circular image and hides the emoji automatically.

### Contact Section
- A contact info panel (address/phone/email/hours) and a simple contact form were added to `index.html`.
- The form performs client-side validation and shows a confirmation toast. Hook it to an email/API endpoint later as needed.

## Customer Accounts & Checkout Flow

### Customer Authentication
- Customer auth is separate from RBAC and lives in `customer/`.
- Registration (`customer/register.php`):
  - Validates input, hashes passwords, and appends to `Data/customers.json`.
- Login (`customer/login.php`):
  - Verifies credentials against `Data/customers.json` and sets `$_SESSION['customer_*']` values.
- Dashboard (`customer/dashboard.php`):
  - Displays account info and order history (filtered by customer email).

### Cart and Checkout
- Cart UI uses `localStorage` on the client (added via `index.html`).
- Checkout (`checkout.php`) bridges the client cart to server JSON by:
  1) Reading the cart from a hidden field (JSON); 2) Resolving products from `Data/products.json`; 3) Creating an order in `Data/orders.json`; 4) Optionally adding/augmenting a customer record in `Data/customers.json`.
- Customers who are logged in have their name/email prefilled at checkout.
- On success, the user is redirected to `order-confirmation.php?order_id=...`, which renders a printable receipt.

### Important Path Note
- The project uses `Data/` (capital D). When reading/writing JSON in PHP, paths were standardized to `__DIR__ . '/Data/...json'` to avoid case mismatches on Windows.

## How to Add/Change Team Photos
1. Prepare square headshots (e.g., 500×500) and save them in `assets/images/team/` with these names:
   - `admin.jpg`, `php-dev.jpg`, `frontend-dev.jpg`, `db-manager.jpg`
2. Edit names/titles directly in the Our Team section of `index.html` if needed.
3. If a photo is missing, the emoji avatar will be shown automatically.

## Quick QA Checklist
- [ ] Can login to RBAC roles via `public/admin.php`
- [ ] Can register/login as a customer (customer/ directory)
- [ ] Products/categories/brands load via `api/get-data.php`
- [ ] Add to cart updates the cart icon and cart page
- [ ] Checkout creates an order in `Data/orders.json`
- [ ] Order confirmation renders a receipt
- [ ] Our Team shows correct names; photos load or fallback to emoji
- [ ] Contact form validates and shows success message
