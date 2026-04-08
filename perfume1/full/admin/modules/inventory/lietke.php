<?php
// =========================
// Nhận bộ lọc
// =========================
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$status  = isset($_GET['status']) ? trim($_GET['status']) : '';
$from    = isset($_GET['from']) ? trim($_GET['from']) : '';
$to      = isset($_GET['to']) ? trim($_GET['to']) : '';

// =========================
// Build điều kiện lọc
// =========================
$where = " WHERE 1=1 ";

if ($keyword !== '') {
    $keyword_safe = mysqli_real_escape_string($mysqli, $keyword);
    $where .= " AND i.inventory_id LIKE '%" . $keyword_safe . "%' ";
}

if ($status !== '' && in_array($status, ['0', '1', '-1'], true)) {
    $status_safe = (int)$status;
    $where .= " AND i.inventory_status = '" . $status_safe . "' ";
}

if ($from !== '') {
    $from_safe = mysqli_real_escape_string($mysqli, $from);
    $where .= " AND DATE(i.inventory_date) >= '" . $from_safe . "' ";
}

if ($to !== '') {
    $to_safe = mysqli_real_escape_string($mysqli, $to);
    $where .= " AND DATE(i.inventory_date) <= '" . $to_safe . "' ";
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
    $where
    GROUP BY i.inventory_id, i.inventory_date, i.inventory_status
    ORDER BY i.inventory_id DESC
";
$query = mysqli_query($mysqli, $sql);

function inventory_status_text($value)
{
    if ($value == 1) {
        return 'Đã hoàn thành';
    }
    if ($value == -1) {
        return 'Đã hủy';
    }
    return 'Chưa hoàn thành';
}

function inventory_status_class($value)
{
    if ($value == 1) {
        return 'bg-success text-white';
    }
    if ($value == -1) {
        return 'bg-danger text-white';
    }
    return 'bg-warning text-dark';
}
?>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h4 class="card-title card-title-dash mb-0">Danh sách phiếu nhập kho</h4>
                    <a href="index.php?action=inventory&query=inventory_add" class="btn btn-primary btn-sm">
                        + Thêm phiếu nhập
                    </a>
                </div>

                <!-- FORM TÌM KIẾM / LỌC -->
                <form method="GET" action="index.php" class="mb-4">
                    <input type="hidden" name="action" value="inventory">
                    <input type="hidden" name="query" value="inventory_list">

                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Mã phiếu</label>
                            <input
                                type="text"
                                name="keyword"
                                class="form-control"
                                placeholder="Nhập mã phiếu nhập"
                                value="<?php echo htmlspecialchars($keyword); ?>">
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-control">
                                <option value="">-- Tất cả trạng thái --</option>
                                <option value="0" <?php echo ($status === '0') ? 'selected' : ''; ?>>Chưa hoàn thành</option>
                                <option value="1" <?php echo ($status === '1') ? 'selected' : ''; ?>>Đã hoàn thành</option>
                                <option value="-1" <?php echo ($status === '-1') ? 'selected' : ''; ?>>Đã hủy</option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-2">
                            <label class="form-label">Từ ngày</label>
                            <input
                                type="date"
                                name="from"
                                class="form-control"
                                value="<?php echo htmlspecialchars($from); ?>">
                        </div>

                        <div class="col-md-2 mb-2">
                            <label class="form-label">Đến ngày</label>
                            <input
                                type="date"
                                name="to"
                                class="form-control"
                                value="<?php echo htmlspecialchars($to); ?>">
                        </div>

                        <div class="col-md-2 mb-2 d-flex align-items-end">
                            <div class="w-100">
                                <button type="submit" class="btn btn-primary btn-sm w-100 mb-1">Tìm kiếm</button>
                                <a href="index.php?action=inventory&query=inventory_list" class="btn btn-light btn-sm w-100">Làm mới</a>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">STT</th>
                                <th>Mã phiếu</th>
                                <th>Ngày nhập</th>
                                <th>Số dòng SP</th>
                                <th>Tổng tiền nhập</th>
                                <th>Trạng thái</th>
                                <th width="320">Quản lý</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($query && mysqli_num_rows($query) > 0) { ?>
                                <?php
                                $stt = 0;
                                while ($row = mysqli_fetch_array($query)) {
                                    $stt++;
                                ?>
                                    <tr>
                                        <td style="text-align: center;"><?php echo $stt; ?></td>
                                        <td>#<?php echo $row['inventory_id']; ?></td>
                                        <td>
                                            <?php
                                            echo !empty($row['inventory_date'])
                                                ? date('d/m/Y H:i:s', strtotime($row['inventory_date']))
                                                : '';
                                            ?>
                                        </td>
                                        <td><?php echo (int)$row['total_items']; ?></td>
                                        <td><?php echo number_format((float)$row['total_amount'], 0, ',', '.'); ?> đ</td>
                                        <td>
                                            <span class="<?php echo inventory_status_class($row['inventory_status']); ?> px-2 py-1 rounded d-inline-block">
                                                <?php echo inventory_status_text($row['inventory_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="index.php?action=inventory&query=inventory_detail&inventory_id=<?php echo $row['inventory_id']; ?>" class="btn btn-info btn-sm">
                                                Chi tiết
                                            </a>

                                            <?php if ((int)$row['inventory_status'] === 0) { ?>
                                                <a href="index.php?action=inventory&query=inventory_edit&inventory_id=<?php echo $row['inventory_id']; ?>" class="btn btn-warning btn-sm">
                                                    Sửa
                                                </a>

                                                <a href="modules/inventory/xuly.php?action=complete&inventory_id=<?php echo $row['inventory_id']; ?>"
                                                    class="btn btn-success btn-sm"
                                                    onclick="return confirm('Bạn có chắc muốn hoàn thành phiếu nhập này không? Sau khi hoàn thành sẽ cập nhật tồn kho và giá vốn.');">
                                                    Hoàn thành
                                                </a>

                                                <a href="modules/inventory/xuly.php?action=cancel&inventory_id=<?php echo $row['inventory_id']; ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Bạn có chắc muốn hủy phiếu nhập này không?');">
                                                    Hủy
                                                </a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="6" class="text-center">Chưa có phiếu nhập kho nào.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
