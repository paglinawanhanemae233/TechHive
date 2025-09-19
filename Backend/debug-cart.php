<?php
/**
 * Debug Cart Data
 * Simple page to debug cart issues
 */

// Load cart from localStorage (simulated)
$cartData = json_decode($_POST['cart_data'] ?? '[]', true);

// Load products data
$productsFile = __DIR__ . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'products.json';
$products = [];
if (file_exists($productsFile)) {
    $products = json_decode(file_get_contents($productsFile), true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Cart - TechHive</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .debug-title { font-weight: bold; color: #333; margin-bottom: 10px; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Cart Debug Information</h1>
    
    <div class="debug-section">
        <div class="debug-title">Products File Path:</div>
        <pre><?php echo $productsFile; ?></pre>
    </div>
    
    <div class="debug-section">
        <div class="debug-title">Products File Exists:</div>
        <pre><?php echo file_exists($productsFile) ? 'YES' : 'NO'; ?></pre>
    </div>
    
    <div class="debug-section">
        <div class="debug-title">Products Loaded:</div>
        <pre><?php echo json_encode($products, JSON_PRETTY_PRINT); ?></pre>
    </div>
    
    <div class="debug-section">
        <div class="debug-title">Product IDs Available:</div>
        <pre><?php echo json_encode(array_column($products, 'id')); ?></pre>
    </div>
    
    <div class="debug-section">
        <div class="debug-title">Cart Data (from POST):</div>
        <pre><?php echo json_encode($cartData, JSON_PRETTY_PRINT); ?></pre>
    </div>
    
    <div class="debug-section">
        <div class="debug-title">Test Cart Data (simulated):</div>
        <pre id="testCartData"></pre>
    </div>
    
    <script>
        // Load cart from localStorage
        let cart = JSON.parse(localStorage.getItem('techhive_cart')) || [];
        document.getElementById('testCartData').textContent = JSON.stringify(cart, null, 2);
        
        // Test product lookup
        console.log('Cart from localStorage:', cart);
        console.log('Product IDs in cart:', cart.map(item => item.id));
    </script>
</body>
</html>
