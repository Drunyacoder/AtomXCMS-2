<?php
/*-----------------------------------------------\
| 												 |
|  Author:       Andrey Brykin (Drunya)          |
|  Version:      1.5.2                           |
|  Project:      CMS                             |
|  package       CMS AtomX                       |
|  subpackege    Admin Panel module              |
|  copyright     ©Andrey Brykin 2010-2012        |
|  Last mod.     2012/07/08                      |
\-----------------------------------------------*/

/*-----------------------------------------------\
| 												 |
|  any partial or not partial extension          |
|  CMS AtomX,without the consent of the          |
|  author, is illegal                            |
|------------------------------------------------|
|  Любое распространение                         |
|  CMS AtomX или ее частей,                      |
|  без согласия автора, является не законным     |
\-----------------------------------------------*/



include_once '../sys/boot.php';
include_once ROOT . '/admin/inc/adm_boot.php';

 
$pageTitle = __('Plugins');
 
 
if ( !isset( $_GET['ac'] ) ) $_GET['ac'] = 'index';
$actions = array('index', 'off', 'on', 'edit');
					
if ( !in_array( $_GET['ac'], $actions ) ) $_GET['ac'] = 'index';


switch ( $_GET['ac'] )
{
	case 'index':  // главная страница 
		$content = index($pageTitle);
		break;
	case 'edit':        
		$content = editPlugin($pageTitle);
		break;
	case 'off':         
		$content = offPlugin();
		break;
	case 'on':         
		$content = onPlugin();
		break;
	default:
		$content = index($pageTitle);
}



$pageNav = $pageTitle;
$pageNavr = '<a href="plugins.php">' . __('Plugins list') . '</a>';



$dp = new Document_Parser;

$content = $content;
include_once ROOT . '/admin/template/header.php';
echo $content;
include_once ROOT . '/admin/template/footer.php';

	
	
	
function index(&$page_title) {
	global $FpsDB, $pageTitle;
	$content = '';

	
	$plugs = glob(ROOT . '/sys/plugins/*');
	if (!empty($plugs) && count($plugs) > 0) {
		foreach ($plugs as $k => $pl) {
			if (!is_dir($pl)) unset($plugs[$k]);
		}
	}
	if (count($plugs) < 1) 
		return '<div class="list"><div class="head">
				<div class="title">' . __('Not found available plugins') . '</div></div></div>';
	
	

	
	
	$content .= "<div class=\"list\">
		<div class='title'>" . $pageTitle . "</div>
		<div class='level1'>
			<div class='head'>
				<div class='title'>" . __('Title') . "</div>
				<div class='title' style='width:220%;'>" . __('Path')  . "</div>
				<div class='title'>" . __('Description') . "</div>
				<div class='title' style='width:30%;'>HOOK</div>
				<div class='title'>" . __('Directory') . "</div>
				<div class='title'>" . __('Action') .  "</div>
			</div>
			<div class='items'>";
	

	
	foreach ($plugs as $result) {
		$dir = strrchr($result, '/');
		$dir = trim($dir, '/');
		
		if (file_exists($result . '/config.dat')) {
			$params = json_decode(file_get_contents($result . '/config.dat'), true);
		}
		if (empty($params)) $params = array();
		
		$name = (!empty($params['title'])) ? h($params['title']) : 'Unknown';
		$descr = (!empty($params['description'])) ? h($params['description']) : '';
		
		$hooks = array('before_view');
		foreach ($hooks as $h) {
			if (mb_substr($dir, 0, mb_strlen($h)) === $h) {
				$hook = $h;
				break;
			}
		}
		if (empty($hook)) $hook = 'Unknown';
		
		
		$selce = '';
		$content .= "
				<div class='level2'>
					<div class='title2' style='width:10%;'>
						<a href='plugins.php?ac=edit&dir={$dir}'>{$name}</a>
					</div>	
					<div class='title2' style='width:30%;'>
						{$result}
					</div>
					<div class='title2' style='width:15%;'>
						{$descr}
					</div>
					<div class='title2'><span class='unknown' style=\"color:#\">{$hook}</span></div>
					<div class='title2' style='width:12%;'>{$dir}</div>
					<div class='title2-buttons'>
						<a class=\"edit\" href='plugins.php?ac=edit&dir={$dir}'></a>&nbsp;";
						
						if (!empty($params['active'])) {
							$selce = "<a class=\"off\" href='plugins.php?ac=off&dir={$dir}'></a>";
						} else {
							$selce = "<a class=\"on\" href='plugins.php?ac=on&dir={$dir}'></a>";
						}
						$content .= $selce;
		
		$content .= "</div>
				</div>";
	}
	$content .= '</div></div></div>';

	
	return $content;
  
}




function editPlugin(&$page_title) {
	global $FpsDB;
	if (empty($_GET['dir'])) redirect('/admin/plugins.php');
	$dir = $_GET['dir'];
	if (!preg_match('#^[\w\d_-]+$#i', $dir)) redirect('/admin/plugins.php');
	
	
	$settigs_file_path = ROOT . '/sys/plugins/' . $dir . '/settings.php';
	if (!file_exists($settigs_file_path)) 
		return '<div class="list"><div class="title"></div><div class="level1"><div class="head">
				<div class="title">No settings for this plugin</div></div></div></div>';
	
	
	include_once $settigs_file_path;
	$page_title = 'Настройка Плагина';
	return (!empty($output)) ? $output : '';
}




function onPlugin() {
		if (empty($_GET['dir'])) redirect('/');
		$dir = $_GET['dir'];
	
		$pach = ROOT . '/sys/plugins/' . $dir;
		$conf_pach = $pach . '/config.dat';
		$history = (file_exists($conf_pach)) ? json_decode(file_get_contents($conf_pach), true) : array();
		
		$history['active'] = 1;
		file_put_contents($conf_pach, json_encode($history));
		redirect('../admin/plugins.php');
}



function offPlugin() {
		if (empty($_GET['dir'])) redirect('/');
		$dir = $_GET['dir'];
	
		$pach = ROOT . '/sys/plugins/' . $dir;
		$conf_pach = $pach . '/config.dat';
		$history = (file_exists($conf_pach)) ? json_decode(file_get_contents($conf_pach), true) : array();
		
		$history['active'] = 0;
		file_put_contents($conf_pach, json_encode($history));
		redirect('../admin/plugins.php');
}

