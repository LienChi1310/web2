<?php
$product_sql = "
    SELECT product_id, product_name, product_quantity, product_price_import, product_status
    FROM product
    WHERE product_status IN (0, 1)
    ORDER BY product_name ASC
";
$product_query = mysqli_query($mysqli, $product_sql);
$products = [];
if ($product_query) {
    while ($item = mysqli_fetch_assoc($product_query)) {
        $products[] = $item;
    }
}
?>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
            <div class="card-body">
                <h4 class="card-title card-title-dash mb-4">Thêm phiếu nhập kho</h4>

                <form action="modules/inventory/xuly.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Ngày nhập</label>
                        <input type="datetime-local" name="inventory_date" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                    </div>

                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0">Danh sách sản phẩm nhập</label>
                        <button type="button" class="btn btn-success btn-sm" onclick="addInventoryRow()">+ Thêm dòng</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="inventory-table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th width="140">Số lượng</th>
                                    <th width="180">Giá nhập</th>
                                    <th width="120">Xóa</th>
                                </tr>
                            </thead>
                            <tbody id="inventory-body">
                                <tr>
                                    <td>
                                        <select name="product_id[]" class="form-control" required>
                                            <option value="">-- Chọn sản phẩm --</option>
                                            <?php foreach ($products as $product) { ?>
                                                <option value="<?php echo $product['product_id']; ?>">
                                                    <?php echo htmlspecialchars($product['product_name']); ?>
                                                    (Tồn: <?php echo (int)$product['product_quantity']; ?>)
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
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" name="inventory_add" class="btn btn-primary">
                            Lưu phiếu nhập
                        </button>
                        <a href="index.php?action=inventory&query=inventory_list" class="btn btn-light">
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
                <?php foreach ($products as $product) { ?>
                    <option value="<?php echo $product['product_id']; ?>">
                        <?php echo htmlspecialchars($product['product_name']); ?> (Tồn: <?php echo (int)$product['product_quantity']; ?>)
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

function removeInventoryRow(button) {
    const tbody = document.getElementById('inventory-body');
    if (tbody.rows.length <= 1) {
        alert('Phiếu nhập phải có ít nhất 1 sản phẩm.');
        return;
    }
    button.closest('tr').remove();
}
</script>