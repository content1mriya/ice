<?php
function app_config(): array
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config.php';
    }
    return $config;
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function slugify(string $value): string
{
    $value = trim($value);
    $map = [
        'А'=>'a','Б'=>'b','В'=>'v','Г'=>'h','Ґ'=>'g','Д'=>'d','Е'=>'e','Є'=>'ye','Ж'=>'zh','З'=>'z','И'=>'y','І'=>'i','Ї'=>'yi','Й'=>'y','К'=>'k','Л'=>'l','М'=>'m','Н'=>'n','О'=>'o','П'=>'p','Р'=>'r','С'=>'s','Т'=>'t','У'=>'u','Ф'=>'f','Х'=>'kh','Ц'=>'ts','Ч'=>'ch','Ш'=>'sh','Щ'=>'shch','Ь'=>'','Ю'=>'yu','Я'=>'ya',
        'а'=>'a','б'=>'b','в'=>'v','г'=>'h','ґ'=>'g','д'=>'d','е'=>'e','є'=>'ye','ж'=>'zh','з'=>'z','и'=>'y','і'=>'i','ї'=>'yi','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'kh','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'shch','ь'=>'','ю'=>'yu','я'=>'ya',
        'Ё'=>'yo','ё'=>'yo','Ы'=>'y','ы'=>'y','Э'=>'e','э'=>'e','Ъ'=>'','ъ'=>'',
    ];
    $value = strtr($value, $map);
    $value = strtolower($value);
    $value = preg_replace('~[^a-z0-9]+~', '-', $value);
    $value = trim($value, '-');
    return $value !== '' ? $value : 'item';
}

function storage_path(string $file = ''): string
{
    return __DIR__ . '/../storage' . ($file ? '/' . ltrim($file, '/') : '');
}

function get_products(): array
{
    $path = storage_path('products.json');
    if (!is_file($path)) {
        return [];
    }
    $json = file_get_contents($path);
    $data = json_decode($json, true);
    if (!is_array($data)) {
        return [];
    }
    if (isset($data['products']) && is_array($data['products'])) {
        return $data['products'];
    }
    return $data;
}

function is_product_visible(array $product): bool
{
    $status = strtolower(trim((string)($product['status'] ?? '1')));
    return in_array($status, ['1', 'yes', 'true', 'так', 'да', 'show', 'active'], true);
}

function get_visible_products(): array
{
    return array_values(array_filter(get_products(), 'is_product_visible'));
}

function get_brand_slug($brand): string
{
    $brand = trim((string)$brand);
    $normalized = strtolower(str_replace([' ', '&'], ['', 'and'], $brand));
    $known = [
        'tcl' => 'tcl',
        'тсл' => 'tcl',
        'hisense' => 'hisense',
        'skylux' => 'skylux',
        'sky lux' => 'skylux',
    ];
    return $known[$normalized] ?? slugify($brand);
}

function get_product_slug(array $product): string
{
    if (!empty($product['slug'])) {
        return slugify((string)$product['slug']);
    }
    if (!empty($product['sku'])) {
        return slugify((string)$product['sku']);
    }
    return slugify((string)($product['name'] ?? 'product'));
}

function product_url(array $product): string
{
    return '/product/' . get_product_slug($product);
}

function brand_url(string $brandSlug): string
{
    return '/brand/' . slugify($brandSlug);
}

function get_product_image(array $product): string
{
    $image = trim((string)($product['main_image_local'] ?? $product['main_image'] ?? ''));
    return $image !== '' ? $image : '/assets/images/placeholder-product.svg';
}

function split_list($value): array
{
    if (is_array($value)) {
        return array_values(array_filter(array_map('trim', $value)));
    }
    $value = str_replace(["\r\n", "\r", ';'], "\n", (string)$value);
    $parts = preg_split('/\n|,/', $value);
    return array_values(array_filter(array_map('trim', $parts)));
}

function parse_characteristics($value): array
{
    if (is_array($value)) {
        $rows = [];
        foreach ($value as $key => $val) {
            if (is_array($val)) {
                continue;
            }
            $rows[] = ['name' => (string)$key, 'value' => (string)$val];
        }
        return $rows;
    }

    $lines = split_list($value);
    $rows = [];
    foreach ($lines as $line) {
        $delimiter = strpos($line, ':') !== false ? ':' : (strpos($line, '-') !== false ? '-' : null);
        if ($delimiter) {
            [$name, $val] = array_map('trim', explode($delimiter, $line, 2));
            if ($name !== '' && $val !== '') {
                $rows[] = ['name' => $name, 'value' => $val];
            }
        }
    }
    return $rows;
}

function format_price($price): string
{
    $price = trim((string)$price);
    if ($price === '') {
        return '';
    }
    $numeric = preg_replace('/[^0-9.]/', '', str_replace(',', '.', $price));
    if ($numeric !== '' && is_numeric($numeric)) {
        return number_format((float)$numeric, 0, '.', ' ') . ' грн';
    }
    return $price;
}

function get_availability_class(string $availability): string
{
    $availabilityLower = mb_strtolower($availability, 'UTF-8');
    if (strpos($availabilityLower, 'наяв') !== false) {
        return 'is-available';
    }
    if (strpos($availabilityLower, 'замов') !== false) {
        return 'is-preorder';
    }
    return 'is-unavailable';
}

function find_product_by_slug(string $slug): ?array
{
    $slug = slugify($slug);
    foreach (get_visible_products() as $product) {
        if (get_product_slug($product) === $slug) {
            return $product;
        }
    }
    return null;
}

function page_title(string $title = ''): string
{
    $config = app_config();
    return $title ? $title . ' — ' . $config['site_name'] : $config['site_name'];
}

function is_active_path(string $path): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return rtrim($uri, '/') === rtrim($path, '/') ? ' is-active' : '';
}
