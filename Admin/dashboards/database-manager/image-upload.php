<?php
/**
 * TechHive Product Image Upload
 * Handles image uploads for products
 */

require_once '../../includes/session.php';
require_once '../../includes/auth-functions.php';
require_once '../../includes/data-manager.php';
require_once '../../includes/image-manager.php';

// Require database manager role
requireLogin();
if (!hasRole('database_manager')) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$user = getCurrentUser();
$dataManager = new DataManager();
$imageManager = new ImageManager();

$message = '';
$error = '';

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    $productId = $_POST['product_id'] ?? '';
    $product = $dataManager->getProduct($productId);
    
    if (!$product) {
        $error = 'Product not found';
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $result = $imageManager->uploadProductImage($productId, $_FILES['image']);
        
        if ($result['success']) {
            // Update product with image path
            $updateData = ['image' => $result['path']];
            $updateResult = $dataManager->updateProduct($productId, $updateData);
            
            if ($updateResult['success']) {
                $message = 'Image uploaded successfully!';
            } else {
                $error = 'Image uploaded but failed to update product record';
            }
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'No image file selected or upload error occurred';
    }
}

// Handle image deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
    $productId = $_POST['product_id'] ?? '';
    $filename = $_POST['filename'] ?? '';
    
    if ($imageManager->deleteProductImage($filename)) {
        // Update product to remove image
        $updateData = ['image' => null];
        $dataManager->updateProduct($productId, $updateData);
        $message = 'Image deleted successfully!';
    } else {
        $error = 'Failed to delete image';
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
    <title>Product Image Management - TechHive</title>
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

        .btn-danger {
            background: #dc3545;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .product-card {
            border: 1px solid #e1e5e9;
            border-radius: 10px;
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
            color: var(--neutral-blue);
            font-size: 0.9rem;
            text-align: center;
        }

        .product-info {
            padding: 1rem;
        }

        .product-name {
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: var(--secondary-blue);
        }

        .product-id {
            font-size: 0.8rem;
            color: var(--neutral-blue);
            margin-bottom: 1rem;
        }

        .upload-form {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--secondary-blue);
        }

        .form-group input[type="file"] {
            width: 100%;
            padding: 0.5rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
        }

        .form-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 2rem;
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            color: var(--primary-indigo);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">TechHive Image Management</div>
            <div class="user-info">
                <div><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                <a href="../../auth/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <a href="dashboard.php" class="back-link">← Back to Database Manager Dashboard</a>

        <div class="section">
            <h1 class="section-title">📸 Product Image Management</h1>
            <p>Upload and manage product images. Supported formats: JPG, PNG, GIF, WebP (Max 5MB)</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="section">
            <h2 class="section-title">Products</h2>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if (!empty($product['image']) && file_exists('../../' . $product['image'])): ?>
                            <img src="../../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <div class="placeholder">
                                📷<br>
                                No Image
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-id">ID: <?php echo htmlspecialchars($product['id']); ?></div>
                        
                        <div class="upload-form">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                
                                <div class="form-group">
                                    <label for="image_<?php echo $product['id']; ?>">Upload Image</label>
                                    <input type="file" id="image_<?php echo $product['id']; ?>" name="image" accept="image/*" required>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" name="upload_image" class="btn btn-small">Upload</button>
                                    <?php if (!empty($product['image'])): ?>
                                    <button type="submit" name="delete_image" class="btn btn-small btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this image?')">Delete</button>
                                    <input type="hidden" name="filename" value="<?php echo basename($product['image']); ?>">
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
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
