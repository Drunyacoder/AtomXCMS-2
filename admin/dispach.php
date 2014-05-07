<?php

##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      1.0                            ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    Admin Panel module             ##
## copyright     ©Andrey Brykin 2010-2014       ##
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

$Register = Register::getInstance();
$FpsDB = $Register['DB'];


$pageTitle = __('Admin Panel');
$pageNav = $pageTitle . __(' - General information');
$pageNavl = '';

if (empty($_GET['url'])) throw new Exception('Can\'t dispath dinamic URL. Page not found.');
list ($module, $action) = explode('/', $_GET['url']);
if (empty($module) || empty($action))  throw new Exception('Can\'t dispath dinamic URL. Page not found.');

$controller_path = $Register['ModManager']->getSettingsControllerPath($module);
$class_name = $Register['ModManager']->getSettingsControllerClassName($module);
if (!$Register['ModManager']->moduleExists($module)) throw new Exception("Module \"$module\" not found.");
if (!file_exists($controller_path)) throw new Exception("Dinamic pages controller for module \"$module\" not found.");

include_once $controller_path;
if (!class_exists($class_name)) throw new Exception("Dinamic pages controller for module \"$module\" not found.");

$controller = new $class_name;
if (!is_callable(array($controller, $action))) throw new Exception("Method \"$action\" not found in \"$module\" module.");

include_once ROOT . '/admin/template/header.php';
echo $controller->{$action}();
?>




<?php
include_once 'template/footer.php';
?>



