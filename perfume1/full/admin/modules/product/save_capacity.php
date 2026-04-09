<?php
include('../../config/config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$capacity_name = isset($_POST['capacity_name']) ? trim($_POST['capacity_name']) : '';
$capacity_value = isset($_POST['capacity_value']) ? (int)$_POST['capacity_value'] : 0;

// Validate
if (empty($capacity_name) || $capacity_value <= 0) {
    echo json_encode(['success' => false, 'error' => 'Dữ liệu không hợp lệ']);
    exit;
}

// Check if already exists
$check_sql = "SELECT capacity_id FROM capacity WHERE capacity_name = '" . db_escape($mysqli, $capacity_name) . "'";
$check_query = mysqli_query($mysqli, $check_sql);

if (mysqli_num_rows($check_query) > 0) {
    $row = mysqli_fetch_assoc($check_query);
    echo json_encode(['success' => true, 'capacity_id' => $row['capacity_id'], 'message' => 'Dung tích đã tồn tại']);
    exit;
}

// Insert new capacity
$insert_sql = "INSERT INTO capacity (capacity_name) VALUES ('" . db_escape($mysqli, $capacity_name) . "')";
$insert_result = mysqli_query($mysqli, $insert_sql);

if ($insert_result) {
    $capacity_id = mysqli_insert_id($mysqli);
    echo json_encode(['success' => true, 'capacity_id' => $capacity_id, 'message' => 'Đã lưu dung tích thành công']);
} else {
    echo json_encode(['success' => false, 'error' => 'Lỗi cơ sở dữ liệu: ' . mysqli_error($mysqli)]);
}
