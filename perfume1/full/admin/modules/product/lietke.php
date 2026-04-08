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

$low_stock_threshold = isset($_GET['low_stock_threshold']) && $_GET['low_stock_threshold'] !== ''
    ? (int)$_GET['low_stock_threshold']
    : 5;

// Handle sorting (default: newest products first)
$sort_column = 'product_id';
$sort_order = 'DESC';
$allowed_sorts = ['product_name', 'product_quantity', 'product_price_import', 'product_profit_percent', 'product_price', 'product_sale', 'product_id'];

if (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sorts)) {
    $sort_column = $_GET['sort'];
    $sort_order = (isset($_GET['order']) && $_GET['order'] === 'ASC') ? 'ASC' : 'DESC';
}

// Handle status filter
$status_filter = '';
if (isset($_GET['status']) && $_GET['status'] !== '') {
    $status = (int)$_GET['status'];
    $status_filter = " AND product_status = $status";
}

// Handle search filter
$search_filter = '';
$search_keyword = '';
if (isset($_GET['product_search']) && $_GET['product_search'] !== '') {
    $search_keyword = $_GET['product_search'];
    $search_filter = " AND product_name LIKE '%" . mysqli_real_escape_string($mysqli, $search_keyword) . "%'";
}

$sql_product_list = "SELECT * FROM product WHERE 1=1 $status_filter $search_filter ORDER BY $sort_column $sort_order LIMIT $begin,10";
$query_product_list = mysqli_query($mysqli, $sql_product_list);
?>

<div class="dialog__import">
    <div class="import-container p-absolute">
        <div class="import__header d-flex space-between align-center">
            <h3 class="card-title">Chọn file import</h3>
            <span class="icon-close cursor-pointer" id="btnClose"></span>
        </div>
        <div class="import__content">
            <form action="modules/product/import.php" method="POST" enctype="multipart/form-data">
                <input class="import__input" type="file" name="file_import" accept=".xlsx">
                <br>
                <div class="w-100 text-right">
                    <button class="button button-light" id="btnCancel" type="button">Hủy</button>
                    <button type="submit" name="import_product" class="button button-dark">Tải lên</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="header__list d-flex space-between align-center">
            <h3 class="card-title" style="margin: 0;">Danh sách sản phẩm</h3>
            <div class="action_group">
                <a href="modules/product/export.php" class="button button-light">Export</a>
                <button class="button button-light" id="btnImport">Import</button>
                <a href="?action=product&query=product_add" class="button button-dark">Thêm sản phẩm</a>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col">
        <form method="GET" action="index.php" class="d-flex align-items-center" style="gap:10px; flex-wrap:wrap;">
            <input type="hidden" name="action" value="product">
            <input type="hidden" name="query" value="product_list">

            <label class="mb-0">Ngưỡng sắp hết hàng:</label>
            <input type="number" min="0" name="low_stock_threshold" class="form-control" style="width:120px;" value="<?php echo $low_stock_threshold; ?>">

            <label class="mb-0">Tình trạng:</label>
            <select name="status" class="form-control" style="width: 150px;">
                <option value="">-- Tất cả --</option>
                <option value="1" <?php echo (isset($_GET['status']) && $_GET['status'] === '1') ? 'selected' : ''; ?>>Đang bán</option>
                <option value="0" <?php echo (isset($_GET['status']) && $_GET['status'] === '0') ? 'selected' : ''; ?>>Tạm dừng</option>
            </select>

            <button type="submit" class="btn btn-primary btn-sm">Áp dụng</button>

            <div style="flex: 1; text-align: right;">
                <div class="input__search p-relative" style="display: inline-block; width: 250px;">
                    <i class="icon-search p-absolute"></i>
                    <input type="text" id="productSearchInput" name="product_search" class="form-control" title="Nhập tên sản phẩm để tìm kiếm" placeholder="Tìm kiếm sản phẩm..." value="<?php echo isset($_GET['product_search']) ? htmlspecialchars($_GET['product_search']) : ''; ?>">
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
                                    <input type="checkbox" id="checkAll" title="Chọn tất cả">
                                </th>
                                <th style="width: 50px; text-align: center;">STT</th>
                                <th></th>
                                <th style="cursor: pointer;"><a href="?action=product&query=product_list&sort=product_name&order=<?php echo ($sort_column === 'product_name' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&low_stock_threshold=<?php echo $low_stock_threshold; ?>&status=<?php echo isset($_GET['status']) ? urlencode($_GET['status']) : ''; ?>&product_search=<?php echo isset($_GET['product_search']) ? urlencode($_GET['product_search']) : ''; ?>" style="color: inherit; text-decoration: none;">Tên sản phẩm <?php if ($sort_column === 'product_name') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th style="cursor: pointer;"><a href="?action=product&query=product_list&sort=product_quantity&order=<?php echo ($sort_column === 'product_quantity' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&low_stock_threshold=<?php echo $low_stock_threshold; ?>&status=<?php echo isset($_GET['status']) ? urlencode($_GET['status']) : ''; ?>&product_search=<?php echo isset($_GET['product_search']) ? urlencode($_GET['product_search']) : ''; ?>" style="color: inherit; text-decoration: none;">Tồn kho <?php if ($sort_column === 'product_quantity') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th style="cursor: pointer;"><a href="?action=product&query=product_list&sort=product_price_import&order=<?php echo ($sort_column === 'product_price_import' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&low_stock_threshold=<?php echo $low_stock_threshold; ?>&status=<?php echo isset($_GET['status']) ? urlencode($_GET['status']) : ''; ?>&product_search=<?php echo isset($_GET['product_search']) ? urlencode($_GET['product_search']) : ''; ?>" style="color: inherit; text-decoration: none;">Giá vốn <?php if ($sort_column === 'product_price_import') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th style="cursor: pointer;"><a href="?action=product&query=product_list&sort=product_profit_percent&order=<?php echo ($sort_column === 'product_profit_percent' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&low_stock_threshold=<?php echo $low_stock_threshold; ?>&status=<?php echo isset($_GET['status']) ? urlencode($_GET['status']) : ''; ?>&product_search=<?php echo isset($_GET['product_search']) ? urlencode($_GET['product_search']) : ''; ?>" style="color: inherit; text-decoration: none;">% lợi nhuận <?php if ($sort_column === 'product_profit_percent') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th style="cursor: pointer;"><a href="?action=product&query=product_list&sort=product_price&order=<?php echo ($sort_column === 'product_price' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&low_stock_threshold=<?php echo $low_stock_threshold; ?>&status=<?php echo isset($_GET['status']) ? urlencode($_GET['status']) : ''; ?>&product_search=<?php echo isset($_GET['product_search']) ? urlencode($_GET['product_search']) : ''; ?>" style="color: inherit; text-decoration: none;">Giá bán <?php if ($sort_column === 'product_price') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th>Tình trạng</th>
                                <th style="cursor: pointer;"><a href="?action=product&query=product_list&sort=product_sale&order=<?php echo ($sort_column === 'product_sale' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&low_stock_threshold=<?php echo $low_stock_threshold; ?>&status=<?php echo isset($_GET['status']) ? urlencode($_GET['status']) : ''; ?>&product_search=<?php echo isset($_GET['product_search']) ? urlencode($_GET['product_search']) : ''; ?>" style="color: inherit; text-decoration: none;">Sale <?php if ($sort_column === 'product_sale') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stt = $begin + 1;
                            while ($row = mysqli_fetch_array($query_product_list)) {
                                $profit_percent = isset($row['product_profit_percent']) ? (int)$row['product_profit_percent'] : 0;
                                $is_low_stock = ((int)$row['product_quantity'] <= $low_stock_threshold);
                            ?>
                                <tr <?php if ($is_low_stock) echo 'class="low-stock"'; ?>>
                                    <td>
                                        <a href="?action=product&query=product_edit&product_id=<?php echo $row['product_id']; ?>">
                                            <div class="icon-edit" title="Sửa sản phẩm">
                                                <img class="w-100 h-100" src="images/icon-edit.png" alt="">
                                            </div>
                                        </a>
                                    </td>
                                    <td>
                                        <input type="checkbox" class="checkbox" title="Chọn sản phẩm" onclick="testChecked(); getCheckedCheckboxes();" id="<?php echo $row['product_id']; ?>">
                                    </td>
                                    <td style="text-align: center;"><?php echo $stt;
                                                                    $stt++; ?></td>
                                    <td><img src="modules/product/uploads/<?php echo $row['product_image']; ?>" class="product_image" alt="image"></td>
                                    <td>
                                        <?php echo $row['product_name']; ?>
                                        <?php if ($is_low_stock) { ?>
                                            <div><span class="badge bg-warning text-dark mt-1">Sắp hết hàng</span></div>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo (int)$row['product_quantity']; ?></td>
                                    <td><?php echo number_format($row['product_price_import']) . ' ₫'; ?></td>
                                    <td><?php echo $profit_percent; ?>%</td>
                                    <td class="<?php if ($row['product_price'] < $row['product_price_import']) echo "text-danger"; ?>">
                                        <?php echo number_format($row['product_price']) . ' ₫'; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['product_status'] == 1) { ?>
                                            <div class="product__status product__status--active">
                                                <span class="show-status">Đang bán</span>
                                            </div>
                                        <?php } else { ?>
                                            <div class="product__status product__status--pause">
                                                <span class="show-status">Dừng bán</span>
                                            </div>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo $row['product_sale']; ?>%</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination d-flex justify-center">
                    <?php
                    if (isset($_GET['category_id'])) {
                        $query_pages = mysqli_query($mysqli, "SELECT * FROM product JOIN category ON product.product_category = category.category_id WHERE product_category = '" . $_GET['category_id'] . "' $status_filter $search_filter ORDER BY product_id DESC");
                    } else {
                        $query_pages = mysqli_query($mysqli, "SELECT * FROM product WHERE 1=1 $status_filter $search_filter ORDER BY product_id DESC");
                    }
                    $row_count = mysqli_num_rows($query_pages);
                    $totalpage = ceil($row_count / 10);
                    $currentLink = "index.php?action=product&query=product_list&low_stock_threshold=" . $low_stock_threshold;
                    if (isset($_GET['status']) && $_GET['status'] !== '') {
                        $currentLink .= "&status=" . urlencode($_GET['status']);
                    }
                    if (isset($_GET['product_search']) && $_GET['product_search'] !== '') {
                        $currentLink .= "&product_search=" . urlencode($_GET['product_search']);
                    }
                    ?>
                    <?php if ($row_count == 0) { ?>
                        <div class="alert alert-info" style="margin: 20px auto; text-align: center; width: 100%; font-style: italic; color: #000; font-weight: normal;">
                            Không có sản phẩm nào phù hợp với bộ lọc của bạn
                        </div>
                    <?php } else { ?>
                        <ul class="pagination__items d-flex align-center justify-center">
                            <?php if ($page != 1) { ?>
                                <li class="pagination__item">
                                    <a class="d-flex align-center" href="<?php echo $currentLink . '&pagenumber=' . ($page - 1); ?>">
                                        <img src="images/arrow-left.svg" alt="">
                                    </a>
                                </li>
                            <?php } ?>

                            <?php
                            // Smart pagination with ellipsis - Smart pages to show
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

<div class="dialog__control">
    <div class="control__box">
        <a href="#" class="button__control C" onclick="return confirm('Bạn có thực sự muốn xóa sản phẩm này không?')" id="btnDelete">Xóa</a>
        <button class="button__control btn_change" id="btnSale">Giảm giá</button>
    </div>
</div>

<div class="dialog__input">
    <div class="dialog__container">
        <div class="dialog__header d-flex align-center space-between">
            <h6>Thiết lập giảm giá cho sản phẩm</h6>
            <div class="close__btn d-flex align-center justify-center">
                <i class="icon-close"></i>
            </div>
        </div>
        <div class="input__box form-group">
            <label class="d-block" for="input_sale">Giảm giá (%)</label>
            <input class="form-control" type="number" id="input_sale" placeholder="Giảm giá theo phần trăm">
            <div class="w-100 btn__sale">
                <a href="#" id="sale_btn" class="btn btn-outline-dark btn-fw" onclick="return confirm('Xác nhận giảm giá cho các sản phẩm?')">Giảm giá</a>
            </div>
        </div>
    </div>
</div>

<script>
    var dialogImport = document.querySelector('.dialog__import');
    var btnImport = document.querySelector('#btnImport');
    var btnCancel = document.querySelector('#btnCancel');
    var btnClose = document.querySelector('#btnClose');

    btnImport.addEventListener('click', function() {
        dialogImport.classList.add('open');
    });

    btnClose.addEventListener('click', function() {
        dialogImport.classList.remove('open');
    });

    btnCancel.addEventListener('click', function() {
        dialogImport.classList.remove('open');
    });
</script>

<script>
    var linklist = '';
    var dialogInput = document.querySelector(".dialog__input");
    var btnSale = document.getElementById("btnSale");
    var saleBtn = document.querySelector('#sale_btn');
    var btnCloseDialog = document.querySelector(".close__btn");
    var btnDelete = document.getElementById("btnDelete");
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

    btnSale.addEventListener('click', function() {
        dialogInput.classList.add("open");
    });

    btnCloseDialog.addEventListener('click', function() {
        dialogInput.classList.remove("open");
    });

    function getCheckedCheckboxes() {
        var checkeds = document.querySelectorAll('.checkbox:checked');
        var checkedIds = [];
        for (var i = 0; i < checkeds.length; i++) {
            checkedIds.push(checkeds[i].id);
        }
        linklist = "modules/product/xuly.php?data=" + JSON.stringify(checkedIds);
        btnDelete.href = "modules/product/xuly.php?data=" + JSON.stringify(checkedIds);
    }

    var inputSale = document.querySelector('#input_sale');
    inputSale.addEventListener("input", function() {
        saleBtn.href = linklist + "&product_sale=" + inputSale.value;
    });
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
    window.history.pushState(null, "", "index.php?action=product&query=product_list&low_stock_threshold=<?php echo $low_stock_threshold; ?>");
</script>

<script>
    // Auto-search functionality with debounce
    var searchInput = document.getElementById('productSearchInput');
    var searchForm = searchInput ? searchInput.closest('form') : null;
    var searchTimeout;

    if (searchInput && searchForm) {
        searchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);

            // Auto-submit form after user stops typing for 500ms
            searchTimeout = setTimeout(function() {
                searchForm.submit();
            }, 500);
        });
    }
</script>
