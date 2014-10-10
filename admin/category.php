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
| @copyright     ©Andrey Brykin 2010-2014        |
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




/**
 * Return current module which we editing
 */
function getCurrMod() {
	$ModulesManager = new ModulesManager();
	$allow_mods = $ModulesManager->getAllowedModules('categories');
	if (empty($_GET['mod'])) redirect('/admin/category.php?mod=news');
	
	$mod = trim($_GET['mod']);
	if (!in_array($mod, $allow_mods)) redirect('/admin/category.php?mod=news');
	return $mod;
}




/**
 * Try find collision
 */
function deleteCatsCollision() 
{
	global $FpsDB;
	$collision = $FpsDB->select(getCurrMod() . '_categories', DB_ALL, array(
		'joins' => array(
			array(
				'type' => 'LEFT',
				'table' => getCurrMod() . '_categories',
				'alias' => 'b',
				'cond' => '`b`.`id` = `a`.`parent_id`',
			),
		),
		'fields' => array('COUNT(`b`.`id`) as cnt', '`a`.*'),
		'alias' => 'a',
		'group' => '`a`.`parent_id`',
	));
	
	if (count($collision)) {
		foreach ($collision as $key => $cat) {
			if (!empty($cat['parent_id']) && empty($cat['cnt'])) {
				$FpsDB->save(getCurrMod() . '_categories',
				array(
					'parent_id' => 0,
				), 
				array(
					'id' => $cat['id']
				));
			}
		}
	}
}
deleteCatsCollision();





$head = file_get_contents('template/header.php');
$page_title = __(ucfirst(getCurrMod()));
$popups = '';


if (!isset($_GET['ac'])) $_GET['ac'] = 'index';
$permis = array('add', 'del', 'index', 'edit', 'off_home', 'on_home');
if (!in_array($_GET['ac'], $permis)) $_GET['ac'] = 'index';

switch($_GET['ac']) {
	case 'index':
		$content = index($page_title);
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
	case 'on_home':
		$content = on_home();
		break;
	case 'off_home':
		$content = off_home();
		break;
	default:
		$content = index();
}




$pageTitle = $page_title;
$pageNav = $page_title;
$pageNavr = '';
include_once ROOT . '/admin/template/header.php';
?>



<div class="warning">
<?php echo __('If you delete a category, all the materials in it will be removed') ?><br /><br />
</div>
<?php


echo $popups;
echo $content;



function getTreeNode($array, $id = false) {
	$out = array();
	foreach ($array as $key => $val) {
		if ($id === false && empty($val['parent_id'])) {
			$out[$val['id']] = array(
				'category' => $val,
				'subcategories' => getTreeNode($array, $val['id']),
			);
			unset($array[$key]);
		} else {
		
			if ($val['parent_id'] == $id) {
				$out[$val['id']] = array(
					'category' => $val,
					'subcategories' => getTreeNode($array, $val['id']),
				);
				unset($array[$key]);
			}
		}
	}
	return $out;
}


function buildCatsList($catsTree, $catsList, $indent = '') {
	global $popups;

    $Register = Register::getInstance();
	$FpsDB = $Register['DB'];
    $acl_groups = $Register['ACL']->get_group_info();
	$out = '';
	
	
	foreach ($catsTree as $id => $node) {
		$cat = $node['category'];
		$no_access = ($cat['no_access'] !== '') ? explode(',', $cat['no_access']) : array();

		
		$_catList = (count($catsList)) ? $catsList : array();
		$cat_selector = '<select  name="id_sec" id="cat_secId">';
		if (empty($cat['parent_id'])) {
			$cat_selector .= '<option value="0" selected="selected">&nbsp;</option>';
		} else {
			$cat_selector .= '<option value="0">&nbsp;</option>';
		}
		foreach ($_catList as $selector_result) {
			if ($selector_result['id'] == $cat['id']) continue;
			if ($cat['parent_id'] == $selector_result['id']) {
				$cat_selector .= '<option value="' . $selector_result['id'] 
				. '" selected="selected">' . $selector_result['title'] . '</option>';
			} else {
				$cat_selector .= '<option value="' . $selector_result['id'] 
				. '">' . $selector_result['title'] . '</option>';
			}
		}
		$cat_selector .= '</select>';
		
		
		
		$out .= '<div class="level2">
					<div class="number">' . $cat['id'] . '</div>
					<div class="title">' . $indent . h($cat['title']) . '</div>
					<div class="buttons">';
					

		

		if (getCurrMod() != 'foto') {
			if ($cat['view_on_home'] == 1) {
				$out .=  '<a class="off-home" title="Top off" href="?ac=off_home&id=' . $cat['id'] . '&mod='.getCurrMod().'" onClick="return _confirm();">'
					. '</a>';
			} else {
				$out .=  '<a class="on-home" title="On top" href="?ac=on_home&id=' . $cat['id'] . '&mod='.getCurrMod().'" onClick="return _confirm();">'
					. '</a>';
			}
		}
			
			
		$out .= '<a href="javascript://" class="edit" title="Edit" onClick="openPopup(\'' . $cat['id'] . '_cat\');"></a>
				 <a title="Delete" href="?ac=del&id=' . $cat['id'] . '&mod='.getCurrMod().'" class="delete" onClick="return _confirm();"></a>
				</div>
			<div class="posts">' . $cat['cnt'] . '</div>
		</div>';
			
			
			
		$popups .=	'<div id="' . $cat['id'] . '_cat" class="popup">
				<div class="top">
					<div class="title">' . __('Category editing') . '</div>
					<div onClick="closePopup(\'' . $cat['id'] . '_cat\');" class="close"></div>
				</div>
				<form action="category.php?mod=' . getCurrMod() . '&ac=edit&id=' . $cat['id'] . '" method="POST">
				<div class="items">
					<div class="item">
						<div class="left">
							' . __('Parent section') . ':
						</div>
						<div class="right">' . $cat_selector . '</div>
						<div class="clear"></div>
					</div>
					<div class="item">
						<div class="left">
							' . __('Title') . ':
						</div>
						<div class="right"><input type="text" name="title" value="' . h($cat['title']) . '" /></div>
						<div class="clear"></div>
					</div>
					<div class="item">
						<div class="left">
							' . __('Access for') . ':
						</div>
						<div class="right"><table class="checkbox-collection"><tr>';
						$n = 0;
						if ($acl_groups && is_array($acl_groups)) {
							foreach ($acl_groups as $id => $group) {
								if (($n % 3) == 0) $popups .= '</tr><tr>';
								$checked = (in_array($id, $no_access)) ? '' : ' checked="checked"';
								
								$inp_id = md5(rand(0, 99999) . $n);
								
								$popups .= '<td><input id="' . $inp_id . '" type="checkbox" name="access[' . $id . ']" value="' . $id 
								. '"' . $checked . '  /><label for="' . $inp_id . '">' . h($group['title']) . '</label></td>';
								$n++;
							}
						}
						$popups .= '</tr></table></div>
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
			
		
		if (count($node['subcategories'])) {
			$out .= buildCatsList($node['subcategories'], $catsList, $indent . '<div class="cat-indent">&nbsp;</div>');
		}
	}
	
	return $out;
}



function index(&$page_title) {
	global $popups;

    $Register = Register::getInstance();
	$FpsDB = $Register['DB'];
    $acl_groups = $Register['ACL']->get_group_info();


	$page_title .= ' - ' . __('Sections editor');
	$cat_selector = '<select name="id_sec" id="cat_secId">';
	$cat_selector .= '<option value="0">&nbsp;</option>';
    $query_params = array(
        'joins' => array(),
        'fields' => array('a.*', 'COUNT(b.`id`) as cnt'),
        'alias' => 'a',
        'group' => 'a.`id`',
    );

    // count a materials if such model is exists
    try {
        $Register['ModManager']->getModelInstance(getCurrMod());
        $query_params['joins'][] = array(
            'alias' => 'b',
            'type' => 'LEFT',
            'table' => getCurrMod(),
            'cond' => 'a.`id` = b.`category_id`',
        );
    } catch (Exception $e) {
        $query_params['joins'][] = array(
            'alias' => 'b',
            'type' => 'LEFT',
            'table' => '(SELECT NULL as category_id, NULL as id)',
            'cond' => 'a.`id` = b.`category_id`',
        );
    }

	$all_sections = $FpsDB->select(getCurrMod() . '_categories', DB_ALL, $query_params);
	foreach ($all_sections as $result) {
		$cat_selector .= '<option value="' . $result['id'] . '">' . h($result['title']) . '</option>';
	}
	$cat_selector .= '</select>';
	
	$html = '';

	$cats_tree = getTreeNode($all_sections);
	if (count($cats_tree)) {
		foreach ($cats_tree as $catid => $cat) {
		
		}
	}
	

	
	
	
	$popups .=	'<div id="addCat" class="popup">
			<div class="top">
				<div class="title">' . __('Adding category') . '</div>
				<div onClick="closePopup(\'addCat\');" class="close"></div>
			</div>
			<form action="category.php?mod=' . getCurrMod() . '&ac=add" method="POST">
			<div class="items">
				<div class="item">
					<div class="left">
						' . __('Parent section') . ':
					</div>
					<div class="right">' . $cat_selector . '</div>
					<div class="clear"></div>
				</div>
				<div class="item">
					<div class="left">
						' . __('Title') . ':
					</div>
					<div class="right">
						<input type="hidden" name="type" value="cat" />
						<input type="text" name="title" /></div>
					<div class="clear"></div>
				</div>
				<div class="item">
					<div class="left">
						' . __('Access for') . ':
					</div>
					<div class="right">
						<table class="checkbox-collection"><tr>';
						$n = 0;
						$inp_id = md5(rand(0, 99999) . $n);
						if ($acl_groups && is_array($acl_groups)) {
							foreach ($acl_groups as $id => $group) {
								if (($n % 3) == 0) $popups .= '</tr><tr>';
								$popups .= '<td><input id="' . $inp_id . '" type="checkbox" name="access[' . $id . ']" value="' . $id 
								. '"  checked="checked" /><label for="' . $inp_id . '">' . h($group['title']) . '</label></td>';
								$n++;
							}
						}
						$popups .= '</tr></table>
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
		<div class="title">' . __('Categories management') . '</div>
		<div class="add-cat-butt" onClick="openPopup(\'addCat\');"><div class="add"></div>' . __('Add section') . '</div>
		<div class="level1">
			<div class="head">
				<div class="title">' . __('Category') . '</div>
				<div class="buttons">
				</div>
				<div class="clear"></div>
			</div>
			<div class="items">';
			
			
	if (count($all_sections) > 0) {
		$html .= buildCatsList($cats_tree, $all_sections); 	
	} else {
		$html .= __('Sections not found');
	}
	
	
	$html .= '</div></div></div>';

	
	return $html;
}




function edit() {

	if (!isset($_GET['id'])) redirect('/admin/category.php?mod=' . getCurrMod());
	if (!isset($_POST['title'])) redirect('/admin/category.php?mod=' . getCurrMod());
	$id = intval($_GET['id']);
	
	if ($id < 1) redirect('/admin/category.php?mod=' . getCurrMod());
	
	
	global $FpsDB;
	$Register = Register::getInstance();
	$acl_groups = $Register['ACL']->get_group_info();
	$model = $Register['ModManager']->getModelInstance(getCurrMod() . 'Categories');
	
	$error = '';

	if (empty($_POST['title'])) $error .= '<li>' . __('Empty field "title"') . '</li>';
	


	$parent_id = intval($_POST['id_sec']);
	$entity = $model->getById($id);
	if (empty($entity)) $error .= '<li>' . __('Edited section not found') . '</li>';

	
	/* we must know changed parent section or not changed her. And check her  */
	if (!empty($parent_id) && $entity->getParent_id() != $parent_id) {
		$target_section = $model->getById($parent_id);
		if (empty($target_section)) $error .= '<li>' . __('Parent section not found') . '</li>';
	}
	/* if errors exists */
	if (!empty($error)) {
		$_SESSION['errors'] = $Register['DocParser']->wrapErrors($error);
		redirect('/admin/category.php?mod=' . getCurrMod());
	}
	
	
	$no_access = array();
	if ($acl_groups && is_array($acl_groups)) {
		foreach ($acl_groups as $gid => $group) {
			if (!array_key_exists($gid, $_POST['access'])) {
				$no_access[] = $gid;
			}
		}
	}
	$no_access = (count($no_access)) ? implode(',', $no_access) : '';
	if ($no_access !== '') $no_access = New Expr($no_access);
	
	
	/* prepare data to save */
	$entity->setTitle(substr($_POST['title'], 0, 100));
	$entity->setNo_access($no_access);
	
	if (!empty($target_section)) {
		$path = $target_section->getPath();
		$path = (!empty($path)) ? $path . $parent_id . '.' : $parent_id . '.';
		$entity->setParent_id((int)$parent_id);
		$entity->setPath($path);
	}

	$entity->save();
		

	redirect('/admin/category.php?mod=' . getCurrMod());
}



function add() {
	global $FpsDB;
	if (!isset($_POST['title'])) redirect('/admin/category.php?mod=' . getCurrMod());
	
	
	$Register = Register::getInstance();
	$acl_groups = $Register['ACL']->get_group_info();
	$model = $Register['ModManager']->getModelInstance(getCurrMod() . 'Categories');
	
	
	$error = '';
	$title = trim($_POST['title']);
	$parent_id = intval($_POST['id_sec']);
	if ($parent_id < 0) $parent_id = 0;
	
	if (!empty($parent_id)) {
		$target_section = $model->getById($parent_id);
		if (empty($target_section)) $error .= '<li>' . __('Parent section not found') . '</li>';
	}
	
	
	if (empty($title)) $error .= '<li>' . __('Empty field "title"') . '</li>';
	
	$no_access = array();
	if ($acl_groups && is_array($acl_groups)) {
		foreach ($acl_groups as $id => $group) {
			if (!array_key_exists($id, $_POST['access'])) {
				$no_access[] = $id;
			}
		}
	}
	$no_access = (count($no_access)) ? implode(',', $no_access) : '';
	if ($no_access !== '') $no_access = New Expr($no_access);
	
	/* if errors exists */
	if (!empty($error)) {
		$_SESSION['errors'] = $error;
		redirect('/admin/category.php?mod=' . getCurrMod());
	}
	
	
	if (!empty($target_section)) {
		$path = $target_section->getPath();
		$path = (!empty($path)) ? $path . $parent_id . '.' : $parent_id . '.';
	}
	
	
	$data = array(
		'title' => $title,
		'parent_id' => $parent_id,
		'no_access' => $no_access,
	);
	if (!empty($path)) $data['path'] = $path;
	
	$entityName = getCurrMod() . 'CategoriesEntity';
	$entity = new $entityName($data);
	$entity->save();
		
	redirect('/admin/category.php?mod=' . getCurrMod());
}


function delete() {	
	global $Register, $FpsDB;
	$id = (!empty($_GET['id'])) ? intval($_GET['id']) : 0;
	if ($id < 1) redirect('/admin/category.php?mod=' . getCurrMod());
	
	
	$model = $Register['ModManager']->getModelInstance(getCurrMod() . 'Categories');
	$total = $model->getTotal();
	if ($total <= 1) {
		$_SESSION['errors'] = $Register['DocParser']->wrapErrors(__('You can\'t remove the last category'), true);
		redirect('/admin/category.php?mod=' . getCurrMod());
	}
	
	$childrens = $model->getCollection(array('parent_id' => $id));

	
	if (!$childrens) {
		delete_category($id);
	} else {
		foreach ($childrens as $category) {
			delete_category($category->getId());
			delete($category->getId());
		}
		
		$category->delete();
	}
	redirect('/admin/category.php?mod=' . getCurrMod());
}


function delete_category($id) {
	global $Register, $FpsDB;
	
	$attachModel = $Register['ModManager']->getModelInstance(getCurrMod() . 'Attaches');
	$sectionsModel = $Register['ModManager']->getModelInstance(getCurrMod() . 'Categories');
	$model = $Register['ModManager']->getModelInstance(getCurrMod());
	$records = $model->getCollection(array('category_id' => $id));
	
	
	// delete materials and attaches
	if (is_array($records) && count($records) > 0) {
		foreach ($records as $record) {
			$record->delete();
		}
	}
	
	// delete category
	$entity = $sectionsModel->getById($id);
	if ($entity) $entity->delete();
	
	return true;
}



function on_home($cid = false) {
	global $FpsDB;
	if (getCurrMod() == 'foto') redirect('/admin/category.php?mod=' . getCurrMod());
	
	
	if ($cid === false) {
		$id = (!empty($_GET['id'])) ? intval($_GET['id']) : 0;
		if ($id < 1) redirect('/admin/category.php?mod=' . getCurrMod());
	} else {
		$id = $cid;
	}

	
	$childs = $FpsDB->select(getCurrMod() . '_categories', DB_ALL, array('cond' => array('parent_id' => $id)));
	if (count($childs)) {
		foreach ($childs as $child) {
			on_home($child['id']);
		}
	} 
	
	$FpsDB->save(getCurrMod() . '_categories', array('id' => $id, 'view_on_home' => 1));
	$FpsDB->save(getCurrMod(), array('view_on_home' => 1), array('category_id' => $id));

		
	if ($cid === false) redirect('/admin/category.php?mod=' . getCurrMod());
}



function off_home($cid = false) {
	global $FpsDB;
	if (getCurrMod() == 'foto') redirect('/admin/category.php?mod=' . getCurrMod());
	
	
	if ($cid === false) {
		$id = (!empty($_GET['id'])) ? intval($_GET['id']) : 0;
		if ($id < 1) redirect('/admin/category.php?mod=' . getCurrMod());
	} else {
		$id = $cid;
	}

	
	$childs = $FpsDB->select(getCurrMod() . '_categories', DB_ALL, array('cond' => array('parent_id' => $id)));
	if (count($childs)) {
		foreach ($childs as $child) {
			off_home($child['id']);
		}
	} 
	
	$FpsDB->save(getCurrMod() . '_categories', array('id' => $id, 'view_on_home' => 0));
	$FpsDB->save(getCurrMod(), array('view_on_home' => 0), array('category_id' => $id));

		
	if ($cid === false) redirect('/admin/category.php?mod=' . getCurrMod());
}


include_once 'template/footer.php';
