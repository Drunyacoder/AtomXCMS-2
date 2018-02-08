<?php
##################################################
##												##
## @Author:       Andrey Brykin (Drunya)        ##
## @Version:      1.2                           ##
## @Project:      CMS                           ##
## @package       CMS AtomX                     ##
## @subpackege    Admin module                  ##
## @copyright     ©Andrey Brykin 2010-2014      ##
## @last mod.     2014/01/13                    ##
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

// If for menu
$_GET['m'] = 'statistics';



$date = (!empty($_GET['date'])) ? $_GET['date'] : time();
$_date = date("Y-m-d", $date);
if ($_date == date("Y-m-d")) {
	if (file_exists(ROOT . '/sys/logs/counter/' . $_date . '.dat')) {
		$stats[0] = unserialize(file_get_contents(ROOT . '/sys/logs/counter/' . $_date . '.dat'));
	}
} else {
	$stats = $FpsDB->query("SELECT * FROM `" . $FpsDB->getFullTableName('statistics') . "` WHERE `date` = '" . $_date . "'");
}



$graph_from = (!empty($_POST['grfrom'])) ? date("Y-m-d", strtotime($_POST['grfrom'])) : date("Y-m-d", time() - 2592000);
$graph_to = (!empty($_POST['grto'])) ? date("Y-m-d", strtotime($_POST['grto'])) : date("Y-m-d");


$UsersModel = $Register['ModManager']->getModelInstance('Users');
$Model = $Register['ModManager']->getModelInstance('Statistics');
$all = $Model->getCollection(array(
	"date >= '{$graph_from}'",
	"date <= '{$graph_to}'",
));

$interval = 2592000;
$i = 0;
while ($i < 6 && count($all) < 2 && empty($_POST['grfrom']) && empty($_POST['grto'])) {
	if ($i < 5) {
		$interval += 2592000;
		$graph_from = date("Y-m-d", time() - $interval);
	} else {
		$graph_from = '0000-00-00 00:00:00';
	}
	$all = $Model->getCollection(array(
		"date >= '{$graph_from}'",
		"date <= '{$graph_to}'",
	));
	$i++;
}


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
$pageNavr = '';
include_once ROOT . '/admin/template/header.php';

?>


<div id="chart2"></div>
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
		
		<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jqplot/graphlib.js"></script>
		<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jqplot/plugins/jqplot.canvasTextRenderer.min.js"></script>
		<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>
		<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>
		<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jqplot/plugins/jqplot.highlighter.min.js"></script>
		<link href="<?php echo WWW_ROOT ?>/sys/js/jqplot/style.css" type="text/css" rel="stylesheet">
		<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/datepicker/datepicker.js"></script>
		<link type="text/css" rel="StyleSheet" href="<?php echo WWW_ROOT ?>/sys/js/datepicker/datepicker.css" />
		<script type="text/javascript">
		$(document).ready(function(){
			$('.tcal').datetimepicker({
				timepicker:false,
				format:'Y/m/d',
				closeOnDateSelect: true
			});
			var data = '<?php echo $json_data; ?>';
			data = eval(data);
			if (!data[0].length || !data[1].length) {
				$('#graph').hide();
				return false;
			}
			//data = '['+data+']';
			//alert(data);
		  var plot2 = $.jqplot ('graph', data, {
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
				  label: "Quantity",
				  labelRenderer: $.jqplot.CanvasAxisLabelRenderer
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
				fill: false,
				fillAndStroke: true,
				color:'#777',
				fillColor: '#a4a2a2',
				fillAlpha: 0.5,
				label:'Views',
				markerOptions: { style:'dimaond'}
			  },
			  {
				// Use a thicker, 5 pixel line and 10 pixel
				// filled square markers.
				lineWidth:5,
				label:'Hosts',
				color:'#96c703',
				fillColor: '#567703',
				markerOptions: { style:"filledSquare", size:10 }
			  }
			],
			grid: {
				background: '#f4f2f2'
			}
			});
		});
		</script>
		<div style="graph-wrapper"><div style="graph-container" id="graph"></div></div>
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
							&nbsp;<?php echo __('From') ?>&nbsp;:&nbsp;&nbsp;<input class="tcal" id="ffrom" type="text" name="grfrom" value="<?php echo (!empty($_POST['grfrom'])) ? date("Y/m/d", strtotime($_POST['grfrom'])) : date("Y/m/d", time() - 2592000); ?>"/>
							&nbsp;<?php echo __('To') ?>&nbsp;:&nbsp;&nbsp;<input class="tcal" id="fto" type="text" name="grto" value="<?php echo (!empty($_POST['grto'])) ? date("Y/m/d", strtotime($_POST['grto'])) : date("Y/m/d"); ?>"/>
						</td>
						<td><input type="submit" name="send" class="save-button" value="<?php echo __('Apply') ?>" /></td>
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
