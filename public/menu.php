<?php
require __DIR__ . '/../includes/app.php';
$q = $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';
if (!is_string($q) || strlen($q) > 160 || !in_array($category, ['', 'Coffee', 'Tea'], true)) {
    fail(400, 'Check your search term and category.');
}
$sql = 'SELECT id, name, description, base_price_kgs FROM menu_items WHERE available = 1 AND LOCATE(?, name) > 0';
$params = [trim($q)];
if ($category !== '') {
    $sql .= ' AND category = ?';
    $params[] = $category;
}
$stmt = db()->prepare($sql . ' ORDER BY name');
$stmt->execute($params);
$items = $stmt->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<h1>Menu</h1>
<form method="get"><label>Drink name <input name="q" value="<?= h($q) ?>"></label>
<label>Category <select name="category">
<?php foreach (['' => 'All', 'Coffee' => 'Coffee', 'Tea' => 'Tea'] as $value => $label): ?>
<option value="<?= h($value) ?>" <?= $category === $value ? 'selected' : '' ?>><?= h($label) ?></option>
<?php endforeach ?></select></label><button>Search</button></form>
<?php if (!$items): ?><p>No drinks match. <a href="menu.php">Clear filters</a></p><?php endif ?>
<?php foreach ($items as $item): ?><article><h2><a href="drink.php?id=<?= (int)$item['id'] ?>"><?= h($item['name']) ?></a></h2><p><?= h($item['description']) ?></p><p>From <?= (int)$item['base_price_kgs'] ?> KGS</p></article><?php endforeach ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
