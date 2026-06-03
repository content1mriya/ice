<?php
require_once __DIR__ . '/includes/functions.php';
$config = app_config();
$pageTitle = page_title('Кондиціонери для дому, офісу та бізнесу');
$pageDescription = 'Каталог кондиціонерів TCL, Hisense та SkyLux. Підбір, продаж, монтаж і заявка через форму.';
$visibleProducts = get_visible_products();

$featuredProducts = [];
$featuredBrands = ['skylux', 'tcl', 'hisense'];
$usedFeaturedSkus = [];
foreach ($featuredBrands as $featuredBrand) {
    $brandCount = 0;
    foreach ($visibleProducts as $candidateProduct) {
        $candidateBrand = strtolower(trim((string)($candidateProduct['brand'] ?? '')));
        $candidateSku = (string)($candidateProduct['sku'] ?? ($candidateProduct['slug'] ?? $candidateProduct['name'] ?? ''));
        if ($candidateBrand === $featuredBrand && $candidateSku !== '' && !isset($usedFeaturedSkus[$candidateSku])) {
            $featuredProducts[] = $candidateProduct;
            $usedFeaturedSkus[$candidateSku] = true;
            $brandCount++;
        }
        if ($brandCount >= 3) {
            break;
        }
    }
}

if (count($featuredProducts) < 9) {
    foreach ($visibleProducts as $candidateProduct) {
        $candidateSku = (string)($candidateProduct['sku'] ?? ($candidateProduct['slug'] ?? $candidateProduct['name'] ?? ''));
        if ($candidateSku !== '' && !isset($usedFeaturedSkus[$candidateSku])) {
            $featuredProducts[] = $candidateProduct;
            $usedFeaturedSkus[$candidateSku] = true;
        }
        if (count($featuredProducts) >= 9) {
            break;
        }
    }
}
require __DIR__ . '/includes/header.php';
?>

<section class="home-hero home-hero--image">
    <div class="home-hero-overlay"></div>
    <div class="container home-hero-inner">
        <div class="home-hero-content">
            <h1>Кондиціонери для дому, офісу та бізнесу</h1>
            <p>Допомагаємо підібрати кондиціонер під площу приміщення, бюджет і умови монтажу. Продаж, консультація та швидка заявка без складної корзини.</p>
            <div class="hero-actions">
                <a class="btn btn-accent" href="/catalog">Перейти в каталог</a>
                <button class="btn btn-light js-order-btn" type="button" data-product="Консультація з підбору кондиціонера">Отримати консультацію</button>
            </div>
        </div>
    </div>
</section>

<section class="section brand-logo-section brand-logo-section-premium" aria-label="Бренди кондиціонерів">
    <div class="container">
        <div class="brand-logo-grid">
            <a class="brand-logo-tile brand-logo-tile--image" href="/brand/tcl" aria-label="TCL">
                <img src="/assets/images/tcl.png" alt="TCL">
            </a>
            <a class="brand-logo-tile brand-logo-tile--image" href="/brand/hisense" aria-label="Hisense">
                <img src="/assets/images/hisense.png" alt="Hisense">
            </a>
            <a class="brand-logo-tile brand-logo-tile--image" href="/brand/skylux" aria-label="SkyLux">
                <img src="/assets/images/skylux.png" alt="SkyLux">
            </a>
        </div>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="section-head section-head-row">
            <div>
                <span class="eyebrow">Популярне</span>
                <h2>Хітові позиції</h2>
            </div>
            <a class="link-more" href="/catalog">Всі товари →</a>
        </div>

        <?php if (count($featuredProducts) > 0): ?>
            <div class="featured-carousel" data-product-carousel>
                <button class="carousel-arrow carousel-arrow--prev" type="button" aria-label="Попередні товари" data-carousel-prev>‹</button>
                <div class="featured-carousel-viewport" data-carousel-viewport>
                    <div class="featured-carousel-track">
                        <?php foreach ($featuredProducts as $product): ?>
                            <div class="featured-carousel-item">
                                <?php require __DIR__ . '/includes/product-card.php'; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button class="carousel-arrow carousel-arrow--next" type="button" aria-label="Наступні товари" data-carousel-next>›</button>
            </div>
        <?php else: ?>
            <div class="featured-carousel" data-product-carousel>
                <button class="carousel-arrow carousel-arrow--prev" type="button" aria-label="Попередні товари" data-carousel-prev>‹</button>
                <div class="featured-carousel-viewport" data-carousel-viewport>
                    <div class="featured-carousel-track">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <div class="featured-carousel-item">
                                <article class="product-card product-card--placeholder">
                                    <div class="product-card-image"><span>Фото товару</span></div>
                                    <div class="product-card-body">
                                        <span class="product-brand">Бренд</span>
                                        <h3 class="product-title">Місце під хітову позицію</h3>
                                        <div class="product-meta"><span class="product-price">Ціна</span><span class="availability">Наявність</span></div>
                                        <div class="product-actions"><span class="btn btn-secondary">Детальніше</span><span class="btn btn-primary">Замовити</span></div>
                                    </div>
                                </article>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <button class="carousel-arrow carousel-arrow--next" type="button" aria-label="Наступні товари" data-carousel-next>›</button>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section advantages-section-premium">
    <div class="container advantages-grid">
        <div class="advantage-card"><span>01</span><h3>Підбір під площу</h3><p>Допоможемо обрати потужність та модель під кімнату, квартиру або офіс.</p></div>
        <div class="advantage-card"><span>02</span><h3>Досвід з 2007 року</h3><p>Працюємо у сфері кліматичної техніки з 2007 року та добре розуміємо потреби клієнтів.</p></div>
        <div class="advantage-card"><span>03</span><h3>Перевірені бренди і моделі</h3><p>Підбираємо кондиціонери з актуальних лінійок TCL, Hisense та SkyLux для дому й бізнесу.</p></div>
        <div class="advantage-card"><span>04</span><h3>Монтаж кліматичних систем</h3><p>Надаємо монтаж будь-яких кліматичних систем і допомагаємо узгодити всі технічні деталі.</p></div>
    </div>
</section>

<section class="section install-section">
    <div class="container install-card">
        <div>
            <span class="eyebrow eyebrow-light">Монтаж</span>
            <h2>Забезпечуємо монтаж кондиціонерів</h2>
            <p>Після підбору моделі можна узгодити встановлення. Допоможемо підібрати місце монтажу, врахувати довжину траси, тип стіни та особливості приміщення.</p>
        </div>
        <button class="btn btn-accent js-order-btn" type="button" data-product="Консультація щодо монтажу кондиціонера">Уточнити монтаж</button>
    </div>
</section>

<section class="section request-section">
    <div class="container request-card">
        <div>
            <span class="eyebrow">Заявка</span>
            <h2>Потрібна допомога з вибором?</h2>
            <p>Залиште контакти — менеджер допоможе підібрати кондиціонер під площу, бюджет і бажаний функціонал.</p>
        </div>
        <button class="btn btn-primary js-order-btn" type="button" data-product="Консультація з підбору кондиціонера">Залишити заявку</button>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
