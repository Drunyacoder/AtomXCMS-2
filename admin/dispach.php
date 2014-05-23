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
$pageNavr = '';

if (empty($_GET['url'])) throw new Exception('Can\'t dispath dinamic URL. Page not found.');
$url_params = explode('/', $_GET['url']);
$module = array_shift($url_params);
$action = array_shift($url_params);
if (empty($module) || empty($action))  throw new Exception('Can\'t dispath dinamic URL. Page not found.');

$controller_path = $Register['ModManager']->getSettingsControllerPath($module);
$class_name = $Register['ModManager']->getSettingsControllerClassName($module);
if (!$Register['ModManager']->moduleExists($module)) throw new Exception("Module \"$module\" not found.");
if (!file_exists($controller_path)) throw new Exception("Dinamic pages controller for module \"$module\" not found.");

include_once $controller_path;
if (!class_exists($class_name)) throw new Exception("Dinamic pages controller for module \"$module\" not found.");

$controller = new $class_name;
$controller->pageTitle = &$pageTitle;
$controller->pageNav = &$pageNav;
$controller->pageNavr = &$pageNavr;
if (!is_callable(array($controller, $action))) throw new Exception("Method \"$action\" not found in \"$module\" module.");


$content = call_user_func_array(array($controller, $action), $url_params);
include_once ROOT . '/admin/template/header.php';
echo $content;

include_once 'template/footer.php';