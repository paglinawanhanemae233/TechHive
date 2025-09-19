/**
 * TechHive Dashboard JavaScript
 * Handles dashboard functionality and interactions
 */

class TechHiveDashboard {
    constructor() {
        this.currentUser = this.getCurrentUser();
        this.setupEventListeners();
        this.initializeDashboard();
    }

    /**
     * Get current user data
     */
    getCurrentUser() {
        // In a real implementation, this would come from the server
        return {
            id: 'USER-001',
            username: 'admin',
            role: 'admin',
            name: 'Admin User',
            permissions: ['all']
        };
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Handle modal operations
        document.addEventListener('click', this.handleModalClick.bind(this));
        
        // Handle form submissions
        document.addEventListener('submit', this.handleFormSubmit.bind(this));
        
        // Handle data table interactions
        document.addEventListener('click', this.handleTableClick.bind(this));
        
        // Handle quick actions
        document.addEventListener('click', this.handleQuickAction.bind(this));
    }

    /**
     * Initialize dashboard
     */
    initializeDashboard() {
        this.loadDashboardData();
        this.setupCharts();
        this.setupNotifications();
    }

    /**
     * Handle modal clicks
     */
    handleModalClick(event) {
        const target = event.target;
        
        // Open modal
        if (target.hasAttribute('data-modal')) {
            const modalId = target.getAttribute('data-modal');
            this.openModal(modalId);
        }
        
        // Close modal
        if (target.classList.contains('close') || target.classList.contains('modal-overlay')) {
            this.closeModal(target.closest('.modal'));
        }
    }

    /**
     * Handle form submissions
     */
    handleFormSubmit(event) {
        const form = event.target;
        const action = form.getAttribute('data-action');
        
        if (action) {
            event.preventDefault();
            this.handleFormAction(form, action);
        }
    }

    /**
     * Handle table clicks
     */
    handleTableClick(event) {
        const target = event.target;
        
        if (target.classList.contains('edit-btn')) {
            const id = target.getAttribute('data-id');
            this.editItem(id);
        }
        
        if (target.classList.contains('delete-btn')) {
            const id = target.getAttribute('data-id');
            this.deleteItem(id);
        }
        
        if (target.classList.contains('view-btn')) {
            const id = target.getAttribute('data-id');
            this.viewItem(id);
        }
    }

    /**
     * Handle quick actions
     */
    handleQuickAction(event) {
        const target = event.target;
        
        if (target.hasAttribute('data-action')) {
            const action = target.getAttribute('data-action');
            this.executeQuickAction(action);
        }
    }

    /**
     * Open modal
     */
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Focus first input
            const firstInput = modal.querySelector('input, select, textarea');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        }
    }

    /**
     * Close modal
     */
    closeModal(modal) {
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    /**
     * Handle form actions
     */
    handleFormAction(form, action) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        switch (action) {
            case 'create_user':
                this.createUser(data);
                break;
            case 'update_user':
                this.updateUser(data);
                break;
            case 'delete_user':
                this.deleteUser(data);
                break;
            case 'test_api':
                this.testAPI(data);
                break;
            case 'validate_data':
                this.validateData(data);
                break;
            default:
                console.log('Unknown action:', action);
        }
    }

    /**
     * Create user
     */
    createUser(data) {
        console.log('Creating user:', data);
        this.showNotification('User created successfully!', 'success');
        this.closeModal(document.querySelector('.modal'));
    }

    /**
     * Update user
     */
    updateUser(data) {
        console.log('Updating user:', data);
        this.showNotification('User updated successfully!', 'success');
        this.closeModal(document.querySelector('.modal'));
    }

    /**
     * Delete user
     */
    deleteUser(data) {
        if (confirm('Are you sure you want to delete this user?')) {
            console.log('Deleting user:', data);
            this.showNotification('User deleted successfully!', 'success');
        }
    }

    /**
     * Test API
     */
    testAPI(data) {
        console.log('Testing API:', data);
        this.showNotification('API test completed!', 'info');
    }

    /**
     * Validate data
     */
    validateData(data) {
        console.log('Validating data:', data);
        this.showNotification('Data validation completed!', 'info');
    }

    /**
     * Edit item
     */
    editItem(id) {
        console.log('Editing item:', id);
        this.showNotification('Edit functionality will be implemented', 'info');
    }

    /**
     * Delete item
     */
    deleteItem(id) {
        if (confirm('Are you sure you want to delete this item?')) {
            console.log('Deleting item:', id);
            this.showNotification('Item deleted successfully!', 'success');
        }
    }

    /**
     * View item
     */
    viewItem(id) {
        console.log('Viewing item:', id);
        this.showNotification('View functionality will be implemented', 'info');
    }

    /**
     * Execute quick action
     */
    executeQuickAction(action) {
        switch (action) {
            case 'backup_data':
                this.backupData();
                break;
            case 'validate_all':
                this.validateAll();
                break;
            case 'export_data':
                this.exportData();
                break;
            case 'cleanup_data':
                this.cleanupData();
                break;
            case 'generate_report':
                this.generateReport();
                break;
            case 'optimize_data':
                this.optimizeData();
                break;
            default:
                console.log('Unknown quick action:', action);
        }
    }

    /**
     * Backup data
     */
    backupData() {
        this.showNotification('Creating data backup...', 'info');
        setTimeout(() => {
            this.showNotification('Data backup completed!', 'success');
        }, 2000);
    }

    /**
     * Validate all data
     */
    validateAll() {
        this.showNotification('Validating all data...', 'info');
        setTimeout(() => {
            this.showNotification('Data validation completed!', 'success');
        }, 3000);
    }

    /**
     * Export data
     */
    exportData() {
        this.showNotification('Exporting data...', 'info');
        setTimeout(() => {
            this.showNotification('Data export completed!', 'success');
        }, 2000);
    }

    /**
     * Cleanup data
     */
    cleanupData() {
        this.showNotification('Cleaning up data...', 'info');
        setTimeout(() => {
            this.showNotification('Data cleanup completed!', 'success');
        }, 2500);
    }

    /**
     * Generate report
     */
    generateReport() {
        this.showNotification('Generating report...', 'info');
        setTimeout(() => {
            this.showNotification('Report generated successfully!', 'success');
        }, 3000);
    }

    /**
     * Optimize data
     */
    optimizeData() {
        this.showNotification('Optimizing data...', 'info');
        setTimeout(() => {
            this.showNotification('Data optimization completed!', 'success');
        }, 2000);
    }

    /**
     * Load dashboard data
     */
    loadDashboardData() {
        // Simulate loading data
        console.log('Loading dashboard data...');
        
        // Update statistics
        this.updateStatistics();
        
        // Load recent activity
        this.loadRecentActivity();
    }

    /**
     * Update statistics
     */
    updateStatistics() {
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            const number = card.querySelector('.stat-number');
            if (number) {
                this.animateNumber(number, parseInt(number.textContent) || 0);
            }
        });
    }

    /**
     * Animate number
     */
    animateNumber(element, target) {
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current);
        }, 20);
    }

    /**
     * Load recent activity
     */
    loadRecentActivity() {
        // Simulate loading recent activity
        console.log('Loading recent activity...');
    }

    /**
     * Setup charts
     */
    setupCharts() {
        // Initialize any charts or graphs
        console.log('Setting up charts...');
    }

    /**
     * Setup notifications
     */
    setupNotifications() {
        // Setup notification system
        console.log('Setting up notifications...');
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Remove existing notifications
        const existing = document.querySelectorAll('.dashboard-notification');
        existing.forEach(notification => notification.remove());

        // Create notification
        const notification = document.createElement('div');
        notification.className = `dashboard-notification dashboard-notification-${type}`;
        notification.textContent = message;
        
        // Style notification
        notification.style.cssText = `
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
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;

        // Set background color
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        notification.style.backgroundColor = colors[type] || colors.info;

        // Add to page
        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Auto remove
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new TechHiveDashboard();
});

// Export for use in other modules
window.TechHiveDashboard = TechHiveDashboard;
