<?php
##################################################
##												##
## @Author:       Andrey Brykin (Drunya)        ##
## @Version:      1.6.1                         ##
## @Project:      CMS                           ##
## @package       CMS AtomX                     ##
## @subpackege    Admin Panel module            ##
## @copyright     ©Andrey Brykin 2010-2013      ##
## @last mod.     2013/06/15                    ##
##################################################


##################################################
##												##
## any partial or not partial extension         ##
## CMS AtomX,without the consent of the         ##
## author, is illegal                           ##
##################################################
## Любое распространение                        ##
## CMS AtomX или ее частей,                     ##
## без согласия автора, является не законным    ##
##################################################


include_once '../sys/boot.php';
include_once ROOT . '/admin/inc/adm_boot.php';
$pageTitle = __('List of materials');
$Register = Register::getInstance();


$allowed_mods = $Register['ModManager']->getAllowedModules('materialsList');
$allowed_actions = array('edit', 'delete', 'index', 'premoder');


if (empty($_GET['m']) || !in_array($_GET['m'], $allowed_mods)) redirect('/admin/');
$module = $_GET['m'];

$action = (!empty($_GET['ac'])) ? $_GET['ac'] : 'index';
if (empty($action) && !in_array($action, $allowed_actions)) $action = 'index';


$Controll = new MaterialsList;
list($output, $pages) = $Controll->{$action}($module);






class MaterialsList {
	public $pageTitle;
	
	
	public function __construct() {
		$this->pageTitle = __('List of materials');
	}


	public function index($module) {
		$output = '';
		$Register = Register::getInstance();
		$model = $Register['ModManager']->getModelInstance($_GET['m']);
		
		$where = (!empty($_GET['premoder'])) ? array('premoder' => 'nochecked') : array();

		$total = $model->getTotal(array('cond' => $where));
		list ($pages, $page) = pagination($total, 20, '/admin/materials_list.php?m=' . $module
            . (!empty($_GET['order']) ? '&order=' . $_GET['order'] : '')
            . (!empty($_GET['asc']) ? '&asc=1' : ''));

		
		$model->bindModel('author');
		$materials = $model->getCollection($where, array(
			'page' => $page,
			'limit' => 20,
			'order' => $model->getOrderParam(),
		));

		
		if (empty($materials)) 
			$output = '<div class="setting-item"><div class="left"><b>' 
			. __('Materials not found') . '</b></div><div class="clear"></div></div>';


		foreach ($materials as $mat) {
			$output .= '<div class="setting-item"><div class="left">';
			$output .= '<a style="font-weight:bold; margin-bottom:5px;" href="' 
				. get_url('/admin/materials_list.php?m=' . $module . '&ac=edit&id=' 
				. $mat->getId()) . '">' . h($mat->getTitle()) . '</a>';
			$output .= '<br />(' . $mat->getAuthor()->getName() . ')';
			$output .= '</div><div style="width:60%;" class="right">';
			
			
			if ($module == 'foto') {
				$AtmFoto = h(preg_replace('#[^\w\d ]+#ui', ' ', $mat->getTitle()));
				$AtmFoto = '<img alt="' . $AtmFoto . '" src="' . WWW_ROOT . '/sys/files/' . $module . '/preview/' 
					. $mat->getFilename() . '" width="150px" />';
				$AtmFoto = '<a target="_blank" href="' . WWW_ROOT . '/sys/files/' . $module . '/full/' 
					. $mat->getFilename() . '">' . $AtmFoto . '</a>';
				
				$output .= $AtmFoto;

			} else {
				$output .= h(mb_substr($mat->getMain(), 0, 500));
			}
			$output .= '<br /><span class="comment">' . AtmDateTime::getSimpleDate($mat->getDate()) . '</span>';
			
			
			// rejected - отвергнуто
			// confirmed - подтвердил
			// nochecked - не проверено
			if (!empty($_GET['premoder'])) {
				$output .= '</div><div class="unbordered-buttons">
				<a href="' . get_url('/admin/materials_list.php?m=' . $module . '&ac=premoder&status=rejected&id=' . $mat->getId()) . '" class="off"></a>' .
				'<a href="' . get_url('/admin/materials_list.php?m=' . $module . '&ac=premoder&status=confirmed&id=' . $mat->getId()) . '" class="on"></a>
				</div><div class="clear"></div></div>';
			} else {
				$output .= '</div><div class="unbordered-buttons">
				<a href="' . get_url('/admin/materials_list.php?m=' . $module . '&ac=delete&id=' . $mat->getId()) . '" class="delete"></a>' .
				'<a href="' . get_url('/admin/materials_list.php?m=' . $module . '&ac=edit&id=' . $mat->getId()) . '" class="edit"></a>
				</div><div class="clear"></div></div>';
			}
		}
		
		return array($output, $pages);
	}
	
	
	function premoder($module){
		$Register = Register::getInstance();
		$Model = $Register['ModManager']->getModelInstance($module);
		$entity = $Model->getById(intval($_GET['id']));
		if (!empty($entity)) {
			
			$status = $_GET['status'];
			if (!in_array($status, array('rejected', 'confirmed'))) $status = 'nochecked';
			
			$entity->setPremoder($status);
			$entity->save();
			$_SESSION['message'] = __('Saved');
			
			
			//clean cache
			$Cache = new Cache;
			$Cache->clean(CACHE_MATCHING_ANY_TAG, array('module_' . $module));
		} else {
			$_SESSION['errors'] = __('Some error occurred');
		}
		redirect('/admin/materials_list.php?m=' . $module . '&premoder=1');
	}
	
	
	public function delete($module) {
		$Register = Register::getInstance();
		
		$model = $Register['ModManager']->getModelInstance($_GET['m']);
		$id = intval($_GET['id']);
		$entity = $model->getById($id);
		
		if (!empty($entity)) {
			if ($module == 'foto') {
				_unlink(ROOT . '/sys/files/' . $module . '/full/' . $entity->getFilename());
				_unlink(ROOT . '/sys/files/' . $module . '/preview/' . $entity->getFilename());
				$entity->delete();
				
			} else {
				$entity->delete();
			}
			
			$_SESSION['message'] = __('Material has been delete');
		}
		
		redirect('/admin/materials_list.php?m=' . $module);
	}
	
	
	public function edit($module) {
		$this->pageTitle .= ' - ' . __('Edit');
	
		$output = '';
		$Register = Register::getInstance();
		$model = $Register['ModManager']->getModelInstance($_GET['m']);

		
		$id = intval($_GET['id']);
		$entity = $model->getById($id);
		
		
		
		if (!empty($_POST)) {
			$entity->setTitle($_POST['title']);
			$entity->setMain($_POST['main']);
			$entity->setSourse_email($_POST['email']);
			
			
			$entity->save();
			$_SESSION['message'] = __('Saved');
			
			redirect('/admin/materials_list.php?m=' . $module);
		}
		
		
		
		$output .= '
		<div class="setting-item"><div class="left">
			' . __('Title') . '
		</div><div class="right">
			<input type="text" name="title" value="'.h($entity->getTitle()).'" />
		</div><div class="clear"></div></div>
		<div class="setting-item"><div class="left">
			' . __('Text of material') . '
		</div><div class="right">
			<textarea style="height:200px;" name="main">'.h($entity->getMain()).'</textarea>
		</div><div class="clear"></div></div>
		<div class="setting-item"><div class="left">
			' . __('Email') . '
		</div><div class="right">
			<input type="text" name="email" value="'.h($entity->getSourse_email()).'" />
		</div><div class="clear"></div></div>
		<div class="setting-item">
			<div class="left">
			</div>
			<div class="right">
				<input class="save-button" type="submit" name="send" value="' . __('Save') . '" />
			</div>
			<div class="clear"></div>
		</div>';
		
		
		return array($output, '');
	}
}




$pageNav = $Controll->pageTitle;
$pageNavr = '';
include_once ROOT . '/admin/template/header.php';

?>


<form method="POST" action="" enctype="multipart/form-data">
<div class="list">
	<div class="title"><?php echo $pageNav; ?></div>
    <div class="add-cat-butt">
        <select onChange="window.location.href='/admin/materials_list.php?m=<?php echo $_GET['m'] ?>&order='+this.value;">
            <option><?php echo __('Ordering') ?></option>
            <option value="views"><?php echo __('Views') ?> (↓)</option>
            <option value="views&asc=1"><?php echo __('Views') ?> (↑)</option>
            <option value="comments"><?php echo __('Comments') ?> (↓)</option>
            <option value="comments&asc=1"><?php echo __('Comments') ?> (↑)</option>
            <option value="date"><?php echo __('Date') ?> (↓)</option>
            <option value="date&asc=1"><?php echo __('Date') ?> (↑)</option>
        </select>
    </div>
	<div class="level1">
		<div class="head">
			<div class="title settings"><?php echo __('Title') ?></div>
			<div class="title-r"><?php echo __('Text of material') ?></div>
			<div class="clear"></div>
		</div>
		<div class="items">
			<?php echo $output; ?>

		</div>
	</div>
</div>
<div class="pagination"><?php echo $pages ?></div>
</form>



<?php include_once 'template/footer.php'; ?>
