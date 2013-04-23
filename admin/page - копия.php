<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      1.5                            ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    Admin Panel module             ##
## copyright     ©Andrey Brykin 2010-2012       ##
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




class PagesAdminController {
	
	public $Model;
	
	
	public function __construct()
	{
		$Register = Register::getInstance();
		$this->Model = $Register['ModManager']->getModelInstance('Pages');
	}
	
	public function get_children($params)
	{
		return '[{"attr":{"id":"node_2","rel":"drive"},"data":"C:","state":""},{"attr":{"id":"node_6","rel":"drive"},"data":"D:","state":"closed"}]';
		$out = array();
		if (empty($params['id'])) return json_encode($out);
		
		//$tree = $this->Model->getTree($params['id']);
		$tree = $this->Model->getCollection(array());
		foreach($tree as $k => $v){
			$out[] = array(
				"attr" => array("id" => "node_".$k, "rel" => $v->getName()),
				"data" => $v->getName(),
				"state" => true ? "closed" : ""
			);
		}
		return json_encode($out);
	}
}



$jstree = new PagesAdminController;
if(!empty($_REQUEST['operation']) && strpos($_REQUEST['operation'], '_') !== 0 && method_exists($jstree, $_REQUEST['operation'])) {
	header("HTTP/1.0 200 OK");
	header('Content-type: application/json; charset=utf-8');
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Pragma: no-cache");
	echo $jstree->{$_REQUEST["operation"]}($_REQUEST);
	die();
}
 





/**
 * Get array with tree ierarhy
 */
function buildPagesTree($pages, $tree = array())
{
	if (!empty($tree)) {
		foreach ($tree as $tk => $tv) {
		
		
			$sub = array();
			foreach ($pages as $pk => $pv) {
			
			
				$path = $tv->getPath();
				if ('.' === $path) $path = '';
				if ($pv->getPath() === $path . $tv->getId() . '.') {
					unset($pages[$pk]);
					$sub[] = $pv;
				}
			}
			if (!empty($sub)) $sub = buildPagesTree($pages, $sub);
			$tv->setSub($sub);
		}
		
		
	} else {
		$lowest = false;
		foreach ($pages as $pk => $pv) {
			$path = $pv->getPath();
			if (false === $lowest || substr_count($path, '.') < $lowest) {
				$lowest = $path;
			}
		}
		
		
		if (false !== $lowest) {
			foreach ($pages as $k => $page) {
				if ($lowest === $page->getPath()) {
					unset($pages[$k]);
					$tree[] = $page;
				}
			}

			$tree = buildPagesTree($pages, $tree);
		}
	}
	
	return $tree;
}


function buildTreeList($tree, $out = null)
{
	foreach ($tree as $k => $line) {
		$last = ($k == (count($tree) - 1)) ? ' last' : '';
		$out .= '<li id="' . $line->getId() . '" class="closed' . $last . '"><span class="file">' 
			. '<a href="page.php?id=' . $line->getId() . '">' . h($line->getName()) . '</a></span>';
		$sub = $line->getSub();
		if (!empty($sub) && is_array($sub)) {
			$out .= '<ul id="folder21"><li></li>';
			$out = buildTreeList($sub, $out);
			$out .= '</ul>';
		}
		$out .= '</li>';
	}
	return $out;
}




$Register = Register::getInstance();
$FpsDB = $Register['DB'];
$pageTitle = 'Редактор страниц';
$pageNav = $pageTitle;
$pageNavl = 'Страницы &raquo; [редактирование]';





$pagesModel = $Register['ModManager']->getModelInstance('Pages');
$page = array(
	'id' => null, 
	'name' => null, 
	'template' => null, 
	'content' => null, 
	'url' => null, 
	'meta_keywords' => null, 
	'meta_description' => null, 
	'parent_id' => null, 
	'path' => null, 
	'visible' => null,
);



// Ajax rebuild tree. Replace element of tree
if (isset($_REQUEST['ajax']) && !empty($_REQUEST['id']) && !empty($_REQUEST['p'])) {
	$pagesModel->replace(intval($_REQUEST['id']), intval($_REQUEST['p']));
	die('OK');
}







$tree = $pagesModel->getCollection(array(), array('order' => 'path'));
$other = $tree;
$tree = buildPagesTree($tree);


if (!empty($_REQUEST['id'])) {
	$page_source = $pagesModel->getById(intval($_REQUEST['id']));
	if (empty($page_source)) {
		$_SESSION['info_message'] = 'Страница с таким ID не найдена';
	} else {
		$page = $page_source->asArray();
		$other = $pagesModel->getOtherTrees($page_source->getId());
	}
}



// Update or add new page
if (!empty($_POST)) {
	if (!empty($_POST['id'])) { // Edit old record
		$page = array_merge($page, $_POST);
		$entity = new PagesEntity($page);
		$entity->save();
		
		if ($_POST['parent_id'] != $entity->getParent_id()) {
			$pagesModel->replace($entity->getId(), $_POST['parent_id']);
		}

	
	} else { // Add new record
		$data = array_merge($page, $_POST);
		if (isset($data['id'])) unset($data['id']);
		$pagesModel->add($data);
	}
}


include_once ROOT . '/admin/template/header.php';
?>


<style type="text/css">

</style>

<div id="demo"></div>


<script type="text/javascript">

$(function () {

$("#demo")
	// Write log
	.bind("before.jstree", function (e, data) {
		$("#alog").append(data.func + "<br />");
	})
	.jstree({ 
		// List of active plugins
		"plugins" : [ 
			"themes","json_data","ui","crrm","cookies","dnd","search","types","hotkeys","contextmenu" 
		],

		// I usually configure the plugin that handles the data first
		// This example uses JSON as it is most common
		"json_data" : { 
			// This tree is ajax enabled - as this is most common, and maybe a bit more complex
			// All the options are almost the same as jQuery's AJAX (read the docs)
			"ajax" : {
				// the URL to fetch the data
				"url" : "page.php",
				// the `data` function is executed in the instance's scope
				// the parameter is the node being loaded 
				// (may be -1, 0, or undefined when loading the root nodes)
				"data" : function (n) { 
					// the result is fed to the AJAX request `data` option
					return { 
						"operation" : "get_children", 
						"id" : n.attr ? n.attr("id").replace("node_","") : 1 
					}; 
				}
			}
		},
		// Configuring the search plugin
		"search" : {
			// As this has been a common question - async search
			// Same as above - the `ajax` config option is actually jQuery's AJAX object
			"ajax" : {
				"url" : "page.php",
				// You get the search string as a parameter
				"data" : function (str) {
					return { 
						"operation" : "search", 
						"search_str" : str 
					}; 
				}
			}
		},
		// Using types - most of the time this is an overkill
		// read the docs carefully to decide whether you need types
		"types" : {
			// I set both options to -2, as I do not need depth and children count checking
			// Those two checks may slow jstree a lot, so use only when needed
			"max_depth" : -2,
			"max_children" : -2,
			// I want only `drive` nodes to be root nodes 
			// This will prevent moving or creating any other type as a root node
			"valid_children" : [ "drive" ],
			"types" : {
				// The default type
				"default" : {
					// I want this type to have no children (so only leaf nodes)
					// In my case - those are files
					"valid_children" : "none",
					// If we specify an icon for the default type it WILL OVERRIDE the theme icons
					"icon" : {
						"image" : "/static/v.1.0pre/_demo/file.png"
					}
				},
				// The `folder` type
				"folder" : {
					// can have files and other folders inside of it, but NOT `drive` nodes
					"valid_children" : [ "default", "folder" ],
					"icon" : {
						"image" : "/static/v.1.0pre/_demo/folder.png"
					}
				},
				// The `drive` nodes 
				"drive" : {
					// can have files and folders inside, but NOT other `drive` nodes
					"valid_children" : [ "default", "folder" ],
					"icon" : {
						"image" : "/static/v.1.0pre/_demo/root.png"
					},
					// those prevent the functions with the same name to be used on `drive` nodes
					// internally the `before` event is used
					"start_drag" : false,
					"move_node" : false,
					"delete_node" : false,
					"remove" : false
				}
			}
		},
		// UI & core - the nodes to initially select and open will be overwritten by the cookie plugin

		// the UI plugin - it handles selecting/deselecting/hovering nodes
		"ui" : {
			// this makes the node with ID node_4 selected onload
			"initially_select" : [ "node_4" ]
		},
		// the core plugin - not many options here
		"core" : { 
			// just open those two nodes up
			// as this is an AJAX enabled tree, both will be downloaded from the server
			"initially_open" : [ "node_2" , "node_3" ] 
		}
	})
	.bind("create.jstree", function (e, data) {
		$.post(
			"page.php", 
			{ 
				"operation" : "create_node", 
				"id" : data.rslt.parent.attr("id").replace("node_",""), 
				"position" : data.rslt.position,
				"title" : data.rslt.name,
				"type" : data.rslt.obj.attr("rel")
			}, 
			function (r) {
				if(r.status) {
					$(data.rslt.obj).attr("id", "node_" + r.id);
				}
				else {
					$.jstree.rollback(data.rlbk);
				}
			}
		);
	})
	.bind("remove.jstree", function (e, data) {
		data.rslt.obj.each(function () {
			$.ajax({
				async : false,
				type: 'POST',
				url: "page.php",
				data : { 
					"operation" : "remove_node", 
					"id" : this.id.replace("node_","")
				}, 
				success : function (r) {
					if(!r.status) {
						data.inst.refresh();
					}
				}
			});
		});
	})
	.bind("rename.jstree", function (e, data) {
		$.post(
			"/static/v.1.0pre/_demo/server.php", 
			{ 
				"operation" : "rename_node", 
				"id" : data.rslt.obj.attr("id").replace("node_",""),
				"title" : data.rslt.new_name
			}, 
			function (r) {
				if(!r.status) {
					$.jstree.rollback(data.rlbk);
				}
			}
		);
	})
	.bind("move_node.jstree", function (e, data) {
		data.rslt.o.each(function (i) {
			$.ajax({
				async : false,
				type: 'POST',
				url: "/static/v.1.0pre/_demo/server.php",
				data : { 
					"operation" : "move_node", 
					"id" : $(this).attr("id").replace("node_",""), 
					"ref" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace("node_",""), 
					"position" : data.rslt.cp + i,
					"title" : data.rslt.name,
					"copy" : data.rslt.cy ? 1 : 0
				},
				success : function (r) {
					if(!r.status) {
						$.jstree.rollback(data.rlbk);
					}
					else {
						$(data.rslt.oc).attr("id", "node_" + r.id);
						if(data.rslt.cy && $(data.rslt.oc).children("UL").length) {
							data.inst.refresh(data.inst._get_parent(data.rslt.oc));
						}
					}
					$("#analyze").click();
				}
			});
		});
	});

});



</script>

<script type="text/javascript">
	redactor = $(document).ready(
		function()
		{
			redactor = $('#mainTextarea').redactor({
				css: 'redactor.css', 
				autoclear: false, 
				autoformat: false, 
				convert_links: false, 
				init_clear: false,
				remove_styles: false,
				remove_classes: false,
				image_upload: "/img_uploader.php",
			});
		}
	);
</script>


<?php
if (!empty($_SESSION['info_message'])):
?>
<script type="text/javascript">showHelpWin('<?php echo h($_SESSION['info_message']) ?>', 'Сообщение');</script>
<?php
	unset($_SESSION['info_message']);
endif;
?>


<div class="white">
	<div style="float:left; width:20%;">
		<div class="tree-wrapper">
			
			<ul id="pages-list" class="filetree">
				<?php echo buildTreeList($tree); ?>
			</ul>
		</div>
	</div>
	<div style="width:80%; float:left;">
		<form method="POST" action="">
		<table class="settings-tb">

		<tr><td class="left">Заголовок:</td>
		<td><input type="text" name="title" value="<?php echo h($page['name']) ?>">
		<input type="hidden" name="id" value="<?php echo intval($page['id']) ?>"></td></tr>

		<tr><td class="left">URL:<br></td>
		<td><input type="text" name="url" value="<?php echo h($page['url']) ?>"></td></tr>

		<tr><td class="left">Родитель:<br></td>
		<td>
			<select name="parent_id">
				<option value="0"></option>
				<?php foreach ($other as $item): ?>
				<option <?php if ($item->getId() === $page['parent_id']) echo 'selected="selected"'; ?> value="<?php echo $item->getId() ?>"><?php echo h($item->getName()) ?></option>
				<?php endforeach; ?>
			</select>
		</td></tr>

		<tr><td class="left">Видна ли в меню:<br></td>
		<td><input type="checkbox" name="visible" value="1" <?php if ($page['visible']) echo 'checked="checked"' ?>"></td></tr>
		
		<tr><td class="left">Keywords:<br></td>
		<td><input type="text" name="meta_keywords" value="<?php echo h($page['meta_keywords']) ?>"></td></tr>
		
		<tr><td class="left">Description:<br></td>
		<td><input type="text" name="meta_description" value="<?php echo h($page['meta_description']) ?>"></td></tr>
		
		<tr><td class="left">Шаблон:<br></td>
		<td><input type="text" name="template" value="<?php echo h($page['template']) ?>"></td></tr>
		

		<tr><td colspan="2" class="left">
			<textarea id="mainTextarea" name="content"><?php echo $page['content'] ?></textarea>
		</td></tr>

		<tr><td colspan="2" align="center"><input type="submit" name="send" value="Сохранить"><br></td></tr>

		</table>
		</form>
	</div>
	<div class="clear"></div>
</div>








<table class="lines">
	<tr>
		<td>
			<div>
			<ul class="uz">
				<li><div class="global-marks">{{ content }}</div> - Основной контент страницы</li>
				<li><div class="global-marks">{{ title }}</div> - Заголовок страницы</li>
				<li><div class="global-marks">{{ description }}</div> - Содержание Мета-тега description</li>
				<li><div class="global-marks">{{ fps_wday }}</div> - День кратко</li>
				<li><div class="global-marks">{{ fps_date }}</div> - Дата</li>
				<li><div class="global-marks">{{ fps_time }}</div> - Время</li>
				<li><div class="global-marks">{{ headmenu }}</div> - Верхнее меню</li>
				<li><div class="global-marks">{{ fps_user_name }}</div> - Ник текущего пользователя (Для не авторизованного - Гость)</li>
				<li><div class="global-marks">{{ fps_user_group }}</div> - Группа текущего пользователя (Для не авторизованного - Гости)</li>
				<li><div class="global-marks">{{ categories }}</div> - Список категорий раздела</li>
				<li><div class="global-marks">{{ counter }}</div> - Встроенный счетчик посещаемости CMS Fapos</li>
				<li><div class="global-marks">{{ fps_year }}</div> - Год</li>
				<li><div class="global-marks">{{ powered_by }}</div> - CMS Fapos</li>
				<li><div class="global-marks">{{ comments }}</div> - Комментарии к материалу и форма добавления комментариев <b>(если предусмотренно)</b></li>
				<li><div class="global-marks">{{ personal_page_link }}</div> - URL на свою персональную страницу или на страницу регистрации, если не авторизован</li>
			</ul>
			</div>
		</td>
	</tr>
</table>



<?php
include_once 'template/footer.php';
