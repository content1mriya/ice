<?php
$productName = (string)($product['name'] ?? 'Назва товару');
$price = format_price($product['price'] ?? '');
$availability = trim((string)($product['availability'] ?? 'Уточнюйте наявність'));
$brandName = trim((string)($product['brand'] ?? ''));
?>
<article class="product-card">
    <a class="product-card-image" href="<?= e(product_url($product)) ?>">
        <img src="<?= e(get_product_image($product)) ?>" alt="<?= e($productName) ?>" loading="lazy">
    </a>
    <div class="product-card-body">
        <?php if ($brandName !== ''): ?>
            <a class="product-brand" href="<?= e(brand_url(get_brand_slug($brandName))) ?>"><?= e($brandName) ?></a>
        <?php endif; ?>
        <h3 class="product-title"><a href="<?= e(product_url($product)) ?>"><?= e($productName) ?></a></h3>
        <div class="product-meta">
            <?php if ($price !== ''): ?>
                <span class="product-price"><?= e($price) ?></span>
            <?php else: ?>
                <span class="product-price product-price--empty">Ціну уточнюйте</span>
            <?php endif; ?>
            <span class="availability <?= e(get_availability_class($availability)) ?>"><?= e($availability) ?></span>
        </div>
        <div class="product-actions">
            <a class="btn btn-secondary" href="<?= e(product_url($product)) ?>">Детальніше</a>
            <button class="btn btn-primary js-order-btn" type="button" data-product="<?= e($productName) ?>">Замовити</button>
        </div>
    </div>
</article>
