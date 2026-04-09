<?php
// Bật session nếu chưa có (đồng bộ tên session 'guha')
if (session_status() === PHP_SESSION_NONE) {
    session_name('guha');
    session_start();
}

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// unset($_SESSION['cart']);

/* ===== DEBUG: hiển thị lỗi khi checkout (nếu có) ===== */
if (!empty($_SESSION['checkout_errors'])) {
    echo '<div style="background:#ffecec;color:#c00;padding:10px;margin:10px 0;border:1px solid #c00;">';
    echo '<strong>Lỗi khi đặt hàng:</strong><br>';
    foreach ($_SESSION['checkout_errors'] as $msg) {
        echo '- ' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '<br>';
    }
    echo '</div>';
    unset($_SESSION['checkout_errors']);
}

/* ===== Helper ảnh ===== */
if (!function_exists('img_url_phase1')) {
    function img_url_phase1($file)
    {
        $file = trim((string)$file);
        if ($file === '') {
            return './assets/images/no-image.png';
        }
        return 'admin/modules/product/uploads/' . $file;
    }
}
?>

<!-- Style tinh chỉnh layout 2 cột: danh sách sản phẩm + invoice summary -->
<style>
    /* Layout chính */
    .cart__wrapper {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 24px;
        align-items: start;
    }

    /* Danh sách sản phẩm */
    .cart__list-section {
        grid-column: 1;
    }

    /* Summary sidebar - sticky */
    .cart__summary-section {
        grid-column: 2;
        position: sticky;
        top: 20px;
        background: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        height: fit-content;
    }

    /* Checkbox styling */
    .cart__checkbox-wrapper {
        display: flex;
        align-items: center;
        margin-right: 12px;
    }

    .cart__checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #333;
    }

    /* Điều chỉnh layout item khi có checkbox */
    .cart__item.with-checkbox {
        grid-template-columns: 50px 1fr 1.5fr 1fr 1fr;
        gap: 12px;
    }

    .cart__item.with-checkbox .cart__image {
        grid-column: 2;
    }

    .cart__item.with-checkbox .cart__title {
        grid-column: 3;
    }

    .cart__item.with-checkbox .cart__quantity {
        grid-column: 4;
        text-align: center;
    }

    .cart__item.with-checkbox .cart__total {
        grid-column: 5;
        text-align: right;
    }

    /* Text ellipsis cho tên sản phẩm */
    .cart__name {
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        word-break: break-word;
        margin: 0 0 4px 0;
        line-height: 1.4;
    }

    .cart__color {
        font-size: 12px;
        color: #999;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        word-break: break-word;
        margin: 0;
        line-height: 1.4;
    }

    /* Summary styles */
    .cart__summary-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 12px;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #ddd;
    }

    .cart__summary-row.total-row {
        padding-bottom: 15px;
        border-bottom: 2px solid #333;
        font-size: 18px;
        font-weight: 700;
    }

    .cart__summary-label {
        font-size: 14px;
        color: #666;
        margin: 0;
    }

    .cart__summary-value {
        font-size: 14px;
        font-weight: 600;
        margin: 0;
        text-align: right;
    }

    .cart__summary-row.total-row .cart__summary-label {
        color: #333;
        font-size: 16px;
    }

    .cart__summary-row.total-row .cart__summary-value {
        font-size: 20px;
        color: #d32f2f;
    }

    .cart__summary-row.sale {
        color: #e74c3c;
    }

    .cart__summary-context {
        font-size: 12px;
        color: #999;
        margin: 8px 0;
    }

    /* Nút action */
    .cart__action-buttons {
        margin-top: 15px;
    }

    .cart__action-buttons .btn {
        width: 100%;
        display: block;
        margin-top: 8px;
    }

    .cart__action-buttons .btn:first-child {
        margin-top: 0;
    }

    /* Responsive: mobile stacks vertically */
    @media (max-width: 992px) {
        .cart__wrapper {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .cart__summary-section {
            grid-column: auto;
            position: static;
            top: auto;
        }

        .cart__item.with-checkbox {
            grid-template-columns: 30px 90px 1fr auto auto;
            gap: 6px;
        }
    }

    @media (max-width: 768px) {
        .cart__item.with-checkbox {
            grid-template-columns: 30px 80px 1fr auto auto;
            gap: 6px;
        }

        .cart__summary-section {
            padding: 16px;
        }
    }

    @media (max-width: 576px) {
        .cart__item.with-checkbox {
            grid-template-columns: 20px 60px 1fr;
            gap: 6px;
        }

        .cart__item.with-checkbox .cart__quantity,
        .cart__item.with-checkbox .cart__total {
            display: none;
        }

        .cart__checkbox {
            width: 18px;
            height: 18px;
        }
    }
</style>

<div id="toast_message"></div>
<section class="cart pd-section">
    <div class="container">
        <div class="cart__header d-flex space-between align-center">
            <h1 class="h2">Giỏ hàng của bạn</h1>
            <a class="h4" href="index.php?page=products">Quay lại cửa hàng</a>
        </div>
        <?php
        if (!empty($_SESSION['cart'])) {
            $total = 0;
        ?>
            <div class="cart__wrapper">
                <!-- PHẦN DANH SÁCH SẢN PHẨM -->
                <div class="cart__list-section">
                    <div class="cart__container">
                        <div class="cart__heading">
                            <div class="cart__item with-checkbox d-grid">
                                <div class="cart__checkbox-wrapper">
                                    <input type="checkbox" id="select-all-products" class="cart__checkbox" title="Chọn tất cả" />
                                </div>
                                <div class="cart__image">
                                    <span class="h6">TÊN SẢN PHẨM</span>
                                </div>
                                <div class="cart__title"></div>
                                <div class="cart__quantity">
                                    <span class="d-none lg-initital">Số lượng</span>
                                </div>
                                <div class="cart__total">
                                    <span class="h6">GIÁ TIỀN</span>
                                </div>
                            </div>
                        </div>
                        <div class="cart__content">
                            <?php
                            $validate = true;
                            // Đảo ngược thứ tự sản phẩm (thêm sau cùng hiển thị đầu tiên)
                            $cartItems = array_reverse($_SESSION['cart'], true);
                            foreach ($cartItems as $cart_item) {
                                $pid = (int)$cart_item['product_id'];
                                $qProduct = mysqli_query($mysqli, "SELECT * FROM product WHERE product_id = '{$pid}' LIMIT 1");
                                $product  = mysqli_fetch_array($qProduct);

                                $priceOld = (float)$cart_item['product_price'];
                                $sale     = (float)$cart_item['product_sale'];
                                $priceNew = $priceOld - ($priceOld * $sale / 100);
                                $qtyLine  = (int)$cart_item['product_quantity'];

                                if ((int)$product['product_quantity'] >= $qtyLine) {
                                    $total += $priceNew * $qtyLine;
                            ?>
                                    <div class="cart__item with-checkbox d-grid" data-product-id="<?php echo $pid; ?>" data-price="<?php echo $priceNew; ?>" data-price-old="<?php echo $priceOld; ?>" data-qty="<?php echo $qtyLine; ?>" data-sale="<?php echo $sale; ?>">
                                        <div class="cart__checkbox-wrapper">
                                            <input type="checkbox" class="cart__checkbox product-checkbox" data-product-id="<?php echo $pid; ?>" data-price="<?php echo $priceNew; ?>" data-price-old="<?php echo $priceOld; ?>" data-qty="<?php echo $qtyLine; ?>" data-sale="<?php echo $sale; ?>" />
                                        </div>
                                        <div class="cart__image">
                                            <a href="index.php?page=product_detail&product_id=<?php echo $pid; ?>">
                                                <img class="w-100"
                                                    src="<?php echo htmlspecialchars(img_url_phase1($cart_item['product_image'] ?? '')); ?>"
                                                    alt="product" />
                                            </a>
                                        </div>
                                        <div class="cart__title">
                                            <h3 class="cart__name h4"><?php echo htmlspecialchars($cart_item['product_name']); ?></h3>
                                            <span class="cart__color">
                                                Dung tích: <strong><?php echo htmlspecialchars($cart_item['capacity_name'] ?? '—'); ?></strong>
                                                <?php if ((float)$sale > 0) { ?>
                                                    &nbsp;&nbsp;|&nbsp;&nbsp;
                                                    <span style="color: #d32f2f;">-<?php echo (int)$sale; ?>%</span>
                                                <?php } ?>
                                            </span>
                                        </div>
                                        <div class="cart__quantity">
                                            <div class="cart__quantity--container d-flex align-center">
                                                <div class="select__number p-relative">
                                                    <a href="pages/handle/addtocart.php?div=<?php echo $pid; ?>"
                                                        class="select__number--minus cursor-pointer p-absolute d-flex align-center justify-center">
                                                        <img src="./assets/images/icon/minus.svg" alt="minus" />
                                                    </a>
                                                    <!-- NHẬP SỐ LƯỢNG BẰNG TAY -->
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        value="<?php echo $qtyLine; ?>"
                                                        class="select__number--value heading-6 w-100 h-100"
                                                        data-product-id="<?php echo $pid; ?>" />
                                                    <a href="pages/handle/addtocart.php?sum=<?php echo $pid; ?>"
                                                        class="select__number--plus cursor-pointer p-absolute d-flex align-center justify-center">
                                                        <img src="./assets/images/icon/plus.svg" alt="plus" />
                                                    </a>
                                                </div>
                                                <div class="cart__delete cursor-pointer d-flex align-center justify-center">
                                                    <a href="pages/handle/addtocart.php?delete=<?php echo $pid; ?>">
                                                        <img src="./assets/images/icon/delete.svg" alt="delete" />
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="cart__total h4">
                                            <?php echo number_format($priceNew) . ' ₫'; ?>
                                        </div>
                                    </div>
                                <?php
                                } else {
                                    $validate = false;
                                ?>
                                    <div class="cart__item with-checkbox d-grid" data-product-id="<?php echo $pid; ?>" data-price="0" data-price-old="0" data-qty="0" data-sale="0" style="opacity: 0.5;">
                                        <div class="cart__checkbox-wrapper">
                                            <input type="checkbox" class="cart__checkbox product-checkbox" data-product-id="<?php echo $pid; ?>" disabled style="cursor: not-allowed;" />
                                        </div>
                                        <div class="cart__image opacity-50">
                                            <a href="index.php?page=product_detail&product_id=<?php echo $pid; ?>">
                                                <img class="w-100"
                                                    src="<?php echo htmlspecialchars(img_url_phase1($cart_item['product_image'] ?? '')); ?>"
                                                    alt="product" />
                                            </a>
                                        </div>
                                        <div class="cart__title">
                                            <h3 class="cart__name h4 opacity-50"><?php echo htmlspecialchars($cart_item['product_name']); ?></h3>
                                            <span class="cart__color color-wanning">
                                                Còn lại: <span class="product__quantity"><?php echo (int)$product['product_quantity']; ?></span> sản phẩm
                                            </span>
                                        </div>
                                        <div class="cart__quantity">
                                            <div class="cart__quantity--container d-flex align-center">
                                                <div class="select__number p-relative">
                                                    <a href="pages/handle/addtocart.php?div=<?php echo $pid; ?>"
                                                        class="select__number--minus cursor-pointer p-absolute d-flex align-center justify-center">
                                                        <img src="./assets/images/icon/minus.svg" alt="minus" />
                                                    </a>
                                                    <!-- NHẬP SỐ LƯỢNG BẰNG TAY -->
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        value="<?php echo $qtyLine; ?>"
                                                        class="select__number--value heading-6 w-100 h-100"
                                                        data-product-id="<?php echo $pid; ?>" />
                                                    <a href="pages/handle/addtocart.php?sum=<?php echo $pid; ?>"
                                                        class="select__number--plus cursor-pointer p-absolute d-flex align-center justify-center">
                                                        <img src="./assets/images/icon/plus.svg" alt="plus" />
                                                    </a>
                                                </div>
                                                <div class="cart__delete cursor-pointer d-flex align-center justify-center">
                                                    <a href="pages/handle/addtocart.php?delete=<?php echo $pid; ?>">
                                                        <img src="./assets/images/icon/delete.svg" alt="delete" />
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="cart__total h4 opacity-50">
                                            <?php echo number_format($priceNew) . ' ₫'; ?>
                                        </div>
                                    </div>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- PHẦN TỔNG TIỀN - SIDEBAR STICKY -->
                <div class="cart__summary-section">
                    <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 18px;">Hóa đơn</h3>

                    <div class="cart__summary-row">
                        <p class="cart__summary-label">Tạm tính:</p>
                        <p class="cart__summary-value" id="subtotal">0₫</p>
                    </div>

                    <div class="cart__summary-row sale">
                        <p class="cart__summary-label" style="color: #e74c3c; font-weight: 600;">Giảm giá:</p>
                        <p class="cart__summary-value" id="discount-amount" style="color: #e74c3c;">0₫</p>
                    </div>

                    <!-- <div class="cart__summary-row">
                        <p class="cart__summary-label">Phí vận chuyển:</p>
                        <p class="cart__summary-value">Miễn phí</p>
                    </div> -->

                    <div class="cart__summary-row total-row">
                        <p class="cart__summary-label">Tổng tiền:</p>
                        <p class="cart__summary-value" id="total-amount">0₫</p>
                    </div>

                    <!-- <p class="cart__summary-context">
                        Thuế được áp dụng khi thanh toán
                    </p> -->

                    <div class="cart__action-buttons">
                        <?php
                        if (isset($_SESSION['account_email'])) {
                            if ($validate == true) {
                        ?>
                                <form id="checkout-form" action="index.php?page=checkout" method="POST" style="width: 100%;">
                                    <input type="hidden" name="selected_items" id="selected-items-input" value="">
                                    <button type="submit" class="btn btn__solid text-center" style="width: 100%;">Tiến hành đặt hàng</button>
                                </form>
                            <?php } else { ?>
                                <button class="btn btn__solid text-center opacity-50" onclick="showErrorMessage();">Tiến hành đặt hàng</button>
                            <?php } ?>
                        <?php } else { ?>
                            <a class="btn btn__outline" href="index.php?page=login">Đăng nhập đặt hàng</a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php
        } else {
        ?>
            <p>Hiện không có sản phẩm nào trong giỏ hàng</p>
        <?php
        }
        ?>
    </div>
</section>
<!-- end cart -->

<script>
    function showSuccessMessage() {
        toast({
            title: "Success",
            message: "Cập nhật thành công",
            type: "success",
            duration: 3000,
        });
    }

    function showErrorMessage() {
        toast({
            title: "Error",
            message: "Số lượng vượt quá tồn kho",
            type: "error",
            duration: 3000,
        });
    }

    /**
     * Cập nhật hóa đơn dựa trên sản phẩm được chọn
     */
    function updateInvoice() {
        const checkboxes = document.querySelectorAll('.product-checkbox:not(#select-all-products):checked');
        let subtotal = 0; // Giá gốc
        let discount = 0; // Giảm giá
        let total = 0; // Tổng sau giảm

        checkboxes.forEach(checkbox => {
            const priceOld = parseFloat(checkbox.getAttribute('data-price-old')) || 0;
            const priceNew = parseFloat(checkbox.getAttribute('data-price')) || 0;
            const qty = parseInt(checkbox.getAttribute('data-qty')) || 0;
            const sale = parseFloat(checkbox.getAttribute('data-sale')) || 0;

            const itemSubtotal = priceOld * qty;
            const itemDiscount = itemSubtotal - (priceNew * qty);

            subtotal += itemSubtotal;
            discount += itemDiscount;
            total += priceNew * qty;
        });

        // Cập nhật UI
        document.getElementById('subtotal').textContent = number_format(subtotal) + '₫';
        document.getElementById('discount-amount').textContent = number_format(discount) + '₫';
        document.getElementById('total-amount').textContent = number_format(total) + '₫';
    }

    /**
     * Format number theo kiểu Việt Nam (3 chữ số 1 dấu chấm)
     */
    function number_format(number) {
        return Math.round(number).toLocaleString('vi-VN');
    }

    /**
     * Xử lý submit form checkout - kiểm tra sản phẩm được chọn
     */
    function handleCheckoutSubmit(event) {
        const checkboxes = document.querySelectorAll('.product-checkbox:not(#select-all-products):checked');

        if (checkboxes.length === 0) {
            event.preventDefault();
            toast({
                title: "Cảnh báo",
                message: "Vui lòng chọn ít nhất 1 sản phẩm để thanh toán",
                type: "error",
                duration: 3000,
            });
            return false;
        }

        // Thu thập danh sách product IDs
        const selectedIds = Array.from(checkboxes).map(cb => cb.getAttribute('data-product-id')).join(',');
        document.getElementById('selected-items-input').value = selectedIds;
        return true;
    }

    // ====== CHECKOUT FORM EVENT ======
    document.addEventListener("DOMContentLoaded", function() {
        const checkoutForm = document.getElementById('checkout-form');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', handleCheckoutSubmit);
        }
    });

    // ====== CHECKBOX EVENTS ======
    document.addEventListener("DOMContentLoaded", function() {
        const selectAllCheckbox = document.getElementById('select-all-products');
        const productCheckboxes = document.querySelectorAll('.product-checkbox:not(#select-all-products)');

        // Lắng nghe thay đổi checkbox từng sản phẩm
        productCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Cập nhật state của "select all" checkbox
                const allChecked = Array.from(productCheckboxes).every(cb => cb.checked || cb.disabled);
                const anyChecked = Array.from(productCheckboxes).some(cb => cb.checked);
                selectAllCheckbox.checked = allChecked && anyChecked;
                selectAllCheckbox.indeterminate = anyChecked && !allChecked;

                // Cập nhật hóa đơn
                updateInvoice();
            });
        });

        // Lắng nghe thay đổi "select all" checkbox
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                productCheckboxes.forEach(checkbox => {
                    if (!checkbox.disabled) {
                        checkbox.checked = isChecked;
                    }
                });
                updateInvoice();
            });
        }

        // ====== CẬP NHẬT SỐ LƯỢNG ======
        const inputs = document.querySelectorAll(".select__number--value");
        inputs.forEach(function(input) {
            input.addEventListener("change", function() {
                var pid = this.getAttribute("data-product-id");
                var qty = parseInt(this.value, 10);

                if (isNaN(qty) || qty < 0) {
                    qty = 0; // server sẽ xử lý: 0 = xóa sản phẩm
                }

                window.location.href =
                    "pages/handle/addtocart.php?update=" + encodeURIComponent(pid) +
                    "&qty=" + encodeURIComponent(qty);
            });
        });
    });
</script>
<?php
if (isset($_GET['message']) && $_GET['message'] == 'success') {
    echo '<script>showSuccessMessage();window.history.pushState(null, "", "index.php?page=cart");</script>';
} elseif (isset($_GET['message']) && $_GET['message'] == 'error') {
    echo '<script>showErrorMessage();window.history.pushState(null, "", "index.php?page=cart");</script>';
}
?>
