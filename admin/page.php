<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      1.5.1                          ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    Admin Panel module             ##
## copyright     ©Andrey Brykin 2010-2013       ##
## @last mod.     2013/04/05                    ##
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
	
	
	public function move_node($params)
	{
		if (intval($params['id']) < 2) return json_encode(array('status' => '0'));
		if (intval($params['ref']) < 1) $params['ref'] = 1;
		
		
		if ($params['copy']) {
			$parent = $this->Model->getById($params['ref']);
			$entity = $this->Model->getById($params['id']);
			$path = ('.' === $entity->getPath()) ? null : $entity->getPath();
			$tree = $this->Model->getCollection(array("`path` LIKE '" . $path . $entity->getId() . ".%'"));
			
			if (!empty($tree)) $tree = $this->buildPagesTree($tree);
			else $tree = array();
			
			$entity->setSub($tree);
			$tree = array($entity);
		
			$new_id = $this->copyNode($tree, $parent);
			return json_encode(array('status' => 1, 'id' => $new_id));
			
		} else {
			$this->Model->replace($params['id'], intval($params['ref']));
			return json_encode(array('status' => 1, 'id' => $params['id']));
		}
			
		return json_encode(array('status' => '0'));
	}
	
	
	private function copyNode($tree, $parent)
	{
		foreach($tree as $k => $v) {
			$path = ('.' === $parent->getPath()) ? null : $parent->getPath();
			$data = clone $v;
			
			$data->setId(false);
			$data->setParent_id($parent->getId());
			$data->setPath($path . $parent->getId() . '.');
			
			$data->save();
			$id = $data->getId();
			
			
			$sub = $v->getSub();
			if (!empty($sub)) {
				foreach($sub as $child) {
					$this->copyNode($child, $data);
				}
			}
		}
		return !empty($id) ? $id : false;
	}
	
	
	public function rename_node($params)
	{
		if (intval($params['id']) < 2 || empty($params['title'])) return json_encode(array('status' => '0'));
		
		$entity = $this->Model->getById($params['id']);
		$entity->setName($params['title']);
		$entity->save();
		return json_encode(array('status' => 1));
	}
	
	
	public function remove_node($params)
	{
		if (intval($params['id']) < 2) return json_encode(array('status' => '0'));
		
		$this->Model->delete($params['id']);
		return json_encode(array('status' => 1));
	}
	
	
	/**
	"operation" : "create_node", 
	"id" : data.rslt.parent.attr("id").replace("node_",""), 
	"position" : data.rslt.position,
	"title" : data.rslt.name,
	"type" : data.rslt.obj.attr("rel") 
	 */
	public function create_node($params)
	{
		if (intval($params['id']) < 1 || empty($params['title'])) return json_encode(array('status' => '0'));
		
		$parent = $this->Model->getById($params['id']);
		if (!empty($parent)) {
		
			$path = ('.' === $parent->getPath()) ? null : $parent->getPath();
			
			$data = array(
				'path' => $path . $parent->getId() . '.',
				'name' => $params['title'],
				'visible' => '1',
				'parent_id' => $params['id'],
			);

			$new_entity = new PagesEntity($data);
			$new_entity->save();
			if ($new_entity->getId()) return json_encode(array(
				'status' => 1,
				'id' => $new_entity->getId(),
			));
		}
		
		return json_encode(array('status' => '0'));
	}
	
	
	/**
	 * 
	 */
	public function get_children($params)
	{
		$out = array();
		if (!isset($params['id'])) return json_encode($out);
		
		
		if (0 != $params['id'])  {
			$parent = $this->Model->getById($params['id']);
			$path = ('.' === $parent->getPath()) ? null : $parent->getPath();
			$tree = $this->Model->getCollection(array("`path` LIKE '" . $path . $parent->getId() . ".%'"));
		
		
			
			if (!empty($tree)) {
				$tree = $this->buildPagesTree($tree);
			
				foreach($tree as $k => $v){
					$out[] = array(
						"attr" => array(
							"id" => "node_".$v->getId(), 
							"rel" => (false != $v->getSub()) ? "drive" : "default",
						),
						"data" => $v->getName(),
						"state" => (false != $v->getSub() || $params['id'] == 0) ? "closed" : ""
					);
				}
			}
		} else {
			$root = array(
				"attr" => array(
					"id" => "node_1", 
					"rel" => "drive",
				),
				"data" => 'root',
				"state" => "closed"
			);
			$out = array($root);
		}
		return json_encode($out);
	}
	
	
	
	/**
	 * Get array with tree ierarhy
	 */
	private function buildPagesTree($pages, $tree = array())
	{
		if (!empty($tree)) {
			foreach ($tree as $tk => $tv) {
			
			
				$sub = array();
				foreach ($pages as $pk => $pv) {
				
				
					$path = $tv->getPath();
					if ('.' === $path) $path = '';
					if ($pv->getPath() === $path . $tv->getId() . '.') {
						unset($pages [$pk]);
						$sub[] = $pv;
					}
				}
				if (!empty($sub)) $sub = $this->buildPagesTree($pages, $sub);
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

				$tree = $this->buildPagesTree($pages, $tree);
			}
		}
		
		return $tree;
	}
	
	
	public function get($params)
	{
		$entity = $this->Model->getById(intval($params['id']))->asArray();
		if (empty($entity)) return json_encode(array('status' => '0'));
		
		return json_encode(array(
			'status' => '1',
			'data' => $entity,
		));
	}
	
	
	/**
	 *
	 */
	public function save($params)
	{
		if (!empty($params['id'])) {
			$id = intval($params['id']);
			$entity = $this->Model->getById(intval($params['id']));
			if (empty($entity)) return json_encode(array('status' => '0'));

			$entity->setName($params['title']);
			$entity->setUrl($params['url']);
			$entity->setVisible(!empty($params['visible']) ? '1' : '0');
			$entity->setMeta_keywords($params['meta_keywords']);
			$entity->setMeta_description($params['meta_description']);
			$entity->setTemplate($params['template']);
			$entity->setContent($params['content']);
			$entity->save();
			
			
		} else {
			$data = array(
				'name' => $params['title'],
				'template' => $params['template'],
				'meta_keywords' => $params['meta_keywords'],
				'meta_description' => $params['meta_description'],
				'content' => $params['content'],
				'url' => $params['url'],
				'template' => $params['template'],
			);
			$id = $this->Model->add($data);

			if (empty($id)) return json_encode(array('status' => '0'));
			return json_encode(array('status' => '1', 'id' => $id));
		}
		

		return json_encode(array('status' => '1', 'id' => $id));
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



$Register = Register::getInstance();
$FpsDB = $Register['DB'];
$pageTitle = 'Редактор страниц';
$pageNav = $pageTitle;
$pageNavr = 'Страницы &raquo; [редактирование]';


$page = array(
	'name' => '',
	'id' => '',
	'url' => '',
	'visible' => '',
	'parent_id' => '',
	'meta_keywords' => '',
	'meta_description' => '',
	'content' => '',
	'template' => '',
	'visible' => '',
);


include_once ROOT . '/admin/template/header.php';
?>


<div class="white">
	<div class="pages-tree">
		<div class="title">Страницы</div>
		<div class="wrapper">
			<div class="tree-wrapper">
				<div id="pageTree"></div>
			</div>
		</div>
		<div style="width:100%;">&nbsp;</div>
	</div>
	
	

	<div style="display:none;" class="ajax-wrapper" id="ajax-loader"><div class="loader"></div></div>
	<form id="FpsForm" style="opacity:1;" method="POST" action="">


	
	<div class="list pages-form">
		<div class="title">Редактор страницы</div>
		<div class="level1">
			<div class="items">
				<div class="setting-item">
					<div class="left">
						Заголовок
					</div>
					<div class="right">
						<input type="text" name="title" value="">
						<input type="hidden" name="id" value="">
					</div>
					<div class="clear"></div>
				</div>
				<div class="setting-item">
					<div class="left">
						URL
					</div>
					<div class="right">
						<input type="text" name="url" value="">
					</div>
					<div class="clear"></div>
				</div>
				<div class="setting-item">
					<div class="left">
						Видна ли в меню
					</div>
					<div class="right">
						<input id="checkbox1" type="checkbox" name="visible" value="1" checked="checked">
						<label for="checkbox1"></label>
					</div>
					<div class="clear"></div>
				</div>
				<div class="setting-item">
					<div class="left">
						Keywords
					</div>
					<div class="right">
						<input type="text" name="meta_keywords" value="">
					</div>
					<div class="clear"></div>
				</div>
				<div class="setting-item">
					<div class="left">
						Description
					</div>
					<div class="right">
						<input type="text" name="meta_description" value="">
					</div>
					<div class="clear"></div>
				</div>
				<div class="setting-item">
					<div class="left">
						Шаблон
					</div>
					<div class="right">
						<input type="text" name="template" value="">
					</div>
					<div class="clear"></div>
				</div>
				<div class="setting-item">
					<div class="center">
						<textarea style="min-height:300px;" id="mainTextarea" name="content"></textarea>
					</div>
					<div class="clear"></div>
				</div>
				<div class="setting-item">
					<div class="left">
					</div>
					<div class="right">
						<input class="save-button" type="submit" name="send" value="Сохранить" />
					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</div>
	</form>
	<div class="clear"></div>

</div>




<script type="text/javascript">
$(document).ready(function(){
	redactor = $('#mainTextarea').redactor({
		css: 'redactor.css', 
		cleanup: false,
		autoclear: false, 
		autoformat: false, 
		convert_links: false, 
		init_clear: false,
		remove_styles: false,
		remove_classes: false,
		imageGetJson: '/img_uploader.php',
		imageUpload: '/img_uploader.php',
		fileUpload: '/img_uploader.php',
		autoresize: true,
	});
	

	
	
	$.validator.addMethod('chars', function(value, element){
		return value.match(/[ \da-z\-_]*/i);
	}, "Don't use special chars");
	$("#FpsForm").validate({
		submitHandler: function(){
			submitForm();
		},
		rules: {
			title: {
				required: true,
				chars: true,
				maxlength: 50,
				minlength: 3,
			},
			url: {
				chars: true,
				maxlength: 30,
				minlength: 2,
			},
			meta_keywords: {
				maxlength: 250,
				minlength: 5,
			},
			meta_description: {
				maxlength: 250,
				minlength: 5,
				chars: true
			},
			template: {
				maxlength: 30,
				minlength: 3,
				chars: true
			},
			content: {
				required: true,
				minlength: 10
			},
		}
	});
});





$(function () {
$("#pageTree")
	// Write log
	.bind("before.jstree", function (e, data) {
		$("#alog").append(data.func + "<br />");
	})
	.jstree({ 
		// List of active plugins
		"themes" : {
			"theme" : "classic",
			"dots" : true,
			"icons" : true,
			"url" : "../sys/js/jstree/themes/classic/style.css"
		},
		"plugins" : [ 
			"themes","json_data","ui","crrm","cookies","dnd","search","types","contextmenu" 
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
						"id" : n.attr ? n.attr("id").replace("node_","") : 0
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
			"valid_children" : [ "drive","default" ],
			"types" : {
				// The default type
				"default" : {
					// I want this type to have no children (so only leaf nodes)
					// In my case - those are files
					//"valid_children" : "none",
					// If we specify an icon for the default type it WILL OVERRIDE the theme icons
					"icon" : {
						"image" : "../sys/js/jstree/img/file.png"
					}
				},
				// The `folder` type
				"folder" : {
					// can have files and other folders inside of it, but NOT `drive` nodes
					"valid_children" : [ "default", "folder" ],
					"icon" : {
						"image" : "../sys/js/jstree/img/folder.png"
					}
				},
				// The `drive` nodes 
				"drive" : {
					// can have files and folders inside, but NOT other `drive` nodes
					"valid_children" : [ "default", "folder" ],
					"icon" : {
						"image" : "../sys/js/jstree/img/root.png"
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
			"page.php", 
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
				url: "page.php",
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
					//$("#analyze").click();
				}
			});
		});
	})
	.bind("select_node.jstree", function (event, data) { 
		// `data.rslt.obj` is the jquery extended node that was clicked
		var id = data.rslt.obj.attr("id").replace("node_","");
		fillForm(id);
	});

});




/**
 * Get entity and fill form fields
 */
function fillForm(id){
	FpsLib.showLoader();
	var form = $('#FpsForm');
	// Clear form fields. After it, we can create new page
	if (id < 2) {
		$(form).find('input[type="text"], input[type="hidden"]').each(function(){
			$(this).val('');
		});
		$(form).find('input[name="visible"]').attr('checked', false);
		$(form).find('textarea[name="content"]').val('');
		$('div.redactor_editor').html('');
		
		FpsLib.hideLoader();
		return;
	}
	
	
	
	
	$.get('page.php?operation=get&id='+id, function(data){
		var status = data.status; data = data.data;
		$(form).find('input[name="title"]').val(data.name);
		$(form).find('input[name="id"]').val(data.id);
		$(form).find('input[name="url"]').val(data.url);
		if (data.visible == 1)
			$(form).find('input[name="visible"]').attr('checked', 'checked');
		else
			$(form).find('input[name="visible"]').attr('checked', false);
		$(form).find('input[name="meta_keywords"]').val(data.meta_keywords);
		$(form).find('input[name="meta_description"]').val(data.meta_description);
		$(form).find('input[name="template"]').val(data.template);
		$(form).find('textarea[name="content"]').val(data.content);
		$('div.redactor_editor').html(data.content); 
		FpsLib.hideLoader();
	});
}

/**
 * Save changes or create page
 */
function submitForm(){
	FpsLib.showLoader();
	var form = $('#FpsForm');
	var id = $(form).find('input[name="id"]').val();
	$.post(
		'page.php?operation=save&id='+id, 
		$(form).serialize(), 
		function(data){
		
			$("#pageTree").jstree('refresh', -1);
			if (typeof data.id != 'undefined') setTimeout('fillForm('+data.id+')', 1000);
			
		
			FpsLib.hideLoader();
		}
	);
}

</script>




<?php
if (!empty($_SESSION['info_message'])):
?>
<script type="text/javascript">showHelpWin('<?php echo h($_SESSION['info_message']) ?>', 'Сообщение');</script>
<?php
	unset($_SESSION['info_message']);
endif;
?>



<ul class="markers">
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




<?php
include_once 'template/footer.php';


