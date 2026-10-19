<?php
require_once __DIR__ . '/auth.php';
start_secure_session();
forget_remember_token();
destroy_session();
header("Location: login.php");
exit;
?>