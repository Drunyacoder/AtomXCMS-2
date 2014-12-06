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
	global $FpsDB;
	$page_title = __('Plugins list');
	$content = '';

	
	$plugs = glob(ROOT . '/sys/plugins/*');
	if (!empty($plugs) && count($plugs) > 0) {
		foreach ($plugs as $k => $pl) {
			if (!is_dir($pl)) unset($plugs[$k]);
		}
	}
	if (count($plugs) < 1) return '<div class="list"><table cellspacing="0" class="grid"><tr><td>' . __('Not found available plugins') . '</td></tr></table></div>';
	
	

	
	
	$content .= "<div class=\"list\"><table cellspacing=\"0\" class=\"grid\">
		<th width=\"\">" . __('Title') . "</th>
		<th width=\"25%\">" . __('Path') . "</th>
		<th width=\"20%\">" . __('Description') . "</th>
		<th width=\"\">HOOK</th>
		<th width=\"15%\">" . __('Directory') . "</th>
		<th width=\"30px\" colspan=\"2\">" . __('Action') . "</th>";
	

	
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
		
	
	
		$content .= "<tr><td><div class=\"plugin_path\"><a href='plugins.php?ac=edit&dir={$dir}'>{$name}</a></div></td>
			<td><div class=\"plugin_path\">{$result}</div></td>
			<td><div class=\"plugin_path\">{$descr}</div></td>
			<td><span style=\"color:#\">{$hook}</span></td>
			<td>{$dir}</td>
			<td colspan=\"2\">
				<a class=\"edit\" href='plugins.php?ac=edit&dir={$dir}'></a>&nbsp;";
			
			if (!empty($params['active'])) {
				$content .= "<a class=\"off\" href='plugins.php?ac=off&dir={$dir}'></a>
				</td>";
			} else {
				$content .= "<a class=\"on\" href='plugins.php?ac=on&dir={$dir}'></a>
				</td>";

			}
	}
	$content .= '</table></div>';

	
	return $content;
  
}




function editPlugin(&$page_title) {
	global $FpsDB;
	if (empty($_GET['dir'])) redirect('/admin/plugins.php');
	$dir = $_GET['dir'];
	if (!preg_match('#^[\w\d_-]+$#i', $dir)) redirect('/admin/plugins.php');
	
	
	$settigs_file_path = ROOT . '/sys/plugins/' . $dir . '/settings.php';
	if (!file_exists($settigs_file_path)) return '<h2>No settings for this plugin</h2>';
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