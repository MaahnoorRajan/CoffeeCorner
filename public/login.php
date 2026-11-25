<?php
require __DIR__ . '/../includes/app.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_post();
    $user = run_query('SELECT id,role,password_hash FROM users WHERE email=?',[field('email')])->fetch();
    if ($user && is_string($_POST['password'] ?? null) && password_verify($_POST['password'],$user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION = ['uid'=>(int)$user['id'], 'role'=>$user['role'], 'csrf'=>bin2hex(random_bytes(32))];
        go($user['role'] === 'staff' ? 'staff.php' : 'activity.php');
    }
    $error = 'Email or password is incorrect.';
}
require __DIR__ . '/../includes/header.php';
?>
<h1>Sign in</h1><?php if ($error): ?><p role="alert"><?= h($error) ?></p><?php endif ?>
<form method="post"><?= token_field() ?><label>Email<input name="email" type="email" required autocomplete="email"></label>
<label>Password<input name="password" type="password" required autocomplete="current-password"></label>
<button>Sign in</button></form><p>New here? <a href="register.php">Create an account</a></p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
