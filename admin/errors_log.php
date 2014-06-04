<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      0.9                            ##
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



/* current page and cnt pages */
$bytes_per_page = 50 * 1024;
$log_path = ROOT . '/sys/logs/php_errors.log';

if (!empty($_GET['del'])) {
	file_put_contents($log_path, '');
	$_SESSION['message'] = __('Operation is successful');
	redirect('/admin/errors_log.php');
}

$total = (!file_exists($log_path) || !is_readable($log_path)) 
	? 0
	: floor(filesize($log_path) / $bytes_per_page);
list($pages, $page) = pagination($total, 1, '/admin/errors_log.php?');


$offset = ($total > 1) ? ($total - ($page - 1)) * $bytes_per_page : 0;
$data = (file_exists($log_path) || is_readable($log_path))
	? file_get_contents($log_path, NULL, NULL, $offset, $bytes_per_page)
	: '';


$pageTitle = __('Action log');
$pageNav = $pageTitle;
$pageNavr = '';
include_once ROOT . '/admin/template/header.php';
?>




<div class="list">
	<div class="title"><div class="pages"><?php echo $pages ?></div></div>
	<div class="add-cat-butt" onClick="window.location.href='<?php echo get_url('/admin/errors_log.php?del=1') ?>'"><div class="add"></div><?php echo __('Clean log') ?></div>
	<table class="grid" cellspacing="0" style="width:100%;">
		<?php if(!empty($data)): ?>
		<tr>
			<td>
				<div style="height:800px; overflow-y:auto; ">
					<?php echo nl2br(h($data)) ?>
				</div>
			</td>
		</tr>
		<?php else: ?>
		<tr><td>Записей нет</td></tr>
		<?php endif; ?>
	</table>
</div>



<?php
include_once 'template/footer.php';
?>