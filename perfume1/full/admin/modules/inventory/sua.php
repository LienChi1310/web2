<?php
$inventory_id = isset($_GET['inventory_id']) ? (int)$_GET['inventory_id'] : 0;

// lấy thông tin phiếu
$sql_inventory = "
    SELECT * FROM inventory
    WHERE inventory_id = '{$inventory_id}'
    LIMIT 1
";
$query_inventory = mysqli_query($mysqli, $sql_inventory);
$row_inventory = mysqli_fetch_assoc($query_inventory);

// không cho sửa nếu đã hoàn thành
if (!$row_inventory || $row_inventory['inventory_status'] != 0) {
    echo '<div class="alert alert-danger">Không thể sửa phiếu nhập này.</div>';
    return;
}

// lấy sản phẩm
$product_sql = "
    SELECT product_id, product_name, product_quantity
    FROM product
    WHERE product_status IN (0,1)
    ORDER BY product_name ASC
";
$product_query = mysqli_query($mysqli, $product_sql);
$products = [];
while ($p = mysqli_fetch_assoc($product_query)) {
    $products[] = $p;
}

// lấy chi tiết phiếu
$sql_detail = "
    SELECT * FROM inventory_detail
    WHERE inventory_id = '{$inventory_id}'
";
$query_detail = mysqli_query($mysqli, $sql_detail);
$details = [];
while ($d = mysqli_fetch_assoc($query_detail)) {
    $details[] = $d;
}
?>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
            <div class="card-body">
                <h4 class="card-title card-title-dash mb-4">
                    Sửa phiếu nhập #<?php echo $inventory_id; ?>
                </h4>

                <form action="modules/inventory/xuly.php" method="POST">
                    <input type="hidden" name="inventory_id" value="<?php echo $inventory_id; ?>">

                    <div class="mb-3">
                        <label class="form-label">Ngày nhập</label>
                        <input type="datetime-local"
                               name="inventory_date"
                               class="form-control"
                               value="<?php echo date('Y-m-d\TH:i', strtotime($row_inventory['inventory_date'])); ?>"
                               required>
                    </div>

                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0">Danh sách sản phẩm</label>
                        <button type="button" class="btn btn-success btn-sm" onclick="addInventoryRow()">+ Thêm dòng</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th width="140">Số lượng</th>
                                    <th width="180">Giá nhập</th>
                                    <th width="120">Xóa</th>
                                </tr>
                            </thead>
                            <tbody id="inventory-body">

                                <?php foreach ($details as $row) { ?>
                                    <tr>
                                        <td>
                                            <select name="product_id[]" class="form-control" required>
                                                <option value="">-- Chọn sản phẩm --</option>
                                                <?php foreach ($products as $p) { ?>
                                                    <option value="<?php echo $p['product_id']; ?>"
                                                        <?php if ($p['product_id'] == $row['product_id']) echo 'selected'; ?>>
                                                        <?php echo htmlspecialchars($p['product_name']); ?>
                                                        (Tồn: <?php echo (int)$p['product_quantity']; ?>)
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="quantity[]" class="form-control"
                                                   value="<?php echo $row['quantity']; ?>" min="1" required>
                                        </td>
                                        <td>
                                            <input type="number" name="price_import[]" class="form-control"
                                                   value="<?php echo $row['price_import']; ?>" min="0" required>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeInventoryRow(this)">Xóa</button>
                                        </td>
                                    </tr>
                                <?php } ?>

                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" name="inventory_edit" class="btn btn-primary">
                            Cập nhật phiếu nhập
                        </button>

                        <a href="index.php?action=inventory&query=inventory_detail&inventory_id=<?php echo $inventory_id; ?>"
                           class="btn btn-light">
                            Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function addInventoryRow() {
    const tbody = document.getElementById('inventory-body');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <select name="product_id[]" class="form-control" required>
                <option value="">-- Chọn sản phẩm --</option>
                <?php foreach ($products as $p) { ?>
                    <option value="<?php echo $p['product_id']; ?>">
                        <?php echo htmlspecialchars($p['product_name']); ?> (Tồn: <?php echo (int)$p['product_quantity']; ?>)
                    </option>
                <?php } ?>
            </select>
        </td>
        <td>
            <input type="number" name="quantity[]" class="form-control" min="1" required>
        </td>
        <td>
            <input type="number" name="price_import[]" class="form-control" min="0" required>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeInventoryRow(this)">Xóa</button>
        </td>
    `;
    tbody.appendChild(row);
}

function removeInventoryRow(btn) {
    const tbody = document.getElementById('inventory-body');
    if (tbody.rows.length <= 1) {
        alert('Phải có ít nhất 1 sản phẩm');
        return;
    }
    btn.closest('tr').remove();
}
</script>