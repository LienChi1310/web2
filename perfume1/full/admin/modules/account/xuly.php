<?php
session_start();
include('../../config/config.php');

function acc_escape($mysqli, $value)
{
    return mysqli_real_escape_string($mysqli, trim((string)$value));
}

$account_id = isset($_GET['account_id']) ? (int)$_GET['account_id'] : 0;

/* ==============================
 * 1. THÊM TÀI KHOẢN (ADMIN)
 * ============================== */
if (isset($_POST['account_add'])) {

    if (!isset($_SESSION['account_type']) || (int)$_SESSION['account_type'] !== 2) {
        header('Location:../../index.php?action=account&query=account_list&message=error');
        exit;
    }

    $account_name     = acc_escape($mysqli, $_POST['account_name'] ?? '');
    $account_email    = acc_escape($mysqli, $_POST['account_email'] ?? '');
    $account_phone    = acc_escape($mysqli, $_POST['account_phone'] ?? '');
    $account_password = trim($_POST['account_password'] ?? '');
    $account_type     = isset($_POST['account_type']) ? (int)$_POST['account_type'] : 0;
    $account_status   = isset($_POST['account_status']) ? (int)$_POST['account_status'] : 1;

    if ($account_name === '' || $account_email === '' || $account_password === '') {
        header('Location:../../index.php?action=account&query=account_list&message=error');
        exit;
    }

    $check_email = mysqli_query($mysqli, "SELECT account_id FROM account WHERE account_email = '{$account_email}' LIMIT 1");
    if ($check_email && mysqli_num_rows($check_email) > 0) {
        header('Location:../../index.php?action=account&query=account_list&message=error');
        exit;
    }

    $account_password_md5 = md5($account_password);

    $sql_insert = "
        INSERT INTO account(account_name, account_password, account_email, account_phone, account_type, account_status)
        VALUES ('{$account_name}', '{$account_password_md5}', '{$account_email}', '{$account_phone}', '{$account_type}', '{$account_status}')
    ";
    mysqli_query($mysqli, $sql_insert);

    header('Location:../../index.php?action=account&query=account_list&message=success');
    exit;
}

/* ==============================
 * 2. SỬA THÔNG TIN ACCOUNT (USER)
 * ============================== */
if (isset($_POST['account_edit'])) {
    $account_name = acc_escape($mysqli, $_POST['account_name'] ?? '');
    $account_phone = acc_escape($mysqli, $_POST['account_phone'] ?? '');
    $account_address = acc_escape($mysqli, $_POST['account_address'] ?? '');
    $customer_gender = isset($_POST['customer_gender']) ? (int)$_POST['customer_gender'] : 0;

    mysqli_query($mysqli, "
        UPDATE account 
        SET account_name = '{$account_name}', account_phone = '{$account_phone}' 
        WHERE account_id = {$account_id}
    ");

    mysqli_query($mysqli, "
        UPDATE customer 
        SET customer_name = '{$account_name}', 
            customer_phone = '{$account_phone}', 
            customer_gender = '{$customer_gender}', 
            customer_address = '{$account_address}' 
        WHERE account_id = {$account_id}
    ");

    header('Location:../../index.php?action=account&query=my_account&message=success');
    exit;
}

/* ==============================
 * 3. ADMIN SỬA QUYỀN + TRẠNG THÁI
 * ============================== */
if (isset($_POST['account_change'])) {
    if (isset($_SESSION['account_type']) && (int)$_SESSION['account_type'] === 2 && (int)($_SESSION['account_id_admin'] ?? 0) !== $account_id) {

        $account_type = isset($_POST['account_type']) ? (int)$_POST['account_type'] : 0;
        $account_status = isset($_POST['account_status']) ? (int)$_POST['account_status'] : 1;

        mysqli_query($mysqli, "
            UPDATE account 
            SET account_type = {$account_type}, account_status = {$account_status}
            WHERE account_id = {$account_id}
        ");

        header('Location:../../index.php?action=account&query=account_list&message=success');
    } else {
        header('Location:../../index.php?action=account&query=account_list&message=error');
    }
    exit;
}

/* ==============================
 * 4. RESET PASSWORD
 * ============================== */
if (isset($_GET['reset_password']) && $_GET['reset_password'] == 1) {

    if (isset($_SESSION['account_type']) && (int)$_SESSION['account_type'] === 2 && $account_id > 0) {

        $new_password = md5('123456');

        mysqli_query($mysqli, "
            UPDATE account 
            SET account_password = '{$new_password}'
            WHERE account_id = {$account_id}
        ");

        header('Location:../../index.php?action=account&query=account_list&message=success');
    } else {
        header('Location:../../index.php?action=account&query=account_list&message=error');
    }
    exit;
}

/* ==============================
 * 5. KHÓA / MỞ KHÓA NHANH
 * ============================== */
if (isset($_GET['toggle_status']) && $_GET['toggle_status'] == 1) {

    if (isset($_SESSION['account_type']) && (int)$_SESSION['account_type'] === 2 && $account_id > 0) {

        $status = isset($_GET['status']) ? (int)$_GET['status'] : 0;

        mysqli_query($mysqli, "
            UPDATE account 
            SET account_status = {$status}
            WHERE account_id = {$account_id}
        ");

        header('Location:../../index.php?action=account&query=account_list&message=success');
    } else {
        header('Location:../../index.php?action=account&query=account_list&message=error');
    }
    exit;
}

header('Location:../../index.php?action=account&query=account_list&message=error');
exit;
?>