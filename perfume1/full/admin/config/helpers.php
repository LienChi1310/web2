<?php

/**
 * =====================================================
 * HELPERS - SHARED FUNCTIONS FOR INVENTORY & PRODUCT
 * =====================================================
 */

/* =================================================
 * CURRENCY & PRICE FORMATTING
 * ================================================= */

/**
 * Calculate sell price based on import price and profit percent
 * Formula: sell_price = import_price × (100 + profit_percent) / 100
 */
function calculate_sell_price($import_price, $profit_percent)
{
    $import_price = (float)$import_price;
    $profit_percent = (float)$profit_percent;

    if ($import_price < 0) $import_price = 0;
    if ($profit_percent < 0) $profit_percent = 0;

    $result = $import_price * (100 + $profit_percent) / 100;
    return (int)round($result);
}

/**
 * Format currency for display
 */
function format_currency($value)
{
    return number_format((int)$value) . ' ₫';
}

/**
 * Safe string escape for database
 */
function db_escape($mysqli, $value)
{
    return mysqli_real_escape_string($mysqli, trim((string)$value));
}

/* =================================================
 * INVENTORY STATUS HELPERS
 * ================================================= */

/**
 * Get inventory status text
 * 0 = Chưa hoàn thành (Draft)
 * 1 = Đã hoàn thành (Completed)
 * -1 = Đã hủy (Cancelled)
 */
function inventory_status_text($value)
{
    $value = (int)$value;
    switch ($value) {
        case 1:
            return 'Đã hoàn thành';
        case -1:
            return 'Đã hủy';
        case 0:
        default:
            return 'Chưa hoàn thành';
    }
}

/**
 * Get inventory status CSS class
 */
function inventory_status_class($value)
{
    $value = (int)$value;
    switch ($value) {
        case 1:
            return 'bg-success text-white';
        case -1:
            return 'bg-danger text-white';
        case 0:
        default:
            return 'bg-warning text-dark';
    }
}

/* =================================================
 * ORDER STATUS HELPERS
 * ================================================= */

/**
 * Get order status text
 * 0 = Đang xử lí (Processing)
 * 1 = Đang chuẩn bị (Preparing)
 * 2 = Đang giao hàng (Shipping)
 * 3 = Đã giao hàng (Delivered)
 * -1 = Đơn hàng đã hủy (Cancelled)
 */
function order_status_text($value)
{
    $value = (int)$value;
    switch ($value) {
        case 1:
            return 'Đang chuẩn bị';
        case 2:
            return 'Đang giao hàng';
        case 3:
            return 'Đã giao hàng';
        case -1:
            return 'Đã hủy';
        case 0:
        default:
            return 'Đang xử lí';
    }
}

/**
 * Get order status CSS class
 */
function order_status_class($value)
{
    $value = (int)$value;
    switch ($value) {
        case 3:
            return 'bg-success text-white';
        case 2:
            return 'bg-info text-white';
        case 1:
            return 'bg-primary text-white';
        case 0:
            return 'bg-warning text-dark';
        case -1:
            return 'bg-danger text-white';
        default:
            return 'bg-secondary text-white';
    }
}

/**
 * Get order type text (payment method)
 * 1 = COD (Thanh toán khi nhận hàng)
 * 2 = MoMo QR Code
 * 5 = Bank Transfer (Chuyển khoản ngân hàng)
 */
function order_type_text($value)
{
    $value = (int)$value;
    switch ($value) {
        case 1:
            return 'COD';
        case 2:
            return 'MoMo QR';
        case 5:
            return 'Chuyển khoản';
        default:
            return 'Không xác định';
    }
}

/**
 * Get order type badge color
 */
function order_type_class($value)
{
    $value = (int)$value;
    switch ($value) {
        case 1:
            return 'bg-secondary';
        case 2:
            return 'bg-info';
        case 5:
            return 'bg-primary';
        default:
            return 'bg-light';
    }
}

/* =================================================
 * PRODUCT STATUS HELPERS
 * ================================================= */

function product_status_text($value)
{
    $value = (int)$value;
    if ($value == 1) {
        return 'Đang bán';
    }
    return 'Tạm dừng';
}

function product_status_class($value)
{
    $value = (int)$value;
    if ($value == 1) {
        return 'bg-success text-white';
    }
    return 'bg-warning text-dark';
}

/* =================================================
 * DATABASE TABLE & COLUMN CHECKS
 * ================================================= */

/**
 * Check if table exists
 */
function table_exists($mysqli, $table_name)
{
    $table_name = mysqli_real_escape_string($mysqli, $table_name);
    $sql = "SHOW TABLES LIKE '{$table_name}'";
    $query = mysqli_query($mysqli, $sql);
    return ($query && mysqli_num_rows($query) > 0);
}

/**
 * Check if column exists in table
 */
function column_exists($mysqli, $table_name, $column_name)
{
    $table_name = mysqli_real_escape_string($mysqli, $table_name);
    $column_name = mysqli_real_escape_string($mysqli, $column_name);

    $sql = "SHOW COLUMNS FROM `{$table_name}` LIKE '{$column_name}'";
    $query = mysqli_query($mysqli, $sql);

    return ($query && mysqli_num_rows($query) > 0);
}

/* =================================================
 * FILE & IMAGE HELPERS
 * ================================================= */

/**
 * Get product image
 */
function get_product_image($mysql, $product_id, $default = 'default.jpg')
{
    $product_id = (int)$product_id;
    $sql = "SELECT product_image FROM product WHERE product_id = '{$product_id}' LIMIT 1";
    $query = mysqli_query($mysql, $sql);

    if ($query && mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        return !empty($row['product_image']) ? $row['product_image'] : $default;
    }

    return $default;
}

/**
 * Delete product image file
 */
function delete_product_image($mysqli, $product_id)
{
    $product_id = (int)$product_id;
    if ($product_id <= 0) {
        return;
    }

    $sql = "SELECT product_image FROM product WHERE product_id = '{$product_id}' LIMIT 1";
    $query = mysqli_query($mysqli, $sql);

    if ($query && mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_array($query);
        if (!empty($row['product_image']) && file_exists('modules/product/uploads/' . $row['product_image'])) {
            @unlink('modules/product/uploads/' . $row['product_image']);
        }
    }
}

/* =================================================
 * DATA VALIDATION
 * ================================================= */

/**
 * Check if product has related data (orders, inventory)
 */
function product_has_related_data($mysqli, $product_id)
{
    $product_id = (int)$product_id;
    if ($product_id <= 0) {
        return false;
    }

    $sql_order = "SELECT order_detail_id FROM order_detail WHERE product_id = '{$product_id}' LIMIT 1";
    $query_order = mysqli_query($mysqli, $sql_order);
    if ($query_order && mysqli_num_rows($query_order) > 0) {
        return true;
    }

    if (table_exists($mysqli, 'inventory_detail')) {
        $sql_inventory = "SELECT id FROM inventory_detail WHERE product_id = '{$product_id}' LIMIT 1";
        $query_inventory = mysqli_query($mysqli, $sql_inventory);
        if ($query_inventory && mysqli_num_rows($query_inventory) > 0) {
            return true;
        }
    }

    return false;
}

/**
 * Validate email
 */
function is_valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (basic)
 */
function is_valid_phone($phone)
{
    return preg_match('/^\d{10,11}$/', preg_replace('/[^0-9]/', '', $phone));
}

/* =================================================
 * ARRAY & STRING HELPERS
 * ================================================= */

/**
 * Get IDs from JSON data parameter
 */
function get_ids_from_data()
{
    if (!isset($_GET['data']) || $_GET['data'] === '') {
        return [];
    }

    $data = $_GET['data'];
    $decoded = json_decode($data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return [];
    }

    if (is_array($decoded)) {
        return $decoded;
    }

    return [$decoded];
}

/**
 * Safely convert to integer
 */
function safe_int($value, $default = 0)
{
    return is_numeric($value) ? (int)$value : $default;
}

/**
 * Safely convert to float
 */
function safe_float($value, $default = 0.0)
{
    return is_numeric($value) ? (float)$value : $default;
}

/* =================================================
 * CATEGORY & BRAND FILTERING (For Inventory Forms)
 * ================================================= */

/**
 * Get all categories
 */
function get_all_categories($mysqli)
{
    $sql = "SELECT category_id, category_name FROM category WHERE 1=1 ORDER BY category_name ASC";
    $query = mysqli_query($mysqli, $sql);
    $categories = [];
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $categories[] = $row;
        }
    }
    return $categories;
}

/**
 * Get all brands
 */
function get_all_brands($mysqli)
{
    $sql = "SELECT brand_id, brand_name FROM brand WHERE 1=1 ORDER BY brand_name ASC";
    $query = mysqli_query($mysqli, $sql);
    $brands = [];
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $brands[] = $row;
        }
    }
    return $brands;
}

/**
 * Get brands by category
 */
function get_brands_by_category($mysqli, $category_id)
{
    $category_id = (int)$category_id;
    if ($category_id <= 0) {
        return get_all_brands($mysqli);
    }

    $sql = "
        SELECT DISTINCT b.brand_id, b.brand_name
        FROM brand b
        INNER JOIN product p ON p.product_brand = b.brand_id
        WHERE p.product_category = '{$category_id}'
        ORDER BY b.brand_name ASC
    ";
    $query = mysqli_query($mysqli, $sql);
    $brands = [];
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $brands[] = $row;
        }
    }
    return $brands;
}

/**
 * Get products by category and/or brand
 */
function get_products_by_filters($mysqli, $category_id = 0, $brand_id = 0)
{
    $category_id = (int)$category_id;
    $brand_id = (int)$brand_id;

    $sql = "
        SELECT product_id, product_name, product_quantity
        FROM product
        WHERE product_status IN (0, 1)
    ";

    if ($category_id > 0) {
        $sql .= " AND product_category = '{$category_id}'";
    }

    if ($brand_id > 0) {
        $sql .= " AND product_brand = '{$brand_id}'";
    }

    $sql .= " ORDER BY product_name ASC";

    $query = mysqli_query($mysqli, $sql);
    $products = [];
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $products[] = $row;
        }
    }
    return $products;
}
