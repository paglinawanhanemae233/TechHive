<?php
/**
 * TechHive Cart Manager
 * Handles shopping cart operations with JSON storage
 */

class CartManager {
    private $cartFile;
    private $ordersFile;
    private $customersFile;
    
    public function __construct() {
        $dataDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
        $this->cartFile = $dataDir . 'cart.json';
        $this->ordersFile = $dataDir . 'orders.json';
        $this->customersFile = $dataDir . 'customers.json';
        
        // Ensure data directory exists
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
    }
    
    /**
     * Get cart data
     */
    public function getCart() {
        if (file_exists($this->cartFile)) {
            $data = json_decode(file_get_contents($this->cartFile), true);
            return $data ?: [];
        }
        return [];
    }
    
    /**
     * Save cart data
     */
    public function saveCart($cartData) {
        return file_put_contents($this->cartFile, json_encode($cartData, JSON_PRETTY_PRINT));
    }
    
    /**
     * Add item to cart
     */
    public function addToCart($productId, $quantity = 1, $sessionId = null) {
        if (!$sessionId) {
            $sessionId = session_id();
        }
        
        $cart = $this->getCart();
        
        // Initialize session cart if not exists
        if (!isset($cart[$sessionId])) {
            $cart[$sessionId] = [];
        }
        
        // Check if item already exists
        $itemExists = false;
        foreach ($cart[$sessionId] as &$item) {
            if ($item['product_id'] == $productId) {
                $item['quantity'] += $quantity;
                $itemExists = true;
                break;
            }
        }
        
        // Add new item if not exists
        if (!$itemExists) {
            $cart[$sessionId][] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'added_at' => date('Y-m-d H:i:s')
            ];
        }
        
        return $this->saveCart($cart);
    }
    
    /**
     * Update item quantity
     */
    public function updateQuantity($productId, $quantity, $sessionId = null) {
        if (!$sessionId) {
            $sessionId = session_id();
        }
        
        $cart = $this->getCart();
        
        if (!isset($cart[$sessionId])) {
            return false;
        }
        
        foreach ($cart[$sessionId] as &$item) {
            if ($item['product_id'] == $productId) {
                if ($quantity <= 0) {
                    // Remove item if quantity is 0 or negative
                    $cart[$sessionId] = array_filter($cart[$sessionId], function($i) use ($productId) {
                        return $i['product_id'] != $productId;
                    });
                } else {
                    $item['quantity'] = $quantity;
                }
                break;
            }
        }
        
        return $this->saveCart($cart);
    }
    
    /**
     * Remove item from cart
     */
    public function removeFromCart($productId, $sessionId = null) {
        if (!$sessionId) {
            $sessionId = session_id();
        }
        
        $cart = $this->getCart();
        
        if (!isset($cart[$sessionId])) {
            return false;
        }
        
        $cart[$sessionId] = array_filter($cart[$sessionId], function($item) use ($productId) {
            return $item['product_id'] != $productId;
        });
        
        return $this->saveCart($cart);
    }
    
    /**
     * Get cart items with product details
     */
    public function getCartItems($sessionId = null) {
        if (!$sessionId) {
            $sessionId = session_id();
        }
        
        $cart = $this->getCart();
        
        if (!isset($cart[$sessionId])) {
            return [];
        }
        
        // Load products data
        $productsFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'products.json';
        $products = [];
        if (file_exists($productsFile)) {
            $products = json_decode(file_get_contents($productsFile), true) ?: [];
        }
        
        $cartItems = [];
        foreach ($cart[$sessionId] as $item) {
            $product = array_filter($products, function($p) use ($item) {
                return $p['id'] == $item['product_id'];
            });
            
            if (!empty($product)) {
                $product = array_values($product)[0];
                $cartItems[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'added_at' => $item['added_at'],
                    'product' => $product
                ];
            }
        }
        
        return $cartItems;
    }
    
    /**
     * Get cart total
     */
    public function getCartTotal($sessionId = null) {
        $items = $this->getCartItems($sessionId);
        $total = 0;
        
        foreach ($items as $item) {
            $total += $item['product']['price'] * $item['quantity'];
        }
        
        return $total;
    }
    
    /**
     * Get cart item count
     */
    public function getCartItemCount($sessionId = null) {
        $items = $this->getCartItems($sessionId);
        $count = 0;
        
        foreach ($items as $item) {
            $count += $item['quantity'];
        }
        
        return $count;
    }
    
    /**
     * Clear cart
     */
    public function clearCart($sessionId = null) {
        if (!$sessionId) {
            $sessionId = session_id();
        }
        
        $cart = $this->getCart();
        unset($cart[$sessionId]);
        
        return $this->saveCart($cart);
    }
    
    /**
     * Process checkout
     */
    public function processCheckout($customerData, $sessionId = null) {
        if (!$sessionId) {
            $sessionId = session_id();
        }
        
        $cartItems = $this->getCartItems($sessionId);
        
        if (empty($cartItems)) {
            return ['success' => false, 'message' => 'Cart is empty'];
        }
        
        // Generate order ID
        $orderId = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
        // Calculate totals
        $subtotal = $this->getCartTotal($sessionId);
        $tax = $subtotal * 0.08; // 8% tax
        $shipping = $subtotal > 100 ? 0 : 10; // Free shipping over $100
        $total = $subtotal + $tax + $shipping;
        
        // Create order
        $order = [
            'id' => $orderId,
            'customer' => $customerData,
            'items' => $cartItems,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping,
            'total' => $total,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Save order
        $orders = [];
        if (file_exists($this->ordersFile)) {
            $orders = json_decode(file_get_contents($this->ordersFile), true) ?: [];
        }
        
        $orders[] = $order;
        file_put_contents($this->ordersFile, json_encode($orders, JSON_PRETTY_PRINT));
        
        // Save customer data
        $customers = [];
        if (file_exists($this->customersFile)) {
            $customers = json_decode(file_get_contents($this->customersFile), true) ?: [];
        }
        
        $customers[] = [
            'id' => uniqid(),
            'name' => $customerData['name'],
            'email' => $customerData['email'],
            'phone' => $customerData['phone'],
            'address' => $customerData['address'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($this->customersFile, json_encode($customers, JSON_PRETTY_PRINT));
        
        // Clear cart
        $this->clearCart($sessionId);
        
        return ['success' => true, 'order_id' => $orderId, 'order' => $order];
    }
}
?>
