<?php
require __DIR__ . '/../includes/app.php';
verify_post();
$_SESSION = [];
session_destroy();
go('index.php');
