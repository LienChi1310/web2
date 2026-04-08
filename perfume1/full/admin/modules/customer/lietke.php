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

// Handle search filter
$search_filter = '';
$search_keyword = '';
if (isset($_GET['customer_search']) && $_GET['customer_search'] !== '') {
    $search_keyword = $_GET['customer_search'];
    $search_filter = " AND (customer_name LIKE '%" . mysqli_real_escape_string($mysqli, $search_keyword) . "%' OR customer_email LIKE '%" . mysqli_real_escape_string($mysqli, $search_keyword) . "%' OR customer_phone LIKE '%" . mysqli_real_escape_string($mysqli, $search_keyword) . "%')";
}

// Handle sorting
$sort_column = 'customer_id';
$sort_order = 'DESC';
$allowed_sorts = ['customer_name', 'customer_email', 'customer_phone', 'customer_id'];

if (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sorts)) {
    $sort_column = $_GET['sort'];
    $sort_order = (isset($_GET['order']) && $_GET['order'] === 'ASC') ? 'ASC' : 'DESC';
}

$sql_customer_list = "SELECT * FROM customer WHERE 1=1 $search_filter ORDER BY $sort_column $sort_order LIMIT $begin,10";
$query_customer_list = mysqli_query($mysqli, $sql_customer_list);
?>
<div class="row">
    <div class="col">
        <div class="header__list d-flex space-between align-center">
            <h3 class="card-title" style="margin: 0;">Danh sách khách hàng</h3>
            <div class="action_group">
                <a href="modules/customer/export.php" class="button button-dark">Export</a>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col">
        <form method="GET" action="index.php" class="d-flex align-items-center" style="gap:10px; flex-wrap:wrap;">
            <input type="hidden" name="action" value="customer">
            <input type="hidden" name="query" value="customer_list">

            <div style="flex: 1; text-align: right;">
                <div class="input__search p-relative" style="display: inline-block; width: 250px;">
                    <i class="icon-search p-absolute"></i>
                    <input type="text" id="customerSearchInput" name="customer_search" class="form-control" placeholder="Tìm kiếm khách hàng..." value="<?php echo isset($_GET['customer_search']) ? htmlspecialchars($_GET['customer_search']) : ''; ?>">
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
                                <th>
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th style="width: 50px; text-align: center;">STT</th>
                                <th style="cursor: pointer;"><a href="?action=customer&query=customer_list&sort=customer_name&order=<?php echo ($sort_column === 'customer_name' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&customer_search=<?php echo isset($_GET['customer_search']) ? urlencode($_GET['customer_search']) : ''; ?>" style="color: inherit; text-decoration: none;">Tên khách hàng <?php if ($sort_column === 'customer_name') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th>Giới tính</th>
                                <th style="cursor: pointer;"><a href="?action=customer&query=customer_list&sort=customer_email&order=<?php echo ($sort_column === 'customer_email' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&customer_search=<?php echo isset($_GET['customer_search']) ? urlencode($_GET['customer_search']) : ''; ?>" style="color: inherit; text-decoration: none;">Email <?php if ($sort_column === 'customer_email') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th style="cursor: pointer;"><a href="?action=customer&query=customer_list&sort=customer_phone&order=<?php echo ($sort_column === 'customer_phone' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&customer_search=<?php echo isset($_GET['customer_search']) ? urlencode($_GET['customer_search']) : ''; ?>" style="color: inherit; text-decoration: none;">Số điện thoại <?php if ($sort_column === 'customer_phone') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th>Địa chỉ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stt = $begin + 1;
                            while ($row = mysqli_fetch_array($query_customer_list)) {
                            ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="checkbox" onclick="testChecked(); getCheckedCheckboxes();" id="<?php echo $row['customer_id'] ?>">
                                    </td>
                                    <td style="text-align: center;"><?php echo $stt;
                                                                    $stt++; ?></td>
                                    <td><?php echo $row['customer_name'] ?></td>
                                    <td><?php echo format_gender($row['customer_gender']) ?></td>
                                    <td><?php echo $row['customer_email'] ?></td>
                                    <td><?php echo $row['customer_phone'] ?></td>
                                    <td><?php echo $row['customer_address'] ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination d-flex justify-center">
                    <?php

                    $sql_customer = "SELECT * FROM customer WHERE 1=1 $search_filter ORDER BY customer_id DESC";
                    $query_pages = mysqli_query($mysqli, $sql_customer);

                    $row_count = mysqli_num_rows($query_pages);
                    $totalpage = ceil($row_count / 10);
                    $currentLink = "index.php?action=customer&query=customer_list";
                    if (isset($_GET['customer_search']) && $_GET['customer_search'] !== '') {
                        $currentLink .= "&customer_search=" . urlencode($_GET['customer_search']);
                    }
                    if ($totalpage > 1) {
                    ?>
                        <ul class="pagination__items d-flex align-center justify-center">
                            <?php
                            if ($page != 1) {
                            ?>
                                <li class="pagination__item">
                                    <a class="d-flex align-center" href="<?php echo $currentLink . '&pagenumber=' . ($page - 1); ?>">
                                        <img src="images/arrow-left.svg" alt="">
                                    </a>
                                </li>
                            <?php
                            }
                            ?>
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
                                    <a class="pagination__anchor <?php if ($page == $page_num) echo "active"; ?>" href="<?php echo $currentLink . '&pagenumber=' . $page_num; ?>"><?php echo $page_num; ?></a>
                                </li>
                            <?php
                                $prev_page = $page_num;
                            } ?>
                            <?php
                            if ($page != $totalpage && $totalpage > 0) {
                            ?>
                                <li class="pagination__item">
                                    <a class="d-flex align-center" href="<?php echo $currentLink . '&pagenumber=' . ($page + 1); ?>">
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
                        <div class="alert alert-info" style="margin: 20px auto; text-align: center; width: 100%; font-style: italic; color: #000; font-weight: normal;">
                            Không tìm thấy khách hàng nào phù hợp với bộ lọc của bạn
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
        <a href="#" class="button__control btn__wanning" id="btnDelete" onclick="return confirm('Bạn có thực sự muốn xóa thông tin khách hàng này không?')">Xóa</a>
    </div>
</div>

<script>
    // Auto-search functionality
    var customerSearchInput = document.getElementById('customerSearchInput');
    var customerSearchForm = customerSearchInput ? customerSearchInput.closest('form') : null;
    var customerSearchTimeout;

    if (customerSearchInput && customerSearchForm) {
        customerSearchInput.addEventListener('keyup', function() {
            clearTimeout(customerSearchTimeout);
            customerSearchTimeout = setTimeout(function() {
                customerSearchForm.submit();
            }, 500);
        });
    }
</script>

<script>
    var btnDelete = document.getElementById("btnDelete");
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

    console.log(checkboxes[0]);

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
        btnDelete.href = "modules/customer/xuly.php?delete=1&data=" + JSON.stringify(checkedIds);
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

    function showErrorToast() {
        toast({
            title: "Error",
            message: "Bạn không có quyền xóa thông tin này",
            type: "error",
            duration: 0,
        });
    }
</script>

<?php
if (isset($_GET['message']) && $_GET['message'] == 'success') {
    echo '<script>';
    echo '   showSuccessToast();';
    echo '</script>';
} elseif (isset($_GET['message']) && $_GET['message'] == 'error') {
    echo '<script>';
    echo '   showErrorToast();';
    echo '</script>';
}
?>
<script>
    window.history.pushState(null, "", "index.php?action=customer&query=customer_list");
</script>
