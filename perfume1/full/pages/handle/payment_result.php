<?php
// payment_result.php – xử lý kết quả thanh toán GIẢ LẬP cho MoMo
// ⏸️ TEMPORARILY: VNPAY payment gateway is paused (API disabled)

if (session_status() === PHP_SESSION_NONE) {
    session_name('guha');
    session_start();
}

require_once __DIR__ . '/../../admin/config/config.php';
if (isset($mysqli) && $mysqli instanceof mysqli) {
    @$mysqli->set_charset('utf8mb4');
}

$gateway    = $_GET['gateway'] ?? '';          // ⏸️ TEMPORARILY: only 'momo' accepted (vnpay disabled)
$status     = $_GET['status']  ?? '';          // success | fail (hoặc cancel)
$order_code = trim($_GET['order_code'] ?? ''); // ĐỂ DẠNG STRING, KHÔNG ÉP int

// Thiếu dữ liệu cơ bản => về trang chủ
if ($order_code === '' || empty($_SESSION['order_summary'])) {
    header('Location: ../../index.php');
    exit;
}

// Kiểm tra order_code trong session cho chắc
if (
    !empty($_SESSION['order_summary']['order_code'])
    && (string)$_SESSION['order_summary']['order_code'] !== (string)$order_code
) {
    // Không trùng thì thôi, tránh update nhầm đơn
    header('Location: ../../index.php?page=thankiu');
    exit;
}

/**
 * TRƯỜNG HỢP 1: THANH TOÁN THÀNH CÔNG
 * - Cập nhật orders.order_status = 1 (đã thanh toán)
 * - order_type = 2 (MoMo only - VNPAY is now paused)
 * - Cập nhật session order_summary (is_paid = 1)
 * - Chuyển sang trang thankiu (thankiu sẽ xoá giỏ hàng như hiện tại)
 */
if ($status === 'success') {
    // ⏸️ TEMPORARILY: Only MoMo gateway is active
    if ($gateway !== 'momo') {
        // Reject non-MoMo payments (VNPAY paused)
        header('Location: ../../index.php?page=cart&payment=unsupported_gateway');
        exit;
    }

    // MoMo only
    $order_type = 2;

    // ⏸️ Cập nhật order_type (không thay đổi status)
    // Status vẫn giữ nguyên 0 (Đang xử lý) cho đến khi admin xác nhận & chuẩn bị
    $stmt = $mysqli->prepare("
        UPDATE orders
           SET order_type = ?
         WHERE order_code = ?
         LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param('ii', $order_type, $order_code);
        $stmt->execute();
        $stmt->close();
    }

    // Cập nhật info cho màn thankiu
    $_SESSION['order_summary']['payment_method'] = 'Thanh toán MOMO';
    $_SESSION['order_summary']['is_paid'] = 1;

    header('Location: ../../index.php?page=thankiu');
    exit;
}

/**
 * TRƯỜNG HỢP 2: HỦY / THANH TOÁN THẤT BẠI
 * - Đơn online này KHÔNG được tính là đơn hợp lệ nữa
 * - Để không xuất hiện ở lịch sử khách hàng: set order_status = -1
 * - KHÔNG xoá giỏ hàng → customer quay lại cart vẫn còn sản phẩm
 * - Xoá order_summary để không còn quay lại màn hình này
 */
$failedStatus = -1; // -1 = đã hủy / thanh toán thất bại (sẽ ẩn khỏi lịch sử user)

$stmt = $mysqli->prepare("
    UPDATE orders
       SET order_status = ?
     WHERE order_code   = ?
     LIMIT 1
");
if ($stmt) {
    $stmt->bind_param('is', $failedStatus, $order_code);
    $stmt->execute();
    $stmt->close();
}

// Xoá tóm tắt đơn, giữ nguyên giỏ hàng
unset($_SESSION['order_summary']);

// Quay về giỏ hàng, GIỎ HÀNG VẪN CÒN SẢN PHẨM
header('Location: ../../index.php?page=cart&payment=cancel');
exit;
