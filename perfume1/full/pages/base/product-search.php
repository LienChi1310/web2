<?php
$keyword = trim($_GET['keyword'] ?? '');
$category_id = (int)($_GET['category_id'] ?? 0);
$price_from = (float)($_GET['price_from'] ?? 0);
$price_to = (float)($_GET['price_to'] ?? 0);

$page_num = max(1, (int)($_GET['p'] ?? 1));
$limit = 8;
$offset = ($page_num - 1) * $limit;

$where = ["1=1"];

if ($keyword !== '') {
    $keyword_safe = mysqli_real_escape_string($mysqli, $keyword);
    $where[] = "product_name LIKE '%{$keyword_safe}%'";
}

if ($category_id > 0) {
    $where[] = "category_id = {$category_id}";
}

if ($price_from > 0) {
    $where[] = "product_price >= {$price_from}";
}

if ($price_to > 0) {
    $where[] = "product_price <= {$price_to}";
}

$where_sql = implode(' AND ', $where);

/* Đếm tổng số sản phẩm */
$sql_count = "SELECT COUNT(*) AS total FROM product WHERE {$where_sql}";
$query_count = mysqli_query($mysqli, $sql_count);
$row_count = mysqli_fetch_assoc($query_count);
$total_rows = (int)($row_count['total'] ?? 0);
$total_pages = max(1, (int)ceil($total_rows / $limit));

/* Lấy danh sách sản phẩm */
$sql_product_list = "SELECT * FROM product WHERE {$where_sql} ORDER BY product_id DESC LIMIT {$offset}, {$limit}";
$query_product_list = mysqli_query($mysqli, $sql_product_list);
?>

<div class="product-list">
    <div class="container pd-section">
        <div class="row">
            <div class="col">
                <div class="product__title text-center">
                    <h2 class="h2">Danh sách các sản phẩm</h2>
                    <p class="h9">
                        Kết quả tìm kiếm
                        <?php if ($keyword !== ''): ?>
                            có liên quan đến "<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>"
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <?php
            if ($query_product_list && mysqli_num_rows($query_product_list) > 0) {
                while ($row = mysqli_fetch_array($query_product_list)) {
            ?>
                    <div class="col" style="--w: 6; --w-md: 3">
                        <div class="product__card d-flex flex-column">
                            <div class="product__image p-relative">
                                <a href="index.php?page=product_detail&product_id=<?php echo $row['product_id']; ?>">
                                    <img class="w-100 h-100 object-fit-cover"
                                         src="admin/modules/product/uploads/<?php echo htmlspecialchars($row['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                                         alt="product image" />
                                </a>

                                <?php if ((float)$row['product_sale'] > 0): ?>
                                    <span class="product__sale h6 p-absolute">- <?php echo (float)$row['product_sale']; ?>%</span>
                                <?php endif; ?>
                            </div>

                            <div class="product__info">
                                <a href="index.php?page=product_detail&product_id=<?php echo $row['product_id']; ?>">
                                    <h3 class="product__name h5">
                                        <?php echo htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </h3>
                                </a>

                                <span class="review-star-list d-flex">
                                    <?php
                                    $query_evaluate_rating = mysqli_query(
                                        $mysqli,
                                        "SELECT * FROM evaluate WHERE product_id='" . (int)$row['product_id'] . "' AND evaluate_status = 1"
                                    );

                                    $rate1 = 0;
                                    $rate2 = 0;
                                    $rate3 = 0;
                                    $rate4 = 0;
                                    $rate5 = 0;

                                    while ($evaluate_rating = mysqli_fetch_array($query_evaluate_rating)) {
                                        $rate = (int)$evaluate_rating['evaluate_rate'];

                                        if ($rate === 1) {
                                            $rate1++;
                                        } elseif ($rate === 2) {
                                            $rate2++;
                                        } elseif ($rate === 3) {
                                            $rate3++;
                                        } elseif ($rate === 4) {
                                            $rate4++;
                                        } elseif ($rate === 5) {
                                            $rate5++;
                                        }
                                    }

                                    $total_rate = $rate1 + $rate2 + $rate3 + $rate4 + $rate5;
                                    if ($total_rate !== 0) {
                                        $rate_avg = ($rate1 * 1 + $rate2 * 2 + $rate3 * 3 + $rate4 * 4 + $rate5 * 5) / $total_rate;
                                        $rate_avg = round($rate_avg, 1);
                                    } else {
                                        $rate_avg = 0;
                                    }

                                    for ($i = 0; $i < 5; $i++) {
                                        if ($i < $rate_avg) {
                                    ?>
                                            <div class="rating-star"></div>
                                        <?php
                                        } else {
                                        ?>
                                            <div class="rating-star rating-off"></div>
                                    <?php
                                        }
                                    }
                                    ?>
                                </span>

                                <a href="index.php?page=product_detail&product_id=<?php echo $row['product_id']; ?>">
                                    <div class="product__price align-center">
                                        <del class="product__price--old h5">
                                            <?php echo number_format((float)$row['product_price']) . ' ₫'; ?>
                                        </del>
                                        <span class="product__price--new h4">
                                            <?php
                                            $new_price = (float)$row['product_price'] - ((float)$row['product_price'] / 100 * (float)$row['product_sale']);
                                            echo number_format($new_price) . ' vnđ';
                                            ?>
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
            <?php
                }
            } else {
            ?>
                <div class="col">
                    <div class="text-center pd-top">
                        <p class="h5">Không tìm thấy sản phẩm phù hợp.</p>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="row">
                <div class="col">
                    <div class="text-center pd-top">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a class="btn btn__outline"
                               style="margin: 0 4px; <?php echo ($i === $page_num) ? 'font-weight:700;' : ''; ?>"
                               href="index.php?page=search&keyword=<?php echo urlencode($keyword); ?>&category_id=<?php echo $category_id; ?>&price_from=<?php echo $price_from; ?>&price_to=<?php echo $price_to; ?>&p=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col">
                <div class="text-center pd-top">
                    <a class="btn btn__view--all btn__outline" href="index.php?page=products">Xem tất cả</a>
                </div>
            </div>
        </div>
    </div>
</div>