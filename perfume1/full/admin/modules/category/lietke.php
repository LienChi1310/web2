<?php
$sql_category_list = "SELECT * FROM category ORDER BY category_id DESC";
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

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card" style="border-radius: 20px; overflow: hidden;">
            <div class="card-body">
                <div class="card-content">

                    <div class="main-pane-top d-flex justify-center align-center" style="margin-bottom: 20px;">
                        <div class="input__search p-relative">
                            <form class="search-form" action="#" method="GET">
                                <i class="icon-search p-absolute"></i>
                                <input type="search" class="form-control" placeholder="Search Here" title="Search here">
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-action" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="width: 60px; text-align: center;">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <th style="width: 50px; text-align: center;">STT</th>
                                    <th style="width: 110px; text-align: center;">Hình ảnh</th>
                                    <th style="width: 220px;">Tên danh mục</th>
                                    <th>Mô tả</th>
                                    <th style="width: 100px; text-align: center;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $stt = 0;
                                while ($row = mysqli_fetch_array($query_category_list)) {
                                    $stt++; ?>
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <input
                                                type="checkbox"
                                                class="checkbox"
                                                onclick="testChecked(); getCheckedCheckboxes();"
                                                id="<?php echo $row['category_id'] ?>">
                                        </td>

                                        <td style="text-align: center; vertical-align: middle;"><?php echo $stt; ?></td>

                                        <td style="text-align: center; vertical-align: middle;">
                                            <img
                                                src="modules/category/uploads/<?php echo $row['category_image'] ?>"
                                                alt="<?php echo htmlspecialchars($row['category_name']); ?>"
                                                style="width: 56px; height: 56px; object-fit: cover; border-radius: 12px; border: 1px solid #eee;"
                                                onerror="this.src='images/no-image.png'">
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
