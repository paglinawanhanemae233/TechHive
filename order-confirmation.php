<?php
/**
 * TechHive Order Confirmation
 * Displays order confirmation after successful checkout
 */

require_once 'includes/session.php';

$orderId = $_GET['order_id'] ?? '';

if (empty($orderId)) {
    header('Location: index.html');
    exit;
}

// Load order data
$ordersFile = __DIR__ . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'orders.json';
$orders = [];
if (file_exists($ordersFile)) {
    $orders = json_decode(file_get_contents($ordersFile), true) ?: [];
}

$order = null;
if (is_array($orders)) {
    foreach ($orders as $o) {
        if (is_array($o) && isset($o['id']) && $o['id'] === $orderId) {
            $order = $o;
            break;
        }
    }
}

if (!$order) {
    // Debug: Show what we have
    echo "<h1>Debug Information</h1>";
    echo "<p>Order ID requested: " . htmlspecialchars($orderId) . "</p>";
    echo "<p>Orders file: " . htmlspecialchars($ordersFile) . "</p>";
    echo "<p>File exists: " . (file_exists($ordersFile) ? 'YES' : 'NO') . "</p>";
    echo "<p>Orders data type: " . gettype($orders) . "</p>";
    echo "<p>Orders content: " . htmlspecialchars(json_encode($orders, JSON_PRETTY_PRINT)) . "</p>";
    echo "<p><a href='index.html'>Back to Home</a></p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - TechHive</title>
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
            max-width: 800px;
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

        .confirmation-container {
            background: var(--white);
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }

        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
        }

        .confirmation-title {
            font-size: 2.5rem;
            color: var(--primary-indigo);
            margin-bottom: 1rem;
        }

        .confirmation-subtitle {
            color: var(--neutral-blue);
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .order-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 2rem;
            margin: 2rem 0;
            text-align: left;
        }

        .order-id {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--secondary-blue);
            margin-bottom: 1rem;
            text-align: center;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.5rem 0;
        }

        .detail-row.total {
            border-top: 2px solid var(--light-purple);
            font-weight: bold;
            font-size: 1.1rem;
            color: var(--primary-indigo);
            margin-top: 1rem;
        }

        .order-items {
            margin: 2rem 0;
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

        .next-steps {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 10px;
            padding: 2rem;
            margin: 2rem 0;
        }

        .next-steps h3 {
            color: var(--secondary-blue);
            margin-bottom: 1rem;
        }

        .next-steps ul {
            list-style: none;
            padding: 0;
        }

        .next-steps li {
            padding: 0.5rem 0;
            color: var(--neutral-blue);
        }

        .next-steps li::before {
            content: '✓ ';
            color: #28a745;
            font-weight: bold;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-indigo), var(--accent-blue));
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(74, 8, 140, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: var(--primary-indigo);
            border: 2px solid var(--primary-indigo);
        }

        .btn-secondary:hover {
            background: var(--primary-indigo);
            color: var(--white);
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
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
        <div class="confirmation-container">
            <div class="success-icon">✅</div>
            <h1 class="confirmation-title">Order Confirmed!</h1>
            <p class="confirmation-subtitle">Thank you for your purchase. Your order has been successfully placed.</p>

            <div class="order-details">
                <div class="order-id">Order #<?php echo htmlspecialchars($order['id']); ?></div>
                
                <div class="detail-row">
                    <span>Order Date:</span>
                    <span><?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <span>Customer:</span>
                    <span><?php echo htmlspecialchars($order['customer']['name']); ?></span>
                </div>
                <div class="detail-row">
                    <span>Email:</span>
                    <span><?php echo htmlspecialchars($order['customer']['email']); ?></span>
                </div>
                <div class="detail-row">
                    <span>Phone:</span>
                    <span><?php echo htmlspecialchars($order['customer']['phone']); ?></span>
                </div>
                <div class="detail-row">
                    <span>Status:</span>
                    <span style="color: #28a745; font-weight: bold;"><?php echo ucfirst($order['status']); ?></span>
                </div>
            </div>

            <div class="order-items">
                <h3 style="color: var(--secondary-blue); margin-bottom: 1rem;">Order Items</h3>
                <?php foreach ($order['items'] as $item): ?>
                    <div class="order-item">
                        <div class="item-image">
                            <?php if (!empty($item['product']['image'])): ?>
                                <img src="<?php echo $item['product']['image']; ?>" alt="<?php echo htmlspecialchars($item['product']['name']); ?>">
                            <?php else: ?>
                                📦
                            <?php endif; ?>
                        </div>
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['product']['name']); ?></div>
                            <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                        </div>
                        <div class="item-price">$<?php echo number_format($item['product']['price'] * $item['quantity'], 2); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="order-details">
                <div class="detail-row">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($order['subtotal'], 2); ?></span>
                </div>
                <div class="detail-row">
                    <span>Tax (8%):</span>
                    <span>$<?php echo number_format($order['tax'], 2); ?></span>
                </div>
                <div class="detail-row">
                    <span>Shipping:</span>
                    <span><?php echo $order['shipping'] > 0 ? '$' . number_format($order['shipping'], 2) : 'FREE'; ?></span>
                </div>
                <div class="detail-row total">
                    <span>Total:</span>
                    <span>$<?php echo number_format($order['total'], 2); ?></span>
                </div>
            </div>

            <div class="next-steps">
                <h3>What's Next?</h3>
                <ul>
                    <li>You will receive an email confirmation shortly</li>
                    <li>Your order will be processed within 1-2 business days</li>
                    <li>You will receive tracking information once your order ships</li>
                    <li>Expected delivery: 3-5 business days</li>
                </ul>
            </div>

            <div class="action-buttons">
                <a href="index.html" class="btn btn-primary">Continue Shopping</a>
                <a href="cart.php" class="btn btn-secondary">View Cart</a>
            </div>
        </div>
    </div>
</body>
</html>
