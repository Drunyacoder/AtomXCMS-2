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



include_once '../sys/boot.php';
include_once ROOT . '/admin/inc/adm_boot.php';



$pageTitle = __('Admin panel - DB dump');
$pageNav = $pageTitle;
$pageNavr = '';



if (!empty($_GET['ac']) && $_GET['ac'] == 'make_dump') {

	$res = $FpsDB->query("SHOW TABLES");
	if (!empty($res)) {
		if (!file_exists(ROOT . '/sys/logs/db_backups/')) mkdir(ROOT . '/sys/logs/db_backups/', 0777);
		$fp = fopen(ROOT . "/sys/logs/db_backups/" . date("Y-m-d-H-i") . ".sql", "a" );
		if ($fp) {
			foreach ($res as $table) {
			
			
				$query_fields = $FpsDB->query("SHOW FIELDS FROM " . current($table) . "");
				// Начало создания запроса на создание таблицы   
				$str = "\n\n-- ----------------------------- --\n\n";
				$str .= "CREATE TABLE IF NOT EXISTS `" . current($table) . "` (\n";

				// Массив имен колонок
				$fields = array();
				$k = 0; // Количество извлеченных колонок таблицы до цикла извлечения их из выполненного выше запроса
				$i = 0; // Индекс массива колонок
				// Цикл извлечения информации о колонках из выполненного выше запроса
				foreach ($query_fields as $row3) {
					$k++;

					// Извлечение данных о колонке
					$fields[$i]=$row3["Field"]; // Имя
					$type=$row3["Type"]; // Тип
					$null=$row3["Null"]; // Признак NULL
					$key=$row3["Key"]; // Ключевое или нет
					$default=$row3["Default"]; // Значение по-умолчанию
					$extra=$row3["Extra"]; // Дополнительные параметры (auto_increment)

					// К запросу на создание таблицы добавляется имя и тип колонки, а также:
					$str.=" `$fields[$i]` " . strtoupper($type);

					// Если значение NULL = NO, это свойство добавляется к запросу, иначе по-умолчанию NULL будет = YES
					if($null=="NO") {
					   $str .= " NOT NULL";
					}

					// Если эта колонка является ключевой, добавляется это свойство
					if($key=="PRI") {
					   $str .= " PRIMARY KEY";
					}

					// Если значения по-умолчанию не равняется пустым, оно добавляется к запросу
					if(!empty($default)) {
					   $str .= " DEFAULT '" . $default . "'";
					}

					// Если дополнительный параметр = auto_increment, он также добавляется к запросу
					if($extra=="auto_increment") {
					   $str .= " AUTO_INCREMENT";
					}

					// Если количество колонок, добавленных к запросу меньше их общего количества в данной таблице, добавляется запятая, чтобы разделить колонки в запросе
					if($k < count($query_fields)) {
					   $str .= ",\n";
					}
					
					$i++;
				}
				$str .= ") \nENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
				fwrite ($fp, "\n\n-- ----------------------------- --\n\n" . $str . "\n\n");
				fwrite ($fp, 'TRUNCATE TABLE `' . current($table) . '`;' . "\n\n-- ----------------------------- --\n\n" . $str . "\n\n");
				
				$r = $FpsDB->query('SELECT * FROM `' . current($table) . '`');
				foreach ($r as $row) {
					$query = '';
					foreach ( $row as $field ) {
						if ( is_null($field) )
							$field = "NULL";
						else
							$field = "'".mysql_escape_string( $field )."'";
						if ( $query == "" )
							$query = $field;
						else
							$query = $query.', '.$field;
					}
					$query = "INSERT INTO `" . current($table) . "` VALUES (" . $query . ");\n";
					fwrite ($fp, $query);
				}
			}
		}
		fclose ($fp);
	}
	$_SESSION['info_message'] = __('DB backup complete');
	redirect('/admin/dump.php');
	
} else if (!empty($_GET['ac']) && $_GET['ac'] == 'delete' && !empty($_GET['id'])) {
	@unlink($_GET['id']);
	$_SESSION['info_message'] = __('Backup file is removed');
	redirect('/admin/dump.php');
	
} else if (!empty($_GET['ac']) && $_GET['ac'] == 'restore' && !empty($_GET['id'])) {
	if (file_exists($_GET['id'])) {
		$data = file_get_contents($_GET['id']);

		preg_match_all('#^(INSERT.*);$|(CREATE.+);|(TRUNCATE.+);$#msU', $data, $matches);
		if (!empty($matches[0]) && count($matches[0]) > 0) {
			foreach ($matches[0] as $row) {
				$FpsDB->query($row);
			}
		}
	}
	$_SESSION['info_message'] = __('Database is restored');
	redirect('/admin/dump.php');
}


//current dunps
$current_dumps = glob(ROOT . '/sys/logs/db_backups/*');	

include_once ROOT . '/admin/template/header.php';

?>

<div class="warning">
<?php echo __('DB backup is cool') ?>
</div>


<div class="list">
	<div class="title"></div>
	<a href="dump.php?ac=make_dump"><div class="add-cat-butt"><div class="add"></div><?php echo __('Create DB backup') ?></div></a>
	<table style="width:100%;" cellspacing="0" class="grid">

	<?php
	if (!empty($_SESSION['info_message'])):
	?>
	<script type="text/javascript">showHelpWin('<?php echo h($_SESSION['info_message']) ?>', '<?php echo __('Message') ?>');</script>
	<?php
		unset($_SESSION['info_message']);
	endif;
	?>
	<?php  
	if (!empty($current_dumps)): 
		foreach ($current_dumps as $dump): 
		
		
	?>

		<tr>
			<td><?php echo h(substr(strrchr($dump, '/'), 1, 16)) ?></td>
			<td><?php echo round((filesize($dump) / 1024), 1) ?> Kb</td>
			
			<td width="40px">
				<a class="undo" onClick="return confirm('Are you sure?')" title="<?php echo __('Restore') ?>" href="dump.php?ac=restore&id=<?php echo $dump ?>"></a>
				<a class="delete" onClick="return confirm('Are you sure?')" title="<?php echo __('Delete') ?>" href="dump.php?ac=delete&id=<?php echo $dump ?>"></a>
			</td>
		</tr>

	<?php	endforeach; ?>
	<?php else: ?> 

		<tr>
			<td><?php echo __('DB backups not found') ?></td>
		</tr>

	<?php endif; ?>


	</table>
</div>








<?php include_once 'template/footer.php';