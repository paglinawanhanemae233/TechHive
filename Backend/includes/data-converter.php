<?php
/**
 * TechHive Data Converter
 * Converts FileMaker XML data to clean JSON format
 */

class FileMakerDataConverter {
    
    /**
     * Convert FileMaker XML data to clean JSON
     */
    public static function convertFileMakerData($xmlData) {
        $converted = [];
        
        foreach ($xmlData as $tableName => $tableData) {
            if (isset($tableData['{http://www.filemaker.com/fmpxmlresult}RESULTSET'])) {
                $converted[$tableName] = self::convertTableData($tableData);
            }
        }
        
        return $converted;
    }
    
    /**
     * Convert individual table data
     */
    private static function convertTableData($tableData) {
        $metadata = $tableData['{http://www.filemaker.com/fmpxmlresult}METADATA']['{http://www.filemaker.com/fmpxmlresult}FIELD'];
        $fieldNames = array_column($metadata, 'NAME');
        
        $resultset = $tableData['{http://www.filemaker.com/fmpxmlresult}RESULTSET'];
        $rows = $resultset['{http://www.filemaker.com/fmpxmlresult}ROW'];
        
        // Handle single row vs multiple rows
        if (!isset($rows[0])) {
            $rows = [$rows];
        }
        
        $convertedRows = [];
        foreach ($rows as $row) {
            $rowData = [];
            $columns = $row['{http://www.filemaker.com/fmpxmlresult}COL'];
            
            // Handle single column vs multiple columns
            if (!isset($columns[0])) {
                $columns = [$columns];
            }
            
            foreach ($columns as $index => $column) {
                $fieldName = $fieldNames[$index] ?? "field_$index";
                $value = '';
                
                if (isset($column['{http://www.filemaker.com/fmpxmlresult}DATA']['text'])) {
                    $value = $column['{http://www.filemaker.com/fmpxmlresult}DATA']['text'];
                } elseif (isset($column['{http://www.filemaker.com/fmpxmlresult}DATA']) && 
                         is_array($column['{http://www.filemaker.com/fmpxmlresult}DATA']) && 
                         empty($column['{http://www.filemaker.com/fmpxmlresult}DATA'])) {
                    $value = null; // Empty data
                }
                
                $rowData[$fieldName] = $value;
            }
            
            // Add record metadata
            $rowData['_record_id'] = $row['RECORDID'] ?? null;
            $rowData['_mod_id'] = $row['MODID'] ?? null;
            
            $convertedRows[] = $rowData;
        }
        
        return $convertedRows;
    }
    
    /**
     * Clean and normalize data
     */
    public static function cleanData($data) {
        $cleaned = [];
        
        foreach ($data as $tableName => $rows) {
            $cleaned[$tableName] = [];
            
            foreach ($rows as $row) {
                $cleanedRow = [];
                foreach ($row as $key => $value) {
                    // Skip metadata fields
                    if (strpos($key, '_') === 0) {
                        continue;
                    }
                    
                    // Clean and convert values
                    if ($value === null || $value === '') {
                        $cleanedRow[$key] = null;
                    } else {
                        // Convert numeric strings to numbers
                        if (is_numeric($value)) {
                            $cleanedRow[$key] = (float) $value;
                        } else {
                            $cleanedRow[$key] = trim($value);
                        }
                    }
                }
                
                if (!empty($cleanedRow)) {
                    $cleaned[$tableName][] = $cleanedRow;
                }
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Convert to e-commerce format
     */
    public static function convertToEcommerceFormat($cleanedData) {
        $ecommerce = [];
        
        // Convert brands
        if (isset($cleanedData['BRANDS'])) {
            $ecommerce['brands'] = [];
            foreach ($cleanedData['BRANDS'] as $brand) {
                if (!empty($brand['BrandName'])) {
                    $ecommerce['brands'][] = [
                        'id' => (int) $brand['BrandID'],
                        'name' => $brand['BrandName'],
                        'description' => $brand['BrandDescription'] ?? '',
                        'website' => $brand['Website'] ?? '',
                        'is_active' => (bool) $brand['IsActive']
                    ];
                }
            }
        }
        
        // Convert categories
        if (isset($cleanedData['CATEGORIES'])) {
            $ecommerce['categories'] = [];
            foreach ($cleanedData['CATEGORIES'] as $category) {
                if (!empty($category['CategoryName'])) {
                    $ecommerce['categories'][] = [
                        'id' => (int) $category['CategoryID'],
                        'name' => $category['CategoryName'],
                        'description' => $category['CategoryDescription'] ?? '',
                        'parent_id' => (int) ($category['ParentCategoryID'] ?? 0),
                        'sort_order' => (int) ($category['SortOrder'] ?? 0),
                        'url_slug' => $category['URLSlug'] ?? strtolower(str_replace(' ', '-', $category['CategoryName'])),
                        'is_active' => (bool) $category['IsActive']
                    ];
                }
            }
        }
        
        // Convert products
        if (isset($cleanedData['PRODUCTS'])) {
            $ecommerce['products'] = [];
            foreach ($cleanedData['PRODUCTS'] as $product) {
                if (!empty($product['ProductName'])) {
                    $ecommerce['products'][] = [
                        'id' => $product['ProductID'],
                        'name' => $product['ProductName'],
                        'sku' => $product['ProductSKU'] ?? '',
                        'brand_id' => (int) ($product['BrandID'] ?? 0),
                        'category_id' => (int) ($product['CategoryID'] ?? 0),
                        'price' => (float) ($product['Price'] ?? 0),
                        'cost_price' => (float) ($product['CostPrice'] ?? 0),
                        'stock_quantity' => (int) ($product['StockQuantity'] ?? 0),
                        'minimum_stock' => (int) ($product['MinimumStock'] ?? 0),
                        'short_description' => $product['ShortDescription'] ?? '',
                        'long_description' => $product['LongDescription'] ?? '',
                        'meta_title' => $product['MetaTitle'] ?? '',
                        'meta_description' => $product['MetaDescription'] ?? '',
                        'dimensions' => $product['Dimensions'] ?? '',
                        'weight' => (float) ($product['Weight'] ?? 0),
                        'tags' => $product['Tags'] ?? '',
                        'is_active' => (bool) $product['IsActive'],
                        'is_featured' => (bool) $product['IsFeatured'],
                        'date_added' => $product['DateAdded'] ?? date('Y-m-d'),
                        'date_modified' => $product['DateModified'] ?? null
                    ];
                }
            }
        }
        
        // Convert customers
        if (isset($cleanedData['CUSTOMERS'])) {
            $ecommerce['customers'] = [];
            foreach ($cleanedData['CUSTOMERS'] as $customer) {
                if (!empty($customer['FirstName'])) {
                    $ecommerce['customers'][] = [
                        'id' => $customer['CostumerID'],
                        'first_name' => $customer['FirstName'],
                        'last_name' => $customer['LastName'],
                        'email' => $customer['Email'],
                        'phone' => $customer['PhoneNumber'] ?? '',
                        'password' => $customer['Password'] ?? '',
                        'is_active' => (bool) $customer['IsActive'],
                        'date_registered' => $customer['DateRegistered'] ?? date('Y-m-d'),
                        'last_login' => $customer['LastLogin'] ?? null
                    ];
                }
            }
        }
        
        // Convert orders
        if (isset($cleanedData['ORDERS'])) {
            $ecommerce['orders'] = [];
            foreach ($cleanedData['ORDERS'] as $order) {
                if (!empty($order['OrderID'])) {
                    $ecommerce['orders'][] = [
                        'id' => $order['OrderID'],
                        'order_date' => $order['OrderDate'] ?? date('Y-m-d'),
                        'order_status' => $order['OrderStatus'] ?? 'pending',
                        'payment_method' => $order['PaymentMethod'] ?? '',
                        'payment_status' => $order['PaymentStatus'] ?? 'unpaid',
                        'subtotal' => (float) ($order['SubTotal'] ?? 0),
                        'tax_amount' => (float) ($order['TaxAmount'] ?? 0),
                        'shipping_amount' => (float) ($order['ShippingAmount'] ?? 0),
                        'total_amount' => (float) ($order['TotalAmount'] ?? 0),
                        'billing_address' => $order['BillingAddress'] ?? '',
                        'shipping_address' => $order['ShippingAddress'] ?? '',
                        'tracking_number' => $order['TrackingNumber'] ?? '',
                        'notes' => $order['Notes'] ?? ''
                    ];
                }
            }
        }
        
        // Convert order items
        if (isset($cleanedData['ORDER_ITEMS'])) {
            $ecommerce['order_items'] = [];
            foreach ($cleanedData['ORDER_ITEMS'] as $item) {
                if (!empty($item['OrderItemID'])) {
                    $ecommerce['order_items'][] = [
                        'id' => $item['OrderItemID'],
                        'order_id' => $item['OrderID'],
                        'product_id' => $item['ProductID'],
                        'product_name' => $item['ProductName'],
                        'product_sku' => $item['ProductSKU'],
                        'quantity' => (int) $item['Quantity'],
                        'unit_price' => (float) $item['UnitPrice'],
                        'total_price' => (float) $item['TotalPrice']
                    ];
                }
            }
        }
        
        // Convert shopping cart
        if (isset($cleanedData['SHOPPING_CART'])) {
            $ecommerce['shopping_cart'] = [];
            foreach ($cleanedData['SHOPPING_CART'] as $cartItem) {
                if (!empty($cartItem['CartID'])) {
                    $ecommerce['shopping_cart'][] = [
                        'id' => $cartItem['CartID'],
                        'customer_id' => $cartItem['CustomerID'],
                        'product_id' => $cartItem['ProductID'],
                        'quantity' => (int) $cartItem['Quantity'],
                        'session_id' => $cartItem['SessionID'],
                        'date_added' => $cartItem['DateAdded']
                    ];
                }
            }
        }
        
        // Convert inventory log
        if (isset($cleanedData['INVENTORY_LOG'])) {
            $ecommerce['inventory_log'] = [];
            foreach ($cleanedData['INVENTORY_LOG'] as $log) {
                if (!empty($log['LogID'])) {
                    $ecommerce['inventory_log'][] = [
                        'id' => $log['LogID'],
                        'product_id' => $log['ProductID'],
                        'change_type' => $log['ChangeType'],
                        'previous_quantity' => (int) $log['PreviousQuantity'],
                        'new_quantity' => (int) $log['NewQuantity'],
                        'quantity_change' => (int) $log['QuantityChange'],
                        'change_date' => $log['ChangeDate'],
                        'notes' => $log['Notes'] ?? ''
                    ];
                }
            }
        }
        
        // Convert product specifications
        if (isset($cleanedData['PRODUCT_SPECIFICATIONS'])) {
            $ecommerce['product_specifications'] = [];
            foreach ($cleanedData['PRODUCT_SPECIFICATIONS'] as $spec) {
                if (!empty($spec['SpecID'])) {
                    $ecommerce['product_specifications'][] = [
                        'id' => $spec['SpecID'],
                        'product_id' => $spec['ProductID'],
                        'attribute_name' => $spec['AttributeName'],
                        'attribute_value' => $spec['AttributeValue'],
                        'sort_order' => (int) $spec['SortOrder']
                    ];
                }
            }
        }
        
        // Convert customer addresses
        if (isset($cleanedData['CUSTOMER_ADDRESSES'])) {
            $ecommerce['customer_addresses'] = [];
            foreach ($cleanedData['CUSTOMER_ADDRESSES'] as $address) {
                if (!empty($address['AddressID'])) {
                    $ecommerce['customer_addresses'][] = [
                        'id' => $address['AddressID'],
                        'customer_id' => $address['CustomerID'],
                        'address_type' => $address['AddressType'],
                        'address_line1' => $address['AddressLine1'],
                        'address_line2' => $address['AddressLine2'] ?? '',
                        'city' => $address['City'],
                        'state' => $address['State'],
                        'zip_code' => $address['ZipCode'],
                        'country' => $address['Country'],
                        'is_default' => (bool) $address['IsDefault']
                    ];
                }
            }
        }
        
        return $ecommerce;
    }
}
?>
