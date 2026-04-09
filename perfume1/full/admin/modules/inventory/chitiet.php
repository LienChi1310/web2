<?php
$inventory_id = isset($_GET['inventory_id']) ? (int)$_GET['inventory_id'] : 0;

$sql_inventory = "
    SELECT *
    FROM inventory
    WHERE inventory_id = '{$inventory_id}'
    LIMIT 1
";
$query_inventory = mysqli_query($mysqli, $sql_inventory);
$row_inventory = $query_inventory ? mysqli_fetch_assoc($query_inventory) : null;

$sql_detail = "
    SELECT 
        d.*,
        p.product_id,
        p.product_name,
        p.product_quantity AS current_stock
    FROM inventory_detail d
    LEFT JOIN product p ON p.product_id = d.product_id
    WHERE d.inventory_id = '{$inventory_id}'
    ORDER BY d.id ASC
";
$query_detail = mysqli_query($mysqli, $sql_detail);
?>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
            <div class="card-body">
                <?php if (!$row_inventory) { ?>
                    <div class="alert alert-danger">Phiếu nhập không tồn tại.</div>
                <?php } else { ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h4 class="card-title card-title-dash mb-0">
                            Chi tiết phiếu nhập #<?php echo $row_inventory['inventory_id']; ?>
                        </h4>
                        <a href="index.php?action=inventory&query=inventory_list" class="btn btn-light btn-sm">
                            Quay lại
                        </a>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <p><strong>Ngày nhập:</strong> <?php echo date('d/m/Y H:i:s', strtotime($row_inventory['inventory_date'])); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p>
                                <strong>Trạng thái:</strong>
                                <span class="<?php echo inventory_status_class($row_inventory['inventory_status']); ?> p-2 rounded d-inline-block">
                                    <?php echo inventory_status_text($row_inventory['inventory_status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Mã SP</th>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng nhập</th>
                                    <th>Giá nhập</th>
                                    <th>Thành tiền</th>
                                    <th>Tồn hiện tại</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 0;
                                $total = 0;
                                if ($query_detail && mysqli_num_rows($query_detail) > 0) {
                                    while ($row = mysqli_fetch_array($query_detail)) {
                                        $i++;
                                        $line_total = (int)$row['quantity'] * (int)$row['price_import'];
                                        $total += $line_total;
                                ?>
                                        <tr>
                                            <td><?php echo $i; ?></td>
                                            <td><strong>#<?php echo $row['product_id']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                            <td><?php echo (int)$row['quantity']; ?></td>
                                            <td><?php echo number_format((int)$row['price_import'], 0, ',', '.'); ?> đ</td>
                                            <td><?php echo number_format($line_total, 0, ',', '.'); ?> đ</td>
                                            <td><?php echo (int)$row['current_stock']; ?></td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Phiếu nhập chưa có sản phẩm.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Tổng tiền nhập</th>
                                    <th colspan="2"><?php echo number_format($total, 0, ',', '.'); ?> đ</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <?php if ((int)$row_inventory['inventory_status'] == 0) { ?>
                        <div class="mt-4 d-flex flex-wrap gap-2">
                            <a href="index.php?action=inventory&query=inventory_edit&inventory_id=<?php echo $row_inventory['inventory_id']; ?>"
                                class="btn btn-warning btn-sm">
                                Sửa phiếu nhập
                            </a>

                            <a href="modules/inventory/xuly.php?action=complete&inventory_id=<?php echo $row_inventory['inventory_id']; ?>"
                                class="btn btn-success btn-sm"
                                onclick="return confirm('Xác nhận hoàn thành phiếu nhập? Hệ thống sẽ cộng tồn kho và cập nhật giá vốn bình quân.');">
                                Hoàn thành phiếu nhập
                            </a>

                            <a href="modules/inventory/xuly.php?action=cancel&inventory_id=<?php echo $row_inventory['inventory_id']; ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Bạn có chắc muốn hủy phiếu nhập này không?');">
                                Hủy phiếu nhập
                            </a>
                        </div>
                    <?php } ?>

                    <?php if ((int)$row_inventory['inventory_status'] == 1) { ?>
                        <div class="mt-4">
                            <div class="alert alert-success mb-0">
                                Phiếu nhập này đã hoàn thành, không thể chỉnh sửa.
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ((int)$row_inventory['inventory_status'] == -1) { ?>
                        <div class="mt-4">
                            <div class="alert alert-danger mb-0">
                                Phiếu nhập này đã bị hủy.
                            </div>
                        </div>
                    <?php } ?>

                <?php } ?>
            </div>
        </div>
    </div>
</div>
