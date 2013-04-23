<?php
/*-----------------------------------------------\
| 												 |
| @Author:       Andrey Brykin (Drunya)          |
| @Email:        drunyacoder@gmail.com           |
| @Site:         http://fapos.net                |
| @Version:      1.4                             |
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


include_once '../sys/boot.php';
include_once ROOT . '/admin/inc/adm_boot.php';




$pageTitle = __('Forum');


// For all popup's(edit & add). Their must be in main wrapper
$popups_content = '';



if (!isset($_GET['ac'])) $_GET['ac'] = 'index';
$permis = array('add', 'del', 'index', 'edit', 'acl');
if (!in_array($_GET['ac'], $permis)) $_GET['ac'] = 'index';

switch($_GET['ac']) {
	case 'index':
		$content = index($pageTitle);
		break;
	case 'del':
		$content = delete();
		break;
	case 'add':
		$content = add();
		break;
	case 'edit':
		$content = edit();
		break;
	case 'acl':
		$content = acl();
		break; 
	default:
		$content = index();
}


$pageNav = $pageTitle;
$pageNavr = '';

include_once ROOT . '/admin/template/header.php';
 ?>


<div class="warning">
<?php echo __('If you delete a category, all the materials in it will be removed') ?><br /><br />

<?php echo __('Each forum should be inherited from the section') ?>

<?php
if (!empty($_SESSION['addErrors'])) {
	echo '<ul class="error" style="list-style-type:none;color:red;">' . $_SESSION['addErrors'] . '</ul>';
	unset($_SESSION['addErrors']);
}
?>
</div>

<?php
echo $popups_content;
echo $content;



function index(&$page_title) {
	global $FpsDB, $popups_content;
	deleteCollisions();

	$page_title = __('Forum - sections editor');
	
	$query = $FpsDB->select('forum_cat', DB_ALL, array('order' => 'previev_id'));
	
	//cats and position selectors for ADD
	if (count($query) > 0) {
		$cat_selector = '<select name="in_cat" id="cat_secId">';	
		foreach ($query as $key => $result) {
			$cat_selector .= '<option value="' . $result['id'] . '">' . h($result['title']) . '</option>';
		}
		$cat_selector .= '</select>';
	} else {
		$cat_selector = '<b>' . __('First, create a section') . '</b>';
	}
	
	$forums = $FpsDB->select('forums', DB_ALL);

	
	//selector for subforums
	$sub_selector = '<select name="parent_forum_id">';
	$sub_selector .= '<option value=""></option>';
	if (!empty($forums)) {
		foreach($forums as $forum) {
			$sub_selector .= '<option value="' . $forum['id'] . '">' . h($forum['title']) . '</option>';
		}
	}
	$sub_selector .= '</select>';
	
	
	$html = '';


		
	$popups_content .=	'<div id="sec" class="popup">
			<div class="top">
				<div class="title">Добавление категории</div>
				<div onClick="closePopup(\'sec\');" class="close"></div>
			</div>
			<form action="forum_cat.php?ac=add" method="POST">
			<div class="items">
				<div class="item">
					<div class="left">
						' . __('Title') . ':
					</div>
					<div class="right">
						<input type="hidden" name="type" value="section" />
						<input type="text" name="title" />
					</div>
					<div class="clear"></div>
				</div>
				<div class="item">
					<div class="left">
						' . __('Section position') . ':
						<span class="comment">' . __('Numeric') . '</span>
					</div>
					<div class="right">
						<input type="text" name="in_pos" />
					</div>
					<div class="clear"></div>
				</div>
				<div class="item submit">
					<div class="left"></div>
					<div class="right" style="float:left;">
						<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
					</div>
					<div class="clear"></div>
				</div>
			</div>
			</form>
		</div>';
	
	
	
	
	$html .= '<div class="list">
		<div class="title">Управление форумами</div>
		<div class="add-cat-butt" onClick="openPopup(\'sec\');"><div class="add"></div>' . __('Add section') . '</div>';



	
	/*
	$html .= '<td align="right">
				<div align="right" class="topButtonL" id="cat_view"><input type="button" name="add" value="' . __('Create forum') . '" onClick="wiOpen(\'cat\');" /></div></td></tr></table>';
	*/
		
		
	if (count($query) > 0) {
		foreach ($query as $result) {

			$html .= '<div class="level1">
				<div class="head">
					<div class="title">' . h($result['title']) . '</div>
					<div class="buttons">
						<a title="Add" href="javascript://" onClick="openPopup(\'addForum' . $result['id'] . '\');" class="add"></a>
						<a title="Edit" href="javascript://" onClick="openPopup(\'editSec' . $result['id'] . '\');" class="edit"></a>
						<a title="Delete" href="?ac=del&id=' . $result['id'] . '&section" onClick="return _confirm();" class="delete"></a>
					</div>
					<div class="clear"></div>
				</div>
				<div class="items">';
		
		
			// Select current section
			$cat_selector_ = str_replace('selected="selected"', ' ', $cat_selector);
			$cat_selector_ = str_replace(
				'value="' . $result['id'] .'"', 
				' selected="selected" value="' . $result['id'] .'"', 
				$cat_selector_
			);
		
		
			$popups_content .= '<div id="addForum' . $result['id'] . '" class="popup">
					<div class="top">
						<div class="title">Добавление категории</div>
						<div onClick="closePopup(\'addForum' . $result['id'] . '\');" class="close"></div>
					</div>
					<form action="forum_cat.php?ac=add" method="POST" enctype="multipart/form-data">
					<div class="items">
						<div class="item">
							<div class="left">
								' . __('Parent section') . ':
							</div>
							<div class="right">' . $cat_selector_ . '</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Title of forum') . ':
							</div>
							<div class="right">
								<input type="hidden" name="type" value="forum" />
								<input type="text" name="title" />
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Forum position') . ':
								<span class="comment">' . __('Numeric') . '</span>
							</div>
							<div class="right">
								<input type="text" name="in_pos" />
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Parent forum') . ':
								<span class="comment">' . __('For which this will be sub-forum') . '</span>
							</div>
							<div class="right">
								' . $sub_selector . '
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Icon') . ':
								<span class="comment">(' . __('Empty field - no icon') . ')<br />
								' . __('The desired size 16x16 px') . '</span>
							</div>
							<div class="right">
								<input type="file" name="icon" />
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Description') . ':
							</div>
							<div class="right">
								<textarea name="description" /></textarea>
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Lock on passwd') . ':
							</div>
							<div class="right">
								<input type="text" name="lock_passwd"/>
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Lock on posts count') . ':
							</div>
							<div class="right">
								<input type="text" name="lock_posts"/>
							</div>
							<div class="clear"></div>
						</div>
						<div class="item submit">
							<div class="left"></div>
							<div class="right" style="float:left;">
								<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
							</div>
							<div class="clear"></div>
						</div>
					</div>
					</form>
				</div>';
				
				
				
				
			$popups_content .= '<div id="editSec' . $result['id'] . '" class="popup">
					<div class="top">
						<div class="title">Добавление категории</div>
						<div onClick="closePopup(\'editSec' . $result['id'] . '\');" class="close"></div>
					</div>
					<form action="forum_cat.php?ac=edit&id=' . $result['id'] . '" method="POST">
					<div class="items">
						<div class="item">
							<div class="left">
								' . __('Title') . ':
							</div>
							<div class="right">
								<input type="hidden" name="type" value="section" />
								<input type="text" name="title" value="' . $result['title'] . '" />
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Section position') . ':
								<span class="comment">' . __('Numeric') . '</span>
							</div>
							<div class="right">
								<input type="text" name="in_pos" value="' . $result['previev_id'] . '" />
							</div>
							<div class="clear"></div>
						</div>
						<div class="item submit">
							<div class="left"></div>
							<div class="right" style="float:left;">
								<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
							</div>
							<div class="clear"></div>
						</div>
					</div>
					</form>
				</div>';
			/* END EDIT SECTION FORM */
			

			
			$queryCat = $FpsDB->query("
				SELECT a.*, COUNT(b.`id`) as cnt FROM `" . $FpsDB->getFullTableName('forums') . "` a 
				LEFT JOIN `" . $FpsDB->getFullTableName('themes') . "` b ON b.`id_forum` = a.`id` 
				WHERE a.`in_cat` = '" . $result['id'] . "' GROUP BY a.`id` ORDER BY a.`pos`");
			
			if (count($queryCat) > 0) {
				foreach ($queryCat as $cat) {
				
				
				
			
					$Register = Register::getInstance();
					$acl_groups = $Register['ACL']->get_group_info();
					$acl_rules = $Register['ACL']->getRules();
					$forum_rules = $Register['ACL']->getForumRules();
					$forum_rules = (!empty($forum_rules[$cat['id']])) ? $forum_rules[$cat['id']] : array();
					
					
	
					$gr_selector = '<select>';
					foreach ($acl_groups as $gr_id => $group) {
						if ($gr_id === 0) continue;
					
						$gr_selector .= '<option onClick="selectAclTab(\'' . $gr_id . '\')" value="' 
							. $gr_id . '">' . $group['title'] . '</option>';
					}
					$gr_selector .= '</select>';
					
					
					
					/* ACl FORUM FORM */	
					$popups_content .= '<div id="acl' . $cat['id'] . '" class="popup">
						<div class="top">
							<div class="title">Добавление категории</div>
							<div onClick="closePopup(\'acl' . $cat['id'] . '\');" class="close"></div>
						</div>
						<form action="forum_cat.php?ac=acl&id=' . $cat['id'] . '" method="POST" enctype="multipart/form-data">
						<div class="items">
						<div class="item"><div class="left">Просматривать темы в форуме могут:</div>' .
						'<div class="right">' . $gr_selector . '</div>' .
						'<div class="clear"></div></div>';
						
					
					
					
					// Create checkboxses block for each user group
					$f = true;
					foreach ($acl_groups as $gr_id => $group) {
					
						$style = ($f) ? '' : 'style="display: none;"';
						if ($f) $f = false;
						
						
						$checked = array();
						foreach (array('grn', 'grm', 'grsm') as $ch_val) {
							$checked[$ch_val] = (!empty($forum_rules[$gr_id]) && $forum_rules[$gr_id] === $ch_val) 
							? ' checked="checked"' : '';
						}
						
						
						
						$popups_content .= '<div class="acl-perms-collection" id="aclset' . $gr_id . '" ' . $style . '>
							<div class="item"><div class="left">
								Без особых прав:
								<span class="comment">Действуют только права, выставленные в правах групп.</span>
							</div>' .
							'<div class="right">
								<input type="radio" name="access_for_' . $gr_id . '"' . $checked['grn'] 
								. ' value="grn" id="grn' . $gr_id . $cat['id'] . '" />
								<label for="grn' . $gr_id . $cat['id'] . '"></label>
							</div>' .
							'<div class="clear"></div></div>
							
							<div class="item"><div class="left">
								Модератор:
								<span class="comment">Редактирование, закрытие, открытие тем.</span>
							</div>' .
							'<div class="right">
								<input type="radio" name="access_for_' . $gr_id . '"' . $checked['grm'] 
								. ' value="grm" id="grm' . $gr_id . $cat['id'] . '" />
								<label for="grm' . $gr_id . $cat['id'] . '"></label>
							</div>' .
							'<div class="clear"></div></div>
							
							<div class="item"><div class="left">
								Супер модератор:
								<span class="comment">Редактирование, удаление, закрытие, открытие и перенос тем.</span>
							</div>' .
							'<div class="right">
								<input type="radio" name="access_for_' . $gr_id . '"' . $checked['grsm'] 
								. ' value="grsm" id="grsm' . $gr_id . $cat['id'] . '" />
								<label for="grsm' . $gr_id . $cat['id'] . '"></label>
							</div>' .
							'<div class="clear"></div></div>
							</div>';
					}
						
							
					$popups_content .= '<div class="item submit">
								<div class="left"></div>
								<div class="right" style="float:left;">
									<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
								</div>
								<div class="clear"></div>
							</div>
						</div>
						</form>
					</div>';
					/* END ACL FORUM FORM */
					
					

					
					

					
					//cat selector and position selector for EDIT FRORUMS
					$cat_selector = '<select name="in_cat" id="cat_secId">';	
					foreach ($query as $key => $category) {
						if ($cat['in_cat'] == $category['id']) {
							$cat_selector .= '<option value="' . $category['id'] . '" selected="selected">' . $category['title'] . '</option>';
						} else {
							$cat_selector .= '<option value="' . $category['id'] . '">' . $category['title'] . '</option>';
						}
					}
					$cat_selector .= '</select>';

					
					
					//selector for subforums
					$sub_selector = '<select name="parent_forum_id">';
					$sub_selector .= '<option value=""></option>';
					if (!empty($forums)) {
						foreach($forums as $forum) {
							if ($cat['id'] == $forum['id']) continue; 
							$selected = ($cat['parent_forum_id'] == $forum['id']) ? 'selected="selected"' : ''; 
							$sub_selector .= '<option value="' . $forum['id'] . '" ' . $selected . '>' 
							. $forum['title'] . '</option>';
						}
					}
					$sub_selector .= '</select>';
					
					$issubforum = (!empty($cat['parent_forum_id'])) 
					? '&nbsp;<span style="color:#0373FE;">' . __('Under forum with ID') . ' ' . $cat['parent_forum_id'] . '</span>' : '';
					
					
					
					$html .= '<div class="level2">
								<div class="number">' . $cat['id'] . '</div>
								<div class="title">' . h($cat['title']) . ' ' . $issubforum . '</div>
								<div class="buttons">
									<a title="edit" href="javascript://" onClick="openPopup(\'editForum' . $cat['id'] . '\')" class="edit"></a>
									<a title="acl" href="javascript://" onClick="openPopup(\'acl' . $cat['id'] . '\');" class="acl"></a>
									<a title="delete" href="?ac=del&id=' . $cat['id'] . '" onClick="return _confirm();" class="delete"></a>
								</div>
								<div class="posts">' . $cat['cnt'] . '</div>
							</div>';

							
							
					/* EDIT FORUM FORM */	
					$popups_content .= '<div id="editForum' . $cat['id'] . '" class="popup">
							<div class="top">
								<div class="title">Добавление категории</div>
								<div onClick="closePopup(\'editForum' . $cat['id'] . '\');" class="close"></div>
							</div>
							<form action="forum_cat.php?ac=edit&id=' . $cat['id'] . '" method="POST" enctype="multipart/form-data">
							<div class="items">
								<div class="item">
									<div class="left">
										' . __('Parent section') . ':
									</div>
									<div class="right">' . $cat_selector_ . '</div>
									<div class="clear"></div>
								</div>
								<div class="item">
									<div class="left">
										' . __('Title of forum') . ':
									</div>
									<div class="right">
										<input type="hidden" name="type" value="forum" />
										<input type="text" name="title" value="' . $cat['title'] . '" />
									</div>
									<div class="clear"></div>
								</div>
								<div class="item">
									<div class="left">
										' . __('Forum position') . ':
										<span class="comment">' . __('Numeric') . '</span>
									</div>
									<div class="right">
										<input type="text" name="in_pos" value="' . $cat['pos'] . '" />
									</div>
									<div class="clear"></div>
								</div>
								<div class="item">
									<div class="left">
										' . __('Parent forum') . ':
										<span class="comment">' . __('For which this will be sub-forum') . '</span>
									</div>
									<div class="right">
										' . $sub_selector . '
									</div>
									<div class="clear"></div>
								</div>
								<div class="item">
									<div class="left">
										' . __('Icon') . ':
										<span class="comment">(' . __('Empty field - no icon') . ')<br />
										' . __('The desired size 16x16 px') . '</span>
									</div>
									<div class="right">
										<input type="file" name="icon" />
									</div>
									<div class="clear"></div>
								</div>
								<div class="item">
									<div class="left">
										' . __('Description') . ':
									</div>
									<div class="right">
										<textarea name="description" cols="30" rows="3" />' . $cat['description'] . '</textarea>
									</div>
									<div class="clear"></div>
								</div>
								<div class="item">
									<div class="left">
										' . __('Lock on passwd') . ':
									</div>
									<div class="right">
										<input type="text" name="lock_passwd" value="' . $cat['lock_passwd'] . '" />
									</div>
									<div class="clear"></div>
								</div>
								<div class="item">
									<div class="left">
										' . __('Lock on posts count') . ':
									</div>
									<div class="right">
										<input type="text" name="lock_posts" value="' . $cat['lock_posts'] . '" />
									</div>
									<div class="clear"></div>
								</div>
								<div class="item submit">
									<div class="left"></div>
									<div class="right" style="float:left;">
										<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
									</div>
									<div class="clear"></div>
								</div>
							</div>
							</form>
						</div>';
					/* END EDIT FORUM FORM */
					
				}
			} else {
				$html .= '<div class="level2"><div class="left"><div class="title">' . __('Empty') . '</div></div></div>';
			}
			
			$html .= '</div></div>';
			
		}
		$html .= '</div>';
	} else {
		$html .= __('While empty');
	}
	return $html;
}





function edit() {
	global $FpsDB;
	
	if (!isset($_POST['title']) || !isset($_POST['type']) || empty($_GET['id'])) {
		redirect('/admin/forum_cat.php');
	}
	if ($_POST['type'] == 'forum' && 
	(!isset($_POST['in_cat']) || !isset($_POST['description']) || !isset($_FILES['icon']))) {
		redirect('/admin/forum_cat.php');
	}
	$id = (int)$_GET['id'];
	if ($id < 1) {
		redirect('/admin/forum_cat.php');
	}
	if (!isset($_POST['in_pos'])) redirect('/admin/forum_cat.php');
	$in_pos = (int)$_POST['in_pos']; 
	if ($in_pos < 1)  redirect('/admin/forum_cat.php');
	$error = '';
	$title = $_POST['title'];
	if (mb_strlen($title) > 200) $error .= '<li>' . __('Title more than 200 symbol') . '</li>';
	
	
	
	if ($_POST['type'] == 'forum') {
		$in_cat = (int)$_POST['in_cat'];
		$description = $_POST['description'];
		if (!empty($_FILES['icon']['name'])) {
			if ($_FILES['icon']['size'] > 100000) $error = $error . '<li>' . __('Max icon size 100Kb') . '</li>';
			if ($_FILES['icon']['type'] != 'image/gif'
			&& $_FILES['icon']['type'] != 'image/jpeg'
			&& $_FILES['icon']['type'] != 'image/png') $error = $error . '<li>' . __('Wrong icon format') . '</li>';
			if (!empty($error)) {
				$_SESSION['addErrors'] = $error;
				redirect('/admin/forum_cat.php');
			}
		}
		
		
		
		// Lock forum
		$lock_passwd = '';
		$lock_posts = 0;
		if (!empty($_POST['lock_passwd'])) {
			$lock_passwd = $_POST['lock_passwd'];
			if (mb_strlen($lock_passwd) > 100) $error = $error . '<li>' . __('Forum passwd more than 100 sym.') . '</li>';
		}
		if (!empty($_POST['lock_posts'])) {
			$lock_posts = $_POST['lock_posts'];
			if (mb_strlen($lock_posts) > 100) $error = $error . '<li>' . __('Posts count must be numeric') . '</li>';
		}
		
		
		
		//if isset errors
		if (!empty($error)) {
			$_SESSION['addErrors'] = $error;
			redirect('/admin/forum_cat.php');
		}
		
		//busy position
		$busy = $FpsDB->select('forums', DB_COUNT, array('cond' => array('pos' => $in_pos, 'in_cat' => $in_cat)));
		if ($busy > 0) {
			$FpsDB->query("UPDATE `" . $FpsDB->getFullTableName('forums') . "` SET `pos` = `pos` + 1 WHERE `pos` >= '" . $in_pos . "'");
		}
		//default position ON BOTTOM
		if ($in_pos < 1) {
			$last = $FpsDB->query("SELECT MAX(`pos`) AS last FROM `" . $FpsDB->getFullTableName('forums') . "` WHERE `in_cat` = '" . $in_cat . "' LIMIT 1");
			if (!empty($last[0]['last'])) {
				$in_pos = ((int)$last[0]['last'] + 1);
			} else {
				$in_pos = 1;
			}
		}
		
		
		$parent_forum_id = (int)$_POST['parent_forum_id'];
		$parent_forum_id = (!empty($parent_forum_id)) ? $parent_forum_id : '';
		
		//if allright - saving data
		$query = $FpsDB->save('forums', array(
			'id' => $id,
			'description' => $description,
			'title' => $title,
			'in_cat' => $in_cat,
			'pos' => $in_pos,
			'parent_forum_id' => $parent_forum_id,
			'lock_passwd' => $lock_passwd,
			'lock_posts' => $lock_posts,
		));
		if ($query) {
			if (move_uploaded_file($_FILES['icon']['tmp_name'], ROOT . '/sys/img/forum_icon_' . $id . '.jpg')) {
				chmod(ROOT . '/sys/img/forum_icon_' . $id . '.jpg', 0755);
			}
		}
	
	
	} else if ($_POST['type'] == 'section') {
		
		//if isset errors
		if (!empty($error)) {
			$_SESSION['addErrors'] = $error;
			redirect('/admin/forum_cat.php');
		}
		
		//busy position
		$busy = $FpsDB->select('forum_cat', DB_COUNT, array('cond' => array('previev_id' => $in_pos)));
		if ($busy > 0) {
			$FpsDB->query("UPDATE `" . $FpsDB->getFullTableName('forum_cat') . "` SET `previev_id` = `previev_id` + 1 WHERE `previev_id` >= '" . $in_pos . "'");
		}
		//default position ON BOTTOM
		if ($in_pos < 1) {
			$last = $FpsDB->query("SELECT MAX(`previev_id`) AS last FROM `" . $FpsDB->getFullTableName('forum_cat') . "` LIMIT 1");
			if (!empty($last[0]['last'])) {
				$in_pos = ((int)$last[0]['last'] + 1);
			} else {
				$in_pos = 1;
			}
		}
		
		$FpsDB->save('forum_cat', array(
			'id' => $id, 
			'title' => $title, 
			'previev_id' => $in_pos,
		));
	}
	redirect('/admin/forum_cat.php');
}




function acl() {
	if (empty($_GET['id'])) redirect('/admin/forum_cat.php');
	$id = (int)$_GET['id'];
	if ($id < 1) redirect('/admin/forum_cat.php');
	

	$Register = Register::getInstance();
	$acl_groups = $Register['ACL']->get_group_info();
	$forum_rules = $Register['ACL']->getForumRules();
	

	$edited_forum = (array_key_exists($id, $forum_rules)) ? $forum_rules[$id] : array();
	

	foreach ($acl_groups as $gr_id => $grparams) {
		if ($gr_id === 0) continue;
		
		
		if (isset($_POST['access_for_' . $gr_id])) {
			$edited_forum[$gr_id] = $_POST['access_for_' . $gr_id];
		} else {
			$edited_forum[$gr_id] = '';
		}				
	}
	
	
	$forum_rules[$id] = $edited_forum;
	$Register['ACL']->saveForumRules($forum_rules);
	

	redirect('/admin/forum_cat.php');
}





function add() {
	global $FpsDB;
	if (empty($_POST['type'])) redirect('/admin/forum_cat.php');
	if (!isset($_POST['title'])) redirect('/admin/forum_cat.php');
	if (!isset($_POST['in_pos'])) redirect('/admin/forum_cat.php');
	
	$in_pos = (int)$_POST['in_pos'];
	if ($_POST['type'] == 'forum' && (!isset($_FILES['icon']) || !isset($_POST['in_cat']))) redirect('/admin/forum_cat.php');
	$title = $_POST['title'];
	$error = '';
	if (empty($title)) $error .= '<li>' . __('Empty field "title"') . '</li>';
	
	
	if ($_POST['type'] == 'section') {
		if (mb_strlen($title) > 200) $error .= '<li>' . __('Title more than 200 symbol') . '</li>';
		//if isset errors
		if (!empty($error)) {
			$_SESSION['addErrors'] = $error;
			redirect('/admin/forum_cat.php');
		}
		
		//busy position
		$busy = $FpsDB->select('forum_cat', DB_COUNT, array('cond' => array('previev_id' => $in_pos)));
		if ($busy > 0) {
			$FpsDB->query("UPDATE `" . $FpsDB->getFullTableName('forum_cat') . "` SET `previev_id` = `previev_id` + 1 WHERE `previev_id` >= '" . $in_pos . "'");
		}
		//default position ON BOTTOM
		if ($in_pos < 1) {
			$last = $FpsDB->query("SELECT MAX(`previev_id`) AS last FROM `" . $FpsDB->getFullTableName('forum_cat') . "` LIMIT 1");
			if (!empty($last[0]['last'])) {
				$in_pos = ((int)$last[0]['last'] + 1);
			} else {
				$in_pos = 1;
			}
		}
		$FpsDB->save('forum_cat', array('title' => $title, 'previev_id' => $in_pos));
	
	
	} elseif ($_POST['type'] == 'forum') {
		$in_cat = (int)$_POST['in_cat'];
		if (!empty($_FILES['icon']['name'])) {
			if ($_FILES['icon']['size'] > 100000) $error = $error . '<li>' . __('Max icon size 100Kb') . '</li>';
			if ($_FILES['icon']['type'] != 'image/gif'
			&& $_FILES['icon']['type'] != 'image/jpeg'
			&& $_FILES['icon']['type'] != 'image/png') $error = $error . '<li>' . __('Wrong icon format') . '</li>';
		}
		
		
		// Lock forum
		$lock_passwd = '';
		$lock_posts = 0;
		if (!empty($_POST['lock_passwd'])) {
			$lock_passwd = $_POST['lock_passwd'];
			if (mb_strlen($lock_passwd) > 100) $error = $error . '<li>' . __('Forum passwd more than 100 sym.') . '</li>';
		}
		if (!empty($_POST['lock_posts'])) {
			$lock_posts = $_POST['lock_posts'];
			if (mb_strlen($lock_posts) > 100) $error = $error . '<li>' . __('Posts count must be numeric') . '</li>';
		}
		
		
		if (!empty($error)) {
			$_SESSION['addErrors'] = $error;
			redirect('/admin/forum_cat.php');
		}
		
		//busy position
		$busy = $FpsDB->select('forums', DB_COUNT, array('cond' => array('pos' => $in_pos, 'in_cat' => $in_cat)));
		if ($busy > 0) {
			$FpsDB->query("UPDATE `" . $FpsDB->getFullTableName('forums') . "` SET `pos` = `pos` + 1 WHERE `pos` >= '" . $in_pos . "'");
		}
		//default position ON BOTTOM
		if ($in_pos < 1) {
			$last = $FpsDB->query("SELECT MAX(`pos`) AS last FROM `" . $FpsDB->getFullTableName('forums') . "` WHERE `in_cat` = '" . $in_cat . "' LIMIT 1");
			if (!empty($last[0]['last'])) {
				$in_pos = ((int)$last[0]['last'] + 1);
			} else {
				$in_pos = 1;
			}
		}
		
		$parent_forum_id = (int)$_POST['parent_forum_id'];
		$parent_forum_id = (!empty($parent_forum_id)) ? $parent_forum_id : '';
		
		$description = $_POST['description'];
		$FpsDB->save('forums', array(
			'description' => $description,
			'title' => $title,
			'in_cat' => $in_cat,
			'pos' => $in_pos,
			'parent_forum_id' => $parent_forum_id,
			'lock_passwd' => $lock_passwd,
			'lock_posts' => $lock_posts,
		));
		$id = mysql_insert_id();
		if (!empty($_FILES['icon']['name'])) {
			if (move_uploaded_file($_FILES['icon']['tmp_name'], ROOT . '/sys/img/forum_icon_' . $id . '.jpg')) {
				chmod(ROOT . '/sys/img/forum_icon_' . $id . '.jpg', 0755);
			}
		}
	}
	redirect('/admin/forum_cat.php');
	
}





function delete() {
	global $FpsDB;
	if (empty($_GET['id']) || !is_numeric($_GET['id']))  header ('Location: /');
	$id = (int)$_GET['id']; 
	if ($id < 1) redirect('/admin/forum_cat.php');
	
	if (!isset($_GET['section'])) {
		$sql = $FpsDB->select('themes', DB_ALL, array('cond' => array('id_forum' => $id)));
		if (count($sql) > 0) {
			foreach ($sql as $result) {
				delete_theme($result['id']);
			}
		}
		$FpsDB->query("DELETE FROM `" . $FpsDB->getFullTableName('forums') . "` WHERE `id`='{$id}'");
		if (file_exists(ROOT . '/sys/img/forum_icon_' . $id . '.jpg')) 
			unlink(ROOT . '/sys/img/forum_icon_' . $id . '.jpg');
	} else {
		$sql = $FpsDB->select('forums', DB_ALL, array('cond' => array('in_cat' => $id)));
		if (count($sql) > 0) {
			foreach ($sql as $_result) {
				$sql = $FpsDB->select('themes', DB_ALL, array('cond' => array('id_forum' => $_result['id'])));
				if (count($sql) > 0) {
					foreach ($sql as $result) {
						delete_theme($result['id']);
					}
				}
				if (file_exists(ROOT . '/sys/img/forum_icon_' . $_result['id'] . '.jpg')) 
					unlink(ROOT . '/sys/img/forum_icon_' . $_result['id'] . '.jpg');
			}
		}
		$FpsDB->query("DELETE FROM `" . $FpsDB->getFullTableName('forums') . "` WHERE `in_cat`='{$id}'");
		$FpsDB->query("DELETE FROM `" . $FpsDB->getFullTableName('forum_cat') . "` WHERE `id`='{$id}'");
	}
	redirect('/admin/forum_cat.php');
}

// Функция удаляет тему; ID темы передается методом GET
function delete_theme($id_theme) {
	global $FpsDB;
	// Если не передан ID темы, которую надо удалить
	if (empty($id_theme)) {
		redirect('/admin/forum_cat.php');
	}
	$id_theme = (int)$id_theme;
	if ( $id_theme < 1 ) {
		redirect('/admin/forum_cat.php');
	}
	
	// delete colision ( this is paranoia )
	$FpsDB->query("DELETE FROM `" . $FpsDB->getFullTableName('themes') . "` WHERE id NOT IN (SELECT DISTINCT id_theme FROM `" . $FpsDB->getFullTableName('posts') . "`)");
	$FpsDB->query("DELETE FROM `" . $FpsDB->getFullTableName('posts') . "` WHERE id_theme NOT IN (SELECT id FROM `" . $FpsDB->getFullTableName('themes') . "`)");

	
	
	// Сперва мы должны удалить все сообщения (посты) темы;
	// начнем с того, что удалим файлы вложений
	$res = $FpsDB->select('posts', DB_ALL, array('cond' => array('id_theme' => $id_theme)));
	if (count($res) > 0) {
		foreach ($res as $file) {
			// Удаляем файл, если он есть
			$attach_files = $FpsDB->select('forum_attaches', DB_ALL, array('cond' => array('post_id' => $file['id'])));
			if (count($attach_files) > 0) {
				foreach ($attach_files as $attach_file) {
					if (file_exists(ROOT . '/sys/files/forum/' . $attach_file['filename'])) {
						if (@unlink(ROOT . '/sys/files/forum/' . $attach_file['filename'])) {
							$FpsDB->query("DELETE FROM `" . $FpsDB->getFullTableName('forum_attaches') . "` WHERE `id`='" . $attach_file['id'] . "'");
						}
					}
				}
			}
			// заодно обновляем таблицу TABLE_USERS - надо обновить поле posts (кол-во сообщений)
			if ( $file['id_author'] ) {
				$FpsDB->query("UPDATE `" . $FpsDB->getFullTableName('users') . "` SET `posts` = `posts` - 1 WHERE `id` = '" . $file['id_author'] . "'");
			}
		}
	}
	
	
	$attach_files = $FpsDB->select('forum_attaches', DB_ALL, array('cond' => array('theme_id' => $id_theme)));
	if (count($attach_files) > 0) {
		foreach ($attach_files as $attach_file) {
			if (file_exists(ROOT . '/sys/files/forum/' . $attach_file['filename'])) {
				if (@unlink(ROOT . '/sys/files/forum/' . $attach_file['filename'])) {
					$FpsDB->query("DELETE FROM `" . $FpsDB->getFullTableName('forum_attaches') . "` WHERE `id`='" . $attach_file['id'] . "'");
				}
			}
		}
	}

	//we must know id_forum
	$theme = $FpsDB->select('themes', DB_FIRST, array('cond' => array('id' => $id_theme)));
	
	
	//delete posts and theme
	$p_res = $FpsDB->query("DELETE FROM `" . $FpsDB->getFullTableName('posts') . "` WHERE `id_theme` = '" . $id_theme . "'");
	$t_res = $FpsDB->query("DELETE FROM `" . $FpsDB->getFullTableName('themes') . "` WHERE `id` = '" . $id_theme . "'");
	
	if (!empty($theme[0]['id_author'])) {
		// Обновляем таблицу TABLE_USERS - надо обновить поле themes
		$u_res = $FpsDB->query("UPDATE `" . $FpsDB->getFullTableName('users') . "` SET `themes` = `themes` - 1
				WHERE `id` = '" . $theme[0]['id_author'] . "'");
	}
	//clean cache
	$Cache = new Cache;
	$Cache->clean(CACHE_MATCHING_ANY_TAG, array('theme_id_' . $id_theme,));
	$Cache->clean(CACHE_MATCHING_TAG, array('module_forum', 'action_index'));
}


//delete "0" values from forums pos AND forums_cat previev_id
function deleteCollisions() {
	global $FpsDB;
	$categories_err = $FpsDB->select('forum_cat', DB_COUNT, array('cond' => array('previev_id' => 0)));
	$forums_err = $FpsDB->select('forums', DB_COUNT, array('cond' => array('pos' => 0)));
	if ($categories_err > 0 || $forums_err > 0) {
		$categories = $FpsDB->select('forum_cat', DB_ALL);
		if (count($categories) > 0) {
			foreach ($categories as $cat_key => $cat) {
				$forums = $FpsDB->select('forums', DB_ALL, array('cond' => array('in_cat' => $cat['id'])));
				if (count($forums) > 0) {
					foreach ($forums as $key => $forum) {
						$FpsDB->save('forums', array(
							'id' => $forum['id'],
							'pos' => ($key + 1),
						));
					}
				}
				if ((int)$cat['previev_id'] < 1) {
					$FpsDB->save('forum_cat', array(
						'id' => $cat['id'],
						'previev_id' => ($cat_key + 1),
					));
				}
			}
		}
	}
	return;
}

include_once 'template/footer.php';
?>