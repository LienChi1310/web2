<?php
// =========================
// Handle sorting
// =========================
$sort_column = 'inventory_id';
$sort_order = 'DESC';
$allowed_sorts = ['inventory_id', 'inventory_date', 'inventory_status'];

if (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sorts)) {
    $sort_column = $_GET['sort'];
    $sort_order = (isset($_GET['order']) && $_GET['order'] === 'ASC') ? 'ASC' : 'DESC';
}

// =========================
// Handle status filter
// =========================
$status_filter = '';
if (isset($_GET['status']) && $_GET['status'] !== '') {
    $status = (int)$_GET['status'];
    $status_filter = " AND i.inventory_status = $status";
}

// =========================
// Handle date filters
// =========================
$date_filter = '';
if (isset($_GET['from']) && $_GET['from'] !== '') {
    $from_safe = mysqli_real_escape_string($mysqli, $_GET['from']);
    $date_filter .= " AND DATE(i.inventory_date) >= '" . $from_safe . "'";
}
if (isset($_GET['to']) && $_GET['to'] !== '') {
    $to_safe = mysqli_real_escape_string($mysqli, $_GET['to']);
    $date_filter .= " AND DATE(i.inventory_date) <= '" . $to_safe . "'";
}

// =========================
// Handle search
// =========================
$search_filter = '';
$search_keyword = '';
if (isset($_GET['inventory_search']) && $_GET['inventory_search'] !== '') {
    $search_keyword = $_GET['inventory_search'];
    $search_filter = " AND i.inventory_id LIKE '%" . mysqli_real_escape_string($mysqli, $search_keyword) . "%'";
}

// =========================
// Query danh sách phiếu nhập
// =========================
$sql = "
    SELECT 
        i.inventory_id,
        i.inventory_date,
        i.inventory_status,
        COUNT(d.id) AS total_items,
        COALESCE(SUM(d.quantity * d.price_import), 0) AS total_amount
    FROM inventory i
    LEFT JOIN inventory_detail d ON i.inventory_id = d.inventory_id
    WHERE 1=1
    $status_filter
    $date_filter
    $search_filter
    GROUP BY i.inventory_id, i.inventory_date, i.inventory_status
    ORDER BY $sort_column $sort_order
";
$query = mysqli_query($mysqli, $sql);
?>

<div class="row">
    <div class="col">
        <div class="header__list d-flex space-between align-center">
            <h3 class="card-title" style="margin: 0;">Danh sách phiếu nhập kho</h3>
            <div class="action_group">
                <a href="index.php?action=inventory&query=inventory_add" class="button button-dark">+ Thêm phiếu nhập</a>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col">
        <form method="GET" action="index.php" class="d-flex align-items-center" style="gap:10px; flex-wrap:wrap;">
            <input type="hidden" name="action" value="inventory">
            <input type="hidden" name="query" value="inventory_list">

            <label class="mb-0">Trạng thái:</label>
            <select name="status" class="form-control" style="width: 180px;">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="0" <?php echo (isset($_GET['status']) && $_GET['status'] === '0') ? 'selected' : ''; ?>>Chưa hoàn thành</option>
                <option value="1" <?php echo (isset($_GET['status']) && $_GET['status'] === '1') ? 'selected' : ''; ?>>Đã hoàn thành</option>
                <option value="-1" <?php echo (isset($_GET['status']) && $_GET['status'] === '-1') ? 'selected' : ''; ?>>Đã hủy</option>
            </select>

            <label class="mb-0">Từ ngày:</label>
            <input type="date" name="from" class="form-control" style="width: 150px;" value="<?php echo isset($_GET['from']) ? htmlspecialchars($_GET['from']) : ''; ?>">

            <label class="mb-0">Đến ngày:</label>
            <input type="date" name="to" class="form-control" style="width: 150px;" value="<?php echo isset($_GET['to']) ? htmlspecialchars($_GET['to']) : ''; ?>">

            <button type="submit" class="btn btn-primary btn-sm">Áp dụng</button>

            <div style="flex: 1; text-align: right;">
                <div class="input__search p-relative" style="display: inline-block; width: 250px;">
                    <i class="icon-search p-absolute"></i>
                    <input type="text" id="inventorySearchInput" name="inventory_search" class="form-control" title="Nhập mã phiếu để tìm kiếm" placeholder="Tìm kiếm mã phiếu..." value="<?php echo isset($_GET['inventory_search']) ? htmlspecialchars($_GET['inventory_search']) : ''; ?>">
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
                                <th style="width: 50px; text-align: center;">STT</th>
                                <th style="cursor: pointer;"><a href="?action=inventory&query=inventory_list&sort=inventory_id&order=<?php echo ($sort_column === 'inventory_id' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&status=<?php echo isset($_GET['status']) ? urlencode($_GET['status']) : ''; ?>&inventory_search=<?php echo isset($_GET['inventory_search']) ? urlencode($_GET['inventory_search']) : ''; ?>" style="color: inherit; text-decoration: none;">Mã phiếu <?php if ($sort_column === 'inventory_id') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th style="cursor: pointer;"><a href="?action=inventory&query=inventory_list&sort=inventory_date&order=<?php echo ($sort_column === 'inventory_date' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&status=<?php echo isset($_GET['status']) ? urlencode($_GET['status']) : ''; ?>&inventory_search=<?php echo isset($_GET['inventory_search']) ? urlencode($_GET['inventory_search']) : ''; ?>" style="color: inherit; text-decoration: none;">Ngày nhập <?php if ($sort_column === 'inventory_date') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th>Số dòng SP</th>
                                <th>Tổng tiền nhập</th>
                                <th style="cursor: pointer;"><a href="?action=inventory&query=inventory_list&sort=inventory_status&order=<?php echo ($sort_column === 'inventory_status' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&status=<?php echo isset($_GET['status']) ? urlencode($_GET['status']) : ''; ?>&inventory_search=<?php echo isset($_GET['inventory_search']) ? urlencode($_GET['inventory_search']) : ''; ?>" style="color: inherit; text-decoration: none;">Trạng thái <?php if ($sort_column === 'inventory_status') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th>Quản lý</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stt = 1;
                            if ($query && mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_array($query)) {
                            ?>
                                    <tr>
                                        <td style="text-align: center;"><?php echo $stt;
                                                                        $stt++; ?></td>
                                        <td style="font-weight: 500;">#<?php echo $row['inventory_id']; ?></td>
                                        <td>
                                            <?php
                                            echo !empty($row['inventory_date'])
                                                ? date('d/m/Y H:i', strtotime($row['inventory_date']))
                                                : '';
                                            ?>
                                        </td>
                                        <td><?php echo (int)$row['total_items']; ?></td>
                                        <td><?php echo number_format((float)$row['total_amount'], 0, ',', '.'); ?> đ</td>
                                        <td>
                                            <span class="<?php echo inventory_status_class($row['inventory_status']); ?> px-2 py-1 rounded" style="display: inline-block; font-size: 12px;">
                                                <?php echo inventory_status_text($row['inventory_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="index.php?action=inventory&query=inventory_detail&inventory_id=<?php echo $row['inventory_id']; ?>" class="btn btn-sm" style="background-color: #0D6EFD; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; display: inline-block;">
                                                Chi tiết
                                            </a>

                                            <?php if ((int)$row['inventory_status'] === 0) { ?>
                                                <a href="index.php?action=inventory&query=inventory_edit&inventory_id=<?php echo $row['inventory_id']; ?>" class="btn btn-sm" style="background-color: #FFC107; color: black; padding: 5px 10px; border-radius: 4px; text-decoration: none; display: inline-block;">
                                                    Sửa
                                                </a>

                                                <a href="modules/inventory/xuly.php?action=complete&inventory_id=<?php echo $row['inventory_id']; ?>"
                                                    class="btn btn-sm"
                                                    style="background-color: #198754; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; display: inline-block;"
                                                    onclick="return confirm('Bạn có chắc muốn hoàn thành phiếu nhập này không? Sau khi hoàn thành sẽ cập nhật tồn kho và giá vốn.');">
                                                    Hoàn thành
                                                </a>

                                                <a href="modules/inventory/xuly.php?action=cancel&inventory_id=<?php echo $row['inventory_id']; ?>"
                                                    class="btn btn-sm"
                                                    style="background-color: #DC3545; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; display: inline-block;"
                                                    onclick="return confirm('Bạn có chắc muốn hủy phiếu nhập này không?');">
                                                    Hủy
                                                </a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="7" class="text-center" style="padding: 20px;">Không có phiếu nhập kho nào phù hợp với bộ lọc của bạn</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
