<?php
/**
 * TechHive Delete Product
 * Handles product deletion with confirmation
 */

require_once '../../includes/session.php';
require_once '../../includes/auth-functions.php';
require_once '../../includes/data-manager.php';

// Require database manager role
requireLogin();
if (!hasRole('database_manager')) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$user = getCurrentUser();
$dataManager = new DataManager();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = trim($_POST['product_id']);
    
    if (empty($productId)) {
        $error = 'Product ID is required';
    } else {
        // Get product details before deletion for confirmation
        $product = $dataManager->getProduct($productId);
        
        if (!$product) {
            $error = 'Product not found';
        } else {
            // Delete the product
            $result = $dataManager->deleteProduct($productId);
            
            if ($result['success']) {
                // Also delete associated image if it exists
                if (!empty($product['image']) && file_exists('../../' . $product['image'])) {
                    unlink('../../' . $product['image']);
                }
                
                $message = 'Product "' . htmlspecialchars($product['name']) . '" has been deleted successfully!';
            } else {
                $error = $result['message'];
            }
        }
    }
} else {
    $error = 'Invalid request';
}

// Redirect back to dashboard with message
$redirectUrl = 'dashboard.php';
if ($message) {
    $redirectUrl .= '?success=' . urlencode($message);
} elseif ($error) {
    $redirectUrl .= '?error=' . urlencode($error);
}

header('Location: ' . $redirectUrl);
exit();
?>
