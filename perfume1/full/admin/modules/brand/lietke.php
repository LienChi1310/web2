<?php
$sql_brand_list = "SELECT * FROM brand ORDER BY brand_id ASC";
$query_brand_list = mysqli_query($mysqli, $sql_brand_list);

// Xử lý message từ URL
$toast_type = '';
$toast_text = '';

if (isset($_GET['message'])) {
    if ($_GET['message'] === 'success') {
        $toast_type = 'success';
        $toast_text = 'Cập nhật thương hiệu thành công!';
    } elseif ($_GET['message'] === 'error') {
        $toast_type = 'error';
        $toast_text = 'Có lỗi xảy ra, vui lòng thử lại!';
    }
}
?>

<style>
    /* Fallback toast nếu không có showToast() global */
    .fallback-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #111;
        color: #fff;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        opacity: 0;
        transform: translateY(-10px);
        pointer-events: none;
        transition: opacity 0.25s ease, transform 0.25s ease;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .fallback-toast.show {
        opacity: 1;
        transform: translateY(0);
    }

    .fallback-toast__icon {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }

    .fallback-toast__title {
        font-weight: 600;
        margin-bottom: 2px;
    }

    .fallback-toast__close {
        margin-left: 8px;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
    }
</style>

<div class="row">
    <div class="col">
        <div class="header__list d-flex space-between align-center">
            <h3 class="card-title" style="margin: 0;">Danh sách thương hiệu</h3>
            <div class="action_group">
                <a href="?action=brand&query=brand_add" class="button button-dark">Thêm thương hiệu</a>
            </div>
        </div>
    </div>
</div>

<div class="row" style="margin-top: 10px;">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card" style="border-radius: 20px; overflow: hidden;">
            <div class="card-body">
                <div class="main-pane-top d-flex justify-center align-center" style="margin-bottom: 20px;">
                    <div class="input__search p-relative">
                        <form class="search-form" action="#">
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
                                <th>Tên thương hiệu</th>
                                <th style="width: 100px; text-align: center;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            while ($row = mysqli_fetch_array($query_brand_list)) {
                                $i++;
                            ?>
                                <tr>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <input
                                            type="checkbox"
                                            class="checkbox"
                                            onclick="testChecked(); getCheckedCheckboxes();"
                                            id="<?php echo $row['brand_id'] ?>">
                                    </td>

                                    <td style="text-align: center; vertical-align: middle;">
                                        <?php echo $i; ?>
                                    </td>

                                    <td style="vertical-align: middle; font-weight: 500;">
                                        <?php echo htmlspecialchars($row['brand_name']); ?>
                                    </td>

                                    <td style="text-align: center; vertical-align: middle;">
                                        <a href="?action=brand&query=brand_edit&brand_id=<?php echo $row['brand_id'] ?>" title="Sửa">
                                            <div style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid #ddd; border-radius: 10px; background: #fff;">
                                                <img src="images/icon-edit.png" alt="edit" style="width: 18px; height: 18px;">
                                            </div>
                                        </a>
                                    </td>
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

<?php if ($toast_type !== '' && $toast_text !== ''): ?>
    <script>
        (function() {
            var type = "<?php echo $toast_type; ?>";
            var text = "<?php echo $toast_text; ?>";

            if (typeof showToast === 'function') {
                var title = (type === 'success') ? 'Success' : 'Error';
                try {
                    showToast(title, text, type);
                } catch (e) {
                    createFallbackToast(title + ': ' + text);
                }
            } else {
                var title = (type === 'success') ? 'Success' : 'Thông báo';
                createFallbackToast(title + ': ' + text);
            }

            function createFallbackToast(fullText) {
                var toast = document.createElement('div');
                toast.className = 'fallback-toast';
                toast.innerHTML = '' +
                    '<div class="fallback-toast__icon">' + (type === 'success' ? '✓' : '!') + '</div>' +
                    '<div>' +
                    '<div class="fallback-toast__title">' + (type === 'success' ? 'Success' : 'Thông báo') + '</div>' +
                    '<div>' + text + '</div>' +
                    '</div>' +
                    '<div class="fallback-toast__close">&times;</div>';

                document.body.appendChild(toast);

                setTimeout(function() {
                    toast.classList.add('show');
                }, 50);

                var hide = function() {
                    toast.classList.remove('show');
                    setTimeout(function() {
                        if (toast && toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                    }, 250);
                };

                setTimeout(hide, 3000);
                toast.querySelector('.fallback-toast__close').addEventListener('click', hide);
            }
        })();
    </script>
<?php endif; ?>

<script>
    var btnDelete = document.getElementById("btnDelete");
    var checkAll = document.getElementById("checkAll");
    var checkboxes = document.getElementsByClassName("checkbox");
    var dialogControl = document.querySelector('.dialog__control');

    checkAll.addEventListener("click", function() {
        if (checkAll.checked) {
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = true;
            }
        } else {
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = false;
            }
        }
        testChecked();
        getCheckedCheckboxes();
    });

    function testChecked() {
        var count = 0;
        for (let i = 0; i < checkboxes.length; i++) {
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
        btnDelete.href = "modules/brand/xuly.php?data=" + JSON.stringify(checkedIds);
    }
</script>
