<?php
// Handle search
$search_filter = '';
$search_keyword = '';
if (isset($_GET['collection_search']) && $_GET['collection_search'] !== '') {
    $search_keyword = $_GET['collection_search'];
    $search_filter = " AND collection_name LIKE '%" . mysqli_real_escape_string($mysqli, $search_keyword) . "%'";
}

// Handle sorting
$sort_column = 'collection_id';
$sort_order = 'DESC';
$allowed_sorts = ['collection_name', 'collection_id'];

if (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sorts)) {
    $sort_column = $_GET['sort'];
    $sort_order = (isset($_GET['order']) && $_GET['order'] === 'ASC') ? 'ASC' : 'DESC';
}

$sql_collection_list = "SELECT * FROM collection WHERE 1=1 $search_filter ORDER BY $sort_column $sort_order";
$query_collection_list = mysqli_query($mysqli, $sql_collection_list);
?>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="main-pane-top d-flex space-between align-center" style="gap: 10px; flex-wrap: wrap;">
                    <h4 class="card-title" style="margin: 0;">Danh sách bộ sưu tập</h4>

                    <form method="GET" action="index.php" style="flex: 1; min-width: 200px; max-width: 300px;">
                        <input type="hidden" name="action" value="collection">
                        <input type="hidden" name="query" value="collection_list">

                        <div class="input__search p-relative">
                            <i class="icon-search p-absolute"></i>
                            <input type="text" id="collectionSearchInput" name="collection_search" class="form-control" placeholder="Tìm kiếm bộ sưu tập..." value="<?php echo isset($_GET['collection_search']) ? htmlspecialchars($_GET['collection_search']) : ''; ?>">
                        </div>
                    </form>

                    <a href="?action=collection&query=collection_add" class="btn btn-outline-dark btn-fw">Thêm bộ sưu tập</a>
                </div>


                <div class="table-responsive">
                    <table class="table table-hover table-action">
                        <thead>
                            <tr>
                                <th></th>
                                <th>
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th style="width: 50px; text-align: center;">STT</th>
                                <th></th>
                                <th style="cursor: pointer;"><a href="?action=collection&query=collection_list&sort=collection_name&order=<?php echo ($sort_column === 'collection_name' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>&collection_search=<?php echo isset($_GET['collection_search']) ? urlencode($_GET['collection_search']) : ''; ?>" style="color: inherit; text-decoration: none;">Tiêu đề <?php if ($sort_column === 'collection_name') echo ($sort_order === 'ASC') ? '↑' : '↓'; ?></a></th>
                                <th>Loại bộ sưu tập</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stt = 1;
                            while ($row = mysqli_fetch_array($query_collection_list)) {
                            ?>
                                <tr>
                                    <td>
                                        <a href="?action=collection&query=collection_edit&collection_id=<?php echo $row['collection_id'] ?>">
                                            <div class="icon-edit">
                                                <img class="w-100 h-100" src="images/icon-edit.png" alt="">
                                            </div>
                                        </a>
                                    </td>
                                    <td>
                                        <input type="checkbox" class="checkbox" onclick="testChecked(); getCheckedCheckboxes();" id="<?php echo $row['collection_id'] ?>">
                                    </td>
                                    <td style="text-align: center;"><?php echo $stt;
                                                                    $stt++; ?></td>
                                    <td><img src="modules/collection/uploads/<?php echo $row['collection_image'] ?>" alt=""></td>
                                    <td><?php echo $row['collection_name'] ?></td>
                                    <td><?php echo format_collection_type($row['collection_type']) ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
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
    var collectionSearchInput = document.getElementById('collectionSearchInput');
    var collectionSearchForm = collectionSearchInput ? collectionSearchInput.closest('form') : null;
    var collectionSearchTimeout;

    if (collectionSearchInput && collectionSearchForm) {
        collectionSearchInput.addEventListener('keyup', function() {
            clearTimeout(collectionSearchTimeout);
            collectionSearchTimeout = setTimeout(function() {
                collectionSearchForm.submit();
            }, 500);
        });
    }
</script>

<script>
    var btnDelete = document.getElementById("btnDelete");
    var checkAll = document.getElementById("checkAll");
    var checkboxes = document.getElementsByClassName("checkbox");
    var dialogControl = document.querySelector('.dialog__control');
    // Thêm sự kiện click cho checkbox checkAll
    checkAll.addEventListener("click", function() {
        // Nếu checkbox checkAll được chọn
        if (checkAll.checked) {
            // Đặt thuộc tính "checked" cho tất cả các checkbox còn lại
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = true;
            }
        } else {
            // Bỏ thuộc tính "checked" cho tất cả các checkbox còn lại
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = false;
            }
        }
        testChecked();
        getCheckedCheckboxes();
    });

    console.log(checkboxes[0]);

    function testChecked() {
        var count = 0;
        for (let i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                count++;
                console.log(count);
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
        btnDelete.href = "modules/collection/xuly.php?data=" + JSON.stringify(checkedIds);
    }
</script>
