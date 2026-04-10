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
                                                <div class="custom-dropdown-container">
                                                    <input type="hidden" name="category_id[]" class="category-id-input">
                                                    <div class="custom-dropdown">
                                                        <input type="text" class="search-input" placeholder="Chọn danh mục...">
                                                        <div class="dropdown-options">
                                                            <div class="dropdown-option" data-value="">-- Tất cả --</div>
                                                            <?php foreach ($categories as $cat) { ?>
                                                                <div class="dropdown-option"
                                                                    data-value="<?php echo $cat['category_id']; ?>"
                                                                    data-name="<?php echo htmlspecialchars($cat['category_name']); ?>">
                                                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="custom-dropdown-container">
                                                    <input type="hidden" name="brand_id[]" class="brand-id-input">
                                                    <div class="custom-dropdown">
                                                        <input type="text" class="search-input" placeholder="Chọn thương hiệu...">
                                                        <div class="dropdown-options">
                                                            <div class="dropdown-option" data-value="">-- Tất cả --</div>
                                                            <?php foreach ($brands as $brand) { ?>
                                                                <div class="dropdown-option"
                                                                    data-value="<?php echo $brand['brand_id']; ?>"
                                                                    data-name="<?php echo htmlspecialchars($brand['brand_name']); ?>">
                                                                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="custom-dropdown-container">
                                                    <input type="hidden" name="product_id[]" class="product-id-input" required>
                                                    <div class="custom-dropdown">
                                                        <input type="text" class="search-input" placeholder="Chọn sản phẩm...">
                                                        <div class="dropdown-options">
                                                            <?php foreach ($products as $product) { ?>
                                                                <div class="dropdown-option"
                                                                    data-value="<?php echo $product['product_id']; ?>"
                                                                    data-category="<?php echo $product['product_category']; ?>"
                                                                    data-brand="<?php echo $product['product_brand']; ?>"
                                                                    data-name="<?php echo htmlspecialchars($product['product_name']); ?>">
                                                                    #<?php echo $product['product_id']; ?> - <?php echo htmlspecialchars($product['product_name']); ?>
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
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

    /* Custom Dropdown CSS - Bootstrap Theme */
    .custom-dropdown-container {
        position: relative;
        width: 100%;
    }

    .custom-dropdown {
        position: relative;
        width: 100%;
    }

    .search-input {
        display: block;
        width: 100%;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        box-sizing: border-box;
        cursor: pointer;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .search-input::placeholder {
        color: #6c757d;
        opacity: 1;
    }

    .search-input:focus {
        outline: none;
        color: #495057;
        background-color: #fff;
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .search-input:disabled {
        background-color: #e9ecef;
        opacity: 1;
        color: #6c757d;
        cursor: not-allowed;
    }

    .dropdown-options {
        position: fixed;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-top: 1px solid #ced4da;
        max-height: 250px;
        overflow-y: auto;
        z-index: 9999;
        display: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        min-width: 300px;
    }

    .dropdown-options.show {
        display: block;
    }

    .dropdown-option {
        padding: 0.5rem 1rem;
        cursor: pointer;
        border-bottom: none;
        transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
        white-space: normal;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #212529;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .dropdown-option:hover {
        background-color: #f8f9fa;
        color: #212529;
    }

    .dropdown-option:active {
        background-color: #e9ecef;
    }

    .dropdown-option.selected {
        background-color: #007bff;
        color: #fff;
    }

    .dropdown-option.selected:hover {
        background-color: #0062cc;
        color: #fff;
    }

    .dropdown-option:last-child {
        border-bottom: none;
    }

    .dropdown-option.hidden {
        display: none;
    }

    /* Scrollbar styling for dropdown */
    .dropdown-options::-webkit-scrollbar {
        width: 8px;
    }

    .dropdown-options::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .dropdown-options::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .dropdown-options::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

<script>
    const allProducts = <?php echo json_encode($products); ?>;
    const allBrands = <?php echo json_encode($brands); ?>;
    const allCategories = <?php echo json_encode($categories); ?>;

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

    function positionDropdown(searchInput, dropdownOptions) {
        const rect = searchInput.getBoundingClientRect();
        dropdownOptions.style.left = (rect.left) + 'px';
        dropdownOptions.style.top = (rect.top + rect.height) + 'px';
        dropdownOptions.style.width = rect.width + 'px';
    }

    function initCustomDropdown(container, dropdownType = 'product') {
        const searchInput = container.querySelector('.search-input');
        const dropdownOptions = container.querySelector('.dropdown-options');
        const hiddenInput = container.querySelector('input[type="hidden"]');
        const row = container.closest('tr');
        const allOptions = dropdownOptions.querySelectorAll('.dropdown-option');

        // Open dropdown on focus
        searchInput.addEventListener('focus', function() {
            dropdownOptions.classList.add('show');
            positionDropdown(searchInput, dropdownOptions);
        });

        // ALSO open dropdown on click (in case focus doesn't trigger immediately)
        searchInput.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownOptions.classList.add('show');
            positionDropdown(searchInput, dropdownOptions);
        });

        // Attach search listener (REUSABLE)
        attachSearchListener(container);

        // Attach option selection listeners based on dropdown type
        if (dropdownType === 'category') {
            attachCategoryOptionListeners(container, row);
        } else if (dropdownType === 'brand') {
            attachBrandOptionListeners(container, row);
        } else if (dropdownType === 'product') {
            attachProductOptionListeners(container);
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                dropdownOptions.classList.remove('show');
            }
        });

        // Reposition dropdown on scroll
        window.addEventListener('scroll', function() {
            if (dropdownOptions.classList.contains('show')) {
                positionDropdown(searchInput, dropdownOptions);
            }
        });
    }

    // HELPER: Re-attach search filter listener
    function attachSearchListener(container) {
        const searchInput = container.querySelector('.search-input');
        const dropdownOptions = container.querySelector('.dropdown-options');

        // Simple approach: just add the listener (duplicate listeners are OK, they filter the same way)
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const currentOptions = dropdownOptions.querySelectorAll('.dropdown-option');

            currentOptions.forEach(option => {
                const optionText = option.innerText.toLowerCase();
                if (searchTerm === '' || optionText.includes(searchTerm)) {
                    option.classList.remove('hidden');
                } else {
                    option.classList.add('hidden');
                }
            });
        });
    }

    function attachCategoryOptionListeners(categoryContainer, row) {
        const categoryInput = categoryContainer.querySelector('input[type="hidden"]');
        const categorySearchInput = categoryContainer.querySelector('.search-input');
        const categoryOptions = categoryContainer.querySelector('.dropdown-options');
        const categoryOptionDivs = categoryOptions.querySelectorAll('.dropdown-option');

        categoryOptionDivs.forEach(option => {
            option.addEventListener('click', function(e) {
                e.stopPropagation();
                const value = this.getAttribute('data-value');
                const name = this.getAttribute('data-name');
                categoryInput.value = value;
                categorySearchInput.value = name || '';
                categoryOptions.classList.remove('show');
                updateBrandAndProductDropdowns(row);
            });
        });
    }

    function updateBrandAndProductDropdowns(row) {
        // FIX: :nth-of-type doesn't work with classes, use querySelectorAll instead
        const containers = row.querySelectorAll('.custom-dropdown-container');
        const categoryContainer = containers[0];
        const brandContainer = containers[1];
        const productContainer = containers[2];

        const categoryInput = categoryContainer.querySelector('input[type="hidden"]');
        const categoryId = parseInt(categoryInput.value) || 0;

        // Update Brand dropdown
        if (brandContainer) {
            const brandInput = brandContainer.querySelector('input[type="hidden"]');
            const brandOptions = brandContainer.querySelector('.dropdown-options');
            const brandSearchInput = brandContainer.querySelector('.search-input');

            let brandHTML = '<div class="dropdown-option" data-value="">-- Tất cả --</div>';

            if (categoryId > 0) {
                // FIXED: Use same filtering logic as sua.php (tested working version)
                const filteredBrands = allBrands.filter(brand => {
                    return allProducts.some(p =>
                        parseInt(p.product_category) === categoryId &&
                        parseInt(p.product_brand) === parseInt(brand.brand_id)
                    );
                });

                filteredBrands.forEach(brand => {
                    brandHTML += `<div class="dropdown-option" data-value="${brand.brand_id}" data-name="${brand.brand_name}">${brand.brand_name}</div>`;
                });
            } else {
                allBrands.forEach(brand => {
                    brandHTML += `<div class="dropdown-option" data-value="${brand.brand_id}" data-name="${brand.brand_name}">${brand.brand_name}</div>`;
                });
            }

            brandOptions.innerHTML = brandHTML;
            brandInput.value = '';
            brandSearchInput.value = '';

            // Re-attach listeners (IMPORTANT: search listener MUST be re-attached after HTML update)
            attachSearchListener(brandContainer);
            attachBrandOptionListeners(brandContainer, row);
        }

        // Update Product dropdown
        updateProductDropdown(row);
    }

    function attachBrandOptionListeners(brandContainer, row) {
        const brandInput = brandContainer.querySelector('input[type="hidden"]');
        const brandSearchInput = brandContainer.querySelector('.search-input');
        const brandOptions = brandContainer.querySelector('.dropdown-options');
        const brandOptionDivs = brandOptions.querySelectorAll('.dropdown-option');

        brandOptionDivs.forEach(option => {
            option.addEventListener('click', function(e) {
                e.stopPropagation();
                const value = this.getAttribute('data-value');
                const name = this.getAttribute('data-name');
                brandInput.value = value;
                brandSearchInput.value = name || '';
                brandOptions.classList.remove('show');
                updateProductDropdown(row);
            });
        });
    }

    function updateProductDropdown(row) {
        // FIX: :nth-of-type doesn't work with classes, use querySelectorAll instead
        const containers = row.querySelectorAll('.custom-dropdown-container');
        const categoryContainer = containers[0];
        const brandContainer = containers[1];
        const productContainer = containers[2];

        const categoryInput = categoryContainer.querySelector('input[type="hidden"]');
        const brandInput = brandContainer.querySelector('input[type="hidden"]');

        const categoryId = parseInt(categoryInput.value) || 0;
        const brandId = parseInt(brandInput.value) || 0;

        if (productContainer) {
            const productOptions = productContainer.querySelector('.dropdown-options');
            const productSearchInput = productContainer.querySelector('.search-input');
            const productIdInput = productContainer.querySelector('input[type="hidden"]');

            // FIXED: Use same filtering logic as sua.php (tested working version)
            let filteredProducts = allProducts;

            if (categoryId > 0) {
                filteredProducts = filteredProducts.filter(p =>
                    parseInt(p.product_category) === categoryId
                );
            }

            if (brandId > 0) {
                filteredProducts = filteredProducts.filter(p =>
                    parseInt(p.product_brand) === brandId
                );
            }

            let productHTML = '';
            filteredProducts.forEach(product => {
                productHTML += `<div class="dropdown-option" 
                                    data-value="${product.product_id}" 
                                    data-name="#${product.product_id} - ${product.product_name}">
                                    #${product.product_id} - ${product.product_name}
                                </div>`;
            });

            productOptions.innerHTML = productHTML;
            productIdInput.value = '';
            productSearchInput.value = '';

            // Re-attach listeners (IMPORTANT: search listener MUST be re-attached after HTML update)
            attachSearchListener(productContainer);
            attachProductOptionListeners(productContainer);
        }
    }

    function attachProductOptionListeners(productContainer) {
        const productOptions = productContainer.querySelector('.dropdown-options');
        const productSearchInput = productContainer.querySelector('.search-input');
        const productIdInput = productContainer.querySelector('input[name="product_id[]"]'); // FIX: Use specific selector
        const productOptionDivs = productOptions.querySelectorAll('.dropdown-option');

        productOptionDivs.forEach(option => {
            option.addEventListener('click', function(e) {
                e.stopPropagation();
                const value = this.getAttribute('data-value');
                const name = this.getAttribute('data-name');
                productIdInput.value = value; // ← Set product_id
                productSearchInput.value = name || '';
                productOptions.classList.remove('show');
            });
        });
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
                <div class="custom-dropdown-container">
                    <input type="hidden" name="category_id[]" class="category-id-input">
                    <div class="custom-dropdown">
                        <input type="text" class="search-input" placeholder="Tìm danh mục...">
                        <div class="dropdown-options">
                            <div class="dropdown-option" data-value="">-- Tất cả --</div>
                            ${allCategories.map(cat =>
                                `<div class="dropdown-option" data-value="${cat.category_id}" data-name="${cat.category_name}">${cat.category_name}</div>`
                            ).join('')}
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <div class="custom-dropdown-container">
                    <input type="hidden" name="brand_id[]" class="brand-id-input">
                    <div class="custom-dropdown">
                        <input type="text" class="search-input" placeholder="Tìm thương hiệu...">
                        <div class="dropdown-options">
                            <div class="dropdown-option" data-value="">-- Tất cả --</div>
                            ${allBrands.map(brand =>
                                `<div class="dropdown-option" data-value="${brand.brand_id}" data-name="${brand.brand_name}">${brand.brand_name}</div>`
                            ).join('')}
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <div class="custom-dropdown-container">
                    <input type="hidden" name="product_id[]" class="product-id-input" required>
                    <div class="custom-dropdown">
                        <input type="text" class="search-input" placeholder="Tìm kiếm sản phẩm...">
                        <div class="dropdown-options">
                            ${allProducts.map(product =>
                                `<div class="dropdown-option" 
                                     data-value="${product.product_id}"
                                     data-name="#${product.product_id} - ${product.product_name}">
                                    #${product.product_id} - ${product.product_name}
                                </div>`
                            ).join('')}
                        </div>
                    </div>
                </div>
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

        // Init dropdowns cho row mới - FIX: use querySelectorAll instead of :nth-of-type()
        const newContainers = newRow.querySelectorAll('.custom-dropdown-container');
        const categoryContainer = newContainers[0];
        const brandContainer = newContainers[1];
        const productContainer = newContainers[2];

        if (categoryContainer) initCustomDropdown(categoryContainer, 'category');
        if (brandContainer) initCustomDropdown(brandContainer, 'brand');
        if (productContainer) initCustomDropdown(productContainer, 'product');
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

    // Init khi page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing custom dropdowns');

        const rows = document.querySelectorAll('#inventory-body tr');
        rows.forEach(row => {
            const dropdownContainers = row.querySelectorAll('.custom-dropdown-container');
            dropdownContainers.forEach((container, index) => {
                if (index === 0) {
                    initCustomDropdown(container, 'category');
                } else if (index === 1) {
                    initCustomDropdown(container, 'brand');
                } else {
                    initCustomDropdown(container, 'product');
                }
            });
        });

        console.log('Custom dropdowns initialized');

        // === FORM VALIDATION ON SUBMIT ===
        const form = document.getElementById('form-inventory');
        if (form) {
            form.addEventListener('submit', function(e) {
                const rows = document.querySelectorAll('#inventory-body tr');
                let hasValidRow = false;
                let errorMsg = [];

                rows.forEach((row, index) => {
                    const productIdInput = row.querySelector('input[name="product_id[]"]');
                    const quantityInput = row.querySelector('input[name="quantity[]"]');
                    const priceInput = row.querySelector('input[name="price_import[]"]');

                    const productId = productIdInput ? parseInt(productIdInput.value) : 0;
                    const quantity = quantityInput ? parseInt(quantityInput.value) : 0;
                    const price = priceInput ? parseInt(priceInput.value) : 0;

                    if (productId > 0 && quantity > 0 && price >= 0) {
                        hasValidRow = true;
                    } else if (productId === 0 || quantity <= 0) {
                        errorMsg.push(`Dòng ${index + 1}: Chưa chọn sản phẩm hoặc số lượng không hợp lệ`);
                    }
                });

                if (!hasValidRow) {
                    e.preventDefault();
                    alert('Lỗi:\n' + (errorMsg.length > 0 ? errorMsg.join('\n') : 'Phiếu nhập phải có ít nhất 1 sản phẩm hợp lệ.'));
                    return false;
                }
            });
        }
    });
</script>
