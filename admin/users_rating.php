<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      1.2                            ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    Admin Panel module             ##
## copyright     ©Andrey Brykin 2010-2011       ##
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


if (isset($_POST['send'])) {
	$errors = array();
	//Проводим валидациюданных
	for ($i = 0; $i < 11; $i++) {
		if (empty($_POST['rat'.$i]) || strlen($_POST['rat'.$i]) < 1) $errors['rat'.$i] = 'Слишком короткое значение';
		if ($i > 0) {
			if (empty($_POST['cond'.$i]) || strlen($_POST['cond'.$i]) < 1) $errors['cond'.$i] = 'Слишком короткое значение';
			elseif (!is_numeric($_POST['cond'.$i])) $errors['cond'.$i] = 'Значение должно быть числом';
		}
	}
	
	if (empty($errors)) {
		$TempSet['rat0'] = $_POST['rat0'];
		$TempSet['cond1'] = $_POST['cond1'];
		$TempSet['rat1'] = $_POST['rat1'];
		$TempSet['cond2'] = $_POST['cond2'];
		$TempSet['rat2'] = $_POST['rat2'];
		$TempSet['cond3'] = $_POST['cond3'];
		$TempSet['rat3'] = $_POST['rat3'];
		$TempSet['cond4'] = $_POST['cond4'];
		$TempSet['rat4'] = $_POST['rat4'];
		$TempSet['cond5'] = $_POST['cond5'];
		$TempSet['rat5'] = $_POST['rat5'];
		$TempSet['cond6'] = $_POST['cond6'];
		$TempSet['rat6'] = $_POST['rat6'];
		$TempSet['cond7'] = $_POST['cond7'];
		$TempSet['rat7'] = $_POST['rat7'];
		$TempSet['cond8'] = $_POST['cond8'];
		$TempSet['rat8'] = $_POST['rat8'];
		$TempSet['cond9'] = $_POST['cond9'];
		$TempSet['rat9'] = $_POST['rat9'];
		$TempSet['cond10'] = $_POST['cond10'];
		$TempSet['rat10'] = $_POST['rat10'];

		$prepair = serialize($TempSet);
		$what = $FpsDB->select('users_settings', DB_COUNT, array('cond' => array('type' => 'rating')));
		if (count($what) < 1) {
			$FpsDB->save('users_settings', array(
				'type' => 'rating',
				'values' => $prepair,
			));
		} else {
			$FpsDB->save('users_settings', 
				array(
					'values' => $prepair,
				), array(
					'type' => 'rating',
				)
			);
		}
		
		redirect("/admin/users_rating.php");

	}
	

}



$query = $FpsDB->select('users_settings', DB_FIRST, array('cond' => array('type' => 'rating')));
if (count($query) < 1) {
	$result = array();
} else {
	$result = unserialize($query[0]['values']);
}




$pageTitle = 'Ранги пользователей';
$pageNav = $pageTitle;
$pageNavl = '';
include_once ROOT . '/admin/template/header.php';
?>




<form method="POST" action="users_rating.php">
<div class="list">
	<div class="title">Ранги пользователей</div>
	<div class="level1">
		<div class="head">
			<div class="title settings">Номер</div>
			<div class="title-r">Сообщений / Звание</div>
			<div class="clear"></div>
		</div>
		<div class="items">
			<div class="setting-item">
				<div class="left">
					Без ранга
					<span class="comment">(какое звание будет у нового пользователя)</span>
				</div>
				<div class="right">
					<input type="text" name="rat0" value="<?php echo (!empty($result['rat0'])) ? $result['rat0'] : ''; ?>">
					<?php echo (!empty($errors['rat0'])) ? '<br /><span class="error">'.$errors['rat0'].'</span>' : ''; ?>
				</div>
				<div class="clear"></div>
			</div>
			
			
			<?php for ($i = 1; $i < 11; $i++): ?>
			<div class="setting-item">
				<div class="left">
					Ранг № <?php echo $i; ?>
				</div>
				<div class="right">
					<input type="text" name="cond<?php echo $i ?>" value="<?php echo (!empty($result['cond'.$i])) ? intval($result['cond'.$i]) : 10*$i; ?>">&nbsp;<span class="help">Кол-во сообщений</span><br /><br />
					<?php echo (!empty($errors['cond'.$i])) ? '<br /><span class="error">'.$errors['cond'.$i].'</span>' : ''; ?>
					<input type="text" name="rat<?php echo $i ?>" value="<?php echo (!empty($result['rat'.$i])) ? h($result['rat'.$i]) : ''; ?>">&nbsp;<span class="help">Звание</span><br />
					<?php echo (!empty($errors['rat'.$i])) ? '<br /><span class="error">'.$errors['rat'.$i].'</span>' : ''; ?>
				</div>
				<div class="clear"></div>
			</div>
			<?php endfor ?>
			
			
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



<?php
include_once ROOT . '/admin/template/footer.php';
?>