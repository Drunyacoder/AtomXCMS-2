<?php
@ini_set('display_errors', 0);
if (empty($_GET['type'])) $_GET['type'] = true;

if ($_GET['type'] === true) {
	checkRequest();
} else {
	checkUpdate();
}


function checkUpdate() {
	@$b = file_get_contents('http://home.develdo.com/cdn/versions.txt');
	if ($b) {
		if (preg_match('#[^></]+#i', $b)) {
			echo '<a href="http://home.develdo.com/downloads.php">Последняя версия ' . trim($b) . '</a>';
		} else {
			echo 'Не удалось узнать';
		}
	} else {
		echo 'Не удалось узнать';
	}
}

function checkRequest() {
	@$b = file_get_contents('http://home.develdo.com/check.php?v=2.1RC7&d=' . $_SERVER['HTTP_HOST']);
}
?>