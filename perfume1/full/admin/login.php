<?php
session_start();
include('config/config.php');

if (isset($_SESSION['account_id_admin'])) {
    header('Location:index.php');
    exit;
}

if (isset($_POST['login'])) {
    $account_email    = isset($_POST['account_email']) ? trim($_POST['account_email']) : '';
    $account_password = isset($_POST['account_password']) ? trim($_POST['account_password']) : '';

    if ($account_email == '' || $account_password == '') {
        echo '<script>alert("Vui lòng nhập đầy đủ email và mật khẩu");</script>';
    } else {
        $account_email_escape    = mysqli_real_escape_string($mysqli, $account_email);
        $account_password_escape = mysqli_real_escape_string($mysqli, md5($account_password));

        $sql_account = "
            SELECT * FROM account
            WHERE account_email = '".$account_email_escape."'
              AND account_password = '".$account_password_escape."'
              AND (account_type = 1 OR account_type = 2)
              AND account_status = 1
            LIMIT 1
        ";

        $query_account = mysqli_query($mysqli, $sql_account);

        if ($query_account && mysqli_num_rows($query_account) > 0) {
            $row = mysqli_fetch_array($query_account);

            $_SESSION['login']            = $row['account_email'];
            $_SESSION['account_id_admin'] = $row['account_id'];
            $_SESSION['account_name']     = $row['account_name'];
            $_SESSION['account_type']     = $row['account_type'];

            header('Location:index.php');
            exit;
        } else {
            echo '<script>alert("Tài khoản admin không tồn tại, sai mật khẩu hoặc đã bị khóa");</script>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="shortcut icon" href="../assets/images/icon/favicon.ico"/>
</head>
<body>
    <section class="login">
        <div class="form-box">
            <div class="form-value">
                <form action="" autocomplete="off" method="POST">
                    <h2>Admin Login</h2>

                    <div class="inputbox">
                        <ion-icon name="mail-outline"></ion-icon>
                        <input type="email" name="account_email" required>
                        <label>Email</label>
                    </div>

                    <div class="inputbox">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input type="password" name="account_password" required>
                        <label>Password</label>
                    </div>

                    <div class="forget">
                        <label class="remember">
                            <input type="checkbox" disabled>
                            <span>Admin Area</span>
                        </label>
                    </div>

                    <button type="submit" name="login">Log in</button>
                </form>
            </div>
        </div>
    </section>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>