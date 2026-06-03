<?php
require_once __DIR__ . '/includes/functions.php';
$config = app_config();

$brandRaw = trim((string)($_GET['brand'] ?? ''));
$brandFilter = ($brandRaw !== '' && $brandRaw !== 'all') ? get_brand_slug($brandRaw) : '';
$sort = trim((string)($_GET['sort'] ?? ''));
$products = get_visible_products();

if ($brandFilter !== '') {
    $products = array_values(array_filter($products, function ($product) use ($brandFilter) {
        return get_brand_slug($product['brand'] ?? '') === $brandFilter;
    }));
}

usort($products, function ($a, $b) use ($sort) {
    if ($sort === 'price_asc' || $sort === 'price_desc') {
        $pa = (float)preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string)($a['price'] ?? 0)));
        $pb = (float)preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string)($b['price'] ?? 0)));
        return $sort === 'price_asc' ? $pa <=> $pb : $pb <=> $pa;
    }
    if ($sort === 'name') {
        return strcmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
    }
    return 0;
});

$pageTitle = page_title('Каталог кондиціонерів');
$pageDescription = 'Каталог кондиціонерів TCL, Hisense та SkyLux з фільтром по бренду та сортуванням.';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero page-hero--catalog">
    <div class="container">
        <span class="eyebrow">Каталог</span>
        <h1>Каталог кондиціонерів</h1>
    </div>
</section>

<section class="section catalog-section">
    <div class="container">
        <form class="catalog-toolbar" method="get" action="/catalog">
            <label>
                <span>Бренд</span>
                <select name="brand">
                    <option value="" <?= $brandFilter === '' ? 'selected' : '' ?>>Всі бренди</option>
                    <?php foreach ($config['brands'] as $slug => $brand): ?>
                        <option value="<?= e($slug) ?>" <?= $brandFilter === $slug ? 'selected' : '' ?>><?= e($brand['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Сортування</span>
                <select name="sort">
                    <option value="" <?= $sort === '' ? 'selected' : '' ?>>За замовчуванням</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Спочатку дешевші</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Спочатку дорожчі</option>
                    <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>За назвою</option>
                </select>
            </label>
            <button class="btn btn-primary" type="submit">Застосувати</button>
        </form>

        <?php if (count($products) > 0): ?>
            <div class="product-grid catalog-product-grid">
                <?php foreach ($products as $product): ?>
                    <?php require __DIR__ . '/includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>Товарів за цим фільтром не знайдено</h2>
                <p>Спробуйте обрати інший бренд або повернути фільтр “Всі бренди”.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
