<?php
$obj = new CategoryViewer;
$output = $obj->edit();




class CategoryViewer {
	
	private $config;
	
	
	public function __construct() {
		$this->config = dirname(__FILE__) . '/config.dat';
	}
	
	
	public function edit() {
		if (empty($_GET['dir'])) redirect('admin/plugins.php');
		$config = json_decode(file_get_contents($this->config), true);
		
		$templates = glob(ROOT . '/template/*', GLOB_ONLYDIR);
		
		if ($templates) {
			foreach ($templates as &$t) {
				$t = substr(strrchr($t, '/'), 1);
			}
		}
		
		if (!empty($_POST)) {
			$errors = '';
			if (empty($_POST['template']) || !in_array($_POST['template'], $templates)) 
				$errors .= '<li>Выберите корректный шаблон</li>';
			if (!empty($errors)) {
				$_SESSION['form_errors'] = '<ul class="error">' . $errors . '</ul>';
				redirect('admin/plugins.php?ac=edit&dir=' . $_GET['dir']);
			}
			
			$template = $_POST['template'];
			$categories = (!empty($_POST['categories'])) ? trim($_POST['categories']) : '';
			$categories = explode(',',$_POST['categories']);
			$categories = array_filter($categories, 'is_numeric');
			
			$config['categories'] = $categories;
			$config['template'] = $template;
			
			file_put_contents($this->config, json_encode($config));
			
			
			redirect('admin/plugins.php?ac=edit&dir=' . $_GET['dir']);
		}

		$content = '';
		$content .= '<form method="POST" action="'.get_url('/admin/plugins.php?ac=edit&dir=' . $_GET['dir']).'" enctype="multipart/form-data">
		<div class="list">
			<div class="title">Дизайн категорий</div>
			<div class="level1">
				<div class="head">
					<div class="title settings">Ключ</div>
					<div class="title-r">Значение</div>
					<div class="clear"></div>
				</div>
				<div class="items">
					<div class="setting-item">
						<div class="left">
							Категории
							<span class="help">*ID категорий через запятую</span>
						</div>
						<div class="right">
							<input class="" type="text" name="categories" value="'.implode(',', $config['categories']).'" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							Шаблон
						</div>
						<div class="right">
							<select name="template">';
			foreach ($templates as $template) {
				
				$active = (!empty($config['template']) && $template === $config['template']) ? ' selected="selected"' : '';
				$content .= "<option$active value=\"$template\">$template</option>";
			}
							
			$content .= '</select>
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
		</form>';
		return $content;
	}
	
}
