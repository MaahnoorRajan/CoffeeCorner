<?php
require __DIR__ . '/../includes/app.php';
$item = drink($_GET['id'] ?? null);
require __DIR__ . '/../includes/header.php';
?>
<h1><?= h($item['name']) ?></h1><p><?= h($item['description']) ?> Base price: <?= (int)$item['base_price_kgs'] ?> KGS.</p>
<form action="review.php" method="post">
<input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
<?php foreach (OPTIONS as $field => $values): ?>
<label><?= ucfirst($field) ?><select name="<?= $field ?>">
<?php foreach ($values as $key => $extra): ?>
<option value="<?= $key ?>"><?= ucfirst($key) ?> (+<?= $extra ?> KGS)</option>
<?php endforeach ?></select></label>
<?php endforeach ?>
<label>Quantity <input type="number" name="quantity" min="1" max="5" required value="1"></label>
<button>Preview price</button></form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
