<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Email:        drunyacoder@gmail.com         |
| @Site:         http://atomx.net              |
| @Version:      2.0.1                         |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @Subpackege    Define library                |
| @Copyright     ©Andrey Brykin                |
| @Last mod      2017/10/13                    |
|----------------------------------------------|
|											   |
| any partial or not partial extension         |
| CMS AtomX,without the consent of the         |
| author, is illegal                           |
|----------------------------------------------|
| Любое распространение                        |
| CMS AtomX или ее частей,                     |
| без согласия автора, является не законным    |
\---------------------------------------------*/

session_start();
if (isset($_SESSION['db_querys'])) unset($_SESSION['db_querys']);


/**
 * Current version of engine
 */
define('FPS_VERSION', '2.7.2 Beta');

/**
 * Path constants
 */
define('ROOT', dirname(dirname(__FILE__)));
define ('DS', DIRECTORY_SEPARATOR);
define ('R', dirname(dirname(__FILE__)) . DS);


/**
 * If we uses AtomX from subdir or subdirs
 * we must set this variable, because AtomX
 * must know this for good work.
 */
$diff = array_diff_assoc(
	explode(DS, dirname(dirname(__FILE__))),
	explode('/', $_SERVER['DOCUMENT_ROOT'])
);
define ('WWW_ROOT', ((!empty($diff)) ? '/' . implode('/', $diff) : ''));

/**
 * If set to 1, check referer in admin panel
 * and if he dont match current host redirect to
 * index page of admin panel. It doesn't allow to
 * send inquiries from other hosts.
 */
define ('ADM_REFER_PROTECTED', 0);




/**
 * whether the system is installed
 */
function isInstall() {
	return !file_exists(ROOT . '/install');
}




/**
 * Include some core part (Application)
 */
include_once ROOT . '/sys/settings/config.php';


/**
 * Autoload
 */
include_once ROOT . '/sys/inc/autoload.class.php';
Autoload::loadFuncs();
spl_autoload_register(array('Autoload', 'load'));



/**
 * Registry
 */
$Register = Register::getInstance();
$Register['Config'] = new Config(ROOT . '/sys/settings/config.php');



include_once ROOT . '/sys/inc/helpers.lib.php';


/**
 *
 */
new Bootstrap;




include_once ROOT . '/sys/fnc/geshi/geshi.php';




