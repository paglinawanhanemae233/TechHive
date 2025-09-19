<?php
/**
 * TechHive Logout Page
 * Handles user logout and session cleanup
 */

require_once '../includes/session.php';

// Log the logout activity before clearing session
if (isLoggedIn()) {
    logActivity('logout', 'User logged out');
}

// Clear user session
clearUserSession();

// Redirect to login page with logout message
header('Location: login.php?logout=1');
exit();
?>
