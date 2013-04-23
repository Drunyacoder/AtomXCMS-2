<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      0.8                            ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    Admin Panel module             ##
## copyright     ©Andrey Brykin 2010-2011       ##
##################################################


##################################################
##												##
## any partial or not partial extension         ##
## CMS Fapos,without the consent of the         ##
## author, is illegal                           ##
##################################################
## Любое распространение                        ##
## CMS Fapos или ее частей,                     ##
## без согласия автора, является не законным    ##
##################################################

include_once '../sys/boot.php';
include_once ROOT . '/admin/inc/adm_boot.php';

 
$pageTitle = 'Баны по IP адресам';
 
 
if ( !isset( $_GET['ac'] ) ) $_GET['ac'] = 'index';
$actions = array( 'index',
					'del',
					'add');
					
if ( !in_array( $_GET['ac'], $actions ) ) $_GET['ac'] = 'index';

switch ( $_GET['ac'] )
{
	case 'index':  // главная страница 
		$content = index($pageTitle);
		break;
	case 'add':         //смотреть новость
		$content = add();
		break;
	case 'del':         //добавить новость
		$content = delete();
		break;
	default:
		$content = index($pageTitle);
}



$pageNav = $pageTitle;
$pageNavl = '';
include_once ROOT . '/admin/template/header.php';
?>


<?php echo $content; ?>

<?php

include_once ROOT . '/admin/template/footer.php';

	
function index(&$page_title) {
	$content = null;
	if (file_exists(ROOT . '/sys/logs/ip_ban/baned.dat')) {
		$data = file(ROOT . '/sys/logs/ip_ban/baned.dat');
		if (!empty($data)) {
			foreach($data as $key => $row) {
				$content .= '<tr><td>' . $row . '</td><td width="30px"><a class="delete" onClick="return confirm(\'Are you sure?\');" href="ip_ban.php?ac=del&id=' . $key . '"></a></td></tr>';
			}
		}
	}
	
	if (empty($content)) $content = '<div class="list">
		<div class="title">Баны IP адресов</div>
		<div class="add-cat-butt" onClick="openPopup(\'addBan\');"><div class="add"></div>' . __('Add') . '
		</div><table style="width:100%;" cellspacing="0" class="grid"><tr><td colspan="2">Записей пока нет</td></tr></table></div>';
	else $content = '<div class="list">
		<div class="title">Баны IP адресов</div>
		<div class="add-cat-butt" onClick="openPopup(\'addBan\');"><div class="add"></div>' . __('Add') . '
		</div><table cellspacing="0" style="width:100%;" class="grid">' . $content . '</table></div>';
	
	//add form
	$content .= '<div id="addBan" class="popup">
			<div class="top">
				<div class="title">Добавление категории</div>
				<div onClick="closePopup(\'addBan\');" class="close"></div>
			</div>
			<form action="ip_ban.php?ac=add" method="POST">
			<div class="items">
				<div class="item">
					<div class="left">
						IP:
					</div>
					<div class="right"><input type="text" name="ip" /></div>
					<div class="clear"></div>
				</div>
				
				<div class="item submit">
					<div class="left"></div>
					<div class="right" style="float:left;">
						<input type="submit" value="Сохранить" name="send" class="save-button" />
					</div>
					<div class="clear"></div>
				</div>
			</div>
			</form>
		</div>';
	

	
	if (isset($_SESSION['add']['errors'])) {
		$content = $_SESSION['add']['errors'] . $content;
		unset($_SESSION['add']);
	}
	
	return $content;
}



/**
* adding IP to ban list
*/
function add() {
	if (empty($_POST['ip'])) redirect('/admin/ip_ban.php');
	$ip = trim($_POST['ip']);
	$error = null;
	
	
	if (!preg_match('#^\d{1,3}\.\d{1,3}.\d{1,3}.\d{1,3}$#', $ip)) $error = '<li>Не верный формат IP адреса</li>';
	if (!empty($error)) {
		$_SESSION['add']['errors'] = '<ul class="uz_err">' . $error . '</ul>';
		redirect('/admin/ip_ban.php');
	}
	
	if (empty($error)) {
		touchDir(ROOT . '/sys/logs/ip_ban/');
		$f = fopen(ROOT . '/sys/logs/ip_ban/baned.dat', 'a+');
		fwrite($f, $ip . "\n");
		fclose($f);
	}
	
	redirect('/admin/ip_ban.php');
}



/**
* deleting ip
*/
function delete() {
	if (!isset($_GET['id'])) redirect('ip_ban.php');
	if (file_exists(ROOT . '/sys/logs/ip_ban/baned.dat')) {
		$data = file(ROOT . '/sys/logs/ip_ban/baned.dat');
		if (!empty($data)) {

			if (array_key_exists($_GET['id'], $data)) {
				$_data = array();
				foreach ($data as $key => $val) {
					if (empty($val) || $key == $_GET['id']) continue;
					$_data[$key] = $val;
				}
				$data = implode("", $_data);
				file_put_contents(ROOT . '/sys/logs/ip_ban/baned.dat', $data);
			} else {
				$_SESSION['add']['errors'] = '<ul class="error"><li>Записи с таким ключом не найдено</li></ul>';
			}
		}
	}
	
	redirect('/admin/ip_ban.php');
}

?>