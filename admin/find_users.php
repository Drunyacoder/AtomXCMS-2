<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      1.1                            ##
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


if (empty($_GET['name'])) die('');
$name = trim($_GET['name']);

$usersModel = $Register['ModManager']->getModelInstance('users');
$users = $usersModel->getCollection(array(
	"name LIKE '$name%'"
), array(
	'limit' => 100,
));

if (!empty($users)) {
	foreach ($users as  &$user) $user = $user->asArray();
	
	die(json_encode($users));
}
die('');