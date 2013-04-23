<?php
session_start();
if (isset($_SESSION['db_querys'])) unset($_SESSION['db_querys']);


/**
 * Current version of engine
 */
define('FPS_VERSION', '2.1 RC7');

/**
 * Path constants
 */
define('ROOT', dirname(dirname(__FILE__)));
define ('DS', DIRECTORY_SEPARATOR);
define ('R', dirname(dirname(__FILE__)) . DS);


/**
 * If we uses Fapos from subdir or subdirs
 * we must set this variable, because Fapos
 * must know this for good work.
 */
define ('WWW_ROOT', '');
/**
 * If set to 1, check referer in admin panel
 * and if he dont match current host redirect to
 * index page of admin panel. It doesn't allow to
 * send inquiries from other hosts.
 */
define ('ADM_REFER_PROTECTED', 0);






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




