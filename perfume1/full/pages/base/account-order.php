<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('guha');
    session_start();
}

$account_id = (int)($_SESSION['account_id'] ?? 0);

if ($account_id <= 0) {
?>
    <div class="my-account__content">
        <h2 class="my-account__title h3">Đơn hàng đang xử lý</h2>
        <p>Bạn cần đăng nhập để xem đơn hàng.</p>
    </div>
<?php
    exit;
}

/*
 * ĐƠN HÀNG ĐANG XỬ LÝ
 * -------------------
 * Lấy các đơn CHƯA HOÀN THÀNH:
 * status = 0,1,2
 * order_type = 1 (COD) hoặc 2 (MoMo) - ⏸️ VNPAY hidden
 * Sắp xếp đơn mới nhất lên trên
 */

// Capture filter parameters
$filter_date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$filter_date_to   = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$filter_status    = isset($_GET['order_status']) ? trim($_GET['order_status']) : '';

// Build WHERE conditions
$where_conditions = [
    "account_id = {$account_id}",
    "order_status IN (0,1,2)",
    "order_type IN (1, 2, 5)"  // COD + MoMo + Bank Transfer
];

if ($filter_date_from !== '') {
    $filter_date_from_safe = mysqli_real_escape_string($mysqli, $filter_date_from);
    $where_conditions[] = "DATE(order_date) >= '{$filter_date_from_safe}'";
}

if ($filter_date_to !== '') {
    $filter_date_to_safe = mysqli_real_escape_string($mysqli, $filter_date_to);
    $where_conditions[] = "DATE(order_date) <= '{$filter_date_to_safe}'";
}

if ($filter_status !== '' && in_array($filter_status, ['0', '1', '2'], true)) {
    $filter_status_int = (int)$filter_status;
    $where_conditions[] = "order_status = {$filter_status_int}";
}

$where_sql = implode(' AND ', $where_conditions);

$sql_order_list = "
    SELECT *
    FROM orders
    WHERE {$where_sql}
    ORDER BY order_date DESC, order_id DESC
";
$query_order_list = mysqli_query($mysqli, $sql_order_list);
?>
<div class="my-account__content">
    <h2 class="my-account__title h3">Đơn hàng đang xử lý</h2>

    <!-- Filter Section -->
    <div style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 6px;">
        <form method="GET" action="index.php" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <input type="hidden" name="page" value="my_account">
            <input type="hidden" name="tab" value="account_order">

            <!-- Date From -->
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <label style="font-size: 12px; font-weight: 500; color: #666;">Từ ngày:</label>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($filter_date_from); ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            </div>

            <!-- Date To -->
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <label style="font-size: 12px; font-weight: 500; color: #666;">Đến ngày:</label>
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($filter_date_to); ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            </div>

            <!-- Status Filter -->
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <label style="font-size: 12px; font-weight: 500; color: #666;">Tình trạng:</label>
                <select name="order_status" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <option value="">-- Tất cả --</option>
                    <option value="0" <?php echo ($filter_status === '0') ? 'selected' : ''; ?>>Đang xử lý</option>
                    <option value="1" <?php echo ($filter_status === '1') ? 'selected' : ''; ?>>Đang chuẩn bị hàng</option>
                    <option value="2" <?php echo ($filter_status === '2') ? 'selected' : ''; ?>>Đang giao hàng</option>
                </select>
            </div>

            <!-- Buttons -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" style="padding: 8px 16px; background: #333; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500;">Lọc</button>
                <a href="index.php?page=my_account&tab=account_order" style="padding: 8px 16px; background: #f0f0f0; color: #333; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block;">Reset</a>
            </div>
        </form>
    </div>

    <div class="order__items">
        <?php if ($query_order_list && mysqli_num_rows($query_order_list) > 0) { ?>
            <?php while ($order = mysqli_fetch_array($query_order_list)) { ?>
                <a href="index.php?page=order_detail&order_code=<?php echo $order['order_code']; ?>">
                    <div class="order__item">
                        <div class="order__header d-flex align-center space-between">

                            <div class="order__info">
                                <h5 class="order__code">#<?php echo $order['order_code']; ?></h5>
                                <span class="h6 d-block"><?php echo $order['order_date']; ?></span>

                                <span class="h6 d-block">
                                    Thanh toán: <?php echo format_order_type($order['order_type']); ?>
                                </span>
                            </div>

                            <div class="text-right">
                                <span class="order__status h6 d-block">
                                    <?php echo format_order_status($order['order_status']); ?>
                                </span>

                                <?php
                                $type = (int)$order['order_type']; // 1: COD, 2: MOMO, 4: VNPAY

                                if ($type === 1) {
                                    echo '<span class="h6" style="color:#ff9f0a;">Thanh toán khi nhận hàng</span>';
                                } elseif (in_array($type, [2, 4], true)) {
                                    echo '<span class="h6" style="color:#28a745;">Đã thanh toán online</span>';
                                }
                                ?>
                            </div>

                        </div>

                        <div class="order__container">
                            <?php
                            $sql_order_detail_list = "
                                SELECT od.order_detail_id,
                                       p.product_id,
                                       p.product_name,
                                       od.product_quantity,
                                       od.product_price,
                                       od.product_sale,
                                       p.product_image
                                FROM order_detail od
                                JOIN product p ON od.product_id = p.product_id
                                WHERE od.order_code = '" . $order['order_code'] . "'
                                ORDER BY od.order_detail_id DESC
                            ";
                            $query_order_detail_list = mysqli_query($mysqli, $sql_order_detail_list);

                            while ($order_detail = mysqli_fetch_array($query_order_detail_list)) {
                                $final_price = (float)$order_detail['product_price'] - ((float)$order_detail['product_price'] * (float)$order_detail['product_sale'] / 100);
                                if ($final_price < 0) {
                                    $final_price = 0;
                                }
                            ?>
                                <div class="cart__item d-flex align-center">
                                    <div class="cart__image p-relative">
                                        <img
                                            class="w-100 d-block object-fit-cover ratio-1"
                                            src="admin/modules/product/uploads/<?php echo htmlspecialchars($order_detail['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                                            alt="product"
                                            onerror="this.src='./assets/images/product/product-image.jpg'" />
                                    </div>

                                    <div class="flex-1">
                                        <h3 class="cart__name h4">
                                            <?php echo htmlspecialchars($order_detail['product_name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </h3>
                                        <span class="cart__quantity h6 d-block">
                                            x <?php echo (int)$order_detail['product_quantity']; ?>
                                        </span>
                                    </div>

                                    <div class="h5 cart__price">
                                        <?php echo number_format($final_price); ?>₫
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="order__footer d-flex align-center space-between">
                            <span class="h5">
                                Thành tiền: <?php echo number_format((float)$order['total_amount']); ?>₫
                            </span>
                        </div>
                    </div>
                </a>
            <?php } ?>
        <?php } else { ?>
            <div class="order__item">
                <p>Bạn hiện chưa có đơn hàng nào đang xử lý.</p>
            </div>
        <?php } ?>
    </div>
</div>
