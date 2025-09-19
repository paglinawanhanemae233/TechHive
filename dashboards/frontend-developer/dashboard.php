<?php
/**
 * TechHive Frontend Developer Dashboard
 * UI/UX development and design tools
 */

require_once '../../includes/session.php';
require_once '../../includes/auth-functions.php';

// Require frontend developer role
requireLogin();
if (!hasRole('frontend_developer')) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$user = getCurrentUser();

// Get frontend files info
$frontendFiles = [
    'index.html' => 'Main Homepage',
    'assets/css/main.css' => 'Main Stylesheet',
    'assets/css/admin.css' => 'Admin Styles',
    'assets/css/developer.css' => 'Developer Styles',
    'assets/js/main.js' => 'Main JavaScript',
    'assets/js/auth.js' => 'Authentication Scripts'
];

$fileStats = [];
foreach ($frontendFiles as $file => $name) {
    $filePath = "../../$file";
    if (file_exists($filePath)) {
        $fileStats[$file] = [
            'name' => $name,
            'size' => filesize($filePath),
            'last_modified' => date('M j, Y g:i A', filemtime($filePath)),
            'type' => pathinfo($file, PATHINFO_EXTENSION)
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frontend Developer Dashboard - TechHive</title>
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
            background: linear-gradient(135deg, var(--light-purple), var(--accent-blue));
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
            background: var(--primary-indigo);
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
            color: var(--light-purple);
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
            color: var(--light-purple);
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
            color: var(--light-purple);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn {
            background: var(--light-purple);
            color: var(--secondary-blue);
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
            background: var(--accent-blue);
            color: var(--white);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--accent-blue);
            color: var(--white);
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .tool-card {
            background: linear-gradient(135deg, var(--light-purple), var(--accent-blue));
            color: var(--white);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .tool-card:hover {
            transform: translateY(-5px);
        }

        .tool-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .tool-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .tool-description {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .file-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .file-table th,
        .file-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }

        .file-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--secondary-blue);
        }

        .file-table tr:hover {
            background: #f8f9fa;
        }

        .file-type {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .type-html {
            background: #ff6b6b;
            color: white;
        }

        .type-css {
            background: #4ecdc4;
            color: white;
        }

        .type-js {
            background: #ffe66d;
            color: #333;
        }

        .preview-container {
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1rem 0;
        }

        .preview-frame {
            width: 100%;
            height: 400px;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            background: white;
        }

        .color-palette {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }

        .color-card {
            text-align: center;
            padding: 1rem;
            border-radius: 10px;
            color: white;
            font-weight: 600;
        }

        .color-primary {
            background: var(--primary-indigo);
        }

        .color-secondary {
            background: var(--secondary-blue);
        }

        .color-accent {
            background: var(--accent-blue);
        }

        .color-light {
            background: var(--light-purple);
            color: var(--secondary-blue);
        }

        .component-showcase {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }

        .component-card {
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
        }

        .component-preview {
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
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
            
            .tools-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">TechHive Frontend</div>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                </div>
                <div>
                    <div><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                    <div style="font-size: 0.9rem; opacity: 0.8;">Frontend Developer</div>
                </div>
                <a href="../../auth/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Frontend Developer Dashboard</h1>
            <p class="dashboard-subtitle">UI/UX development tools and design system</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($fileStats); ?></div>
                <div class="stat-label">Frontend Files</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format(array_sum(array_column($fileStats, 'size')) / 1024, 1); ?> KB</div>
                <div class="stat-label">Total Size</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">100%</div>
                <div class="stat-label">Responsive</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">A+</div>
                <div class="stat-label">Performance</div>
            </div>
        </div>

        <!-- Design Tools -->
        <div class="section">
            <h2 class="section-title">🎨 Design Tools</h2>
            <div class="tools-grid">
                <div class="tool-card" onclick="showColorPalette()">
                    <div class="tool-icon">🎨</div>
                    <div class="tool-title">Color Palette</div>
                    <div class="tool-description">TechHive brand colors and themes</div>
                </div>
                <div class="tool-card" onclick="showComponentLibrary()">
                    <div class="tool-icon">🧩</div>
                    <div class="tool-title">Component Library</div>
                    <div class="tool-description">Reusable UI components</div>
                </div>
                <div class="tool-card" onclick="showResponsivePreview()">
                    <div class="tool-icon">📱</div>
                    <div class="tool-title">Responsive Preview</div>
                    <div class="tool-description">Test across different devices</div>
                </div>
                <div class="tool-card" onclick="showCodeEditor()">
                    <div class="tool-icon">💻</div>
                    <div class="tool-title">Code Editor</div>
                    <div class="tool-description">Edit HTML, CSS, and JavaScript</div>
                </div>
            </div>
        </div>

        <!-- Color Palette -->
        <div id="colorPalette" class="section" style="display: none;">
            <h2 class="section-title">🎨 TechHive Color Palette</h2>
            <div class="color-palette">
                <div class="color-card color-primary">
                    <div style="font-size: 1.2rem; margin-bottom: 0.5rem;">Primary Indigo</div>
                    <div>#4A088C</div>
                </div>
                <div class="color-card color-secondary">
                    <div style="font-size: 1.2rem; margin-bottom: 0.5rem;">Secondary Blue</div>
                    <div>#120540</div>
                </div>
                <div class="color-card color-accent">
                    <div style="font-size: 1.2rem; margin-bottom: 0.5rem;">Accent Blue</div>
                    <div>#433C73</div>
                </div>
                <div class="color-card color-light">
                    <div style="font-size: 1.2rem; margin-bottom: 0.5rem;">Light Purple</div>
                    <div>#AEA7D9</div>
                </div>
            </div>
        </div>

        <!-- Component Library -->
        <div id="componentLibrary" class="section" style="display: none;">
            <h2 class="section-title">🧩 Component Library</h2>
            <div class="component-showcase">
                <div class="component-card">
                    <div class="component-preview">
                        <button class="btn">Primary Button</button>
                    </div>
                    <h4>Buttons</h4>
                    <p>Primary, secondary, and action buttons</p>
                </div>
                <div class="component-card">
                    <div class="component-preview">
                        <div style="background: var(--light-purple); padding: 1rem; border-radius: 8px; color: var(--secondary-blue);">
                            <strong>Card Component</strong><br>
                            Content goes here
                        </div>
                    </div>
                    <h4>Cards</h4>
                    <p>Product cards and content containers</p>
                </div>
                <div class="component-card">
                    <div class="component-preview">
                        <input type="text" placeholder="Input field" style="width: 100%; padding: 0.5rem; border: 1px solid #e1e5e9; border-radius: 4px;">
                    </div>
                    <h4>Form Elements</h4>
                    <p>Inputs, selects, and form controls</p>
                </div>
                <div class="component-card">
                    <div class="component-preview">
                        <div style="background: #e8f5e8; color: #2e7d32; padding: 0.5rem; border-radius: 4px; font-size: 0.9rem;">
                            Success Message
                        </div>
                    </div>
                    <h4>Alerts</h4>
                    <p>Success, error, and info messages</p>
                </div>
            </div>
        </div>

        <!-- Frontend Files -->
        <div class="section">
            <h2 class="section-title">📄 Frontend Files</h2>
            <table class="file-table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Last Modified</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fileStats as $file => $stats): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($stats['name']); ?></div>
                            <div style="font-size: 0.9rem; color: var(--neutral-blue);"><?php echo $file; ?></div>
                        </td>
                        <td>
                            <span class="file-type type-<?php echo $stats['type']; ?>">
                                <?php echo strtoupper($stats['type']); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($stats['size'] / 1024, 2); ?> KB</td>
                        <td><?php echo $stats['last_modified']; ?></td>
                        <td>
                            <button class="btn btn-small" onclick="editFile('<?php echo $file; ?>')">Edit</button>
                            <button class="btn btn-small btn-secondary" onclick="previewFile('<?php echo $file; ?>')">Preview</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Responsive Preview -->
        <div id="responsivePreview" class="section" style="display: none;">
            <h2 class="section-title">📱 Responsive Preview</h2>
            <div class="preview-container">
                <div style="margin-bottom: 1rem;">
                    <button class="btn btn-small" onclick="setPreviewSize('mobile')">Mobile (375px)</button>
                    <button class="btn btn-small" onclick="setPreviewSize('tablet')">Tablet (768px)</button>
                    <button class="btn btn-small" onclick="setPreviewSize('desktop')">Desktop (1200px)</button>
                </div>
                <iframe id="previewFrame" class="preview-frame" src="../index.html"></iframe>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="section">
            <h2 class="section-title">⚡ Quick Actions</h2>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button class="btn" onclick="optimizeCSS()">Optimize CSS</button>
                <button class="btn btn-secondary" onclick="minifyJS()">Minify JavaScript</button>
                <button class="btn" onclick="validateHTML()">Validate HTML</button>
                <button class="btn" onclick="generateSitemap()">Generate Sitemap</button>
            </div>
        </div>
    </div>

    <script>
        function showColorPalette() {
            document.getElementById('colorPalette').style.display = 'block';
            document.getElementById('colorPalette').scrollIntoView({ behavior: 'smooth' });
        }

        function showComponentLibrary() {
            document.getElementById('componentLibrary').style.display = 'block';
            document.getElementById('componentLibrary').scrollIntoView({ behavior: 'smooth' });
        }

        function showResponsivePreview() {
            document.getElementById('responsivePreview').style.display = 'block';
            document.getElementById('responsivePreview').scrollIntoView({ behavior: 'smooth' });
        }

        function showCodeEditor() {
            alert('Code Editor will be implemented');
        }

        function editFile(filename) {
            alert('Editing file: ' + filename);
        }

        function previewFile(filename) {
            alert('Previewing file: ' + filename);
        }

        function setPreviewSize(size) {
            const frame = document.getElementById('previewFrame');
            switch(size) {
                case 'mobile':
                    frame.style.width = '375px';
                    frame.style.height = '667px';
                    break;
                case 'tablet':
                    frame.style.width = '768px';
                    frame.style.height = '1024px';
                    break;
                case 'desktop':
                    frame.style.width = '100%';
                    frame.style.height = '600px';
                    break;
            }
        }

        function optimizeCSS() {
            alert('Optimizing CSS files...');
        }

        function minifyJS() {
            alert('Minifying JavaScript files...');
        }

        function validateHTML() {
            alert('Validating HTML files...');
        }

        function generateSitemap() {
            alert('Generating sitemap...');
        }
    </script>
</body>
</html>
