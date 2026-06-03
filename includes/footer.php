<?php $config = app_config(); ?>
</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="logo footer-logo" href="/">
                <span class="logo-mark">❄</span>
                <span class="logo-text"><?= e($config['site_name']) ?></span>
            </a>
            <p class="footer-text">Каталог кліматичної техніки з консультацією, доставкою по Україні та монтажем у Києві й Київській області.</p>
        </div>
        <div>
            <h3 class="footer-title">Каталог</h3>
            <a href="/catalog">Всі кондиціонери</a>
            <?php foreach ($config['brands'] as $slug => $brand): ?>
                <a href="<?= e(brand_url($slug)) ?>"><?= e($brand['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <div>
            <h3 class="footer-title">Інформація</h3>
            <a href="/about">Про нас</a>
            <a href="/payment-delivery">Оплата і доставка</a>
            <a href="/warranty-return">Гарантія та повернення</a>
            <a href="/public-offer">Публічна оферта</a>
            <a href="/user-agreement">Угода користувача</a>
            <a href="/privacy-policy">Політика конфіденційності</a>
        </div>
        <div>
            <h3 class="footer-title">Контакти</h3>
            <a href="tel:+380679383447">+38 (067) 938 34 47</a>
            <span>ТОВ «ТЕХНОВІЗІЯ»</span>
            <span>Самовивіз: м. Київ, вул. Володимира Сікевича, 20</span>
            <span>Юридична адреса: Київська обл., Фастівський р-н, с. Новосілки, вул. Приміська, 26-Б, кв. 246</span>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>© <?= date('Y') ?> <?= e($config['site_name']) ?>. Всі права захищено.</span>
    </div>
</footer>

<div class="modal" id="orderModal" aria-hidden="true">
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="orderModalTitle">
        <button class="modal-close" type="button" aria-label="Закрити" data-modal-close>×</button>
        <h2 id="orderModalTitle">Залишити заявку</h2>
        <p class="modal-subtitle">Менеджер зв’яжеться з вами для уточнення деталей.</p>
        <form class="order-form" id="orderForm" action="/order-handler.php" method="post">
            <label><span>Товар</span><input type="text" name="product" id="orderProduct" readonly value="Консультація з підбору кондиціонера"></label>
            <label><span>Ім’я *</span><input type="text" name="name" required placeholder="Ваше ім’я"></label>
            <label><span>Телефон *</span><input type="tel" name="phone" required placeholder="+38 (___) ___-__-__"></label>
            <label><span>Місто</span><input type="text" name="city" placeholder="Ваше місто"></label>
            <label><span>Коментар</span><textarea name="comment" rows="4" placeholder="Наприклад: потрібен монтаж, площа кімнати 25 м²"></textarea></label>
            <input type="hidden" name="page_url" id="orderPageUrl" value="">
            <button class="btn btn-primary btn-block" type="submit">Відправити заявку</button>
            <p class="form-message" id="orderMessage"></p>
        </form>
    </div>
</div>
<script src="/assets/js/main.js"></script>
</body>
</html>
