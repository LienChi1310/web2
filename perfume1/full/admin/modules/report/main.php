<?php
// =========================
// NHẬN THAM SỐ LỌC
// =========================
$product_id_stock = isset($_GET['product_id_stock']) ? (int)$_GET['product_id_stock'] : 0;
$stock_date       = isset($_GET['stock_date']) ? trim($_GET['stock_date']) : '';

$date_from        = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to          = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$product_id_flow  = isset($_GET['product_id_flow']) ? (int)$_GET['product_id_flow'] : 0;

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
$flow_rows = [];
$flow_error = '';

if ($date_from !== '' && $date_to !== '') {
    $today = date('Y-m-d');

    if ($date_from > $today || $date_to > $today) {
        $flow_error = 'Không được chọn khoảng thời gian trong tương lai!';
    } elseif ($date_from > $date_to) {
        $flow_error = 'Từ ngày không được lớn hơn đến ngày!';
    } else {
        $date_from_safe = mysqli_real_escape_string($mysqli, $date_from . ' 00:00:00');
        $date_to_safe   = mysqli_real_escape_string($mysqli, $date_to . ' 23:59:59');

        $flow_where_product_import = '';
        $flow_where_product_export = '';
        if ($product_id_flow > 0) {
            $flow_where_product_import = " AND d.product_id = '{$product_id_flow}' ";
            $flow_where_product_export = " AND od.product_id = '{$product_id_flow}' ";
        }

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
              {$flow_where_product_import}
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
              {$flow_where_product_export}
            GROUP BY od.product_id
        ";
        $query_export_range = mysqli_query($mysqli, $sql_export_range);

        $export_map = [];
        if ($query_export_range) {
            while ($row = mysqli_fetch_assoc($query_export_range)) {
                $export_map[$row['product_id']] = (int)$row['total_export'];
            }
        }

        // gộp tất cả product_id có phát sinh
        $all_product_ids = array_unique(array_merge(array_keys($import_map), array_keys($export_map)));

        foreach ($all_product_ids as $pid) {
            $flow_rows[] = [
                'product_id'    => $pid,
                'product_name'  => isset($product_map[$pid]) ? $product_map[$pid]['product_name'] : 'Sản phẩm #' . $pid,
                'total_import'  => isset($import_map[$pid]) ? $import_map[$pid] : 0,
                'total_export'  => isset($export_map[$pid]) ? $export_map[$pid] : 0,
            ];
        }

        usort($flow_rows, function ($a, $b) {
            return strcmp($a['product_name'], $b['product_name']);
        });
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
?>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
            <div class="card-body">
                <h4 class="card-title card-title-dash mb-4">Báo cáo tồn kho và thống kê</h4>

                <!-- =========================
                     1. TRA CỨU TỒN KHO TẠI 1 THỜI ĐIỂM
                ========================== -->
                <div class="mb-5">
                    <h5 class="mb-3">1. Tra cứu số lượng tồn của một sản phẩm tại một thời điểm</h5>
                    <form method="GET" action="index.php" class="row">
                        <input type="hidden" name="action" value="report">
                        <input type="hidden" name="query" value="report_main">

                        <div class="col-md-5 mb-3">
                            <label class="form-label">Sản phẩm</label>
                            <select name="product_id_stock" class="form-control" required>
                                <option value="">-- Chọn sản phẩm --</option>
                                <?php foreach ($product_map as $pid => $p) { ?>
                                    <option value="<?php echo $pid; ?>" <?php echo ($product_id_stock === (int)$pid) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['product_name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Thời điểm</label>
                            <input
                                type="date"
                                name="stock_date"
                                class="form-control"
                                value="<?php echo htmlspecialchars($stock_date); ?>"
                                max="<?php echo date('Y-m-d'); ?>"
                                required
                            >
                        </div>

                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Tra cứu</button>
                        </div>
                    </form>

                    <?php if ($stock_error !== '') { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($stock_error); ?></div>
                    <?php } ?>

                    <?php if ($stock_result !== null) { ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
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
                    <?php } ?>
                </div>

                <!-- =========================
                     2. BÁO CÁO NHẬP - XUẤT
                ========================== -->
                <div class="mb-5">
                    <h5 class="mb-3">2. Báo cáo tổng số lượng nhập - xuất trong khoảng thời gian</h5>

                    <form method="GET" action="index.php" class="row">
                        <input type="hidden" name="action" value="report">
                        <input type="hidden" name="query" value="report_main">

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sản phẩm</label>
                            <select name="product_id_flow" class="form-control">
                                <option value="">-- Tất cả sản phẩm --</option>
                                <?php foreach ($product_map as $pid => $p) { ?>
                                    <option value="<?php echo $pid; ?>" <?php echo ($product_id_flow === (int)$pid) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['product_name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Từ ngày</label>
                            <input
                                type="date"
                                name="date_from"
                                class="form-control"
                                value="<?php echo htmlspecialchars($date_from); ?>"
                                max="<?php echo date('Y-m-d'); ?>"
                                required
                            >
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Đến ngày</label>
                            <input
                                type="date"
                                name="date_to"
                                class="form-control"
                                value="<?php echo htmlspecialchars($date_to); ?>"
                                max="<?php echo date('Y-m-d'); ?>"
                                required
                            >
                        </div>

                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">Xem báo cáo</button>
                        </div>
                    </form>

                    <?php if ($flow_error !== '') { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($flow_error); ?></div>
                    <?php } ?>

                    <?php if ($date_from !== '' && $date_to !== '' && $flow_error === '') { ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Mã SP</th>
                                        <th>Tên sản phẩm</th>
                                        <th>Tổng nhập</th>
                                        <th>Tổng xuất</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($flow_rows)) { ?>
                                        <?php foreach ($flow_rows as $row) { ?>
                                            <tr>
                                                <td>#<?php echo $row['product_id']; ?></td>
                                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                                <td><?php echo number_format($row['total_import'], 0, ',', '.'); ?></td>
                                                <td><?php echo number_format($row['total_export'], 0, ',', '.'); ?></td>
                                            </tr>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Không có dữ liệu nhập - xuất trong khoảng thời gian này.</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>

                <!-- =========================
                     3. CẢNH BÁO SẮP HẾT HÀNG
                ========================== -->
                <div class="mb-2">
                    <h5 class="mb-3">3. Cảnh báo sản phẩm sắp hết hàng</h5>

                    <form method="GET" action="index.php" class="row">
                        <input type="hidden" name="action" value="report">
                        <input type="hidden" name="query" value="report_main">

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Ngưỡng sắp hết hàng</label>
                            <input type="number" min="0" name="low_stock_limit" class="form-control" value="<?php echo (int)$low_stock_limit; ?>">
                        </div>

                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-danger w-100">Cảnh báo</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered">
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
</div>