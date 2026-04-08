<?php
$order_code = $_GET['order_code'] ?? '';

// Escape chống SQL injection
$order_code_safe = mysqli_real_escape_string($mysqli, $order_code);

/**
 * Lấy danh sách sản phẩm trong đơn
 */
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
    WHERE od.order_code = '" . $order_code_safe . "'
    ORDER BY od.order_detail_id DESC
";
$query_order_detail_list = mysqli_query($mysqli, $sql_order_detail_list);

/**
 * Lấy thông tin đơn + thông tin giao hàng
 */
$sql_order = "
    SELECT o.*,
           d.delivery_name,
           d.delivery_phone,
           d.delivery_address,
           d.delivery_note
    FROM orders o
    JOIN delivery d ON o.delivery_id = d.delivery_id
    WHERE o.order_code = '" . $order_code_safe . "'
    ORDER BY o.order_id DESC
    LIMIT 1
";
$query_order = mysqli_query($mysqli, $sql_order);
$order_info  = $query_order ? mysqli_fetch_assoc($query_order) : null;
?>
<section class="checkout pd-section">
    <div class="container">
        <div class="row">

            <!-- Cột thông tin khách hàng -->
            <div class="col" style="--w-md:8;">
                <h2 class="checkout__title h4 d-flex align-center space-between">
                    Thông tin khách hàng
                </h2>

                <div class="checkout__infomation">
                    <?php if ($order_info): ?>
                        <div class="info__item d-flex">
                            <label class="info__title" for="delivery_name">Tên khách hàng:</label>
                            <input type="text"
                                class="info__input flex-1"
                                name="delivery_name"
                                value="<?php echo $order_info['delivery_name']; ?>"
                                readonly>
                        </div>

                        <div class="info__item d-flex">
                            <label class="info__title">Số điện thoại:</label>
                            <input type="text"
                                class="info__input flex-1"
                                name="delivery_phone"
                                value="<?php echo $order_info['delivery_phone']; ?>"
                                readonly>
                        </div>

                        <div class="info__item d-flex">
                            <label class="info__title">Địa chỉ:</label>
                            <input type="text"
                                class="info__input flex-1"
                                name="delivery_address"
                                value="<?php echo $order_info['delivery_address']; ?>"
                                readonly>
                        </div>

                        <div class="info__item d-flex">
                            <label class="info__title">Ghi chú</label>
                            <input type="text"
                                class="info__input flex-1"
                                name="delivery_note"
                                value="<?php echo $order_info['delivery_note']; ?>"
                                readonly>
                        </div>

                        <div class="info__item d-flex">
                            <label class="info__title" for="order_type">Phương thức:</label>
                            <input type="text"
                                class="info__input flex-1"
                                name="order_type"
                                value="<?php echo format_order_type($order_info['order_type']); ?>"
                                readonly>
                        </div>
                    <?php else: ?>
                        <p>
                            Không tìm thấy thông tin đơn hàng
                            <strong><?php echo htmlspecialchars($order_code); ?></strong>.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cột danh sách sản phẩm + tổng tiền -->
            <div class="col" style="--w-md:4;">
                <div class="checkout__cart" style="padding-block: 0;">
                    <h2 class="h4" style="margin-bottom: 0;">Danh sách sản phẩm:</h2>

                    <div class="checkout__items">
                        <?php
                        $total = 0;
                        if ($query_order_detail_list && mysqli_num_rows($query_order_detail_list) > 0):
                            while ($cart_item = mysqli_fetch_array($query_order_detail_list)) {
                                $price_after_sale = $cart_item['product_price']
                                    - ($cart_item['product_price'] / 100 * $cart_item['product_sale']);

                                $total += $price_after_sale * $cart_item['product_quantity'];
                        ?>
                                <div class="checkout__item d-flex align-center">
                                    <div class="checkout__image p-relative">
                                        <div class="product-quantity align-center d-flex justify-center p-absolute">
                                            <span class="h6">
                                                <?php echo $cart_item['product_quantity']; ?>
                                            </span>
                                        </div>
                                        <a href="index.php?page=product_detail&product_id=<?php echo $cart_item['product_id']; ?>">
                                            <img class="w-100 d-block object-fit-cover ratio-1"
                                                src="admin/modules/product/uploads/<?php echo $cart_item['product_image']; ?>"
                                                alt="">
                                        </a>
                                    </div>

                                    <div class="checkout__name flex-1">
                                        <h3 class="h6"><?php echo $cart_item['product_name']; ?></h3>
                                        <p style="font-size: 12px; color: #666; margin: 4px 0;">
                                            <?php echo $cart_item['product_quantity']; ?> × <?php echo number_format($price_after_sale); ?>₫
                                            <?php if ((float)$cart_item['product_sale'] > 0) { ?>
                                                (giảm <?php echo (int)$cart_item['product_sale']; ?>%)
                                            <?php } ?>
                                        </p>
                                    </div>

                                    <div class="h6 checkout__price">
                                        <?php echo number_format($price_after_sale) . ' ₫'; ?>
                                    </div>
                                </div>
                            <?php
                            }
                        else:
                            ?>
                            <p>Không có sản phẩm trong đơn hàng này.</p>
                        <?php endif; ?>
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

                        <!-- Tạm tính -->
                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                            <span style="font-size: 14px;">Tạm tính</span>
                            <span style="font-size: 14px; font-weight: 500;"><?php echo number_format($original_total) ?>₫</span>
                        </div>

                        <!-- Giảm giá -->
                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                            <span style="font-size: 14px; color: #e74c3c;">Giảm giá</span>
                            <span style="font-size: 14px; font-weight: 500; color: #e74c3c;">-<?php echo number_format($discount_amount) ?>₫</span>
                        </div>

                        <!-- Phí vận chuyển -->
                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #333;">
                            <span style="font-size: 14px;">Phí vận chuyển</span>
                            <span style="font-size: 14px; font-weight: 500;">Miễn phí</span>
                        </div>

                        <!-- Tổng tiền -->
                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: center;">
                            <h4 style="font-size: 16px; font-weight: 700; margin: 0;">Tổng tiền:</h4>
                            <span style="font-size: 20px; font-weight: 700;"><?php echo number_format((float)$order_info['total_amount']); ?>₫</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hành động + trạng thái đơn -->
            <div class="w-100 d-flex align-center space-between mg-t-20">
                <div class="order__detail--action">
                    <?php if ($order_info): ?>
                        <?php if ((int)$order_info['order_status'] === 0): ?>
                            <!-- Chỉ cho hủy khi đơn đang xử lý -->
                            <a href="pages/handle/order.php?order_cancel=1&order_code=<?php echo urlencode($order_code); ?>"
                                class="btn btn__solid"
                                onclick="return confirm('Bạn có muốn hủy đơn hàng này không?')">
                                Hủy đơn hàng
                            </a>
                        <?php else: ?>
                            <!-- Các trạng thái khác chỉ hiển thị liên hệ -->
                            <a href="tel:+84878398141" class="btn btn__solid">
                                Liên hệ
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="tel:+84878398141" class="btn btn__solid">
                            Liên hệ
                        </a>
                    <?php endif; ?>

                    <a href="index.php?page=my_account&tab=account_order" class="btn anchor">
                        Trở về danh sách đơn hàng
                    </a>
                </div>

                <div class="order__detail--status">
                    Tình trạng đơn:
                    <?php
                    $status_val = $order_info ? (int)$order_info['order_status'] : 0;
                    echo format_order_status($status_val);
                    ?>
                </div>
            </div>

        </div>
    </div>
</section>
