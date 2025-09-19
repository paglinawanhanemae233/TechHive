<?php
/**
 * TechHive Checkout
 * Handles the checkout process and order creation
 */

require_once 'includes/session.php';
require_once 'includes/cart-manager.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartData = json_decode($_POST['cart_data'] ?? '[]', true);
    
    
    if (empty($cartData)) {
        $error = 'Cart is empty. Please add items to your cart first.';
    } else {
        $customerData = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => [
                'street' => $_POST['street'] ?? '',
                'city' => $_POST['city'] ?? '',
                'state' => $_POST['state'] ?? '',
                'zip' => $_POST['zip'] ?? '',
                'country' => $_POST['country'] ?? 'PH'
            ]
        ];
        
        // Validate required fields
        if (empty($customerData['name']) || empty($customerData['email']) || empty($customerData['phone'])) {
            $error = 'Please fill in all required fields.';
        } else {
            // Process the order using cart data
            $result = processOrderFromCartData($cartData, $customerData);
            
            if ($result['success']) {
                header('Location: order-confirmation.php?order_id=' . $result['order_id']);
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}

/**
 * Process order from cart data
 */
function processOrderFromCartData($cartData, $customerData) {
    // Load products data
    $productsFile = __DIR__ . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'products.json';
    $products = [];
    if (file_exists($productsFile)) {
        $products = json_decode(file_get_contents($productsFile), true) ?: [];
    }
    
    
    // Convert cart data to order format
    $orderItems = [];
    $subtotal = 0;
    
    foreach ($cartData as $item) {
        // Find product details
        $product = null;
        foreach ($products as $p) {
            if ($p['id'] == $item['id']) {
                $product = $p;
                break;
            }
        }
        
        if ($product) {
            $itemTotal = $product['price'] * $item['quantity'];
            $subtotal += $itemTotal;
            
            $orderItems[] = [
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'added_at' => date('Y-m-d H:i:s'),
                'product' => $product
            ];
        }
    }
    
    if (empty($orderItems)) {
        return ['success' => false, 'message' => 'No valid products found in cart'];
    }
    
    // Calculate totals
    $tax = $subtotal * 0.08; // 8% tax
    $shipping = $subtotal > 100 ? 0 : 10; // Free shipping over $100
    $total = $subtotal + $tax + $shipping;
    
    // Generate order ID
    $orderId = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Create order
    $order = [
        'id' => $orderId,
        'customer' => $customerData,
        'items' => $orderItems,
        'subtotal' => $subtotal,
        'tax' => $tax,
        'shipping' => $shipping,
        'total' => $total,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Save order
    $ordersFile = __DIR__ . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'orders.json';
    $orders = [];
    if (file_exists($ordersFile)) {
        $orders = json_decode(file_get_contents($ordersFile), true) ?: [];
    }
    
    $orders[] = $order;
    file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT));
    
    // Save customer data
    $customersFile = __DIR__ . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'customers.json';
    $customers = [];
    if (file_exists($customersFile)) {
        $customers = json_decode(file_get_contents($customersFile), true) ?: [];
    }
    
    $customers[] = [
        'id' => uniqid(),
        'name' => $customerData['name'],
        'email' => $customerData['email'],
        'phone' => $customerData['phone'],
        'address' => $customerData['address'],
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents($customersFile, json_encode($customers, JSON_PRETTY_PRINT));
    
    return ['success' => true, 'order_id' => $orderId, 'order' => $order];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - TechHive</title>
    <style>
        :root {
            --primary-indigo: #4A088C;
            --secondary-blue: #120540;
            --accent-blue: #433C73;
            --light-purple: #AEA7D9;
            --neutral-blue: #727FA6;
            --white: #ffffff;
            --black: #000000;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            color: var(--secondary-blue);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            background: var(--white);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-indigo);
            text-decoration: none;
        }

        .page-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title h1 {
            font-size: 2.5rem;
            color: var(--primary-indigo);
            margin-bottom: 0.5rem;
        }

        .page-title p {
            color: var(--neutral-blue);
            font-size: 1.1rem;
        }

        .checkout-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .checkout-form {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--secondary-blue);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--light-purple);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--secondary-blue);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-indigo);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .order-summary {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .summary-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--secondary-blue);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--light-purple), var(--accent-blue));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.2rem;
            margin-right: 1rem;
            overflow: hidden;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: var(--secondary-blue);
            margin-bottom: 0.25rem;
        }

        .item-quantity {
            color: var(--neutral-blue);
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: bold;
            color: var(--primary-indigo);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }

        .summary-row.total {
            border-top: 2px solid var(--light-purple);
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-indigo);
            margin-top: 1rem;
        }

        .place-order-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-indigo), var(--accent-blue));
            color: var(--white);
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }

        .place-order-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(74, 8, 140, 0.3);
        }

        .place-order-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
        }

        .empty-cart {
            text-align: center;
            padding: 3rem;
            color: var(--neutral-blue);
        }

        .empty-cart h3 {
            margin-bottom: 1rem;
            color: var(--secondary-blue);
        }

        .empty-cart a {
            color: var(--primary-indigo);
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <nav class="nav">
            <a href="index.html" class="logo">TechHive</a>
        </nav>
    </div>

    <div class="container">
        <div class="page-title">
            <h1>Checkout</h1>
            <p>Complete your order securely</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="checkout-container">
            <form class="checkout-form" method="POST" id="checkoutForm">
                <input type="hidden" name="cart_data" id="cartData">
                
        <div class="form-section">
            <h3 class="section-title">Shipping Information</h3>
            <?php if (isset($_SESSION['customer_id'])): ?>
                <div class="form-group">
                    <label>Logged in as: <?php echo htmlspecialchars($_SESSION['customer_name']); ?></label>
                    <div style="background: #e8f5e8; padding: 1rem; border-radius: 8px; color: #2e7d32; margin-bottom: 1rem;">
                        ✓ You're logged in! Your information will be automatically filled.
                    </div>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <div style="background: #fff3cd; padding: 1rem; border-radius: 8px; color: #856404; margin-bottom: 1rem;">
                        💡 <a href="customer/login.php" style="color: #856404; font-weight: 600;">Login to your account</a> to save time and track your orders!
                    </div>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" required 
                       value="<?php echo isset($_SESSION['customer_name']) ? htmlspecialchars($_SESSION['customer_name']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_SESSION['customer_email']) ? htmlspecialchars($_SESSION['customer_email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
        </div>

                <div class="form-section">
                    <h3 class="section-title">Billing Address</h3>
                    <div class="form-group">
                        <label for="street">Street Address *</label>
                        <input type="text" id="street" name="street" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        <div class="form-group">
                            <label for="state">State *</label>
                            <input type="text" id="state" name="state" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="zip">ZIP Code *</label>
                            <input type="text" id="zip" name="zip" required>
                        </div>
                        <div class="form-group">
                            <label for="country">Country *</label>
                            <select id="country" name="country" required>
                                <option value="PH">Philippines</option>
                                <option value="US">United States</option>
                                <option value="CA">Canada</option>
                                <option value="MX">Mexico</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Payment Information</h3>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center; color: var(--neutral-blue);">
                            💳 Payment will be processed securely at delivery
                        </div>
                    </div>
                </div>
            </form>

            <div class="order-summary" id="orderSummary">
                <h3 class="summary-title">Order Summary</h3>
                <div class="empty-cart">
                    <h3>Loading cart...</h3>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load cart from localStorage
        let cart = JSON.parse(localStorage.getItem('techhive_cart')) || [];
        
        // Redirect if cart is empty
        if (cart.length === 0) {
            document.querySelector('.order-summary').innerHTML = `
                <div class="empty-cart">
                    <h3>Your cart is empty</h3>
                    <p>Please add items to your cart before checkout.</p>
                    <a href="index.html">Continue Shopping</a>
                </div>
            `;
        } else {
            loadOrderSummary();
        }

        function loadOrderSummary() {
            const orderSummary = document.getElementById('orderSummary');
            let total = 0;
            let cartItemsHTML = '';

            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                
                cartItemsHTML += `
                    <div class="order-item">
                        <div class="item-image">
                            ${item.image ? `<img src="${item.image}" alt="${item.name}">` : '📦'}
                        </div>
                        <div class="item-details">
                            <div class="item-name">${item.name}</div>
                            <div class="item-quantity">Qty: ${item.quantity}</div>
                        </div>
                        <div class="item-price">₱${itemTotal.toLocaleString()}</div>
                    </div>
                `;
            });

            const subtotal = total;
            const tax = subtotal * 0.08;
            const shipping = subtotal > 100 ? 0 : 10;
            const grandTotal = subtotal + tax + shipping;

            orderSummary.innerHTML = `
                <h3 class="summary-title">Order Summary</h3>
                ${cartItemsHTML}
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>₱${subtotal.toLocaleString()}</span>
                </div>
                <div class="summary-row">
                    <span>Tax (8%):</span>
                    <span>₱${tax.toLocaleString()}</span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>${shipping > 0 ? '₱' + shipping.toLocaleString() : 'FREE'}</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>₱${grandTotal.toLocaleString()}</span>
                </div>
                <button type="submit" class="place-order-btn" onclick="submitOrder()">
                    Place Order
                </button>
            `;
        }

        function submitOrder() {
            // Set cart data in hidden input
            document.getElementById('cartData').value = JSON.stringify(cart);
            
            // Submit form
            document.getElementById('checkoutForm').submit();
        }
    </script>
</body>
</html>