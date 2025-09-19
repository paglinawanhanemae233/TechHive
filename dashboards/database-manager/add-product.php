<?php
/**
 * TechHive Add Product Form
 * Handles adding new products to the system
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

// Load brands and categories for the form
$brands = $dataManager->loadData('brands.json');
$categories = $dataManager->loadData('categories.json');

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $productData = [
        'name' => trim($_POST['name']),
        'sku' => trim($_POST['sku']),
        'brand_id' => (int) $_POST['brand_id'],
        'category_id' => (int) $_POST['category_id'],
        'price' => (float) $_POST['price'],
        'cost_price' => (float) $_POST['cost_price'],
        'stock_quantity' => (int) $_POST['stock_quantity'],
        'minimum_stock' => (int) $_POST['minimum_stock'],
        'short_description' => trim($_POST['short_description']),
        'long_description' => trim($_POST['long_description']),
        'meta_title' => trim($_POST['meta_title']),
        'meta_description' => trim($_POST['meta_description']),
        'dimensions' => trim($_POST['dimensions']),
        'weight' => (float) $_POST['weight'],
        'tags' => trim($_POST['tags']),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0
    ];
    
    // Handle image upload
    $imagePath = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/products/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['product_image']['name']);
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $filePath)) {
                $imagePath = 'uploads/products/' . $fileName;
            } else {
                $error = 'Failed to upload image';
            }
        } else {
            $error = 'Invalid image format. Please upload JPG, PNG, or GIF files only.';
        }
    }
    
    // Add image path to product data
    $productData['image'] = $imagePath;
    
    // Debug: Show what data we received
    if (isset($_POST['debug'])) {
        echo "<pre>POST Data: ";
        print_r($_POST);
        echo "\nProduct Data: ";
        print_r($productData);
        echo "\nDataManager Data Directory: " . $dataManager->getDataDir();
        echo "\nData Directory Exists: " . (is_dir($dataManager->getDataDir()) ? 'Yes' : 'No');
        echo "\nData Directory Writable: " . (is_writable($dataManager->getDataDir()) ? 'Yes' : 'No');
        echo "</pre>";
    }
    
    // Validate required fields
    if (empty($productData['name'])) {
        $error = 'Product name is required';
    } elseif (empty($productData['sku'])) {
        $error = 'Product SKU is required';
    } elseif (empty($productData['brand_id'])) {
        $error = 'Please select a gadget brand';
    } elseif (empty($productData['category_id'])) {
        $error = 'Please select a gadget category';
    } elseif ($productData['price'] <= 0) {
        $error = 'Price must be greater than 0';
    } else {
        $result = $dataManager->addProduct($productData);
        
        if ($result['success']) {
            $message = 'Product added successfully!' . ($imagePath ? ' Image uploaded.' : '');
            // Clear form data
            $productData = [];
        } else {
            $error = $result['message'];
        }
    }
}

// Get brands and categories for dropdowns
$brands = $dataManager->getAllBrands();
$categories = $dataManager->getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - TechHive Database Manager</title>
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
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .form-card {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .form-title {
            font-size: 1.8rem;
            color: var(--accent-blue);
            margin-bottom: 1.5rem;
            text-align: center;
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-blue);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .checkbox-group {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin-right: 0.5rem;
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
            font-size: 1rem;
        }

        .btn:hover {
            background: var(--primary-indigo);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
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

        .required {
            color: #dc3545;
        }

        .image-upload-container {
            border: 2px dashed #e1e5e9;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            transition: border-color 0.3s ease;
            cursor: pointer;
        }

        .image-upload-container:hover {
            border-color: var(--accent-blue);
        }

        .image-upload-container input[type="file"] {
            display: none;
        }

        .upload-placeholder {
            padding: 2rem;
            color: #6c757d;
        }

        .upload-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .upload-placeholder p {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .upload-placeholder small {
            color: #adb5bd;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .image-actions {
            margin-top: 1rem;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">TechHive - Add Product</div>
            <div class="user-info">
                <div><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                <a href="../../auth/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <a href="dashboard.php" class="back-link">← Back to Database Manager Dashboard</a>

        <div class="form-card">
            <h1 class="form-title">➕ Add New Product</h1>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Product Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="sku">SKU <span class="required">*</span></label>
                        <input type="text" id="sku" name="sku" value="<?php echo htmlspecialchars($_POST['sku'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="brand_id">Gadget Brand <span class="required">*</span></label>
                        <select id="brand_id" name="brand_id" required>
                            <option value="">Select Gadget Brand</option>
                            <?php foreach ($brands as $brand): ?>
                                <?php if ($brand['is_active']): ?>
                                    <option value="<?php echo $brand['id']; ?>" <?php echo (isset($_POST['brand_id']) && $_POST['brand_id'] == $brand['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($brand['name']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Gadget Category <span class="required">*</span></label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Gadget Category</option>
                            <?php foreach ($categories as $category): ?>
                                <?php if ($category['is_active']): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (₱) <span class="required">*</span></label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="cost_price">Cost Price (₱)</label>
                        <input type="number" id="cost_price" name="cost_price" step="0.01" min="0" value="<?php echo htmlspecialchars($_POST['cost_price'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo htmlspecialchars($_POST['stock_quantity'] ?? '0'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="minimum_stock">Minimum Stock</label>
                        <input type="number" id="minimum_stock" name="minimum_stock" min="0" value="<?php echo htmlspecialchars($_POST['minimum_stock'] ?? '0'); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="short_description">Short Description</label>
                    <textarea id="short_description" name="short_description" placeholder="Brief product description..."><?php echo htmlspecialchars($_POST['short_description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="long_description">Long Description</label>
                    <textarea id="long_description" name="long_description" placeholder="Detailed product description..."><?php echo htmlspecialchars($_POST['long_description'] ?? ''); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="meta_title">Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title" value="<?php echo htmlspecialchars($_POST['meta_title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="weight">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" step="0.01" min="0" value="<?php echo htmlspecialchars($_POST['weight'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="meta_description">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" placeholder="SEO description..."><?php echo htmlspecialchars($_POST['meta_description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="dimensions">Dimensions</label>
                    <input type="text" id="dimensions" name="dimensions" placeholder="e.g., 35.8 x 23.5 x 1.9 cm" value="<?php echo htmlspecialchars($_POST['dimensions'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="tags">Tags</label>
                    <input type="text" id="tags" name="tags" placeholder="e.g., Dell, Laptop, Windows 11, Core i5" value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="product_image">Product Image</label>
                    <div class="image-upload-container">
                        <input type="file" id="product_image" name="product_image" accept="image/*" onchange="previewImage(this)">
                        <div class="image-preview" id="imagePreview">
                            <div class="upload-placeholder">
                                <div class="upload-icon">📷</div>
                                <p>Click to upload product image</p>
                                <small>Supports: JPG, PNG, GIF (Max: 5MB)</small>
                            </div>
                        </div>
                        <div class="image-actions" id="imageActions" style="display: none;">
                            <button type="button" onclick="removeImage()" class="btn btn-danger btn-sm">Remove Image</button>
                        </div>
                    </div>
                </div>

                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="is_active" value="1" <?php echo (isset($_POST['is_active']) || !isset($_POST['name'])) ? 'checked' : ''; ?>>
                        Active Product
                    </label>
                    <label>
                        <input type="checkbox" name="is_featured" value="1" <?php echo (isset($_POST['is_featured'])) ? 'checked' : ''; ?>>
                        Featured Product
                    </label>
                </div>

                <div class="btn-group">
                    <button type="submit" name="add_product" class="btn">➕ Add Product</button>
                    <button type="submit" name="debug" class="btn btn-secondary">🔍 Debug</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-generate SKU from product name
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value;
            const sku = name.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().substring(0, 10);
            document.getElementById('sku').value = sku;
        });

        // Image upload functionality
        function previewImage(input) {
            const file = input.files[0];
            const preview = document.getElementById('imagePreview');
            const actions = document.getElementById('imageActions');
            
            if (file) {
                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    input.value = '';
                    return;
                }
                
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('Please select a valid image file');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Product Preview">`;
                    actions.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        function removeImage() {
            const input = document.getElementById('product_image');
            const preview = document.getElementById('imagePreview');
            const actions = document.getElementById('imageActions');
            
            input.value = '';
            preview.innerHTML = `
                <div class="upload-placeholder">
                    <div class="upload-icon">📷</div>
                    <p>Click to upload product image</p>
                    <small>Supports: JPG, PNG, GIF (Max: 5MB)</small>
                </div>
            `;
            actions.style.display = 'none';
        }

        // Click to upload
        document.getElementById('imagePreview').addEventListener('click', function() {
            document.getElementById('product_image').click();
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const sku = document.getElementById('sku').value.trim();
            const price = document.getElementById('price').value;
            const brandId = document.getElementById('brand_id').value;
            const categoryId = document.getElementById('category_id').value;

            if (!name) {
                alert('Product name is required');
                e.preventDefault();
                return;
            }

            if (!sku) {
                alert('Product SKU is required');
                e.preventDefault();
                return;
            }

            if (!brandId) {
                alert('Please select a gadget brand');
                e.preventDefault();
                return;
            }

            if (!categoryId) {
                alert('Please select a gadget category');
                e.preventDefault();
                return;
            }

            if (!price || parseFloat(price) <= 0) {
                alert('Price must be greater than 0');
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html>
