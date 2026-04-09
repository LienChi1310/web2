<?php

/**
 * API for filtering brands and products in inventory forms
 * Used by them.php (add) and sua.php (edit) forms
 */

require_once '../../config/config.php';
require_once '../../config/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Get brands by category
if ($action === 'get_brands_by_category') {
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

    $brands = get_brands_by_category($mysqli, $category_id);

    echo json_encode([
        'success' => true,
        'data' => $brands
    ]);
    exit;
}

// Get products by category and/or brand
if ($action === 'get_products') {
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
    $brand_id = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0;

    $products = get_products_by_filters($mysqli, $category_id, $brand_id);

    echo json_encode([
        'success' => true,
        'data' => $products
    ]);
    exit;
}

// Get all categories
if ($action === 'get_categories') {
    $categories = get_all_categories($mysqli);

    echo json_encode([
        'success' => true,
        'data' => $categories
    ]);
    exit;
}

// Get all brands
if ($action === 'get_brands') {
    $brands = get_all_brands($mysqli);

    echo json_encode([
        'success' => true,
        'data' => $brands
    ]);
    exit;
}

// Default error response
echo json_encode([
    'success' => false,
    'message' => 'Invalid action'
]);
