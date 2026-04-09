<?php
$order_code = $_GET['order_code'];

$sql_order = "SELECT * FROM orders WHERE order_code = '$order_code' LIMIT 1";
$query_order = mysqli_query($mysqli, $sql_order);

$order = mysqli_fetch_array($query_order);
$order_date = $order['order_date'];
$order_date = date('d/m/Y', strtotime($order_date));

$sql_order_detail_list = "SELECT od.order_detail_id, p.product_id, p.product_name, od.product_quantity, od.product_price, od.product_sale, p.product_image FROM order_detail od JOIN product p ON od.product_id = p.product_id WHERE od.order_code = '" . $order_code . "' ORDER BY od.order_detail_id DESC";
$query_order_detail_list = mysqli_query($mysqli, $sql_order_detail_list);

//Lay ra thong tin don hang
$sql_order = "SELECT * FROM orders JOIN delivery ON orders.delivery_id = delivery.delivery_id WHERE orders.order_code = '" . $order_code . "' ORDER BY orders.order_id DESC";
$query_order = mysqli_query($mysqli, $sql_order);
?>

<div class="row" style="margin-bottom: 10px;">
    <div class="col d-flex" style="justify-content: space-between; align-items: flex-end;">
        <h3>
            Chi tiết đơn hàng
        </h3>
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
                                <div class="checkout__title d-flex align-center space-between"><span>Mã đơn hàng: <?php echo $order_code; ?></span> <span>Thời gian: <?php echo $order_date ?></span></div>

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
                                    $total;
                                    while ($account = mysqli_fetch_array($query_order)) {
                                        $total = $account['total_amount'];
                                    ?>
                                        <div class="info__item d-flex">
                                            <label class="info__title" for="">Tên khách hàng:</label>
                                            <input type="text" class="info__input flex-1" name="delivery_name" value="<?php echo $account['delivery_name'] ?>" readonly></input>
                                        </div>
                                        <div class="info__item d-flex">
                                            <label class="info__title" for="">Địa chỉ:</label>
                                            <input type="text" class="info__input flex-1" name="delivery_address" value="<?php echo $account['delivery_address'] ?>" readonly></input>
                                        </div>
                                        <div class="info__item d-flex">
                                            <label class="info__title" for="">Số điện thoại:</label>
                                            <input type="text" class="info__input flex-1" name="delivery_phone" value="<?php echo $account['delivery_phone'] ?>" readonly></input>
                                        </div>
                                        <div class="info__item d-flex">
                                            <label class="info__title" for="">Ghi chú:</label>
                                            <input type="text" class="info__input flex-1" name="delivery_note" value="<?php echo $account['delivery_note'] ?>" readonly></input>
                                        </div>
                                        <div class="info__item d-flex">
                                            <label for="" class="info__title" for="order_type">Phương thức:</label>
                                            <input type="text" class="info__input flex-1" name="order_type" value="<?php echo format_order_type($account['order_type']) ?>"></input>
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
                                        ?>
                                            <div class="checkout__item d-flex align-center">
                                                <div class="checkout__image p-relative">
                                                    <div class="product-quantity align-center d-flex justify-center p-absolute"><span class="quantity-number"><?php echo $cart_item['product_quantity'] ?></span></div>
                                                    <img class="w-100 d-block object-fit-cover ratio-1" src="modules/product/uploads/<?php echo $cart_item['product_image'] ?>" alt="" onerror="this.src='images/placeholder-image.webp'">
                                                </div>
                                                <div class="checkout__name flex-1">
                                                    <h3 class="checkout__name"><?php echo $cart_item['product_name'] ?></h3>
                                                </div>
                                                <div class="checkout__price"><?php echo (number_format($cart_item['product_price'] - ($cart_item['product_price'] / 100 * $cart_item['product_sale']))) . ' ₫' ?></div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <table class="w-100 mg-t-20">
                                        <tr class="table-row">
                                            <td class="h6 table-col">Giảm giá</td>
                                            <td class="h6 table-col text-right"> 0₫</td>
                                        </tr>
                                        <tr class="table-row">
                                            <td class="h6 table-col">Phí vận chuyển</td>
                                            <td class="h6 table-col text-right">Miễn phí</td>
                                        </tr>
                                    </table>
                                    <div class="checkout__bottom d-flex align-center space-between">
                                        <h4 class="checkout__total">Tổng tiền:</h4>
                                        <span class="checkout__total"><?php echo number_format((float) $total) . '₫' ?></span>
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
                            $order_date_for_calc = $status_row['order_date'];
                            $estimated_delivery = get_estimated_delivery_date($order_date_for_calc);
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

                        <div style="margin-top: 20px;">
                            <a href="modules/order/indonhang.php?order_code=<?php echo $order_code ?>" target="_blank" class="btn btn-outline-dark btn-fw">In Hóa Đơn</a>
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
</script>

<?php
if (isset($_GET['message']) && $_GET['message'] == 'success') {
    echo '<script>';
    echo 'showSuccessToast();';
    echo '</script>';
}
?>

<script>
    window.history.pushState(null, "", "index.php?action=order&query=order_detail&order_code=" + "<?php echo $order_code ?>");
</script>
