<?php
require_once __DIR__ . '/includes/functions.php';
$config = app_config();
$slug = $_GET['slug'] ?? '';
$product = find_product_by_slug($slug);

if (!$product) {
    http_response_code(404);
    $pageTitle = page_title('Товар не знайдено');
    require __DIR__ . '/includes/header.php';
    echo '<section class="page-hero"><div class="container"><h1>Товар не знайдено</h1><p>Можливо, товар ще не доданий або прихований.</p><a class="btn btn-primary" href="/catalog">Повернутися в каталог</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$productName = (string)($product['name'] ?? 'Товар');
$price = format_price($product['price'] ?? '');
$availability = trim((string)($product['availability'] ?? 'Уточнюйте наявність'));
$brandName = trim((string)($product['brand'] ?? ''));
$brandSlug = get_brand_slug($brandName);
$mainImage = get_product_image($product);
$gallery = split_list($product['gallery_local'] ?? $product['gallery'] ?? '');
$allImages = array_values(array_unique(array_filter(array_merge([$mainImage], $gallery))));
$characteristics = parse_characteristics($product['characteristics'] ?? '');
$relatedProducts = [];
foreach (get_visible_products() as $item) {
    if (get_product_slug($item) === get_product_slug($product)) {
        continue;
    }
    if (get_brand_slug($item['brand'] ?? '') === $brandSlug) {
        $relatedProducts[] = $item;
    }
    if (count($relatedProducts) >= 4) {
        break;
    }
}
$pageTitle = page_title($productName);
$pageDescription = strip_tags((string)($product['short_description'] ?? $productName));
require __DIR__ . '/includes/header.php';
?>
<style>
/* Emergency product-page overrides */
.product-layout, .product-layout-updated, .product-layout-compact{align-items:start!important;}
.product-info-updated{display:block!important;height:auto!important;min-height:0!important;align-self:start!important;justify-content:flex-start!important;padding:34px!important;}
.product-info-updated:before,.product-info-updated:after{display:none!important;content:none!important;}
.product-buy-box{margin:20px 0 14px!important;}
.short-description{margin-bottom:24px!important;}
.product-tabs-section{padding-top:28px!important;}
.product-tabs-nav{display:inline-flex!important;width:fit-content!important;gap:8px!important;padding:7px!important;border:1px solid #E2E8F0!important;border-radius:999px!important;background:#fff!important;box-shadow:0 12px 34px rgba(15,61,94,.06)!important;margin-bottom:18px!important;}
.product-tab-btn{border:0!important;border-radius:999px!important;padding:13px 24px!important;background:transparent!important;color:#64748B!important;font-weight:900!important;cursor:pointer!important;}
.product-tab-btn.is-active{color:#fff!important;background:#0F3D5E!important;}
.product-tab-panel{display:none!important;}
.product-tab-panel.is-active{display:block!important;}
.product-tab-panel.content-card{width:100%!important;}
.characteristics-table-compact{max-width:760px!important;}
.characteristics-table-compact td{padding-left:34px!important;}
@media(max-width:820px){.product-tabs-nav{display:grid!important;width:100%!important;grid-template-columns:1fr 1fr;border-radius:22px!important}.product-tab-btn{border-radius:16px!important}.characteristics-table-compact{max-width:100%!important}}
</style>
<section class="product-top section">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлібні крихти">
            <a href="/">Головна</a>
            <span>/</span>
            <a href="/catalog">Каталог</a>
            <?php if ($brandName !== ''): ?>
                <span>/</span>
                <a href="<?= e(brand_url($brandSlug)) ?>"><?= e($brandName) ?></a>
            <?php endif; ?>
            <span>/</span>
            <span><?= e($productName) ?></span>
        </nav>

        <h1 class="product-page-title"><?= e($productName) ?></h1>

        <div class="product-layout product-layout-updated product-layout-compact">
            <div class="product-gallery">
                <button class="product-main-image js-lightbox-open" type="button" data-index="0" aria-label="Відкрити фото">
                    <img src="<?= e($mainImage) ?>" alt="<?= e($productName) ?>" id="productMainImage">
                </button>
                <?php if (count($allImages) > 1): ?>
                    <div class="product-thumbs">
                        <?php foreach ($allImages as $index => $image): ?>
                            <button class="product-thumb js-product-thumb <?= $index === 0 ? 'is-active' : '' ?>" type="button" data-index="<?= (int)$index ?>" data-src="<?= e($image) ?>" aria-label="Фото <?= (int)$index + 1 ?>">
                                <img src="<?= e($image) ?>" alt="<?= e($productName) ?>" loading="lazy">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-info product-info-updated">
                <?php if ($brandName !== ''): ?>
                    <a class="product-brand" href="<?= e(brand_url($brandSlug)) ?>"><?= e($brandName) ?></a>
                <?php endif; ?>
                <div class="product-buy-box">
                    <?php if ($price !== ''): ?>
                        <div class="product-page-price"><?= e($price) ?></div>
                    <?php else: ?>
                        <div class="product-page-price">Ціну уточнюйте</div>
                    <?php endif; ?>
                    <div class="availability <?= e(get_availability_class($availability)) ?>"><?= e($availability) ?></div>
                </div>

                <?php if (!empty($product['short_description'])): ?>
                    <div class="short-description"><?= nl2br(e($product['short_description'])) ?></div>
                <?php endif; ?>

                <button class="btn btn-primary btn-large js-order-btn" type="button" data-product="<?= e($productName) ?>">Замовити</button>
            </div>
        </div>
    </div>
</section>

<section class="section consultation-section">
    <div class="container consultation-card">
        <div>
            <span class="eyebrow eyebrow-light">Консультація</span>
            <h2>Не впевнені, що ця модель підходить?</h2>
            <p>Залиште заявку — допоможемо перевірити потужність під площу приміщення, підібрати альтернативу та узгодити монтаж.</p>
        </div>
        <button class="btn btn-accent js-order-btn" type="button" data-product="Консультація щодо <?= e($productName) ?>">Отримати консультацію</button>
    </div>
</section>

<?php if (!empty($product['description_html']) || count($characteristics) > 0): ?>
<section class="section section-soft product-tabs-section">
    <div class="container">
        <div class="product-tabs" data-tabs>
            <div class="product-tabs-nav" role="tablist" aria-label="Інформація про товар">
                <?php if (!empty($product['description_html'])): ?>
                    <button class="product-tab-btn is-active" type="button" data-tab-target="description" role="tab" aria-selected="true">Опис</button>
                <?php endif; ?>
                <?php if (count($characteristics) > 0): ?>
                    <button class="product-tab-btn <?= empty($product['description_html']) ? 'is-active' : '' ?>" type="button" data-tab-target="characteristics" role="tab" aria-selected="<?= empty($product['description_html']) ? 'true' : 'false' ?>">Характеристики</button>
                <?php endif; ?>
            </div>

            <?php if (!empty($product['description_html'])): ?>
                <article class="content-card product-tab-panel is-active" data-tab-panel="description" role="tabpanel">
                    <div class="html-description html-description-full"><?= $product['description_html'] ?></div>
                </article>
            <?php endif; ?>

            <?php if (count($characteristics) > 0): ?>
                <article class="content-card product-tab-panel <?= empty($product['description_html']) ? 'is-active' : '' ?>" data-tab-panel="characteristics" role="tabpanel">
                    <table class="characteristics-table characteristics-table-compact">
                        <?php foreach ($characteristics as $row): ?>
                            <tr>
                                <th><?= e($row['name']) ?></th>
                                <td><?= e($row['value']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </article>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (count($relatedProducts) > 0): ?>
<section class="section related-section">
    <div class="container">
        <div class="section-head section-head-row">
            <div>
                <span class="eyebrow">Бренд</span>
                <h2>Товари цього бренду</h2>
            </div>
            <a class="link-more" href="<?= e(brand_url($brandSlug)) ?>">Всі <?= e($brandName) ?> →</a>
        </div>
        <div class="product-grid">
            <?php foreach ($relatedProducts as $relatedProduct): ?>
                <?php $product = $relatedProduct; require __DIR__ . '/includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (count($allImages) > 0): ?>
<div class="lightbox" id="productLightbox" aria-hidden="true">
    <button class="lightbox-close" type="button" data-lightbox-close aria-label="Закрити">×</button>
    <button class="lightbox-arrow lightbox-prev" type="button" data-lightbox-prev aria-label="Попереднє фото">‹</button>
    <img src="<?= e($allImages[0]) ?>" alt="<?= e($productName) ?>" id="lightboxImage">
    <button class="lightbox-arrow lightbox-next" type="button" data-lightbox-next aria-label="Наступне фото">›</button>
</div>
<script>
window.productGalleryImages = <?= json_encode($allImages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>
<?php endif; ?>

<script>
(function () {
  document.addEventListener('click', function (event) {
    var button = event.target.closest('[data-tab-target]');
    if (!button) return;
    var tabs = button.closest('[data-tabs]');
    if (!tabs) return;
    var target = button.getAttribute('data-tab-target');
    tabs.querySelectorAll('[data-tab-target]').forEach(function (btn) {
      var active = btn === button;
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    tabs.querySelectorAll('[data-tab-panel]').forEach(function (panel) {
      panel.classList.toggle('is-active', panel.getAttribute('data-tab-panel') === target);
    });
  });
})();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
