<?php
/**
 * PHASE 3: ĐẶT HÀNG THẬT VÀ GIẢ LẬP THANH TOÁN
 * - Ghi đơn vào DB (delivery, orders, order_detail, trừ tồn kho)
 * - Không gọi MoMo/VNPAY thật
 * - Dùng SESSION order_summary để thankiu hiển thị
 */

if (session_status() === PHP_SESSION_NONE) {
    session_name('guha');
    session_start();
}

// ==== KẾT NỐI DB ====
require_once __DIR__ . '/../../admin/config/config.php';
if (isset($mysqli) && $mysqli instanceof mysqli) {
    @$mysqli->set_charset('utf8mb4');
}

/* ======= Helper: sinh số nguyên duy nhất cho cột ID không auto increment ======= */
function generate_unique_int(mysqli $db, string $table, string $col, int $digits = 8): int {
    do {
        $code = (int)str_pad((string)random_int(0, (10 ** $digits) - 1), $digits, '0', STR_PAD_LEFT);
        $sql  = "SELECT 1 FROM {$table} WHERE {$col} = ? LIMIT 1";
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            break;
        }
        $stmt->bind_param('i', $code);
        $stmt->execute();
        $exists = (bool)$stmt->get_result()->fetch_row();
        $stmt->close();
    } while ($exists);
    return $code;
}

/* ======= Form không submit đúng ======== */
if (!isset($_POST['redirect'])) {
    header('Location: ../../index.php?page=cart');
    exit;
}

/* ======= Bắt buộc đăng nhập ======= */
if (empty($_SESSION['account_id']) && empty($_SESSION['account_email'])) {
    header('Location: ../../index.php?page=login');
    exit;
}

$account_id = (int)($_SESSION['account_id'] ?? 0);

/* ======= Lấy dữ liệu từ form ======= */
$address_type = $_POST['address_type'] ?? 'default';

$delivery_name    = trim($_POST['delivery_name'] ?? '');
$delivery_address = trim($_POST['delivery_address'] ?? '');
$delivery_phone   = trim($_POST['delivery_phone'] ?? '');
$delivery_note    = trim($_POST['delivery_note'] ?? '');
$order_type       = (int)($_POST['order_type'] ?? 1); // 1 COD – 2 Momo – 4 VNPAY
$mode             = $_POST['mode'] ?? 'cart';         // từ checkout.php gửi qua

if ($address_type === 'new') {
    $delivery_name    = trim($_POST['new_delivery_name'] ?? '');
    $delivery_address = trim($_POST['new_delivery_address'] ?? '');
    $delivery_phone   = trim($_POST['new_delivery_phone'] ?? '');
}

/* ======= Validate ======= */
$errors = [];

if ($delivery_name === '') {
    $errors[] = 'Vui lòng nhập tên người nhận.';
}
if ($delivery_address === '') {
    $errors[] = 'Vui lòng nhập địa chỉ nhận hàng.';
}
if ($delivery_phone === '') {
    $errors[] = 'Vui lòng nhập số điện thoại.';
} elseif (!preg_match('/^0\d{9,10}$/', $delivery_phone)) {
    $errors[] = 'Số điện thoại phải bắt đầu bằng 0 và có 10-11 số.';
}

if (!in_array($address_type, ['default', 'new'], true)) {
    $errors[] = 'Loại địa chỉ không hợp lệ.';
}

if (!in_array($order_type, [1, 2, 4], true)) {
    $order_type = 1;
}

if (!empty($errors)) {
    $_SESSION['checkout_errors'] = $errors;
    header('Location: ../../index.php?page=checkout');
    exit;
}

/* ======= Lấy danh sách sản phẩm từ SESSION ======= */
$sessionItems = [];

if ($mode === 'buynow' && !empty($_SESSION['buynow'])) {
    $sessionItems[] = $_SESSION['buynow'];
} elseif (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $sessionItems = $_SESSION['cart'];
} else {
    header('Location: ../../index.php?page=cart');
    exit;
}

/* ======= Chuẩn hoá & kiểm tra tồn kho, tính lại tổng từ DB ======= */
$orderItems   = [];
$total_amount = 0.0;

foreach ($sessionItems as $item) {
    $pid = (int)($item['product_id'] ?? 0);
    $qty = (int)($item['product_quantity'] ?? 0);

    if ($pid <= 0 || $qty <= 0) {
        continue;
    }

    $stmt = $mysqli->prepare("
        SELECT product_id, product_name, product_price, product_sale,
               product_quantity, quantity_sales, product_image
        FROM product
        WHERE product_id = ?
        LIMIT 1
    ");
    if (!$stmt) {
        $_SESSION['checkout_errors'] = ['Lỗi hệ thống (prepare product). Vui lòng thử lại sau.'];
        header('Location: ../../index.php?page=cart');
        exit;
    }

    $stmt->bind_param('i', $pid);
    $stmt->execute();
    $res  = $stmt->get_result();
    $prod = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$prod) {
        $_SESSION['checkout_errors'] = ['Sản phẩm không tồn tại hoặc đã bị xoá.'];
        header('Location: ../../index.php?page=cart');
        exit;
    }

    if ((int)$prod['product_quantity'] < $qty) {
        $_SESSION['checkout_errors'] = [
            'Sản phẩm "' . $prod['product_name'] . '" không đủ số lượng trong kho.'
        ];
        header('Location: ../../index.php?page=cart');
        exit;
    }

    $price = (float)$prod['product_price'];
    $sale  = (float)$prod['product_sale'];
    $unit  = $price - ($price * $sale / 100);
    if ($unit < 0) {
        $unit = 0;
    }

    $line = $unit * $qty;
    $total_amount += $line;

    $orderItems[] = [
        'product_id'       => (int)$prod['product_id'],
        'product_name'     => $prod['product_name'],
        'product_quantity' => $qty,
        'product_price'    => $price,
        'product_sale'     => $sale,
        'product_image'    => $prod['product_image'],
    ];
}

if (empty($orderItems)) {
    $_SESSION['checkout_errors'] = ['Giỏ hàng trống hoặc không hợp lệ.'];
    header('Location: ../../index.php?page=cart');
    exit;
}

/* ======= Sinh mã đơn (INT) & delivery_id ======= */
$order_code  = generate_unique_int($mysqli, 'orders', 'order_code', 8);
$delivery_id = generate_unique_int($mysqli, 'delivery', 'delivery_id', 8);

/* ======= GHI ĐƠN VÀO DB TRONG TRANSACTION ======= */
$mysqli->begin_transaction();

try {
    // 1) Insert delivery
    $stmt = $mysqli->prepare("
        INSERT INTO delivery (delivery_id, account_id, delivery_name, delivery_phone, delivery_address, delivery_note)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        throw new Exception('Lỗi tạo đơn (prepare delivery).');
    }

    $stmt->bind_param(
        'iissss',
        $delivery_id,
        $account_id,
        $delivery_name,
        $delivery_phone,
        $delivery_address,
        $delivery_note
    );
    $stmt->execute();
    $stmt->close();

    // 2) Insert orders
    $order_date = date('Y-m-d H:i:s');

    $stmt = $mysqli->prepare("
        INSERT INTO orders (order_code, order_date, account_id, delivery_id, total_amount, order_type, order_status)
        VALUES (?, ?, ?, ?, ?, ?, 0)
    ");
    if (!$stmt) {
        throw new Exception('Lỗi tạo đơn (prepare orders).');
    }

    $stmt->bind_param(
        'isiidi',
        $order_code,
        $order_date,
        $account_id,
        $delivery_id,
        $total_amount,
        $order_type
    );
    $stmt->execute();
    $stmt->close();

    // 3) Insert order_detail + cập nhật stock
    $stmtDetail = $mysqli->prepare("
        INSERT INTO order_detail (order_code, product_id, product_quantity, product_price, product_sale)
        VALUES (?, ?, ?, ?, ?)
    ");
    if (!$stmtDetail) {
        throw new Exception('Lỗi tạo đơn (prepare order_detail).');
    }

    $stmtUpd = $mysqli->prepare("
        UPDATE product
           SET product_quantity = product_quantity - ?,
               quantity_sales   = quantity_sales + ?
         WHERE product_id = ?
    ");
    if (!$stmtUpd) {
        throw new Exception('Lỗi cập nhật tồn kho.');
    }

    foreach ($orderItems as $oi) {
        $pid  = (int)$oi['product_id'];
        $qty  = (int)$oi['product_quantity'];
        $p    = (float)$oi['product_price'];
        $sale = (float)$oi['product_sale'];

        $stmtDetail->bind_param('iiidd', $order_code, $pid, $qty, $p, $sale);
        $stmtDetail->execute();

        $stmtUpd->bind_param('iii', $qty, $qty, $pid);
        $stmtUpd->execute();
    }

    $stmtDetail->close();
    $stmtUpd->close();

    $mysqli->commit();
} catch (Throwable $e) {
    $mysqli->rollback();
    $_SESSION['checkout_errors'] = ['Có lỗi xảy ra khi tạo đơn. Vui lòng thử lại sau.'];
    header('Location: ../../index.php?page=cart');
    exit;
}

/* ======= GIẢ LẬP THANH TOÁN (CHƯA XÁC NHẬN) ======= */
$is_paid = 0;

if ($order_type == 1) {
    $payment_text = "COD – Thanh toán khi nhận hàng";
} elseif ($order_type == 2) {
    $payment_text = "Thanh toán MOMO (giả lập – chờ xác nhận)";
} elseif ($order_type == 4) {
    $payment_text = "Thanh toán VNPAY (giả lập – chờ xác nhận)";
} else {
    $payment_text = "Thanh toán (giả lập)";
}

/* ======= Lưu vào session cho trang thankiu & gateway fake ======= */
$_SESSION['order_summary'] = [
    'order_code'       => $order_code,
    'delivery_name'    => $delivery_name,
    'delivery_address' => $delivery_address,
    'delivery_phone'   => $delivery_phone,
    'delivery_note'    => $delivery_note,
    'address_type'     => $address_type,
    'order_type'       => $order_type,
    'payment_method'   => $payment_text,
    'is_paid'          => $is_paid,
    'total_amount'     => $total_amount,
    'items'            => $orderItems,
];

/* ======= Điều hướng: COD -> thankiu, MoMo/VNPAY -> màn fake ======= */
if ($order_type == 1) {
    header('Location: ../../index.php?page=thankiu&order_type=1');
} elseif ($order_type == 2) {
    header('Location: ../../index.php?page=payment_momo_fake');
} elseif ($order_type == 4) {
    header('Location: ../../index.php?page=payment_vnpay_fake');
} else {
    header('Location: ../../index.php?page=thankiu');
}
exit;