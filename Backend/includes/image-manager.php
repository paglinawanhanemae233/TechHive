<?php
/**
 * TechHive Image Manager
 * Handles product image uploads and management
 */

class ImageManager {
    
    private $uploadDir;
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    public function __construct($uploadDir = '../assets/images/products/') {
        $this->uploadDir = $uploadDir;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
    }
    
    /**
     * Upload product image
     */
    public function uploadProductImage($productId, $file) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'message' => 'No file uploaded'];
        }
        
        // Validate file
        $validation = $this->validateFile($file);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }
        
        // Generate filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $productId . '_' . time() . '.' . $extension;
        $filepath = $this->uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Create thumbnail
            $this->createThumbnail($filepath, $this->uploadDir . 'thumbs/' . $filename);
            
            return [
                'success' => true, 
                'filename' => $filename,
                'path' => 'assets/images/products/' . $filename,
                'thumbnail' => 'assets/images/products/thumbs/' . $filename
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to upload file'];
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            return ['valid' => false, 'message' => 'File too large. Maximum size is 5MB.'];
        }
        
        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            return ['valid' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', $this->allowedTypes)];
        }
        
        // Check if it's actually an image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['valid' => false, 'message' => 'File is not a valid image'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Create thumbnail
     */
    private function createThumbnail($sourcePath, $thumbPath) {
        // Create thumbs directory if it doesn't exist
        $thumbDir = dirname($thumbPath);
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }
        
        $imageInfo = getimagesize($sourcePath);
        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $sourceType = $imageInfo[2];
        
        // Thumbnail dimensions
        $thumbWidth = 300;
        $thumbHeight = 300;
        
        // Calculate aspect ratio
        $aspectRatio = $sourceWidth / $sourceHeight;
        if ($aspectRatio > 1) {
            $thumbHeight = $thumbWidth / $aspectRatio;
        } else {
            $thumbWidth = $thumbHeight * $aspectRatio;
        }
        
        // Create source image
        switch ($sourceType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }
        
        // Create thumbnail
        $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        // Preserve transparency for PNG and GIF
        if ($sourceType == IMAGETYPE_PNG || $sourceType == IMAGETYPE_GIF) {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
            imagefilledrectangle($thumbImage, 0, 0, $thumbWidth, $thumbHeight, $transparent);
        }
        
        // Resize image
        imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);
        
        // Save thumbnail
        $extension = strtolower(pathinfo($thumbPath, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($thumbImage, $thumbPath, 90);
                break;
            case 'png':
                imagepng($thumbImage, $thumbPath, 9);
                break;
            case 'gif':
                imagegif($thumbImage, $thumbPath);
                break;
            case 'webp':
                imagewebp($thumbImage, $thumbPath, 90);
                break;
        }
        
        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($thumbImage);
        
        return true;
    }
    
    /**
     * Delete product image
     */
    public function deleteProductImage($filename) {
        $mainPath = $this->uploadDir . $filename;
        $thumbPath = $this->uploadDir . 'thumbs/' . $filename;
        
        $deleted = true;
        
        if (file_exists($mainPath)) {
            $deleted = $deleted && unlink($mainPath);
        }
        
        if (file_exists($thumbPath)) {
            $deleted = $deleted && unlink($thumbPath);
        }
        
        return $deleted;
    }
    
    /**
     * Get product images
     */
    public function getProductImages($productId) {
        $images = [];
        $pattern = $this->uploadDir . $productId . '_*';
        $files = glob($pattern);
        
        foreach ($files as $file) {
            $filename = basename($file);
            $images[] = [
                'filename' => $filename,
                'path' => 'assets/images/products/' . $filename,
                'thumbnail' => 'assets/images/products/thumbs/' . $filename,
                'size' => filesize($file),
                'modified' => filemtime($file)
            ];
        }
        
        return $images;
    }
    
    /**
     * Generate placeholder image
     */
    public function generatePlaceholder($productId, $productName, $width = 400, $height = 400) {
        $image = imagecreatetruecolor($width, $height);
        
        // Background color
        $bgColor = imagecolorallocate($image, 240, 240, 240);
        imagefill($image, 0, 0, $bgColor);
        
        // Text color
        $textColor = imagecolorallocate($image, 100, 100, 100);
        
        // Add product name
        $fontSize = 5;
        $text = substr($productName, 0, 20);
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textHeight = imagefontheight($fontSize);
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;
        
        imagestring($image, $fontSize, $x, $y, $text, $textColor);
        
        // Save placeholder
        $filename = $productId . '_placeholder.png';
        $filepath = $this->uploadDir . $filename;
        imagepng($image, $filepath);
        
        // Clean up
        imagedestroy($image);
        
        return [
            'filename' => $filename,
            'path' => 'assets/images/products/' . $filename,
            'thumbnail' => 'assets/images/products/thumbs/' . $filename
        ];
    }
}
?>
