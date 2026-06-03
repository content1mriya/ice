<?php
require_once __DIR__ . '/../includes/functions.php';

$brandSlug = 'skylux';
$brandName = 'SkyLux';
$brandTitle = 'Кондиціонери SkyLux';
$brandLogo = '/assets/images/skylux.png';
$brandDescription = 'SkyLux — практичні кондиціонери для дому, офісу та комерційних приміщень. Бренд пропонує рішення для різної площі та різних умов експлуатації, де важливі зрозуміле керування, стабільна робота, доступна ціна й комфортний мікроклімат.';

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
            <span>SkyLux</span>
        </nav>

        <div class="brand-page-card brand-page-card--with-logo">
            <div class="brand-page-content">
                <span class="eyebrow eyebrow-light">Бренд</span>
                <h1>Кондиціонери SkyLux</h1>
                <p>SkyLux — практичні кондиціонери для дому, офісу та комерційних приміщень. Бренд пропонує рішення для різної площі та різних умов експлуатації, де важливі зрозуміле керування, стабільна робота, доступна ціна й комфортний мікроклімат.</p>
                <div class="brand-hero-actions">
                    <a class="btn btn-accent" href="#brand-products">Дивитися товари</a>
                    <button class="btn btn-light js-order-btn" type="button" data-product="Консультація щодо кондиціонерів SkyLux">Отримати консультацію</button>
                </div>
            </div>

            <div class="brand-hero-logo brand-hero-logo--image" aria-label="Логотип SkyLux">
                <img src="/assets/images/skylux.png" alt="SkyLux" loading="eager">
            </div>
        </div>
    </div>
</section>

<section class="section" id="brand-products">
    <div class="container">
        <div class="section-head section-head-row">
            <div>
                <span class="eyebrow">Товари бренду</span>
                <h2>SkyLux — моделі в каталозі</h2>
            </div>
            <a class="link-more" href="/catalog?brand=skylux">Відкрити в каталозі →</a>
        </div>

        <?php if (count($products) > 0): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <?php require __DIR__ . '/../includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>Товари SkyLux ще не додані</h2>
                <p>Після імпорту Excel усі активні товари бренду SkyLux автоматично з’являться на цій сторінці.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
