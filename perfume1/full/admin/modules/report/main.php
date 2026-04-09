<?php
// =========================
// NHẬN THAM SỐ LỌC
// =========================
$product_id_stock = isset($_GET['product_id_stock']) ? (int)$_GET['product_id_stock'] : 0;
$stock_date       = isset($_GET['stock_date']) ? trim($_GET['stock_date']) : '';

$date_from        = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to          = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

$low_stock_limit  = isset($_GET['low_stock_limit']) ? (int)$_GET['low_stock_limit'] : 5;

// =========================
// LẤY DANH SÁCH SẢN PHẨM
// =========================
$sql_products = "SELECT product_id, product_name, product_quantity, product_price_import, product_price, product_status 
                 FROM product
                 ORDER BY product_name ASC";
$query_products = mysqli_query($mysqli, $sql_products);

// tạo mảng sản phẩm để dùng nhiều chỗ
$product_map = [];
if ($query_products) {
    while ($p = mysqli_fetch_assoc($query_products)) {
        $product_map[$p['product_id']] = $p;
    }
}

// =========================
// 1) TRA CỨU TỒN KHO TẠI 1 THỜI ĐIỂM
// =========================
$stock_result = null;
$stock_error = '';

if ($product_id_stock > 0 && $stock_date !== '') {
    $today = date('Y-m-d');

    if ($stock_date > $today) {
        $stock_error = 'Không được chọn thời điểm trong tương lai!';
        $stock_result = null;
    } else {
        $stock_date_safe = mysqli_real_escape_string($mysqli, $stock_date . ' 23:59:59');

        // Tổng nhập đến thời điểm chọn (chỉ phiếu nhập đã hoàn thành)
        $sql_import_before = "
            SELECT COALESCE(SUM(d.quantity), 0) AS total_import
            FROM inventory_detail d
            INNER JOIN inventory i ON d.inventory_id = i.inventory_id
            WHERE d.product_id = '{$product_id_stock}'
              AND i.inventory_status = 1
              AND i.inventory_date <= '{$stock_date_safe}'
        ";
        $query_import_before = mysqli_query($mysqli, $sql_import_before);
        $row_import_before = $query_import_before ? mysqli_fetch_assoc($query_import_before) : ['total_import' => 0];
        $total_import_before = (int)$row_import_before['total_import'];

        // Tổng xuất đến thời điểm chọn
        // Chỉ tính các đơn đã xác nhận / đang giao / hoàn thành
        $sql_export_before = "
            SELECT COALESCE(SUM(od.product_quantity), 0) AS total_export
            FROM order_detail od
            INNER JOIN orders o ON od.order_code = o.order_code
            WHERE od.product_id = '{$product_id_stock}'
              AND o.order_status IN (1,2,3)
              AND o.order_date <= '{$stock_date_safe}'
        ";
        $query_export_before = mysqli_query($mysqli, $sql_export_before);
        $row_export_before = $query_export_before ? mysqli_fetch_assoc($query_export_before) : ['total_export' => 0];
        $total_export_before = (int)$row_export_before['total_export'];

        $stock_result = [
            'product_id'    => $product_id_stock,
            'product_name'  => isset($product_map[$product_id_stock]) ? $product_map[$product_id_stock]['product_name'] : '',
            'stock_date'    => $stock_date,
            'total_import'  => $total_import_before,
            'total_export'  => $total_export_before,
            'stock'         => $total_import_before - $total_export_before,
        ];
    }
}

// =========================
// 2) BÁO CÁO NHẬP - XUẤT TRONG KHOẢNG THỜI GIAN
// =========================
$flow_data = [];
$flow_error = '';
$flow_total = 0;

// Handle sorting
$sort_column = 'product_name';
$sort_order = 'ASC';
$allowed_sorts = ['product_id', 'product_name', 'total_import', 'total_export'];

if (isset($_GET['sort_flow']) && in_array($_GET['sort_flow'], $allowed_sorts)) {
    $sort_column = $_GET['sort_flow'];
    $sort_order = (isset($_GET['order_flow']) && $_GET['order_flow'] === 'DESC') ? 'DESC' : 'ASC';
}

// Handle search
$search_keyword_flow = '';
$search_filter_flow = '';
if (isset($_GET['flow_search']) && $_GET['flow_search'] !== '') {
    $search_keyword_flow = $_GET['flow_search'];
    $search_filter_flow = " AND (product_id LIKE '%" . mysqli_real_escape_string($mysqli, $search_keyword_flow) . "%' OR product_name LIKE '%" . mysqli_real_escape_string($mysqli, $search_keyword_flow) . "%')";
}

// Pagination
$page_flow = isset($_GET['page_flow']) ? max(1, (int)$_GET['page_flow']) : 1;
$per_page_flow = 10;

if ($date_from !== '' && $date_to !== '') {
    $today = date('Y-m-d');

    if ($date_from > $today || $date_to > $today) {
        $flow_error = 'Không được chọn khoảng thời gian trong tương lai!';
    } elseif ($date_from > $date_to) {
        $flow_error = 'Từ ngày không được lớn hơn đến ngày!';
    } else {
        $date_from_safe = mysqli_real_escape_string($mysqli, $date_from . ' 00:00:00');
        $date_to_safe   = mysqli_real_escape_string($mysqli, $date_to . ' 23:59:59');

        // tổng nhập theo sản phẩm
        $sql_import_range = "
            SELECT 
                d.product_id,
                COALESCE(SUM(d.quantity), 0) AS total_import
            FROM inventory_detail d
            INNER JOIN inventory i ON d.inventory_id = i.inventory_id
            WHERE i.inventory_status = 1
              AND i.inventory_date >= '{$date_from_safe}'
              AND i.inventory_date <= '{$date_to_safe}'
            GROUP BY d.product_id
        ";
        $query_import_range = mysqli_query($mysqli, $sql_import_range);

        $import_map = [];
        if ($query_import_range) {
            while ($row = mysqli_fetch_assoc($query_import_range)) {
                $import_map[$row['product_id']] = (int)$row['total_import'];
            }
        }

        // tổng xuất theo sản phẩm
        $sql_export_range = "
            SELECT 
                od.product_id,
                COALESCE(SUM(od.product_quantity), 0) AS total_export
            FROM order_detail od
            INNER JOIN orders o ON od.order_code = o.order_code
            WHERE o.order_status IN (1,2,3)
              AND o.order_date >= '{$date_from_safe}'
              AND o.order_date <= '{$date_to_safe}'
            GROUP BY od.product_id
        ";
        $query_export_range = mysqli_query($mysqli, $sql_export_range);

        $export_map = [];
        if ($query_export_range) {
            while ($row = mysqli_fetch_assoc($query_export_range)) {
                $export_map[$row['product_id']] = (int)$row['total_export'];
            }
        }

        // Lấy tất cả sản phẩm để hiển thị (kể cả những sản phẩm không có nhập/xuất)
        $sql_all_products = "
            SELECT product_id, product_name
            FROM product
            WHERE 1=1
            {$search_filter_flow}
            ORDER BY product_name ASC
        ";
        $query_all_products = mysqli_query($mysqli, $sql_all_products);

        if ($query_all_products) {
            while ($row = mysqli_fetch_assoc($query_all_products)) {
                $flow_data[] = [
                    'product_id'    => $row['product_id'],
                    'product_name'  => $row['product_name'],
                    'total_import'  => isset($import_map[$row['product_id']]) ? $import_map[$row['product_id']] : 0,
                    'total_export'  => isset($export_map[$row['product_id']]) ? $export_map[$row['product_id']] : 0,
                ];
            }
        }

        // Sort $flow_data theo cột được chọn
        usort($flow_data, function ($a, $b) use ($sort_column, $sort_order) {
            $cmp = 0;
            if ($sort_column === 'product_id') {
                $cmp = (int)$a['product_id'] - (int)$b['product_id'];
            } elseif ($sort_column === 'product_name') {
                $cmp = strcmp($a['product_name'], $b['product_name']);
            } elseif ($sort_column === 'total_import') {
                $cmp = $a['total_import'] - $b['total_import'];
            } elseif ($sort_column === 'total_export') {
                $cmp = $a['total_export'] - $b['total_export'];
            }
            return ($sort_order === 'DESC') ? -$cmp : $cmp;
        });

        $flow_total = count($flow_data);
    }
}

// =========================
// 3) CẢNH BÁO SẮP HẾT HÀNG
// =========================
if ($low_stock_limit < 0) {
    $low_stock_limit = 0;
}

$sql_low_stock = "
    SELECT product_id, product_name, product_quantity, product_price_import, product_price, product_status
    FROM product
    WHERE product_quantity <= '{$low_stock_limit}'
    ORDER BY product_quantity ASC, product_name ASC
";
$query_low_stock = mysqli_query($mysqli, $sql_low_stock);

// Tạo mảng JSON cho JavaScript (dùng cho custom dropdown)
$products_json = json_encode(array_values($product_map));
?>

<!-- Header -->
<div class="row">
    <div class="col">
        <div class="header__list d-flex space-between align-center">
            <h3 class="card-title" style="margin: 0;">Báo cáo tồn kho và thống kê</h3>
        </div>
    </div>
</div>

<!-- ========================================
     1. TRA CỨU TỒN KHO TẠI 1 THỜI ĐIỂM
     ======================================== -->

<!-- Filter Form -->
<div class="row mb-3">
    <div class="col">
        <h6 style="margin-bottom: 15px; font-weight: 600;">1. Tra cứu tồn kho</h6>
        <form method="GET" action="index.php" class="d-flex" style="gap: 15px; flex-wrap: wrap;" id="form_section1">
            <input type="hidden" name="action" value="report">
            <input type="hidden" name="query" value="report_main">
            <!-- Preserve other sections' values -->
            <input type="hidden" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            <input type="hidden" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            <input type="hidden" name="low_stock_limit" value="<?php echo (int)$low_stock_limit; ?>">

            <div style="min-width: 250px; display: flex; align-items: center; gap: 8px;">
                <label class="form-label mb-0" style="white-space: nowrap;">Sản phẩm</label>
                <div class="custom-dropdown-container" id="dropdown_stock" data-input-name="product_id_stock" style="flex: 1;">
                    <input
                        type="text"
                        class="form-control form-control-sm search-input"
                        placeholder="Tìm kiếm..."
                        autocomplete="off">
                    <input type="hidden" class="hidden-select" name="product_id_stock" value="<?php echo (int)$product_id_stock; ?>">
                    <div class="dropdown-options">
                        <div class="dropdown-option" data-value="">-- Chọn sản phẩm --</div>
                    </div>
                </div>
            </div>

            <div style="min-width: 140px; display: flex; align-items: center; gap: 8px;">
                <label class="form-label mb-0" style="white-space: nowrap;">Thời điểm</label>
                <input
                    type="date"
                    name="stock_date"
                    class="form-control form-control-sm"
                    value="<?php echo htmlspecialchars($stock_date); ?>"
                    max="<?php echo date('Y-m-d'); ?>"
                    required>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Tra cứu</button>
        </form>
    </div>
</div>

<!-- Result Table -->
<?php if ($stock_error !== '') { ?>
    <div class="row mb-3">
        <div class="col">
            <div class="alert alert-danger"><?php echo htmlspecialchars($stock_error); ?></div>
        </div>
    </div>
<?php } ?>

<?php if ($stock_result !== null) { ?>
    <div class="row mb-5">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Thời điểm</th>
                                    <th>Tổng nhập đến thời điểm đó</th>
                                    <th>Tổng xuất đến thời điểm đó</th>
                                    <th>Tồn kho tại thời điểm đó</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo htmlspecialchars($stock_result['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($stock_result['stock_date']); ?></td>
                                    <td><?php echo number_format($stock_result['total_import'], 0, ',', '.'); ?></td>
                                    <td><?php echo number_format($stock_result['total_export'], 0, ',', '.'); ?></td>
                                    <td>
                                        <strong><?php echo number_format($stock_result['stock'], 0, ',', '.'); ?></strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<!-- ========================================
     2. BÁO CÁO NHẬP - XUẤT
     ======================================== -->

<!-- Filter Form -->
<div class="row mb-3">
    <div class="col">
        <h6 style="margin-bottom: 15px; font-weight: 600;">2. Báo cáo nhập-xuất</h6>
        <form method="GET" action="index.php" class="d-flex" style="gap: 15px; flex-wrap: wrap;" id="form_section2">
            <input type="hidden" name="action" value="report">
            <input type="hidden" name="query" value="report_main">
            <!-- Preserve other sections' values -->
            <input type="hidden" name="product_id_stock" value="<?php echo (int)$product_id_stock; ?>">
            <input type="hidden" name="stock_date" value="<?php echo htmlspecialchars($stock_date); ?>">
            <input type="hidden" name="low_stock_limit" value="<?php echo (int)$low_stock_limit; ?>">

            <div style="min-width: 120px; display: flex; align-items: center; gap: 8px;">
                <label class="form-label mb-0" style="white-space: nowrap;">Từ ngày</label>
                <input
                    type="date"
                    name="date_from"
                    class="form-control form-control-sm"
                    value="<?php echo htmlspecialchars($date_from); ?>"
                    max="<?php echo date('Y-m-d'); ?>"
                    required>
            </div>

            <div style="min-width: 120px; display: flex; align-items: center; gap: 8px;">
                <label class="form-label mb-0" style="white-space: nowrap;">Đến ngày</label>
                <input
                    type="date"
                    name="date_to"
                    class="form-control form-control-sm"
                    value="<?php echo htmlspecialchars($date_to); ?>"
                    max="<?php echo date('Y-m-d'); ?>"
                    required>
            </div>

            <button type="submit" class="btn btn-success btn-sm">Xem báo cáo</button>

            <div style="flex: 1; display: flex; flex-direction: row; align-items: center; justify-content: flex-end;">
                <div class="input__search p-relative" style="display: flex; width: 250px;">
                    <i class="icon-search p-absolute" style="z-index: 1;"></i>
                    <input type="text" name="flow_search" class="form-control form-control-sm" placeholder="Tìm kiếm..." value="<?php echo isset($_GET['flow_search']) ? htmlspecialchars($_GET['flow_search']) : ''; ?>">
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Result Table -->
<?php if ($flow_error !== '') { ?>
    <div class="row mb-3">
        <div class="col">
            <div class="alert alert-danger"><?php echo htmlspecialchars($flow_error); ?></div>
        </div>
    </div>
<?php } ?>

<?php if ($date_from !== '' && $date_to !== '' && $flow_error === '') { ?>
    <div class="row mb-5">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-action">
                            <thead>
                                <tr>
                                    <th style="width: 50px; text-align: center;">STT</th>
                                    <th style="cursor: pointer;"><a href="?action=report&query=report_main&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&sort_flow=product_id&order_flow=<?php echo ($sort_column === 'product_id' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&flow_search=<?php echo isset($_GET['flow_search']) ? urlencode($_GET['flow_search']) : ''; ?>&product_id_stock=<?php echo (int)$product_id_stock; ?>&stock_date=<?php echo htmlspecialchars($stock_date); ?>&low_stock_limit=<?php echo (int)$low_stock_limit; ?>" style="color: inherit; text-decoration: none;">Mã SP <?php if ($sort_column === 'product_id') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                    <th style="cursor: pointer;"><a href="?action=report&query=report_main&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&sort_flow=product_name&order_flow=<?php echo ($sort_column === 'product_name' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&flow_search=<?php echo isset($_GET['flow_search']) ? urlencode($_GET['flow_search']) : ''; ?>&product_id_stock=<?php echo (int)$product_id_stock; ?>&stock_date=<?php echo htmlspecialchars($stock_date); ?>&low_stock_limit=<?php echo (int)$low_stock_limit; ?>" style="color: inherit; text-decoration: none;">Tên sản phẩm <?php if ($sort_column === 'product_name') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                    <th style="cursor: pointer;"><a href="?action=report&query=report_main&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&sort_flow=total_import&order_flow=<?php echo ($sort_column === 'total_import' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&flow_search=<?php echo isset($_GET['flow_search']) ? urlencode($_GET['flow_search']) : ''; ?>&product_id_stock=<?php echo (int)$product_id_stock; ?>&stock_date=<?php echo htmlspecialchars($stock_date); ?>&low_stock_limit=<?php echo (int)$low_stock_limit; ?>" style="color: inherit; text-decoration: none;">Tổng nhập <?php if ($sort_column === 'total_import') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                    <th style="cursor: pointer;"><a href="?action=report&query=report_main&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&sort_flow=total_export&order_flow=<?php echo ($sort_column === 'total_export' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&flow_search=<?php echo isset($_GET['flow_search']) ? urlencode($_GET['flow_search']) : ''; ?>&product_id_stock=<?php echo (int)$product_id_stock; ?>&stock_date=<?php echo htmlspecialchars($stock_date); ?>&low_stock_limit=<?php echo (int)$low_stock_limit; ?>" style="color: inherit; text-decoration: none;">Tổng xuất <?php if ($sort_column === 'total_export') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stt = 1 + ($page_flow - 1) * $per_page_flow;
                                $start = ($page_flow - 1) * $per_page_flow;
                                $end = min($start + $per_page_flow, $flow_total);
                                $page_data = array_slice($flow_data, $start, $per_page_flow);

                                if (!empty($page_data)) {
                                    foreach ($page_data as $row) {
                                ?>
                                        <tr>
                                            <td style="text-align: center;"><?php echo $stt;
                                                                            $stt++; ?></td>
                                            <td style="font-weight: 500;">#<?php echo $row['product_id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                            <td><?php echo number_format($row['total_import'], 0, ',', '.'); ?></td>
                                            <td><?php echo number_format($row['total_export'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Không có dữ liệu nhập - xuất trong khoảng thời gian này.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($flow_total > $per_page_flow) { ?>
                        <nav style="margin-top: 20px;">
                            <div class="pagination d-flex justify-center">
                                <ul class="pagination__items d-flex align-center justify-center">
                                    <?php
                                    $total_pages = ceil($flow_total / $per_page_flow);
                                    for ($i = 1; $i <= $total_pages; $i++) {
                                    ?>
                                        <li class="pagination__item">
                                            <a class="pagination__anchor <?php if ($i === $page_flow) echo "active"; ?>" href="?action=report&query=report_main&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&page_flow=<?php echo $i; ?>&sort_flow=<?php echo $sort_column; ?>&order_flow=<?php echo $sort_order; ?>&flow_search=<?php echo isset($_GET['flow_search']) ? urlencode($_GET['flow_search']) : ''; ?>&product_id_stock=<?php echo (int)$product_id_stock; ?>&stock_date=<?php echo htmlspecialchars($stock_date); ?>&low_stock_limit=<?php echo (int)$low_stock_limit; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </nav>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<!-- ========================================
     3. CẢNH BÁO SẮP HẾT HÀNG
     ======================================== -->

<!-- Filter Form -->
<div class="row mb-3">
    <div class="col">
        <h6 style="margin-bottom: 15px; font-weight: 600;">3. Cảnh báo sắp hết hàng</h6>
        <form method="GET" action="index.php" class="d-flex" style="gap: 15px; flex-wrap: wrap;" id="form_section3">
            <input type="hidden" name="action" value="report">
            <input type="hidden" name="query" value="report_main">
            <!-- Preserve other sections' values -->
            <input type="hidden" name="product_id_stock" value="<?php echo (int)$product_id_stock; ?>">
            <input type="hidden" name="stock_date" value="<?php echo htmlspecialchars($stock_date); ?>">
            <input type="hidden" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            <input type="hidden" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">

            <div style="min-width: 140px; display: flex; align-items: center; gap: 8px;">
                <label class="form-label mb-0" style="white-space: nowrap;">Ngưỡng sắp hết</label>
                <input type="number" min="0" name="low_stock_limit" class="form-control form-control-sm" value="<?php echo (int)$low_stock_limit; ?>">
            </div>

            <button type="submit" class="btn btn-danger btn-sm">Cảnh báo</button>
        </form>
    </div>
</div>

<!-- Result Table -->
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã SP</th>
                                <th>Tên sản phẩm</th>
                                <th>Số lượng tồn hiện tại</th>
                                <th>Giá vốn</th>
                                <th>Giá bán</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($query_low_stock && mysqli_num_rows($query_low_stock) > 0) { ?>
                                <?php while ($row = mysqli_fetch_assoc($query_low_stock)) { ?>
                                    <tr>
                                        <td>#<?php echo $row['product_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                        <td>
                                            <strong class="<?php echo ((int)$row['product_quantity'] <= (int)$low_stock_limit) ? 'text-danger' : ''; ?>">
                                                <?php echo number_format($row['product_quantity'], 0, ',', '.'); ?>
                                            </strong>
                                        </td>
                                        <td><?php echo number_format($row['product_price_import'], 0, ',', '.'); ?> đ</td>
                                        <td><?php echo number_format($row['product_price'], 0, ',', '.'); ?> đ</td>
                                        <td>
                                            <?php echo ((int)$row['product_status'] == 1) ? 'Đang bán' : 'Ẩn'; ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="6" class="text-center">Không có sản phẩm nào ở mức sắp hết hàng.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS cho custom dropdown -->
<style>
    .custom-dropdown-container {
        position: relative;
        width: 100%;
    }

    .custom-dropdown-container .search-input {
        width: 100%;
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 4px;
        font-size: 14px;
        background-color: white;
    }

    .custom-dropdown-container .search-input:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .custom-dropdown-container .dropdown-options {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
    }

    .custom-dropdown-container .dropdown-options.show {
        display: block;
    }

    .custom-dropdown-container .dropdown-option {
        padding: 10px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
    }

    .custom-dropdown-container .dropdown-option:last-child {
        border-bottom: none;
    }

    .custom-dropdown-container .dropdown-option:hover {
        background-color: #f0f0f0;
    }

    .custom-dropdown-container .dropdown-option.hidden {
        display: none;
    }

    .custom-dropdown-container .dropdown-option.selected {
        background-color: #e3f2fd;
        color: #1976d2;
        font-weight: 500;
    }
</style>

<!-- JavaScript cho custom dropdown -->
<script>
    // Dữ liệu sản phẩm từ backend
    const allProducts = <?php echo $products_json; ?>;

    /**
     * Khởi tạo custom dropdown
     */
    function initCustomDropdown(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const searchInput = container.querySelector('.search-input');
        const hiddenSelect = container.querySelector('.hidden-select');
        const dropdownOptions = container.querySelector('.dropdown-options');

        // Populate dropdown options
        populateDropdownOptions(container);

        // Xử lý focus
        searchInput.addEventListener('focus', function() {
            dropdownOptions.classList.add('show');
        });

        // Xử lý blur
        searchInput.addEventListener('blur', function() {
            setTimeout(() => {
                dropdownOptions.classList.remove('show');
            }, 200);
        });

        // Xử lý input để search
        searchInput.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const options = dropdownOptions.querySelectorAll('.dropdown-option');

            options.forEach(option => {
                const optionText = option.textContent.toLowerCase();
                if (optionText.includes(searchText) || searchText === '') {
                    option.classList.remove('hidden');
                } else {
                    option.classList.add('hidden');
                }
            });
        });
    }

    /**
     * Populate dropdown options từ data
     */
    function populateDropdownOptions(container) {
        const dropdownOptions = container.querySelector('.dropdown-options');
        const hiddenSelect = container.querySelector('.hidden-select');
        const currentValue = parseInt(hiddenSelect.value) || 0;

        // Xóa các option cũ
        dropdownOptions.innerHTML = '';

        // Thêm option default
        const defaultOption = document.createElement('div');
        defaultOption.className = 'dropdown-option';
        defaultOption.setAttribute('data-value', '');
        if (container.id === 'dropdown_stock') {
            defaultOption.textContent = '-- Chọn sản phẩm --';
        } else {
            defaultOption.textContent = '-- Tất cả sản phẩm --';
        }
        if (currentValue === 0) {
            defaultOption.classList.add('selected');
        }
        dropdownOptions.appendChild(defaultOption);

        // Thêm các product options
        allProducts.forEach(product => {
            const option = document.createElement('div');
            option.className = 'dropdown-option';
            option.setAttribute('data-value', product.product_id);
            option.textContent = '#' + product.product_id + ' - ' + product.product_name;

            if (parseInt(product.product_id) === currentValue) {
                option.classList.add('selected');
            }

            option.addEventListener('click', function() {
                const value = parseInt(this.getAttribute('data-value')) || 0;
                hiddenSelect.value = value;

                // Update search input display
                const searchInput = container.querySelector('.search-input');
                if (value === 0) {
                    searchInput.value = '';
                } else {
                    const selected = allProducts.find(p => parseInt(p.product_id) === value);
                    if (selected) {
                        searchInput.value = '#' + selected.product_id + ' - ' + selected.product_name;
                    }
                }

                // Update selected state
                const allOptions = dropdownOptions.querySelectorAll('.dropdown-option');
                allOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');

                dropdownOptions.classList.remove('show');
            });

            dropdownOptions.appendChild(option);
        });
    }

    // Khởi tạo các dropdown khi trang load
    document.addEventListener('DOMContentLoaded', function() {
        initCustomDropdown('dropdown_stock');
    });
</script>
</div>
