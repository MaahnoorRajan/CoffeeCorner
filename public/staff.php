<?php
require __DIR__ . '/../includes/app.php';
member(true);
$next = ['placed'=>'preparing','preparing'=>'ready','ready'=>'collected'];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_post();
    $from=field('from');
    if (!isset($next[$from])) fail(422,'Invalid order transition.');
    if (!run_query('UPDATE orders SET status=? WHERE id=? AND status=?',[$next[$from],field('id'),$from])->rowCount())
        fail(409,'Order changed. Refresh the staff desk.');
    go('staff.php','Order status updated.');
}
$status=$_GET['status'] ?? 'active';
if (!in_array($status,['active',...array_keys($next),'collected','cancelled'],true)) fail(422,'Unknown status.');
$sql='SELECT o.*,u.name FROM orders o JOIN users u ON u.id=o.user_id ';
$orders=$status==='active'?run_query($sql."WHERE status IN ('placed','preparing','ready') ORDER BY o.id")->fetchAll():
    run_query($sql.'WHERE status=? ORDER BY o.id DESC',[$status])->fetchAll();
$day=$_GET['day'] ?? date('Y-m-d');
if (!is_string($day) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D',$day) || !checkdate((int)substr($day,5,2),(int)substr($day,8,2),(int)substr($day,0,4))) fail(422,'Invalid booking date.');
$bookings=run_query('SELECT u.name,t.label,r.slot_start,r.party_size FROM reservations r JOIN users u ON u.id=r.user_id JOIN cafe_tables t ON t.id=r.table_id WHERE DATE(slot_start)=? ORDER BY slot_start,t.label',[$day])->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<h1>Staff desk</h1><a href="manage-menu.php">Manage café menu</a><h2>Pickup queue</h2>
<form method="get" class="filters"><label>Show orders<select name="status"><?php foreach (['active','placed','preparing','ready','collected','cancelled'] as $s): ?><option <?= $s===$status?'selected':'' ?>><?= $s ?></option><?php endforeach ?></select></label><label>Reservation date<input name="day" type="date" value="<?= h($day) ?>"></label><button>Apply</button></form>
<?php if (!$orders): ?><p>No matching orders.</p><?php endif ?>
<?php foreach ($orders as $o): ?><article><span class="status"><?= h($o['status']) ?></span><h3>#<?= $o['id'] ?> · <?= h($o['name']) ?></h3>
<p><?= $o['quantity'] ?> × <?= h($o['item_name_snapshot'].' · '.$o['size'].' · '.$o['milk'].' milk · '.$o['syrup'].' syrup') ?> · <?= $o['total_kgs'] ?> KGS</p>
<?php if(isset($next[$o['status']])): ?><form method="post"><?= token_field() ?><input type="hidden" name="id" value="<?= $o['id'] ?>"><input type="hidden" name="from" value="<?= h($o['status']) ?>"><button>Mark <?= h($next[$o['status']]) ?></button></form><?php endif ?></article><?php endforeach ?>
<h2>Tables for <?= h($day) ?></h2><?php if (!$bookings): ?><p>No bookings for this day.</p><?php endif ?>
<?php foreach ($bookings as $b): ?><article><?= h($b['slot_start'].' · '.$b['label'].' · '.$b['name']) ?> · <?= $b['party_size'] ?> guests</article><?php endforeach ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
