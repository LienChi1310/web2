<?php
include('../../config/config.php');

/**
 * Helper: escape string an toàn hơn
 */
function db_escape($mysqli, $value)
{
    return mysqli_real_escape_string($mysqli, trim((string)$value));
}

/**
 * Helper: lấy danh sách id từ tham số ?data=
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
 * Kiểm tra bảng có tồn tại hay không
 */
function table_exists($mysqli, $table_name)
{
    $table_name = mysqli_real_escape_string($mysqli, $table_name);
    $sql = "SHOW TABLES LIKE '{$table_name}'";
    $query = mysqli_query($mysqli, $sql);
    return ($query && mysqli_num_rows($query) > 0);
}

/**
 * Kiểm tra cột có tồn tại hay không
 */
function column_exists($mysqli, $table_name, $column_name)
{
    $table_name = mysqli_real_escape_string($mysqli, $table_name);
    $column_name = mysqli_real_escape_string($mysqli, $column_name);

    $sql = "SHOW COLUMNS FROM `{$table_name}` LIKE '{$column_name}'";
    $query = mysqli_query($mysqli, $sql);

    return ($query && mysqli_num_rows($query) > 0);
}

/**
 * Tính giá bán theo % lợi nhuận
 */
function calculate_sell_price($import_price, $profit_percent)
{
    $import_price = (int)$import_price;
    $profit_percent = (int)$profit_percent;

    if ($import_price < 0) $import_price = 0;
    if ($profit_percent < 0) $profit_percent = 0;

    return (int) round($import_price * (100 + $profit_percent) / 100);
}

/**
 * Kiểm tra sản phẩm đã phát sinh dữ liệu chưa
 * - Nếu đã có trong order_detail hoặc inventory_detail => không xóa cứng
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
 * Xóa ảnh sản phẩm
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
        if (!empty($row['product_image']) && file_exists('uploads/' . $row['product_image'])) {
            @unlink('uploads/' . $row['product_image']);
        }
    }
}

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$has_profit_column = column_exists($mysqli, 'product', 'product_profit_percent');

/* =====================================================
 * 1) THÊM SẢN PHẨM
 * ===================================================== */
if (isset($_POST['product_add'])) {

    $product_name     = db_escape($mysqli, $_POST['product_name'] ?? '');
    $product_brand    = (int)($_POST['product_brand'] ?? 0);
    $product_capacity = (int)($_POST['product_capacity'] ?? 0);
    $product_category = (int)($_POST['product_category'] ?? 0);

    $product_price_import = isset($_POST['product_price_import']) && $_POST['product_price_import'] !== ''
        ? (int)$_POST['product_price_import'] : 0;

    $product_profit_percent = isset($_POST['product_profit_percent']) && $_POST['product_profit_percent'] !== ''
        ? (int)$_POST['product_profit_percent'] : 20;

    $product_price = calculate_sell_price($product_price_import, $product_profit_percent);

    $product_sale = isset($_POST['product_sale']) && $_POST['product_sale'] !== ''
        ? (int)$_POST['product_sale'] : 0;

    $product_description = db_escape($mysqli, $_POST['product_description'] ?? '');
    $product_status      = isset($_POST['product_status']) && $_POST['product_status'] !== ''
        ? (int)$_POST['product_status'] : 0;

    $product_image_name = $_FILES['product_image']['name'] ?? '';
    $product_image_tmp  = $_FILES['product_image']['tmp_name'] ?? '';
    $product_image      = '';

    if ($product_image_name !== '') {
        $product_image = time() . '_' . basename($product_image_name);
        move_uploaded_file($product_image_tmp, 'uploads/' . $product_image);
    }

    if ($has_profit_column) {
        $sql_add = "
            INSERT INTO product(
                product_name,
                product_category,
                product_brand,
                capacity_id,
                product_price_import,
                product_profit_percent,
                product_price,
                product_sale,
                product_description,
                product_image,
                product_status
            )
            VALUES(
                '{$product_name}',
                '{$product_category}',
                '{$product_brand}',
                '{$product_capacity}',
                '{$product_price_import}',
                '{$product_profit_percent}',
                '{$product_price}',
                '{$product_sale}',
                '{$product_description}',
                '{$product_image}',
                '{$product_status}'
            )
        ";
    } else {
        $sql_add = "
            INSERT INTO product(
                product_name,
                product_category,
                product_brand,
                capacity_id,
                product_price_import,
                product_price,
                product_sale,
                product_description,
                product_image,
                product_status
            )
            VALUES(
                '{$product_name}',
                '{$product_category}',
                '{$product_brand}',
                '{$product_capacity}',
                '{$product_price_import}',
                '{$product_price}',
                '{$product_sale}',
                '{$product_description}',
                '{$product_image}',
                '{$product_status}'
            )
        ";
    }

    mysqli_query($mysqli, $sql_add);

    header('Location: ../../index.php?action=product&query=product_list&message=success');
    exit;
}

/* =====================================================
 * 2) SỬA SẢN PHẨM
 * ===================================================== */
elseif (isset($_POST['product_edit'])) {

    $product_name     = db_escape($mysqli, $_POST['product_name'] ?? '');
    $product_brand    = (int)($_POST['product_brand'] ?? 0);
    $product_capacity = (int)($_POST['product_capacity'] ?? 0);
    $product_category = (int)($_POST['product_category'] ?? 0);

    $product_price_import = isset($_POST['product_price_import']) && $_POST['product_price_import'] !== ''
        ? (int)$_POST['product_price_import'] : 0;

    $product_profit_percent = isset($_POST['product_profit_percent']) && $_POST['product_profit_percent'] !== ''
        ? (int)$_POST['product_profit_percent'] : 20;

    $product_price = calculate_sell_price($product_price_import, $product_profit_percent);

    $product_sale = isset($_POST['product_sale']) && $_POST['product_sale'] !== ''
        ? (int)$_POST['product_sale'] : 0;

    $product_description = db_escape($mysqli, $_POST['product_description'] ?? '');
    $product_status      = isset($_POST['product_status']) && $_POST['product_status'] !== ''
        ? (int)$_POST['product_status'] : 0;

    $product_image_name = $_FILES['product_image']['name'] ?? '';
    $product_image_tmp  = $_FILES['product_image']['tmp_name'] ?? '';

    $profit_sql_part = $has_profit_column ? "product_profit_percent = '{$product_profit_percent}'," : "";

    if ($product_image_name !== '') {
        $product_image = time() . '_' . basename($product_image_name);
        move_uploaded_file($product_image_tmp, 'uploads/' . $product_image);

        delete_product_image($mysqli, $product_id);

        $sql_update = "
            UPDATE product SET
                product_name         = '{$product_name}',
                product_brand        = '{$product_brand}',
                capacity_id          = '{$product_capacity}',
                product_category     = '{$product_category}',
                product_price_import = '{$product_price_import}',
                {$profit_sql_part}
                product_price        = '{$product_price}',
                product_sale         = '{$product_sale}',
                product_description  = '{$product_description}',
                product_image        = '{$product_image}',
                product_status       = '{$product_status}'
            WHERE product_id        = '{$product_id}'
        ";
    } else {
        $sql_update = "
            UPDATE product SET
                product_name         = '{$product_name}',
                product_brand        = '{$product_brand}',
                capacity_id          = '{$product_capacity}',
                product_category     = '{$product_category}',
                product_price_import = '{$product_price_import}',
                {$profit_sql_part}
                product_price        = '{$product_price}',
                product_sale         = '{$product_sale}',
                product_description  = '{$product_description}',
                product_status       = '{$product_status}'
            WHERE product_id        = '{$product_id}'
        ";
    }

    mysqli_query($mysqli, $sql_update);
    header('Location: ../../index.php?action=product&query=product_list&message=success');
    exit;
}

/* =====================================================
 * 3) SET GIẢM GIÁ HÀNG LOẠT
 * ===================================================== */
elseif (isset($_GET['product_sale'])) {

    $sale        = (int)$_GET['product_sale'];
    $product_ids = get_ids_from_data();

    if (!empty($product_ids)) {
        foreach ($product_ids as $id) {
            $id = (int)$id;
            if ($id <= 0) {
                continue;
            }

            $sql_sale = "UPDATE product SET product_sale = {$sale} WHERE product_id = '{$id}'";
            mysqli_query($mysqli, $sql_sale);
        }
    }

    header('Location: ../../index.php?action=product&query=product_list&message=success');
    exit;
}

/* =====================================================
 * 4) XÓA ĐÁNH GIÁ
 * ===================================================== */
elseif (isset($_GET['deleteevaluate']) && (int)$_GET['deleteevaluate'] === 1) {

    $evaluate_ids = get_ids_from_data();

    if (!empty($evaluate_ids)) {
        foreach ($evaluate_ids as $id) {
            $id = (int)$id;
            if ($id <= 0) {
                continue;
            }

            $sql_delete_evaluate = "DELETE FROM evaluate WHERE evaluate_id = '{$id}'";
            mysqli_query($mysqli, $sql_delete_evaluate);
        }
    }

    header('Location: ../../index.php?action=product&query=product_edit&product_id=' . $product_id . '&message=success#product_evaluate');
    exit;
}

/* =====================================================
 * 5) ĐÁNH DẤU SPAM ĐÁNH GIÁ
 * ===================================================== */
elseif (isset($_GET['spamevaluate']) && (int)$_GET['spamevaluate'] === 1) {

    $evaluate_ids = get_ids_from_data();

    if (!empty($evaluate_ids)) {
        foreach ($evaluate_ids as $id) {
            $id = (int)$id;
            if ($id <= 0) {
                continue;
            }

            $sql_update_evaluate = "UPDATE evaluate SET evaluate_status = -1 WHERE evaluate_id = '{$id}'";
            mysqli_query($mysqli, $sql_update_evaluate);
        }
    }

    header('Location: ../../index.php?action=product&query=product_edit&product_id=' . $product_id . '&message=success#product_evaluate');
    exit;
}

/* =====================================================
 * 6) XÓA / ẨN SẢN PHẨM HÀNG LOẠT
 * ===================================================== */
else {

    $product_ids = get_ids_from_data();

    if (!empty($product_ids)) {
        foreach ($product_ids as $id) {
            $id = (int)$id;
            if ($id <= 0) {
                continue;
            }

            if (product_has_related_data($mysqli, $id)) {
                $sql_hide = "UPDATE product SET product_status = 0 WHERE product_id = '{$id}'";
                mysqli_query($mysqli, $sql_hide);
            } else {
                delete_product_image($mysqli, $id);

                $sql_delete = "DELETE FROM product WHERE product_id = '{$id}'";
                mysqli_query($mysqli, $sql_delete);
            }
        }
    }

    header('Location: ../../index.php?action=product&query=product_list&message=success');
    exit;
}
?>