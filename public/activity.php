<?php
require __DIR__ . '/../includes/app.php';
$uid = member();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_post();
    $changed = match(field('action')) {
        'cancel_order' => run_query("UPDATE orders SET status='cancelled' WHERE id=? AND user_id=? AND status='placed'",[field('id'),$uid]),
        'cancel_booking' => run_query('DELETE FROM reservations WHERE id=? AND user_id=? AND slot_start>?',[field('id'),$uid,date('Y-m-d H:i:s')]),
        default => fail(422,'Unknown activity action.')
    };
    if (!$changed->rowCount()) fail(409,'This record cannot be cancelled.');
    go('activity.php','Cancellation confirmed.');
}
$orders = run_query('SELECT * FROM orders WHERE user_id=? ORDER BY id DESC',[$uid]);
$bookings = run_query('SELECT r.*,t.label FROM reservations r JOIN cafe_tables t ON t.id=r.table_id WHERE user_id=? ORDER BY slot_start DESC',[$uid])->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<h1>My activity</h1><h2>Pickup orders</h2>
<?php $rows=$orders->fetchAll(); if (!$rows): ?><p>No orders yet. <a href="menu.php">Browse drinks</a></p><?php endif ?>
<?php foreach ($rows as $o): ?><article><span class="status"><?= h(ucfirst($o['status'])) ?></span>
<h3>#<?= $o['id'] ?> · <?= h($o['item_name_snapshot']) ?></h3><p><?= $o['quantity'] ?> × <?= h($o['size'].' · '.$o['milk'].' milk · '.$o['syrup'].' syrup') ?> · <?= $o['total_kgs'] ?> KGS</p>
<?php if ($o['status']==='placed'): ?><form method="post"><?= token_field() ?><input type="hidden" name="id" value="<?= $o['id'] ?>"><button name="action" value="cancel_order">Cancel order</button></form><?php endif ?></article><?php endforeach ?>
<h2>Table reservations</h2><?php if (!$bookings): ?><p>No reservations. <a href="reserve.php">Find a table</a></p><?php endif ?>
<?php foreach ($bookings as $b): ?><article><h3><?= h($b['label']) ?></h3><p><?= h($b['slot_start']) ?> · <?= $b['party_size'] ?> guests</p>
<?php if ($b['slot_start']>date('Y-m-d H:i:s')): ?><form method="post"><?= token_field() ?><input type="hidden" name="id" value="<?= $b['id'] ?>"><button name="action" value="cancel_booking">Cancel reservation</button></form><?php endif ?></article><?php endforeach ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
