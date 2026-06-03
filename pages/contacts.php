<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = page_title('Контакти');
$pageDescription = 'Контакти компанії ТЕХНОВІЗІЯ: телефон, адреса, реквізити та форма заявки.';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_text_page_styles.php';
?>
<section class="text-hero"><div class="container"><span class="eyebrow">Контакти</span><h1>Зв’яжіться з нами для підбору кліматичної техніки</h1><p>Допоможемо підібрати кондиціонер, уточнимо наявність, підготуємо рахунок і проконсультуємо щодо доставки або монтажу.</p></div></section>
<section class="text-layout"><div class="container"><article class="text-card">
<h2>Основні контакти</h2>
<div class="contact-list">
    <div class="contact-card"><span>Телефон</span><a href="tel:+380679383447">+38 (067) 938 34 47</a></div>
    <div class="contact-card"><span>Компанія</span><b>ТОВ «ТЕХНОВІЗІЯ»</b></div>
    <div class="contact-card"><span>Юридична адреса</span><b>Київська обл., Фастівський р-н, с. Новосілки, вул. Приміська, 26-Б, кв. 246</b></div>
    <div class="contact-card"><span>Самовивіз</span><b>м. Київ, вул. Володимира Сікевича, 20</b></div>
</div>
<h2>Реквізити компанії</h2>
<div class="legal-box"><p><strong>ТОВАРИСТВО З ОБМЕЖЕНОЮ ВІДПОВІДАЛЬНІСТЮ «ТЕХНОВІЗІЯ»</strong></p><p>Р/р UA693052990000026004045041261, АТ КБ «ПРИВАТБАНК»</p><p>Код ЄДРПОУ: 45698246</p><p>ІПН: 456982410131</p><p>Директор: Даценко Марія Вячеславівна</p></div>
<h2>Заявка на консультацію</h2>
<p>Натисніть кнопку нижче, залиште ім’я, телефон і коментар. Менеджер зв’яжеться з вами для уточнення деталей.</p>
<div class="cta-text"><div><h2>Потрібна консультація?</h2><p>Підберемо модель під площу, бюджет і умови монтажу.</p></div><button class="btn btn-accent js-order-btn" type="button" data-product="Консультація з підбору кондиціонера">Залишити заявку</button></div>
</article></div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
