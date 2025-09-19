<?php
/**
 * TechHive Customer Logout
 * Handles customer logout
 */

require_once '../includes/session.php';

// Clear customer session
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
unset($_SESSION['customer_email']);

// Redirect to login page
header('Location: login.php?logout=1');
exit;
?>
