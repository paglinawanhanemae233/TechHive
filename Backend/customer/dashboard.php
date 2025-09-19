<?php
/**
 * TechHive Customer Dashboard
 * Shows customer orders and account information
 */

require_once '../includes/session.php';

// Redirect if not logged in as customer
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$customerId = $_SESSION['customer_id'];
$customerName = $_SESSION['customer_name'];
$customerEmail = $_SESSION['customer_email'];

// Load customer data
$customersFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'customers.json';
$customers = [];
if (file_exists($customersFile)) {
    $customers = json_decode(file_get_contents($customersFile), true) ?: [];
}

$customer = null;
if (is_array($customers)) {
    foreach ($customers as $c) {
        if (is_array($c) && isset($c['id']) && $c['id'] === $customerId) {
            $customer = $c;
            break;
        }
    }
}

// Load orders for this customer
$ordersFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'orders.json';
$orders = [];
if (file_exists($ordersFile)) {
    $allOrders = json_decode(file_get_contents($ordersFile), true) ?: [];
    // Filter orders for this customer
    foreach ($allOrders as $order) {
        if (isset($order['customer']['email']) && $order['customer']['email'] === $customerEmail) {
            $orders[] = $order;
        }
    }
}

// Sort orders by date (newest first)
usort($orders, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - TechHive</title>
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

        .nav-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .welcome {
            color: var(--neutral-blue);
            font-weight: 500;
        }

        .logout-btn {
            background: var(--primary-indigo);
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: var(--accent-blue);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem 2rem;
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

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }

        .account-info {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--secondary-blue);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--light-purple);
        }

        .info-item {
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }

        .info-label {
            font-weight: 600;
            color: var(--secondary-blue);
            margin-bottom: 0.25rem;
        }

        .info-value {
            color: var(--neutral-blue);
        }

        .orders-section {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .order-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .order-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .order-id {
            font-weight: bold;
            color: var(--primary-indigo);
            font-size: 1.1rem;
        }

        .order-date {
            color: var(--neutral-blue);
            font-size: 0.9rem;
        }

        .order-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-shipped {
            background: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
        }

        .order-items {
            margin-bottom: 1rem;
        }

        .order-item-detail {
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .order-item-detail:last-child {
            border-bottom: none;
        }

        .item-name {
            flex: 1;
            font-weight: 500;
            color: var(--secondary-blue);
        }

        .item-quantity {
            color: var(--neutral-blue);
            margin-right: 1rem;
        }

        .item-price {
            font-weight: bold;
            color: var(--primary-indigo);
        }

        .order-total {
            text-align: right;
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--primary-indigo);
            border-top: 2px solid var(--light-purple);
            padding-top: 0.5rem;
        }

        .no-orders {
            text-align: center;
            padding: 3rem;
            color: var(--neutral-blue);
        }

        .no-orders h3 {
            margin-bottom: 1rem;
            color: var(--secondary-blue);
        }

        .no-orders a {
            color: var(--primary-indigo);
            text-decoration: none;
            font-weight: 600;
        }

        .quick-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-primary {
            background: var(--primary-indigo);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--accent-blue);
            transform: translateY(-2px);
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
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .quick-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <nav class="nav">
            <a href="../index.html" class="logo">TechHive</a>
            <div class="nav-actions">
                <span class="welcome">Welcome, <?php echo htmlspecialchars($customerName); ?>!</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </nav>
    </div>

    <div class="container">
        <div class="page-title">
            <h1>My Account</h1>
            <p>Manage your orders and account information</p>
        </div>

        <div class="dashboard-grid">
            <div class="account-info">
                <h3 class="section-title">Account Information</h3>
                <div class="info-item">
                    <div class="info-label">Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($customerName); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($customerEmail); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Phone</div>
                    <div class="info-value"><?php echo htmlspecialchars($customer['phone'] ?? $customer['phone_number'] ?? 'Not provided'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Member Since</div>
                    <div class="info-value"><?php echo date('F j, Y', strtotime($customer['date_registered'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Last Login</div>
                    <div class="info-value"><?php echo $customer['last_login'] ? date('F j, Y g:i A', strtotime($customer['last_login'])) : 'Never'; ?></div>
                </div>
            </div>

            <div class="orders-section">
                <h3 class="section-title">Order History</h3>
                
                <?php if (empty($orders)): ?>
                    <div class="no-orders">
                        <h3>No orders yet</h3>
                        <p>You haven't placed any orders yet. Start shopping to see your order history here.</p>
                        <a href="../index.html">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-item">
                            <div class="order-header">
                                <div>
                                    <div class="order-id">Order #<?php echo htmlspecialchars($order['id']); ?></div>
                                    <div class="order-date"><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></div>
                                </div>
                                <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </div>
                            </div>
                            
                            <div class="order-items">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="order-item-detail">
                                        <div class="item-name"><?php echo htmlspecialchars($item['product']['name']); ?></div>
                                        <div class="item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                                        <div class="item-price">₱<?php echo number_format($item['product']['price'] * $item['quantity']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="order-total">
                                Total: ₱<?php echo number_format($order['total']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="quick-actions">
            <a href="../index.html" class="btn btn-primary">Continue Shopping</a>
            <a href="../cart.php" class="btn btn-secondary">View Cart</a>
        </div>
    </div>
</body>
</html>
