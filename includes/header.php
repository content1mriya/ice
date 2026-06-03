<?php
require_once __DIR__ . '/functions.php';
$config = app_config();
$pageTitle = $pageTitle ?? $config['site_name'];
$pageDescription = $pageDescription ?? $config['site_description'];
?>
<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="site-header site-header--dark">
    <div class="container header-inner">
        <div class="header-left">
            <a class="logo" href="/" aria-label="На головну">
                <span class="logo-mark">❄</span>
                <span class="logo-text"><?= e($config['site_name']) ?></span>
            </a>
            <a class="header-catalog-btn" href="/catalog">Каталог</a>
        </div>

        <button class="nav-toggle" type="button" aria-label="Відкрити меню" data-nav-toggle>
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav" data-main-nav>
            <div class="nav-dropdown">
                <span class="nav-link nav-link--dropdown">Бренди</span>
                <div class="nav-dropdown-menu">
                    <?php foreach ($config['brands'] as $slug => $brand): ?>
                        <a href="<?= e(brand_url($slug)) ?>"><?= e($brand['name']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <a class="nav-link<?= is_active_path('/about') ?>" href="/about">Про нас</a>
            <a class="nav-link<?= is_active_path('/payment-delivery') ?>" href="/payment-delivery">Оплата і доставка</a>
            <a class="nav-link<?= is_active_path('/warranty-return') ?>" href="/warranty-return">Гарантія</a>
            <a class="nav-link<?= is_active_path('/contacts') ?>" href="/contacts">Контакти</a>
        </nav>

        <div class="header-actions">
            <a class="header-phone" href="tel:+380679383447">+38 (067) 938 34 47</a>
            <button class="btn btn-accent btn-small js-order-btn" type="button" data-product="Консультація з підбору кондиціонера">Консультація</button>
        </div>
    </div>
</header>
<main class="site-main">
