<?php
session_start();
define ('ROOT', dirname(dirname(__FILE__)));


if (isset($_POST['send'])) {
	include_once ROOT . '/sys/inc/config.class.php';
	new Config(ROOT . '/sys/settings/config.php');


	if (empty($_POST['host'])) $errors['db_host'] = 'Не заполненно поле "Хост базы данных"';
	if (empty($_POST['base'])) $errors['db_name'] = 'Не заполненно поле "Имя базы данных"';
	if (empty($_POST['user'])) $errors['db_user'] = 'Не заполненно поле "Логин"';
	
	if (empty($_POST['adm_login'])) $errors['adm_login'] = 'Не заполненно поле "Логин"';
	if (empty($_POST['adm_pass'])) $errors['adm_pass'] = 'Не заполненно поле "Пароль"';
	
	@$db = mysql_connect($_POST['host'], $_POST['user'], $_POST['pass']);
	if (!$db) $errors['connect'] = 'Не удалось подключиться к базе. Проверте настройки!';
	@$db = mysql_select_db($_POST['base'], $db);
	if (!$db) $errors['select'] = 'Не удалось найти базу. Проверте имя базы!';
	
	if (!empty($_POST['prefix']) && !preg_match('#^[a-z_]*$#i', $_POST['prefix'])) $errors['db_prefix'] = 'Не допустимые символы в поле "Префикс"';
	
	if (empty($errors)) {
		$settings = Config::read('all');
		$settings['db'] = array();
		$settings['db']['host']   = $_POST['host'];
		$settings['db']['name']   = $_POST['base'];
		$settings['db']['user']   = $_POST['user'];
		$settings['db']['pass']   = $_POST['pass'];
		$settings['db']['prefix'] = $_POST['prefix'];

		Config::write($settings);

		
		$_SESSION['adm_name'] = $_POST['adm_login'];
		$_SESSION['adm_pass'] = $_POST['adm_pass'];
		header ('Location: step2.php '); die();
	}	
}

?><!doctype html>
<html>
<head>
<title>Fapos CMS - вместе в будущее</title>
<meta content="text/html; charset=utf-8" http-equiv="content-type">
<link rel="shortcut icon" href="../sys/img/favicon.ico" type="image/x-icon">
<link type="text/css" rel="StyleSheet" href="css/style.css" />
<script language="JavaScript" type="text/javascript" src="../sys/js/jquery.js"></script>
</head>
<body>
<div id="head">
	<div id="logo"></div>
	<ul id="progressbar">
		<li class="progressbar-li"><img src="img/loader-progressbar.gif" style="display:none;" id="ajaxLoader3" /></li>
	</ul>
</div>
<div id="subhead"></div>


<div id="container2">
	
	<div id="newv"></div>
	
	<h3>Права на папки(с учетом вложенных файлов)</h3>
	<img src="img/ajax_loader.gif" style="display:none;" id="ajaxLoader" />
	<div id="checkAccess"></div>
	<br />
	<h3>Настройки сервера и PHP</h3>
	<img src="img/ajax_loader.gif" style="display:none;" id="ajaxLoader2" />
	<div id="checkServer"></div>
	<br />
	
	<h3>Настройки подключения к базе</h3>
	Введите пожалуйста, данные доступа к базе данных. Поля "Логин" и "Пароль" - это поля для логина и пароля пользователя базы данных.<br />
	На пример, на локальном хосте это, почти всегда, логин - ROOT и пароль пустой.<br /><br />

	<form method="post" action="">
		<table align="left">
			<tr>
				<td>Хост Сервера SQL<td>
				<td><div class="inp"><input id="sqlhostInp" type="text" name="host" value="localhost" onChange="checkSQServer(this.value, $('#sqluserInp').val(), $('#sqlpassInp').val());" /><span id="SQLserver">*</span></div><td>
			</tr>
			<tr>
				<td>База Данных<td>
				<td><div class="inp"><input id="sqlbaseInp" type="text" name="base" value="fapos" onChange="checkSQBase(this.value, $('#sqlhostInp').val(), $('#sqluserInp').val(), $('#sqlpassInp').val());" /><span id="SQLbase">*</span></div><td>
			</tr>
			<tr>
				<td>Пользователь Базы<td>
				<td><div class="inp"><input id="sqluserInp" type="text" name="user" value="root" onChange="checkSQServer($('#sqlhostInp').val(), this.value, $('#sqlpassInp').val());" /><span id="SQLuser">*</span></div><td>
			</tr>
			<tr>
				<td>Пароль Пользователя<td>
				<td><div class="inp"><input id="sqlpassInp" type="text" name="pass" value=""  onChange='checkSQServer($("#sqlhostInp").val(), $("#sqluserInp").val(), this.value);' /><span id="SQLpass">&nbsp;</span></div><td>
			</tr>
			<tr>
				<td>Префикс таблиц<td>
				<td><div class="inp"><input id="sqlprefInp" type="text" name="prefix" value=""  onChange="checkSQPrefix(this.value);" /><span id="SQLpref">&nbsp;</span></div><td>
			</tr>
			<tr>
				<td>&nbsp;<td>
				<td>&nbsp;<td>
			</tr>
			<tr>
				<td>&nbsp;<td>
				<td>&nbsp;<td>
			</tr>
			<tr>
				<td>Логин Администратора<td>
				<td><div class="inp"><input type="text" name="adm_login" value="" /><span>*</span></div><td>
			</tr>
			<tr>
				<td>Пароль Администратора<td>
				<td><div class="inp"><input type="text" name="adm_pass" value="" /><span>*</span></div><td>
			</tr>
		
		</table>
		<input class="btn" type="submit" name="send" value="" />
	</form> 
	<br />


</div>

<div id="footer2"></div>
<script type="text/javascript">
function checkSQPrefix(val) {
	if (/^[a-z_]*$/.test(val)) {
		$('#SQLpref').html('&nbsp;');
	} else {
		$('#SQLpref').html('<span style="color:#FF0000">Запрещенные символы</span>');
	}
}
function checkSQBase(base, host, user, pass) {
	$.get('check_sql_server.php?base='+base+'&type=base&host='+host+'&user='+user+'&pass='+pass, function(data){
		$('#SQLbase').html(data);
	});
}
function checkSQServer(host, user, pass) {
	$.get('check_sql_server.php?host='+host+'&user='+user+'&pass='+pass, function(data){
		$('#SQLserver').html(data);
		$('#SQLuser').html(data);
		$('#SQLpass').html(data);
	});
}
function checkAccess() {
	$('#ajaxLoader').show();
	$.get('check_access.php', function(data){
		$('#ajaxLoader').hide();
		$('#checkAccess').html(data);
	});
}
function checkServer() {
	$('#ajaxLoader2').show();
	$.get('check_server.php', function(data){
		$('#ajaxLoader2').hide();
		$('#checkServer').html(data);
	});
}
function progressBar() {
	$('#ajaxLoader3').show();
	$.get('progressbar.php', function(data){
		$('#ajaxLoader3').hide();
		$('#progressbar').html(data);
	});
}
function checkUpdate() {
	$.get('ping.php?type=v', function(data) {
		$('#newv').html(data);
	});
}
checkAccess();
checkServer();
progressBar();
checkUpdate();

<?php if (!empty($errors)): ?>
checkSQServer($('# id="sqlhostInp"').value, 'host');
checkSQServer($('# id="sqlusetInp"').value, 'user');
checkSQServer($('# id="sqlpassInp"').value, 'pass');
checkSQBase($('# id="sqlbaseInp"').value);
<?php endif; ?>
</script>
</body>
</html>