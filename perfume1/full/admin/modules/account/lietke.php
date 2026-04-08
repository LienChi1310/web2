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

$account_keyword = isset($_GET['account_keyword']) ? trim($_GET['account_keyword']) : '';
$account_type_filter = null;
$account_status_filter = null;

// Parse account_type_filter - accept 0, 1, 2
if (isset($_GET['account_type_filter']) && $_GET['account_type_filter'] !== '') {
    $account_type_filter = (int)$_GET['account_type_filter'];
}

// Parse account_status_filter - accept -1, 0, 1
if (isset($_GET['account_status_filter']) && $_GET['account_status_filter'] !== '') {
    $account_status_filter = (int)$_GET['account_status_filter'];
}

// Handle sorting
$sort_column = 'account_id';
$sort_order = 'DESC';
$allowed_sorts = ['account_name', 'account_email', 'account_id'];

if (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sorts)) {
    $sort_column = $_GET['sort'];
    $sort_order = (isset($_GET['order']) && $_GET['order'] === 'ASC') ? 'ASC' : 'DESC';
}

// Build WHERE clause with filters
$where_clause = "WHERE 1=1";

if ($account_keyword !== '') {
    $keyword_safe = mysqli_real_escape_string($mysqli, $account_keyword);
    $where_clause .= " AND (account_email LIKE '%{$keyword_safe}%' OR account_name LIKE '%{$keyword_safe}%')";
}

if ($account_type_filter !== null) {
    $where_clause .= " AND account_type = {$account_type_filter}";
}

if ($account_status_filter !== null) {
    $where_clause .= " AND account_status = {$account_status_filter}";
}

$sql_account_list = "
    SELECT * 
    FROM account
    {$where_clause}
    ORDER BY $sort_column $sort_order
    LIMIT $begin,10
";

$query_account_list = mysqli_query($mysqli, $sql_account_list);

function account_status_badge($status)
{
    if ((int)$status === 1) {
        return '<span class="badge bg-success text-white">Hoạt động</span>';
    }
    if ((int)$status === 0) {
        return '<span class="badge bg-danger text-white">Đã khóa</span>';
    }
    if ((int)$status === -1) {
        return '<span class="badge bg-warning text-dark">Tạm khóa</span>';
    }
    return '<span class="badge bg-secondary text-white">Không xác định</span>';
}
?>

<div class="row">
    <div class="col">
        <div class="header__list d-flex space-between align-center">
            <h3 class="card-title" style="margin: 0;">Danh sách tài khoản</h3>
            <div class="action_group">
                <?php if (isset($_SESSION['account_type']) && (int)$_SESSION['account_type'] === 2) { ?>
                    <a href="index.php?action=account&query=account_add" class="button button-dark">
                        + Thêm tài khoản
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col">
        <form method="GET" action="index.php" class="d-flex align-items-center" style="gap:10px; flex-wrap:wrap;">
            <input type="hidden" name="action" value="account">
            <input type="hidden" name="query" value="account_list">

            <label class="mb-0">Loại tài khoản:</label>
            <select name="account_type_filter" class="form-control" style="width: 150px; height: 38px;">
                <option value="">-- Tất cả --</option>
                <option value="0" <?php echo ($account_type_filter === 0) ? 'selected' : ''; ?>>Khách hàng</option>
                <option value="1" <?php echo ($account_type_filter === 1) ? 'selected' : ''; ?>>Nhân viên</option>
                <option value="2" <?php echo ($account_type_filter === 2) ? 'selected' : ''; ?>>Quản trị viên</option>
            </select>

            <label class="mb-0">Tình trạng:</label>
            <select name="account_status_filter" class="form-control" style="width: 150px; height: 38px;">
                <option value="">-- Tất cả --</option>
                <option value="1" <?php echo ($account_status_filter === 1) ? 'selected' : ''; ?>>Hoạt động</option>
                <option value="0" <?php echo ($account_status_filter === 0) ? 'selected' : ''; ?>>Đã khóa</option>
                <option value="-1" <?php echo ($account_status_filter === -1) ? 'selected' : ''; ?>>Tạm khóa</option>
            </select>

            <button type="submit" class="btn btn-primary btn-sm">Áp dụng</button>

            <div style="flex: 1; text-align: right;">
                <div class="input__search p-relative" style="display: inline-block; width: 250px;">
                    <i class="icon-search p-absolute"></i>
                    <input type="text" id="accountSearchInput" name="account_keyword" class="form-control" placeholder="Tìm theo tên hoặc email" value="<?php echo htmlspecialchars($account_keyword); ?>">
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
                                <th width="60"></th>
                                <th width="60">
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th style="width: 50px; text-align: center;">STT</th>
                                <th style="cursor: pointer;"><a href="?action=account&query=account_list&sort=account_name&order=<?php echo ($sort_column === 'account_name' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&account_keyword=<?php echo isset($_GET['account_keyword']) ? urlencode($_GET['account_keyword']) : ''; ?>&account_type_filter=<?php echo $account_type_filter !== null ? $account_type_filter : ''; ?>&account_status_filter=<?php echo $account_status_filter !== null ? $account_status_filter : ''; ?>" style="color: inherit; text-decoration: none;">Tên người dùng <?php if ($sort_column === 'account_name') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th style="cursor: pointer;"><a href="?action=account&query=account_list&sort=account_email&order=<?php echo ($sort_column === 'account_email' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&account_keyword=<?php echo isset($_GET['account_keyword']) ? urlencode($_GET['account_keyword']) : ''; ?>&account_type_filter=<?php echo $account_type_filter !== null ? $account_type_filter : ''; ?>&account_status_filter=<?php echo $account_status_filter !== null ? $account_status_filter : ''; ?>" style="color: inherit; text-decoration: none;">Email <?php if ($sort_column === 'account_email') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th>Số điện thoại</th>
                                <th>Loại tài khoản</th>
                                <th>Tình trạng</th>
                                <?php if (isset($_SESSION['account_type']) && (int)$_SESSION['account_type'] === 2) { ?>
                                    <th width="260">Thao tác nhanh</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $stt = $begin + 1;
                            if ($query_account_list && mysqli_num_rows($query_account_list) > 0) { ?>
                                <?php while ($row = mysqli_fetch_array($query_account_list)) { ?>
                                    <tr>
                                        <td>
                                            <?php if (isset($_SESSION['account_type']) && (int)$_SESSION['account_type'] === 2) { ?>
                                                <a href="?action=account&query=account_edit&account_id=<?php echo $row['account_id']; ?>">
                                                    <div class="icon-edit">
                                                        <img class="w-100 h-100" src="images/icon-edit.png" alt="">
                                                    </div>
                                                </a>
                                            <?php } ?>
                                        </td>

                                        <td>
                                            <input type="checkbox" class="checkbox" id="<?php echo $row['account_id']; ?>">
                                        </td>

                                        <td style="text-align: center;"><?php echo $stt;
                                                                        $stt++; ?></td>

                                        <td><?php echo htmlspecialchars($row['account_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['account_email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['account_phone'] ?? ''); ?></td>
                                        <td><?php echo format_account_type($row['account_type']); ?></td>
                                        <td><?php echo account_status_badge($row['account_status']); ?></td>

                                        <?php if (isset($_SESSION['account_type']) && (int)$_SESSION['account_type'] === 2) { ?>
                                            <td>
                                                <div class="d-flex flex-wrap" style="gap:8px;">
                                                    <a
                                                        href="index.php?action=account&query=account_edit&account_id=<?php echo $row['account_id']; ?>"
                                                        class="btn btn-outline-primary btn-sm">
                                                        Sửa
                                                    </a>

                                                    <a
                                                        href="modules/account/xuly.php?reset_password=1&account_id=<?php echo $row['account_id']; ?>"
                                                        class="btn btn-outline-warning btn-sm"
                                                        onclick="return confirm('Đặt lại mật khẩu tài khoản này về 123456?');">
                                                        Reset mật khẩu
                                                    </a>

                                                    <?php if ((int)$row['account_status'] === 1) { ?>
                                                        <a
                                                            href="modules/account/xuly.php?toggle_status=1&account_id=<?php echo $row['account_id']; ?>&status=0"
                                                            class="btn btn-outline-danger btn-sm"
                                                            onclick="return confirm('Bạn có chắc muốn khóa tài khoản này?');">
                                                            Khóa
                                                        </a>
                                                    <?php } else { ?>
                                                        <a
                                                            href="modules/account/xuly.php?toggle_status=1&account_id=<?php echo $row['account_id']; ?>&status=1"
                                                            class="btn btn-outline-success btn-sm"
                                                            onclick="return confirm('Bạn có chắc muốn mở khóa tài khoản này?');">
                                                            Mở khóa
                                                        </a>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="<?php echo (isset($_SESSION['account_type']) && (int)$_SESSION['account_type'] === 2) ? '8' : '7'; ?>" class="text-center">
                                        <div class="alert alert-info" style="margin: 20px auto; text-align: center; width: 100%; font-style: italic; color: #000; font-weight: normal;">
                                            Không tìm thấy tài khoản nào phù hợp với bộ lọc của bạn
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination d-flex justify-center">
                    <?php
                    // Get total count for pagination
                    $sql_count = "SELECT COUNT(*) as total FROM account {$where_clause}";

                    $query_count = mysqli_query($mysqli, $sql_count);
                    $row_count = mysqli_fetch_array($query_count);
                    $total_records = $row_count['total'];
                    $totalpage = ceil($total_records / 10);

                    $currentLink = "index.php?action=account&query=account_list";
                    if ($account_keyword !== '') {
                        $currentLink .= "&account_keyword=" . urlencode($account_keyword);
                    }
                    if ($account_type_filter !== null) {
                        $currentLink .= "&account_type_filter=" . $account_type_filter;
                    }
                    if ($account_status_filter !== null) {
                        $currentLink .= "&account_status_filter=" . $account_status_filter;
                    }

                    if ($totalpage > 1) {
                    ?>
                        <ul class="pagination__items d-flex align-center justify-center">
                            <?php if ($page != 1) { ?>
                                <li class="pagination__item">
                                    <a class="d-flex align-center" href="<?php echo $currentLink . '&pagenumber=' . ($page - 1); ?>">
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

                            // Show current page and adjacent pages
                            for ($i = max(1, $page - 1); $i <= min($totalpage, $page + 1); $i++) {
                                $show_pages[$i] = true;
                            }

                            ksort($show_pages);
                            $prev_page = 0;

                            // Output pages with ... for gaps
                            foreach ($show_pages as $page_num => $val) {
                                // Show ... if there's a gap
                                if ($page_num - $prev_page > 1) {
                            ?>
                                    <li class="pagination__item">
                                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; color: #121212;">...</span>
                                    </li>
                                <?php
                                }
                                ?>
                                <li class="pagination__item">
                                    <a class="pagination__anchor <?php if ($page == $page_num) echo "active"; ?>" href="<?php echo $currentLink . '&pagenumber=' . $page_num; ?>">
                                        <?php echo $page_num; ?>
                                    </a>
                                </li>
                            <?php
                                $prev_page = $page_num;
                            } ?>

                            <?php if ($page != $totalpage && $totalpage > 0) { ?>
                                <li class="pagination__item">
                                    <a class="d-flex align-center" href="<?php echo $currentLink . '&pagenumber=' . ($page + 1); ?>">
                                        <img src="images/icon-nextlink.svg" alt="">
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var checkAll = document.getElementById("checkAll");
    var checkboxes = document.getElementsByClassName("checkbox");

    if (checkAll) {
        checkAll.addEventListener("click", function() {
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = checkAll.checked;
            }
        });
    }

    function showSuccessToast() {
        toast({
            title: "Success",
            message: "Cập nhật thành công",
            type: "success",
            duration: 0,
        });
    }

    function showErrorToast() {
        toast({
            title: "Error",
            message: "Không thể cập nhật tài khoản",
            type: "error",
            duration: 0,
        });
    }
</script>

<script>
    // Auto-search functionality
    var accountSearchInput = document.getElementById('accountSearchInput');
    var accountSearchForm = accountSearchInput ? accountSearchInput.closest('form') : null;
    var accountSearchTimeout;

    if (accountSearchInput && accountSearchForm) {
        accountSearchInput.addEventListener('keyup', function() {
            clearTimeout(accountSearchTimeout);
            accountSearchTimeout = setTimeout(function() {
                accountSearchForm.submit();
            }, 500);
        });
    }
</script>

<?php
if (isset($_GET['message']) && $_GET['message'] == 'success') {
    echo '<script>';
    echo 'showSuccessToast();';
    echo 'window.history.pushState(null, "", "index.php?action=account&query=account_list");';
    echo '</script>';
} elseif (isset($_GET['message']) && $_GET['message'] == 'error') {
    echo '<script>';
    echo 'showErrorToast();';
    echo 'window.history.pushState(null, "", "index.php?action=account&query=account_list");';
    echo '</script>';
}
?>
