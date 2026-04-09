<?php
include('../../config/config.php');

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
 * ===================================================== */ elseif (isset($_POST['product_edit'])) {

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
    $delete_image       = isset($_POST['delete_image']) && $_POST['delete_image'] === '1' ? true : false;

    $profit_sql_part = $has_profit_column ? "product_profit_percent = '{$product_profit_percent}'," : "";

    // Handle image deletion
    if ($delete_image) {
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
                product_image        = '',
                product_status       = '{$product_status}'
            WHERE product_id        = '{$product_id}'
        ";
        mysqli_query($mysqli, $sql_update);
    } elseif ($product_image_name !== '') {
        // Handle new image upload
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
        mysqli_query($mysqli, $sql_update);
    } else {
        // No image change
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
        mysqli_query($mysqli, $sql_update);
    }

    header('Location: ../../index.php?action=product&query=product_list&message=success');
    exit;
}

/* =====================================================
 * 3) SET GIẢM GIÁ HÀNG LOẠT
 * ===================================================== */ elseif (isset($_GET['product_sale'])) {

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
 * ===================================================== */ elseif (isset($_GET['deleteevaluate']) && (int)$_GET['deleteevaluate'] === 1) {

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
 * ===================================================== */ elseif (isset($_GET['spamevaluate']) && (int)$_GET['spamevaluate'] === 1) {

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
 * ===================================================== */ else {

    $product_ids = get_ids_from_data();
    $hidden_count = 0;
    $deleted_count = 0;
    $products_with_stock = [];

    if (!empty($product_ids)) {
        foreach ($product_ids as $id) {
            $id = (int)$id;
            if ($id <= 0) {
                continue;
            }

            $stock = get_product_stock($mysqli, $id);

            // Check if product has stock or related data
            if ($stock > 0 || product_has_related_data($mysqli, $id)) {
                // Hide the product instead of deleting
                $sql_hide = "UPDATE product SET product_status = 0 WHERE product_id = '{$id}'";
                mysqli_query($mysqli, $sql_hide);
                $hidden_count++;

                if ($stock > 0) {
                    $products_with_stock[] = $id;
                }
            } else {
                // Product can be safely deleted (no stock, no orders, no inventory)
                delete_product_image($mysqli, $id);
                $sql_delete = "DELETE FROM product WHERE product_id = '{$id}'";
                mysqli_query($mysqli, $sql_delete);
                $deleted_count++;
            }
        }
    }

    // Prepare message based on what happened
    $message = 'success';
    if (!empty($products_with_stock)) {
        $message = 'success_with_stock';
    }

    header('Location: ../../index.php?action=product&query=product_list&message=' . $message . '&hidden=' . $hidden_count . '&deleted=' . $deleted_count);
    exit;
}
