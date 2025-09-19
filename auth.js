/**
 * TechHive Authentication JavaScript
 * Handles login, session management, and role-based access
 */

class TechHiveAuth {
    constructor() {
        this.sessionTimeout = 3600000; // 1 hour in milliseconds
        this.checkSession();
        this.setupEventListeners();
    }

    /**
     * Check if user session is valid
     */
    checkSession() {
        const lastActivity = localStorage.getItem('lastActivity');
        const now = Date.now();
        
        if (lastActivity && (now - lastActivity) > this.sessionTimeout) {
            this.logout();
            return false;
        }
        
        // Update last activity
        localStorage.setItem('lastActivity', now);
        return true;
    }

    /**
     * Setup event listeners for authentication
     */
    setupEventListeners() {
        // Update activity on user interaction
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, () => {
                localStorage.setItem('lastActivity', Date.now());
            }, true);
        });

        // Handle form submissions
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', this.handleLogin.bind(this));
        }

        // Handle logout
        const logoutBtn = document.querySelector('.logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', this.handleLogout.bind(this));
        }
    }

    /**
     * Handle login form submission
     */
    handleLogin(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const username = formData.get('username');
        const password = formData.get('password');
        
        if (!username || !password) {
            this.showAlert('Please enter both username and password.', 'error');
            return;
        }

        // Show loading state
        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Logging in...';
        submitBtn.disabled = true;

        // Simulate login process (in real implementation, this would be an AJAX call)
        setTimeout(() => {
            // Reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            
            // In a real implementation, you would handle the server response here
            // For now, we'll just show a success message
            this.showAlert('Login successful! Redirecting...', 'success');
            
            // Redirect after a short delay
            setTimeout(() => {
                window.location.href = this.getDashboardUrl(username);
            }, 1000);
        }, 1500);
    }

    /**
     * Handle logout
     */
    handleLogout(event) {
        event.preventDefault();
        
        if (confirm('Are you sure you want to logout?')) {
            // Clear session data
            localStorage.removeItem('lastActivity');
            sessionStorage.clear();
            
            // Redirect to login page
            window.location.href = '../../auth/logout.php';
        }
    }

    /**
     * Get dashboard URL based on username
     */
    getDashboardUrl(username) {
        const roleMap = {
            'admin': '../dashboards/admin/dashboard.php',
            'phpdev': '../dashboards/php-developer/dashboard.php',
            'frontenddev': '../dashboards/frontend-developer/dashboard.php',
            'dbmanager': '../dashboards/database-manager/dashboard.php'
        };
        
        return roleMap[username] || '../dashboards/admin/dashboard.php';
    }

    /**
     * Show alert message
     */
    showAlert(message, type = 'info') {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.auth-alert');
        existingAlerts.forEach(alert => alert.remove());

        // Create new alert
        const alert = document.createElement('div');
        alert.className = `auth-alert auth-alert-${type}`;
        alert.textContent = message;
        
        // Style the alert
        alert.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;

        // Set background color based on type
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        alert.style.backgroundColor = colors[type] || colors.info;

        // Add to page
        document.body.appendChild(alert);

        // Auto remove after 5 seconds
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    /**
     * Validate form input
     */
    validateInput(input) {
        const value = input.value.trim();
        const type = input.type;
        const required = input.hasAttribute('required');

        if (required && !value) {
            this.showFieldError(input, 'This field is required');
            return false;
        }

        if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                this.showFieldError(input, 'Please enter a valid email address');
                return false;
            }
        }

        if (type === 'password' && value && value.length < 8) {
            this.showFieldError(input, 'Password must be at least 8 characters long');
            return false;
        }

        this.clearFieldError(input);
        return true;
    }

    /**
     * Show field error
     */
    showFieldError(input, message) {
        this.clearFieldError(input);
        
        const error = document.createElement('div');
        error.className = 'field-error';
        error.textContent = message;
        error.style.cssText = `
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        `;
        
        input.style.borderColor = '#dc3545';
        input.parentNode.appendChild(error);
    }

    /**
     * Clear field error
     */
    clearFieldError(input) {
        const existingError = input.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        input.style.borderColor = '';
    }

    /**
     * Check user permissions
     */
    hasPermission(permission) {
        // In a real implementation, this would check against server-side permissions
        const userRole = this.getUserRole();
        const permissions = this.getRolePermissions(userRole);
        return permissions.includes(permission);
    }

    /**
     * Get user role
     */
    getUserRole() {
        return localStorage.getItem('userRole') || 'guest';
    }

    /**
     * Get role permissions
     */
    getRolePermissions(role) {
        const rolePermissions = {
            admin: ['all'],
            php_developer: ['api_management', 'data_processing', 'backend_tools'],
            frontend_developer: ['ui_components', 'design_tools', 'responsive_design'],
            database_manager: ['data_management', 'content_editing', 'inventory_management']
        };
        
        return rolePermissions[role] || [];
    }

    /**
     * Redirect based on role
     */
    redirectToDashboard(role) {
        const dashboardUrls = {
            admin: '../dashboards/admin/dashboard.php',
            php_developer: '../dashboards/php-developer/dashboard.php',
            frontend_developer: '../dashboards/frontend-developer/dashboard.php',
            database_manager: '../dashboards/database-manager/dashboard.php'
        };
        
        const url = dashboardUrls[role];
        if (url) {
            window.location.href = url;
        }
    }
}

// Initialize authentication when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new TechHiveAuth();
});

// Export for use in other modules
window.TechHiveAuth = TechHiveAuth;
