<?php
$order_code = $_GET['order_code'];

// Lấy 1 đơn theo mã
$sql_order = "SELECT * FROM orders WHERE order_code = '$order_code' LIMIT 1";
$query_order = mysqli_query($mysqli, $sql_order);

$order = mysqli_fetch_array($query_order);
$order_date = $order['order_date'];
$order_date = date('d/m/Y', strtotime($order_date));

// Danh sách sản phẩm trong đơn
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
    WHERE od.order_code = '" . $order_code . "'
    ORDER BY od.order_detail_id DESC
";
$query_order_detail_list = mysqli_query($mysqli, $sql_order_detail_list);

// Lấy thông tin đơn + delivery
$sql_order = "
    SELECT *
    FROM orders
    JOIN delivery ON orders.delivery_id = delivery.delivery_id
    WHERE orders.order_code = '" . $order_code . "'
    ORDER BY orders.order_id DESC
";
$query_order = mysqli_query($mysqli, $sql_order);
?>

<div class="row" style="margin-bottom: 10px;">
    <div class="col d-flex" style="justify-content: space-between; align-items: flex-end;">
        <h3>Chi tiết đơn hàng</h3>
        <a href="index.php?action=order&query=order_list" class="btn btn-outline-dark btn-fw">
            <i class="mdi mdi-reply"></i>
            Quay lại
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="card-content">
                    <div class="checkout">
                        <div class="row">
                            <div class="col col-lg-7">
                                <div class="checkout__title d-flex align-center space-between">
                                    <span>Mã đơn hàng: <?php echo $order_code; ?></span>
                                    <span>Thời gian: <?php echo $order_date ?></span>
                                </div>

                                <!-- COMPLETION STATUS INDICATOR -->
                                <?php
                                $query_completion = mysqli_query($mysqli, "SELECT order_status FROM orders WHERE order_code = '$order_code' LIMIT 1");
                                if ($comp_row = mysqli_fetch_array($query_completion)) {
                                    $comp_status = get_completion_status($comp_row['order_status']);
                                ?>
                                    <div class="<?php echo $comp_status['bg_class']; ?>" style="margin: 15px 0; padding: 12px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                                        <i class="<?php echo $comp_status['icon_class']; ?>" style="font-size: 20px;"></i>
                                        <strong><?php echo $comp_status['text']; ?></strong>
                                    </div>
                                <?php } ?>

                                <div class="checkout__infomation">
                                    <?php
                                    $total = 0;
                                    while ($account = mysqli_fetch_array($query_order)) {
                                        $total = $account['total_amount'];
                                    ?>
                                        <div class="info__item d-flex">
                                            <label class="info__title">Tên khách hàng:</label>
                                            <input type="text"
                                                class="info__input flex-1"
                                                name="delivery_name"
                                                value="<?php echo $account['delivery_name'] ?>"
                                                readonly>
                                        </div>

                                        <div class="info__item d-flex">
                                            <label class="info__title">Địa chỉ:</label>
                                            <input type="text"
                                                class="info__input flex-1"
                                                name="delivery_address"
                                                value="<?php echo $account['delivery_address'] ?>"
                                                readonly>
                                        </div>

                                        <div class="info__item d-flex">
                                            <label class="info__title">Số điện thoại:</label>
                                            <input type="text"
                                                class="info__input flex-1"
                                                name="delivery_phone"
                                                value="<?php echo $account['delivery_phone'] ?>"
                                                readonly>
                                        </div>

                                        <div class="info__item d-flex">
                                            <label class="info__title">Ghi chú:</label>
                                            <input type="text"
                                                class="info__input flex-1"
                                                name="delivery_note"
                                                value="<?php echo $account['delivery_note'] ?>"
                                                readonly>
                                        </div>

                                        <div class="info__item d-flex">
                                            <label class="info__title" for="order_type">Phương thức:</label>
                                            <input type="text"
                                                class="info__input flex-1"
                                                name="order_type"
                                                value="<?php echo format_order_type($account['order_type']) ?>"
                                                readonly>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="col col-lg-5">
                                <div class="checkout__cart">
                                    <div class="checkout__items">
                                        <?php
                                        while ($cart_item = mysqli_fetch_array($query_order_detail_list)) {
                                            $price_after_sale = $cart_item['product_price'] - ($cart_item['product_price'] / 100 * $cart_item['product_sale']);
                                            $item_total = $price_after_sale * $cart_item['product_quantity'];
                                        ?>
                                            <div class="checkout__item d-flex align-center">
                                                <div class="checkout__image p-relative">
                                                    <div class="product-quantity align-center d-flex justify-center p-absolute">
                                                        <span class="quantity-number">
                                                            <?php echo $cart_item['product_quantity'] ?>
                                                        </span>
                                                    </div>
                                                    <img class="w-100 d-block object-fit-cover ratio-1"
                                                        src="modules/product/uploads/<?php echo $cart_item['product_image'] ?>"
                                                        alt="">
                                                </div>

                                                <div class="checkout__name flex-1">
                                                    <h3 class="checkout__name">
                                                        <?php echo $cart_item['product_name'] ?>
                                                    </h3>
                                                    <p style="font-size: 12px; color: #666; margin: 4px 0;">
                                                        <?php echo $cart_item['product_quantity'] ?> × <?php echo number_format($price_after_sale) ?>₫
                                                        <?php if ((float)$cart_item['product_sale'] > 0) { ?>
                                                            (giảm <?php echo (int)$cart_item['product_sale'] ?>%)
                                                        <?php } ?>
                                                    </p>
                                                </div>

                                                <div class="checkout__price">
                                                    <div style="font-weight: 600;">
                                                        <?php echo number_format($item_total) ?>₫
                                                    </div>
                                                    <p style="font-size: 11px; color: #999; margin-top: 4px;">
                                                        (Giá gốc: <?php echo number_format($cart_item['product_price']) ?>₫)
                                                    </p>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>

                                    <!-- INVOICE SECTION -->
                                    <?php
                                    // Tính tổng tiền từ sản phẩm
                                    $sql_detail_calc = "SELECT 
                                        SUM((od.product_price - (od.product_price / 100 * od.product_sale)) * od.product_quantity) as subtotal,
                                        SUM(od.product_price * od.product_quantity) as original_total,
                                        SUM((od.product_price / 100 * od.product_sale) * od.product_quantity) as discount_amount
                                        FROM order_detail od
                                        WHERE od.order_code = '{$order_code}'";
                                    $query_detail = mysqli_query($mysqli, $sql_detail_calc);
                                    $detail_row = mysqli_fetch_array($query_detail);
                                    $subtotal = (float)($detail_row['subtotal'] ?? 0);
                                    $original_total = (float)($detail_row['original_total'] ?? 0);
                                    $discount_amount = (float)($detail_row['discount_amount'] ?? 0);
                                    ?>
                                    <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                                        <h4 style="margin-bottom: 20px; font-weight: 600; font-size: 16px;">Chi tiết hóa đơn</h4>

                                        <!-- Invoice Item -->
                                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                                            <span style="font-size: 14px;">Tạm tính</span>
                                            <span style="font-size: 14px; font-weight: 500;"><?php echo number_format($original_total) ?>₫</span>
                                        </div>

                                        <!-- Discount Item -->
                                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                                            <span style="font-size: 14px; color: #e74c3c;">Giảm giá</span>
                                            <span style="font-size: 14px; font-weight: 500; color: #e74c3c;">-<?php echo number_format($discount_amount) ?>₫</span>
                                        </div>

                                        <!-- Shipping Fee -->
                                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #333;">
                                            <span style="font-size: 14px;">Phí vận chuyển</span>
                                            <span style="font-size: 14px; font-weight: 500;">Miễn phí</span>
                                        </div>

                                        <!-- Total -->
                                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: center;">
                                            <h4 style="font-size: 16px; font-weight: 700; margin: 0;">Tổng tiền:</h4>
                                            <span style="font-size: 20px; font-weight: 700;"><?php echo number_format((float)$total) ?>₫</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DELIVERY TIMELINE SECTION -->
                        <?php
                        $query_status = mysqli_query(
                            $mysqli,
                            "SELECT * FROM orders WHERE order_code = '$order_code' LIMIT 1"
                        );
                        if ($status_row = mysqli_fetch_array($query_status)) {
                            $current_status = $status_row['order_status'];
                            $order_date = $status_row['order_date'];
                            $estimated_delivery = get_estimated_delivery_date($order_date);
                        ?>
                            <div class="order__timeline" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                                <h5 style="margin-bottom: 20px; font-weight: 600;">Quá trình xử lý đơn hàng</h5>

                                <div style="display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; justify-content: space-between; align-items: flex-start;">
                                    <!-- Timeline Step 1 -->
                                    <div style="flex: 1; min-width: 100px; text-align: center;">
                                        <div style="width: 50px; height: 50px; margin: 0 auto 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; background: <?php echo ($current_status >= 0 && $current_status != -1) ? '#2bcc71' : '#ddd'; ?>;">
                                            <i class="<?php echo ($current_status >= 0 && $current_status != -1) ? 'mdi mdi-check-circle' : 'mdi mdi-radio-button-unchecked'; ?>"></i>
                                        </div>
                                        <p style="font-size: 11px; margin: 0; color: #666;">Chưa xác nhận</p>
                                    </div>

                                    <!-- Timeline Line 1 -->
                                    <div style="flex: auto; display: flex; align-items: flex-start; padding-top: 14px; min-width: 20px;">
                                        <div style="height: 2px; background: <?php echo ($current_status >= 1 && $current_status != -1) ? '#2bcc71' : '#ddd'; ?>; width: 100%;"></div>
                                    </div>

                                    <!-- Timeline Step 2 -->
                                    <div style="flex: 1; min-width: 100px; text-align: center;">
                                        <div style="width: 50px; height: 50px; margin: 0 auto 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; background: <?php echo ($current_status >= 1 && $current_status != -1) ? '#2bcc71' : '#ddd'; ?>;">
                                            <i class="<?php echo ($current_status >= 1 && $current_status != -1) ? 'mdi mdi-check-circle' : 'mdi mdi-radio-button-unchecked'; ?>"></i>
                                        </div>
                                        <p style="font-size: 11px; margin: 0; color: #666;">Chờ chuẩn bị</p>
                                    </div>

                                    <!-- Timeline Line 2 -->
                                    <div style="flex: auto; display: flex; align-items: flex-start; padding-top: 14px; min-width: 20px;">
                                        <div style="height: 2px; background: <?php echo ($current_status >= 2 && $current_status != -1) ? '#2bcc71' : '#ddd'; ?>; width: 100%;"></div>
                                    </div>

                                    <!-- Timeline Step 3 -->
                                    <div style="flex: 1; min-width: 100px; text-align: center;">
                                        <div style="width: 50px; height: 50px; margin: 0 auto 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; background: <?php echo ($current_status >= 2 && $current_status != -1) ? '#2bcc71' : '#ddd'; ?>;">
                                            <i class="<?php echo ($current_status >= 2 && $current_status != -1) ? 'mdi mdi-check-circle' : 'mdi mdi-radio-button-unchecked'; ?>"></i>
                                        </div>
                                        <p style="font-size: 11px; margin: 0; color: #666;">Đang giao hàng</p>
                                    </div>

                                    <!-- Timeline Line 3 -->
                                    <div style="flex: auto; display: flex; align-items: flex-start; padding-top: 14px; min-width: 20px;">
                                        <div style="height: 2px; background: <?php echo ($current_status >= 3 && $current_status != -1) ? '#2bcc71' : '#ddd'; ?>; width: 100%;"></div>
                                    </div>

                                    <!-- Timeline Step 4 -->
                                    <div style="flex: 1; min-width: 100px; text-align: center;">
                                        <div style="width: 50px; height: 50px; margin: 0 auto 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; background: <?php echo ($current_status >= 3 && $current_status != -1) ? '#2bcc71' : '#ddd'; ?>;">
                                            <i class="<?php echo ($current_status >= 3 && $current_status != -1) ? 'mdi mdi-check-circle' : 'mdi mdi-radio-button-unchecked'; ?>"></i>
                                        </div>
                                        <p style="font-size: 11px; margin: 0; color: #666;">Đã giao hàng</p>
                                    </div>
                                </div>

                                <!-- Delivery Dates -->
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px;">
                                    <div class="color-bg-blue" style="padding: 15px; border-radius: 6px;">
                                        <p style="margin: 0; font-size: 12px; font-weight: 500;">Dự kiến giao</p>
                                        <p style="margin: 8px 0 0 0; font-size: 16px; font-weight: 600;"><?php echo date('d/m/Y', strtotime($estimated_delivery)); ?></p>
                                    </div>
                                    <div class="<?php echo ($current_status == 3) ? 'color-bg-green' : 'color-bg-orange'; ?>" style="padding: 15px; border-radius: 6px;">
                                        <p style="margin: 0; font-size: 12px; font-weight: 500;">Thực tế giao</p>
                                        <p style="margin: 8px 0 0 0; font-size: 16px; font-weight: 600;">
                                            <?php echo ($current_status == 3) ? 'Đã giao hàng' : 'Đang xử lý'; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="d-flex algin-center space-between">
                            <?php
                            $query_status = mysqli_query(
                                $mysqli,
                                "SELECT * FROM orders WHERE order_code = '$order_code' LIMIT 1"
                            );
                            while ($status = mysqli_fetch_array($query_status)) {
                            ?>
                                <div class="order__detail--action" style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    <?php
                                    // Nếu đơn còn ở trạng thái <= 2 thì cho phép Duyệt đơn
                                    if ($status['order_status'] <= 2) {
                                    ?>
                                        <a href="modules/order/xuly.php?confirm=1&order_code=<?php echo $order_code ?>"
                                            class="btn btn-outline-dark btn-fw">
                                            Duyệt đơn
                                        </a>
                                    <?php
                                    }

                                    // Nếu đơn chưa bị hủy (status != -1) và chưa hoàn thành (status < 3) thì cho phép Hủy đơn
                                    if ($status['order_status'] != -1 && $status['order_status'] < 3) {
                                    ?>
                                        <a href="javascript:void(0);"
                                            onclick="confirmCancelOrder(<?php echo $order_code ?>)"
                                            class="btn btn-outline-danger btn-fw">
                                            Hủy đơn
                                        </a>
                                    <?php
                                    }

                                    // Nếu đơn đã bị hủy thì cho phép xóa
                                    if ($status['order_status'] == -1) {
                                    ?>
                                        <a href="javascript:void(0);"
                                            onclick="confirmDeleteOrder(<?php echo $order_code ?>)"
                                            class="btn btn-outline-danger btn-fw">
                                            Xóa đơn
                                        </a>
                                    <?php
                                    }
                                    ?>
                                </div>

                                <div class="order_status">
                                    Tình trạng đơn:
                                    <span class="col-span">
                                        <?php echo format_order_status($status['order_status']); ?>
                                    </span>
                                </div>
                            <?php
                            }
                            ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showSuccessToast() {
        toast({
            title: "Success",
            message: "Cập nhật thành công",
            type: "success",
            duration: 0,
        });
    }

    function showInfoToast() {
        toast({
            title: "Info",
            message: "Tính năng tạm thời đang phát triển",
            type: "info",
            duration: 0,
        });
    }

    function showErrorToast() {
        toast({
            title: "Error",
            message: "Không thể thực thi yêu cầu",
            type: "error",
            duration: 0,
        });
    }

    function confirmCancelOrder(orderCode) {
        if (confirm('Bạn có chắc muốn hủy đơn hàng này không? Hành động này sẽ hoàn lại hàng tồn kho.')) {
            window.location.href = 'modules/order/xuly.php?cancel=1&data=' + JSON.stringify([orderCode]);
        }
    }

    function confirmDeleteOrder(orderCode) {
        if (confirm('Bạn có chắc muốn xóa đơn hàng này không? Hành động này không thể hoàn tác.')) {
            window.location.href = 'modules/order/xuly.php?delete_order=1&order_code=' + orderCode;
        }
    }
</script>

<?php
if (isset($_GET['message']) && $_GET['message'] == 'success') {
    echo '<script>showSuccessToast();</script>';
} elseif (isset($_GET['message']) && $_GET['message'] == 'info') {
    echo '<script>showInfoToast();</script>';
}
?>

<script>
    // Làm sạch URL, bỏ query message nếu có
    window.history.pushState(
        null,
        "",
        "index.php?action=order&query=order_detail_online&order_code=" + "<?php echo $order_code ?>"
    );
</script>
