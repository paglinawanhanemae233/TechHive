<?php
/**
 * TechHive Data Manager
 * Handles JSON data operations for the Database Manager role
 */

class DataManager {
    
    private $dataDir;
    
    public function __construct($dataDir = null) {
        if ($dataDir === null) {
            // Get the absolute path to the data directory
            $this->dataDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR;
        } else {
            $this->dataDir = $dataDir;
        }
    }
    
    /**
     * Get the current data directory path
     */
    public function getDataDir() {
        return $this->dataDir;
    }
    
    /**
     * Load data from JSON file
     */
    public function loadData($filename) {
        $filepath = $this->dataDir . $filename;
        if (!file_exists($filepath)) {
            return [];
        }
        
        $content = file_get_contents($filepath);
        $data = json_decode($content, true);
        
        return is_array($data) ? $data : [];
    }
    
    /**
     * Save data to JSON file
     */
    public function saveData($filename, $data) {
        $filepath = $this->dataDir . $filename;
        
        // Ensure the directory exists
        if (!is_dir($this->dataDir)) {
            if (!mkdir($this->dataDir, 0755, true)) {
                error_log("Failed to create data directory: " . $this->dataDir);
                return false;
            }
        }
        
        $json = json_encode($data, JSON_PRETTY_PRINT);
        
        if ($json === false) {
            error_log("Failed to encode JSON data: " . json_last_error_msg());
            return false;
        }
        
        $result = file_put_contents($filepath, $json);
        
        if ($result === false) {
            error_log("Failed to write to file: " . $filepath);
            error_log("Directory exists: " . (is_dir($this->dataDir) ? 'Yes' : 'No'));
            error_log("Directory writable: " . (is_writable($this->dataDir) ? 'Yes' : 'No'));
            error_log("File path: " . $filepath);
        }
        
        return $result !== false;
    }
    
    /**
     * Get all data files info
     */
    public function getDataFilesInfo() {
        $files = [
            'products.json' => 'Product Catalog',
            'categories.json' => 'Product Categories',
            'brands.json' => 'Brand Information',
            'customers.json' => 'Customer Data',
            'orders.json' => 'Order Data',
            'users.json' => 'User Accounts'
        ];
        
        $info = [];
        foreach ($files as $file => $name) {
            $filepath = $this->dataDir . $file;
            if (file_exists($filepath)) {
                $data = $this->loadData($file);
                $info[$file] = [
                    'name' => $name,
                    'records' => count($data),
                    'size' => filesize($filepath),
                    'last_modified' => date('M j, Y g:i A', filemtime($filepath)),
                    'valid' => json_last_error() === JSON_ERROR_NONE
                ];
            }
        }
        
        return $info;
    }
    
    /**
     * Add new product
     */
    public function addProduct($productData) {
        $products = $this->loadData('products.json');
        
        // Generate new product ID
        $newId = 'PROD-' . str_pad(count($products) + 1, 3, '0', STR_PAD_LEFT);
        
        $product = [
            'id' => $newId,
            'name' => $productData['name'],
            'sku' => $productData['sku'],
            'brand_id' => (int) $productData['brand_id'],
            'category_id' => (int) $productData['category_id'],
            'price' => (float) $productData['price'],
            'cost_price' => (float) $productData['cost_price'],
            'stock_quantity' => (int) $productData['stock_quantity'],
            'minimum_stock' => (int) $productData['minimum_stock'],
            'short_description' => $productData['short_description'],
            'long_description' => $productData['long_description'],
            'meta_title' => $productData['meta_title'] ?? '',
            'meta_description' => $productData['meta_description'] ?? '',
            'dimensions' => $productData['dimensions'] ?? '',
            'weight' => (float) ($productData['weight'] ?? 0),
            'tags' => $productData['tags'] ?? '',
            'image' => $productData['image'] ?? '',
            'is_active' => (bool) ($productData['is_active'] ?? true),
            'is_featured' => (bool) ($productData['is_featured'] ?? false),
            'date_added' => date('Y-m-d'),
            'date_modified' => null
        ];
        
        $products[] = $product;
        
        if ($this->saveData('products.json', $products)) {
            return ['success' => true, 'product' => $product];
        } else {
            return ['success' => false, 'message' => 'Failed to save product'];
        }
    }
    
    /**
     * Update product
     */
    public function updateProduct($productId, $productData) {
        $products = $this->loadData('products.json');
        
        foreach ($products as &$product) {
            if ($product['id'] === $productId) {
                // Update fields
                foreach ($productData as $key => $value) {
                    if (isset($product[$key])) {
                        if (in_array($key, ['price', 'cost_price', 'weight'])) {
                            $product[$key] = (float) $value;
                        } elseif (in_array($key, ['stock_quantity', 'minimum_stock', 'brand_id', 'category_id'])) {
                            $product[$key] = (int) $value;
                        } elseif (in_array($key, ['is_active', 'is_featured'])) {
                            $product[$key] = (bool) $value;
                        } else {
                            $product[$key] = $value;
                        }
                    }
                }
                $product['date_modified'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        if ($this->saveData('products.json', $products)) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Failed to update product'];
        }
    }
    
    /**
     * Delete product
     */
    public function deleteProduct($productId) {
        $products = $this->loadData('products.json');
        
        $filteredProducts = array_filter($products, function($product) use ($productId) {
            return $product['id'] !== $productId;
        });
        
        if ($this->saveData('products.json', array_values($filteredProducts))) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Failed to delete product'];
        }
    }
    
    /**
     * Get product by ID
     */
    public function getProduct($productId) {
        $products = $this->loadData('products.json');
        
        foreach ($products as $product) {
            if ($product['id'] === $productId) {
                return $product;
            }
        }
        
        return null;
    }
    
    /**
     * Get all products
     */
    public function getAllProducts() {
        return $this->loadData('products.json');
    }
    
    /**
     * Get products by category
     */
    public function getProductsByCategory($categoryId) {
        $products = $this->loadData('products.json');
        
        return array_filter($products, function($product) use ($categoryId) {
            return $product['category_id'] == $categoryId;
        });
    }
    
    /**
     * Get low stock products
     */
    public function getLowStockProducts() {
        $products = $this->loadData('products.json');
        
        return array_filter($products, function($product) {
            return $product['stock_quantity'] <= $product['minimum_stock'];
        });
    }
    
    /**
     * Add new category
     */
    public function addCategory($categoryData) {
        $categories = $this->loadData('categories.json');
        
        // Generate new category ID
        $newId = count($categories) + 1;
        
        $category = [
            'id' => $newId,
            'name' => $categoryData['name'],
            'description' => $categoryData['description'],
            'parent_id' => (int) ($categoryData['parent_id'] ?? 0),
            'sort_order' => (int) ($categoryData['sort_order'] ?? 0),
            'url_slug' => $categoryData['url_slug'] ?? strtolower(str_replace(' ', '-', $categoryData['name'])),
            'is_active' => (bool) ($categoryData['is_active'] ?? true)
        ];
        
        $categories[] = $category;
        
        if ($this->saveData('categories.json', $categories)) {
            return ['success' => true, 'category' => $category];
        } else {
            return ['success' => false, 'message' => 'Failed to save category'];
        }
    }
    
    /**
     * Get all categories
     */
    public function getAllCategories() {
        return $this->loadData('categories.json');
    }
    
    /**
     * Get all brands
     */
    public function getAllBrands() {
        return $this->loadData('brands.json');
    }
    
    /**
     * Validate JSON data
     */
    public function validateData($filename) {
        $data = $this->loadData($filename);
        $errors = [];
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errors[] = 'Invalid JSON format: ' . json_last_error_msg();
        }
        
        // Additional validation based on file type
        switch ($filename) {
            case 'products.json':
                foreach ($data as $index => $product) {
                    if (empty($product['name'])) {
                        $errors[] = "Product at index $index has no name";
                    }
                    if (!is_numeric($product['price'])) {
                        $errors[] = "Product '{$product['name']}' has invalid price";
                    }
                }
                break;
                
            case 'categories.json':
                foreach ($data as $index => $category) {
                    if (empty($category['name'])) {
                        $errors[] = "Category at index $index has no name";
                    }
                }
                break;
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Get data statistics
     */
    public function getDataStatistics() {
        $files = $this->getDataFilesInfo();
        
        $stats = [
            'total_files' => count($files),
            'total_records' => array_sum(array_column($files, 'records')),
            'valid_files' => count(array_filter($files, function($f) { return $f['valid']; })),
            'total_size' => array_sum(array_column($files, 'size')),
            'files' => $files
        ];
        
        return $stats;
    }
    
    /**
     * Backup all data
     */
    public function backupData() {
        $backupDir = $this->dataDir . 'backups/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $backupDir . "backup_$timestamp.json";
        
        $allData = [];
        $files = ['products.json', 'categories.json', 'brands.json', 'customers.json', 'orders.json', 'users.json'];
        
        foreach ($files as $file) {
            $allData[$file] = $this->loadData($file);
        }
        
        $allData['backup_info'] = [
            'created' => date('Y-m-d H:i:s'),
            'files_count' => count($files),
            'total_records' => array_sum(array_map('count', $allData))
        ];
        
        if (file_put_contents($backupFile, json_encode($allData, JSON_PRETTY_PRINT))) {
            return ['success' => true, 'backup_file' => $backupFile];
        } else {
            return ['success' => false, 'message' => 'Failed to create backup'];
        }
    }
}
?>
