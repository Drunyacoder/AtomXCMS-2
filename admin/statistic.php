<?php
##################################################
##												##
## @Author:       Andrey Brykin (Drunya)        ##
## @Version:      1.1                           ##
## @Project:      CMS                           ##
## @package       CMS Fapos                     ##
## @subpackege    Admin module                  ##
## @copyright     ©Andrey Brykin 2010-2012      ##
## @last mod.     2012/06/10                    ##
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


$date = (!empty($_GET['date'])) ? $_GET['date'] : time();
$_date = mysql_real_escape_string(date("Y-m-d", $date));
if ($_date == date("Y-m-d")) {
	if (file_exists(ROOT . '/sys/logs/counter/' . $_date . '.dat')) {
		$stats[0] = unserialize(file_get_contents(ROOT . '/sys/logs/counter/' . $_date . '.dat'));
	}
} else {
	$stats = $FpsDB->query("SELECT * FROM `" . $FpsDB->getFullTableName('statistics') . "` WHERE `date` = '" . $_date . "'");
}


if (!empty($_POST['grfrom'])) $_POST['grfrom'] = preg_replace('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', '$3-$1-$2', $_POST['grfrom']);
if (!empty($_POST['grto'])) $_POST['grto'] = preg_replace('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', '$3-$1-$2', $_POST['grto']);
$graph_from = (!empty($_POST['grfrom']) && preg_match('#^\d{4}-\d{2}-\d{2}$#', $_POST['grfrom'])) ? $_POST['grfrom'] : '0000-00-00';
$graph_to = (!empty($_POST['grto']) && preg_match('#^\d{4}-\d{2}-\d{2}$#', $_POST['grto'])) ? $_POST['grto'] : date("Y-m-d");


$UsersModel = $Register['ModManager']->getModelInstance('Users');
$Model = $Register['ModManager']->getModelInstance('Statistics');
$all = $Model->getCollection(array(
	"date >= '{$graph_from}'",
	"date <= '{$graph_to}'",
));
$users = $UsersModel->getCollection(array(
	
), array(
	'joins' => array(
		array(
			'alias' => 'b',
			'type' => 'LEFT',
			'table' => 'users',
			'cond' => array("`b`.`puttime` = `a`.`puttime`"),
		),
	),
	'fields' => array(
		"COUNT(b.id) as cnt",
		"a.puttime as date",
	),
	'group' => '`date`',
	'alias' => 'a',
));
$json_data_v = array();
$json_data_h = array();
if (!empty($all)) {
	foreach ($all as $item) {
		//$json_data[] = (int)$item->getViews();
		$json_data_v[] = array(
			$item->getDate(),
			(int)$item->getViews(),
		);
		$json_data_h[] = array(
			$item->getDate(),
			(int)$item->getIps(),
		);
	}
}





if (!empty($stats)) {
	$t_views = 0;
	$t_hosts = $stats[0]['ips'];
	$t_visitors = 0;
	$t_views = $stats[0]['views'];
	$t_visitors = $stats[0]['cookie'];
	$views_on_visit = number_format(($t_views / $t_visitors), 1);
	$bot_views = $stats[0]['yandex_bot_views'] + $stats[0]['google_bot_views'] + $stats[0]['other_bot_views'];
}


$json_data = json_encode(array(
	$json_data_v,
	$json_data_h,
));

//pr($json_data); die();
$pageTitle = 'Статистика';
$pageNav = $pageTitle;
$pageNavl = '';
include_once ROOT . '/admin/template/header.php';

?>



<div class="list">
	<div class="title">
		<table cellspacing="0" width="100%">
			<tr>
			<td><a style="color:#8BB35B;" href="statistic.php?date=<?php echo $date - 168000 ?>"><?php echo '&laquo; ' . date("Y-m-d", $date - 168000) ?></a></td>
			<td><a style="color:#8BB35B;" href="statistic.php?date=<?php echo $date - 84000 ?>"><?php echo '&laquo; ' . date("Y-m-d", $date - 84000) ?></a></td>
			<td align="center"><a href="statistic.php?date=<?php echo $date ?>"><span style="color:#8BB35B;"><?php echo date("Y-m-d", $date) ?></span></a></td>
			<td align="right" width="20%"> 
			<?php  if (($date + 84000) < time()): ?>
			<a style="color:#8BB35B;" href="statistic.php?date=<?php echo $date + 84000 ?>"><?php echo date("Y-m-d", $date + 84000) . ' &raquo;' ?></a>
			<?php endif; ?>
			</td>
			<td width="20%" align="right"> 
			<?php  if (($date + 168000) < time()): ?>
			<a style="color:#8BB35B;" href="statistic.php?date=<?php echo $date + 168000 ?>"><?php echo date("Y-m-d", $date + 168000) . ' &raquo;' ?></a>
			<?php endif; ?>
			</td>
			</tr>
		</table>
	</div>
	<table class="grid" style="width:100%;"  cellspacing="0px">
		<?php if (!empty($stats)): ?>
		<tr>
			<td>Просмотров</td>
			<td><?php echo $t_views ?></td>
		</tr>
		<tr>
			<td>Хостов</td>
			<td><?php echo $t_hosts ?></td>
		</tr>
		<tr>
			<td>Посетителей</td>
			<td><?php echo $t_visitors ?></td>
		</tr>
		<tr>
			<td>Просмотров на посетителя</td>
			<td><?php echo $views_on_visit ?></td>
		</tr>
		<tr>
			<td>Просмотров роботами</td>
			<td><?php echo $bot_views ?></td>
		</tr>
		<tr>
			<td>Робот ПС google</td>
			<td><?php echo $stats[0]['google_bot_views'] ?></td>
		</tr>
		<tr>
			<td>Робот ПС yandex</td>
			<td><?php echo $stats[0]['yandex_bot_views'] ?></td>
		</tr>
		<tr>
			<td>Переходы с других сайтов</td>
			<td><?php echo $stats[0]['other_site_visits'] ?></td>
		</tr>
		<?php else: ?>
		
		<tr>
			<td align="center" colspan="2">Записей нет</td>
		</tr>
		
		<?php endif; ?>
		
		
		
		


		<?php if(!empty($json_data)): ?>
		<tr>
			<td colspan="2">
		<link type="text/css" rel="StyleSheet" href="template/css/tcal.css" />
		<script type="text/javascript" src="../sys/js/graphlib.js"></script>
		<script type="text/javascript" src="../sys/js/tcal.js"></script>
		<script type="text/javascript">
		$(document).ready(function(){
			var data = '<?php echo $json_data; ?>';
			//data = '['+data+']';
			//alert(data);
		  var plot2 = $.jqplot ('graph', eval(data), {
			  // Give the plot a title.
			  title: 'Views and hosts',
			  // You can specify options for all axes on the plot at once with
			  // the axesDefaults object.  Here, we're using a canvas renderer
			  // to draw the axis label which allows rotated text.
			  axesDefaults: {
				labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
				gridLineColor: "#ff0000",
				border: '#ff0000'
			  },
			  // An axes object holds options for all axes.
			  // Allowable axes are xaxis, x2axis, yaxis, y2axis, y3axis, ...
			  // Up to 9 y axes are supported.
			  axes: {
				// options for each axis are specified in seperate option objects.
				xaxis: {
				  renderer:$.jqplot.DateAxisRenderer,
				  tickOptions:{
					//formatString:'%b&nbsp;%#d'
					formatString:'%b-%d'
				  }
				},
				yaxis: {
				  label: "Y Axis"
				}
			  },
			  highlighter: {
				show: true,
				sizeAdjust: 7.5
			  },
			  cursor: {
				show: false
			  },
			series:[
			  {
				// Change our line width and use a diamond shaped marker.
				lineWidth:1,
				fill: true,
				fillAndStroke: true,
				fillColor: '#d5eC86',
				fillAlpha: 0.5,
				label:'Views',
				//color:'#333',
				markerOptions: { style:'dimaond'}
			  },
			  {
				// Use a thicker, 5 pixel line and 10 pixel
				// filled square markers.
				lineWidth:5,
				label:'Hosts',
				markerOptions: { style:"filledSquare", size:10 }
			  }
			],
			grid: {
				background: '#282828'
			},
			});
		});
		</script>
		<div style="width:90%; height:350px; margin:0px auto;" id="graph"></div>
			</td>
		</tr>
		<?php endif; ?>


		

			

		<tr>
			<td colspan="2">
				<form method="POST" action="">
				<br />
				<table class="lines"  cellspacing="0px">
					<tr>
						<td>
							&nbsp;От&nbsp;:&nbsp;&nbsp;<input class="tcal" id="ffrom" type="text" name="grfrom" />
							&nbsp;До&nbsp;:&nbsp;&nbsp;<input class="tcal" id="fto" type="text" name="grto" />
						</td>
						<td><input type="submit" name="send" value="Отправить" /></td>
					</tr>
				</table>
				</form>
			</td>
		</tr>
	</table>
</div>




<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />

<?php 
include_once 'template/footer.php';
?>
