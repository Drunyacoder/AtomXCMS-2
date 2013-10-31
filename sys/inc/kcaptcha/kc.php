<?php
include( 'kcaptcha.php' );


$name = (!empty($_GET['name'])) ? trim($_GET['name']) : 'captcha_keystring';


session_start();
$captcha = new KCAPTCHA();
$_SESSION[$name] = $captcha->getKeyString();

/**
* SESSION may be work incorrect
*/
$tmp_files = glob('../../logs/captcha_keystring_*.dat');
foreach ($tmp_files as $tmp_file) {
	if (!preg_match('#'. date("Y-m-d") . '\.dat$#', $tmp_file)) @unlink($tmp_file);
}
@$f = fopen('../../logs/captcha_keystring_' . session_id() . '-' . date("Y-m-d") . '.dat', 'w');
@fwrite($f, $captcha->getKeyString());
@fclose($f);
?>