<?php
require_once __DIR__ . '/includes/functions.php';
http_response_code(404);
$pageTitle = page_title('Сторінку не знайдено');
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">404</span>
        <h1>Сторінку не знайдено</h1>
        <p>Перейдіть у каталог або поверніться на головну.</p>
        <a class="btn btn-primary" href="/catalog">У каталог</a>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
