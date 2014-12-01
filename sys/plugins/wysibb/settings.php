<?php

function cmpTitle($a, $b) {
	$a = isset($a['title']) ? $a['title'] : '';
	$b = isset($b['title']) ? $b['title'] : '';
    if ($a === $b) return 0;
    return ($a < $b) ? -1 : 1;
}

$markers = array(
	'editor_head' => 'HTML-код, добавляемый в &lt;HEAD&gt;',
	'editor_body' => 'HTML-код, добавляемый в &lt;BODY&gt;',
	'editor_buttons' => 'HTML-код панели кнопок',
	'editor_text' => 'HTML-код поля ввода',
	'editor_forum_text' => 'HTML-код поля ввода для форума',
	'editor_forum_quote' => 'HTML-код кнопки "цитировать" для форума',
	'editor_forum_name' => 'HTML-код кнопки добавления имени пользователя',
);

$editor_set = array();

include 'config.php';

if (isset($_POST['ac'])) {
	$editor = array('title' => isset($_POST['title']) ? trim($_POST['title']) : '');
	foreach ($markers as $marker => $value) {
		$editor[$marker] = isset($_POST[$marker]) ? trim($_POST[$marker]) : '';
	}
	
	switch (strtolower(trim($_POST['ac']))) {
		case 'set':
			$number = isset($_POST['number']) ? intval(trim($_POST['number'])) : null;
			if (isset($number) && $editor_set && is_array($editor_set)) {
				foreach ($editor_set as $index => $editor) {
					$editor['default'] = ($index == $number);
					$editor_set[$index] = $editor;
				}
			}
			break;
		case 'new':
			$editor_set[] = $editor;
			break;
		case 'edit':
			$number = isset($_POST['number']) ? intval(trim($_POST['number'])) : null;
			if (isset($number)) {
				if (isset($editor_set[$number])) $editor['default'] = $editor_set[$number]['default'];
				$editor_set[$number] = $editor;
			} else {
				$editor_set[] = $editor;
			}
			break;
		case 'del':
			$number = isset($_POST['number']) ? intval(trim($_POST['number'])) : null;
			if (isset($number)) {
				unset($editor_set[$number]);
			}
			break;
		default:
	}
	usort($editor_set, "cmpTitle");
	$fopen = @fopen(dirname(__FILE__) . '/config.php', 'w');
	if ($fopen) {
		fputs($fopen, '<?php ' . "\n" . '$editor_set = ' . var_export($editor_set, true) . ";\n" . '?>');
		fclose($fopen);
	}
}

$popups_content = '<div class="popup" id="addEditor" style="display: none;">
	<div class="top">
		<div class="title">Добавление редактора</div>
		<div class="close" onclick="closePopup(\'addEditor\');"></div>
	</div>
	<form method="POST" action="">
	<div class="items">
		<div class="item">' . __('Title') . ':<br />
			<input type="text" name="title" style="width:95%" />
			<input type="hidden" name="ac" value="new" />
		<div class="clear"></div></div>';


foreach ($markers as $marker => $value) {
	$popups_content .= '<div class="item">
		Маркер {{ ' . $marker . ' }} - 
		' . $value . ':<br />';
	$popups_content .= ($marker == 'editor_text' || $marker == 'editor_forum_text' || $marker == 'editor_forum_quote' || $marker == 'editor_forum_name') ? 
		'<input type="text" name="' . $marker . '" style="width:95%" />' :
		'<textarea name="' . $marker . '" cols="30" rows="3" style="width:95%" /></textarea>';
	$popups_content .= '<div class="clear"></div></div>';
}
	
$popups_content .= '<div class="item submit">
			<div class="left"></div>
			<div style="float:left;" class="right">
				<input type="submit" class="save-button" name="send" value="Сохранить">
			</div>
			<div class="clear"></div>
		</div>
	</div>
	</form>
</div>';


$output = '
	<div class="list">
		<div class="title">Управление редакторами</div>
		<div onclick="openPopup(\'addEditor\');" class="add-cat-butt"><div class="add"></div>Добавить редактор</div>';

if ($editor_set && is_array($editor_set) && count($editor_set)) {
	$output .= '
		<form name="deleteEditor" action="" method="POST">
			<input type="hidden" name="ac" value="del" />
			<input type="hidden" name="number" value="" />
		</form>
		<form name="setEditor" action="" method="POST">
			<input type="hidden" name="ac" value="set" />
			<input type="hidden" name="number" value="" />
		</form>
		<div class="level1">
			<div class="items">';
	foreach ($editor_set as $index => $editor) {
		$output .= '<div class="level2">
					<div class="title">' . (isset($editor['title']) && strlen($editor['title']) > 0 ? h($editor['title']) : 'Редактор ' . $index) . '</div>
					<div class="buttons">
						<a class="edit" onclick="openPopup(\'editEditor' . $index . '\')" href="javascript://"></a>
						<a class="' . ((isset($editor['default']) && $editor['default']) ? 'on' : 'off') . '" onclick="document.forms[\'setEditor\'].number.value=' . $index . ';document.forms[\'setEditor\'].submit();" href="javascript://"></a>
						<a class="delete" onclick="if (_confirm()) {document.forms[\'deleteEditor\'].number.value=' . $index . ';document.forms[\'deleteEditor\'].submit();};" href="javascript://"></a>
					</div>
				</div>';
		$popups_content .= '<div class="popup" id="editEditor' . $index . '">
	<div class="top">
		<div class="title">Настройка редактора</div>
		<div class="close" onclick="closePopup(\'editEditor' . $index . '\');"></div>
	</div>
	<form method="POST" action="">
	<div class="items">
		<div class="item">' . __('Title') . ':<br />
			<input type="text" name="title" value="' . (isset($editor['title']) ? htmlspecialchars($editor['title']) : '') . '" style="width:95%" />
			<input type="hidden" name="ac" value="edit" />
			<input type="hidden" name="number" value="' . $index . '" />
		<div class="clear"></div></div>';

		foreach ($markers as $marker => $value) {
			$popups_content .= '<div class="item">
				Маркер {{ ' . $marker . ' }} - 
				' . $value . ':<br />';
			$popups_content .= ($marker == 'editor_text' || $marker == 'editor_forum_text' || $marker == 'editor_forum_quote' || $marker == 'editor_forum_name') ? 
				'<input type="text" name="' . $marker . '" style="width:95%" value="' . (isset($editor[$marker]) ? htmlspecialchars($editor[$marker]) : '') . '" />' :
				'<textarea name="' . $marker . '" cols="30" rows="3" style="width:95%" />' . (isset($editor[$marker]) ? htmlspecialchars($editor[$marker]) : '') . '</textarea>';
			$popups_content .= '<div class="clear"></div></div>';
		}
	
		$popups_content .= '<div class="item submit">
			<div class="left"></div>
			<div style="float:left;" class="right">
				<input type="submit" class="save-button" name="send" value="Сохранить">
			</div>
			<div class="clear"></div>
		</div>
	</div>
	</form>
</div>';
	}
}
$output .= '</div></div>';
$output = $popups_content . $output;

?>