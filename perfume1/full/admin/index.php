<?php
session_start();

/* =======================
 * XỬ LÝ LOGOUT
 * ======================= */
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
  session_unset();
  session_destroy();

  header('Location: login.php');
  exit;
}

/* =======================
 * CHẶN TRUY CẬP NẾU CHƯA LOGIN
 * ======================= */
if (!isset($_SESSION['login']) || !isset($_SESSION['account_id_admin'])) {
  header('Location: login.php');
  exit;
}

/* =======================
 * CHẶN TOÀN CỤC CÁC CHỨC NĂNG KHÔNG DÙNG NỮA
 * ======================= */
if (isset($_GET['action'], $_GET['query'])) {
  $cur = $_GET['action'] . ':' . $_GET['query'];

  $ban = [
    'order:order_live',           // Đơn hàng tại quầy
    'order:order_payment',        // Lịch sử thanh toán (moved to Payment module)
    'product:product_inventory',  // Hàng tồn kho (cũ)
    'article:article_add',        // Bài viết
    'article:article_list',
    'article:article_edit',
    'dashboard:dashboard'         // Thống kê (cũ)
  ];

  // 🚨 QUAN TRỌNG: KHÔNG CHẶN INVENTORY NỮA

  if (in_array($cur, $ban, true)) {
    http_response_code(404);
    exit('404 Not Found');
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Admin</title>

  <link rel="shortcut icon" href="../assets/images/icon/favicon.ico" />
  <link rel="shortcut icon" href="images/favicon.png" />

  <!-- plugins:css -->
  <link rel="stylesheet" href="vendors/feather/feather.css">
  <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="vendors/typicons/typicons.css">
  <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">

  <!-- custom css -->
  <link rel="stylesheet" href="css/customize.css">
  <link rel="stylesheet" href="css/toast.css">
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">

  <!-- Plugin css -->
  <link rel="stylesheet" href="vendors/datatables.net-bs4/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="js/select.dataTables.min.css">

  <!-- main css -->
  <link rel="stylesheet" href="css/vertical-layout-light/style.css">

  <style>
    #toast,
    #toast * {
      opacity: 1 !important;
      filter: none !important;
      color: #000 !important;
    }
  </style>

  <!-- JS -->
  <script src="js/toast_message.js"></script>
  <script src="https://kit.fontawesome.com/a2e1cc550d.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
  <script src="js/validator.js"></script>
</head>

<body>

  <div id="toast"></div>

  <div class="container-scroller">
    <?php
    include('config/config.php');
    include('format/format.php');

    include('./modules/header.php');
    ?>

    <div class="container-fluid page-body-wrapper">
      <?php include('./modules/menu.php'); ?>
      <?php include('./modules/main.php'); ?>
    </div>
  </div>

  <!-- plugins -->
  <script src="vendors/js/vendor.bundle.base.js"></script>

  <!-- charts -->
  <script src="vendors/chart.js/Chart.min.js"></script>
  <script src="vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
  <script src="vendors/progressbar.js/progressbar.min.js"></script>

  <!-- core -->
  <script src="js/off-canvas.js"></script>
  <script src="js/hoverable-collapse.js"></script>
  <script src="js/template.js"></script>
  <script src="js/settings.js"></script>
  <script src="js/todolist.js"></script>

  <!-- icons -->
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons/ionicons.js"></script>

  <!-- dashboard -->
  <script src="js/dashboard.js"></script>
  <script src="js/Chart.roundedBarCharts.js"></script>

  <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>

</body>

</html>
