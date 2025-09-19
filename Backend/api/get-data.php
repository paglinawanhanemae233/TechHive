<?php
/**
 * TechHive Data API - New Version
 * Serves JSON data with proper CORS headers
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$type = $_GET['type'] ?? '';

$dataDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR;
error_log("NEW API - Data directory: " . $dataDir);

switch ($type) {
    case 'products':
        $file = $dataDir . 'products.json';
        break;
    case 'categories':
        $file = $dataDir . 'categories.json';
        break;
    case 'brands':
        $file = $dataDir . 'brands.json';
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid type parameter']);
        exit();
}

error_log("NEW API - File path: " . $file);
error_log("NEW API - File exists: " . (file_exists($file) ? 'YES' : 'NO'));

if (!file_exists($file)) {
    error_log("NEW API - File not found: " . $file);
    http_response_code(404);
    echo json_encode(['error' => 'Data file not found: ' . $file]);
    exit();
}

$data = file_get_contents($file);
if ($data === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to read data file']);
    exit();
}

// Debug: Show what file we're reading from
if ($type === 'products') {
    $debug = json_decode($data, true);
    if (isset($debug[0]['category_id'])) {
        error_log("NEW API - Returning category_id: " . $debug[0]['category_id'] . " from file: " . $file);
    }
}

echo $data;
?>
