<?php
/**
 * Session Management for TechHive Role-Based Access Control
 * Handles user sessions, authentication, and role-based access
 */

// Only start session if headers haven't been sent
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    // Set secure session parameters before starting session
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    
    session_start();
}

/**
 * Initialize session with security settings
 */
function initSession() {
    // Set session timeout
    $timeout = 3600; // 1 hour
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role'],
        'first_name' => $_SESSION['first_name'],
        'last_name' => $_SESSION['last_name'],
        'permissions' => $_SESSION['permissions']
    ];
}

/**
 * Set user session data
 */
function setUserSession($user) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['permissions'] = $user['permissions'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
}

/**
 * Clear user session
 */
function clearUserSession() {
    session_unset();
    session_destroy();
}

/**
 * Check if user has specific permission
 */
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user = getCurrentUser();
    return isset($user['permissions'][$permission]) && $user['permissions'][$permission] === true;
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user = getCurrentUser();
    return $user['role'] === $role;
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../auth/login.php');
        exit();
    }
}

/**
 * Redirect to appropriate dashboard based on role
 */
function redirectToDashboard() {
    if (!isLoggedIn()) {
        return;
    }
    
    $user = getCurrentUser();
    $role = $user['role'];
    
    switch ($role) {
        case 'admin':
            header('Location: ../dashboards/admin/dashboard.php');
            break;
        case 'php_developer':
            header('Location: ../dashboards/php-developer/dashboard.php');
            break;
        case 'frontend_developer':
            header('Location: ../dashboards/frontend-developer/dashboard.php');
            break;
        case 'database_manager':
            header('Location: ../dashboards/database-manager/dashboard.php');
            break;
        default:
            header('Location: ../auth/login.php');
            break;
    }
    exit();
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Log user activity
 */
function logActivity($action, $details = '') {
    if (!isLoggedIn()) {
        return;
    }
    
    $user = getCurrentUser();
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $user['user_id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'action' => $action,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // Store in session for now (in production, store in file or database)
    if (!isset($_SESSION['activity_log'])) {
        $_SESSION['activity_log'] = [];
    }
    $_SESSION['activity_log'][] = $logEntry;
}

// Initialize session
initSession();
?>
