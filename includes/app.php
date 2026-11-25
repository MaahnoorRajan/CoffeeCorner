<?php
declare(strict_types=1);

function db(): PDO {
    static $db;
    if (!$db) {
        $config = require __DIR__ . '/../config/database.example.php';
        $local = __DIR__ . '/../config/database.local.php';
        if (is_file($local)) {
            $config = array_replace($config, require $local);
        }
        $db = new PDO($config['dsn'], $config['user'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $db;
}

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function fail(int $code, string $message): never {
    http_response_code($code);
    require __DIR__ . '/header.php';
    echo '<h1>Request could not be completed</h1><p>' . h($message) . '</p>';
    require __DIR__ . '/footer.php';
    exit;
}

set_exception_handler(function (Throwable $error): void {
    error_log($error->getMessage());
    fail(500, 'The café service is unavailable. Please try again later.');
});

function drink(mixed $id): array {
    if (!is_string($id) || !ctype_digit($id) || (int)$id < 1) {
        fail(400, 'Choose an item from the menu.');
    }
    $query = db()->prepare('SELECT * FROM menu_items WHERE id = ? AND available = 1');
    $query->execute([$id]);
    $item = $query->fetch();
    if (!$item) {
        fail(404, 'This drink is unavailable.');
    }
    return $item;
}

const OPTIONS = [
    'size' => ['small' => 0, 'medium' => 20, 'large' => 40],
    'milk' => ['regular' => 0, 'oat' => 25, 'almond' => 25],
    'syrup' => ['none' => 0, 'vanilla' => 10, 'caramel' => 10],
];

function quote(int $basePrice, array $input): array {
    $unit = $basePrice;
    foreach (OPTIONS as $field => $prices) {
        $choice = $input[$field] ?? null;
        if (!is_string($choice) || !array_key_exists($choice, $prices)) {
            throw new InvalidArgumentException("Choose a valid $field.");
        }
        $unit += $prices[$choice];
    }
    $quantity = filter_var($input['quantity'] ?? null, FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 5]]);
    if ($quantity === false) {
        throw new InvalidArgumentException('Quantity must be a whole number from 1 to 5.');
    }
    return ['unit' => $unit, 'quantity' => $quantity, 'total' => $unit * $quantity];
}

require_once __DIR__ . '/session.php';
