<?php 
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
    } else {
        $action = "-1";
    }

    if (isset($_GET['query'])) {
        $query = $_GET['query'];
    } else {
        $query = "-1";
    }
?>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item <?php if ($action === 'home') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=home&query=">
                <i class="menu-icon mdi mdi-home"></i>
                <span class="menu-title">Trang chủ</span>
            </a>
        </li>

        <li class="nav-item <?php if ($action === 'order') { echo "active"; } ?>">
            <a class="nav-link" data-bs-toggle="collapse" href="#orders" aria-expanded="<?php if ($action === 'order') { echo "true"; } else { echo "false"; } ?>" aria-controls="orders">
                <i class="menu-icon mdi mdi-table"></i>
                <span class="menu-title">Đơn hàng</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse <?php if ($action === 'order') { echo "show"; } ?>" id="orders">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item <?php if ($query === 'order_list') { echo "active"; } ?>">
                        <a class="nav-link" href="index.php?action=order&query=order_list">Đơn hàng trực tuyến</a>
                    </li>
                    <li class="nav-item <?php if ($query === 'order_payment') { echo "active"; } ?>">
                        <a class="nav-link" href="index.php?action=order&query=order_payment">Lịch sử thanh toán</a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item <?php if ($action === 'product' || $action === 'category' || $action === 'collection' || $action === 'brand') { echo "active"; } ?>">
            <a class="nav-link" data-bs-toggle="collapse" href="#products" aria-expanded="<?php if ($action === 'product' || $action === 'category' || $action === 'collection' || $action === 'brand') { echo "true"; } else { echo "false"; } ?>" aria-controls="products">
                <i class="menu-icon mdi mdi-flask"></i>
                <span class="menu-title">Sản phẩm</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse <?php if ($action === 'product' || $action === 'category' || $action === 'collection' || $action === 'brand') { echo "show"; } ?>" id="products">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item <?php if ($query === 'product_list') { echo "active"; } ?>">
                        <a class="nav-link" href="index.php?action=product&query=product_list">Danh sách sản phẩm</a>
                    </li>
                    <li class="nav-item <?php if ($query === 'category_list') { echo "active"; } ?>">
                        <a class="nav-link" href="index.php?action=category&query=category_list">Danh mục sản phẩm</a>
                    </li>
                    <li class="nav-item <?php if ($query === 'brand_list') { echo "active"; } ?>">
                        <a class="nav-link" href="index.php?action=brand&query=brand_list">Danh sách thương hiệu</a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item <?php if ($action === 'inventory') { echo "active"; } ?>">
            <a class="nav-link" data-bs-toggle="collapse" href="#inventory" aria-expanded="<?php if ($action === 'inventory') { echo "true"; } else { echo "false"; } ?>" aria-controls="inventory">
                <i class="menu-icon mdi mdi-clipboard-list"></i>
                <span class="menu-title">Phiếu nhập kho</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse <?php if ($action === 'inventory') { echo "show"; } ?>" id="inventory">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item <?php if ($query === 'inventory_list') { echo "active"; } ?>">
                        <a class="nav-link" href="index.php?action=inventory&query=inventory_list">Danh sách phiếu nhập</a>
                    </li>
                    <li class="nav-item <?php if ($query === 'inventory_add') { echo "active"; } ?>">
                        <a class="nav-link" href="index.php?action=inventory&query=inventory_add">Thêm phiếu nhập</a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item <?php if ($action === 'report') { echo "active"; } ?>">
            <a class="nav-link" data-bs-toggle="collapse" href="#report" aria-expanded="<?php if ($action === 'report') { echo "true"; } else { echo "false"; } ?>" aria-controls="report">
                <i class="menu-icon mdi mdi-chart-bar"></i>
                <span class="menu-title">Báo cáo</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse <?php if ($action === 'report') { echo "show"; } ?>" id="report">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item <?php if ($query === 'report_main') { echo "active"; } ?>">
                        <a class="nav-link" href="index.php?action=report&query=report_main">Tồn kho và thống kê</a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item <?php if ($action === 'customer') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=customer&query=customer_list">
                <i class="mdi mdi-account-box-outline menu-icon"></i>
                <span class="menu-title">Khách hàng</span>
            </a>
        </li>

        <li class="nav-item <?php if ($action === 'account') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=account&query=account_list">
                <i class="mdi mdi-account-multiple-outline menu-icon"></i>
                <span class="menu-title">Tài khoản</span>
            </a>
        </li>

        <li class="nav-item <?php if ($action === 'settings') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=settings&query=settings">
                <i class="menu-icon mdi mdi-settings-box"></i>
                <span class="menu-title">Cài đặt</span>
            </a>
        </li>
    </ul>
</nav>

<?php echo "<!-- MENU LOADED FROM: " . __FILE__ . " -->"; ?>