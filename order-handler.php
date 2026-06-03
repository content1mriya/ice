<?php
require_once __DIR__ . '/includes/functions.php';

$config = app_config();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не дозволений.'], JSON_UNESCAPED_UNICODE);
    exit;
}

function clean_order_value(string $value): string
{
    $value = strip_tags($value);
    $value = str_replace(["\r", "\n"], ' ', $value);
    return trim($value);
}

$name = clean_order_value((string)($_POST['name'] ?? ''));
$phone = clean_order_value((string)($_POST['phone'] ?? ''));
$city = clean_order_value((string)($_POST['city'] ?? ''));
$product = clean_order_value((string)($_POST['product'] ?? ''));
$comment = trim(strip_tags((string)($_POST['comment'] ?? '')));
$pageUrl = trim((string)($_POST['page_url'] ?? ''));

if ($name === '' || $phone === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Заповніть ім’я та телефон.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$siteName = (string)($config['site_name'] ?? 'ТЕХНОВІЗІЯ');
$recipientEmail = (string)($config['site_email'] ?? 'content.1mriya@gmail.com');

if ($recipientEmail === '' || $recipientEmail === 'your-email@example.com') {
    $recipientEmail = 'content.1mriya@gmail.com';
}

$createdAt = date('Y-m-d H:i:s');
$ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
$host = preg_replace('/^www\./', '', (string)($_SERVER['HTTP_HOST'] ?? 'technovizia.com'));
$fromEmail = 'noreply@' . $host;

$order = [$createdAt, $product, $name, $phone, $city, $comment, $pageUrl, $ip];

$ordersPath = storage_path('orders.csv');
$needHeader = !is_file($ordersPath) || filesize($ordersPath) === 0;
$fh = fopen($ordersPath, 'a');

if ($fh) {
    flock($fh, LOCK_EX);

    if ($needHeader) {
        fputcsv($fh, ['Дата', 'Товар', 'Ім’я', 'Телефон', 'Місто', 'Коментар', 'Сторінка', 'IP']);
    }

    fputcsv($fh, $order);
    flock($fh, LOCK_UN);
    fclose($fh);
}

$message = "Нова заявка з сайту {$siteName}\n\n" .
    "Дата: {$createdAt}\n" .
    "Товар: " . ($product !== '' ? $product : 'Не вказано') . "\n" .
    "Ім'я: {$name}\n" .
    "Телефон: {$phone}\n" .
    "Місто: " . ($city !== '' ? $city : 'Не вказано') . "\n" .
    "Коментар: " . ($comment !== '' ? $comment : 'Без коментаря') . "\n" .
    "Сторінка: " . ($pageUrl !== '' ? $pageUrl : 'Не вказано') . "\n" .
    "IP: " . ($ip !== '' ? $ip : 'Не визначено') . "\n";

$subject = 'Нова заявка з сайту ' . $siteName;
$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';
$headers[] = 'Content-Transfer-Encoding: 8bit';
$headers[] = 'From: ' . $siteName . ' <' . $fromEmail . '>';
$headers[] = 'Reply-To: ' . $recipientEmail;
$headers[] = 'X-Mailer: PHP/' . phpversion();

$mailSent = @mail($recipientEmail, $encodedSubject, $message, implode("\r\n", $headers));

if (!empty($config['telegram_bot_token']) && !empty($config['telegram_chat_id'])) {
    $telegramUrl = 'https://api.telegram.org/bot' . $config['telegram_bot_token'] . '/sendMessage';
    $payload = http_build_query([
        'chat_id' => $config['telegram_chat_id'],
        'text' => $message,
    ]);
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 5,
        ],
    ]);
    @file_get_contents($telegramUrl, false, $context);
}

echo json_encode([
    'success' => true,
    'message' => 'Заявку відправлено. Ми скоро зв’яжемося з вами.',
    'mail_sent' => $mailSent,
], JSON_UNESCAPED_UNICODE);
