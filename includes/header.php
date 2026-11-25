<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Coffee Corner</title><link rel="stylesheet" href="style.css"></head><body>
<header><nav aria-label="Main navigation"><a class="brand" href="index.php">Coffee Corner<span>.</span></a>
<a href="menu.php">Menu</a><a href="reserve.php">Reserve a table</a>
<?php if (isset($_SESSION['uid'])): ?><a href="activity.php">My activity</a>
<?php if ($_SESSION['role']==='staff'): ?><a href="staff.php">Staff desk</a><?php endif ?>
<form action="logout.php" method="post"><?= token_field() ?><button class="quiet">Sign out</button></form>
<?php else: ?><a class="quiet" href="login.php">Sign in</a><?php endif ?></nav></header><main>
<?php if (!empty($_SESSION['notice'])): ?><p class="notice" role="status"><?= h($_SESSION['notice']) ?></p><?php unset($_SESSION['notice']); endif ?>
