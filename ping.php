<?php
session_start();
$_SESSION['mt_rnd'] = mt_rand(1,9999);
$_SESSION['microtime'] = microtime(true);
echo 'pong';
?>