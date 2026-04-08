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

$order_status = isset($_GET['order_status']) ? trim($_GET['order_status']) : '';
$filter_date  = isset($_GET['filter_date']) ? trim($_GET['filter_date']) : '';
$date_from    = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to      = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$filter_addr  = isset($_GET['filter_addr']) ? trim($_GET['filter_addr']) : '';
$order_search = isset($_GET['order_search']) ? trim($_GET['order_search']) : '';
$order_type   = isset($_GET['order_type']) ? trim($_GET['order_type']) : ''; // Filter: tất cả (empty) / 1 (COD) / 2 (MoMo)

// Logic: if filter_date is set, ignore date_from/date_to
if ($filter_date !== '') {
    $date_from = '';
    $date_to = '';
}
// Logic: if date_from or date_to is set, ignore filter_date
if ($date_from !== '' || $date_to !== '') {
    $filter_date = '';
}

// Handle sorting
$sort_column = 'order_date';
$sort_order = 'DESC';
$allowed_sorts = ['order_date', 'account_name', 'delivery_address', 'order_id'];

if (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sorts)) {
    $sort_column = $_GET['sort'];
    $sort_order = (isset($_GET['order']) && $_GET['order'] === 'ASC') ? 'ASC' : 'DESC';
}

$url_status = ($order_status !== '') ? '&order_status=' . urlencode($order_status) : '';
$url_date   = ($filter_date !== '') ? '&filter_date=' . urlencode($filter_date) : '';
$url_from   = ($date_from !== '') ? '&date_from=' . urlencode($date_from) : '';
$url_to     = ($date_to !== '') ? '&date_to=' . urlencode($date_to) : '';
$url_addr   = ($filter_addr !== '') ? '&filter_addr=' . urlencode($filter_addr) : '';
$url_search = ($order_search !== '') ? '&order_search=' . urlencode($order_search) : '';
$url_type   = ($order_type !== '') ? '&order_type=' . urlencode($order_type) : '';

$where = [];
$where[] = "1=1";

if ($order_status !== '') {
    $order_status_int = (int)$order_status;
    $where[] = "orders.order_status = {$order_status_int}";
}

if ($filter_date !== '') {
    $filter_date_safe = mysqli_real_escape_string($mysqli, $filter_date);
    $where[] = "DATE(orders.order_date) = '{$filter_date_safe}'";
}

if ($date_from !== '') {
    $date_from_safe = mysqli_real_escape_string($mysqli, $date_from);
    $where[] = "DATE(orders.order_date) >= '{$date_from_safe}'";
}

if ($date_to !== '') {
    $date_to_safe = mysqli_real_escape_string($mysqli, $date_to);
    $where[] = "DATE(orders.order_date) <= '{$date_to_safe}'";
}

if ($filter_addr !== '') {
    $filter_addr_safe = mysqli_real_escape_string($mysqli, $filter_addr);
    $where[] = "delivery.delivery_address LIKE '%{$filter_addr_safe}%'";
}

if ($order_search !== '') {
    $order_search_safe = mysqli_real_escape_string($mysqli, $order_search);
    $where[] = "(orders.order_code LIKE '%{$order_search_safe}%' OR account.account_name LIKE '%{$order_search_safe}%' OR delivery.delivery_address LIKE '%{$order_search_safe}%')";
}

// ⏸️ Filter: Bỏ VNPAY + filter theo loại đơn hàng
$where[] = "orders.order_type IN (1, 2)"; // COD + MoMo QR only
if ($order_type === '1') {  // Filter: COD only
    $where[] = "orders.order_type = 1";
} elseif ($order_type === '2') {  // Filter: MoMo QR only
    $where[] = "orders.order_type = 2";
}

$where_sql = implode(' AND ', $where);

$sql_order_list = "
    SELECT orders.*, account.account_name, delivery.delivery_address
    FROM orders
    JOIN account ON orders.account_id = account.account_id
    LEFT JOIN delivery ON orders.delivery_id = delivery.delivery_id
    WHERE {$where_sql}
    ORDER BY {$sort_column} {$sort_order}
    LIMIT {$begin},10
";
$query_order_list = mysqli_query($mysqli, $sql_order_list);
?>
<div class="row">
    <div class="col">
        <div class="header__list d-flex space-between align-center">
            <h3 class="card-title" style="margin: 0;">Danh sách đơn hàng online</h3>
            <div class="action_group">
                <a href="modules/order/export.php" class="button button-dark">Export</a>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col">
        <form method="GET" action="index.php" class="d-flex align-items-center" style="gap:15px; flex-wrap:wrap;">
            <input type="hidden" name="action" value="order">
            <input type="hidden" name="query" value="order_list">

            <!-- Filter Date Options with Radio -->
            <div style="display: flex; align-items: flex-end; gap: 15px; flex-wrap: wrap;">
                <!-- Option 1: Filter by single date -->
                <div style="display: flex; align-items: flex-end; gap: 8px;">
                    <label style="margin-bottom: 0; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                        <input type="radio" name="date_filter_type" value="single" <?php echo ($date_from === '' && $date_to === '' ? 'checked' : ''); ?> onchange="document.getElementById('dateRangeFields').style.opacity = '0.5'; document.getElementById('dateRangeFields').style.pointerEvents = 'none'; document.getElementById('singleDateField').style.opacity = '1'; document.getElementById('singleDateField').style.pointerEvents = 'auto';">
                        <span style="font-size: 12px;">Lọc theo ngày</span>
                    </label>
                    <div id="singleDateField" style="opacity: <?php echo ($filter_date !== '' ? '1' : '0.5'); ?>; pointer-events: <?php echo ($filter_date !== '' ? 'auto' : 'none'); ?>; transition: opacity 0.3s;">
                        <input type="date" name="filter_date" class="form-control" value="<?php echo htmlspecialchars($filter_date); ?>" style="min-width: 150px; height: 38px;">
                    </div>
                </div>

                <!-- Option 2: Filter by date range -->
                <div style="display: flex; align-items: flex-end; gap: 8px;">
                    <label style="margin-bottom: 0; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                        <input type="radio" name="date_filter_type" value="range" <?php echo ($date_from !== '' || $date_to !== '' ? 'checked' : ''); ?> onchange="document.getElementById('singleDateField').style.opacity = '0.5'; document.getElementById('singleDateField').style.pointerEvents = 'none'; document.getElementById('dateRangeFields').style.opacity = '1'; document.getElementById('dateRangeFields').style.pointerEvents = 'auto';">
                        <span style="font-size: 12px;">Từ ngày</span>
                    </label>
                    <div id="dateRangeFields" style="display: flex; gap: 8px; opacity: <?php echo ($date_from !== '' || $date_to !== '' ? '1' : '0.5'); ?>; pointer-events: <?php echo ($date_from !== '' || $date_to !== '' ? 'auto' : 'none'); ?>; transition: opacity 0.3s;">
                        <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>" style="min-width: 150px; height: 38px;">
                        <label style="margin-bottom: 0; display: flex; align-items: center; font-size: 12px;">đến</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>" style="min-width: 150px; height: 38px;">
                    </div>
                </div>
            </div>

            <!-- Address Filter -->
            <div>
                <input type="text" name="filter_addr" class="form-control" placeholder="Phường / địa chỉ" value="<?php echo htmlspecialchars($filter_addr); ?>" style="min-width: 200px; height: 38px; border: 1px solid #ddd;">
            </div>

            <!-- Filter Button - Only for date options -->
            <button type="submit" class="btn btn-primary btn-sm" style="height: 38px;">Lọc</button>
            <a href="index.php?action=order&query=order_list" class="btn btn-light btn-sm" style="height: 38px; display: flex; align-items: center;">Xóa lọc</a>
        </form>
    </div>
</div>

<div class="row mb-3">
    <div class="col">
        <form method="GET" action="index.php" class="d-flex align-items-center" style="gap:10px; flex-wrap:wrap;">
            <input type="hidden" name="action" value="order">
            <input type="hidden" name="query" value="order_list">

            <!-- Preserve date filter values -->
            <?php if ($filter_date !== '') { ?>
                <input type="hidden" name="filter_date" value="<?php echo htmlspecialchars($filter_date); ?>">
                <input type="hidden" name="date_filter_type" value="single">
            <?php } ?>
            <?php if ($date_from !== '') { ?>
                <input type="hidden" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                <input type="hidden" name="date_filter_type" value="range">
            <?php } ?>
            <?php if ($date_to !== '') { ?>
                <input type="hidden" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                <input type="hidden" name="date_filter_type" value="range">
            <?php } ?>
            <?php if ($filter_addr !== '') { ?>
                <input type="hidden" name="filter_addr" value="<?php echo htmlspecialchars($filter_addr); ?>">
            <?php } ?>

            <label class="mb-0">Tình trạng:</label>
            <select name="order_status" class="form-control" style="width: 200px;" onchange="this.form.submit();">
                <option value="">-- Tất cả --</option>
                <option value="0" <?php echo (isset($_GET['order_status']) && $_GET['order_status'] === '0') ? 'selected' : ''; ?>>Đang xử lý</option>
                <option value="1" <?php echo (isset($_GET['order_status']) && $_GET['order_status'] === '1') ? 'selected' : ''; ?>>Đang chuẩn bị hàng</option>
                <option value="2" <?php echo (isset($_GET['order_status']) && $_GET['order_status'] === '2') ? 'selected' : ''; ?>>Đang giao hàng</option>
                <option value="3" <?php echo (isset($_GET['order_status']) && $_GET['order_status'] === '3') ? 'selected' : ''; ?>>Đã hoàn thành</option>
                <option value="-1" <?php echo (isset($_GET['order_status']) && $_GET['order_status'] === '-1') ? 'selected' : ''; ?>>Đã hủy</option>
            </select>

            <label class="mb-0">Loại đơn:</label>
            <select name="order_type" class="form-control" style="width: 200px;" onchange="this.form.submit();">
                <option value="">-- Tất cả --</option>
                <option value="1" <?php echo (isset($_GET['order_type']) && $_GET['order_type'] === '1') ? 'selected' : ''; ?>>COD (Thanh toán khi nhận)</option>
                <option value="2" <?php echo (isset($_GET['order_type']) && $_GET['order_type'] === '2') ? 'selected' : ''; ?>>MoMo QR</option>
            </select>

            <div style="flex: 1; text-align: right;">
                <div class="input__search p-relative" style="display: inline-block; width: 250px;">
                    <i class="icon-search p-absolute"></i>
                    <input type="search" name="order_search" class="form-control" placeholder="Tìm kiếm..." value="<?php echo isset($_GET['order_search']) ? htmlspecialchars($_GET['order_search']) : ''; ?>" title="Search by order code, customer name, or address" style="height: 38px;">
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover table-action">
                        <thead>
                            <tr>
                                <th></th>
                                <th>
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th style="width: 50px; text-align: center;">STT</th>
                                <th>Mã đơn hàng</th>
                                <th style="cursor: pointer;"><a href="?action=order&query=order_list&sort=order_date&order=<?php echo ($sort_column === 'order_date' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?><?php echo $url_date . $url_from . $url_to . $url_addr . $url_status; ?>" style="color: inherit; text-decoration: none;">Thời gian <?php if ($sort_column === 'order_date') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th style="cursor: pointer;"><a href="?action=order&query=order_list&sort=account_name&order=<?php echo ($sort_column === 'account_name' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?><?php echo $url_date . $url_from . $url_to . $url_addr . $url_status; ?>" style="color: inherit; text-decoration: none;">Tên người đặt <?php if ($sort_column === 'account_name') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th style="cursor: pointer;"><a href="?action=order&query=order_list&sort=delivery_address&order=<?php echo ($sort_column === 'delivery_address' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?><?php echo $url_date . $url_from . $url_to . $url_addr . $url_status; ?>" style="color: inherit; text-decoration: none;">Địa chỉ giao hàng <?php if ($sort_column === 'delivery_address') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th>Loại đơn hàng</th>
                                <th class="text-center">Tình trạng đơn hàng</th>
                                <th style="display:none;">Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stt = $begin + 1;
                            if ($query_order_list && mysqli_num_rows($query_order_list) > 0) {
                                while ($row = mysqli_fetch_array($query_order_list)) {
                            ?>
                                    <tr>
                                        <td>
                                            <a href="?action=order&query=order_detail_online&order_code=<?php echo $row['order_code']; ?>">
                                                <div class="icon-edit">
                                                    <img class="w-100 h-100" src="images/icon-view.png" alt="">
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="checkbox" onclick="testChecked(); getCheckedCheckboxes();" id="<?php echo $row['order_code']; ?>">
                                        </td>
                                        <td style="text-align: center;"><?php echo $stt;
                                                                        $stt++; ?></td>
                                        <td><?php echo $row['order_code']; ?></td>
                                        <td><?php echo $row['order_date']; ?></td>
                                        <td><?php echo htmlspecialchars($row['account_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['delivery_address'] ?? ''); ?></td>
                                        <td><?php echo format_order_type($row['order_type']); ?></td>
                                        <td class="text-center">
                                            <span class="col-span <?php echo format_status_style($row['order_status']); ?>">
                                                <?php echo format_order_status($row['order_status']); ?>
                                            </span>
                                        </td>
                                        <!-- ⏸️ HIDDEN: Delete button (use hủy đơn instead) -->
                                        <td style="display:none;">
                                            <a href="javascript:void(0);" onclick="confirmDeleteOrder(<?php echo $row['order_code']; ?>)">
                                                Xóa
                                            </a>
                                        </td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="9" class="text-center">Không có đơn hàng phù hợp.</td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination d-flex justify-center">
                    <?php
                    $sql_count = "
                        SELECT orders.order_id
                        FROM orders
                        JOIN account ON orders.account_id = account.account_id
                        LEFT JOIN delivery ON orders.delivery_id = delivery.delivery_id
                        WHERE {$where_sql}
                    ";
                    $query_pages = mysqli_query($mysqli, $sql_count);
                    $row_count = $query_pages ? mysqli_num_rows($query_pages) : 0;
                    $totalpage = ceil($row_count / 10);

                    $baseLink = "index.php?action=order&query=order_list{$url_status}{$url_date}{$url_from}{$url_to}{$url_addr}";

                    if ($totalpage > 1) {
                    ?>
                        <ul class="pagination__items d-flex align-center justify-center">
                            <?php if ($page != 1) { ?>
                                <li class="pagination__item">
                                    <a class="d-flex align-center" href="<?php echo $baseLink . '&pagenumber=' . ($page - 1); ?>">
                                        <img src="images/arrow-left.svg" alt="">
                                    </a>
                                </li>
                            <?php } ?>

                            <?php
                            // Smart pagination with ellipsis - Only show relevant pages
                            $show_pages = array();

                            // Always show first 2 pages
                            for ($i = 1; $i <= min(2, $totalpage); $i++) {
                                $show_pages[$i] = true;
                            }

                            // Always show last 2 pages
                            for ($i = max(1, $totalpage - 1); $i <= $totalpage; $i++) {
                                $show_pages[$i] = true;
                            }

                            // Show current page and adjacent
                            for ($i = max(1, $page - 1); $i <= min($totalpage, $page + 1); $i++) {
                                $show_pages[$i] = true;
                            }

                            ksort($show_pages);
                            $prev_page = 0;

                            foreach ($show_pages as $page_num => $val) {
                                if ($page_num - $prev_page > 1) {
                            ?>
                                    <li class="pagination__item">
                                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; color: #121212;">...</span>
                                    </li>
                                <?php
                                }
                                ?>
                                <li class="pagination__item">
                                    <a class="pagination__anchor <?php if ($page == $page_num) echo "active"; ?>" href="<?php echo $baseLink . '&pagenumber=' . $page_num; ?>">
                                        <?php echo $page_num; ?>
                                    </a>
                                </li>
                            <?php
                                $prev_page = $page_num;
                            } ?>

                            <?php if ($page != $totalpage) { ?>
                                <li class="pagination__item">
                                    <a class="d-flex align-center" href="<?php echo $baseLink . '&pagenumber=' . ($page + 1); ?>">
                                        <img src="images/icon-nextlink.svg" alt="">
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
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
    var orderSearchInput = document.querySelector('input[name="order_search"]');
    var searchForm = orderSearchInput ? orderSearchInput.closest('form') : null;
    var searchTimeout;

    if (orderSearchInput && searchForm) {
        orderSearchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                searchForm.submit();
            }, 500);
        });
    }
</script>

<div class="dialog__control">
    <div class="control__box">
        <a href="modules/order/xuly.php?confirm=1" class="button__control" id="btnConfirm">Duyệt đơn hàng</a>
        <a href="modules/order/xuly.php?cancel=1" class="button__control" id="btnCancel">Hủy đơn hàng</a>
    </div>
</div>

<script>
    var btnConfirm = document.getElementById("btnConfirm");
    var btnCancel = document.getElementById("btnCancel");
    var checkAll = document.getElementById("checkAll");
    var checkboxes = document.getElementsByClassName("checkbox");
    var dialogControl = document.querySelector('.dialog__control');

    checkAll.addEventListener("click", function() {
        if (checkAll.checked) {
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = true;
            }
        } else {
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
        btnCancel.href = "modules/order/xuly.php?cancel=1&data=" + JSON.stringify(checkedIds);
    }

    function confirmDeleteOrder(orderCode) {
        if (confirm("Bạn có chắc chắn muốn xoá đơn " + orderCode + " ?")) {
            window.location.href = "modules/order/xuly.php?delete_order=1&order_code=" + orderCode;
        }
    }

    function showSuccessToast(message) {
        toast({
            title: "Success",
            message: message || "Cập nhật thành công",
            type: "success",
            duration: 0,
        });
    }

    function showErrorToast(message) {
        toast({
            title: "Error",
            message: message || "Không thể thực thi yêu cầu",
            type: "error",
            duration: 0,
        });
    }
</script>

<?php
if (isset($_GET['message'])) {
    if ($_GET['message'] == 'success') {
        echo '<script>showSuccessToast("Cập nhật trạng thái thành công");</script>';
    } elseif ($_GET['message'] == 'delete_success') {
        echo '<script>showSuccessToast("Xoá đơn thành công");</script>';
    } elseif ($_GET['message'] == 'cannot_delete') {
        echo '<script>showErrorToast("Không thể xoá đơn đã hoàn thành/đang giao hàng");</script>';
    }
}
?>

<script>
    window.history.pushState(
        null,
        "",
        "index.php?action=order&query=order_list<?php echo $url_status . $url_date . $url_from . $url_to . $url_addr . $url_page; ?>"
    );
</script>
