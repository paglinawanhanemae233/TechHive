<?php
/**
 * Authentication Functions for TechHive Role-Based Access Control
 * Handles user authentication, password management, and user operations
 */

require_once 'session.php';

/**
 * Load users from JSON file
 */
function loadUsers() {
    $usersFile = '../data/users.json';
    if (!file_exists($usersFile)) {
        return ['users' => []];
    }
    
    $json = file_get_contents($usersFile);
    return json_decode($json, true);
}

/**
 * Save users to JSON file
 */
function saveUsers($usersData) {
    $usersFile = '../data/users.json';
    return file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT));
}

/**
 * Find user by username or email
 */
function findUser($identifier) {
    $usersData = loadUsers();
    
    foreach ($usersData['users'] as $user) {
        if ($user['username'] === $identifier || $user['email'] === $identifier) {
            return $user;
        }
    }
    return null;
}

/**
 * Find user by ID
 */
function findUserById($userId) {
    $usersData = loadUsers();
    
    foreach ($usersData['users'] as $user) {
        if ($user['user_id'] === $userId) {
            return $user;
        }
    }
    return null;
}

/**
 * Authenticate user login
 */
function authenticateUser($username, $password) {
    $user = findUser($username);
    
    if (!$user) {
        return ['success' => false, 'message' => 'User not found'];
    }
    
    if (!$user['is_active']) {
        return ['success' => false, 'message' => 'Account is deactivated'];
    }
    
    if (!password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid password'];
    }
    
    // Update last login
    updateLastLogin($user['user_id']);
    
    return ['success' => true, 'user' => $user];
}

/**
 * Update user's last login time
 */
function updateLastLogin($userId) {
    $usersData = loadUsers();
    
    foreach ($usersData['users'] as &$user) {
        if ($user['user_id'] === $userId) {
            $user['last_login'] = date('Y-m-d H:i:s');
            break;
        }
    }
    
    saveUsers($usersData);
}

/**
 * Create new user (admin only)
 */
function createUser($userData) {
    if (!hasPermission('can_manage_users')) {
        return ['success' => false, 'message' => 'Insufficient permissions'];
    }
    
    $usersData = loadUsers();
    
    // Check if username or email already exists
    if (findUser($userData['username']) || findUser($userData['email'])) {
        return ['success' => false, 'message' => 'Username or email already exists'];
    }
    
    // Generate new user ID
    $newUserId = 'USER-' . str_pad(count($usersData['users']) + 1, 3, '0', STR_PAD_LEFT);
    
    // Hash password
    $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
    
    // Create user object
    $newUser = [
        'user_id' => $newUserId,
        'username' => $userData['username'],
        'email' => $userData['email'],
        'password_hash' => $passwordHash,
        'role' => $userData['role'],
        'first_name' => $userData['first_name'],
        'last_name' => $userData['last_name'],
        'is_active' => true,
        'created_date' => date('Y-m-d'),
        'last_login' => null,
        'permissions' => getRolePermissions($userData['role'])
    ];
    
    $usersData['users'][] = $newUser;
    
    if (saveUsers($usersData)) {
        logActivity('user_created', 'Created user: ' . $userData['username']);
        return ['success' => true, 'user' => $newUser];
    } else {
        return ['success' => false, 'message' => 'Failed to save user'];
    }
}

/**
 * Update user data
 */
function updateUser($userId, $userData) {
    if (!hasPermission('can_manage_users') && getCurrentUser()['user_id'] !== $userId) {
        return ['success' => false, 'message' => 'Insufficient permissions'];
    }
    
    $usersData = loadUsers();
    
    foreach ($usersData['users'] as &$user) {
        if ($user['user_id'] === $userId) {
            // Update allowed fields
            if (isset($userData['first_name'])) $user['first_name'] = $userData['first_name'];
            if (isset($userData['last_name'])) $user['last_name'] = $userData['last_name'];
            if (isset($userData['email'])) $user['email'] = $userData['email'];
            if (isset($userData['is_active'])) $user['is_active'] = $userData['is_active'];
            
            // Only admin can change role
            if (hasPermission('can_manage_users') && isset($userData['role'])) {
                $user['role'] = $userData['role'];
                $user['permissions'] = getRolePermissions($userData['role']);
            }
            
            break;
        }
    }
    
    if (saveUsers($usersData)) {
        logActivity('user_updated', 'Updated user: ' . $userId);
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => 'Failed to update user'];
    }
}

/**
 * Change user password
 */
function changePassword($userId, $currentPassword, $newPassword) {
    $user = findUserById($userId);
    
    if (!$user) {
        return ['success' => false, 'message' => 'User not found'];
    }
    
    // Verify current password
    if (!password_verify($currentPassword, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Current password is incorrect'];
    }
    
    // Validate new password
    if (strlen($newPassword) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters long'];
    }
    
    $usersData = loadUsers();
    
    foreach ($usersData['users'] as &$user) {
        if ($user['user_id'] === $userId) {
            $user['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            break;
        }
    }
    
    if (saveUsers($usersData)) {
        logActivity('password_changed', 'Changed password for user: ' . $userId);
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => 'Failed to update password'];
    }
}

/**
 * Get role permissions
 */
function getRolePermissions($role) {
    $permissions = [
        'admin' => [
            'can_manage_users' => true,
            'can_edit_products' => true,
            'can_process_orders' => true,
            'can_access_admin_panel' => true,
            'can_modify_system_settings' => true,
            'can_manage_roles' => true,
            'can_view_analytics' => true,
            'can_access_all_dashboards' => true
        ],
        'php_developer' => [
            'can_manage_api' => true,
            'can_process_json_data' => true,
            'can_access_backend_tools' => true,
            'can_view_error_logs' => true,
            'can_test_endpoints' => true,
            'can_manage_data_processing' => true,
            'can_access_php_dashboard' => true
        ],
        'frontend_developer' => [
            'can_edit_ui_components' => true,
            'can_access_design_tools' => true,
            'can_modify_css' => true,
            'can_edit_javascript' => true,
            'can_preview_pages' => true,
            'can_access_frontend_dashboard' => true,
            'can_manage_responsive_design' => true
        ],
        'database_manager' => [
            'can_edit_products' => true,
            'can_manage_categories' => true,
            'can_edit_customer_data' => true,
            'can_process_orders' => true,
            'can_validate_data' => true,
            'can_access_database_dashboard' => true,
            'can_manage_inventory' => true
        ]
    ];
    
    return $permissions[$role] ?? [];
}

/**
 * Get all users (admin only)
 */
function getAllUsers() {
    if (!hasPermission('can_manage_users')) {
        return [];
    }
    
    $usersData = loadUsers();
    return $usersData['users'];
}

/**
 * Delete user (admin only)
 */
function deleteUser($userId) {
    if (!hasPermission('can_manage_users')) {
        return ['success' => false, 'message' => 'Insufficient permissions'];
    }
    
    // Prevent admin from deleting themselves
    if (getCurrentUser()['user_id'] === $userId) {
        return ['success' => false, 'message' => 'Cannot delete your own account'];
    }
    
    $usersData = loadUsers();
    
    foreach ($usersData['users'] as $index => $user) {
        if ($user['user_id'] === $userId) {
            unset($usersData['users'][$index]);
            $usersData['users'] = array_values($usersData['users']); // Reindex array
            break;
        }
    }
    
    if (saveUsers($usersData)) {
        logActivity('user_deleted', 'Deleted user: ' . $userId);
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => 'Failed to delete user'];
    }
}

/**
 * Validate user input
 */
function validateUserInput($data, $isUpdate = false) {
    $errors = [];
    
    if (!$isUpdate || isset($data['username'])) {
        if (empty($data['username'])) {
            $errors[] = 'Username is required';
        } elseif (strlen($data['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters long';
        }
    }
    
    if (!$isUpdate || isset($data['email'])) {
        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
    }
    
    if (!$isUpdate || isset($data['password'])) {
        if (empty($data['password'])) {
            $errors[] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
    }
    
    if (empty($data['first_name'])) {
        $errors[] = 'First name is required';
    }
    
    if (empty($data['last_name'])) {
        $errors[] = 'Last name is required';
    }
    
    if (!$isUpdate && empty($data['role'])) {
        $errors[] = 'Role is required';
    }
    
    return $errors;
}
?>
