<?php
require __DIR__ . '/../includes/app.php';
$uid = member();
$post = $_SERVER['REQUEST_METHOD'] === 'POST';
if ($post) verify_post();
$input = $post ? $_POST : $_GET;
$date = $input['date'] ?? date('Y-m-d', strtotime('tomorrow'));
$hour = $input['hour'] ?? '09';
$party = filter_var($input['party'] ?? 2,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>6]]);
$hours = array_map(fn($n)=>sprintf('%02d',$n),range(9,17));
if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D',$date) || !in_array($hour,$hours,true) || !$party) {
    fail(422,'Choose a date, an opening hour and a party of 1–6.');
}
$slot = $date . ' ' . $hour . ':00:00';
$time = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',$slot);
if (!$time || $time->format('Y-m-d H:i:s') !== $slot || $time <= new DateTimeImmutable()) fail(422,'Choose a future date and hour.');
if ($post) {
    $table = run_query('SELECT id FROM cafe_tables WHERE id=? AND seats>=?', [field('table_id'),$party])->fetchColumn();
    if (!$table) fail(422,'Choose a table that seats your party.');
    try {
        run_query('INSERT INTO reservations(user_id,table_id,slot_start,party_size) VALUES(?,?,?,?)',[$uid,$table,$slot,$party]);
        go('activity.php','Table reserved for one hour.');
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? 0) !== 1062) throw $e;
        $notice='That table was just booked. Choose another table or hour.';
        http_response_code(409);
    }
}
$tables = run_query('SELECT id,label,seats FROM cafe_tables t WHERE seats>=? AND NOT EXISTS
    (SELECT 1 FROM reservations r WHERE r.table_id=t.id AND r.slot_start=?) ORDER BY seats,id',[$party,$slot])->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<h1>Reserve a table</h1><p>One-hour sittings, 09:00–18:00 local time.</p>
<form method="get" class="filters"><label>Date<input type="date" name="date" required min="<?= date('Y-m-d') ?>" value="<?= h($date) ?>"></label>
<label>Time<select name="hour"><?php foreach ($hours as $opt): ?><option value="<?= $opt ?>" <?= $opt === $hour?'selected':'' ?>><?= $opt ?>:00</option><?php endforeach ?></select></label>
<label>Guests<input type="number" name="party" min="1" max="6" required value="<?= $party ?>"></label><button>Find tables</button></form>
<h2>Available tables</h2><?php if (isset($notice)): ?><p role="alert"><?= h($notice) ?></p><?php endif ?>
<?php if (!$tables): ?><p>No tables available. Try another hour or date.</p><?php endif ?>
<div class="grid"><?php foreach ($tables as $t): ?><article><h3><?= h($t['label']) ?></h3><p>Seats <?= $t['seats'] ?> · <?= h($date) ?> at <?= $hour ?>:00</p>
<form method="post"><?= token_field() ?><input type="hidden" name="table_id" value="<?= $t['id'] ?>"><input type="hidden" name="date" value="<?= h($date) ?>">
<input type="hidden" name="hour" value="<?= $hour ?>"><input type="hidden" name="party" value="<?= $party ?>"><button>Reserve table</button></form></article><?php endforeach ?></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
