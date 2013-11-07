<?php
$obj_votes = new BannerxSettings;


if (!empty($_GET['plac'])) {
	switch ($_GET['plac']) {
		case 'delete':
			$obj_votes->delete();
			break;
			
		case 'add':
			$obj_votes->add();
			break;
			
		case 'edit':
			$obj_votes->edit();
			break;
	}
}


$output = $obj_votes->pllist();







class BannerxSettings {
	
	private $path;
	private $templ_path;
	
	
	
	
	public function __construct() {
		$this->path = dirname(__FILE__) . '/';
		$this->templ_path = dirname(__FILE__) . '/template/';
	}
	
	
	public function add() {
		if (empty($_GET['dir'])) redirect('admin/plugins.php');
		$dir = $_GET['dir'];
		
		$_SESSION['errors'] = false;
		
	
		$db_path = $this->path . 'db.dat';
		$banners = (file_exists($db_path)) ? json_decode(file_get_contents($db_path), true) : array();
	
	
		// get title and url
		$title = (!empty($_POST['title'])) ? $_POST['title'] : '';
		$url = (!empty($_POST['url'])) ? $_POST['url'] : '';
		$description = (!empty($_POST['description'])) ? $_POST['description'] : '';
		
		
		// save image
		if (!empty($_FILES['img'])) {
			if ($_FILES['img']['type'] != 'image/jpeg' &&
			$_FILES['img']['type'] != 'image/gif' &&
			$_FILES['img']['type'] != 'image/bmp' &&
			$_FILES['img']['type'] != 'image/png') {
				$_SESSION['message'] = 'Не верный формат файла';
			}
			
			
			$ext_ = strrchr($_FILES['img']['name'], ".");
			$ext = strtolower($ext_);
			
			// get escaped name
			$img_name_escaped = str_replace('.' . $ext_, '', $_FILES['img']['name']);
			$img_name_escaped = strtolower(preg_replace('#[^a-z0-9]#i', '_', $img_name_escaped));
			$img_name_escaped .= $ext;
			
			// save
			$save_path = ROOT . '/sys/img/' . $img_name_escaped;
			if (@!move_uploaded_file($_FILES['img']['tmp_name'], $save_path))
				$_SESSION['message'] = 'Не удалось загрузить файл на сервер';
		}
		
		
		// get ID
		if (!empty($banners)) {
			$id = max(array_keys($banners)) + 1;
		} else {
			$id = 1;
		}
		

		
		if (empty($_SESSION['message'])) {
			$banner = array(
				'url' => $url,
				'title' => $title,
				'img' => $img_name_escaped,
				'id' => $id,
				'description' => $description,
			);
		
		
			$banners[$id] = $banner;
			file_put_contents($db_path, json_encode($banners));
			$_SESSION['message'] = 'Сохранено';
		}
		
		redirect('admin/plugins.php?ac=edit&dir=' . $dir);
	}

	
	public function delete() {
		
		if (empty($_GET['dir'])) redirect('admin/plugins.php');
		$dir = $_GET['dir'];
		$id = (!empty($_GET['id'])) ? $_GET['id'] : 0;
		if (empty($id)) redirect('admin/plugins.php?ac=edit&dir=' . $dir);
		
	
		$db_path = $this->path . 'db.dat';
		$banners = (file_exists($db_path)) ? json_decode(file_get_contents($db_path), true) : array();
		
		if (!empty($banners) && count($banners) > 0) {
			foreach ($banners as $key => $value) {
				if ($key == $id) {
					
					
					if (!empty($value['img'])) {
						$img_path = ROOT . '/sys/img/' . $value['img'];
						if (file_exists($img_path)) @unlink($img_path);
					}
					
					
					unset($banners[$key]);
					break;
				}
			}
		}
		
		file_put_contents($db_path, json_encode($banners));
		redirect('admin/plugins.php?ac=edit&dir=' . $dir);
	}
	
	
	public function edit() {
		if (empty($_GET['dir'])) redirect('admin/plugins.php');
		$dir = $_GET['dir'];
		$id = (!empty($_POST['id'])) ? $_POST['id'] : 0;
		if (empty($id)) redirect('admin/plugins.php?ac=edit&dir=' . $dir);
		
		
		$db_path = $this->path . 'db.dat';
		$banners = (file_exists($db_path)) ? json_decode(file_get_contents($db_path), true) : array();
		
		
		if (!array_key_exists($id, $banners)) {
			$_SESSION['message'] = 'Баннер с таким ID не найден';
			redirect('admin/plugins.php?ac=edit&dir=' . $dir);
		}
		
		
		// get title and url
		$title = (!empty($_POST['title'])) ? $_POST['title'] : '';
		$url = (!empty($_POST['url'])) ? $_POST['url'] : '';
		$description = (!empty($_POST['description'])) ? $_POST['description'] : '';
		
		
		// save image
		if (!empty($_FILES['img'])) {
			if ($_FILES['img']['type'] != 'image/jpeg' &&
			$_FILES['img']['type'] != 'image/gif' &&
			$_FILES['img']['type'] != 'image/bmp' &&
			$_FILES['img']['type'] != 'image/png') {
				$_SESSION['message'] = 'Не верный формат файла';
			}
			
			
			$ext_ = strrchr($_FILES['img']['name'], ".");
			$ext = strtolower($ext_);
			
			// get escaped name
			$img_name_escaped = str_replace('.' . $ext_, '', $_FILES['img']['name']);
			$img_name_escaped = strtolower(preg_replace('#[^a-z0-9]#i', '_', $img_name_escaped));
			$img_name_escaped .= '.' . $ext;
			
			// save
			$save_path = ROOT . '/sys/img/' . $img_name_escaped;
			if (@!move_uploaded_file($_FILES['img']['tmp_name'], $save_path))
				$_SESSION['message'] = 'Не удалось загрузить файл на сервер';
		}
		
		
		if (empty($_SESSION['message'])) {
			if (!empty($title)) $banners[$id]['title'] = $title;
			if (!empty($url)) $banners[$id]['url'] = $url;
			if (!empty($description)) $banners[$id]['description'] = $description;
			if (!empty($img_name_escaped)) $banners[$id]['img'] = $img_name_escaped;
			
		
			file_put_contents($db_path, json_encode($banners));
			$_SESSION['message'] = 'Сохранено';
		}
		
		redirect('admin/plugins.php?ac=edit&dir=' . $dir);
	}
	
	
	public function pllist() {
		$tpl = file_get_contents($this->templ_path . 'list.html');
		$config = $this->path . 'config.dat';
		$db = $this->path . 'db.dat';
		
		$settings = json_decode(file_get_contents($config), true);
		$banners = (file_exists($db)) ? json_decode(file_get_contents($db), true) : array();
		$dir = trim(strrchr(dirname(__FILE__), DS), DS);

		$Viewer = new Fps_Viewer_Manager;
		
	
		$context = array(
			'banners' => $banners,
			'dir' => $dir,
		);
		
		
		if (!empty($_SESSION['message'])) {
			$context['message'] = $_SESSION['message'];
			unset($_SESSION['message']);
		}
		
		
		return $Viewer->parseTemplate($tpl, array('context' => $context));
	}

}
