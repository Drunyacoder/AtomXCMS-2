<?php/*-----------------------------------------------\| 												 || @Author:       Andrey Brykin (Drunya)          || @Email:        drunyacoder@gmail.com           || @Site:         http://fapos.net                || @Version:      1.2                             || @Project:      CMS                             || @package       CMS Fapos                       || @subpackege    Admin Panel module              || @copyright     ©Andrey Brykin 2010-2013        |\-----------------------------------------------*//*-----------------------------------------------\| 												 ||  any partial or not partial extension          ||  CMS Fapos,without the consent of the          ||  author, is illegal                            ||------------------------------------------------||  Любое распространение                         ||  CMS Fapos или ее частей,                      ||  без согласия автора, является не законным     |\-----------------------------------------------*/include_once '../sys/boot.php';include_once ROOT . '/admin/inc/adm_boot.php';$pageTitle = '';$pageNav = '';$pageNavl = '';include_once ROOT . '/admin/template/header.php';if (isset($_SESSION['backup_dis'])) {	unset($_SESSION['backup_dis']);	header( 'Refresh: ' . Config::read('redirect_delay') . '; url=' . get_url('/admin/') );	echo $head . '<div class="warning ok" style="">' . __('Backup complete') . '</div>';} else {		$template_standart  = glob(ROOT . '/template/' . Config::read('template') . '/css/*.stand');	$template_standart  = array_merge(glob(ROOT . '/template/' . Config::read('template') . '/html/*/*.stand'), $template_standart);	if (is_array($template_standart)) {		foreach ($template_standart as $file) {			unlink($file);		}	}	$_SESSION['backup_dis'] = 1;	redirect('/admin/backup_dis.php');}include_once 'template/footer.php'; ?>