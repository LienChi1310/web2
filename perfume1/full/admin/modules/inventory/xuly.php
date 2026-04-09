<?php
include('../../config/config.php');
// db_escape(), calculate_sell_price() available from config/helpers.php

/* =====================================================
 * 1) TẠO PHIẾU NHẬP
 * ===================================================== */
if (isset($_POST['inventory_add'])) {

    $inventory_date = isset($_POST['inventory_date']) && $_POST['inventory_date'] !== ''
        ? db_escape($mysqli, $_POST['inventory_date'])
        : date('Y-m-d H:i:s');

    $product_ids   = $_POST['product_id'] ?? [];
    $quantities    = $_POST['quantity'] ?? [];
    $price_imports = $_POST['price_import'] ?? [];

    if (empty($product_ids)) {
        header('Location: ../../index.php?action=inventory&query=inventory_add');
        exit;
    }

    mysqli_begin_transaction($mysqli);

    try {
        $sql_inventory = "
            INSERT INTO inventory(inventory_date, inventory_status)
            VALUES ('$inventory_date', 0)
        ";
        mysqli_query($mysqli, $sql_inventory);

        $inventory_id = mysqli_insert_id($mysqli);

        for ($i = 0; $i < count($product_ids); $i++) {

            $product_id   = (int)$product_ids[$i];
            $quantity     = (int)$quantities[$i];
            $price_import = (int)$price_imports[$i];

            if ($product_id <= 0 || $quantity <= 0) continue;

            mysqli_query($mysqli, "
                INSERT INTO inventory_detail(inventory_id, product_id, quantity, price_import)
                VALUES ('$inventory_id', '$product_id', '$quantity', '$price_import')
            ");
        }

        mysqli_commit($mysqli);

        header('Location: ../../index.php?action=inventory&query=inventory_list&message=success');
        exit;
    } catch (Exception $e) {
        mysqli_rollback($mysqli);
        header('Location: ../../index.php?action=inventory&query=inventory_add');
        exit;
    }
}

/* =====================================================
 * 2) HỦY PHIẾU NHẬP
 * ===================================================== */
if (isset($_GET['action']) && $_GET['action'] == 'cancel') {

    $inventory_id = (int)$_GET['inventory_id'];

    mysqli_query($mysqli, "
        UPDATE inventory 
        SET inventory_status = -1 
        WHERE inventory_id = '$inventory_id'
    ");

    header('Location: ../../index.php?action=inventory&query=inventory_list');
    exit;
}

/* =====================================================
 * 3) HOÀN THÀNH PHIẾU NHẬP
 * ===================================================== */
if (isset($_GET['action']) && $_GET['action'] == 'complete') {

    $inventory_id = (int)$_GET['inventory_id'];

    $sql_inventory = "SELECT * FROM inventory WHERE inventory_id = '$inventory_id' LIMIT 1";
    $query_inventory = mysqli_query($mysqli, $sql_inventory);
    $inv = mysqli_fetch_assoc($query_inventory);

    if (!$inv || $inv['inventory_status'] != 0) {
        header('Location: ../../index.php?action=inventory&query=inventory_list');
        exit;
    }

    $sql_detail = "SELECT * FROM inventory_detail WHERE inventory_id = '$inventory_id'";
    $query_detail = mysqli_query($mysqli, $sql_detail);

    mysqli_begin_transaction($mysqli);

    try {

        while ($row = mysqli_fetch_assoc($query_detail)) {

            $product_id = $row['product_id'];
            $qty        = $row['quantity'];
            $price      = $row['price_import'];

            $sql_product = "
                SELECT product_quantity, product_price_import, product_profit_percent
                FROM product WHERE product_id = '$product_id'
            ";
            $query_product = mysqli_query($mysqli, $sql_product);
            $product = mysqli_fetch_assoc($query_product);

            if (!$product) continue;

            $old_qty   = $product['product_quantity'];
            $old_price = $product['product_price_import'];
            $profit    = $product['product_profit_percent'];

            $new_qty = $old_qty + $qty;

            // 🔥 GIÁ VỐN BÌNH QUÂN
            if ($new_qty > 0) {
                $new_import = (($old_qty * $old_price) + ($qty * $price)) / $new_qty;
            } else {
                $new_import = $price;
            }

            // 🔥 GIÁ BÁN ĐÚNG ĐỀ
            $new_sell = calculate_sell_price($new_import, $profit);

            mysqli_query($mysqli, "
                UPDATE product SET
                    product_quantity = '$new_qty',
                    product_price_import = '$new_import',
                    product_price = '$new_sell',
                    product_status = 1
                WHERE product_id = '$product_id'
            ");
        }

        mysqli_query($mysqli, "
            UPDATE inventory 
            SET inventory_status = 1 
            WHERE inventory_id = '$inventory_id'
        ");

        mysqli_commit($mysqli);

        header('Location: ../../index.php?action=inventory&query=inventory_detail&inventory_id=' . $inventory_id);
        exit;
    } catch (Exception $e) {
        mysqli_rollback($mysqli);
        header('Location: ../../index.php?action=inventory&query=inventory_list');
        exit;
    }
}

/* =====================================================
 * 4) SỬA PHIẾU NHẬP
 * ===================================================== */
if (isset($_POST['inventory_edit'])) {

    $inventory_id = (int)$_POST['inventory_id'];

    // chỉ cho sửa nếu chưa hoàn thành
    $check = mysqli_fetch_assoc(mysqli_query($mysqli, "
        SELECT inventory_status FROM inventory WHERE inventory_id = '$inventory_id'
    "));

    if ($check['inventory_status'] != 0) {
        header('Location: ../../index.php?action=inventory&query=inventory_list');
        exit;
    }

    mysqli_query($mysqli, "
        DELETE FROM inventory_detail 
        WHERE inventory_id = '$inventory_id'
    ");

    $product_ids = $_POST['product_id'];
    $quantities  = $_POST['quantity'];
    $prices      = $_POST['price_import'];

    for ($i = 0; $i < count($product_ids); $i++) {

        $pid = (int)$product_ids[$i];
        $qty = (int)$quantities[$i];
        $pri = (int)$prices[$i];

        if ($pid <= 0 || $qty <= 0) continue;

        mysqli_query($mysqli, "
            INSERT INTO inventory_detail(inventory_id, product_id, quantity, price_import)
            VALUES('$inventory_id','$pid','$qty','$pri')
        ");
    }

    header('Location: ../../index.php?action=inventory&query=inventory_detail&inventory_id=' . $inventory_id);
    exit;
}

/* =====================================================
 * DEFAULT
 * ===================================================== */
header('Location: ../../index.php?action=inventory&query=inventory_list');
exit;
