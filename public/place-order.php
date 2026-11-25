<?php
require __DIR__ . '/../includes/app.php';
$uid = member();
verify_post();
$key = field('key');
if (!preg_match('/^[0-9a-f]{64}$/D',$key)) fail(422,'Invalid order reference.');
if (run_query('SELECT id FROM orders WHERE request_key=? AND user_id=?',[$key,$uid])->fetchColumn()) {
    go('activity.php','This order has already been placed.');
}
$draft = $_SESSION['draft'] ?? null;
if (!$draft || !hash_equals($draft['key'],$key)) fail(409,'Preview this drink again before confirming.');
db()->beginTransaction();
$item = run_query('SELECT * FROM menu_items WHERE id=? FOR UPDATE',[$draft['id']])->fetch();
if (!$item || !$item['available'] || (int)$item['base_price_kgs'] !== $draft['base']) {
    db()->rollBack();
    fail(409,'The menu has changed. Return to Menu for an updated price.');
}
$price = quote((int)$item['base_price_kgs'],$draft);
run_query('INSERT INTO orders(user_id,item_id,item_name_snapshot,size,milk,syrup,quantity,unit_price_kgs,total_kgs,request_key) VALUES(?,?,?,?,?,?,?,?,?,?)',
    [$uid,$item['id'],$item['name'],$draft['size'],$draft['milk'],$draft['syrup'],$price['quantity'],$price['unit'],$price['total'],$key]);
db()->commit();
unset($_SESSION['draft']);
go('activity.php','Order placed. Pay at the counter when you collect it.');
