<?php
/**
 * TechHive Cart Actions
 * Handles AJAX requests for cart operations
 */

require_once 'includes/session.php';
require_once 'includes/cart-manager.php';

header('Content-Type: application/json');

$cartManager = new CartManager();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = $_POST['product_id'] ?? '';
        $quantity = intval($_POST['quantity'] ?? 1);
        
        if (empty($productId)) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }
        
        $result = $cartManager->addToCart($productId, $quantity);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Item added to cart']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
        }
        break;
        
    case 'update':
        $productId = $_POST['product_id'] ?? '';
        $quantity = intval($_POST['quantity'] ?? 0);
        
        if (empty($productId)) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }
        
        $result = $cartManager->updateQuantity($productId, $quantity);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Quantity updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
        }
        break;
        
    case 'remove':
        $productId = $_POST['product_id'] ?? '';
        
        if (empty($productId)) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }
        
        $result = $cartManager->removeFromCart($productId);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove item from cart']);
        }
        break;
        
    case 'clear':
        $result = $cartManager->clearCart();
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cart cleared']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to clear cart']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
