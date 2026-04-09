<?php
$keyword = trim((string)($_GET['keyword'] ?? ''));
$keyword = preg_replace('/\s+/u', ' ', $keyword);

$DEFAULT_MIN = 0;
$DEFAULT_MAX = 15000000;

$category_id = (int)($_GET['category_id'] ?? 0);
$brand_id = (int)($_GET['brand_id'] ?? 0);

$raw_price_from = $_GET['pricefrom'] ?? $_GET['price_from'] ?? $DEFAULT_MIN;
$raw_price_to = $_GET['priceto'] ?? $_GET['price_to'] ?? $DEFAULT_MAX;

$price_from = (int)$raw_price_from;
$price_to = (int)$raw_price_to;
$price_from = max($DEFAULT_MIN, min($DEFAULT_MAX, $price_from));
$price_to = max($DEFAULT_MIN, min($DEFAULT_MAX, $price_to));
if ($price_from > $price_to) {
    $tmp = $price_from;
    $price_from = $price_to;
    $price_to = $tmp;
}

$priceSort = strtolower((string)($_GET['pricesort'] ?? ''));
$priceSort = in_array($priceSort, ['asc', 'desc'], true) ? $priceSort : '';

$page_num = max(1, (int)($_GET['pagenumber'] ?? $_GET['p'] ?? 1));
$limit = 9;
$offset = ($page_num - 1) * $limit;

$EFFECTIVE_PRICE = '(p.product_price * (100 - COALESCE(p.product_sale,0)) / 100.0)';

$where = ['p.product_status = 1'];

if ($keyword !== '') {
    $keyword_lower = mb_strtolower($keyword, 'UTF-8');

    $parts = preg_split('/[\s\/,;|+\-]+/u', $keyword_lower);
    $parts = is_array($parts) ? array_values(array_filter(array_map('trim', $parts), static function ($v) {
        return $v !== '';
    })) : [];

    $has_male = in_array('nam', $parts, true);
    $has_female = in_array('nu', $parts, true) || in_array('nữ', $parts, true);

    if ($has_male xor $has_female) {
        $where[] = $has_male ? 'p.product_category = 2' : 'p.product_category = 3';
    }

    $keyword_parts = preg_split('/[\s\/,;|+\-]+/u', $keyword);
    foreach ($keyword_parts as $part) {
        $part = trim((string)$part);
        if ($part === '') {
            continue;
        }

        $part_lower = mb_strtolower($part, 'UTF-8');
        if (in_array($part_lower, ['nam', 'nu', 'nữ'], true)) {
            continue;
        }

        $part_safe = mysqli_real_escape_string($mysqli, $part);
        $where[] = "(p.product_name LIKE '%{$part_safe}%' OR c.category_name LIKE '%{$part_safe}%' OR b.brand_name LIKE '%{$part_safe}%')";
    }
}

if ($category_id > 0) {
    $where[] = "p.product_category = {$category_id}";
}
if ($brand_id > 0) {
    $where[] = "p.product_brand = {$brand_id}";
}

$where[] = "{$EFFECTIVE_PRICE} BETWEEN {$price_from} AND {$price_to}";
$where_sql = implode(' AND ', $where);

$order_sql = $priceSort !== ''
    ? "{$EFFECTIVE_PRICE} " . strtoupper($priceSort)
    : 'p.product_id DESC';

$sql_count = "
    SELECT COUNT(*) AS total
    FROM product p
    LEFT JOIN category c ON p.product_category = c.category_id
    LEFT JOIN brand b ON p.product_brand = b.brand_id
    WHERE {$where_sql}
";
$query_count = mysqli_query($mysqli, $sql_count);
$row_count = mysqli_fetch_assoc($query_count);
$total_rows = (int)($row_count['total'] ?? 0);
$total_pages = max(1, (int)ceil($total_rows / $limit));
if ($page_num > $total_pages) {
    $page_num = $total_pages;
    $offset = ($page_num - 1) * $limit;
}

$sql_product_list = "
    SELECT p.*
    FROM product p
    LEFT JOIN category c ON p.product_category = c.category_id
    LEFT JOIN brand b ON p.product_brand = b.brand_id
    WHERE {$where_sql}
    ORDER BY {$order_sql}
    LIMIT {$offset}, {$limit}
";
$query_product_list = mysqli_query($mysqli, $sql_product_list);

$category_label = '';
if ($category_id > 0) {
    $sql_category_label = "SELECT category_name FROM category WHERE category_id = {$category_id} LIMIT 1";
    $q_category_label = mysqli_query($mysqli, $sql_category_label);
    if ($q_category_label && ($row_category = mysqli_fetch_assoc($q_category_label))) {
        $category_label = (string)$row_category['category_name'];
    }
}

$brand_label = '';
if ($brand_id > 0) {
    $sql_brand_label = "SELECT brand_name FROM brand WHERE brand_id = {$brand_id} LIMIT 1";
    $q_brand_label = mysqli_query($mysqli, $sql_brand_label);
    if ($q_brand_label && ($row_brand = mysqli_fetch_assoc($q_brand_label))) {
        $brand_label = (string)$row_brand['brand_name'];
    }
}

$show_price_tag = !($price_from === $DEFAULT_MIN && $price_to === $DEFAULT_MAX);

$base_params = ['page' => 'search'];
if ($keyword !== '') {
    $base_params['keyword'] = $keyword;
}
if ($category_id > 0) {
    $base_params['category_id'] = $category_id;
}
if ($brand_id > 0) {
    $base_params['brand_id'] = $brand_id;
}
if ($show_price_tag) {
    $base_params['pricefrom'] = $price_from;
    $base_params['priceto'] = $price_to;
}
if ($priceSort !== '') {
    $base_params['pricesort'] = $priceSort;
}

$build_search_url = static function (array $params) {
    return 'index.php?' . http_build_query($params);
};
?>

<div class="product-list">
    <div class="container pd-section">
        <div class="row">
            <div class="col">
                <div class="product__title text-center">
                    <h2 class="h2">Danh sách các sản phẩm</h2>
                    <p class="h8">
                        <?php if ($keyword !== ''): ?>
                            Tìm thấy <?php echo number_format($total_rows); ?> sản phẩm liên quan đến từ khóa "<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>".
                        <?php else: ?>
                            Tìm thấy <?php echo number_format($total_rows); ?> sản phẩm.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="product__filter-sort">
            <div class="row">
                <div class="col" style="--w-md:6;">
                    <div class="product__filter d-flex">
                        <div class="sort__item h5">Hiện có: <?php echo $total_rows; ?> sản phẩm</div>
                    </div>
                </div>
                <div class="col" style="--w-md:6;">
                    <div class="product__sort d-flex">
                        <div class="sort__item h5">Sắp xếp theo:</div>
                        <div class="sort__item h5">
                            <details class="sort__select p-relative">
                                <summary class="cursor-pointer d-flex align-center">
                                    Giá
                                    <img src="./assets/images/icon/icon-chevron-down.svg" alt="down" class="icon-open d-block" style="margin-left: 8px;">
                                    <img src="./assets/images/icon/chevron-up.svg" alt="up" class="icon-close d-none" style="margin-left: 8px;">
                                </summary>
                                <div class="sort__selectbox p-absolute selectbox__right">
                                    <div class="selectbox__item">
                                        <?php
                                        $params_asc = $base_params;
                                        $params_asc['pricesort'] = 'asc';
                                        $params_asc['pagenumber'] = 1;
                                        ?>
                                        <a href="<?php echo $build_search_url($params_asc); ?>">Giá từ thấp đến cao</a>
                                    </div>
                                    <div class="selectbox__item">
                                        <?php
                                        $params_desc = $base_params;
                                        $params_desc['pricesort'] = 'desc';
                                        $params_desc['pagenumber'] = 1;
                                        ?>
                                        <a href="<?php echo $build_search_url($params_desc); ?>">Giá từ cao đến thấp</a>
                                    </div>
                                </div>
                            </details>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col" style="--w-md:3;">
                <div class="product__sidebar">
                    <div class="sidebar__item w-100">
                        <div class="sidebar__item--heading">
                            <h3 class="h3">Danh mục</h3>
                        </div>
                        <div class="sidebar__item--content">
                            <?php
                            $qcat = mysqli_query($mysqli, 'SELECT category_id, category_name FROM category ORDER BY category_id DESC');
                            while ($qcat && ($cat = mysqli_fetch_assoc($qcat))) {
                                $cat_id = (int)$cat['category_id'];
                                $active = ($category_id === $cat_id) ? 'category__active' : '';

                                $link_params = $base_params;
                                $link_params['category_id'] = $cat_id;
                                $link_params['pagenumber'] = 1;
                                $href = $build_search_url($link_params);
                                ?>
                                <a href="<?php echo $href; ?>" class="sidebar__item--label d-block <?php echo $active; ?>">
                                    <?php echo htmlspecialchars((string)$cat['category_name']); ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="sidebar__item w-100">
                        <div class="sidebar__item--heading">
                            <h3 class="h3">Lọc theo giá</h3>
                        </div>
                        <div class="sidebar__item--content product-detail__variant--items d-flex">
                            <div class="price__range">
                                <div class="slider">
                                    <div class="progress"></div>
                                </div>
                                <div class="range-input">
                                    <input type="range" class="range-min" id="minPrice" min="<?php echo $DEFAULT_MIN; ?>" max="<?php echo $DEFAULT_MAX; ?>" value="<?php echo $price_from; ?>" step="1000">
                                    <input type="range" class="range-max" id="maxPrice" min="<?php echo $DEFAULT_MIN; ?>" max="<?php echo $DEFAULT_MAX; ?>" value="<?php echo $price_to; ?>" step="1000">
                                </div>
                                <div class="price-input d-flex space-between">
                                    <div class="field">
                                        <input type="number" id="price-from" class="input-min h4" value="<?php echo $price_from; ?>" min="<?php echo $DEFAULT_MIN; ?>" max="<?php echo $DEFAULT_MAX; ?>" step="1000">
                                        <span class="h6 min-value">đ</span>
                                    </div>
                                    <div class="separator">&mdash;</div>
                                    <div class="field">
                                        <input type="number" id="price-to" class="input-max h4" value="<?php echo $price_to; ?>" min="<?php echo $DEFAULT_MIN; ?>" max="<?php echo $DEFAULT_MAX; ?>" step="1000">
                                        <span class="h6 max-value">đ</span>
                                    </div>
                                </div>
                                <a href="#" class="btn btn__solid btn__filter text-right" onclick="setUrlPrice(); return false;">Lọc</a>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar__item w-100">
                        <div class="sidebar__item--heading">
                            <h3 class="h3">Thương hiệu</h3>
                        </div>
                        <div class="sidebar__item--content">
                            <div class="product-detail__variant--items d-flex">
                                <?php
                                $qbrand = mysqli_query($mysqli, 'SELECT brand_id, brand_name FROM brand ORDER BY brand_id DESC');
                                while ($qbrand && ($brand = mysqli_fetch_assoc($qbrand))) {
                                    $brand_item_id = (int)$brand['brand_id'];
                                    $active = ($brand_id === $brand_item_id) ? 'variant__active' : '';

                                    $link_params = $base_params;
                                    $link_params['brand_id'] = $brand_item_id;
                                    $link_params['pagenumber'] = 1;
                                    $href = $build_search_url($link_params);
                                    ?>
                                    <a href="<?php echo $href; ?>" class="custom-label product-detail__variant--item <?php echo $active; ?>">
                                        <?php echo htmlspecialchars((string)$brand['brand_name']); ?>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col" style="--w-md:9;">
                <div class="row">
                    <div class="col">
                        <div class="product__tag d-flex">
                            <?php if ($show_price_tag) {
                                $clear_price_params = $base_params;
                                unset($clear_price_params['pricefrom'], $clear_price_params['priceto']);
                                ?>
                                <a class="tag__item" href="<?php echo $build_search_url($clear_price_params); ?>">
                                    <div class="d-flex align-center">
                                        <div class="btn__tag d-flex align-center"><img class="icon-close" src="./assets/images/icon/icon-close.png" alt=""></div>
                                        <div class="tag__content d-flex align-center">
                                            <span class="tag__name h5">Giá <?php echo number_format($price_from); ?>đ - <?php echo number_format($price_to); ?>đ</span>
                                        </div>
                                    </div>
                                </a>
                            <?php } ?>

                            <?php if ($category_id > 0) {
                                $clear_category_params = $base_params;
                                unset($clear_category_params['category_id']);
                                ?>
                                <a class="tag__item" href="<?php echo $build_search_url($clear_category_params); ?>">
                                    <div class="d-flex align-center">
                                        <div class="btn__tag d-flex align-center"><img class="icon-close" src="./assets/images/icon/icon-close.png" alt=""></div>
                                        <div class="tag__content d-flex align-center">
                                            <span class="tag__name h5"><?php echo htmlspecialchars($category_label !== '' ? $category_label : 'Danh mục'); ?></span>
                                        </div>
                                    </div>
                                </a>
                            <?php } ?>

                            <?php if ($brand_id > 0) {
                                $clear_brand_params = $base_params;
                                unset($clear_brand_params['brand_id']);
                                ?>
                                <a class="tag__item" href="<?php echo $build_search_url($clear_brand_params); ?>">
                                    <div class="d-flex align-center">
                                        <div class="btn__tag d-flex align-center"><img class="icon-close" src="./assets/images/icon/icon-close.png" alt=""></div>
                                        <div class="tag__content d-flex align-center">
                                            <span class="tag__name h5"><?php echo htmlspecialchars($brand_label !== '' ? $brand_label : 'Thương hiệu'); ?></span>
                                        </div>
                                    </div>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?php
                    if ($query_product_list && mysqli_num_rows($query_product_list) > 0) {
                        while ($row = mysqli_fetch_assoc($query_product_list)) {
                            ?>
                            <div class="col" style="--w: 9; --w-md: 4">
                                <div class="product__card d-flex flex-column">
                                    <div class="product__image p-relative">
                                        <a href="index.php?page=product_detail&product_id=<?php echo (int)$row['product_id']; ?>">
                                            <img class="w-100 h-100 object-fit-cover"
                                                src="admin/modules/product/uploads/<?php echo htmlspecialchars((string)$row['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                                                alt="<?php echo htmlspecialchars((string)$row['product_name'], ENT_QUOTES, 'UTF-8'); ?>" />
                                        </a>

                                        <?php if ((int)$row['product_sale'] > 0) { ?>
                                            <span class="product__sale h6 p-absolute"> - <?php echo (int)$row['product_sale']; ?>%</span>
                                        <?php } ?>
                                    </div>

                                    <div class="product__info">
                                        <a href="index.php?page=product_detail&product_id=<?php echo (int)$row['product_id']; ?>">
                                            <h3 class="product__name h5"><?php echo htmlspecialchars((string)$row['product_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                        </a>

                                        <span class="review-star-list d-flex">
                                            <?php
                                            $query_evaluate_rating = mysqli_query(
                                                $mysqli,
                                                'SELECT evaluate_rate FROM evaluate WHERE product_id=' . (int)$row['product_id'] . ' AND evaluate_status = 1'
                                            );

                                            $sum_rate = 0;
                                            $total_rate = 0;
                                            while ($query_evaluate_rating && ($evaluate_rating = mysqli_fetch_assoc($query_evaluate_rating))) {
                                                $rate = (int)$evaluate_rating['evaluate_rate'];
                                                if ($rate >= 1 && $rate <= 5) {
                                                    $sum_rate += $rate;
                                                    $total_rate++;
                                                }
                                            }

                                            $rate_avg = $total_rate > 0 ? round($sum_rate / $total_rate, 1) : 0;
                                            for ($i = 0; $i < 5; $i++) {
                                                if ($i < $rate_avg) {
                                                    echo '<div class="rating-star"></div>';
                                                } else {
                                                    echo '<div class="rating-star rating-off"></div>';
                                                }
                                            }
                                            ?>
                                        </span>

                                        <a href="index.php?page=product_detail&product_id=<?php echo (int)$row['product_id']; ?>">
                                            <div class="product__price align-center">
                                                <?php
                                                $old_price = (int)$row['product_price'];
                                                $sale_percent = (int)$row['product_sale'];
                                                $new_price = $sale_percent > 0 ? ($old_price - ($old_price * $sale_percent / 100)) : $old_price;
                                                if ($sale_percent > 0) {
                                                    echo '<del class="product__price--old h5">' . number_format($old_price) . ' ₫</del>';
                                                }
                                                ?>
                                                <span class="product__price--new h4"><?php echo number_format($new_price); ?> ₫</span>
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
                    <?php } ?>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="pagination">
                            <?php if ($total_pages > 1) {
                                $page_params = $base_params;
                                ?>
                                <ul class="pagination__items d-flex align-center justify-center">
                                    <?php
                                    if ($page_num > 1) {
                                        $prev_params = $page_params;
                                        $prev_params['pagenumber'] = $page_num - 1;
                                        ?>
                                        <li class="pagination__item"><a class="d-flex align-center" href="<?php echo $build_search_url($prev_params); ?>"><img src="./assets/images/icon/arrow-left.svg" alt=""></a></li>
                                    <?php } ?>

                                    <?php for ($i = 1; $i <= $total_pages; $i++) {
                                        $num_params = $page_params;
                                        $num_params['pagenumber'] = $i;
                                        ?>
                                        <li class="pagination__item">
                                            <a class="pagination__anchor <?php echo ($page_num === $i ? 'active' : ''); ?>" href="<?php echo $build_search_url($num_params); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php } ?>

                                    <?php
                                    if ($page_num < $total_pages) {
                                        $next_params = $page_params;
                                        $next_params['pagenumber'] = $page_num + 1;
                                        ?>
                                        <li class="pagination__item"><a class="d-flex align-center" href="<?php echo $build_search_url($next_params); ?>"><img src="./assets/images/icon/icon-nextlink.svg" alt=""></a></li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="text-center pd-top">
                            <a class="btn btn__view--all btn__outline" href="index.php?page=products">Xem tất cả</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const DEF_MIN = <?php echo $DEFAULT_MIN; ?>;
    const DEF_MAX = <?php echo $DEFAULT_MAX; ?>;
    const minR = document.getElementById('minPrice');
    const maxR = document.getElementById('maxPrice');
    const minI = document.getElementById('price-from');
    const maxI = document.getElementById('price-to');
    const progress = document.querySelector('.slider .progress');

    function clamp(v) {
        v = parseInt(v || 0, 10);
        if (isNaN(v)) v = DEF_MIN;
        return Math.max(DEF_MIN, Math.min(DEF_MAX, v));
    }

    function renderProgress() {
        const left = ((clamp(minR.value) - DEF_MIN) / (DEF_MAX - DEF_MIN)) * 100;
        const right = 100 - ((clamp(maxR.value) - DEF_MIN) / (DEF_MAX - DEF_MIN)) * 100;
        if (progress) {
            progress.style.left = left + '%';
            progress.style.right = right + '%';
        }
    }

    function syncFromRange() {
        let a = clamp(minR.value);
        let b = clamp(maxR.value);
        if (a > b) {
            const t = a;
            a = b;
            b = t;
        }
        minR.value = a;
        maxR.value = b;
        minI.value = a;
        maxI.value = b;
        renderProgress();
    }

    function syncFromInput() {
        let a = clamp(minI.value);
        let b = clamp(maxI.value);
        if (a > b) {
            const t = a;
            a = b;
            b = t;
        }
        minR.value = a;
        maxR.value = b;
        minI.value = a;
        maxI.value = b;
        renderProgress();
    }

    [minR, maxR].forEach(function (el) {
        el.addEventListener('input', syncFromRange);
    });
    [minI, maxI].forEach(function (el) {
        el.addEventListener('input', syncFromInput);
    });
    syncFromRange();

    window.setUrlPrice = function () {
        const p = new URLSearchParams(window.location.search);
        p.set('page', 'search');

        const keyword = <?php echo json_encode($keyword, JSON_UNESCAPED_UNICODE); ?>;
        if (keyword) p.set('keyword', keyword);
        else p.delete('keyword');

        <?php if ($category_id > 0) { ?>
        p.set('category_id', '<?php echo $category_id; ?>');
        <?php } else { ?>
        p.delete('category_id');
        <?php } ?>

        <?php if ($brand_id > 0) { ?>
        p.set('brand_id', '<?php echo $brand_id; ?>');
        <?php } else { ?>
        p.delete('brand_id');
        <?php } ?>

        <?php if ($priceSort !== '') { ?>
        p.set('pricesort', '<?php echo $priceSort; ?>');
        <?php } else { ?>
        p.delete('pricesort');
        <?php } ?>

        p.set('pagenumber', '1');

        const a = clamp(minI.value);
        const b = clamp(maxI.value);
        if (a === DEF_MIN && b === DEF_MAX) {
            p.delete('pricefrom');
            p.delete('priceto');
        } else {
            p.set('pricefrom', String(a));
            p.set('priceto', String(b));
        }

        window.location.href = 'index.php?' + p.toString();
    };
})();
</script>