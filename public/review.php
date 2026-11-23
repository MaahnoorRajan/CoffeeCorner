<?php
require __DIR__ . '/../includes/app.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    fail(405, 'Use the drink form to preview a price.');
}
$item = drink($_POST['id'] ?? null);
try {
    $price = quote((int)$item['base_price_kgs'], $_POST);
} catch (InvalidArgumentException $error) {
    fail(422, $error->getMessage());
}
require __DIR__ . '/../includes/header.php';
?>
<h1>Your drink preview</h1>
<p><?= h($item['name']) ?> · <?= h($_POST['size']) ?> · <?= h($_POST['milk']) ?> milk · syrup: <?= h($_POST['syrup']) ?></p>
<p><?= $price['quantity'] ?> × <?= $price['unit'] ?> KGS</p>
<p><strong>Total: <?= $price['total'] ?> KGS</strong></p>
<p>Price preview only. No order has been placed.</p>
<a href="drink.php?id=<?= (int)$item['id'] ?>">Configure another drink</a>
<?php require __DIR__ . '/../includes/footer.php'; ?>
