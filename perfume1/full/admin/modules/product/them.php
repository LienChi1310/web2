<div class="row" style="margin-bottom: 10px;">
    <div class="col d-flex" style="justify-content: space-between; align-items: flex-end;">
        <h3 class="card-title">
            Thêm sản phẩm
        </h3>
        <a href="index.php?action=product&query=product_list" class="btn btn-outline-dark btn-fw">
            <i class="mdi mdi-reply"></i>
            Quay lại
        </a>
    </div>
</div>

<form method="POST" action="modules/product/xuly.php" id="form-product" enctype="multipart/form-data">
    <div class="row">
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="card-content">
                        <div class="input-item form-group">
                            <label class="d-block">Mã sản phẩm</label>
                            <input type="text" class="d-block form-control text-muted" value="Mã sẽ được sinh tự động (ID)" readonly>
                            <!-- <small class="text-muted">Mã sẽ được sinh tự động (ID)</small> -->
                        </div>

                        <div class="input-item form-group">
                            <label for="product_name" class="d-block">Tên sản phẩm</label>
                            <input type="text" id="product_name" name="product_name" class="d-block form-control" value="" placeholder="Tên sản phẩm" required>
                            <span class="form-message"></span>
                        </div>

                        <div class="input-item form-group">
                            <label for="product_brand" class="d-block">Thương hiệu sản phẩm</label>
                            <select name="product_brand" id="product_brand" class="form-control select_brand">
                                <option value="0">Chưa xác định</option>
                                <?php
                                $sql_brand_list = "SELECT * FROM brand ORDER BY brand_id DESC";
                                $query_brand_list = mysqli_query($mysqli, $sql_brand_list);
                                while ($row_brand = mysqli_fetch_array($query_brand_list)) {
                                ?>
                                    <option value="<?php echo $row_brand['brand_id']; ?>"><?php echo $row_brand['brand_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="input-item form-group">
                            <label for="product_capacity" class="d-block">Dung tích sản phẩm (ml)</label>
                            <select name="product_capacity" id="product_capacity" class="form-control select_capacity" onchange="handleCapacityChange(this)">
                                <option value="0">Chưa xác định</option>
                                <?php
                                $sql_capacity_list = "SELECT * FROM capacity ORDER BY capacity_id ASC";
                                $query_capacity_list = mysqli_query($mysqli, $sql_capacity_list);
                                while ($row_capacity = mysqli_fetch_array($query_capacity_list)) {
                                ?>
                                    <option value="<?php echo $row_capacity['capacity_id']; ?>"><?php echo $row_capacity['capacity_name']; ?></option>
                                <?php } ?>
                                <option value="_add_new" style="color: #000000; font-weight: bold;">+ Thêm dung tích mới</option>
                            </select>
                            <div id="capacityFormContainer" style="display: none; margin-top: 10px; padding: 10px; background: #f5f5f5; border-radius: 4px;">
                                <label>Nhập dung tích mới (ml):</label>
                                <input type="number" id="new_capacity_value" class="form-control" placeholder="Ví dụ: 50, 100, 200..." min="1" style="margin: 5px 0;">
                                <span class="form-message" id="capacityErrorMsg" style="color: #dc3545; display: block; font-size: 12px; margin-bottom: 8px;"></span>
                                <div style="display: flex; gap: 5px; margin-top: 8px;">
                                    <button type="button" class="btn btn-success btn-sm" onclick="addNewCapacity()">Lưu</button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="cancelCapacityForm()">Hủy</button>
                                </div>
                            </div>
                        </div>

                        <div class="input-item form-group">
                            <label for="product_category" class="d-block">Danh mục sản phẩm</label>
                            <select name="product_category" id="product_category" class="form-control select_category">
                                <option value="0">Chưa phân loại</option>
                                <?php
                                $sql_category_list = "SELECT * FROM category ORDER BY category_id DESC";
                                $query_category_list = mysqli_query($mysqli, $sql_category_list);
                                while ($row_category = mysqli_fetch_array($query_category_list)) {
                                ?>
                                    <option value="<?php echo $row_category['category_id']; ?>"><?php echo $row_category['category_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="input-item form-group">
                            <label for="product_price_import" class="d-block">Giá vốn / giá nhập</label>
                            <input class="d-block form-control" id="product_price_import" name="product_price_import" type="number" min="0" value="" placeholder="Nhập giá vốn" required>
                            <span class="form-message"></span>
                        </div>

                        <div class="input-item form-group">
                            <label for="product_profit_percent" class="d-block">% lợi nhuận mong muốn</label>
                            <input class="d-block form-control" id="product_profit_percent" name="product_profit_percent" type="number" min="0" value="20" placeholder="Ví dụ: 20">
                            <small class="text-muted">Giá bán sẽ tự tính = giá vốn x (100 + % lợi nhuận) / 100</small>
                            <span class="form-message"></span>
                        </div>

                        <div class="input-item form-group">
                            <label for="product_price" class="d-block">Giá bán ra sản phẩm</label>
                            <input class="d-block form-control" id="product_price" name="product_price" type="number" min="0" value="" placeholder="Tự động tính từ giá vốn và % lợi nhuận" required readonly>
                            <span class="form-message"></span>
                        </div>

                        <div class="input-item form-group">
                            <label for="product_description" class="d-block">Mô tả sản phẩm</label>
                            <textarea class="d-block form-control" id="product_description" name="product_description" style="min-height: 300px; padding: 10px; font-family: inherit; font-size: 14px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                            <span class="form-message"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="card-content">
                        <div class="main-pane-top">
                            <h4 class="card-title"></h4>
                        </div>

                        <div class="input-item form-group">
                            <label class="d-block" for="product_image">Hình ảnh</label>
                            <div class="image-box w-100">
                                <figure class="image-container p-relative">
                                    <img id="chosen-image">
                                    <figcaption id="file-name"></figcaption>
                                </figure>
                                <input type="file" class="d-none" id="product_image" name="product_image" accept="image/*">
                                <label class="label-for-image" for="product_image">
                                    <i class="fas fa-upload"></i> &nbsp; Tải lên hình ảnh
                                </label>
                            </div>
                            <small class="text-muted">Không bắt buộc phải có hình khi tạo mới</small>
                        </div>

                        <div class="input-item form-group">
                            <label for="product_sale" class="d-block">Sale (%)</label>
                            <input class="d-block form-control" id="product_sale" name="product_sale" type="number" min="0" value="0" placeholder="product sale">
                            <span class="form-message"></span>
                        </div>

                        <div class="input-item form-group">
                            <label for="product_price_final" class="d-block">Giá bán cuối cùng (sau khi - sale)</label>
                            <input class="d-block form-control" id="product_price_final" type="number" min="0" readonly placeholder="Giá bán cuối">
                            <small class="text-muted">Giá này là những gì khách hàng sẽ trả</small>
                        </div>

                        <div class="input-item form-group">
                            <label for="product_status" class="d-block">Trạng thái</label>
                            <select name="product_status" id="product_status" class="form-control">
                                <option value="1">Hiển thị / Đang bán</option>
                                <option value="0">Ẩn / Tạm dừng bán</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="w-100" style="text-align: left;">
        <button type="submit" name="product_add" class="btn btn-primary btn-icon-text">
            <i class="ti-file btn-icon-prepend"></i>
            Thêm
        </button>
    </div>
</form>

<script>
    $('.select_brand').chosen();
    $('.select_capacity').chosen();
    $('.select_category').chosen();

    // ===== REAL-TIME PRICE CALCULATION =====
    const priceImportInput = document.getElementById('product_price_import');
    const profitInput = document.getElementById('product_profit_percent');
    const saleInput = document.getElementById('product_sale');
    const priceOutput = document.getElementById('product_price');
    const priceFinalOutput = document.getElementById('product_price_final');

    /**
     * Calculate sell price based on import price and profit percentage
     * Formula: sell_price = import_price × (100 + profit_percent) / 100
     * Final price = sell_price - (sell_price × sale_percent / 100)
     */
    function calculatePrice() {
        const importPrice = parseInt(priceImportInput.value) || 0;
        const profitPercent = parseInt(profitInput.value) || 0;
        const salePercent = parseInt(saleInput.value) || 0;

        if (importPrice <= 0) {
            priceOutput.value = 0;
            priceFinalOutput.value = 0;
            return;
        }

        const sellPrice = Math.round(importPrice * (100 + profitPercent) / 100);
        const finalPrice = Math.round(sellPrice - (sellPrice * salePercent / 100));

        priceOutput.value = sellPrice;
        priceFinalOutput.value = finalPrice > 0 ? finalPrice : 0;
    }

    // Attach event listeners
    if (priceImportInput && profitInput && priceOutput) {
        priceImportInput.addEventListener('input', calculatePrice);
        priceImportInput.addEventListener('change', calculatePrice);

        profitInput.addEventListener('input', calculatePrice);
        profitInput.addEventListener('change', calculatePrice);

        saleInput.addEventListener('input', calculatePrice);
        saleInput.addEventListener('change', calculatePrice);

        // Initial calculation on page load
        calculatePrice();
    }

    // Initialize TinyMCE
    tinymce.init({
        selector: '#product_description',
        height: 300,
        min_height: 300,
        max_height: 800,
        resize: true,
        menubar: 'edit view insert format tools',
        plugins: 'link lists image table code help paste',
        toolbar: 'formatselect | bold italic underline | bullist numlist | link image table | code | removeformat | undo redo',
        paste_data_images: true,
        relative_urls: false,
        branding: false
    });

    // ===== HANDLE CAPACITY DROPDOWN CHANGE =====
    function handleCapacityChange(selectElement) {
        if (selectElement.value === '_add_new') {
            document.getElementById('capacityFormContainer').style.display = 'block';
            document.getElementById('new_capacity_value').focus();
            selectElement.value = '0'; // Reset dropdown to default
        } else {
            document.getElementById('capacityFormContainer').style.display = 'none';
        }
    }

    // Cancel capacity form
    function cancelCapacityForm() {
        document.getElementById('capacityFormContainer').style.display = 'none';
        document.getElementById('new_capacity_value').value = '';
        document.getElementById('capacityErrorMsg').textContent = '';
        document.getElementById('product_capacity').value = '0'; // Reset to default
    }

    // Show error message
    function showCapacityError(msg) {
        document.getElementById('capacityErrorMsg').textContent = msg;
        document.getElementById('new_capacity_value').focus();
    }

    // Clear error message
    function clearCapacityError() {
        document.getElementById('capacityErrorMsg').textContent = '';
    }

    // ===== ADD NEW CAPACITY =====
    function addNewCapacity() {
        let newValue = document.getElementById('new_capacity_value').value.trim();
        if (!newValue) {
            showCapacityError('Vui lòng nhập giá trị dung tích');
            return;
        }

        let valueInt = parseInt(newValue);
        if (isNaN(valueInt) || valueInt <= 0) {
            showCapacityError('Vui lòng nhập số hợp lệ (lớn hơn 0)');
            return;
        }

        clearCapacityError();
        const capacityName = valueInt + 'ml';
        const select = document.getElementById('product_capacity');

        // Check if capacity already exists
        let exists = false;
        let existingId = null;
        for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].text === capacityName) {
                exists = true;
                existingId = select.options[i].value;
                break;
            }
        }

        if (exists) {
            showCapacityError('Dung tích "' + capacityName + '" đã tồn tại');
            // Auto-select existing capacity
            select.value = existingId;
            // Hide form
            setTimeout(() => cancelCapacityForm(), 1000);
            return;
        }

        // Save new capacity to database via AJAX
        fetch('modules/product/save_capacity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'capacity_name=' + encodeURIComponent(capacityName) + '&capacity_value=' + valueInt
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add new option with returned ID
                    let newOption = document.createElement('option');
                    newOption.value = data.capacity_id;
                    newOption.text = capacityName;

                    // Insert before the "+  Thêm dung tích mới" option
                    const addNewOption = select.querySelector('option[value="_add_new"]');
                    if (addNewOption) {
                        select.insertBefore(newOption, addNewOption);
                    } else {
                        select.appendChild(newOption);
                    }

                    // Auto-select the new capacity
                    select.value = data.capacity_id;

                    // Hide form and clear input
                    clearCapacityError();
                    document.getElementById('new_capacity_value').value = '';
                    document.getElementById('capacityFormContainer').style.display = 'none';
                } else {
                    showCapacityError(data.error || 'Lỗi khi lưu dung tích');
                }
            })
            .catch(error => {
                showCapacityError('Lỗi kết nối: ' + error.message);
            });
    }

    // Attach submit button event listener on document ready
    // (No longer needed - using direct onclick handler on button)
</script>

<script>
    Validator({
        form: '#form-product',
        errorSelector: '.form-message',
        rules: [
            Validator.isRequired('#product_name', 'Vui lòng nhập tên sản phẩm'),
            Validator.isRequired('#product_price_import', 'Vui lòng nhập giá vốn')
        ],
        onSubmit: function(data) {
            console.log(data);
        }
    });
</script>

<script>
    function calculateSellPrice() {
        const importPrice = parseFloat(document.getElementById('product_price_import').value) || 0;
        const profitPercent = parseFloat(document.getElementById('product_profit_percent').value) || 0;
        const sellPrice = Math.round(importPrice * (100 + profitPercent) / 100);
        document.getElementById('product_price').value = sellPrice > 0 ? sellPrice : '';
    }

    document.getElementById('product_price_import').addEventListener('input', calculateSellPrice);
    document.getElementById('product_profit_percent').addEventListener('input', calculateSellPrice);
    document.getElementById('product_sale').addEventListener('input', calculateSellPrice);
    calculateSellPrice();

    // Form Validation for Add Product
    const addForm = document.getElementById('form-product');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            let isValid = true;
            let errorMsg = [];

            // Validate product name
            const productName = document.getElementById('product_name');
            if (!productName.value.trim()) {
                isValid = false;
                errorMsg.push('Tên sản phẩm không được để trống');
            } else if (productName.value.trim().length < 3) {
                isValid = false;
                errorMsg.push('Tên sản phẩm phải có ít nhất 3 ký tự');
            }

            // Validate price import
            const priceImport = document.getElementById('product_price_import');
            if (!priceImport.value || parseInt(priceImport.value) <= 0) {
                isValid = false;
                errorMsg.push('Giá vốn phải lớn hơn 0');
            }

            // Validate profit percent
            const profitPercent = document.getElementById('product_profit_percent');
            if (profitPercent.value < 0 || profitPercent.value > 200) {
                isValid = false;
                errorMsg.push('% lợi nhuận phải từ 0 đến 200%');
            }

            // Validate sale
            const sale = document.getElementById('product_sale');
            if (sale.value < 0 || sale.value >= 100) {
                isValid = false;
                errorMsg.push('Sale phải từ 0 đến 99%');
            }

            // Image is optional for new products - removed requirement

            if (!isValid) {
                e.preventDefault();
                alert('Vui lòng sửa các lỗi sau:\n' + errorMsg.join('\n'));
                return false;
            }
        });
    }
</script>

<script>
    let uploadButton = document.getElementById("product_image");
    let chosenImage = document.getElementById("chosen-image");
    let fileName = document.getElementById("file-name");

    uploadButton.onchange = () => {
        let reader = new FileReader();
        reader.readAsDataURL(uploadButton.files[0]);
        reader.onload = () => {
            chosenImage.setAttribute("src", reader.result);
        }
        fileName.textContent = uploadButton.files[0].name;
    };
</script>
