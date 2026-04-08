<div class="main-panel">
    <div class="content-wrapper">
        <?php
        if (isset($_GET['action']) && isset($_GET['query'])) {
            $action = $_GET['action'];
            $query  = $_GET['query'];
        } else {
            $action = '';
            $query  = '';
        }

        // CHẶN NHANH các chức năng cần xoá
        $ban_actions = [
            'order:order_live',           // Đơn hàng tại quầy
            'product:product_inventory',  // Hàng tồn kho
            'article:article_add',
            'article:article_list',
            'article:article_edit',       // Bài viết
            'dashboard:dashboard'         // Thống kê
        ];

        $cur = $action . ':' . $query;
        if (in_array($cur, $ban_actions, true)) {
            http_response_code(404);
            exit('404 Not Found');
        }

        // ======= CÁC TRANG CÒN GIỮ =======
        if ($action == 'order' && $query == 'order_list') {
            include("./modules/order/lietke.php");
        }
        // HIDDEN (2026-04-08): Payment history moved to separate Payment module
        // elseif ($action == 'order' && $query == 'order_payment') {
        //     include("./modules/order/lichsuthanhtoan.php");
        // }
        elseif ($action == 'order' && $query == 'order_add') {
            include("./modules/order/them.php");
        } elseif ($action == 'order' && $query == 'order_search') {
            include("./modules/order/timkiem.php");
        } elseif ($action == 'order' && $query == 'order_detail') {
            include("./modules/order/chitiet.php");
        } elseif ($action == 'order' && $query == 'order_detail_online') {
            include("./modules/order/chitiet_online.php");
        } elseif ($action == 'category' && $query == 'category_add') {
            include("./modules/category/them.php");
        } elseif ($action == 'category' && $query == 'category_list') {
            include("./modules/category/lietke.php");
        } elseif ($action == 'category' && $query == 'category_edit') {
            include("./modules/category/sua.php");
        } elseif ($action == 'collection' && $query == 'collection_add') {
            include("./modules/collection/them.php");
        } elseif ($action == 'collection' && $query == 'collection_list') {
            include("./modules/collection/lietke.php");
        } elseif ($action == 'collection' && $query == 'collection_edit') {
            include("./modules/collection/sua.php");
        } elseif ($action == 'product' && $query == 'product_add') {
            include("./modules/product/them.php");
        } elseif ($action == 'product' && $query == 'product_list') {
            include("./modules/product/lietke.php");
        } elseif ($action == 'product' && $query == 'product_edit') {
            include("./modules/product/sua.php");
        } elseif ($action == 'product' && $query == 'product_search') {
            include("./modules/product/timkiem.php");
        } elseif ($action == 'account' && $query == 'my_account') {
            include("./modules/account/my_account.php");
        } elseif ($action == 'account' && $query == 'password_change') {
            include("./modules/account/password_change.php");
        } elseif ($action == 'account' && $query == 'account_list') {
            include("./modules/account/lietke.php");
        } elseif ($action == 'account' && $query == 'account_edit') {
            include("./modules/account/sua.php");
        } elseif ($action == 'account' && $query == 'account_add') {
            include("./modules/account/them.php");
        } elseif ($action == 'brand' && $query == 'brand_list') {
            include("./modules/brand/lietke.php");
        } elseif ($action == 'brand' && $query == 'brand_add') {
            include("./modules/brand/them.php");
        } elseif ($action == 'brand' && $query == 'brand_edit') {
            include("./modules/brand/sua.php");
        } elseif ($action == 'customer' && $query == 'customer_list') {
            include("./modules/customer/lietke.php");
        } elseif ($action == 'settings' && $query == 'settings') {
            include("./modules/settings/main.php");
        }

        // ======= MỞ LẠI ROUTE PHIẾU NHẬP KHO =======
        elseif ($action == 'inventory' && $query == 'inventory_list') {
            include("./modules/inventory/lietke.php");
        } elseif ($action == 'inventory' && $query == 'inventory_add') {
            include("./modules/inventory/them.php");
        } elseif ($action == 'inventory' && $query == 'inventory_detail') {
            include("./modules/inventory/chitiet.php");
        } elseif ($action == 'inventory' && $query == 'inventory_edit') {
            include("./modules/inventory/sua.php");
        }

        // ======= ĐÃ GỠ HOÀN TOÀN CÁC ROUTE SAU =======
        // order&order_live        (Đơn hàng tại quầy)
        // article&{add|list|edit} (Bài viết)
        // dashboard&dashboard     (Thống kê)

        // ================= REPORT =================
        elseif ($action == 'report' && $query == 'report_main') {
            include("./modules/report/main.php");
        } else {
            // Mặc định: trang home
            include("./modules/home.php");
        }
        ?>
    </div>
</div>
