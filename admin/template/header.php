<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $pageTitle; ?></title>
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
	<script language="JavaScript" type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jquery.js"></script>
	
	<script language="JavaScript" type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jquery.validate.js"></script>
	<script language="JavaScript" type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jquery-ui-1.8.14.custom.min.js"></script>
	<script type="text/javascript" src="<?php echo WWW_ROOT ?>/admin/js/drunya.lib.js"></script>

	<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/redactor/redactor.js"></script>
	<link type="text/css" rel="StyleSheet" href="<?php echo WWW_ROOT ?>/sys/js/redactor/css/redactor.css" />
	
	<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jquery.cookie.js"></script>
	<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jquery.hotkeys.js"></script>
	<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jstree/jstree.min.js"></script>
	
	<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/fancybox/jquery.fancybox.js"></script>
	<link type="text/css" rel="StyleSheet" href="<?php echo WWW_ROOT ?>/sys/js/fancybox/css/fancy.css" />
	
	<link rel="StyleSheet" type="text/css" href="<?php echo WWW_ROOT ?>/admin/template/css/style.css" />
	<!--<link rel="StyleSheet" href="<?php echo WWW_ROOT ?>/admin/template/css/font-awesome.min.css" />-->
	
	<script type="text/javascript">
	
	
	$(document).ready(function(){
		$("a.gallery").fancybox();
		//setTimeout(function(){
		//	$('#overlay').height($('#wrapper').height());
		//}, 2000);
		$('#wrapper').css('min-height', ($('body').height() - 136)); // 136 - top+bottom wrapper padding
		$('body').height($('#wrapper').outerHeight() - 55); // 55 - side menu top indent
		
		
		
		// get size to current scroll position
		function getScrollTop() {
				   var scrOfY = 0;
				   if( typeof( window.pageYOffset ) == "number" ) {
						   //Netscape compliant
						   scrOfY = window.pageYOffset;
				   } else if( document.body
				   && ( document.body.scrollLeft
				   || document.body.scrollTop ) ) {
						   //DOM compliant
						   scrOfY = document.body.scrollTop;
				   } else if( document.documentElement
				   && ( document.documentElement.scrollLeft
					|| document.documentElement.scrollTop ) ) {
						   //IE6 Strict
						   scrOfY = document.documentElement.scrollTop;
				   }
				   return scrOfY;
		}
		
		function setMenuPosition() {
			var position = getScrollTop();
			if (typeof position == 'undefined' || !position) return false;
			
			
			if (position > 106) {
				
				if ($('.headmenuwrap .crumbs').is(':visible')) $('.headmenuwrap .crumbs').hide();
				$('.headmenuwrap').stop().animate({
					'height': '55px',
					'z-index': 5,
					'top': parseInt(position) + 'px'
				}, 100);
				
			} else {
				if (!$('.headmenuwrap .crumbs').is(':visible')) $('.headmenuwrap .crumbs').show();
				$('.headmenuwrap').stop().animate({
					'height': '106px',
					'z-index': 1,
					'top': '0px'
				}, 500);
			}
			return false;
		}
		
		//window.addEventListener("scroll", setMenuPosition, false);
	});
	</script>
</head> 
<body>
	<div class="headmenuwrap">
		<div class="headmenu">
			<a href="<?php echo WWW_ROOT; ?>/admin/"><div class="logo"></div></a>
			<div class="menu" id="topmenu">
				<ul>
					<li><a href="#">Общее</a></li>
					<li><a href="#">Плагины</a></li>
					<li><a href="#">Сниппеты</a></li>
					<li><a href="#">Дизайн</a></li>
					<li><a href="#">Статистика</a></li>
					<li><a href="#">Безопасность</a></li>
					<li><a href="#">Дополнительно</a></li>
					<li><a href="#">Помощь</a></li>
					<div class="clear"></div>
				</ul>
			</div>
			<div class="userbar">
				<?php
				if (!empty($_SESSION['user'])) {
					
					$ava_path = (file_exists(ROOT . '/sys/avatars/' . $_SESSION['user']['id'] . '.jpg'))
					? WWW_ROOT . '/sys/avatars/' . $_SESSION['user']['id'] . '.jpg'
					:  WWW_ROOT . '/sys/img/noavatar.png';
				
				}


                // Used below
                $serverMessage = (!empty($isMainPage))
                    ? AtmApiService::getServerMessage() : '';


				$new_ver = AtmApiService::getLastVersion();
				$newVersion = ($new_ver)
					? '<a href="https://github.com/Drunyacoder/AtomXCMS-2/releases" title="Next version">' . __('New version of AtomX') . '</a>'
					: '';
				
				
				$group_info = $Register['ACL']->get_user_group($_SESSION['user']['status']);
				$group_title = $group_info['title'];
				
				?>
				<div class="ava"><img src="<?php echo $ava_path; ?>" alt="user ava" title="user ava" /></div>
				<div class="name"><a href="#"><?php echo h($_SESSION['user']['name']); ?></a><span><?php echo h($group_title) ?></span></div>
				<a href="exit.php" class="exit"></a>
			</div>
			<div class="clear"></div>
		</div>
		<div class="rcrumbs">
			<?php echo (!empty($pageNavr)) ? $pageNavr : ''; ?>
		</div>
		<div class="crumbs">
			<?php echo (!empty($pageNav)) ? $pageNav : ''; ?>
		</div>
	</div>
	<div id="side-menu" class="side-menu">
		<div class="search">
			<form>
				<div class="input"><input type="text" name="search" placeholder="Search..." /></div>
				<input class="submit-butt" type="submit" name="send" value="" />
			</form>
		</div>
		<ul>
		
		
		
		<?php
		$modsInstal = new FpsModuleInstaller;
		$nsmods = $modsInstal->checkNewModules();

		if (count($nsmods)):
			foreach ($nsmods as $mk => $mv):
		?>	
		
			<li>
				<div class="icon new-module"></div>
                <div class="sub-opener" onClick="subMenu('sub<?php echo $mk; ?>')"></div>
                <a href="#"><?php echo $mk; ?></a>
				<div class="clear"></div>
				<div id="sub<?php echo $mk; ?>" class="sub">
					<div class="shadow">
						<ul>
							<li><a href="<?php echo WWW_ROOT; ?>/admin?install=<?php echo $mk ?>">Install</a></li>
						</ul>
					</div>
				</div>
			</li>
		<?php
			endforeach;
		endif;




		$modules = getAdmFrontMenuParams();

		foreach ($modules as $modKey => $modData): 
			if (!empty($nsmods) && array_key_exists($modKey, $nsmods)) continue;
		?>
		
			<li>
				<div class="icon <?php echo $modKey ?>"></div>
                <div class="sub-opener" onClick="subMenu('sub<?php echo $modKey ?>')"></div>
                <a href="<?php echo $modData['url']; ?>"><?php echo $modData['ankor']; ?></a>
				<div class="clear"></div>
				<div id="sub<?php echo $modKey ?>" class="sub">
					<div class="shadow">
						<ul>
							<?php foreach ($modData['sub'] as $url => $ankor): ?>
							<li><a href="<?php echo get_url('/admin/' . $url); ?>"><?php echo $ankor; ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</li>
		<?php endforeach; ?>
		</ul>
		<div class="clear"></div>
	</div>
	<script>
		var FpsAdmPanel = {};
		FpsAdmPanel.sidePanel = 'visible';
		function hideSide(){
			if(FpsAdmPanel.sidePanel == 'visible') {
				$('#side-menu').animate({left:'-300px'}, 500); 
				$('#side-menu-td').animate({width:'0px'}, 500);
				FpsAdmPanel.sidePanel = 'hidden';
			} else {
				$('#side-menu').animate({left:'3%'}, 500); 
				$('#side-menu-td').animate({width:'237px'}, 500);
				FpsAdmPanel.sidePanel = 'visible';
			}
		}
	</script>
	<div id="side-menu-label" onClick="hideSide();">
		<a href="#">Спрятать</a>
	</div>
	<div id="wrapper">

		<!-- AdminBar -->
		<script type="text/javascript">

		document.top_menu = new drunyaMenu([
		['<?php echo __('General'); ?>',
		  [
		  '<a href="/admin"><?php echo __('Main page'); ?></a>',
		  'sep',
		  '<span><?php echo __('Version of AtomX'); ?> [ <b><?php echo FPS_VERSION ?></b> ]</span>',
		  <?php if ($new_ver): ?>
		  '<span><?php echo $newVersion; ?> [ <?php echo $new_ver; ?> ]</span>',
		  <?php endif; ?>
		  'sep',
		  '<a href="/admin/settings.php?m=sys"><?php echo __('Common settings'); ?></a>',
		  '<a href="/admin/clean_cache.php"><?php echo __('Clean cache'); ?></a>'
		  ]],

		['<?php echo __('Plugins'); ?>',
		  [
		  '<a href="/admin/plugins.php"><?php echo __('List'); ?></a>',
		  '<a href="/admin/get_plugins.php?a=ed">Установка</a>'
		  ]],

		  
		  
		['<?php echo __('Snippets'); ?>',
		  [
		  '<a href="/admin/snippets.php"><?php echo __('Create'); ?></a>',
		  '<a href="/admin/snippets.php?a=ed"><?php echo __('Edit'); ?></a>'
		  ]],

		  
		 
		['<?php echo __('Design'); ?>',
		  [
		  '<a href="design.php?d=default&t=main"><?php echo __('General design and css'); ?></a>',
		  '<a href="menu_editor.php"><?php echo __('Menu editor'); ?></a>'
		  ]],
		  
		  
		['<?php echo __('Statistic'); ?>',
		  [
		  '<a href="/admin/statistic.php"><?php echo __('View'); ?></a>',
		  '<a href="/admin/settings.php?m=statistics"><?php echo __('Settings of module'); ?></a>'
		  ]],



		['<?php echo __('Security'); ?>',
		  [
		  '<a href="settings.php?m=secure"><?php echo __('Security settings'); ?></a>',
		  '<a href="ip_ban.php"><?php echo __('Bans by IP'); ?></a>',
		  '<a href="dump.php"><?php echo __('Backup control'); ?></a>',
		  'sep',
		  '<a href="system_log.php"><?php echo __('Action log'); ?></a>',
		  '<a href="errors_log.php"><?php echo __('Errors log'); ?></a>'
		  ]],
		  
		  
		['<?php echo __('Additional'); ?>',
		  [
		  '<a href="settings.php?m=hlu"><?php echo __('SEO settings'); ?></a>',
		  '<a href="settings.php?m=rss"><?php echo __('RSS settings'); ?></a>',
		  '<a href="settings.php?m=sitemap"><?php echo __('Sitemap settings'); ?></a>',
		  '<a href="settings.php?m=watermark"><?php echo __('Watermark settings'); ?></a>',
		  '<a href="settings.php?m=autotags"><?php echo __('Autotags settings'); ?></a>',
		  '<a href="settings.php?m=links"><?php echo __('Links settings'); ?></a>'
		  ]],
		  

		['<?php echo __('Help'); ?>',
		  [
		  '<a href="http://atomx.net" target="_blank"><?php echo __('AtomX CMS Community'); ?></a>',
		  '<a href="faq.php"><?php echo __('FAQ'); ?></a>',
		  'sep',
		  '<a href="authors.php"><?php echo __('Dev. Team'); ?></a>',
		  ]]
		]);

		</script>
		<!-- /AdminBar -->
		
		
		<div class="center-wrapper">
			<table class="side-separator" cellpadding="0" cellspacing="0" width="100%" >
				<tr>
					<td id="side-menu-td" width="237" min-height="100%">
					</td>
					<td style="position:relative;">
						<div id="content-wrapper">

<?php if (!empty($serverMessage)): ?>
    <!-- AtomX Server Notification -->
    <div class="warning <?php echo $serverMessage['type'] ?>">
        <?php echo $serverMessage['message'] ?>
    </div>
    <!-- /AtomX Server Notification -->
<?php endif  ?>
<?php if (!empty($_SESSION['message'])): ?>
    <div class="warning ok">
		<?php echo $_SESSION['message'] ?>
	</div>
    <?php unset($_SESSION['message']); ?>
<?php endif; if (!empty($_SESSION['errors'])): ?>
    <div class="warning error">
		<?php echo $_SESSION['errors'] ?>
	</div>
<?php unset($_SESSION['errors']); endif; ?>