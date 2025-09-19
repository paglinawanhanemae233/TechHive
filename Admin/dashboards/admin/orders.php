<?php
/**
 * TechHive Admin Orders Management
 * View and manage customer orders
 */

require_once '../../includes/session.php';
require_once '../../includes/auth-functions.php';

// Check if user is logged in and has admin role
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../auth/login.php');
    exit;
}

// Load orders data
$ordersFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'orders.json';
$orders = [];
if (file_exists($ordersFile)) {
    $orders = json_decode(file_get_contents($ordersFile), true) ?: [];
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = $_POST['order_id'] ?? '';
    $newStatus = $_POST['status'] ?? '';
    
    if (!empty($orderId) && !empty($newStatus)) {
        foreach ($orders as &$order) {
            if ($order['id'] === $orderId) {
                $order['status'] = $newStatus;
                $order['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT));
        $success = "Order status updated successfully!";
    }
}

// Get status counts
$statusCounts = [
    'pending' => 0,
    'processing' => 0,
    'shipped' => 0,
    'delivered' => 0,
    'cancelled' => 0
];

foreach ($orders as $order) {
    $status = $order['status'] ?? 'pending';
    if (isset($statusCounts[$status])) {
        $statusCounts[$status]++;
    }
}

$totalOrders = count($orders);
$totalRevenue = array_sum(array_column($orders, 'total'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - TechHive Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        .orders-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2.5rem;
            color: var(--primary-indigo);
            margin-bottom: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-indigo);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--neutral-blue);
            font-weight: 600;
        }

        .orders-table {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            background: linear-gradient(135deg, var(--primary-indigo), var(--accent-blue));
            color: var(--white);
            padding: 1.5rem;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--secondary-blue);
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cce5ff;
            color: #004085;
        }

        .status-shipped {
            background: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-indigo);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--secondary-blue);
        }

        .btn-secondary {
            background: var(--neutral-blue);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: var(--accent-blue);
        }

        .order-details {
            max-width: 600px;
            background: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin: 1rem 0;
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
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--light-purple), var(--accent-blue));
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
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
        }

        .item-quantity {
            color: var(--neutral-blue);
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: bold;
            color: var(--primary-indigo);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: var(--white);
            margin: 5% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light-purple);
        }

        .close {
            font-size: 2rem;
            cursor: pointer;
            color: var(--neutral-blue);
        }

        .close:hover {
            color: var(--primary-indigo);
        }

        @media (max-width: 768px) {
            .orders-container {
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .table {
                font-size: 0.9rem;
            }
            
            .table th,
            .table td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="orders-container">
        <div class="page-header">
            <h1 class="page-title">Order Management</h1>
            <p>Manage customer orders and track order status</p>
        </div>

        <?php if (isset($success)): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalOrders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">$<?php echo number_format($totalRevenue, 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $statusCounts['pending']; ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $statusCounts['delivered']; ?></div>
                <div class="stat-label">Delivered Orders</div>
            </div>
        </div>

        <div class="orders-table">
            <div class="table-header">
                Recent Orders
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($orders) as $order): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($order['id']); ?></strong></td>
                            <td>
                                <div><?php echo htmlspecialchars($order['customer']['name']); ?></div>
                                <div style="font-size: 0.8rem; color: var(--neutral-blue);"><?php echo htmlspecialchars($order['customer']['email']); ?></div>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                            <td><?php echo count($order['items']); ?> items</td>
                            <td><strong>$<?php echo number_format($order['total'], 2); ?></strong></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-primary" onclick="viewOrder('<?php echo $order['id']; ?>')">View</button>
                                <button class="btn btn-secondary" onclick="updateStatus('<?php echo $order['id']; ?>', '<?php echo $order['status']; ?>')">Update</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div id="orderDetails"></div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Order Status</h2>
                <span class="close" onclick="closeStatusModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" id="statusOrderId">
                <div style="margin-bottom: 1rem;">
                    <label for="statusSelect" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">New Status:</label>
                    <select name="status" id="statusSelect" style="width: 100%; padding: 0.8rem; border: 2px solid #e9ecef; border-radius: 5px;">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </form>
        </div>
    </div>

    <script>
        function viewOrder(orderId) {
            // This would typically fetch order details via AJAX
            // For now, we'll show a simple message
            document.getElementById('orderDetails').innerHTML = `
                <p>Order details for ${orderId} would be loaded here.</p>
                <p>This would include customer information, order items, shipping address, and order history.</p>
            `;
            document.getElementById('orderModal').style.display = 'block';
        }

        function updateStatus(orderId, currentStatus) {
            document.getElementById('statusOrderId').value = orderId;
            document.getElementById('statusSelect').value = currentStatus;
            document.getElementById('statusModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const orderModal = document.getElementById('orderModal');
            const statusModal = document.getElementById('statusModal');
            if (event.target === orderModal) {
                orderModal.style.display = 'none';
            }
            if (event.target === statusModal) {
                statusModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
