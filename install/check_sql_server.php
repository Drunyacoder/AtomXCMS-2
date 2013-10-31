<?php
@ini_set('display_errors', 0);


if (empty($_GET['type'])) $_GET['type'] = true;

if ('base' === $_GET['type']) {
	if (@mysql_connect($_GET['host'], $_GET['user'], $_GET['pass']) !== false
	&& @mysql_select_db($_GET['base']) !== false) {
		echo '<span style="color:#46B100">База найдена</span>';
	} else {
		echo '<span style="color:#FF0000">Не удалось найти базу</span>';
	}
} else {
	if (false === @mysql_connect($_GET['host'], $_GET['user'], $_GET['pass'])) {
		echo '<span style="color:#FF0000">Не удалось подключиться</span>';
	} else {
		echo '<span style="color:#46B100">Подключились</span>';
	}
}

?>