<?php
require __DIR__ . '/../includes/app.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_post();
    $name = field('name');
    $email = field('email');
    $pass = $_POST['password'] ?? '';
    if ($name === '' || strlen($name) > 80 || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || !is_string($pass) || strlen($pass) < 8 || strlen($pass) > 72 || str_contains($pass, chr(0))) {
        $error = 'Enter a name, valid email and a password of 8–72 bytes.';
    } else {
        try {
            run_query('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)', [$name,$email,password_hash($pass,PASSWORD_DEFAULT)]);
            go('login.php','Account created. Sign in to continue.');
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) !== 1062) throw $e;
            $error = 'That email is already registered.';
        }
    }
}
require __DIR__ . '/../includes/header.php';
?>
<h1>Create an account</h1><p>Keep your pickup orders and table reservations together.</p>
<?php if ($error): ?><p role="alert"><?= h($error) ?></p><?php endif ?>
<form method="post"><?= token_field() ?>
<label>Name<input name="name" required maxlength="80" autocomplete="name" value="<?= h(field('name')) ?>"></label>
<label>Email<input name="email" type="email" required autocomplete="email" value="<?= h(field('email')) ?>"></label>
<label>Password<input name="password" type="password" minlength="8" required autocomplete="new-password"></label>
<button>Create account</button></form><p>Already registered? <a href="login.php">Sign in</a></p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
