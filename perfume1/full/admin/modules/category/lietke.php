<?php
// Handle search
$search_filter = '';
$search_keyword = '';
if (isset($_GET['category_search']) && $_GET['category_search'] !== '') {
    $search_keyword = $_GET['category_search'];
    $search_filter = " AND category_name LIKE '%" . mysqli_real_escape_string($mysqli, $search_keyword) . "%'";
}

// Handle sorting
$sort_column = 'category_id';
$sort_order = 'DESC';
$allowed_sorts = ['category_name', 'category_id'];

if (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sorts)) {
    $sort_column = $_GET['sort'];
    $sort_order = (isset($_GET['order']) && $_GET['order'] === 'ASC') ? 'ASC' : 'DESC';
}

$sql_category_list = "SELECT * FROM category WHERE 1=1 $search_filter ORDER BY $sort_column $sort_order";
$query_category_list = mysqli_query($mysqli, $sql_category_list);
?>

<div class="row">
    <div class="col">
        <div class="header__list d-flex space-between align-center" style="margin-bottom: 20px;">
            <h3 class="card-title" style="margin: 0;">Danh mục sản phẩm</h3>
            <div class="action_group">
                <a href="?action=category&query=category_add" class="button button-dark">Thêm danh mục</a>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col">
        <form method="GET" action="index.php" class="d-flex align-items-center" style="gap:10px; flex-wrap:wrap;">
            <input type="hidden" name="action" value="category">
            <input type="hidden" name="query" value="category_list">

            <div style="flex: 1; text-align: right;">
                <div class="input__search p-relative" style="display: inline-block; width: 250px;">
                    <i class="icon-search p-absolute"></i>
                    <input type="text" id="categorySearchInput" name="category_search" class="form-control" placeholder="Tìm kiếm danh mục..." value="<?php echo isset($_GET['category_search']) ? htmlspecialchars($_GET['category_search']) : ''; ?>">
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card" style="border-radius: 20px; overflow: hidden;">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover table-action" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th style="width: 50px; text-align: center;">STT</th>
                                <th style="width: 110px; text-align: center;">Hình ảnh</th>
                                <th style="width: 220px; cursor: pointer;"><a href="?action=category&query=category_list&sort=category_name&order=<?php echo ($sort_column === 'category_name' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&category_search=<?php echo isset($_GET['category_search']) ? urlencode($_GET['category_search']) : ''; ?>" style="color: inherit; text-decoration: none;">Tên danh mục <?php if ($sort_column === 'category_name') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th>Mô tả</th>
                                <th style="width: 100px; text-align: center;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $stt = 1;
                            while ($row = mysqli_fetch_array($query_category_list)) {
                            ?>
                                <tr>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <input
                                            type="checkbox"
                                            class="checkbox"
                                            onclick="testChecked(); getCheckedCheckboxes();"
                                            id="<?php echo $row['category_id'] ?>">
                                    </td>

                                    <td style="text-align: center; vertical-align: middle;"><?php echo $stt;
                                                                                            $stt++; ?></td>

                                    <td style="text-align: center; vertical-align: middle;">
                                        <img
                                            src="modules/category/uploads/<?php echo $row['category_image'] ?>"
                                            alt="<?php echo htmlspecialchars($row['category_name']); ?>"
                                            style="width: 56px; height: 56px; object-fit: cover; border-radius: 12px; border: 1px solid #eee;"
                                            onerror="this.src='images/placeholder-image.webp'">
                                    </td>

                                    <td style="vertical-align: middle; font-weight: 500;">
                                        <?php echo htmlspecialchars($row['category_name']); ?>
                                    </td>

                                    <td style="vertical-align: middle; color: #555; line-height: 1.5;">
                                        <?php echo !empty($row['category_description']) ? htmlspecialchars($row['category_description']) : '<span style="color:#999;">Chưa có mô tả</span>'; ?>
                                    </td>

                                    <td style="text-align: center; vertical-align: middle;">
                                        <a href="?action=category&query=category_edit&category_id=<?php echo $row['category_id'] ?>" title="Sửa">
                                            <div style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid #ddd; border-radius: 10px; background: #fff;">
                                                <img src="images/icon-edit.png" alt="edit" style="width: 18px; height: 18px;">
                                            </div>
                                        </a>
                                    </td>
                                </tr>
                            <?php }
                            if (mysqli_num_rows($query_category_list) == 0) {
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="alert alert-info" style="margin: 20px auto; text-align: center; width: 100%; font-style: italic; color: #000; font-weight: normal;">
                                            Không tìm thấy danh mục nào phù hợp với bộ lọc của bạn
                                        </div>
                                    </td>
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

<div class="dialog__control">
    <div class="control__box">
        <a href="#" class="button__control" id="btnDelete">Xóa</a>
    </div>
</div>

<script>
    // Auto-search functionality
    var categorySearchInput = document.getElementById('categorySearchInput');
    var categorySearchForm = categorySearchInput ? categorySearchInput.closest('form') : null;
    var categorySearchTimeout;

    if (categorySearchInput && categorySearchForm) {
        categorySearchInput.addEventListener('keyup', function() {
            clearTimeout(categorySearchTimeout);
            categorySearchTimeout = setTimeout(function() {
                categorySearchForm.submit();
            }, 500);
        });
    }
</script>

<script>
    var btnDelete = document.getElementById("btnDelete");
    var checkAll = document.getElementById("checkAll");
    var checkboxes = document.getElementsByClassName("checkbox");
    var dialogControl = document.querySelector('.dialog__control');

    checkAll.addEventListener("click", function() {
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = checkAll.checked;
        }
        testChecked();
        getCheckedCheckboxes();
    });

    function testChecked() {
        var count = 0;
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                count++;
            }
        }

        if (count > 0) {
            dialogControl.classList.add('active');
        } else {
            dialogControl.classList.remove('active');
            checkAll.checked = false;
        }
    }

    function getCheckedCheckboxes() {
        var checkeds = document.querySelectorAll('.checkbox:checked');
        var checkedIds = [];
        for (var i = 0; i < checkeds.length; i++) {
            checkedIds.push(checkeds[i].id);
        }
        btnDelete.href = "modules/category/xuly.php?data=" + JSON.stringify(checkedIds);
    }
</script>
