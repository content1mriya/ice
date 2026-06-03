<?php
require_once __DIR__ . '/includes/functions.php';
$config = app_config();
$key = $_GET['key'] ?? '';

if (!hash_equals($config['import_key'], $key)) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

$pageTitle = page_title('Імпорт товарів');
$xlsxPath = __DIR__ . '/data/products.xlsx';
$jsonPath = storage_path('products.json');
$uploadsRoot = __DIR__ . '/uploads/products';
$messages = [];
$errors = [];
$importResult = null;

function import_col_index(string $letters): int
{
    $letters = strtoupper($letters);
    $index = 0;
    for ($i = 0; $i < strlen($letters); $i++) {
        $index = $index * 26 + (ord($letters[$i]) - 64);
    }
    return $index - 1;
}

function import_cell_text(SimpleXMLElement $cell, array $sharedStrings): string
{
    $type = (string)($cell['t'] ?? '');

    $cell->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

    if ($type === 'inlineStr') {
        $texts = [];
        $nodes = $cell->xpath('.//x:t');

        if ($nodes) {
            foreach ($nodes as $t) {
                $texts[] = (string)$t;
            }
        }

        return trim(implode('', $texts));
    }

    $valueNodes = $cell->xpath('./x:v');
    $value = '';

    if ($valueNodes && isset($valueNodes[0])) {
        $value = (string)$valueNodes[0];
    } elseif (isset($cell->v)) {
        $value = (string)$cell->v;
    }

    if ($type === 's') {
        $idx = (int)$value;
        return trim((string)($sharedStrings[$idx] ?? ''));
    }

    return trim($value);
}
function import_read_shared_strings(ZipArchive $zip): array
{
    $xml = $zip->getFromName('xl/sharedStrings.xml');

    if ($xml === false) {
        return [];
    }

    $sx = simplexml_load_string($xml);

    if (!$sx) {
        return [];
    }

    $sx->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

    $strings = [];
    $items = $sx->xpath('//x:si');

    if (!$items) {
        return [];
    }

    foreach ($items as $si) {
        $si->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $parts = [];
        $texts = $si->xpath('.//x:t');

        if ($texts) {
            foreach ($texts as $t) {
                $parts[] = (string)$t;
            }
        }

        $strings[] = implode('', $parts);
    }

    return $strings;
}

function import_read_xlsx_rows(string $path): array
{
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('На хостингу не встановлено PHP-розширення ZipArchive. Без нього PHP не може прочитати .xlsx файл.');
    }

    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        throw new RuntimeException('Не вдалося відкрити products.xlsx. Перевір, чи файл не пошкоджений.');
    }

    $sharedStrings = import_read_shared_strings($zip);
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();

    if ($sheetXml === false) {
        throw new RuntimeException('У products.xlsx не знайдено перший лист sheet1.xml.');
    }

    $sheet = simplexml_load_string($sheetXml);
    if (!$sheet) {
        throw new RuntimeException('Не вдалося прочитати XML першого листа Excel.');
    }

    $rows = [];
    foreach ($sheet->sheetData->row as $row) {
        $cells = [];
        foreach ($row->c as $cell) {
            $ref = (string)($cell['r'] ?? '');
            if ($ref !== '' && preg_match('/^([A-Z]+)/i', $ref, $m)) {
                $idx = import_col_index($m[1]);
            } else {
                $idx = count($cells);
            }
            $cells[$idx] = import_cell_text($cell, $sharedStrings);
        }
        if ($cells) {
            ksort($cells);
            $max = max(array_keys($cells));
            $normalized = [];
            for ($i = 0; $i <= $max; $i++) {
                $normalized[] = $cells[$i] ?? '';
            }
            $rows[] = $normalized;
        }
    }

    return $rows;
}

function import_header_key(string $value): string
{
    $value = trim(mb_strtolower($value, 'UTF-8'));
    $value = str_replace([' ', '-', '–'], '_', $value);
    return preg_replace('/[^a-zа-яіїєґ0-9_]+/u', '', $value);
}

function import_split_urls($value): array
{
    $value = trim((string)$value);
    if ($value === '') {
        return [];
    }
    $value = str_replace(["\r\n", "\r", ';'], "\n", $value);
    $parts = preg_split('/\n|,/', $value);
    $urls = [];
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part !== '' && preg_match('~^https?://~i', $part)) {
            $urls[] = $part;
        }
    }
    return array_values(array_unique($urls));
}

function import_normalize_brand(string $brand): string
{
    $brand = trim($brand);
    $key = mb_strtolower(str_replace([' ', '-', '_'], '', $brand), 'UTF-8');
    $map = [
        'tcl' => 'TCL',
        'тсл' => 'TCL',
        'hisense' => 'Hisense',
        'skylux' => 'SkyLux',
        'sky lux' => 'SkyLux',
    ];
    return $map[$key] ?? $brand;
}

function import_image_extension(string $url, string $contentType = ''): string
{
    $path = parse_url($url, PHP_URL_PATH) ?: '';
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (in_array($ext, $allowed, true)) {
        return $ext;
    }
    if (stripos($contentType, 'png') !== false) return 'png';
    if (stripos($contentType, 'webp') !== false) return 'webp';
    if (stripos($contentType, 'gif') !== false) return 'gif';
    return 'jpg';
}

function import_download_image(string $url, string $productSlug, int $position, string $uploadsRoot, array &$errors): string
{
    $productSlug = slugify($productSlug);
    $dir = rtrim($uploadsRoot, '/') . '/' . $productSlug;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        $errors[] = 'Не вдалося створити папку для фото: ' . $dir;
        return $url;
    }

    $context = stream_context_create([
        'http' => [
            'timeout' => 25,
            'follow_location' => 1,
            'header' => "User-Agent: Mozilla/5.0\r\n",
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    $data = @file_get_contents($url, false, $context);
    if ($data === false || strlen($data) < 100) {
        $errors[] = 'Фото не завантажилось, залишено зовнішнє посилання: ' . $url;
        return $url;
    }

    $contentType = '';
    if (!empty($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (stripos($header, 'Content-Type:') === 0) {
                $contentType = trim(substr($header, 13));
                break;
            }
        }
    }

    $ext = import_image_extension($url, $contentType);
    $filename = sprintf('%02d.%s', $position, $ext);
    $fullPath = $dir . '/' . $filename;

    if (@file_put_contents($fullPath, $data) === false) {
        $errors[] = 'Не вдалося записати фото: ' . $fullPath;
        return $url;
    }

    return '/uploads/products/' . $productSlug . '/' . $filename;
}

function import_existing_products(string $jsonPath): array
{
    if (!is_file($jsonPath)) {
        return [];
    }
    $json = file_get_contents($jsonPath);
    $data = json_decode($json, true);
    if (!is_array($data)) {
        return [];
    }
    $products = isset($data['products']) && is_array($data['products']) ? $data['products'] : $data;
    $indexed = [];
    foreach ($products as $product) {
        $sku = trim((string)($product['sku'] ?? ''));
        if ($sku !== '') {
            $indexed[$sku] = $product;
        }
    }
    return $indexed;
}

function import_products_from_xlsx(string $xlsxPath, string $jsonPath, string $uploadsRoot, array &$messages, array &$errors): array
{
    $rows = import_read_xlsx_rows($xlsxPath);
    if (count($rows) < 2) {
        throw new RuntimeException('У products.xlsx немає товарів або не знайдено рядок заголовків.');
    }

    $headersRaw = array_shift($rows);
    $headers = [];
    foreach ($headersRaw as $i => $header) {
        $headers[$i] = import_header_key((string)$header);
    }

    $required = ['sku', 'brand', 'name'];
    foreach ($required as $col) {
        if (!in_array($col, $headers, true)) {
            throw new RuntimeException('У файлі не вистачає обов’язкової колонки: ' . $col);
        }
    }

    $existing = import_existing_products($jsonPath);
    $updatedSkus = [];
    $created = 0;
    $updated = 0;
    $imageCount = 0;

    foreach ($rows as $row) {
        $item = [];
        foreach ($headers as $i => $header) {
            if ($header !== '') {
                $item[$header] = isset($row[$i]) ? trim((string)$row[$i]) : '';
            }
        }

        $sku = trim((string)($item['sku'] ?? ''));
        $name = trim((string)($item['name'] ?? ''));
        if ($sku === '' && $name === '') {
            continue;
        }
        if ($sku === '') {
            $sku = slugify($name);
        }
        if ($name === '') {
            $name = $sku;
        }

        $slug = !empty($item['slug']) ? slugify((string)$item['slug']) : slugify($name . '-' . $sku);
        $brand = import_normalize_brand((string)($item['brand'] ?? ''));
        $wasExisting = isset($existing[$sku]);

        $product = $existing[$sku] ?? [];
        $product = array_merge($product, [
            'sku' => $sku,
            'brand' => $brand,
            'name' => $name,
            'slug' => $slug,
            'price' => (string)($item['price'] ?? ''),
            'availability' => (string)($item['availability'] ?? 'Уточнюйте наявність'),
            'status' => ($item['status'] ?? '') !== '' ? (string)$item['status'] : '1',
            'short_description' => (string)($item['short_description'] ?? ''),
            'description_html' => (string)($item['description_html'] ?? ''),
            'main_image' => (string)($item['main_image'] ?? ''),
            'gallery' => (string)($item['gallery'] ?? ''),
            'characteristics' => (string)($item['characteristics'] ?? ''),
            'updated_at' => date('c'),
        ]);

        $urls = [];
        $mainUrls = import_split_urls($product['main_image'] ?? '');
        if (!empty($mainUrls)) {
            $urls[] = $mainUrls[0];
        }
        foreach (import_split_urls($product['gallery'] ?? '') as $url) {
            $urls[] = $url;
        }
        $urls = array_values(array_unique($urls));

        $localUrls = [];
        foreach ($urls as $index => $url) {
            $local = import_download_image($url, $slug, $index + 1, $uploadsRoot, $errors);
            $localUrls[] = $local;
            if (strpos($local, '/uploads/products/') === 0) {
                $imageCount++;
            }
        }

        if (!empty($localUrls)) {
            $product['main_image_local'] = $localUrls[0];
            $product['gallery_local'] = implode("\n", array_slice($localUrls, 1));
        }

        $existing[$sku] = $product;
        $updatedSkus[$sku] = true;
        $wasExisting ? $updated++ : $created++;
    }

    $products = array_values($existing);
    usort($products, function ($a, $b) {
        return strcmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
    });

    $payload = [
        'generated_at' => date('c'),
        'source_file' => 'data/products.xlsx',
        'total' => count($products),
        'products' => $products,
    ];

    if (!is_dir(dirname($jsonPath)) && !mkdir(dirname($jsonPath), 0755, true) && !is_dir(dirname($jsonPath))) {
        throw new RuntimeException('Не вдалося створити папку storage.');
    }

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if (@file_put_contents($jsonPath, $json) === false) {
        throw new RuntimeException('Не вдалося записати storage/products.json. Перевір права на папку storage.');
    }

    $messages[] = 'Створено нових товарів: ' . $created;
    $messages[] = 'Оновлено існуючих товарів: ' . $updated;
    $messages[] = 'Усього товарів у products.json: ' . count($products);
    $messages[] = 'Завантажено фото на хостинг: ' . $imageCount;

    return [
        'created' => $created,
        'updated' => $updated,
        'total' => count($products),
        'images' => $imageCount,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!is_file($xlsxPath)) {
            throw new RuntimeException('Файл /data/products.xlsx не знайдено. Спочатку завантаж його у папку /data/.');
        }
        $importResult = import_products_from_xlsx($xlsxPath, $jsonPath, $uploadsRoot, $messages, $errors);
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Службова сторінка</span>
        <h1>Імпорт товарів</h1>
        <?php if (is_file($xlsxPath)): ?>
            <p>Файл <strong>/data/products.xlsx</strong> знайдено. Натисни кнопку нижче, щоб створити або оновити <strong>/storage/products.json</strong> і підтягнути фото в <strong>/uploads/products/</strong>.</p>
        <?php else: ?>
            <p>Файл <strong>/data/products.xlsx</strong> не знайдено. Завантаж його через файловий менеджер у папку <strong>/data/</strong>.</p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <article class="content-card">
            <h2>Запуск імпорту</h2>
            <?php if (!empty($messages)): ?>
                <div class="notice notice-success">
                    <?php foreach ($messages as $message): ?>
                        <p><?= e($message) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="notice notice-error">
                    <h3>Повідомлення імпорту</h3>
                    <?php foreach (array_slice($errors, 0, 30) as $error): ?>
                        <p><?= e($error) ?></p>
                    <?php endforeach; ?>
                    <?php if (count($errors) > 30): ?>
                        <p>І ще <?= e(count($errors) - 30) ?> повідомлень по фото або файлах.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/import.php?key=<?= e($key) ?>">
                <button class="btn btn-primary btn-large" type="submit" <?= is_file($xlsxPath) ? '' : 'disabled' ?>>Запустити імпорт товарів</button>
            </form>

            <?php if ($importResult): ?>
                <p style="margin-top: 18px;"><a class="btn btn-secondary" href="/catalog">Перейти в каталог</a></p>
            <?php endif; ?>
        </article>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
