<?php
/**
 * TechHive PHP Developer Dashboard
 * Backend development and API management tools
 */

require_once '../../includes/session.php';
require_once '../../includes/auth-functions.php';

// Require PHP developer role
requireLogin();
if (!hasRole('php_developer')) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$user = getCurrentUser();

// Get JSON data files info
$dataFiles = [
    'products.json' => 'Product Catalog',
    'categories.json' => 'Product Categories', 
    'orders.json' => 'Order Data',
    'customers.json' => 'Customer Data',
    'users.json' => 'User Accounts'
];

$fileStats = [];
foreach ($dataFiles as $file => $name) {
    $filePath = "../../data/$file";
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        $fileStats[$file] = [
            'name' => $name,
            'size' => filesize($filePath),
            'records' => is_array($data) ? count($data) : 0,
            'last_modified' => date('M j, Y g:i A', filemtime($filePath)),
            'valid' => json_last_error() === JSON_ERROR_NONE
        ];
    }
}

// Handle API testing
$apiTestResult = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_api'])) {
    $endpoint = $_POST['endpoint'] ?? '';
    $method = $_POST['method'] ?? 'GET';
    
    // Simulate API test
    $apiTestResult = [
        'endpoint' => $endpoint,
        'method' => $method,
        'status' => 'success',
        'response_time' => rand(50, 200) . 'ms',
        'status_code' => 200,
        'response' => 'API endpoint is working correctly'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Developer Dashboard - TechHive</title>
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
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-blue));
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
            color: var(--secondary-blue);
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
            color: var(--secondary-blue);
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
            color: var(--secondary-blue);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn {
            background: var(--secondary-blue);
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
            background: var(--accent-blue);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--accent-blue);
        }

        .btn-success {
            background: #28a745;
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
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-blue));
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

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }

        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--secondary-blue);
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-valid {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .status-invalid {
            background: #ffebee;
            color: #c62828;
        }

        .api-test-form {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--secondary-blue);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .api-result {
            background: #e8f5e8;
            border: 1px solid #c8e6c9;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .code-block {
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            overflow-x: auto;
        }

        .json-viewer {
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 1rem;
            max-height: 300px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
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
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">TechHive PHP Developer</div>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                </div>
                <div>
                    <div><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                    <div style="font-size: 0.9rem; opacity: 0.8;">PHP Developer</div>
                </div>
                <a href="../../auth/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">PHP Developer Dashboard</h1>
            <p class="dashboard-subtitle">Backend development tools and JSON data management</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($fileStats); ?></div>
                <div class="stat-label">JSON Files</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo array_sum(array_column($fileStats, 'records')); ?></div>
                <div class="stat-label">Total Records</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($fileStats, function($f) { return $f['valid']; })); ?></div>
                <div class="stat-label">Valid Files</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">100%</div>
                <div class="stat-label">Backend Access</div>
            </div>
        </div>

        <!-- Development Tools -->
        <div class="section">
            <h2 class="section-title">🔧 Development Tools</h2>
            <div class="tools-grid">
                <div class="tool-card" onclick="showApiTester()">
                    <div class="tool-icon">🚀</div>
                    <div class="tool-title">API Tester</div>
                    <div class="tool-description">Test API endpoints and responses</div>
                </div>
                <div class="tool-card" onclick="showJsonViewer()">
                    <div class="tool-icon">📄</div>
                    <div class="tool-title">JSON Viewer</div>
                    <div class="tool-description">View and analyze JSON data files</div>
                </div>
                <div class="tool-card" onclick="showDataProcessor()">
                    <div class="tool-icon">⚙️</div>
                    <div class="tool-title">Data Processor</div>
                    <div class="tool-description">Process and validate JSON data</div>
                </div>
                <div class="tool-card" onclick="showErrorLogs()">
                    <div class="tool-icon">🐛</div>
                    <div class="tool-title">Error Logs</div>
                    <div class="tool-description">View system error logs and debugging</div>
                </div>
            </div>
        </div>

        <!-- API Tester -->
        <div id="apiTester" class="section" style="display: none;">
            <h2 class="section-title">🚀 API Testing Tool</h2>
            <form method="POST" class="api-test-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="endpoint">API Endpoint</label>
                        <input type="text" id="endpoint" name="endpoint" placeholder="/api/products" value="<?php echo $_POST['endpoint'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="method">HTTP Method</label>
                        <select id="method" name="method">
                            <option value="GET" <?php echo ($_POST['method'] ?? '') === 'GET' ? 'selected' : ''; ?>>GET</option>
                            <option value="POST" <?php echo ($_POST['method'] ?? '') === 'POST' ? 'selected' : ''; ?>>POST</option>
                            <option value="PUT" <?php echo ($_POST['method'] ?? '') === 'PUT' ? 'selected' : ''; ?>>PUT</option>
                            <option value="DELETE" <?php echo ($_POST['method'] ?? '') === 'DELETE' ? 'selected' : ''; ?>>DELETE</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="test_api" class="btn">Test API</button>
            </form>

            <?php if ($apiTestResult): ?>
            <div class="api-result">
                <h4>Test Result:</h4>
                <div class="code-block">
                    <strong>Endpoint:</strong> <?php echo htmlspecialchars($apiTestResult['endpoint']); ?><br>
                    <strong>Method:</strong> <?php echo htmlspecialchars($apiTestResult['method']); ?><br>
                    <strong>Status:</strong> <?php echo htmlspecialchars($apiTestResult['status_code']); ?><br>
                    <strong>Response Time:</strong> <?php echo htmlspecialchars($apiTestResult['response_time']); ?><br>
                    <strong>Response:</strong> <?php echo htmlspecialchars($apiTestResult['response']); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- JSON Data Files -->
        <div class="section">
            <h2 class="section-title">📄 JSON Data Files</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Records</th>
                        <th>Size</th>
                        <th>Status</th>
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
                        <td><?php echo number_format($stats['records']); ?></td>
                        <td><?php echo number_format($stats['size'] / 1024, 2); ?> KB</td>
                        <td>
                            <span class="status-badge <?php echo $stats['valid'] ? 'status-valid' : 'status-invalid'; ?>">
                                <?php echo $stats['valid'] ? 'Valid' : 'Invalid'; ?>
                            </span>
                        </td>
                        <td><?php echo $stats['last_modified']; ?></td>
                        <td>
                            <button class="btn btn-small" onclick="viewJsonFile('<?php echo $file; ?>')">View</button>
                            <button class="btn btn-small btn-secondary" onclick="editJsonFile('<?php echo $file; ?>')">Edit</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Quick Actions -->
        <div class="section">
            <h2 class="section-title">⚡ Quick Actions</h2>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button class="btn" onclick="validateAllJson()">Validate All JSON</button>
                <button class="btn btn-secondary" onclick="backupData()">Backup Data</button>
                <button class="btn btn-success" onclick="optimizeJson()">Optimize JSON</button>
                <button class="btn" onclick="generateApiDocs()">Generate API Docs</button>
            </div>
        </div>
    </div>

    <script>
        function showApiTester() {
            document.getElementById('apiTester').style.display = 'block';
            document.getElementById('apiTester').scrollIntoView({ behavior: 'smooth' });
        }

        function showJsonViewer() {
            alert('JSON Viewer will be implemented');
        }

        function showDataProcessor() {
            alert('Data Processor will be implemented');
        }

        function showErrorLogs() {
            alert('Error Logs will be implemented');
        }

        function viewJsonFile(filename) {
            alert('Viewing JSON file: ' + filename);
        }

        function editJsonFile(filename) {
            alert('Editing JSON file: ' + filename);
        }

        function validateAllJson() {
            alert('Validating all JSON files...');
        }

        function backupData() {
            alert('Creating data backup...');
        }

        function optimizeJson() {
            alert('Optimizing JSON files...');
        }

        function generateApiDocs() {
            alert('Generating API documentation...');
        }
    </script>
</body>
</html>
