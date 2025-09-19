# 📸 TechHive Image Management System

## Overview
Since FileMaker container fields cannot be exported, this system provides an alternative way to add and manage product images for your TechHive e-commerce store.

## 🚀 Quick Start

### 1. Access Image Upload
- **From Admin Portal**: Go to `public/admin.php` → Login as Database Manager → Click "Add Product Images"
- **Direct Access**: Visit `simple-image-upload.php`

### 2. Upload Images
1. Select an image file (JPG, PNG, GIF, WebP - Max 5MB)
2. Click "Upload Image" for the desired product
3. Image will be automatically saved and linked to the product
4. Images appear immediately on the main store page

## 📁 File Structure

```
TechHive/
├── assets/
│   └── images/
│       ├── products/          # Main product images
│       └── team/              # Our Team photos (admin.jpg, php-dev.jpg, ...)
├── includes/
│   ├── image-manager.php      # Image processing class
│   └── data-manager.php       # Product data management
├── simple-image-upload.php    # Main upload interface
└── dashboards/
    └── database-manager/
        └── image-upload.php   # Advanced upload interface
```

## 🛠️ Features

### ✅ What's Included
- **Simple Upload Interface**: Easy-to-use form for adding images
- **Automatic File Naming**: Images are named as `{ProductID}_{timestamp}.{extension}`
- **File Validation**: Checks file type and size before upload
- **Real-time Preview**: See image before uploading
- **Product Integration**: Images automatically link to products
- **Main Store Display**: Images appear on the main store page
 - **Team Photos Support**: Optional headshots for the "Our Team" section

### 📋 Supported Formats
- **JPG/JPEG**: Best for photos
- **PNG**: Best for graphics with transparency
- **GIF**: For simple animations
- **WebP**: Modern format with good compression

### 📏 File Limits
- **Maximum Size**: 5MB per image
- **Recommended Size**: 800x600 pixels or similar
- **Aspect Ratio**: Any ratio supported

## 🔧 Technical Details

### Image Storage
- Product images are stored in `assets/images/products/`
  - Filename format: `{ProductID}_{timestamp}.{extension}` (e.g., `LAP-001_1695123456.jpg`)
- Team photos are stored in `assets/images/team/`
  - Recommended filenames: `admin.jpg`, `php-dev.jpg`, `frontend-dev.jpg`, `db-manager.jpg`
  - Square images (e.g., 500×500) look best; they render as circular avatars

### Database Integration
- Product records are updated with image path
- Image field added to product JSON structure
- Automatic linking between products and images

Team photos are not stored in JSON; `index.html` references the files directly and hides the emoji avatar when the image loads successfully.

### Security Features
- File type validation
- File size limits
- Secure file naming
- Upload directory protection

## 🎯 Usage Examples

### Adding Image to Dell Laptop
1. Go to `simple-image-upload.php`
2. Find "Dell Inspiron 15 Laptop" product
3. Click "Choose Image File"
4. Select a laptop image
5. Click "Upload Image"
6. Image appears on main store immediately

### Managing Multiple Products
- Each product has its own upload form
- Upload images one at a time
- Images are automatically linked to correct products
- No need to manually edit JSON files

## 🔄 Workflow Integration

### Database Manager Role
1. **Access**: Login as Database Manager
2. **Navigate**: Go to Product Management section
3. **Upload**: Click "Add Product Images" button
4. **Manage**: Use the simple interface to add images

### Main Store Display
1. **Automatic**: Images appear when available
2. **Fallback**: Shows placeholder when no image
3. **Responsive**: Images scale properly on all devices

### Our Team Photos
1. Place headshots in `assets/images/team/` using these names:
   - `admin.jpg`, `php-dev.jpg`, `frontend-dev.jpg`, `db-manager.jpg`
2. Edit the names/titles in the Our Team section in `index.html` if needed
3. If a photo is missing, the emoji avatar fallback remains visible

## 🚨 Troubleshooting

### Common Issues

**"File too large" Error**
- Reduce image size to under 5MB
- Use image compression tools
- Convert to more efficient format

**"Invalid file type" Error**
- Use only JPG, PNG, GIF, or WebP
- Check file extension
- Ensure file is actually an image

**Image not appearing on store**
- Check if file was uploaded successfully
- Verify product ID matches
- Clear browser cache

**Team photo not showing**
- Confirm the file exists in `assets/images/team/`
- Verify the filename matches what `index.html` expects
- Ensure the image is reachable at the URL (open directly in browser)

### File Permissions
- Ensure `assets/images/products/` is writable
- Ensure `assets/images/team/` exists (read access is sufficient)
- Check web server permissions
- Verify directory exists

## 🔮 Future Enhancements

### Planned Features
- **Bulk Upload**: Upload multiple images at once
- **Image Editing**: Basic crop/resize tools
- **Thumbnail Generation**: Automatic thumbnail creation
- **Cloud Storage**: Integration with cloud services
- **Image Optimization**: Automatic compression

### Advanced Options
- **CDN Integration**: For faster image loading
- **Watermarking**: Add watermarks to images
- **Image Galleries**: Multiple images per product
- **Alt Text Management**: SEO-friendly image descriptions

## 📞 Support

### Getting Help
1. Check this README first
2. Review error messages carefully
3. Verify file permissions
4. Test with different image formats

### Best Practices
- Use high-quality images
- Optimize file sizes
- Use descriptive filenames
- Keep backups of important images

---

**Note**: This system is designed as a workaround for FileMaker's container field limitations. For production environments, consider implementing a more robust image management solution with cloud storage and advanced features.
