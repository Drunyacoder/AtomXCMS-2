<?php
$obj_votes = new VotesSettings;
if (isset($_GET['plac']) && $_GET['plac'] === 'delete') {
	$obj_votes->delete();
} else if (isset($_GET['plac']) && $_GET['plac'] === 'add') {
	$obj_votes->add();
} else if (isset($_GET['plac']) && $_GET['plac'] === 'edit') {
	$obj_votes->edit();
}
$output = $obj_votes->pllist();







class VotesSettings {
	
	private $path;
	private $templ_path;
	
	
	
	
	public function __construct() {
		$this->path = dirname(__FILE__) . '/';
		$this->templ_path = dirname(__FILE__) . '/template/';
	}
	
	
	public function add() {
		if (empty($_GET['dir'])) redirect('admin/plugins.php');
		$dir = $_GET['dir'];
		
	
		$votes_pach = $this->path . 'votes_history.dat';
		$history = (file_exists($votes_pach)) ? unserialize(file_get_contents($votes_pach)) : array();
	
		$title = (!empty($_POST['title'])) ? $_POST['title'] : 'Unknown';
		$vars = (count($_POST['variant']) > 0) ? $_POST['variant'] : array();
		$fvars = array();
		foreach ($vars as $k => $variant) {
			$fvars[$variant] = 0;
		}
		unset($vars);
		if (empty($fvars)) $_SESSION['errors'] = '<li>Не заполненны варианты ответа</li>';
		
		
	
		$history[$title] = $fvars;
		file_put_contents($votes_pach, serialize($history));
		
		
		redirect('admin/plugins.php?ac=edit&dir=' . $dir);
	}


	
	public function delete() {
		if (empty($_GET['dir'])) redirect('admin/plugins.php');
		$dir = $_GET['dir'];
		$id = (!empty($_GET['id'])) ? $_GET['id'] : 0;
		if (empty($id)) redirect('admin/plugins.php');
		
	
		$votes_pach = $this->path . 'votes_history.dat';
		$history = (file_exists($votes_pach)) ? unserialize(file_get_contents($votes_pach)) : array();
	
		if (!empty($history) && count($history) > 0) {
			foreach ($history as $key => $value) {
				if ($key === $id) {
					unset($history[$key]);
					break;
				}
			}
		}
		file_put_contents($votes_pach, serialize($history));
		redirect('admin/plugins.php?ac=edit&dir=' . $dir);
	}
	
	
	public function edit() {
		if (empty($_GET['dir']) || empty($_GET['id'])) redirect('admin/plugins.php');
		$history_file = R . 'sys/plugins/' . $_GET['dir'] . '/votes_history.dat';
		if (!file_exists($history_file)) redirect('admin/plugins.php');
		
		$votes = unserialize(file_get_contents($history_file));
		
		$errors = '';
		if (empty($_POST['title'])) $errors .= '<li>Заполните заголовок</li>';
		if (empty($_POST['variant']) || count($_POST['variant']) < 1) $errors .= '<li>Заполните варианты ответа</li>';
		if (!empty($errors)) {
			$_SESSION['form_errors'] = '<ul class="error">' . $errors . '</ul>';
			redirect('admin/plugins.php?ac=edit&dir=' . $_GET['dir']);
		}
		
		$title = $_POST['title'];
		$oldtitle = (!empty($_POST['oldval'])) ? html_entity_decode($_POST['oldval']) : '';
		$variants = array();
		foreach ($_POST['variant'] as $var) {
			if (!empty($votes[$oldtitle]) && !empty($votes[$oldtitle][$var])) {
				$variants[$var] = $votes[$oldtitle][$var];
				continue;
			}
			$variants[$var] = 0;
		}
		
		// Create array for saving
		$out = array();
		if (!empty($votes) && is_array($votes)) {
			if (!empty($votes[$oldtitle])) {
				unset($votes[$oldtitle]);
			}
		}
		$votes[$title] = $variants;
		file_put_contents($history_file, serialize($votes));
		
		
		redirect('admin/plugins.php?ac=edit&dir=' . $_GET['dir']);
	}
	
	
	public function pllist() {
		$content = file_get_contents($this->templ_path . 'settings.html');
		$set_pach = $this->path . 'config.dat';
		$votes_pach = $this->path . 'votes_history.dat';
		
		$settings = unserialize(file_get_contents($set_pach));
		$history = (file_exists($votes_pach)) ? unserialize(file_get_contents($votes_pach)) : array();
		$set_fields = '';
		$dir = trim(strrchr(dirname(__FILE__), DS), DS);

		
		
		if (empty($settings['adm_settings']) || count($settings['adm_settings']) < 1) {
			$set_fields = '';
		} else {
			$templ = file_get_contents($this->templ_path . 'settings2.html');
			foreach ($settings['adm_settings'] as $name => $val) {
				$templrow = file_get_contents($this->templ_path . 'settings_row2.html');
				$set_fields .= str_replace('{' . strtoupper($name) . '}', h($val), $templrow);
			}
			$set_fields = str_replace('{ROWS}', $set_fields, $templ);
		}
		
		
		
		$out = '';
		$rows = '';
		if (!empty($history) && count($history) > 0) {
			foreach ($history as $key => $vote) {
				$tpl = file_get_contents($this->templ_path . 'settings_row.html');
				$markets = array();
				
				
				// colored ansvers
				$colored_ansv = array();
				foreach ($vote as $k => $v) {
					$colored_ansv[] = '<span style="color:#' . $this->getRandColor() . '">' . $k . '</span>&nbsp;(' . $v . ')';
				}
				
				
				$markets['{QUESTION}'] = (!empty($key)) ? h($key) : 'Unknown';
				$markets['{VARIABLES}'] = implode('<br />', $colored_ansv);
				$markets['{VOTED}'] = 'Всего голосов: ' . array_sum($vote);
				$markets['{ACTION}'] = '<a class="edit" href="javascript:void(0)" onClick="openPopup(\'' . md5($key) . '\')"></a>&nbsp;
				<a class="delete" href="plugins.php?ac=edit&id=' . h($key) . '&dir=' . h($dir) . '&plac=delete'  
				. '"></a>';
				
				

				$out .= '<div id="' . md5($key) . '" class="popup">
					<div class="top">
						<div class="title">Редактирование голосования</div>
						<div onClick="closePopup(\'' . md5($key) . '\');" class="close"></div>
					</div>
					<form action="plugins.php?ac=edit&plac=edit&dir=' . $dir . '&id=' . urlencode($key) . '" method="POST">
					<div class="items">
						<div class="item">
							<div class="left">
								Вопрос голосования:
							</div>
							<div class="right">
								<input type="hidden" name="oldval" value="' . h($key) . '" />
								<input type="text" style="width:230px" name="title" value="' . h($key) . '" />
							</div>
							<div class="clear"></div>
						</div>
						<div id="' . md5($key) . 'addfields">';
						
						
						$n = 1;
						foreach ($vote as $vot => $cnt_ansv) {
							$out .= '<div class="item"><div class="left">
								Вариант ' . $n . ':</div><div class="right">
								<input class="' . md5($key) . '_fps-votes-fields" type="text" style="width:230px" name="variant[]" value="' . h($vot) . '" /><br />
								</div><div class="clear"></div></div>';
							$n++;
						}
						
						$out .= '</div>
						<div class="form-item2">
						<a onClick="addField(\'' . md5($key) . '\')" href="javascript:void(0)">Добавить ответ</a><br />
						<div style="clear:both;"></div></div>
						<div class="item submit">
							<div class="left"></div>
							<div class="right" style="float:left;">
								<input type="submit" value="Save" name="send" class="save-button" />
							</div>
							<div class="clear"></div>
						</div>
					</div>
					</form>
				</div>';

				
				
				$rows .= str_replace(array_keys($markets), $markets, $tpl);
			}
		}
		
		
		if (empty($set_fields) && empty($out)) {
			$rows = file_get_contents($this->templ_path . 'error.html');
		}
		

		$content = str_replace(array('{ROWS}', '{SETTINGS}', '{PLDIR}', '{UNIQID}')
		, array($rows, $set_fields, $dir, md5(rand())), $content);
		$content .= $out;
		
		if (!empty($_SESSION['form_errors'])) {
			$content .= '<script type="text/javascript">showHelpWin(\'' . $_SESSION['form_errors'] . '\', \'Ошибки\');</script>';
			unset($_SESSION['form_errors']);
		}
		
		return $content;
	}
	
	
	private function getRandColor() {
		$colors = array(
			'7F9DB9',
			'2144DA',
			'A1523A',
			'0B6C24',
			'E3BF6A',
			'0A737E',
			'FF0000',
		);
		$rand = rand(0, 6);
		return $colors[$rand];
	}
	
	
}
?>