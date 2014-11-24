<?php

##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      1.0                            ##
## Project:      CMS                            ##
## package       CMS AtomX                      ##
## subpackege    Admin Panel module             ##
## copyright     ©Andrey Brykin 2010-2014       ##
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

$Register = Register::getInstance();
$FpsDB = $Register['DB'];


$pageTitle = __('Admin Panel');
$pageNav = $pageTitle . __(' - General information');
$pageNavr = '';




$cnt_usrs = $FpsDB->select('users', DB_COUNT);;

$groups_info = array();
$users_groups = $ACL->get_group_info();
if (!empty($users_groups)) {
	foreach ($users_groups as $key => $group) {
		if ($key === 0) {
			$groups_info[0] = null;
			continue;
		}
		$groups_info[$group['title']] = $FpsDB->select('users', DB_COUNT, array('cond' => array('status' => $key)));
	}
}


$cnt_for = $FpsDB->select('themes', DB_COUNT);
$cnt_news = $FpsDB->select('news', DB_COUNT);
$cnt_premoder_news = $FpsDB->select('news', DB_COUNT, array('cond' => array('premoder' => 'nochecked')));
$cnt_premoder_news_comments = $FpsDB->select('comments', DB_COUNT, array('cond' => array('premoder' => 'nochecked', 'module' => 'news')));
$cnt_loads = $FpsDB->select('loads', DB_COUNT);
$cnt_premoder_loads = $FpsDB->select('loads', DB_COUNT, array('cond' => array('premoder' => 'nochecked')));
$cnt_premoder_loads_comments = $FpsDB->select('comments', DB_COUNT, array('cond' => array('premoder' => 'nochecked', 'module' => 'loads')));
$cnt_stat = $FpsDB->select('stat', DB_COUNT);
$cnt_premoder_stat = $FpsDB->select('stat', DB_COUNT, array('cond' => array('premoder' => 'nochecked')));
$cnt_premoder_stat_comments = $FpsDB->select('comments', DB_COUNT, array('cond' => array('premoder' => 'nochecked', 'module' => 'stat')));
$cnt_mat = $cnt_news + $cnt_for + $cnt_loads + $cnt_stat;

$all_hosts = $FpsDB->query("
	SELECT 
	SUM(`views`) as hits_cnt 
	, SUM(ips) as hosts_cnt
	, (SELECT SUM(`views`) FROM `" . $FpsDB->getFullTableName('statistics') . "` WHERE `date` = '" . date("Y-m-d") . "') as today_hits
	, (SELECT ips FROM `" . $FpsDB->getFullTableName('statistics') . "` WHERE `date` = '" . date("Y-m-d") . "') as today_hosts
	FROM `" . $FpsDB->getFullTableName('statistics') . "`");

$tmp_datafile = ROOT . '/sys/logs/counter/' . date("Y-m-d") . '.dat';

if (file_exists($tmp_datafile) && is_readable($tmp_datafile)) {
	$stats = unserialize(file_get_contents($tmp_datafile));
	$today_hits = $stats['views'];
	$today_hosts = $stats['cookie'];
} else {
	$today_hits = 0;
	$today_hosts = 0;
}
$all_hosts[0]['hits_cnt'] += $today_hits;
$all_hosts[0]['hosts_cnt'] += $today_hosts;




	
//echo $header;
include 'template/header.php';
?>


<?php
if (!empty($_SESSION['clean_cache'])):
?>
<script type="text/javascript">showHelpWin('<?php echo __('Cache is cleared'); ?>', '<?php echo __('Message') ?>');</script>
<?php
	unset($_SESSION['clean_cache']);
endif;
?>




<!--************ GENERAL **********-->							
<div class="list">
	<div class="title"><?php echo __('Common settings') ?></div>
	<div class="level1">
		<div class="head">
			<div class="title settings"><?php echo __('Name'); ?></div>
			<div class="title-r"><?php echo __('Value'); ?></div>
			<div class="clear"></div>
		</div>
		<div class="items">
			<div class="setting-item">
				<div class="left">
					<?php echo __('Current domain'); ?>
					<span class="comment"><?php echo __('Domain is your site address'); ?></span>
				</div>
				<div class="right"><?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/' ?></div>
				<div class="clear"></div>
			</div>
			<div class="setting-item">
				<div class="left">
					<?php echo __('SQL inj state'); ?>
					<span class="comment"><?php echo __('Is the control of SQL inj'); ?></span>
				</div>
				<div class="right"><div class="<?php echo (Config::read('antisql', 'secure') == 1) ? 'yes' : 'no' ?>"></div></div>
				<div class="clear"></div>
			</div>
			<div class="setting-item">
				<div class="left">
					<?php echo __('Anti DDOS protection'); ?>
					<span class="comment"><?php echo __('Is the enable Anti DDOS'); ?></span>
				</div>
				<div class="right"><div class="<?php echo (Config::read('anti_ddos', 'secure') == 1) ? 'yes' : 'no' ?>"></div></div>
				<div class="clear"></div>
			</div>
			<div class="setting-item">
				<div class="left">
					<?php echo __('Cache'); ?>
					<span class="comment"><?php echo __('The site will run faster'); ?></span>
				</div>
				<div class="right"><div class="<?php echo (Config::read('cache') == 1) ? 'yes' : 'no' ?>"></div></div>
				<div class="clear"></div>
			</div>
			<div class="setting-item">
				<div class="left">
					<?php echo __('SQL cache'); ?>
					<span class="comment"><?php echo __('SQL. Site will be run faster'); ?></span>
				</div>
				<div class="right"><div class="<?php echo (Config::read('cache_querys') == 1) ? 'yes' : 'no' ?>"></div></div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>

<!--************ MATERIALS **********-->							
<div class="list">
	<div class="title"><?php echo __('Materials') ?></div>
	<div class="level1">
		<div class="head">
			<div class="title settings"><?php echo __('Material') ?></div>
			<div class="title-r"><?php echo __('Quantity') . ' / ' . __('Pending moderation materials') . ' / ' . __('Pending moderation comments') ?></div>
			<div class="clear"></div>
		</div>
		<div class="items">
			<div class="setting-item">
				<div class="left">
					<?php echo __('Total materials') ?>
				</div>
				<div class="right"><?php echo $cnt_mat ?></div>
				<div class="clear"></div>
			</div>
			<div class="setting-item">
				<div class="left">
					<?php echo __('News') ?>
				</div>
				<div class="right">
					<?php echo $cnt_news ?> / 
					<span class="red"><?php echo $cnt_premoder_news ?></span> / 
					<span class="green"><?php echo $cnt_premoder_news_comments ?></span>
				</div>
				<div class="clear"></div>
			</div>
			<div class="setting-item">
				<div class="left">
					<?php echo __('Loads') ?>
				</div>
				<div class="right">
					<?php echo $cnt_loads ?> / 
					<span class="red"><?php echo $cnt_premoder_loads ?></span> / 
					<span class="green"><?php echo $cnt_premoder_loads_comments ?></span>
				</div>
				<div class="clear"></div>
			</div>
			<div class="setting-item">
				<div class="left">
					<?php echo __('Stat') ?>
				</div>
				<div class="right">
					<?php echo $cnt_stat ?> / 
					<span class="red"><?php echo $cnt_premoder_stat ?></span> / 
					<span class="green"><?php echo $cnt_premoder_stat_comments ?></span>
				</div>
				<div class="clear"></div>
			</div>
			<div class="setting-item">
				<div class="left">
					<?php echo __('Forum topics') ?>
				</div>
				<div class="right"><?php echo $cnt_for ?></div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>							

<!--************ USERS **********-->							
<div class="list">
	<div class="title"><?php echo __('Users') ?></div>
	<div class="level1">
		<div class="head">
			<div class="title settings"><?php echo __('Group') ?></div>
			<div class="title-r"><?php echo __('Quantity') ?></div>
			<div class="clear"></div>
		</div>
		<div class="items">
			<div class="setting-item">
				<div class="left">
					<?php echo __('All users') ?>
				</div>
				<div class="right"><?php echo $cnt_usrs ?></div>
				<div class="clear"></div>
			</div>
				
			<?php if (!empty($groups_info)):
					  foreach ($groups_info as $key => $group_info):
			?>
			<div class="setting-item">
				<?php if($key === 0): ?>
				<div class="left">
					Гости
					<span class="comment"><?php echo '*' . __('Guest') . __(' - abstrack group') ?></span>
				</div>
				<div class="right"><div class="<?php echo (Config::read('antisql', 'secure') == 1) ? 'yes' : 'no' ?>"></div></div>
				<?php else: ?>
				<div class="left">
					<?php echo $key ?>
				</div>
				<div class="right"><?php echo $group_info ?></div>
				<?php endif; ?>
				<div class="clear"></div>
			</div>
			<?php     endforeach;
				  endif;
			?>

		</div>
	</div>
</div>


<!--************ STATISTIC **********-->							
<div class="list">
	<div class="title"><?php echo __('Statistics') ?></div>
	<div class="level1">
		<div class="head">
			<div class="title settings"><?php echo __('Name'); ?></div>
			<div class="title-r"><?php echo __('Value'); ?></div>
			<div class="clear"></div>
		</div>
		<div class="items">
			<div class="setting-item">
				<div class="left">
					<?php echo __('Total hosts') ?>
					<span class="comment">*Хост - это уникальный посетитель, фактически - это<br />
					заход на сайт с разных компьютеров или IP адресов</span>
				</div>
				<div class="right"><?php echo $all_hosts[0]['hosts_cnt'] ?></div>
				<div class="clear"></div>
			</div>
			<div class="setting-item">
				<div class="left">
					<?php echo __('Total hits') ?>
					<span class="comment">*Хиты(hits) - это просмотры, фактически - это любой<br />
						просмотрт страницы, даже с одного IP. На один хост может приходиться<br />
						любое кол-во хитов</span>
				</div>
				<div class="right"><?php echo $all_hosts[0]['hits_cnt'] ?></div>
				<div class="clear"></div>
			</div>
			<div class="setting-item">
				<div class="left">
					<?php echo __('Today hosts') ?>
				</div>
				<div class="right"><?php echo $today_hosts ?></div>
				<div class="clear"></div>
			</div>
			<div class="setting-item">
				<div class="left">
					<?php echo __('Today hits') ?>
				</div>
				<div class="right"><?php echo $today_hits ?></div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>



<!--************ MODULES **********-->	
<?php $modules = glob(ROOT . '/modules/*'); ?>						
<div class="list">
	<div class="title">Модули</div>
	<div class="level1">
		<div class="head">
			<div class="title settings"><?php echo __('Module') ?></div>
			<div class="title-r"><?php echo __('Status') ?></div>
			<div class="clear"></div>
		</div>
		<div class="items">
			<div class="setting-item">
				<div class="left">
					<?php echo __('Total modules') ?>
					<span class="comment">*Модули, которые присутствуют у Вас на сайте</span>
				</div>
				<div class="right"><?php echo count($modules); ?></div>
				<div class="clear"></div>
			</div>
			<?php foreach ($modules as $modul): ?>
			<?php if (preg_match('#/(\w+)$#i', $modul, $modul_name)): ?>
			<?php if (is_dir($modul)): ?>

			<div class="setting-item">
				<div class="left">
					<?php echo $modul_name[1] ?>
				</div>
				<div class="right"><div class="<?php echo (Config::read('active', $modul_name[1])) ? 'yes' : 'no' ?>"></div></div>
				<div class="clear"></div>
			</div>

			<?php endif; ?>
			<?php endif; ?>
			<?php endforeach; ?>
		</div>

	</div>
</div>


<?php
include_once 'template/footer.php';
?>



