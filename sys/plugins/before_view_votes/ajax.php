<?php
error_reporting(E_ALL);
include_once dirname(__FILE__) . '/index.php';
$obj = new Votes;
$title = (!empty($_POST['title'])) ? $_POST['title'] : '';


if (isset($_POST['type']) && $_POST['type'] == 'sendvote') {
	$num = (!empty($_POST['ans'])) ? $_POST['ans'] : '';
	if (empty($num) || empty($title)) die('error');
	$res = $obj->saveVote($title, $num);
	
	echo ($res === false) ? 'Ошибка' : 'Все ОК';
	
	
} else {
	if (empty($title)) die('error');
	$res = $obj->showResult($title);
	
	
	echo ($res === false) ? 'Ошибка' : $res;
}
