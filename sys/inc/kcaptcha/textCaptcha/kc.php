<?php
include( 'AtmCaptcha.class.php' );


session_start();
$key = !empty($_GET['name']) ? trim($_GET['name']) : 'captcha_keystring';

$obj = new AtmCaptcha;
$keystring = $obj->getContent();
$_SESSION[$key] = $keystring;