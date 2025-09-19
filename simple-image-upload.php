<?php
/**
 * Simple Image Upload for TechHive
 * No GD extension required
 */

require_once 'includes/data-manager.php';

$dataManager = new DataManager();
$message = '';
$error = '';

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $productId = $_POST['product_id'];
    $product = $dataManager->getProduct($productId);
    
    if (!$product) {
        $error = 'Product not found';
    } else {
        $uploadDir = 'assets/images/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $file = $_FILES['image'];
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Validate file
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            $error = 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes);
        } elseif ($file['size'] > $maxSize) {
            $error = 'File too large. Maximum size is 5MB.';
        } else {
            // Generate filename
            $filename = $productId . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Update product with image path
                $updateData = ['image' => 'assets/images/products/' . $filename];
                $updateResult = $dataManager->updateProduct($productId, $updateData);
                
                if ($updateResult['success']) {
                    $message = "Image uploaded successfully for {$product['name']}!";
                } else {
                    $error = 'Image uploaded but failed to update product record';
                }
            } else {
                $error = 'Failed to upload file';
            }
        }
    }
}

// Get all products
$products = $dataManager->getAllProducts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product Images - TechHive</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: #f8f9fa;
        }
        
        .header {
            background: linear-gradient(135deg, #4A088C, #120540);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-image .placeholder {
            color: #6c757d;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .product-details {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .upload-form {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #495057;
        }
        
        .form-group input[type="file"] {
            width: 100%;
            padding: 0.5rem;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            background: white;
        }
        
        .btn {
            background: #007bff;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .instructions {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        
        .instructions h3 {
            color: #004085;
            margin-bottom: 1rem;
        }
        
        .instructions ol {
            color: #004085;
            margin-left: 1.5rem;
        }
        
        .instructions li {
            margin-bottom: 0.5rem;
        }
        
        .back-links {
            text-align: center;
            margin-top: 2rem;
        }
        
        .back-links a {
            color: #007bff;
            text-decoration: none;
            margin: 0 1rem;
            font-weight: 500;
        }
        
        .back-links a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📸 Add Product Images</h1>
        <p>Upload images for your products since FileMaker container fields can't be exported</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="instructions">
        <h3>📝 How to Add Images:</h3>
        <ol>
            <li><strong>Select an image file</strong> - JPG, PNG, GIF, or WebP format (Max 5MB)</li>
            <li><strong>Click "Upload Image"</strong> for the product you want to add an image to</li>
            <li><strong>Image will be saved</strong> and automatically appear on the main store page</li>
            <li><strong>Images are stored</strong> in the assets/images/products/ directory</li>
        </ol>
        <p><strong>Note:</strong> This is a workaround for FileMaker's container field limitation. For production, consider using cloud storage or a proper image management system.</p>
    </div>

    <div class="products-grid">
        <?php foreach ($products as $product): ?>
        <div class="product-card">
            <div class="product-image">
                <?php if (!empty($product['image']) && file_exists($product['image'])): ?>
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php else: ?>
                    <div class="placeholder">
                        📷<br>
                        No Image<br>
                        <small>Upload an image below</small>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="product-info">
                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                <div class="product-details">
                    <strong>ID:</strong> <?php echo htmlspecialchars($product['id']); ?><br>
                    <strong>Price:</strong> ₱<?php echo number_format($product['price'], 2); ?><br>
                    <strong>Stock:</strong> <?php echo $product['stock_quantity']; ?> units<br>
                    <strong>SKU:</strong> <?php echo htmlspecialchars($product['sku']); ?>
                </div>
                
                <div class="upload-form">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                        
                        <div class="form-group">
                            <label for="image_<?php echo $product['id']; ?>">Choose Image File</label>
                            <input type="file" id="image_<?php echo $product['id']; ?>" name="image" accept="image/*" required>
                        </div>
                        
                        <button type="submit" class="btn">📤 Upload Image</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="back-links">
        <a href="public/admin.php">🏠 Admin Portal</a>
        <a href="dashboards/database-manager/dashboard.php">📊 Database Manager</a>
        <a href="index.html">🛒 Main Store</a>
    </div>

    <script>
        // Preview image before upload
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const productCard = input.closest('.product-card');
                        const imageContainer = productCard.querySelector('.product-image');
                        imageContainer.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</body>
</html>
