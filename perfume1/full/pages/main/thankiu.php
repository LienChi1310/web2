<?php
// Phase 3 – Thank You (GIẢ LẬP THANH TOÁN)

// Đồng bộ session với toàn site (guha)
if (session_status() === PHP_SESSION_NONE) {
    session_name('guha');
    session_start();
}

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

// Helper format tiền
if (!function_exists('vnd')) {
    function vnd($n)
    {
        return number_format((float)$n, 0, ',', '.') . ' ₫';
    }
}

if (!function_exists('e')) {
    function e($v)
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('resolve_thankiu_image')) {
    function resolve_thankiu_image($raw)
    {
        $raw = trim((string)$raw);
        if ($raw === '') {
            return './assets/images/product/product-image.jpg';
        }

        $raw = str_replace('\\', '/', $raw);

        if (preg_match('~^https?://~i', $raw)) {
            return $raw;
        }

        $file = basename($raw);
        $candidates = [
            'admin/modules/product/uploads/' . $file,
            'assets/images/products/' . $file,
            ltrim($raw, '/'),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return 'admin/modules/product/uploads/' . $file;
    }
}

// Mặc định: thất bại
$status = 0;

// Lấy thông tin đơn từ session
$orderSummary = $_SESSION['order_summary'] ?? null;

if ($orderSummary && is_array($orderSummary)) {
    $status          = 1;
    $order_code      = (int)($orderSummary['order_code'] ?? 0);
    $delivery_name   = (string)($orderSummary['delivery_name'] ?? '');
    $delivery_phone  = (string)($orderSummary['delivery_phone'] ?? '');
    $delivery_addr   = (string)($orderSummary['delivery_address'] ?? '');
    $delivery_note   = (string)($orderSummary['delivery_note'] ?? '');
    $payment_method  = (string)($orderSummary['payment_method'] ?? '');
    $order_type      = (int)($orderSummary['order_type'] ?? 0);
    $is_paid         = (int)($orderSummary['is_paid'] ?? 0);
    $total_amount    = (float)($orderSummary['total_amount'] ?? 0);
    $items           = (array)($orderSummary['items'] ?? []);
    $bank_name       = (string)($orderSummary['bank_name'] ?? '');
    $bank_account_number = (string)($orderSummary['bank_account_number'] ?? '');
    $bank_account_holder = (string)($orderSummary['bank_account_holder'] ?? '');
} else {
    $order_code = 0;
    $delivery_name = '';
    $delivery_phone = '';
    $delivery_addr = '';
    $delivery_note = '';
    $payment_method = '';
    $order_type = 0;
    $is_paid = 0;
    $total_amount = 0;
    $items = [];
    $bank_name = '';
    $bank_account_number = '';
    $bank_account_holder = '';
}

// Chuẩn bị mã hiển thị
$display_code = $order_code > 0 ? 'ORD' . $order_code : '';
?>

<?php if ($status == 1): ?>
    <section class="thankiu pd-section">
        <div class="container">
            <div class="thankiu__box">
                <div class="text-center">
                    <div class="thankiu_image">
                        <img src="assets/images/icon/icon-success.gif" alt="success">
                    </div>
                    <h1 class="thankiu__heading h2">Đặt hàng thành công</h1>
                    <span class="thankiu__heading2 h3">Cảm ơn quý khách đã mua hàng tại Guha Perfume</span>
                </div>

                <p class="thankiu__description text-center" style="margin-top: 16px;">
                    Đơn hàng của quý khách đã được tiếp nhận và đang trong thời gian xử lý.
                    Chúng tôi sẽ thông báo đến quý khách ngay khi hàng chuẩn bị được giao.
                </p>

                <div class="row" style="margin-top: 24px;">
                    <div class="col" style="--w: 12; --w-md: 6;">
                        <div style="padding:16px;border:1px solid #e5e7eb;border-radius:10px;height:100%;">
                            <h3 class="h4" style="margin-bottom:12px;">Thông tin đơn hàng</h3>

                            <?php if ($display_code !== ''): ?>
                                <p><strong>Mã đơn hàng:</strong> <?php echo e($display_code); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($payment_method)): ?>
                                <p><strong>Phương thức thanh toán:</strong> <?php echo e($payment_method); ?></p>
                            <?php endif; ?>

                            <p>
                                <strong>Trạng thái thanh toán:</strong>
                                <?php echo $is_paid ? 'Đã thanh toán' : 'Chưa thanh toán'; ?>
                            </p>

                            <?php if ($total_amount > 0): ?>
                                <p><strong>Tổng tiền:</strong> <?php echo vnd($total_amount); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col" style="--w: 12; --w-md: 6;">
                        <div style="padding:16px;border:1px solid #e5e7eb;border-radius:10px;height:100%;">
                            <h3 class="h4" style="margin-bottom:12px;">Thông tin giao hàng</h3>

                            <?php if ($delivery_name !== ''): ?>
                                <p><strong>Người nhận:</strong> <?php echo e($delivery_name); ?></p>
                            <?php endif; ?>

                            <?php if ($delivery_phone !== ''): ?>
                                <p><strong>Số điện thoại:</strong> <?php echo e($delivery_phone); ?></p>
                            <?php endif; ?>

                            <?php if ($delivery_addr !== ''): ?>
                                <p><strong>Địa chỉ giao hàng:</strong> <?php echo e($delivery_addr); ?></p>
                            <?php endif; ?>

                            <?php if ($delivery_note !== ''): ?>
                                <p><strong>Ghi chú:</strong> <?php echo e($delivery_note); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Bank Transfer Info (if Type 5) -->
                <?php if ($order_type == 5 && !empty($bank_name)): ?>
                    <div style="margin-top: 24px; padding:16px; border:1px solid #e5e7eb; border-radius:10px; background:#f0f8ff;">
                        <h3 class="h4" style="margin-bottom:12px; color:#0066cc;">✓ Thông tin chuyển khoản đã nhận</h3>
                        <p><strong>Ngân Hàng:</strong> <?php echo e($bank_name); ?></p>
                        <p><strong>Số Tài Khoản:</strong> <span style="font-family: monospace; font-weight: 600;"><?php echo e($bank_account_number); ?></span></p>
                        <p><strong>Tên Chủ Tài Khoản:</strong> <?php echo e($bank_account_holder); ?></p>
                        <p style="margin-top:12px; font-size:13px; color:#28a745; font-weight: 600;">✓ Thanh toán thành công</p>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 24px; padding:16px; border:1px solid #e5e7eb; border-radius:10px;">
                    <h3 class="h4" style="margin-bottom:16px;">Tóm tắt sản phẩm</h3>

                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                            <?php
                            $product_id = (int)($item['product_id'] ?? 0);
                            $product_name = (string)($item['product_name'] ?? '');
                            $product_qty = (int)($item['product_quantity'] ?? 0);
                            $product_price = (float)($item['product_price'] ?? 0);
                            $product_sale = (float)($item['product_sale'] ?? 0);
                            $product_image = resolve_thankiu_image($item['product_image'] ?? '');

                            $final_price = $product_price - ($product_price * $product_sale / 100);
                            if ($final_price < 0) {
                                $final_price = 0;
                            }
                            $line_total = $final_price * $product_qty;
                            ?>
                            <div class="d-flex align-center" style="padding:12px 0;border-bottom:1px solid #f1f1f1;gap:16px;">
                                <div style="width:80px;min-width:80px;">
                                    <a href="index.php?page=product_detail&product_id=<?php echo $product_id; ?>">
                                        <img src="<?php echo e($product_image); ?>"
                                            alt="<?php echo e($product_name); ?>"
                                            style="width:80px;height:80px;object-fit:cover;border-radius:8px;"
                                            onerror="this.src='./assets/images/product/product-image.jpg'">
                                    </a>
                                </div>

                                <div class="flex-1">
                                    <a href="index.php?page=product_detail&product_id=<?php echo $product_id; ?>" style="text-decoration:none;color:inherit;">
                                        <h4 class="h5" style="margin-bottom:6px;"><?php echo e($product_name); ?></h4>
                                    </a>
                                    <p>Số lượng: <?php echo $product_qty; ?></p>
                                    <p>Đơn giá: <?php echo vnd($final_price); ?></p>
                                </div>

                                <div style="min-width:140px;text-align:right;">
                                    <strong><?php echo vnd($line_total); ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Không có dữ liệu sản phẩm trong đơn hàng.</p>
                    <?php endif; ?>
                </div>

                <div class="thankiu_link text-center" style="margin-top: 24px;">
                    <a href="index.php" class="btn btn__outline">Trang chủ</a>

                    <?php if ($order_code > 0): ?>
                        <a href="index.php?page=order_detail&order_code=<?php echo urlencode((string)$order_code); ?>" class="btn btn__outline">
                            Xem chi tiết đơn hàng
                        </a>
                    <?php else: ?>
                        <a href="index.php?page=my_account&tab=account_order" class="btn btn__outline">
                            Xem chi tiết đơn hàng
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php
    // Xóa chỉ những sản phẩm đã thanh toán khỏi giỏ hàng
    if (!empty($_SESSION['order_summary']['items']) && !empty($_SESSION['cart'])) {
        $paid_items = $_SESSION['order_summary']['items'];
        $paid_product_ids = array_map(function ($item) {
            return (int)$item['product_id'];
        }, $paid_items);

        // Lọc lại giỏ hàng, chỉ giữ những sản phẩm KHÔNG được thanh toán
        $remaining_items = [];
        foreach ($_SESSION['cart'] as $cart_item) {
            if (!in_array((int)$cart_item['product_id'], $paid_product_ids)) {
                $remaining_items[] = $cart_item;
            }
        }

        // Cập nhật hoặc xóa giỏ hàng
        if (!empty($remaining_items)) {
            $_SESSION['cart'] = $remaining_items; // Còn sản phẩm chưa thanh toán
        } else {
            unset($_SESSION['cart']); // Xóa giỏ hàng nếu toàn bộ đã thanh toán
        }
    } else {
        unset($_SESSION['cart']); // Xóa nếu không có thông tin order
    }

    unset($_SESSION['order_summary']);
    ?>

<?php else: ?>
    <section class="thankiu pd-section">
        <div class="container">
            <div class="thankiu__box text-center">
                <div class="thankiu_image">
                    <img src="assets/images/icon/icon-error.gif" alt="error">
                </div>
                <h1 class="thankiu__heading heading--wanning h2">Giao dịch thất bại</h1>
                <span class="thankiu__heading2 h3">Thanh toán không thành công hoặc không tìm thấy đơn hàng</span>
                <p class="thankiu__description">
                    Quý khách vui lòng thực hiện đặt hàng lại hoặc chọn phương thức khác.
                    Các sản phẩm hiện vẫn còn trong giỏ hàng (nếu có).
                </p>
                <div class="thankiu_link">
                    <a href="index.php" class="btn btn__outline">Trang chủ</a>
                    <a href="index.php?page=cart" class="btn btn__outline">Xem giỏ hàng</a>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
