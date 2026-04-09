<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('guha');
    session_start();
}

require_once __DIR__ . '/../../admin/config/config.php';
$mysqli->set_charset('utf8mb4');

function back_register(string $status = 'error', ?array $oldData = null): void
{
    if ($oldData !== null) {
        unset($oldData['account_password'], $oldData['account_password_confirn']);
        $_SESSION['register_old'] = $oldData;
    }

    header('Location: ../../index.php?page=register&message=' . urlencode($status));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['register'])) {
    back_register('error');
}

$name = trim($_POST['account_name'] ?? '');
$email = trim($_POST['account_email'] ?? '');
$phone = trim($_POST['account_phone'] ?? '');
$password = trim($_POST['account_password'] ?? '');
$password2 = trim($_POST['account_password_confirn'] ?? '');
$gender = isset($_POST['account_gender']) ? (int) $_POST['account_gender'] : 0;

$customer_address = trim($_POST['customer_address'] ?? '');

$oldData = [
    'account_name' => $name,
    'account_email' => $email,
    'account_phone' => $phone,
    'account_gender' => $gender,
    'customer_address' => $customer_address,
];

if (
    $name === '' ||
    $email === '' ||
    $phone === '' ||
    $customer_address === '' ||
    $password === '' ||
    $password2 === ''
) {
    back_register('invalid', $oldData);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    back_register('invalid_email', $oldData);
}

if ($password !== $password2) {
    back_register('password_mismatch', $oldData);
}

$stmt = $mysqli->prepare("SELECT account_id FROM account WHERE account_email = ? LIMIT 1");
if (!$stmt) {
    back_register('error', $oldData);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    back_register('exists', $oldData);
}
$stmt->close();

$hash = md5($password);

$stmt = $mysqli->prepare("
    INSERT INTO account
    (account_name, account_password, account_email, account_phone, account_type, account_status)
    VALUES (?, ?, ?, ?, 0, 1)
");
if (!$stmt) {
    back_register('error', $oldData);
}

$stmt->bind_param("ssss", $name, $hash, $email, $phone);

if (!$stmt->execute()) {
    $stmt->close();
    back_register('error', $oldData);
}

$account_id = (int) $stmt->insert_id;
$stmt->close();

$stmt = $mysqli->prepare("
    INSERT INTO customer
    (customer_name, customer_email, customer_address, customer_phone, customer_gender, account_id)
    VALUES (?, ?, ?, ?, ?, ?)
");
if (!$stmt) {
    back_register('error', $oldData);
}

$stmt->bind_param("ssssii", $name, $email, $customer_address, $phone, $gender, $account_id);

if (!$stmt->execute()) {
    $stmt->close();
    back_register('error', $oldData);
}
$stmt->close();

unset($_SESSION['register_old']);

$_SESSION['account_id'] = $account_id;
$_SESSION['account_email'] = $email;
$_SESSION['account_name'] = $name;
$_SESSION['account_phone'] = $phone;

header('Location: ../../index.php?page=home&message=success');
exit;