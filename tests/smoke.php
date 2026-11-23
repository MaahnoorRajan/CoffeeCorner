<?php
declare(strict_types=1);
// Run against a local development server with the original sample data.
$base = rtrim($argv[1] ?? 'http://127.0.0.1:8000', '/');
function checkPage(string $path, int $status, string $contains, ?array $form = null): void {
    global $base;
    $http = ['method' => 'GET', 'ignore_errors' => true, 'timeout' => 5];
    if ($form !== null) {
        $http += ['header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => http_build_query($form)];
        $http['method'] = 'POST';
    }
    $body = file_get_contents($base . '/' . $path, false, stream_context_create(['http' => $http]));
    if (!str_contains($http_response_header[0] ?? '', " $status ") || $body === false || !str_contains($body, $contains)) {
        throw new RuntimeException("Failed: $path (expected $status and $contains)");
    }
    echo "PASS $path ($status)\n";
}
checkPage('index.php', 200, 'Browse drinks');
checkPage('menu.php?q=Latte', 200, 'Latte</a>');
checkPage('menu.php?q=Latte&category=Tea', 200, 'No drinks match');
checkPage('menu.php?q=Mocha', 200, 'No drinks match');
checkPage('drink.php?id=1', 200, 'Preview price');
checkPage('drink.php?id=4', 404, 'unavailable');
checkPage('review.php', 405, 'Use the drink form');
$order = ['id'=>'1', 'quantity'=>'2', 'size'=>'medium', 'milk'=>'oat', 'syrup'=>'vanilla', 'total'=>'1'];
checkPage('review.php', 200, 'Total: 490 KGS', $order);
checkPage('review.php', 422, 'whole number', array_replace($order,['quantity'=>'6']));
checkPage('review.php', 422, 'valid milk', array_replace($order,['milk'=>'unknown']));
