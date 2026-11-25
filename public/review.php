<?php
require __DIR__ . '/../includes/app.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: POST'); fail(405,'Use the drink form to preview a price.'); }
$item = drink($_POST['id'] ?? null);
try { $price = quote((int)$item['base_price_kgs'],$_POST); }
catch (InvalidArgumentException $e) { fail(422,$e->getMessage()); }
if (isset($_SESSION['uid'])) {
    verify_post();
    $_SESSION['draft'] = ['key'=>bin2hex(random_bytes(32)), 'id'=>$item['id'], 'base'=>(int)$item['base_price_kgs'],
        'size'=>field('size'),'milk'=>field('milk'),'syrup'=>field('syrup'),'quantity'=>$price['quantity']];
}
require __DIR__ . '/../includes/header.php';
?>
<h1>Review your drink</h1><article><h2><?= h($item['name']) ?></h2>
<p><?= h(field('size')) ?> · <?= h(field('milk')) ?> milk · <?= h(field('syrup')) ?> syrup</p>
<p><?= $price['quantity'] ?> × <?= $price['unit'] ?> KGS</p><strong>Total: <?= $price['total'] ?> KGS</strong></article>
<?php if (isset($_SESSION['uid'])): ?><form action="place-order.php" method="post"><?= token_field() ?>
<input type="hidden" name="key" value="<?= $_SESSION['draft']['key'] ?>"><button>Confirm pickup order</button></form>
<?php else: ?><p><a href="login.php">Sign in</a> to configure and confirm a pickup order.</p><?php endif ?>
<a href="drink.php?id=<?= (int)$item['id'] ?>">Change options</a>
<?php require __DIR__ . '/../includes/footer.php'; ?>
