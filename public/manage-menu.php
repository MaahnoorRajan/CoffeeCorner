<?php
require __DIR__ . '/../includes/app.php';
member(true);
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_post();
    $id=field('id');
    $action=field('action');
    try {
        if ($action==='delete') {
            run_query('DELETE FROM menu_items WHERE id=?',[$id]);
        } else {
            $name=field('name'); $desc=field('description'); $cat=field('category');
            $price=filter_var(field('price'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>65535]]);
            if (!$price || $name==='' || strlen($name)>80 || $desc==='' || strlen($desc)>255 || !in_array($cat,['Coffee','Tea'],true)) fail(422,'Check the name, description, category and whole-som price.');
            $values=[$name,$desc,$cat,$price,isset($_POST['available'])?1:0];
            if ($action==='create') run_query('INSERT INTO menu_items(name,description,category,base_price_kgs,available) VALUES(?,?,?,?,?)',$values);
            elseif ($action==='update') run_query('UPDATE menu_items SET name=?,description=?,category=?,base_price_kgs=?,available=? WHERE id=?',[...$values,$id]);
            else fail(422,'Unknown menu action.');
        }
    } catch (PDOException $e) {
        if (($e->errorInfo[1]??0)===1451) fail(409,'This item has order history. Edit it and turn off availability.');
        if (($e->errorInfo[1]??0)===1062) fail(409,'This drink name already exists.');
        throw $e;
    }
    go('manage-menu.php','Menu updated.');
}
$item=['id'=>'','name'=>'','description'=>'','category'=>'Coffee','base_price_kgs'=>'','available'=>1];
if (isset($_GET['edit'])) {
    $id=filter_var($_GET['edit'],FILTER_VALIDATE_INT);
    $item=run_query('SELECT * FROM menu_items WHERE id=?',[$id?:0])->fetch() ?: fail(404,'Drink not found.');
}
$all=run_query('SELECT * FROM menu_items ORDER BY name');
require __DIR__ . '/../includes/header.php';
?>
<h1>Manage menu</h1><h2><?= $item['id']?'Edit drink':'Add drink' ?></h2>
<form method="post"><?= token_field() ?><input type="hidden" name="id" value="<?= $item['id'] ?>">
<label>Name<input name="name" required maxlength="80" value="<?= h($item['name']) ?>"></label>
<label>Description<input name="description" required maxlength="255" value="<?= h($item['description']) ?>"></label>
<label>Category<select name="category"><?php foreach(['Coffee','Tea'] as $c): ?><option <?= $c===$item['category']?'selected':'' ?>><?= $c ?></option><?php endforeach ?></select></label>
<label>Base price (KGS)<input name="price" type="number" min="1" max="65535" required value="<?= $item['base_price_kgs'] ?>"></label>
<label><input type="checkbox" name="available" <?= $item['available']?'checked':'' ?>> Available to order</label>
<button name="action" value="<?= $item['id']?'update':'create' ?>">Save drink</button></form>
<h2>Catalogue</h2><?php foreach($all as $row): ?><article><h3><?= h($row['name']) ?></h3><p><?= $row['base_price_kgs'] ?> KGS · <?= $row['available']?'Available':'Hidden' ?></p>
<a href="?edit=<?= $row['id'] ?>">Edit drink</a><form method="post"><?= token_field() ?><input type="hidden" name="id" value="<?= $row['id'] ?>"><button name="action" value="delete">Delete drink</button></form></article><?php endforeach ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
