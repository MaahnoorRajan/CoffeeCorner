<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Bishkek');
session_start(['use_strict_mode' => 1, 'cookie_httponly' => true,
    'cookie_samesite' => 'Lax', 'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

function token_field(): string {
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}

function verify_post(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        fail(405, 'Submit the form to continue.');
    }
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        fail(403, 'Refresh the page and try again.');
    }
}

function run_query(string $sql, array $values = []): PDOStatement {
    $stmt = db()->prepare($sql);
    $stmt->execute($values);
    return $stmt;
}

function go(string $page, string $notice = ''): never {
    if ($notice !== '') $_SESSION['notice'] = $notice;
    header('Location: ' . $page, true, 303);
    exit;
}

function member(bool $staff = false): int {
    if (empty($_SESSION['uid'])) go('login.php');
    if ($staff && run_query('SELECT role FROM users WHERE id=?', [$_SESSION['uid']])->fetchColumn() !== 'staff') {
        fail(403, 'This page is for café staff.');
    }
    return (int)$_SESSION['uid'];
}

function field(string $name): string {
    return is_string($_POST[$name] ?? null) ? trim($_POST[$name]) : '';
}
