<?php
if (session_status() === PHP_SESSION_NONE) {
  session_name('guha');
  session_start();
}

/* ====== DB include nếu thiếu ====== */
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
  $candidates = [
    __DIR__ . '/../../admin/config/config.php',
    __DIR__ . '/../../../admin/config/config.php',
    __DIR__ . '/admin/config/config.php',
  ];
  foreach ($candidates as $p) {
    if (is_file($p)) {
      require_once $p;
      break;
    }
  }
}
if (isset($mysqli) && $mysqli instanceof mysqli) {
  @$mysqli->set_charset('utf8mb4');
}

/* ===== Nhận selected_items từ cart page ===== */
$selected_items_param = (string)($_POST['selected_items'] ?? '');
if (empty($selected_items_param) && !empty($_GET['selected_items'])) {
  $selected_items_param = (string)$_GET['selected_items'];
}

/* ===== Helpers ===== */
function vnd($n)
{
  return number_format((float)$n, 0, ',', '.') . '₫';
}
function e($v)
{
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
function is_logged_in(): bool
{
  return !empty($_SESSION['account_id']) || !empty($_SESSION['account_email']);
}
function effective_price(float $price, float $sale): float
{
  if ($sale <= 0) return $price;
  return max(0, $price - ($price * $sale / 100));
}
function resolve_product_image(?string $raw): string
{
  if (!$raw) return '';
  $raw = trim(str_replace('\\', '/', $raw));
  if (preg_match('~^https?://~i', $raw)) return $raw;
  return 'admin/modules/product/uploads/' . basename($raw);
}

/* ===== Prefill shipping info ===== */
$prefill = ['customer_name' => '', 'customer_address' => '', 'customer_phone' => ''];
$has_any = false;

$acc_id = (int)($_SESSION['account_id'] ?? 0);
$acc_email = (string)($_SESSION['account_email'] ?? '');

/** Try helper: fetch 1 row */
function fetch_one(mysqli $db, string $sql, string $types = '', array $params = []): ?array
{
  if (!$db) return null;
  $stmt = $db->prepare($sql);
  if (!$stmt) return null;
  if ($types && $params) $stmt->bind_param($types, ...$params);
  if (!$stmt->execute()) {
    $stmt->close();
    return null;
  }
  $res = $stmt->get_result();
  $row = $res ? $res->fetch_assoc() : null;
  $stmt->close();
  return $row ?: null;
}

if (is_logged_in() && isset($mysqli) && $mysqli instanceof mysqli) {
  // 1) CUSTOMER theo account_id
  if (!$has_any && $acc_id > 0) {
    if ($row = fetch_one(
      $mysqli,
      "SELECT customer_name, customer_address, customer_phone
       FROM customer WHERE account_id = ? LIMIT 1",
      "i",
      [$acc_id]
    )) {
      $prefill['customer_name']    = (string)($row['customer_name'] ?? '');
      $prefill['customer_address'] = (string)($row['customer_address'] ?? '');
      $prefill['customer_phone']   = (string)($row['customer_phone'] ?? '');
      $has_any = $prefill['customer_name'] || $prefill['customer_address'] || $prefill['customer_phone'];
    }
  }
  // 1b) CUSTOMER theo email
  if (!$has_any && $acc_email !== '') {
    if ($row = fetch_one(
      $mysqli,
      "SELECT c.customer_name, c.customer_address, c.customer_phone
         FROM customer c
         JOIN account a ON a.account_id = c.account_id
        WHERE a.account_email = ?
        LIMIT 1",
      "s",
      [$acc_email]
    )) {
      $prefill['customer_name']    = (string)($row['customer_name'] ?? '');
      $prefill['customer_address'] = (string)($row['customer_address'] ?? '');
      $prefill['customer_phone']   = (string)($row['customer_phone'] ?? '');
      $has_any = $prefill['customer_name'] || $prefill['customer_address'] || $prefill['customer_phone'];
    }
  }

  // 2) DELIVERY gần nhất theo account_id/email
  if (!$has_any && $acc_id > 0) {
    if ($row = fetch_one(
      $mysqli,
      "SELECT delivery_name, delivery_address, delivery_phone
         FROM delivery
        WHERE account_id = ?
        ORDER BY delivery_id DESC
        LIMIT 1",
      "i",
      [$acc_id]
    )) {
      $prefill['customer_name']    = (string)($row['delivery_name'] ?? '');
      $prefill['customer_address'] = (string)($row['delivery_address'] ?? '');
      $prefill['customer_phone']   = (string)($row['delivery_phone'] ?? '');
      $has_any = $prefill['customer_name'] || $prefill['customer_address'] || $prefill['customer_phone'];
    }
  }
  if (!$has_any && $acc_email !== '') {
    if ($row = fetch_one(
      $mysqli,
      "SELECT d.delivery_name, d.delivery_address, d.delivery_phone
         FROM delivery d
         JOIN account a ON a.account_id = d.account_id
        WHERE a.account_email = ?
        ORDER BY d.delivery_id DESC
        LIMIT 1",
      "s",
      [$acc_email]
    )) {
      $prefill['customer_name']    = (string)($row['delivery_name'] ?? '');
      $prefill['customer_address'] = (string)($row['delivery_address'] ?? '');
      $prefill['customer_phone']   = (string)($row['delivery_phone'] ?? '');
      $has_any = $prefill['customer_name'] || $prefill['customer_address'] || $prefill['customer_phone'];
    }
  }

  // 3) ACCOUNT (name, phone)
  if (!$has_any && ($acc_id > 0 || $acc_email !== '')) {
    $row = null;
    if ($acc_id > 0) {
      $row = fetch_one(
        $mysqli,
        "SELECT account_name, account_phone FROM account WHERE account_id = ? LIMIT 1",
        "i",
        [$acc_id]
      );
    } elseif ($acc_email !== '') {
      $row = fetch_one(
        $mysqli,
        "SELECT account_name, account_phone FROM account WHERE account_email = ? LIMIT 1",
        "s",
        [$acc_email]
      );
    }
    if ($row) {
      $prefill['customer_name']  = (string)($row['account_name'] ?? '');
      $prefill['customer_phone'] = (string)($row['account_phone'] ?? '');
      $has_any = $prefill['customer_name'] || $prefill['customer_phone'];
    }
  }
}

/* 4) Fallback session key nếu có */
if (!$has_any) {
  if (!empty($_SESSION['account_name']))  $prefill['customer_name']  = (string)$_SESSION['account_name'];
  if (!empty($_SESSION['account_phone'])) $prefill['customer_phone'] = (string)$_SESSION['account_phone'];
  if (!empty($_SESSION['account_address'])) $prefill['customer_address'] = (string)$_SESSION['account_address'];
  $has_any = $prefill['customer_name'] || $prefill['customer_address'] || $prefill['customer_phone'];
}

/* ===== XÁC ĐỊNH DANH SÁCH SẢN PHẨM CHECKOUT ===== */
/*
 * mode=buynow  => dùng session['buynow'] (chỉ 1 sản phẩm vừa bấm "Mua ngay")
 * selected_items => lấy từ POST (checkout chỉ sản phẩm được chọn)
 * mặc định     => dùng session['cart'] (checkout toàn bộ giỏ hàng)
 */
$isBuyNow = isset($_GET['mode']) && $_GET['mode'] === 'buynow';
$checkout_items = [];
$selected_product_ids = [];

// Nếu có selected_items từ cart page (chỉ lấy sản phẩm được chọn)
if (!empty($_POST['selected_items'])) {
  $selected_product_ids = array_map('intval', explode(',', trim($_POST['selected_items'])));
  $selected_product_ids = array_filter($selected_product_ids);
}

if ($isBuyNow && !empty($_SESSION['buynow'])) {
  $checkout_items = [$_SESSION['buynow']];
} elseif (!empty($selected_product_ids) && !empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
  // Chỉ lấy sản phẩm có product_id trong selected_product_ids
  foreach ($_SESSION['cart'] as $cart_item) {
    if (in_array((int)$cart_item['product_id'], $selected_product_ids)) {
      $checkout_items[] = $cart_item;
    }
  }
} else {
  // Fallback: lấy toàn bộ giỏ hàng
  if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $checkout_items = $_SESSION['cart'];
  }
}
?>
<!-- start checkout -->
<style>
  .payment-item-option {
    position: relative;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease, background-color 0.25s ease;
  }

  .payment-item-option::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 4px;
    background: var(--payment-accent, #d1d5db);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.25s ease;
  }

  .payment-item-option::before {
    content: '';
    position: absolute;
    inset: 0 auto 0 0;
    width: 6px;
    background: transparent;
    transition: background-color 0.25s ease;
  }

  .payment-item-option--active {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
  }

  .payment-item-option--active::after {
    transform: scaleX(1);
  }

  .payment-item-option--active::before {
    background: var(--payment-accent, #111827);
  }

  .payment-item-option .payment-selected-badge {
    position: absolute;
    top: 10px;
    right: 12px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .02em;
    color: #fff;
    background: var(--payment-accent, #111827);
    opacity: 0;
    transform: translateY(-4px);
    transition: opacity 0.2s ease, transform 0.2s ease;
    pointer-events: none;
  }

  .payment-item-option--active .payment-selected-badge {
    opacity: 1;
    transform: translateY(0);
  }

  .payment-item-option[data-value="1"] {
    --payment-accent: #2563eb;
  }

  .payment-item-option[data-value="2"] {
    --payment-accent: #db2777;
  }

  .payment-item-option[data-value="5"] {
    --payment-accent: #059669;
  }

  .payment-item-option[data-value="1"] .payment-selected-badge {
    background: #2563eb;
  }

  .payment-item-option[data-value="2"] .payment-selected-badge {
    background: #db2777;
  }

  .payment-item-option[data-value="5"] .payment-selected-badge {
    background: #059669;
  }

  .payment-item-option .payment__icon {
    transition: transform 0.25s ease, filter 0.25s ease;
  }

  .payment-item-option--active .payment__icon {
    transform: scale(1.08);
    filter: drop-shadow(0 6px 12px rgba(15, 23, 42, 0.12));
  }

  .payment-item-option--active label span:first-child {
    color: var(--payment-accent, #111827) !important;
  }

  #bank-transfer-form {
    border: 1px solid transparent;
    border-radius: 12px;
    transition: opacity 0.25s ease, transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
  }

  #bank-transfer-form.is-visible {
    border-color: #d1fae5;
    box-shadow: 0 14px 30px rgba(5, 150, 105, 0.08);
    transform: translateY(0);
  }

  #bank-transfer-form.is-hidden {
    opacity: 0;
    transform: translateY(-6px);
  }

  #bank-transfer-form .bank-transfer-panel {
    border: 1px solid #d1fae5;
    border-radius: 12px;
    background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);
  }
</style>
<section class="checkout pd-section">
  <div class="container">
    <form action="pages/handle/checkout.php" method="POST">
      <input type="hidden" name="mode" value="<?= $isBuyNow ? 'buynow' : 'cart'; ?>">
      <input type="hidden" name="selected_items" value="<?= htmlspecialchars($selected_items_param, ENT_QUOTES, 'UTF-8'); ?>">

      <div class="row">
        <div class="col" style="--w-md:7;">
          <h2 class="checkout__title h4 d-flex align-center space-between">Thông tin người nhận hàng:</h2>

          <?php if (!empty($_SESSION['checkout_errors']) && is_array($_SESSION['checkout_errors'])): ?>
            <div style="margin:12px 0;padding:12px;border:1px solid #f5c2c7;background:#f8d7da;color:#842029;border-radius:6px;">
              <?php foreach ($_SESSION['checkout_errors'] as $err): ?>
                <div><?= e($err) ?></div>
              <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['checkout_errors']); ?>
          <?php endif; ?>

          <div class="checkout__infomation">
            <?php if (is_logged_in()) : ?>

              <div class="info__item" style="margin-bottom: 16px;">
                <label style="margin-right:16px;">
                  <input type="radio" name="address_type" value="default" checked>
                  Dùng địa chỉ mặc định từ tài khoản
                </label>
                <label>
                  <input type="radio" name="address_type" value="new">
                  Nhập địa chỉ giao hàng mới
                </label>
              </div>

              <div class="info__item d-flex">
                <label class="info__title" for="delivery_name">Tên khách hàng:</label>
                <input id="delivery_name" type="text" class="info__input flex-1" name="delivery_name"
                  value="<?= e($prefill['customer_name']) ?>"
                  placeholder="Tên người nhận từ tài khoản" readonly required>
              </div>

              <div class="info__item d-flex">
                <label class="info__title" for="delivery_address">Địa chỉ:</label>
                <input id="delivery_address" type="text" class="info__input flex-1" name="delivery_address"
                  value="<?= e($prefill['customer_address']) ?>"
                  placeholder="Địa chỉ từ tài khoản" readonly required>
              </div>

              <div class="info__item d-flex">
                <label class="info__title" for="delivery_phone">Số điện thoại:</label>
                <input id="delivery_phone" type="tel" class="info__input flex-1" name="delivery_phone"
                  value="<?= e($prefill['customer_phone']) ?>"
                  placeholder="Số điện thoại từ tài khoản"
                  pattern="^0\d{9,10}$" title="SĐT bắt đầu bằng 0 và có 10-11 số" readonly required>
              </div>

              <div id="new-address-box" style="display:none;">
                <div class="info__item d-flex">
                  <label class="info__title" for="new_delivery_name">Tên người nhận mới:</label>
                  <input id="new_delivery_name" type="text" class="info__input flex-1" name="new_delivery_name"
                    placeholder="Nhập tên người nhận mới">
                </div>
                <div class="info__item d-flex">
                  <label class="info__title" for="new_delivery_address">Địa chỉ mới:</label>
                  <input id="new_delivery_address" type="text" class="info__input flex-1" name="new_delivery_address"
                    placeholder="Nhập địa chỉ giao hàng mới">
                </div>
                <div class="info__item d-flex">
                  <label class="info__title" for="new_delivery_phone">Số điện thoại mới:</label>
                  <input id="new_delivery_phone" type="tel" class="info__input flex-1" name="new_delivery_phone"
                    placeholder="Nhập số điện thoại mới"
                    pattern="^0\d{9,10}$" title="SĐT bắt đầu bằng 0 và có 10-11 số">
                </div>
              </div>

              <div class="info__item d-flex">
                <label class="info__title" for="delivery_note">Ghi chú:</label>
                <input id="delivery_note" type="text" class="info__input flex-1"
                  placeholder="Nhập vào ghi chú với người bán ..." name="delivery_note" value="">
              </div>
            <?php else : ?>
              <a href="index.php?page=login">Vui lòng đăng nhập tài khoản</a>
            <?php endif; ?>
          </div>
        </div>

        <div class="col" style="--w-md:5;">
          <div class="checkout__cart">
            <div class="checkout__items">
              <?php
              $total = 0;
              if (!empty($checkout_items)):
                foreach ($checkout_items as $ci):
                  $price = (float)($ci['product_price'] ?? 0);
                  $sale  = (float)($ci['product_sale']  ?? 0);
                  $qty   = (int)  ($ci['product_quantity'] ?? 0);
                  $unit  = effective_price($price, $sale);
                  $line  = $unit * $qty;
                  $total += $line;
                  $imgUrl = resolve_product_image($ci['product_image'] ?? '');
                  $pname  = e($ci['product_name'] ?? '');
                  $pid    = (int)($ci['product_id'] ?? 0);
              ?>
                  <div class="checkout__item d-flex align-center">
                    <div class="checkout__image p-relative">
                      <div class="product-quantity align-center d-flex justify-center p-absolute"><span class="h6"><?= $qty ?></span></div>
                      <a href="index.php?page=product_detail&product_id=<?= $pid ?>">
                        <img class="w-100 d-block object-fit-cover ratio-1" src="<?= e($imgUrl) ?>" alt="<?= $pname ?>">
                      </a>
                    </div>
                    <div class="checkout__name flex-1">
                      <h3 class="h6"><?= $pname ?></h3>
                    </div>
                    <div class="h6 checkout__price"><?= vnd($unit) ?></div>
                  </div>
                <?php endforeach;
              else: ?>
                <span>Không tồn tại giỏ hàng</span>
              <?php endif; ?>

              <!-- Invoice Section - Grid Layout -->
              <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-top: 20px;">
                <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                  <span class="h6" style="margin: 0;">Tạm tính:</span>
                  <span class="h6" style="margin: 0;"><?= vnd($total) ?></span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                  <span class="h6" style="margin: 0; color: #e74c3c;">Giảm giá:</span>
                  <span class="h6" style="margin: 0; color: #e74c3c;">0₫</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                  <span class="h6" style="margin: 0;">Phí vận chuyển:</span>
                  <span class="h6" style="margin: 0;">Miễn phí</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: center; border-bottom: 2px solid #333; padding-bottom: 15px;">
                  <h4 style="font-size: 16px; font-weight: 700; margin: 0;">Tổng tiền:</h4>
                  <span style="font-size: 20px; font-weight: 700;"><?= vnd($total) ?></span>
                </div>
              </div>
            </div>

            <div class="checkout__bottom text-right">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col" style="width: 100% !important; box-sizing: border-box; padding-inline: var(--gutter);">
            <h4 class="h4 payment__heading">Phương thức thanh toán:</h4>
            <div class="payment__items" style="display: flex; flex-direction: column; gap: 12px; margin-top: 16px; width: 100%;">
              <div style="display: flex; align-items: center; width: 100%; padding: 12px; border: 2px solid #333; border-radius: 8px; cursor: pointer; gap: 16px; background: #f9f9f9; transition: all 0.3s; min-height: 80px; box-sizing: border-box;" class="payment-item-option" data-value="1">
                <span class="payment-selected-badge">Đã chọn</span>
                <input class="payment__radio" type="radio" name="order_type" id="payment_default" value="1" checked style="width: 20px; height: 20px; cursor: pointer; flex-shrink: 0;">
                <img class="payment__icon" src="./assets/images/icon/icon-shipcod.png" alt="Ship COD" style="width: 48px; height: 48px; object-fit: contain; flex-shrink: 0;">
                <label for="payment_default" style="flex: 1; cursor: pointer; display: flex; flex-direction: column; gap: 6px; margin: 0;">
                  <span style="font-weight: 600; font-size: 16px; color: #333;">COD</span>
                  <span style="font-size: 14px; color: #666; line-height: 1.4;">Thanh toán khi nhận hàng</span>
                </label>
              </div>
              <div style="display: flex; align-items: center; width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; gap: 16px; background: transparent; transition: all 0.3s; min-height: 80px; box-sizing: border-box;" class="payment-item-option" data-value="2">
                <span class="payment-selected-badge">Đã chọn</span>
                <input class="payment__radio" type="radio" name="order_type" id="payment_momo_qr" value="2" style="width: 20px; height: 20px; cursor: pointer; flex-shrink: 0;">
                <img class="payment__icon" src="./assets/images/payment/qrcode.png" alt="QR CODE" style="width: 48px; height: 48px; object-fit: contain; flex-shrink: 0;">
                <label for="payment_momo_qr" style="flex: 1; cursor: pointer; display: flex; flex-direction: column; gap: 6px; margin: 0;">
                  <span style="font-weight: 600; font-size: 16px; color: #333;">QR CODE</span>
                  <span style="font-size: 14px; color: #666; line-height: 1.4;">Thanh toán MOMO QRCODE</span>
                </label>
              </div>
              <!-- Chuyển khoản Ngân Hàng -->
              <div style="display: flex; align-items: center; width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; gap: 16px; background: transparent; transition: all 0.3s; min-height: 80px; box-sizing: border-box;" class="payment-item-option" data-value="5">
                <span class="payment-selected-badge">Đã chọn</span>
                <input class="payment__radio" type="radio" name="order_type" id="payment_bank" value="5" style="width: 20px; height: 20px; cursor: pointer; flex-shrink: 0;">
                <img class="payment__icon" src="./assets/images/payment/icons8-money-48.png" alt="Bank Transfer" style="width: 48px; height: 48px; object-fit: contain; flex-shrink: 0;">
                <label for="payment_bank" style="flex: 1; cursor: pointer; display: flex; flex-direction: column; gap: 6px; margin: 0;">
                  <span style="font-weight: 600; font-size: 16px; color: #333;">Chuyển Khoản</span>
                  <span style="font-size: 14px; color: #666; line-height: 1.4;">Chuyển tiền từ tài khoản ngân hàng của bạn</span>
                </label>
              </div>
              <!-- ⏸️ HIDDEN: VNPAY payment option (gateway paused) -->
              <div class="payment__item d-flex align-center" style="display:none;">
                <input class="payment__radio" type="radio" name="order_type" id="payment_vnp" value="4" disabled />
                <img class="payment__icon" src="./assets/images/payment/vnpay.png" alt="VNPAY" style="width:62px;">
                <label class="payment__label w-100 h-100" for="payment_vnp">
                  <span class="d-block">VNPAY</span>
                  <span class="d-block">Thanh toán chuyển khoản VNPAY</span>
                </label>
              </div>
            </div>

            <!-- Bank Transfer Form (Type 5) -->
            <div id="bank-transfer-form" class="is-hidden" style="display:none; margin-top:16px; padding:0;">

              <!-- Hint Box -->
              <div class="bank-transfer-panel" style="margin-bottom:16px; padding:12px;">
                <p style="margin:0; font-size:13px; color:#333;"><strong>Demo:</strong></p>
                <p style="margin:8px 0 0 0; font-size:12px; color:#666; line-height:1.6;">
                  • Ngân Hàng: <strong>BIDV</strong><br>
                  • Số TK: <strong>123456789</strong><br>
                  • Tên CT: <strong>ADMIN DEMO</strong>
                </p>
              </div>

              <div style="margin-bottom:16px;">
                <label for="bank_name" style="display:block; margin-bottom:8px; font-weight:500;">Chọn Ngân Hàng: <span style="color:red;">*</span></label>
                <select name="bank_name" id="bank_name" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px; font-size:14px; background:#fff;">
                  <option value="">-- Chọn ngân hàng --</option>
                  <option value="Vietinbank">Vietinbank (VTB)</option>
                  <option value="BIDV">BIDV</option>
                  <option value="Vietcombank">Vietcombank (VCB)</option>
                </select>
                <small id="bank_name_error" style="color:red; display:none; margin-top:4px; font-size:12px;"></small>
              </div>

              <div style="margin-bottom:16px;">
                <label for="bank_account_number" style="display:block; margin-bottom:8px; font-weight:500;">Số Tài Khoản: <span style="color:red;">*</span></label>
                <input type="text" name="bank_account_number" id="bank_account_number" placeholder="VD: 123456789" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px; font-size:14px; background:#fff; box-sizing:border-box;">
                <small id="bank_account_number_error" style="color:red; display:none; margin-top:4px; font-size:12px;"></small>
              </div>

              <div style="margin-bottom:0;">
                <label for="bank_account_holder" style="display:block; margin-bottom:8px; font-weight:500;">Tên Chủ Tài Khoản: <span style="color:red;">*</span></label>
                <input type="text" name="bank_account_holder" id="bank_account_holder" placeholder="VD: ADMIN DEMO" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px; font-size:14px; background:#fff; box-sizing:border-box;">
                <small id="bank_account_holder_error" style="color:red; display:none; margin-top:4px; font-size:12px;"></small>
              </div>
            </div>
          </div>
        </div>

        <div class="w-100 pd-top text-left">
          <button type="submit" name="redirect" class="btn btn__solid">
            Đặt hàng
          </button>
          <a href="index.php?page=cart" class="btn anchor">Trở về giỏ hàng</a>
        </div>
    </form>
  </div>
</section>
<!-- end checkout -->

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const addressRadios = document.querySelectorAll('input[name="address_type"]');
    const newAddressBox = document.getElementById('new-address-box');

    const defaultName = document.getElementById('delivery_name');
    const defaultAddress = document.getElementById('delivery_address');
    const defaultPhone = document.getElementById('delivery_phone');

    const newName = document.getElementById('new_delivery_name');
    const newAddress = document.getElementById('new_delivery_address');
    const newPhone = document.getElementById('new_delivery_phone');

    function toggleAddressForm() {
      const checked = document.querySelector('input[name="address_type"]:checked');
      const mode = checked ? checked.value : 'default';
      const isNew = mode === 'new';

      newAddressBox.style.display = isNew ? 'block' : 'none';

      defaultName.required = !isNew;
      defaultAddress.required = !isNew;
      defaultPhone.required = !isNew;

      if (newName) newName.required = isNew;
      if (newAddress) newAddress.required = isNew;
      if (newPhone) newPhone.required = isNew;
    }

    addressRadios.forEach(function(radio) {
      radio.addEventListener('change', toggleAddressForm);
    });
    toggleAddressForm();

    // ============ PAYMENT METHOD HANDLERS ============
    const paymentRadios = document.querySelectorAll('input[name="order_type"]');
    const bankInfo = document.getElementById('bank-info');
    const bankTransferForm = document.getElementById('bank-transfer-form');
    const paymentItemOptions = document.querySelectorAll('.payment-item-option');

    const paymentThemeMap = {
      '1': {
        border: '#2563eb',
        background: 'rgba(37, 99, 235, 0.08)'
      },
      '2': {
        border: '#db2777',
        background: 'rgba(219, 39, 119, 0.08)'
      },
      '5': {
        border: '#059669',
        background: 'rgba(5, 150, 105, 0.08)'
      }
    };

    function resetPaymentItem(item) {
      item.classList.remove('payment-item-option--active');
      item.style.borderColor = '#ddd';
      item.style.backgroundColor = 'transparent';
      item.style.boxShadow = 'none';
    }

    function applyPaymentTheme(item, value) {
      const theme = paymentThemeMap[value] || paymentThemeMap['1'];
      item.classList.add('payment-item-option--active');
      item.style.borderColor = theme.border;
      item.style.backgroundColor = theme.background;
      item.style.boxShadow = '0 12px 30px rgba(15, 23, 42, 0.12)';
    }

    function highlightPaymentItem() {
      const checked = document.querySelector('input[name="order_type"]:checked');
      if (!checked) return;

      const selectedValue = checked.value;

      paymentItemOptions.forEach(function(item) {
        const itemValue = item.getAttribute('data-value');
        if (itemValue === selectedValue) {
          applyPaymentTheme(item, selectedValue);
        } else {
          resetPaymentItem(item);
        }
      });
    }

    function syncPaymentState() {
      highlightPaymentItem();
      toggleBankTransferForm();
      toggleBankInfo();
    }

    function toggleBankTransferForm() {
      const checked = document.querySelector('input[name="order_type"]:checked');
      const value = checked ? checked.value : '1';
      if (value === '5') {
        bankTransferForm.style.display = 'block';
        bankTransferForm.classList.remove('is-hidden');
        requestAnimationFrame(function () {
          bankTransferForm.classList.add('is-visible');
        });
      } else {
        bankTransferForm.classList.remove('is-visible');
        bankTransferForm.classList.add('is-hidden');
        window.setTimeout(function () {
          var currentChecked = document.querySelector('input[name="order_type"]:checked');
          if (!currentChecked || currentChecked.value !== '5') {
            bankTransferForm.style.display = 'none';
          }
        }, 220);
      }

      // Clear errors when toggling away
      if (value !== '5') {
        clearBankTransferErrors();
      }
    }

    function clearBankTransferErrors() {
      const errorElements = document.querySelectorAll('[id$="_error"]');
      errorElements.forEach(el => {
        if (el.id.startsWith('bank_')) {
          el.style.display = 'none';
          el.textContent = '';
        }
      });
    }

    function toggleBankInfo() {
      bankInfo.style.display = 'none'; // Always hidden
    }

    function validateBankTransferForm() {
      const checked = document.querySelector('input[name="order_type"]:checked');
      const value = checked ? checked.value : '1';

      // Only validate if bank transfer is selected
      if (value !== '5') {
        return true;
      }

      clearBankTransferErrors();
      let isValid = true;

      // Validate bank name
      const bankName = document.getElementById('bank_name').value.trim();
      if (bankName === '') {
        document.getElementById('bank_name_error').textContent = 'Vui lòng chọn ngân hàng';
        document.getElementById('bank_name_error').style.display = 'block';
        isValid = false;
      }

      // Validate account number (only digits)
      const accountNumber = document.getElementById('bank_account_number').value.trim();
      if (accountNumber === '') {
        document.getElementById('bank_account_number_error').textContent = 'Vui lòng nhập số tài khoản';
        document.getElementById('bank_account_number_error').style.display = 'block';
        isValid = false;
      } else if (!/^\d+$/.test(accountNumber)) {
        document.getElementById('bank_account_number_error').textContent = 'Số tài khoản chỉ được chứa chữ số';
        document.getElementById('bank_account_number_error').style.display = 'block';
        isValid = false;
      } else if (accountNumber.length < 8 || accountNumber.length > 20) {
        document.getElementById('bank_account_number_error').textContent = 'Số tài khoản từ 8-20 chữ số';
        document.getElementById('bank_account_number_error').style.display = 'block';
        isValid = false;
      }

      // Validate account holder (letters and spaces only)
      const accountHolder = document.getElementById('bank_account_holder').value.trim();
      if (accountHolder === '') {
        document.getElementById('bank_account_holder_error').textContent = 'Vui lòng nhập tên chủ tài khoản';
        document.getElementById('bank_account_holder_error').style.display = 'block';
        isValid = false;
      } else if (!/^[a-zA-Z\s]+$/.test(accountHolder)) {
        document.getElementById('bank_account_holder_error').textContent = 'Tên chủ tài khoản chỉ chứa chữ cái và khoảng trắng';
        document.getElementById('bank_account_holder_error').style.display = 'block';
        isValid = false;
      }

      return isValid;
    }

    // ============ ATTACH EVENT LISTENERS ============
    paymentRadios.forEach(function(radio) {
      radio.addEventListener('change', function() {
        syncPaymentState();
      });
    });

    // ============ CLICK ENTIRE PAYMENT ITEM TO SELECT ============
    paymentItemOptions.forEach(function(item) {
      item.addEventListener('click', function(e) {
        // Prevent double-trigger if clicking radio directly
        if (e.target.tagName === 'INPUT') return;

        const radio = item.querySelector('input[type="radio"]');
        if (radio) {
          radio.checked = true;
          radio.dispatchEvent(new Event('change', {
            bubbles: true
          }));
        }
      });
    });

    // ============ INIT: Highlight and show/hide forms on page load ============
    syncPaymentState();
    window.setTimeout(syncPaymentState, 0);
    window.addEventListener('pageshow', syncPaymentState);

    // ============ FORM SUBMISSION ============
    const checkoutForm = document.querySelector('form');
    if (checkoutForm) {
      checkoutForm.addEventListener('submit', function(e) {
        if (!validateBankTransferForm()) {
          e.preventDefault();
          return false;
        }
      });
    }
  });
</script>
