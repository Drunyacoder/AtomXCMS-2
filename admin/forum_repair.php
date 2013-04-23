<?php
/*-----------------------------------------------\
| 												 |
| @Author:       Andrey Brykin (Drunya)          |
| @Email:        drunyacoder@gmail.com           |
| @Site:         http://fapos.net                |
| @Version:      1.0                             |
| @Project:      CMS                             |
| @package       CMS Fapos                       |
| @subpackege    Admin Panel module  			 |
| @copyright     ©Andrey Brykin 2010-2013        |
\-----------------------------------------------*/

/*-----------------------------------------------\
| 												 |
|  any partial or not partial extension          |
|  CMS Fapos,without the consent of the          |
|  author, is illegal                            |
|------------------------------------------------|
|  Любое распространение                         |
|  CMS Fapos или ее частей,                      |
|  без согласия автора, является не законным     |
\-----------------------------------------------*/


/** 
* repair forums, themes, messages count 
*
*/
include_once '../sys/boot.php';
include_once ROOT . '/admin/inc/adm_boot.php';




$forums = $FpsDB->select('forums', DB_ALL, array());
if (!empty($forums)) {
	foreach ($forums as $forum) {
		$themes = $FpsDB->select('themes', DB_ALL, array('cond' => array('id_forum' => $forum['id'])));
		if (!empty($themes)) {
			foreach ($themes as $theme) {
				$FpsDB->query("UPDATE `" . $FpsDB->getFullTableName('themes') . "` SET 
								`posts` = (SELECT COUNT(*) FROM `" . $FpsDB->getFullTableName('posts') . "` WHERE `id_theme` = '" . $theme['id'] . "')
								WHERE `id` = '" . $theme['id'] . "'");
			}
		}
		$FpsDB->query("UPDATE `" . $FpsDB->getFullTableName('forums') . "` SET 
						`themes` = (SELECT COUNT(*) FROM `" . $FpsDB->getFullTableName('themes') . "` WHERE `id_forum` = '" . $forum['id'] . "')
						, `posts` = (SELECT SUM(posts) FROM `" . $FpsDB->getFullTableName('themes') . "` WHERE `id_forum` = '" . $forum['id'] . "')
						WHERE `id` = '" . $forum['id'] . "'");
	}
}



$pageTitle = __('Recalculation forum');
$pageNav = $pageTitle;
$pageNavr = '';

include_once ROOT . '/admin/template/header.php';
?>


<div class="info-str"><?php echo __('All done') ?></div>


<?php
include_once 'template/footer.php';
