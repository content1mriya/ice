<?php
require_once __DIR__ . '/includes/functions.php';
$config = app_config();

function resolve_brand_slug_from_request(): string
{
    $brand = trim((string)($_GET['brand'] ?? ''));

    if ($brand === '') {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
        $parts = array_values(array_filter(explode('/', trim($path, '/'))));
        $brandIndex = array_search('brand', $parts, true);
        if ($brandIndex !== false && isset($parts[$brandIndex + 1])) {
            $brand = $parts[$brandIndex + 1];
        }
    }

    return get_brand_slug(urldecode($brand));
}

$brandSlug = resolve_brand_slug_from_request();

$brandContent = [
    'tcl' => [
        'name' => 'TCL',
        'title' => 'Кондиціонери TCL',
        'logo' => '/assets/images/tcl.png',
        'description' => 'TCL — сучасний бренд кліматичної техніки для дому, квартири, офісу та комерційних приміщень. У каталозі представлені моделі для охолодження, обігріву та щоденного підтримання комфортного мікроклімату. Кондиціонери TCL поєднують лаконічний дизайн, зручне керування, енергоефективність і практичний набір функцій для стабільної роботи протягом сезону.',
    ],
    'hisense' => [
        'name' => 'Hisense',
        'title' => 'Кондиціонери Hisense',
        'logo' => '/assets/images/hisense.png',
        'description' => 'Hisense — надійні кондиціонери для комфортного клімату в житлових і робочих приміщеннях. Моделі бренду підходять для охолодження влітку, обігріву у міжсезоння та підтримання стабільної температури протягом дня. У лінійці можна підібрати рішення для квартири, приватного будинку, офісу або невеликого комерційного простору.',
    ],
    'skylux' => [
        'name' => 'SkyLux',
        'title' => 'Кондиціонери SkyLux',
        'logo' => '/assets/images/skylux.png',
        'description' => 'SkyLux — практична кліматична техніка для дому, офісу та комерційних об’єктів. Бренд пропонує моделі для різної площі та різних сценаріїв використання: від стандартних кімнат до більших просторів, де важлива стабільна робота, зрозуміле керування й оптимальне співвідношення ціни та можливостей.',
    ],
];

$brand = $brandContent[$brandSlug] ?? null;

if (!$brand) {
    http_response_code(404);
    $pageTitle = page_title('Бренд не знайдено');
    require __DIR__ . '/includes/header.php';
    echo '<section class="page-hero"><div class="container"><h1>Бренд не знайдено</h1><p>Перейдіть у каталог або оберіть бренд у меню.</p><a class="btn btn-primary" href="/catalog">У каталог</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$products = array_values(array_filter(get_visible_products(), function ($product) use ($brandSlug) {
    return get_brand_slug($product['brand'] ?? '') === $brandSlug;
}));

$pageTitle = page_title($brand['title']);
$pageDescription = $brand['description'];
require __DIR__ . '/includes/header.php';
?>
<section class="brand-page-hero">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлібні крихти">
            <a href="/">Головна</a>
            <span>/</span>
            <a href="/catalog">Каталог</a>
            <span>/</span>
            <span><?= e($brand['name']) ?></span>
        </nav>

        <div class="brand-page-card brand-page-card--with-logo">
            <div class="brand-page-content">
                <span class="eyebrow eyebrow-light">Бренд</span>
                <h1><?= e($brand['title']) ?></h1>
                <p><?= e($brand['description']) ?></p>
                <div class="brand-hero-actions">
                    <a class="btn btn-accent" href="#brand-products">Дивитися товари</a>
                    <button class="btn btn-light js-order-btn" type="button" data-product="Консультація щодо кондиціонерів <?= e($brand['name']) ?>">Отримати консультацію</button>
                </div>
            </div>

            <a class="brand-hero-logo brand-hero-logo--image" href="#brand-products" aria-label="Товари <?= e($brand['name']) ?>">
                <img src="<?= e($brand['logo']) ?>" alt="<?= e($brand['name']) ?>">
            </a>
        </div>
    </div>
</section>

<section class="section" id="brand-products">
    <div class="container">
        <div class="section-head section-head-row">
            <div>
                <span class="eyebrow">Товари бренду</span>
                <h2><?= e($brand['name']) ?> — моделі в каталозі</h2>
            </div>
            <a class="link-more" href="/catalog?brand=<?= e($brandSlug) ?>">Відкрити в каталозі →</a>
        </div>

        <?php if (count($products) > 0): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <?php require __DIR__ . '/includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>Товари бренду ще не додані</h2>
                <p>Після імпорту Excel всі товари з брендом <?= e($brand['name']) ?> будуть автоматично виведені тут.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
