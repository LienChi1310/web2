<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('guha');
    session_start();
}

$account_id = (int)($_SESSION['account_id'] ?? 0);

if ($account_id <= 0) {
?>
    <div class="my-account__content">
        <h2 class="my-account__title h3">Lịch sử đơn hàng</h2>
        <p>Bạn cần đăng nhập để xem lịch sử đơn hàng.</p>
    </div>
<?php
    exit;
}

/*
 * LỊCH SỬ ĐƠN HÀNG
 * ----------------
 * Gồm:
 *   - Đơn đã giao hàng (order_status = 3)
 *   - Đơn đã huỷ (order_status = -1)
 * ⏸️ Chỉ hiển thị COD (1) + MoMo (2) - VNPAY hidden
 */

// Capture filter parameters
$filter_date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$filter_date_to   = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$filter_status    = isset($_GET['order_status']) ? trim($_GET['order_status']) : '';

// Build WHERE conditions
$where_conditions = [
    "account_id = {$account_id}",
    "order_status IN (3, -1)",
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

if ($filter_status !== '' && in_array($filter_status, ['3', '-1'], true)) {
    $filter_status_int = (int)$filter_status;
    $where_conditions[] = "order_status = {$filter_status_int}";
}

$where_sql = implode(' AND ', $where_conditions);

$sql_order_list = "
    SELECT *
    FROM orders
    WHERE {$where_sql}
    ORDER BY order_id DESC
";

$query_order_list = mysqli_query($mysqli, $sql_order_list);
?>
<div class="my-account__content">
    <h2 class="my-account__title h3">Lịch sử đơn hàng</h2>

    <!-- Filter Section -->
    <div style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 6px;">
        <form method="GET" action="index.php" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <input type="hidden" name="page" value="my_account">
            <input type="hidden" name="tab" value="account_history">

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
                    <option value="3" <?php echo ($filter_status === '3') ? 'selected' : ''; ?>>Đã giao hàng</option>
                    <option value="-1" <?php echo ($filter_status === '-1') ? 'selected' : ''; ?>>Đã huỷ</option>
                </select>
            </div>

            <!-- Buttons -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" style="padding: 8px 16px; background: #333; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500;">Lọc</button>
                <a href="index.php?page=my_account&tab=account_history" style="padding: 8px 16px; background: #f0f0f0; color: #333; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block;">Reset</a>
            </div>
        </form>
    </div>

    <div class="order__items">
        <?php while ($order = mysqli_fetch_array($query_order_list)) { ?>
            <a href="index.php?page=order_detail&order_code=<?php echo $order['order_code'] ?>">
                <div class="order__item">
                    <div class="order__header d-flex align-center space-between">

                        <div class="order__info">
                            <h5 class="order__code">#<?php echo $order['order_code'] ?></h5>
                            <span class="h6 d-block"><?php echo $order['order_date'] ?></span>

                            <!-- 1 dòng hiển thị phương thức thanh toán -->
                            <span class="h6 d-block">
                                Thanh toán: <?php echo format_order_type($order['order_type']); ?>
                            </span>
                        </div>

                        <div class="text-right">
                            <!-- Trạng thái đơn: Đã giao hàng / Đã huỷ -->
                            <span class="order__status h6 d-block">
                                <?php echo format_order_status($order['order_status']); ?>
                            </span>

                            <?php
                            // Chỉ hiển thị "Đã thanh toán ..." khi đơn đã giao thành công (status = 3)
                            $status = (int)$order['order_status'];
                            $type   = (int)$order['order_type'];

                            if ($status === 3) {
                                if ($type === 1) {
                                    echo '<span class="h6" style="color:#28a745;">Đã thanh toán COD</span>';
                                } else {
                                    echo '<span class="h6" style="color:#28a745;">Đã thanh toán online</span>';
                                }
                            }
                            // status = -1 (Đã huỷ): không cần thêm dòng gì nữa,
                            // vì phía trên đã có "Đã huỷ" rồi.
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

                        while ($od = mysqli_fetch_array($query_order_detail_list)) {
                        ?>
                            <div class="cart__item d-flex align-center">
                                <div class="cart__image p-relative">
                                    <img
                                        class="w-100 d-block object-fit-cover ratio-1"
                                        src="admin/modules/product/uploads/<?php echo $od['product_image'] ?>"
                                        alt="product" />
                                </div>

                                <div class="flex-1">
                                    <h3 class="cart__name h4">
                                        <?php echo $od['product_name'] ?>
                                    </h3>
                                    <span class="cart__quantity h6 d-block">
                                        x <?php echo $od['product_quantity'] ?>
                                    </span>
                                </div>

                                <div class="h5 cart__price">
                                    <?php echo number_format($od['product_price']) ?>₫
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="order__footer d-flex align-center space-between">
                        <span class="h5">
                            Thành tiền: <?php echo number_format($order['total_amount']) ?>₫
                        </span>
                    </div>
                </div>
            </a>
        <?php } ?>
    </div>
</div>
