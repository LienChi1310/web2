<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('guha');
    session_start();
}

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

$orderSummary = $_SESSION['order_summary'] ?? null;

if (!$orderSummary || !is_array($orderSummary)) {
    ?>
    <section class="checkout pd-section">
        <div class="container">
            <div class="thankiu__box text-center">
                <h2 class="h3">Không tìm thấy thông tin đơn hàng</h2>
                <p>Vui lòng thực hiện đặt hàng lại.</p>
                <a href="index.php?page=cart" class="btn btn__outline">Về giỏ hàng</a>
            </div>
        </div>
    </section>
    <?php
    return;
}

$order_code    = (int)($orderSummary['order_code'] ?? 0);
$display_code  = $order_code > 0 ? 'ORD' . $order_code : '';
$total_amount  = (float)($orderSummary['total_amount'] ?? 0);
$customer_name = isset($_SESSION['account_name']) ? (string)$_SESSION['account_name'] : '';
$customer_name = $customer_name !== '' ? $customer_name : 'Quý khách';

if (!function_exists('vnd_transfer_fake')) {
    function vnd_transfer_fake($n) {
        return number_format((float)$n, 0, ',', '.') . ' ₫';
    }
}

$bank_name = 'BIDV';
$bank_account = '123456789';
$bank_holder = 'GUHA PERFUME';
$transfer_content = $display_code !== '' ? $display_code : 'Thanh toan don hang';
?>
<style>
    .transfer-page {
        padding: 60px 0;
        background: #f5f5f5;
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .transfer-card {
        display: flex;
        width: 980px;
        max-width: 100%;
        margin: 0 auto;
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.18);
    }

    .transfer-left {
        flex: 1;
        background: linear-gradient(135deg, #0f766e, #14b8a6);
        color: #fff;
        padding: 32px 28px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .transfer-left h2 {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
    }

    .transfer-left .sub {
        font-size: 15px;
        line-height: 1.6;
        opacity: 0.96;
        margin: 0;
    }

    .transfer-summary {
        margin-top: 10px;
        padding-top: 14px;
        border-top: 1px solid rgba(255, 255, 255, 0.25);
    }

    .transfer-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .transfer-label {
        color: rgba(255, 255, 255, 0.82);
    }

    .transfer-value {
        font-weight: 700;
        text-align: right;
    }

    .transfer-amount {
        margin-top: 14px;
        padding: 14px 16px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.12);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .transfer-amount .label {
        font-size: 13px;
        color: rgba(255, 255, 255, 0.85);
    }

    .transfer-amount .value {
        font-size: 20px;
        font-weight: 800;
    }

    .transfer-note {
        font-size: 13px;
        color: #ecfeff;
        line-height: 1.5;
    }

    .transfer-right {
        flex: 1.15;
        padding: 30px 24px;
        background: #fff;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .panel-title {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        color: #111827;
    }

    .bank-box {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 18px;
        background: #fafafa;
    }

    .bank-box__head {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .bank-box__icon {
        width: 54px;
        height: 54px;
        object-fit: contain;
        flex: 0 0 auto;
    }

    .bank-box__title {
        font-size: 18px;
        font-weight: 800;
        color: #111827;
        margin: 0;
    }

    .bank-list {
        display: grid;
        gap: 10px;
        margin: 0;
    }

    .bank-item {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        font-size: 14px;
        line-height: 1.5;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
    }

    .bank-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .bank-item strong {
        color: #111827;
    }

    .bank-item span:last-child {
        text-align: right;
        color: #374151;
        font-weight: 600;
    }

    .online-box {
        border-radius: 16px;
        padding: 18px;
        background: linear-gradient(135deg, #111827, #374151);
        color: #fff;
    }

    .online-box h3 {
        margin: 0 0 8px;
        font-size: 18px;
    }

    .online-box p {
        margin: 0 0 10px;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.9);
    }

    .online-pill {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        font-size: 13px;
        font-weight: 700;
    }

    .transfer-actions {
        margin-top: auto;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .transfer-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 12px 18px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.15s ease, opacity 0.15s ease;
    }

    .transfer-btn:hover {
        transform: translateY(-1px);
    }

    .transfer-btn-primary {
        background: #0f766e;
        color: #fff;
    }

    .transfer-btn-secondary {
        background: #fff;
        color: #111827;
        border: 1px solid #d1d5db;
    }

    .transfer-btn-disabled {
        background: #e5e7eb;
        color: #6b7280;
        pointer-events: none;
        cursor: default;
    }

    .transfer-footer {
        margin-top: 4px;
        font-size: 13px;
        color: #6b7280;
        line-height: 1.5;
    }

    @media (max-width: 900px) {
        .transfer-card {
            flex-direction: column;
        }

        .transfer-left,
        .transfer-right {
            padding: 22px 18px;
        }
    }

    @media (max-width: 480px) {
        .transfer-page {
            padding: 30px 0;
        }
    }
</style>

<section class="transfer-page">
    <div class="container">
        <div class="transfer-card">
            <div class="transfer-left">
                <h2>Thanh toán chuyển khoản</h2>
                <p class="sub">
                    Xin chào <strong><?php echo htmlspecialchars($customer_name, ENT_QUOTES, 'UTF-8'); ?></strong>,
                    vui lòng chuyển khoản theo thông tin bên phải để hoàn tất đơn hàng.
                </p>

                <div class="transfer-summary">
                    <div class="transfer-row">
                        <span class="transfer-label">Mã đơn hàng</span>
                        <span class="transfer-value"><?php echo $display_code !== '' ? htmlspecialchars($display_code, ENT_QUOTES, 'UTF-8') : '—'; ?></span>
                    </div>
                    <div class="transfer-row">
                        <span class="transfer-label">Người nhận</span>
                        <span class="transfer-value"><?php echo htmlspecialchars($customer_name, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="transfer-row">
                        <span class="transfer-label">Trạng thái</span>
                        <span class="transfer-value">Chờ chuyển khoản</span>
                    </div>

                    <div class="transfer-amount">
                        <div class="label">Tổng thanh toán</div>
                        <div class="value"><?php echo vnd_transfer_fake($total_amount); ?></div>
                    </div>
                    <div class="transfer-note">
                        Nội dung chuyển khoản: <strong><?php echo htmlspecialchars($transfer_content, ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>
                </div>

                <div class="transfer-actions">
                    <a href="index.php?page=cart" class="transfer-btn transfer-btn-secondary">Quay lại giỏ hàng</a>
                    <span class="transfer-btn transfer-btn-disabled">Thanh toán trực tuyến: đang cập nhật</span>
                </div>
            </div>

            <div class="transfer-right">
                <h3 class="panel-title">Thông tin chuyển khoản</h3>

                <div class="bank-box">
                    <div class="bank-box__head">
                        <img src="assets/images/payment/icons8-money-48.png" alt="Chuyển khoản" class="bank-box__icon">
                        <div>
                            <p class="bank-box__title">Ngân hàng nhận tiền</p>
                            <div class="transfer-footer">Thực hiện chuyển khoản đúng nội dung để hệ thống đối chiếu.</div>
                        </div>
                    </div>

                    <div class="bank-list">
                        <div class="bank-item">
                            <strong>Ngân hàng</strong>
                            <span><?php echo htmlspecialchars($bank_name, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="bank-item">
                            <strong>Số tài khoản</strong>
                            <span><?php echo htmlspecialchars($bank_account, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="bank-item">
                            <strong>Chủ tài khoản</strong>
                            <span><?php echo htmlspecialchars($bank_holder, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="bank-item">
                            <strong>Số tiền</strong>
                            <span><?php echo vnd_transfer_fake($total_amount); ?></span>
                        </div>
                        <div class="bank-item">
                            <strong>Nội dung</strong>
                            <span><?php echo htmlspecialchars($transfer_content, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="online-box">
                    <h3>Thanh toán trực tuyến</h3>
                    <p>
                        Tính năng thanh toán trực tuyến đang được cập nhật.
                        Hiện tại bạn chỉ cần sử dụng phương thức chuyển khoản ở trên.
                    </p>
                    <span class="online-pill">Chưa xử lý tiếp</span>
                </div>
            </div>
        </div>
    </div>
</section>
