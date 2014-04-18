<?php

##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      1.2                            ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    Admin Panel module             ##
## copyright     ©Andrey Brykin                 ##
## last mod.     2014/03/04                     ##
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




$pageTitle = __('Mass mailing');
$pageNav = $pageTitle;
$pageNavr = '';



$ACL = $Register['ACL'];
$FpsDB = $Register['DB'];
$users_groups = $ACL->get_group_info();
$count_usr = $FpsDB->select('users', DB_ALL, array(
	'group' => 'status',
	'fields' => array(
		'COUNT(*) as cnt',
		'status'
	),
));
foreach ($users_groups as $id => $gr) {
	$users_groups[$id]['cnt'] = 0;
	foreach ($count_usr as $key => $val) {
		if ($id == $val['status']) {
			$users_groups[$id]['cnt'] = $val['cnt'];
			break;
		}
	}
}
if (isset($users_groups[0])) unset($users_groups[0]);

$all_users_cnt = 0;
foreach ($count_usr as $val) {
	$all_users_cnt += $val['cnt'];
}


// templates for select & current template
$email_templates_path = ROOT . '/sys/settings/email_templates/';
$email_templates = glob($email_templates_path . '*.html');
if (!empty($email_templates)) {
    foreach($email_templates as $k => $template) {
        $template = substr(strrchr($template, '/'), 1);
        $template = str_replace('.html', '', $template);
        $email_templates[$k] = $template;
    }
}

if (!empty($_GET['tpl']) && file_exists($email_templates_path . $_GET['tpl'] . '.html')) {
    $message_text = file_get_contents($email_templates_path . $_GET['tpl'] . '.html');
} else {
    $message_text = '';
}


	
if (isset($_POST['send'])) {
	if (!empty($_POST['message']) 
	&& !empty($_POST['subject']) 
	&& !empty($_POST['groups'])
	&& count($_POST['groups']) > 0) {
		
		$status_ids = array();
		foreach ($_POST['groups'] as $group) {
			$status_ids[] = intval($group);
		}
		$status_ids = array_unique($status_ids);
		$status_ids = implode(', ', $status_ids);
		
		
		$mail_list = $FpsDB->select('users', DB_ALL, array(
			'cond' => array(
				'`status` IN (' . $status_ids . ')',
			),
		));
		

		
		if (count($mail_list) > 0) {
			$from = (!empty($_POST['from'])) ? trim($_POST['from']) : Config::read('admin_email');
			$subject = trim($_POST['subject']);
			$headers = "Precedence: bulk\n";

            $mailer = new AtmMail($email_templates_path);
            $mailer->prepare(false, $from, $headers);
            $mailer->setBody($_POST['message']);

			$n = 0;
			$start_time = microtime(true);
			foreach ($mail_list as $result) {
                // Send password in email is deny
                unset($result['passw']);
                $context = array(
					'user' => $result,
				);
				if ($mailer->sendMail($result['email'], $subject, $context)) {
					$n++;
				}
			}
			
			if (empty($error)) 
				$_SESSION['info_message'] = __('Mails are sent') . ': ' . $n 
				. '<br>Времени потрачено: ' . round(microtime(true) - $start_time, 4) . ' сек.';
		} else {
			$_SESSION['info_message'] = '<span style="color:red;">' . __('Users not found') . '</span>';
		}
	} else {
		$_SESSION['info_message'] = '<span style="color:red;">' . __('Needed fields are empty') . '</span>';
	}
	
	redirect('/admin/users_sendmail.php');
}



include_once ROOT . '/admin/template/header.php';
?>

<?php /*
<div id="sec" class="popup">
	<div class="top">
		<div class="title">Добавление категории</div>
		<div onClick="closePopup('sec');" class="close"></div>
	</div>
	<form action="forum_cat.php?ac=add" method="POST">
	<div class="items" style="height:400px; overflow:auto;">
		<?php foreach($subscribes_emails as $email): ?>
		<?php echo h($email); ?><br />
		<?php endforeach; ?>
	</div>
	</form>
</div>
*/ ?>


<div class="warning">
	<span class="greytxt"><?php echo __('Available emails') ?>:</span> <?php echo $all_users_cnt; ?><br /><br />


	<span class="greytxt"><?php echo __('Max email length') ?>:</span> 10000 <?php echo __('Symbols') ?><br /><br />


	<span class="greytxt"><b><?php echo __('In the mail body available below markers') ?>:</b></span><br />
	{{ user }}<span class="greytxt"> - <?php echo __('Receiver') ?>(<?php echo __('also available an user object variables') ?>)</span><br />
	{{ site_title }}<span class="greytxt"> - <?php echo __('Site name') ?></span><br />
	{{ site_url }}<span class="greytxt"> - <?php echo __('Your site URL') ?></span><br />
	{{ subject }}<span class="greytxt"> - <?php echo __('Subject') ?></span><br />
</div>






<form action="" method="POST">
<div class="list">
	<div class="title"><?php echo __('Mass mailing') ?></div>
	<!--<div class="add-cat-butt" onClick="openPopup('sec');"><div class="add"></div>Список подписчиков</div>-->
	<div class="level1">
		<div class="items">
			<div class="setting-item">
				<div class="left">
					<?php echo __('Send to groups') ?>
				</div>
				<div class="right">
					<table>
						<tr>
						<?php foreach ($users_groups as $id => $group):  $chb_id = md5(rand(0, 9999) . $id); ?>
							<td style="text-align:center;">
								<input id="<?php echo $chb_id; ?>" type="checkbox" name="groups[<?php echo (int)$id; ?>]" value="<?php echo (int)$id; ?>" checked="checked" /><label for="<?php echo $chb_id; ?>"><?php echo h($group['title']) . ' (' . $group['cnt'] . ')'; ?></label><br />
								
							</td>
						<?php endforeach; ?>
						</tr>
					</table>
				</div>
				<div class="clear"></div>
			</div>
            <div class="setting-item">
                <div class="left">
                    <?php echo __('Email template') ?>
                </div>
                <div class="right">
                    <select onChange="window.location.href = '<?php echo get_url('/admin/users_sendmail.php?tpl=') ?>'+$(this).val();" name="template">
                        <?php
                        if (!empty($email_templates)) {
                            ?>
                            <option value=""><?php echo __('Without template') ?></option>
                            <?php
                            foreach ($email_templates as $template) {
                                echo '<option '.((!empty($_GET['tpl']) && $_GET['tpl'] == $template) ? 'selected="selected"' : '')
                                    .' value="'.$template.'">'.$template.'</option>';
                            }
                        } else {
                            echo '<option selected="selected" value="">' . __('Templates not found') . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
			<div class="setting-item">
				<div class="left">
					<?php echo __('Subject') ?>
				</div>
				<div class="right">
					<input size="120" type="text" name="subject" />
				</div>
				<div class="clear"></div>
			</div>
			<div class="setting-item">
				<div class="left">
					<?php echo __('Sender\'s email') ?>
				</div>
				<div class="right">
					<input size="120" type="text" name="from" value="<?php 
					echo (Config::read('admin_email')) ? Config::read('admin_email') : ''; ?>" />
				</div>
				<div class="clear"></div>
			</div>
			<div class="setting-item">
				<div class="left">
					<?php echo __('Email text') ?>
				</div>
				<div class="right">
					<textarea name="message" style="height:200px;"><?php echo $message_text ?></textarea>
				</div>
				<div class="clear"></div>
			</div>
			<div class="setting-item">
				<div class="left">
				</div>
				<div class="right">
					<input class="save-button" type="submit" name="send" value="<?php echo __('Send') ?>" />
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
</form>




<?php
if (!empty($_SESSION['info_message'])):
?>
<script type="text/javascript">showHelpWin('<?php echo $_SESSION['info_message'] ?>', 'Сообщение');</script>
<?php
	unset($_SESSION['info_message']);
endif;
?>


<?php

include_once ROOT . '/admin/template/footer.php';
?>

