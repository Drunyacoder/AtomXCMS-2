<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      0.7                            ##
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

$Log = $Register['Log'];
$ACL = $Register['ACL'];


/* current page and cnt pages */
$log_files = glob(ROOT . '/sys/logs/' . $Log->logDir . '/*.dat');
$total_files = (!empty($log_files)) ? count($log_files) : 0;
list($pages, $page) = pagination($total_files, 1, '/admin/system_log.php?');



if (!empty($log_files)) {
	$filename = (strrchr($log_files[$page - 1], '/'));
	$filename = substr($filename, 1, strlen($filename));
	$data = $Log->read($filename);
}




$pageTitle = 'Лог действий';
$pageNav = $pageTitle;
$pageNavr = '';
include_once ROOT . '/admin/template/header.php';
?>





















<div class="list">
<div class="title"><div class="pages"><?php echo $pages ?></div></div>
<table class="grid" cellspacing="0" width="100%">
	<?php if(!empty($data)): ?>
	<th width="15%">Дата</th>
	<th width="30%">Действие</th>
	<th width="20%">Пользователь</th>
	<th width="10%">IP адрес</th>
	<th>Доп. информация</th>
	<?php foreach($data as $line): 
			//for coompare old version
			$color = '';
			if (!empty($line['user_status']) && is_numeric($line['user_status'])) {
				$group_info = $ACL->get_user_group($line['user_status']);
				if (!empty($group_info)) {
					if (!empty($group_info['color'])) $color = $group_info['color'];
					$line['user_status'] = '<span style="float:right;color:#' . $color . ';">' . h($group_info['title']) . '</span>';
				} else {
					$line['user_status'] = '<span style="float:right;color:#F14242;">*</span>';
				}
			} else {
				$line['user_status'] = '<span style="float:right;color:#F14242;">*</span>';
			}
			
			
	?>

	<tr>
		<td align="center"><span style="color:green;"><?php echo $line['date'] ?></span></td>
		<td><?php echo $line['action'] ?></td>
		<td><?php echo (!empty($line['user_id']) && !empty($line['user_name']) && !empty($line['user_status'])) ? 
				'(' . $line['user_id'] . ')' . h($line['user_name']) . '' 
				. $line['user_status'] . '' : 'Unknown'; ?></td>
		<td align="center"><?php echo $line['ip'] ?></td>
		<td><?php echo (!empty($line['comment'])) ? h($line['comment']) : '--'; ?></td>
	</tr>
	<?php
		endforeach;
	else:
	?>
	<tr><td>Записей нет</td></tr>
	<?php
	endif;
	?>
</table></div>
<?php
include_once 'template/footer.php';
?>