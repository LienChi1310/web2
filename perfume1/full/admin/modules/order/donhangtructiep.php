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
$order_search = isset($_GET['order_search']) ? trim($_GET['order_search']) : '';

// Handle sorting
$sort_column = 'order_date';
$sort_order = 'DESC';
$allowed_sorts = ['order_date', 'account_name', 'order_id'];

if (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sorts)) {
    $sort_column = $_GET['sort'];
    $sort_order = (isset($_GET['order']) && $_GET['order'] === 'ASC') ? 'ASC' : 'DESC';
}

$url_status = ($order_status !== '') ? '&order_status=' . urlencode($order_status) : '';
$url_search = ($order_search !== '') ? '&order_search=' . urlencode($order_search) : '';

$where = [];
$where[] = "orders.order_type = 5"; // Direct/offline orders only

if ($order_status !== '') {
    $order_status_safe = mysqli_real_escape_string($mysqli, $order_status);
    $where[] = "orders.order_status = {$order_status_safe}";
}

if ($order_search !== '') {
    $order_search_safe = mysqli_real_escape_string($mysqli, $order_search);
    $where[] = "(orders.order_code LIKE '%{$order_search_safe}%' OR account.account_name LIKE '%{$order_search_safe}%')";
}

$where_sql = implode(' AND ', $where);

$sql_order_list = "
    SELECT orders.*, account.account_name
    FROM orders
    JOIN account ON orders.account_id = account.account_id
    WHERE {$where_sql}
    ORDER BY {$sort_column} {$sort_order}
    LIMIT {$begin},10
";
$query_order_list = mysqli_query($mysqli, $sql_order_list);

// Get total count for pagination
$sql_count = "
    SELECT COUNT(*) as total
    FROM orders
    JOIN account ON orders.account_id = account.account_id
    WHERE {$where_sql}
";
$query_count = mysqli_query($mysqli, $sql_count);
$row_count = mysqli_fetch_array($query_count);
$total_records = $row_count['total'];
$total_pages = ceil($total_records / 10);
?>
<div class="row">
    <div class="col">
        <div class="header__list d-flex space-between align-center">
            <h3 class="card-title" style="margin: 0;">Danh sách đơn hàng mua tại quầy</h3>
            <div class="action_group">
                <a href="?action=order&query=order_add" class="button button-dark">Tạo đơn hàng</a>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col">
        <form method="GET" action="index.php" class="d-flex align-items-center" style="gap:15px; flex-wrap:wrap;">
            <input type="hidden" name="action" value="order">
            <input type="hidden" name="query" value="order_live">

            <!-- Status Filter -->
            <select name="order_status" class="form-control" style="width: fit-content; min-width: 200px;" onchange="this.form.submit();">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="-1" <?php echo ($order_status === '-1') ? 'selected' : ''; ?>>Đơn hàng đã hủy</option>
                <option value="0" <?php echo ($order_status === '0') ? 'selected' : ''; ?>>Đang xử lý</option>
                <option value="1" <?php echo ($order_status === '1') ? 'selected' : ''; ?>>Đang chuẩn bị</option>
                <option value="2" <?php echo ($order_status === '2') ? 'selected' : ''; ?>>Đang giao hàng</option>
                <option value="3" <?php echo ($order_status === '3') ? 'selected' : ''; ?>>Đã giao hàng</option>
            </select>

            <!-- Search and Filter Button -->
            <input type="search" name="order_search" class="form-control" placeholder="Tìm mã đơn, tên nhân viên..."
                value="<?php echo htmlspecialchars($order_search); ?>" style="width: 250px; height: 38px;">

            <button type="submit" class="button button-dark">Áp dụng</button>
            <a href="index.php?action=order&query=order_live" class="button button-light">Xóa bộ lọc</a>
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
                                <th style="cursor: pointer;"><a href="?action=order&query=order_live&sort=order_date&order=<?php echo ($sort_column === 'order_date' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?><?php echo $url_status . $url_search; ?>" style="color: inherit; text-decoration: none;">Thời gian <?php if ($sort_column === 'order_date') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th style="cursor: pointer;"><a href="?action=order&query=order_live&sort=account_name&order=<?php echo ($sort_column === 'account_name' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?><?php echo $url_status . $url_search; ?>" style="color: inherit; text-decoration: none;">Nhân viên lên đơn <?php if ($sort_column === 'account_name') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th class="text-center">Tình trạng đơn hàng</th>
                                <th class="text-right">Tổng tiền</th>
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
                                            <a href="?action=order&query=order_detail&order_code=<?php echo $row['order_code']; ?>">
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
                                        <td class="text-center">
                                            <span class="col-span <?php echo format_status_style($row['order_status']); ?>">
                                                <?php echo format_order_status($row['order_status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-right"><?php echo number_format($row['total_amount']); ?>₫</td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center" style="padding: 20px;">Không có đơn hàng nào</td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Smart Pagination -->
                <?php if ($total_pages > 1) { ?>
                    <div class="pagination d-flex justify-center">
                        <ul class="pagination__items d-flex align-center justify-center">
                            <?php
                            // Previous button
                            if ($page > 1) {
                            ?>
                                <li class="pagination__item">
                                    <a class="d-flex align-center" href="?action=order&query=order_live&pagenumber=<?php echo $page - 1; ?><?php echo $url_status . $url_search; ?>&sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>">
                                        <img src="images/arrow-left.svg" alt="">
                                    </a>
                                </li>
                            <?php
                            }

                            // Smart pagination logic: show first 2, current±1, last 2, with ellipsis
                            $pages_to_show = [];

                            // First 2 pages
                            for ($i = 1; $i <= min(2, $total_pages); $i++) {
                                $pages_to_show[$i] = true;
                            }

                            // Current page and neighbors
                            for ($i = max(1, $page - 1); $i <= min($total_pages, $page + 1); $i++) {
                                $pages_to_show[$i] = true;
                            }

                            // Last 2 pages
                            for ($i = max(1, $total_pages - 1); $i <= $total_pages; $i++) {
                                $pages_to_show[$i] = true;
                            }

                            ksort($pages_to_show);
                            $prev = 0;

                            foreach ($pages_to_show as $p => $show) {
                                if ($p - $prev > 1) {
                                    echo '<li class="pagination__item"><span style="padding: 10px 5px;">...</span></li>';
                                }
                            ?>
                                <li class="pagination__item">
                                    <a class="pagination__anchor <?php if ($page == $p) echo "active"; ?>"
                                        href="?action=order&query=order_live&pagenumber=<?php echo $p; ?><?php echo $url_status . $url_search; ?>&sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>">
                                        <?php echo $p; ?>
                                    </a>
                                </li>
                            <?php
                                $prev = $p;
                            }

                            // Next button
                            if ($page < $total_pages) {
                            ?>
                                <li class="pagination__item">
                                    <a class="d-flex align-center" href="?action=order&query=order_live&pagenumber=<?php echo $page + 1; ?><?php echo $url_status . $url_search; ?>&sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>">
                                        <img src="images/icon-nextlink.svg" alt="">
                                    </a>
                                </li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

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

    // Auto-submit search on input
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
    echo '<script>showSuccessToast();</script>';
}
?>

<script>
    // Clean URL history
    window.history.pushState(null, "", "index.php?action=order&query=order_live");
</script>
