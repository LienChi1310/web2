<div class="row" style="margin-bottom: 10px;">
    <div class="col d-flex" style="justify-content: space-between; align-items: flex-end;">
        <h3 class="card-title">Thêm tài khoản</h3>
        <a href="index.php?action=account&query=account_list" class="btn btn-outline-dark btn-fw">
            <i class="mdi mdi-reply"></i>
            Quay lại
        </a>
    </div>
</div>

<form method="POST" action="modules/account/xuly.php" id="form-account-add">
    <div class="row">
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="card-content">
                        <div class="input-item form-group">
                            <label for="account_name" class="d-block">Tên tài khoản</label>
                            <input type="text" id="account_name" name="account_name" class="form-control" required>
                            <span class="form-message"></span>
                        </div>

                        <div class="input-item form-group">
                            <label for="account_email" class="d-block">Email</label>
                            <input type="email" id="account_email" name="account_email" class="form-control" required>
                            <span class="form-message"></span>
                        </div>

                        <div class="input-item form-group">
                            <label for="account_phone" class="d-block">Số điện thoại</label>
                            <input type="text" id="account_phone" name="account_phone" class="form-control">
                            <span class="form-message"></span>
                        </div>

                        <div class="input-item form-group">
                            <label for="account_password" class="d-block">Mật khẩu</label>
                            <input type="text" id="account_password" name="account_password" class="form-control" value="123456" required>
                            <small class="text-muted">Mặc định có thể để 123456</small>
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
                        <div class="input-item form-group">
                            <label for="account_type" class="d-block">Quyền hạn</label>
                            <select name="account_type" id="account_type" class="form-control">
                                <option value="0">Khách hàng</option>
                                <option value="1">Nhân viên</option>
                                <option value="2">Quản trị viên</option>
                            </select>
                        </div>

                        <div class="input-item form-group">
                            <label for="account_status" class="d-block">Trạng thái</label>
                            <select name="account_status" id="account_status" class="form-control">
                                <option value="1">Hoạt động</option>
                                <option value="0">Khóa</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" name="account_add" class="btn btn-primary btn-icon-text">
        <i class="ti-file btn-icon-prepend"></i>
        Thêm tài khoản
    </button>
</form>

<script>
    Validator({
        form: '#form-account-add',
        errorSelector: '.form-message',
        rules: [
            Validator.isRequired('#account_name', 'Vui lòng nhập tên tài khoản'),
            Validator.isRequired('#account_email', 'Vui lòng nhập email'),
            Validator.isRequired('#account_password', 'Vui lòng nhập mật khẩu')
        ],
        onSubmit: function(data) {
            console.log(data);
        }
    });
</script>