<?php
require_once __DIR__ . '/../includes/functions.php';

$brandSlug = 'tcl';
$brandName = 'TCL';
$brandTitle = 'Кондиціонери TCL';
$brandLogo = '/assets/images/tcl.png';
$brandDescription = 'TCL — сучасна кліматична техніка для дому, квартири, офісу та комерційних приміщень. Кондиціонери бренду поєднують енергоефективність, зручне керування, акуратний дизайн і набір функцій для комфортного охолодження та обігріву протягом сезону.';

$products = array_values(array_filter(get_visible_products(), function ($product) use ($brandSlug) {
    return get_brand_slug($product['brand'] ?? '') === $brandSlug;
}));

$pageTitle = page_title($brandTitle);
$pageDescription = $brandDescription;
require __DIR__ . '/../includes/header.php';
?>
<section class="brand-page-hero">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлібні крихти">
            <a href="/">Головна</a>
            <span>/</span>
            <a href="/catalog">Каталог</a>
            <span>/</span>
            <span>TCL</span>
        </nav>

        <div class="brand-page-card brand-page-card--with-logo">
            <div class="brand-page-content">
                <span class="eyebrow eyebrow-light">Бренд</span>
                <h1>Кондиціонери TCL</h1>
                <p>TCL — сучасна кліматична техніка для дому, квартири, офісу та комерційних приміщень. Кондиціонери бренду поєднують енергоефективність, зручне керування, акуратний дизайн і набір функцій для комфортного охолодження та обігріву протягом сезону.</p>
                <div class="brand-hero-actions">
                    <a class="btn btn-accent" href="#brand-products">Дивитися товари</a>
                    <button class="btn btn-light js-order-btn" type="button" data-product="Консультація щодо кондиціонерів TCL">Отримати консультацію</button>
                </div>
            </div>

            <div class="brand-hero-logo brand-hero-logo--image" aria-label="Логотип TCL">
                <img src="/assets/images/tcl.png" alt="TCL" loading="eager">
            </div>
        </div>
    </div>
</section>

<section class="section" id="brand-products">
    <div class="container">
        <div class="section-head section-head-row">
            <div>
                <span class="eyebrow">Товари бренду</span>
                <h2>TCL — моделі в каталозі</h2>
            </div>
            <a class="link-more" href="/catalog?brand=tcl">Відкрити в каталозі →</a>
        </div>

        <?php if (count($products) > 0): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <?php require __DIR__ . '/../includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>Товари TCL ще не додані</h2>
                <p>Після імпорту Excel усі активні товари бренду TCL автоматично з’являться на цій сторінці.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
