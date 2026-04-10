<?php
include('../../config/config.php');

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

/* ==========================================================
 * 1) THÊM DANH MỤC
 * ========================================================== */
if (isset($_POST['category_add'])) {

    $category_name        = mysqli_real_escape_string($mysqli, $_POST['category_name'] ?? '');
    $category_description = mysqli_real_escape_string($mysqli, $_POST['category_description'] ?? '');

    // Xử lý ảnh
    $category_image_name = $_FILES['category_image']['name']     ?? '';
    $category_image_tmp  = $_FILES['category_image']['tmp_name'] ?? '';
    $category_image      = '';

    if ($category_image_name !== '') {
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type     = mime_content_type($category_image_tmp);

        if (in_array($file_type, $allowed_types)) {
            $category_image = time() . '_' . basename($category_image_name);
            move_uploaded_file($category_image_tmp, 'uploads/' . $category_image);
        }
    }

    $sql_add = "
        INSERT INTO category(category_name, category_description, category_image)
        VALUE ('{$category_name}', '{$category_description}', '{$category_image}')
    ";

    mysqli_query($mysqli, $sql_add);
    header('Location: ../../index.php?action=category&query=category_list');
    exit;
}

/* ==========================================================
 * 2) SỬA DANH MỤC
 * ========================================================== */ elseif (isset($_POST['category_edit'])) {

    $category_name        = mysqli_real_escape_string($mysqli, $_POST['category_name'] ?? '');
    $category_description = mysqli_real_escape_string($mysqli, $_POST['category_description'] ?? '');

    $category_image_name = $_FILES['category_image']['name']     ?? '';
    $category_image_tmp  = $_FILES['category_image']['tmp_name'] ?? '';
    $category_image      = '';
    $upload_success      = true;

    if ($category_image_name !== '') {
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type     = mime_content_type($category_image_tmp);

        if (!in_array($file_type, $allowed_types)) {
            $upload_success = false;
            // File type invalid - skip upload, keep old image
        } else {
            // Tạo tên file mới
            $category_image = time() . '_' . basename($category_image_name);

            // Upload file mới
            if (move_uploaded_file($category_image_tmp, 'uploads/' . $category_image)) {
                // Lấy ảnh cũ
                $sql   = "SELECT category_image FROM category WHERE category_id = '{$category_id}' LIMIT 1";
                $query = mysqli_query($mysqli, $sql);
                $row   = mysqli_fetch_array($query);

                // Xóa ảnh cũ nếu tồn tại
                if (!empty($row['category_image']) && file_exists('uploads/' . $row['category_image'])) {
                    @unlink('uploads/' . $row['category_image']);
                }
            } else {
                // Upload thất bại - reset image name to not update DB
                $category_image = '';
                $upload_success = false;
            }
        }
    }

    // Update database
    if (!empty($category_image)) {
        // Có ảnh mới - update ảnh
        $sql_update = "
            UPDATE category 
            SET category_name        = '{$category_name}',
                category_description = '{$category_description}',
                category_image       = '{$category_image}'
            WHERE category_id = '{$category_id}'
        ";
    } else {
        // Không đổi ảnh - chỉ update tên và mô tả
        $sql_update = "
            UPDATE category 
            SET category_name        = '{$category_name}',
                category_description = '{$category_description}'
            WHERE category_id = '{$category_id}'
        ";
    }

    mysqli_query($mysqli, $sql_update);
    header('Location: ../../index.php?action=category&query=category_list');
    exit;
}

/* ==========================================================
 * 3) XOÁ NHIỀU DANH MỤC (?data=[...])
 * ========================================================== */ else {

    $category_ids = get_ids_from_data();

    foreach ($category_ids as $id) {
        $id = (int)$id;
        if ($id <= 0) continue;

        // Lấy & xóa ảnh
        $sql   = "SELECT category_image FROM category WHERE category_id = '{$id}' LIMIT 1";
        $query = mysqli_query($mysqli, $sql);
        $row   = mysqli_fetch_array($query);

        if (!empty($row['category_image']) && file_exists('uploads/' . $row['category_image'])) {
            @unlink('uploads/' . $row['category_image']);
        }

        // Xóa record
        mysqli_query($mysqli, "DELETE FROM category WHERE category_id = '{$id}'");
    }

    header('Location: ../../index.php?action=category&query=category_list');
    exit;
}
