<?php
$product_sql = "
    SELECT product_id, product_name, product_quantity, product_category, product_brand
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

// Get all categories and brands
$categories = get_all_categories($mysqli);
$brands = get_all_brands($mysqli);
?>

<div class="row" style="margin-bottom: 10px;">
    <div class="col d-flex" style="justify-content: space-between; align-items: flex-end;">
        <h3 class="card-title">
            Thêm phiếu nhập kho
        </h3>
        <a href="index.php?action=inventory&query=inventory_list" class="btn btn-outline-dark btn-fw">
            <i class="mdi mdi-reply"></i>
            Quay lại
        </a>
    </div>
</div>

<form method="POST" action="modules/inventory/xuly.php" id="form-inventory">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="card-content">

                        <div class="input-item form-group">
                            <label for="inventory_date" class="d-block">Ngày nhập</label>
                            <input type="datetime-local" id="inventory_date" name="inventory_date" class="d-block form-control" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                            <span class="form-message"></span>
                        </div>

                        <div class="input-item form-group">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">Danh sách sản phẩm nhập</label>
                                <button type="button" class="btn btn-success btn-sm" onclick="addInventoryRow()">+ Thêm dòng</button>
                            </div>

                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-bordered" id="inventory-table">
                                    <thead>
                                        <tr>
                                            <th width="40" class="text-center">STT</th>
                                            <th width="200" class="text-center">Danh mục</th>
                                            <th width="200" class="text-center">Thương hiệu</th>
                                            <th class="text-center">Sản phẩm</th>
                                            <th width="140" class="text-center">Số lượng</th>
                                            <th width="180" class="text-center">Giá nhập</th>
                                            <th width="100" class="text-center">Xóa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="inventory-body">
                                        <tr>
                                            <td class="text-center" style="vertical-align: middle; background-color: #f5f5f5;">
                                                <span class="row-number">1</span>
                                            </td>
                                            <td>
                                                <select name="category_id[]" class="form-control category-select" onchange="updateBrandAndProduct(this)">
                                                    <option value="">-- Tất cả --</option>
                                                    <?php foreach ($categories as $cat) { ?>
                                                        <option value="<?php echo $cat['category_id']; ?>">
                                                            <?php echo htmlspecialchars($cat['category_name']); ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="brand_id[]" class="form-control brand-select" onchange="updateProduct(this)">
                                                    <option value="">-- Tất cả --</option>
                                                    <?php foreach ($brands as $brand) { ?>
                                                        <option value="<?php echo $brand['brand_id']; ?>">
                                                            <?php echo htmlspecialchars($brand['brand_name']); ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="product_id[]" class="form-control inventory-product-select" required>
                                                    <option value="">-- Chọn sản phẩm --</option>
                                                    <?php foreach ($products as $product) { ?>
                                                        <option value="<?php echo $product['product_id']; ?>"
                                                            data-category="<?php echo $product['product_category']; ?>"
                                                            data-brand="<?php echo $product['product_brand']; ?>">
                                                            <?php echo htmlspecialchars($product['product_name']); ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="quantity[]" class="form-control text-center" min="1" required>
                                            </td>
                                            <td>
                                                <input type="number" name="price_import[]" class="form-control text-center" min="0" required>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="removeInventoryRow(this)">Xóa</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <span class="form-message"></span>
                        </div>

                        <div class="input-item form-group">
                            <button type="submit" name="inventory_add" class="btn btn-primary" style="width: 100%;">
                                <i class="mdi mdi-content-save"></i>
                                Lưu phiếu nhập
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .table-responsive select {
        max-height: 300px;
    }

    .table-responsive option {
        padding: 5px;
    }
</style>

<script>
    // Store all products data
    const allProducts = <?php echo json_encode($products); ?>;
    const allBrands = <?php echo json_encode($brands); ?>;
    const allCategories = <?php echo json_encode($categories); ?>;

    // DEBUG: Log data to console
    console.log('All Products:', allProducts);
    console.log('All Brands:', allBrands);
    console.log('All Categories:', allCategories);

    function updateRowNumber() {
        const tbody = document.getElementById('inventory-body');
        const rows = tbody.querySelectorAll('tr');
        rows.forEach((row, index) => {
            const sttCell = row.querySelector('.row-number');
            if (sttCell) {
                sttCell.textContent = index + 1;
            }
        });
    }

    function updateBrandAndProduct(categorySelect) {
        const row = categorySelect.closest('tr');
        const categoryId = parseInt(categorySelect.value) || 0;
        const brandSelect = row.querySelector('.brand-select');
        const productSelect = row.querySelector('.inventory-product-select');

        console.log('Category selected:', categoryId);

        // Update brand select - filter brands that have products in this category
        let brandOptions = '<option value="">-- Tất cả --</option>';
        if (categoryId > 0) {
            const filteredBrands = allBrands.filter(brand => {
                return allProducts.some(p =>
                    parseInt(p.product_category) === categoryId && parseInt(p.product_brand) === parseInt(brand.brand_id)
                );
            });
            console.log('Filtered brands for category', categoryId, ':', filteredBrands);
            filteredBrands.forEach(brand => {
                brandOptions += `<option value="${brand.brand_id}">${brand.brand_name}</option>`;
            });
        } else {
            allBrands.forEach(brand => {
                brandOptions += `<option value="${brand.brand_id}">${brand.brand_name}</option>`;
            });
        }
        brandSelect.innerHTML = brandOptions;

        // Update product select - show all products of selected category
        let productOptions = '<option value="">-- Chọn sản phẩm --</option>';
        if (categoryId > 0) {
            const categoryProducts = allProducts.filter(p => parseInt(p.product_category) === categoryId);
            console.log('Products for category', categoryId, ':', categoryProducts);
            categoryProducts.forEach(product => {
                productOptions += `<option value="${product.product_id}" data-category="${product.product_category}" data-brand="${product.product_brand}">${product.product_name}</option>`;
            });
        } else {
            allProducts.forEach(product => {
                productOptions += `<option value="${product.product_id}" data-category="${product.product_category}" data-brand="${product.product_brand}">${product.product_name}</option>`;
            });
        }
        productSelect.innerHTML = productOptions;

        // Reset selects
        brandSelect.value = '';
        productSelect.value = '';

        // Trigger Chosen to refresh UI
        $(brandSelect).trigger('chosen:updated');
        $(productSelect).trigger('chosen:updated');
    }

    function updateProduct(brandSelect) {
        const row = brandSelect.closest('tr');
        const categorySelect = row.querySelector('.category-select');
        const productSelect = row.querySelector('.inventory-product-select');

        const categoryId = parseInt(categorySelect.value) || 0;
        const brandId = parseInt(brandSelect.value) || 0;

        console.log('Brand selected:', brandId, 'Category:', categoryId);

        // Build product list based on filters
        let productOptions = '<option value="">-- Chọn sản phẩm --</option>';
        let filteredProducts = allProducts;

        if (categoryId > 0) {
            filteredProducts = filteredProducts.filter(p => parseInt(p.product_category) === categoryId);
        }

        if (brandId > 0) {
            filteredProducts = filteredProducts.filter(p => parseInt(p.product_brand) === brandId);
        }

        console.log('Filtered products:', filteredProducts);

        // ADD PRODUCTS TO OPTIONS STRING
        filteredProducts.forEach(product => {
            productOptions += `<option value="${product.product_id}" data-category="${product.product_category}" data-brand="${product.product_brand}">${product.product_name}</option>`;
        });

        productSelect.innerHTML = productOptions;
        productSelect.value = '';

        // Trigger Chosen to refresh UI
        $(productSelect).trigger('chosen:updated');
    }

    function addInventoryRow() {
        const tbody = document.getElementById('inventory-body');
        const newRow = document.createElement('tr');
        const nextNumber = tbody.querySelectorAll('tr').length + 1;

        newRow.innerHTML = `
            <td class="text-center" style="vertical-align: middle; background-color: #f5f5f5;">
                <span class="row-number">${nextNumber}</span>
            </td>
            <td>
                <select name="category_id[]" class="form-control category-select" onchange="updateBrandAndProduct(this)">
                    <option value="">-- Tất cả --</option>
                    ${allCategories.map(cat => 
                        `<option value="${cat.category_id}">${cat.category_name}</option>`
                    ).join('')}
                </select>
            </td>
            <td>
                <select name="brand_id[]" class="form-control brand-select" onchange="updateProduct(this)">
                    <option value="">-- Tất cả --</option>
                    ${allBrands.map(brand => 
                        `<option value="${brand.brand_id}">${brand.brand_name}</option>`
                    ).join('')}
                </select>
            </td>
            <td>
                <select name="product_id[]" class="form-control inventory-product-select" required>
                    <option value="">-- Chọn sản phẩm --</option>
                    ${allProducts.map(product => 
                        `<option value="${product.product_id}" data-category="${product.product_category}" data-brand="${product.product_brand}">
                            ${product.product_name}
                        </option>`
                    ).join('')}
                </select>
            </td>
            <td>
                <input type="number" name="quantity[]" class="form-control text-center" min="1" required>
            </td>
            <td>
                <input type="number" name="price_import[]" class="form-control text-center" min="0" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeInventoryRow(this)">Xóa</button>
            </td>
        `;

        tbody.appendChild(newRow);
        updateRowNumber();

        // Init Chosen for new selects
        const newCategory = newRow.querySelector('.category-select');
        const newBrand = newRow.querySelector('.brand-select');
        const newProduct = newRow.querySelector('.inventory-product-select');

        $(newCategory).chosen({
            search_contains: true,
            width: '100%'
        });
        $(newBrand).chosen({
            search_contains: true,
            width: '100%'
        });
        $(newProduct).chosen({
            search_contains: true,
            width: '100%'
        });
    }

    function removeInventoryRow(button) {
        const tbody = document.getElementById('inventory-body');
        if (tbody.rows.length <= 1) {
            alert('Phiếu nhập phải có ít nhất 1 sản phẩm.');
            return;
        }
        button.closest('tr').remove();
        updateRowNumber();
    }

    // Initialize Chosen for all dropdowns on page load
    $(document).ready(function() {
        $('.category-select').chosen({
            search_contains: true,
            width: '100%'
        });
        $('.brand-select').chosen({
            search_contains: true,
            width: '100%'
        });
        $('.inventory-product-select').chosen({
            search_contains: true,
            width: '100%'
        });
    });
</script>
