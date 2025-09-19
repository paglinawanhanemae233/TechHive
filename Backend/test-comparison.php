<?php
// Test product ID comparison
$testCartId = "LAP-001";
$testProductId = "LAP-001";

echo "Testing comparison:\n";
echo "Cart ID: '" . $testCartId . "'\n";
echo "Product ID: '" . $testProductId . "'\n";
echo "Equal (==): " . ($testCartId == $testProductId ? 'true' : 'false') . "\n";
echo "Identical (===): " . ($testCartId === $testProductId ? 'true' : 'false') . "\n";
echo "Length cart: " . strlen($testCartId) . "\n";
echo "Length product: " . strlen($testProductId) . "\n";

// Test with actual data
$productsFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'products.json';
if (file_exists($productsFile)) {
    $products = json_decode(file_get_contents($productsFile), true) ?: [];
    if (!empty($products)) {
        $actualProductId = $products[0]['id'];
        echo "\nActual product ID from file: '" . $actualProductId . "'\n";
        echo "Length: " . strlen($actualProductId) . "\n";
        echo "Equal with test: " . ($testCartId == $actualProductId ? 'true' : 'false') . "\n";
    }
}
?>
