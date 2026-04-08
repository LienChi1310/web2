<?php
if (isset($_GET['pagenumber'])) {
    $page = (int)$_GET['pagenumber'];
} else {
    $page = 1;
}

if ($page <= 1) {
    $begin = 0;
    $page = 1;
} else {
    $begin = ($page * 10) - 10;
}

$payment_search = isset($_GET['payment_search']) ? trim($_GET['payment_search']) : '';

// Handle sorting - only MoMo for now
// ⏸️ TEMPORARILY: Show MoMo only (VNPAY feature paused)
$sort_order = 'DESC';

// ⏸️ TEMPORARILY HIDDEN: VNPAY payment filter
// if (isset($_GET['payment_type']) && $_GET['payment_type'] == 'vnpay') { ... }

// Active: MoMo Payment History (default)
$sort_column_order = 'momo_id DESC';
if ($payment_search !== '') {
    $payment_search_safe = mysqli_real_escape_string($mysqli, $payment_search);
    $sql_payment_list = "SELECT * FROM momo WHERE order_code LIKE '%{$payment_search_safe}%' ORDER BY $sort_column_order LIMIT $begin,10";
} else {
    $sql_payment_list = "SELECT * FROM momo ORDER BY $sort_column_order LIMIT $begin,10";
}
$query_payment_list = mysqli_query($mysqli, $sql_payment_list);

/* ⏸️ TEMPORARILY HIDDEN: VNPAY Payment History
else {
    $sort_column_order = 'vnp_paydate DESC';
    if ($payment_search !== '') {
        $payment_search_safe = mysqli_real_escape_string($mysqli, $payment_search);
        $sql_payment_list = "SELECT * FROM vnpay WHERE order_code LIKE '%{$payment_search_safe}%' ORDER BY $sort_column_order LIMIT $begin,10";
    } else {
        $sql_payment_list = "SELECT * FROM vnpay ORDER BY $sort_column_order LIMIT $begin,10";
    }
    $query_payment_list = mysqli_query($mysqli, $sql_payment_list);
}
*/
?>
<div class="row">
    <div class="col">
        <div class="header__list d-flex space-between align-center">
            <h3 class="card-title" style="margin: 0;">Lịch sử thanh toán</h3>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col">
        <form method="GET" action="index.php" class="d-flex align-items-center" style="gap:10px; flex-wrap:wrap;">
            <input type="hidden" name="action" value="order">
            <input type="hidden" name="query" value="order_payment">

            <label class="mb-0">Cổng thanh toán:</label>
            <select name="payment_type" class="form-control" style="width: 150px;" disabled>
                <!-- ⏸️ TEMPORARILY: Show MoMo only -->
                <option value="momo">MoMo</option>
                <!-- ⏸️ TEMPORARILY HIDDEN: VNPAY option
                <option value="vnpay">VNPAY</option>
                -->
            </select>

            <div style="flex: 1; text-align: right;">
                <div class="input__search p-relative" style="display: inline-block; width: 250px;">
                    <i class="icon-search p-absolute"></i>
                    <input type="search" name="payment_search" class="form-control" placeholder="Tìm kiếm mã đơn..." value="<?php echo isset($_GET['payment_search']) ? htmlspecialchars($_GET['payment_search']) : ''; ?>" title="Search by order code" style="height: 38px;">
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">

                <?php
                // ⏸️ TEMPORARILY: Show MoMo only
                ?>
                <table class="table table-hover table-action">
                    <thead>
                        <tr>
                            <th></th>
                            <th>
                                <input type="checkbox" id="checkAll">
                            </th>
                            <th style="width: 50px; text-align: center;">STT</th>
                            <th>Mã đơn hàng</th>
                            <th>Thời gian</th>
                            <th>Tổng tiền</th>
                            <th>Thẻ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stt = $begin + 1;
                        while ($row = mysqli_fetch_array($query_payment_list)) {
                        ?>
                            <tr>
                                <td>
                                    <a href="index.php?action=order&query=order_detail&order_code=<?php echo $row['order_code'] ?>">
                                        <div class="icon-edit">
                                            <img class="w-100 h-100" src="images/icon-view.png" alt="">
                                        </div>
                                    </a>
                                </td>
                                <td>
                                    <input type="checkbox" class="checkbox" onclick="testChecked(); getCheckedCheckboxes();" id="<?php echo $row['order_code'] ?>">
                                </td>
                                <td style="text-align: center;"><?php echo $stt;
                                                                $stt++; ?></td>
                                <td><?php echo $row['order_code'] ?></td>
                                <td><?php echo $row['payment_date'] ?></td>
                                <td><?php echo number_format($row['momo_amount']) ?>đ</td>
                                <td><?php echo $row['pay_type'] ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                <!-- ⏸️ TEMPORARILY HIDDEN: VNPAY Payment History table -->
            </div>
            <div class="pagination d-flex justify-center">
                <?php
                // ⏸️ TEMPORARILY: Show MoMo pagination only
                $sql_pay_list = "SELECT * FROM momo ORDER BY momo_id DESC";

                /* ⏸️ TEMPORARILY HIDDEN: VNPAY pagination
                    if (isset($_GET['payment_type']) && $_GET['payment_type'] == 'momo') {
                        $sql_pay_list = "SELECT * FROM momo ORDER BY momo_id DESC";
                    } else {
                        $sql_pay_list = "SELECT * FROM vnpay ORDER BY vnp_id DESC";
                    }
                    */

                $query_pages = mysqli_query($mysqli, $sql_pay_list);
                $row_count = mysqli_num_rows($query_pages);
                $totalpage = ceil($row_count / 10);
                $currentLink = $_SERVER['REQUEST_URI'];
                if ($totalpage > 1) {
                ?>
                    <ul class="pagination__items d-flex align-center justify-center">
                        <?php
                        if ($page != 1) {
                        ?>
                            <li class="pagination__item">
                                <a class="d-flex align-center" href="<?php echo $currentLink ?>&pagenumber=<?php echo $i + 1 ?>">
                                    <img src="images/arrow-left.svg" alt="">
                                </a>
                            </li>
                        <?php
                        }
                        ?>
                        <?php
                        for ($i = 1; $i <= $totalpage; $i++) {
                        ?>
                            <li class="pagination__item">
                                <a class="pagination__anchor <?php if ($page == $i) {
                                                                    echo "active";
                                                                } ?>" href="<?php echo $currentLink ?>&pagenumber=<?php echo $i ?>"><?php echo $i ?></a>
                            </li>
                        <?php
                        }
                        ?>
                        <?php
                        if ($page != $totalpage) {
                        ?>
                            <li class="pagination__item">
                                <a class="d-flex align-center" href="<?php echo $currentLink ?>&pagenumber=<?php echo $i ?>">
                                    <img src="images/icon-nextlink.svg" alt="">
                                </a>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                <?php
                } elseif ($totalpage == 0) {
                ?>
                    <div class="w-100 text-center">
                        <p class="color-t-red">Không có đơn hàng nào cần xử lý !</p>
                    </div>
                <?php
                }
                ?>

            </div>
        </div>
    </div>
</div>
</div>

<!-- Auto-submit search -->
<script>
    var paymentSearchInput = document.querySelector('input[name="payment_search"]');
    var searchForm = paymentSearchInput ? paymentSearchInput.closest('form') : null;
    var searchTimeout;

    if (paymentSearchInput && searchForm) {
        paymentSearchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                searchForm.submit();
            }, 500);
        });
    }
</script>

<div class="dialog__control">
    <div class="control__box">
        <a href="modules/order/xuly.php?reverse=1" class="button__control" id="btnCancel">Hoàn tiền</a>
    </div>
</div>
<script>
    var btnConfirm = document.getElementById("btnConfirm");
    var btnCancel = document.getElementById("btnCancel");
    var checkAll = document.getElementById("checkAll");
    var checkboxes = document.getElementsByClassName("checkbox");
    var dialogControl = document.querySelector('.dialog__control');
    // Thêm sự kiện click cho checkbox checkAll
    checkAll.addEventListener("click", function() {
        // Nếu checkbox checkAll được chọn
        if (checkAll.checked) {
            // Đặt thuộc tính "checked" cho tất cả các checkbox còn lại
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = true;
            }
        } else {
            // Bỏ thuộc tính "checked" cho tất cả các checkbox còn lại
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = false;
            }
        }
        testChecked();
        getCheckedCheckboxes();
    });

    function testChecked() {
        var count = 0;
        for (let i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                count++;
                console.log(count);
            }
        }
        if (count > 0) {
            dialogControl.classList.add('active');
        } else {
            dialogControl.classList.remove('active');
            checkAll.checked = false;
        }
    }

    function getCheckedCheckboxes() {
        var checkeds = document.querySelectorAll('.checkbox:checked');
        var checkedIds = [];
        for (var i = 0; i < checkeds.length; i++) {
            checkedIds.push(checkeds[i].id);
        }
        btnConfirm.href = "modules/order/xuly.php?confirm=1&data=" + JSON.stringify(checkedIds);
        btnCancel.href = "modules/order/xuly.php?payment='vnpay'reverse=1&data=" + JSON.stringify(checkedIds);
    }
</script>

<script>
    window.history.pushState(null, "", "index.php?action=order&query=order_payment");
</script>
