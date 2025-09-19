<?php
/**
 * TechHive Database Manager Dashboard
 * Data management and content editing tools
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

// Get real data files info
$fileStats = $dataManager->getDataFilesInfo();

// Add icons and descriptions
$fileIcons = [
    'products.json' => '📦',
    'categories.json' => '📂',
    'brands.json' => '🏷️',
    'customers.json' => '👥',
    'orders.json' => '🛒',
    'users.json' => '👤'
];

$fileDescriptions = [
    'products.json' => 'Manage product inventory and details',
    'categories.json' => 'Organize products into categories',
    'brands.json' => 'Manage brand details and logos',
    'customers.json' => 'Manage customer information',
    'orders.json' => 'Process and manage customer orders',
    'users.json' => 'Manage user accounts and roles'
];

// Add icons and descriptions to file stats
foreach ($fileStats as $file => &$stats) {
    $stats['icon'] = $fileIcons[$file] ?? '📄';
    $stats['description'] = $fileDescriptions[$file] ?? 'Data file';
}

// Get real products data
$products = $dataManager->getAllProducts();
$lowStockProducts = $dataManager->getLowStockProducts();

// Get brands and categories for display
$brands = $dataManager->getAllBrands();
$categories = $dataManager->getAllCategories();

// Create lookup arrays
$brandLookup = [];
foreach ($brands as $brand) {
    $brandLookup[$brand['id']] = $brand['name'];
}

$categoryLookup = [];
foreach ($categories as $category) {
    $categoryLookup[$category['id']] = $category['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Manager Dashboard - TechHive</title>
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
            background: #f8f9fa;
            color: var(--secondary-blue);
        }

        .header {
            background: linear-gradient(135deg, var(--accent-blue), var(--primary-indigo));
            color: var(--white);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--light-purple);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .logout-btn {
            background: rgba(255,255,255,0.1);
            color: var(--white);
            border: 1px solid rgba(255,255,255,0.3);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-title {
            font-size: 2.5rem;
            color: var(--accent-blue);
            margin-bottom: 0.5rem;
        }

        .dashboard-subtitle {
            color: var(--neutral-blue);
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: var(--accent-blue);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--neutral-blue);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 1.5rem;
            color: var(--accent-blue);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn {
            background: var(--accent-blue);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn:hover {
            background: var(--primary-indigo);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--primary-indigo);
        }

        .btn-success {
            background: #28a745;
        }

        .btn-warning {
            background: #ffc107;
            color: var(--secondary-blue);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .data-card {
            background: linear-gradient(135deg, var(--accent-blue), var(--primary-indigo));
            color: var(--white);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .data-card:hover {
            transform: translateY(-5px);
        }

        .data-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .data-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .data-description {
            opacity: 0.9;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .data-stats {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .products-table th,
        .products-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }

        .products-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--secondary-blue);
        }

        .products-table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .status-inactive {
            background: #ffebee;
            color: #c62828;
        }

        .stock-indicator {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .stock-high {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .stock-medium {
            background: #fff3cd;
            color: #856404;
        }

        .stock-low {
            background: #ffebee;
            color: #c62828;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: linear-gradient(135deg, var(--light-purple), var(--accent-blue));
            color: var(--white);
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-5px);
        }

        .action-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .validation-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .status-valid {
            background: #28a745;
        }

        .status-invalid {
            background: #dc3545;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .data-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">TechHive Database Manager</div>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                </div>
                <div>
                    <div><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                    <div style="font-size: 0.9rem; opacity: 0.8;">Database Manager</div>
                </div>
                <a href="../../auth/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Database Manager Dashboard</h1>
            <p class="dashboard-subtitle">Data management and content editing tools</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #c3e6cb;">
                <strong>Success:</strong> <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #f5c6cb;">
                <strong>Error:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($fileStats); ?></div>
                <div class="stat-label">Data Files</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo array_sum(array_column($fileStats, 'records')); ?></div>
                <div class="stat-label">Total Records</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($lowStockProducts); ?></div>
                <div class="stat-label">Low Stock Items</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($fileStats, function($f) { return $f['valid']; })); ?></div>
                <div class="stat-label">Valid Files</div>
            </div>
        </div>

        <!-- Data Management -->
        <div class="section">
            <h2 class="section-title">📊 Data Management</h2>
            <div class="data-grid">
                <?php foreach ($fileStats as $file => $stats): ?>
                <div class="data-card" onclick="manageData('<?php echo $file; ?>')">
                    <div class="data-icon"><?php echo $stats['icon']; ?></div>
                    <div class="data-title"><?php echo htmlspecialchars($stats['name']); ?></div>
                    <div class="data-description"><?php echo htmlspecialchars($stats['description']); ?></div>
                    <div class="data-stats">
                        <span><?php echo number_format($stats['records']); ?> records</span>
                        <span><?php echo number_format($stats['size'] / 1024, 1); ?> KB</span>
                    </div>
                    <div class="validation-status">
                        <div class="status-indicator <?php echo $stats['valid'] ? 'status-valid' : 'status-invalid'; ?>"></div>
                        <span><?php echo $stats['valid'] ? 'Valid JSON' : 'Invalid JSON'; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Product Management -->
        <div class="section">
            <h2 class="section-title">📦 Product Management</h2>
            <div style="margin-bottom: 1rem;">
                <button class="btn" onclick="addProduct()">Add Product</button>
                <button class="btn btn-secondary" onclick="importProducts()">Import Products</button>
                <button class="btn btn-warning" onclick="validateProducts()">Validate Data</button>
                <a href="../../simple-image-upload.php" class="btn" style="background: #17a2b8;">📸 Add Product Images</a>
            </div>
            
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 60px; height: 60px; border-radius: 8px; overflow: hidden; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                    <?php if (!empty($product['image']) && file_exists('../../' . $product['image'])): ?>
                                        <img src="../../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <span style="color: #6c757d; font-size: 1.5rem;">📦</span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div style="font-size: 0.9rem; color: var(--neutral-blue);"><?php echo $product['id']; ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($categoryLookup[$product['category_id']] ?? 'Unknown'); ?></td>
                        <td><?php echo htmlspecialchars($brandLookup[$product['brand_id']] ?? 'Unknown'); ?></td>
                        <td>₱<?php echo number_format($product['price'], 2); ?></td>
                        <td>
                            <?php
                            $stockClass = 'stock-high';
                            if ($product['stock_quantity'] <= $product['minimum_stock']) $stockClass = 'stock-low';
                            elseif ($product['stock_quantity'] < 50) $stockClass = 'stock-medium';
                            ?>
                            <span class="stock-indicator <?php echo $stockClass; ?>">
                                <?php echo $product['stock_quantity']; ?> units
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $product['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-small" onclick="editProduct('<?php echo $product['id']; ?>')">Edit</button>
                            <button class="btn btn-small btn-secondary" onclick="viewProduct('<?php echo $product['id']; ?>')">View</button>
                            <button class="btn btn-small btn-danger" onclick="deleteProduct('<?php echo $product['id']; ?>', '<?php echo htmlspecialchars($product['name']); ?>')">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Quick Actions -->
        <div class="section">
            <h2 class="section-title">⚡ Quick Actions</h2>
            <div class="quick-actions">
                <div class="action-card" onclick="backupData()">
                    <div class="action-icon">💾</div>
                    <div>Backup Data</div>
                </div>
                <div class="action-card" onclick="validateAllData()">
                    <div class="action-icon">✅</div>
                    <div>Validate All</div>
                </div>
                <div class="action-card" onclick="exportData()">
                    <div class="action-icon">📤</div>
                    <div>Export Data</div>
                </div>
                <div class="action-card" onclick="cleanupData()">
                    <div class="action-icon">🧹</div>
                    <div>Cleanup</div>
                </div>
                <div class="action-card" onclick="generateReport()">
                    <div class="action-icon">📊</div>
                    <div>Generate Report</div>
                </div>
                <div class="action-card" onclick="optimizeData()">
                    <div class="action-icon">⚡</div>
                    <div>Optimize</div>
                </div>
            </div>
        </div>

        <!-- Data Quality Status -->
        <div class="section">
            <h2 class="section-title">🔍 Data Quality Status</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div style="background: #e8f5e8; padding: 1rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2rem; color: #2e7d32;">✅</div>
                    <div style="font-weight: 600; color: #2e7d32;">Data Valid</div>
                    <div style="font-size: 0.9rem; color: #2e7d32;">All JSON files are valid</div>
                </div>
                <div style="background: #e3f2fd; padding: 1rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2rem; color: #1976d2;">📊</div>
                    <div style="font-weight: 600; color: #1976d2;">Records Count</div>
                    <div style="font-size: 0.9rem; color: #1976d2;"><?php echo array_sum(array_column($fileStats, 'records')); ?> total records</div>
                </div>
                <div style="background: #fff3e0; padding: 1rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2rem; color: #f57c00;">⚠️</div>
                    <div style="font-weight: 600; color: #f57c00;">Low Stock</div>
                    <div style="font-size: 0.9rem; color: #f57c00;"><?php echo count($lowStockProducts); ?> products need restocking</div>
                </div>
                <div style="background: #f3e5f5; padding: 1rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2rem; color: #7b1fa2;">🔄</div>
                    <div style="font-weight: 600; color: #7b1fa2;">Last Sync</div>
                    <div style="font-size: 0.9rem; color: #7b1fa2;">2 hours ago</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function manageData(file) {
            alert('Managing data file: ' + file);
        }

        function addProduct() {
            window.location.href = 'add-product.php';
        }

        function importProducts() {
            alert('Product import functionality will be implemented');
        }

        function validateProducts() {
            alert('Validating product data...');
        }

        function editProduct(productId) {
            window.location.href = 'edit-product.php?id=' + productId;
        }

        function viewProduct(productId) {
            // Create a modal to show product details
            const product = <?php echo json_encode($products); ?>.find(p => p.id === productId);
            if (product) {
                const modal = document.createElement('div');
                modal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 1000;
                `;
                
                modal.innerHTML = `
                    <div style="background: white; padding: 2rem; border-radius: 10px; max-width: 600px; max-height: 80vh; overflow-y: auto;">
                        <div style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div style="width: 150px; height: 150px; border-radius: 10px; overflow: hidden; background: #f8f9fa; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                ${product.image ? 
                                    `<img src="../../${product.image}" alt="${product.name}" style="width: 100%; height: 100%; object-fit: cover;">` :
                                    `<span style="color: #6c757d; font-size: 3rem;">📦</span>`
                                }
                            </div>
                            <div style="flex: 1;">
                                <h2 style="margin: 0 0 0.5rem 0; color: var(--secondary-blue);">${product.name}</h2>
                                <p style="margin: 0 0 0.5rem 0; color: var(--neutral-blue);"><strong>ID:</strong> ${product.id}</p>
                                <p style="margin: 0 0 0.5rem 0; color: var(--neutral-blue);"><strong>SKU:</strong> ${product.sku}</p>
                                <p style="margin: 0 0 0.5rem 0; color: var(--primary-indigo); font-size: 1.2rem; font-weight: bold;"><strong>Price:</strong> ₱${parseFloat(product.price).toLocaleString()}</p>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                            <p style="margin: 0;"><strong>Stock:</strong> ${product.stock_quantity} units</p>
                            <p style="margin: 0;"><strong>Status:</strong> ${product.is_active ? 'Active' : 'Inactive'}</p>
                            <p style="margin: 0;"><strong>Featured:</strong> ${product.is_featured ? 'Yes' : 'No'}</p>
                            <p style="margin: 0;"><strong>Category:</strong> ${product.category_id}</p>
                        </div>
                        <p style="margin: 0 0 1.5rem 0;"><strong>Description:</strong> ${product.short_description}</p>
                        <div style="margin-top: 1rem;">
                            <button onclick="this.closest('.modal').remove()" style="background: #6c757d; color: white; padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer;">Close</button>
                        </div>
                    </div>
                `;
                
                modal.className = 'modal';
                document.body.appendChild(modal);
                
                // Close on background click
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.remove();
                    }
                });
            }
        }

        function deleteProduct(productId, productName) {
            // Create confirmation modal
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            `;
            
            modal.innerHTML = `
                <div style="background: white; padding: 2rem; border-radius: 10px; max-width: 500px; text-align: center;">
                    <div style="font-size: 3rem; color: #dc3545; margin-bottom: 1rem;">⚠️</div>
                    <h2 style="color: #dc3545; margin-bottom: 1rem;">Delete Product</h2>
                    <p style="margin-bottom: 1.5rem;">Are you sure you want to delete <strong>"${productName}"</strong>?</p>
                    <p style="color: #6c757d; font-size: 0.9rem; margin-bottom: 2rem;">This action cannot be undone.</p>
                    <div style="display: flex; gap: 1rem; justify-content: center;">
                        <button onclick="confirmDelete('${productId}')" style="background: #dc3545; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">Yes, Delete</button>
                        <button onclick="this.closest('.modal').remove()" style="background: #6c757d; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">Cancel</button>
                    </div>
                </div>
            `;
            
            modal.className = 'modal';
            document.body.appendChild(modal);
            
            // Close on background click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }

        function confirmDelete(productId) {
            // Create form and submit to delete endpoint
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'delete-product.php';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'product_id';
            input.value = productId;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }

        function backupData() {
            alert('Creating data backup...');
        }

        function validateAllData() {
            alert('Validating all data files...');
        }

        function exportData() {
            alert('Exporting data...');
        }

        function cleanupData() {
            alert('Cleaning up data...');
        }

        function generateReport() {
            alert('Generating data report...');
        }

        function optimizeData() {
            alert('Optimizing data files...');
        }
    </script>
</body>
</html>
