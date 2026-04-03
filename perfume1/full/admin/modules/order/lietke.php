<?php
if (isset($_GET['pagenumber'])) {
    $page = (int)$_GET['pagenumber'];
    $url_page = '&pagenumber=' . $page;
} else {
    $page = 1;
    $url_page = '';
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

$url_status = ($order_status !== '') ? '&order_status=' . urlencode($order_status) : '';
$url_date   = ($filter_date !== '') ? '&filter_date=' . urlencode($filter_date) : '';
$url_from   = ($date_from !== '') ? '&date_from=' . urlencode($date_from) : '';
$url_to     = ($date_to !== '') ? '&date_to=' . urlencode($date_to) : '';
$url_addr   = ($filter_addr !== '') ? '&filter_addr=' . urlencode($filter_addr) : '';

$where = [];
$where[] = "1=1";

if ($order_status !== '') {
    $order_status_int = (int)$order_status;
    $where[] = "orders.order_status = {$order_status_int}";
} else {
    $where[] = "orders.order_status >= -1";
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

$where_sql = implode(' AND ', $where);

$sql_order_list = "
    SELECT orders.*, account.account_name, delivery.delivery_address
    FROM orders
    JOIN account ON orders.account_id = account.account_id
    LEFT JOIN delivery ON orders.delivery_id = delivery.delivery_id
    WHERE {$where_sql}
    ORDER BY orders.order_id DESC
    LIMIT {$begin},10
";
$query_order_list = mysqli_query($mysqli, $sql_order_list);
?>
<div class="row">
    <div class="col">
        <div class="header__list d-flex space-between align-center">
            <h3 class="card-title" style="margin: 0;">Danh sách đơn hàng online</h3>
            <div class="action_group">
                <a href="#" class="button button-dark">Export</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="main-pane-top d-flex space-between align-center" style="padding-inline: 20px; gap: 12px; flex-wrap: wrap;">
                    <div class="input__search p-relative">
                        <form class="search-form" action="?action=order&query=order_search" method="POST">
                            <i class="icon-search p-absolute"></i>
                            <input type="search" name="order_search" class="form-control" placeholder="Search Here" title="Search here">
                        </form>
                    </div>

                    <form action="index.php" method="GET" class="d-flex align-center" style="gap: 10px; flex-wrap: wrap;">
                        <input type="hidden" name="action" value="order">
                        <input type="hidden" name="query" value="order_list">

                        <?php if ($order_status !== '') { ?>
                            <input type="hidden" name="order_status" value="<?php echo htmlspecialchars($order_status); ?>">
                        <?php } ?>

                        <div>
                            <label style="font-size: 13px; margin-bottom: 4px; display: block;">Lọc 1 ngày</label>
                            <input
                                type="date"
                                name="filter_date"
                                class="form-control"
                                value="<?php echo htmlspecialchars($filter_date); ?>"
                                style="min-width: 170px;">
                        </div>

                        <div>
                            <label style="font-size: 13px; margin-bottom: 4px; display: block;">Từ ngày</label>
                            <input
                                type="date"
                                name="date_from"
                                class="form-control"
                                value="<?php echo htmlspecialchars($date_from); ?>"
                                style="min-width: 170px;">
                        </div>

                        <div>
                            <label style="font-size: 13px; margin-bottom: 4px; display: block;">Đến ngày</label>
                            <input
                                type="date"
                                name="date_to"
                                class="form-control"
                                value="<?php echo htmlspecialchars($date_to); ?>"
                                style="min-width: 170px;">
                        </div>

                        <div>
                            <label style="font-size: 13px; margin-bottom: 4px; display: block;">Phường / địa chỉ</label>
                            <input
                                type="text"
                                name="filter_addr"
                                class="form-control"
                                placeholder="Lọc theo phường / địa chỉ giao hàng"
                                value="<?php echo htmlspecialchars($filter_addr); ?>"
                                style="min-width: 240px;">
                        </div>

                        <div style="display:flex; gap:8px; align-items:flex-end; padding-top: 20px;">
                            <button type="submit" class="btn btn-primary">Lọc</button>

                            <a href="index.php?action=order&query=order_list<?php echo $url_status; ?>" class="btn btn-light">
                                Xóa lọc
                            </a>
                        </div>
                    </form>

                    <div class="dropdown dropdown__item">
                        <button class="btn btn-outline-dark dropdown-toggle" type="button" id="dropdownMenuSizeButton2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php
                            if ($order_status !== '' && (int)$order_status === 0) {
                                echo "Đơn đang xử lý";
                            } elseif ($order_status !== '' && (int)$order_status === 1) {
                                echo "Đang chuẩn bị hàng";
                            } elseif ($order_status !== '' && (int)$order_status === 2) {
                                echo "Đang giao hàng";
                            } elseif ($order_status !== '' && (int)$order_status === 3) {
                                echo "Đã hoàn thành";
                            } elseif ($order_status !== '' && (int)$order_status === -1) {
                                echo "Đơn đã hủy";
                            } else {
                                echo "Tất cả trạng thái";
                            }
                            ?>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuSizeButton2">
                            <a class="dropdown-item" href="index.php?action=order&query=order_list<?php echo $url_date . $url_from . $url_to . $url_addr; ?>">Tất cả trạng thái</a>
                            <a class="dropdown-item" href="index.php?action=order&query=order_list&order_status=0<?php echo $url_date . $url_from . $url_to . $url_addr; ?>">Đang xử lý</a>
                            <a class="dropdown-item" href="index.php?action=order&query=order_list&order_status=1<?php echo $url_date . $url_from . $url_to . $url_addr; ?>">Đang chuẩn bị hàng</a>
                            <a class="dropdown-item" href="index.php?action=order&query=order_list&order_status=2<?php echo $url_date . $url_from . $url_to . $url_addr; ?>">Đang giao hàng</a>
                            <a class="dropdown-item" href="index.php?action=order&query=order_list&order_status=3<?php echo $url_date . $url_from . $url_to . $url_addr; ?>">Đã hoàn thành</a>
                            <a class="dropdown-item" href="index.php?action=order&query=order_list&order_status=-1<?php echo $url_date . $url_from . $url_to . $url_addr; ?>">Đã hủy</a>
                        </div>
                    </div>
                </div>

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
                                <th>Thời gian</th>
                                <th>Tên người đặt</th>
                                <th>Địa chỉ giao hàng</th>
                                <th>Loại đơn hàng</th>
                                <th class="text-center">Tình trạng đơn hàng</th>
                                <th>Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            if ($query_order_list && mysqli_num_rows($query_order_list) > 0) {
                                while ($row = mysqli_fetch_array($query_order_list)) {
                                    $i++;
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
                                        <td style="text-align: center;"><?php echo $i; ?></td>
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
                                        <td>
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

                            <?php for ($i = 1; $i <= $totalpage; $i++) { ?>
                                <li class="pagination__item">
                                    <a class="pagination__anchor <?php if ($page == $i) {
                                                                        echo "active";
                                                                    } ?>" href="<?php echo $baseLink . '&pagenumber=' . $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php } ?>

                            <?php if ($page != $totalpage) { ?>
                                <li class="pagination__item">
                                    <a class="d-flex align-center" href="<?php echo $baseLink . '&pagenumber=' . ($page + 1); ?>">
                                        <img src="images/icon-nextlink.svg" alt="">
                                    </a>
                                </li>
                            <?php } ?>
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
