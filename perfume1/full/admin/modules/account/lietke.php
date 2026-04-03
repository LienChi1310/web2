<?php
$account_keyword = isset($_POST['account_keyword']) ? trim($_POST['account_keyword']) : '';

if ($account_keyword !== '') {
    $keyword_safe = mysqli_real_escape_string($mysqli, $account_keyword);
    $sql_account_list = "
        SELECT * 
        FROM account
        WHERE account_email LIKE '%{$keyword_safe}%'
           OR account_name LIKE '%{$keyword_safe}%'
        ORDER BY account_type DESC, account_id DESC
    ";
} else {
    $sql_account_list = "
        SELECT * 
        FROM account
        ORDER BY account_type DESC, account_id DESC
    ";
}

$query_account_list = mysqli_query($mysqli, $sql_account_list);

function account_status_badge($status)
{
    if ((int)$status === 1) {
        return '<span class="badge bg-success text-white">Hoạt động</span>';
    }
    if ((int)$status === 0) {
        return '<span class="badge bg-danger text-white">Đã khóa</span>';
    }
    if ((int)$status === -1) {
        return '<span class="badge bg-warning text-dark">Tạm khóa</span>';
    }
    return '<span class="badge bg-secondary text-white">Không xác định</span>';
}
?>

<div class="row">
    <div class="col">
        <div class="header__list d-flex space-between align-center">
            <h3 class="card-title" style="margin: 0;">Danh sách tài khoản</h3>
            <div class="action_group">
                <?php if (isset($_SESSION['account_type']) && (int)$_SESSION['account_type'] === 2) { ?>
                    <a href="index.php?action=account&query=account_add" class="button button-dark">
                        + Thêm tài khoản
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="main-pane-top d-flex justify-center align-center">
                    <div class="input__search p-relative">
                        <form class="search-form" action="" method="POST">
                            <i class="icon-search p-absolute"></i>
                            <input
                                type="search"
                                class="form-control"
                                name="account_keyword"
                                placeholder="Tìm theo tên hoặc email"
                                title="Tìm theo tên hoặc email"
                                value="<?php echo htmlspecialchars($account_keyword); ?>">
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-action">
                        <thead>
                            <tr>
                                <th width="60"></th>
                                <th width="60">
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th style="width: 50px; text-align: center;">STT</th>
                                <th>Tên người dùng</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Loại tài khoản</th>
                                <th>Tình trạng</th>
                                <?php if (isset($_SESSION['account_type']) && (int)$_SESSION['account_type'] === 2) { ?>
                                    <th width="260">Thao tác nhanh</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $stt = 0;
                            if ($query_account_list && mysqli_num_rows($query_account_list) > 0) { ?>
                                <?php while ($row = mysqli_fetch_array($query_account_list)) {
                                    $stt++; ?>
                                    <tr>
                                        <td>
                                            <?php if (isset($_SESSION['account_type']) && (int)$_SESSION['account_type'] === 2) { ?>
                                                <a href="?action=account&query=account_edit&account_id=<?php echo $row['account_id']; ?>">
                                                    <div class="icon-edit">
                                                        <img class="w-100 h-100" src="images/icon-edit.png" alt="">
                                                    </div>
                                                </a>
                                            <?php } ?>
                                        </td>

                                        <td>
                                            <input type="checkbox" class="checkbox" id="<?php echo $row['account_id']; ?>">
                                        </td>

                                        <td style="text-align: center;"><?php echo $stt; ?></td>

                                        <td><?php echo htmlspecialchars($row['account_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['account_email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['account_phone'] ?? ''); ?></td>
                                        <td><?php echo format_account_type($row['account_type']); ?></td>
                                        <td><?php echo account_status_badge($row['account_status']); ?></td>

                                        <?php if (isset($_SESSION['account_type']) && (int)$_SESSION['account_type'] === 2) { ?>
                                            <td>
                                                <div class="d-flex flex-wrap" style="gap:8px;">
                                                    <a
                                                        href="index.php?action=account&query=account_edit&account_id=<?php echo $row['account_id']; ?>"
                                                        class="btn btn-outline-primary btn-sm">
                                                        Sửa
                                                    </a>

                                                    <a
                                                        href="modules/account/xuly.php?reset_password=1&account_id=<?php echo $row['account_id']; ?>"
                                                        class="btn btn-outline-warning btn-sm"
                                                        onclick="return confirm('Đặt lại mật khẩu tài khoản này về 123456?');">
                                                        Reset mật khẩu
                                                    </a>

                                                    <?php if ((int)$row['account_status'] === 1) { ?>
                                                        <a
                                                            href="modules/account/xuly.php?toggle_status=1&account_id=<?php echo $row['account_id']; ?>&status=0"
                                                            class="btn btn-outline-danger btn-sm"
                                                            onclick="return confirm('Bạn có chắc muốn khóa tài khoản này?');">
                                                            Khóa
                                                        </a>
                                                    <?php } else { ?>
                                                        <a
                                                            href="modules/account/xuly.php?toggle_status=1&account_id=<?php echo $row['account_id']; ?>&status=1"
                                                            class="btn btn-outline-success btn-sm"
                                                            onclick="return confirm('Bạn có chắc muốn mở khóa tài khoản này?');">
                                                            Mở khóa
                                                        </a>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="<?php echo (isset($_SESSION['account_type']) && (int)$_SESSION['account_type'] === 2) ? '8' : '7'; ?>" class="text-center">
                                        Không tìm thấy tài khoản nào.
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

<script>
    var checkAll = document.getElementById("checkAll");
    var checkboxes = document.getElementsByClassName("checkbox");

    if (checkAll) {
        checkAll.addEventListener("click", function() {
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = checkAll.checked;
            }
        });
    }

    function showSuccessToast() {
        toast({
            title: "Success",
            message: "Cập nhật thành công",
            type: "success",
            duration: 0,
        });
    }

    function showErrorToast() {
        toast({
            title: "Error",
            message: "Không thể cập nhật tài khoản",
            type: "error",
            duration: 0,
        });
    }
</script>

<?php
if (isset($_GET['message']) && $_GET['message'] == 'success') {
    echo '<script>';
    echo 'showSuccessToast();';
    echo 'window.history.pushState(null, "", "index.php?action=account&query=account_list");';
    echo '</script>';
} elseif (isset($_GET['message']) && $_GET['message'] == 'error') {
    echo '<script>';
    echo 'showErrorToast();';
    echo 'window.history.pushState(null, "", "index.php?action=account&query=account_list");';
    echo '</script>';
}
?>
