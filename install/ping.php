<?php
@ini_set('display_errors', 0);
if (empty($_GET['type'])) $_GET['type'] = true;

if ($_GET['type'] === true) {
	checkRequest();
} else {
	checkUpdate();
}


function checkUpdate() {
	@$b = file_get_contents('http://home.atomx.net/cdn/versions.txt');
	if ($b) {
		if (preg_match('#[^></]+#i', $b)) {
			echo '<a target="_blank" href="https://github.com/Drunyacoder/AtomXCMS-2/releases">Последняя версия ' . trim($b) . '</a>';
		} else {
			echo 'Не удалось узнать';
		}
	} else {
		echo 'Не удалось узнать';
	}
}

function checkRequest() {
	@$b = file_get_contents('http://home.atomx.net/check.php?v=2.7.0Beta&d=' . $_SERVER['HTTP_HOST']);
}
?>