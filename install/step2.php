<?php 
session_start(); 
define ('ROOT', dirname(dirname(__FILE__)));
?><!doctype html>
<html>
<head>
<title>Fapos CMS - вместе в будущее</title>
<meta content="text/html; charset=utf-8" http-equiv="content-type">
<link rel="shortcut icon" href="../sys/img/favicon.ico" type="image/x-icon">
<link type="text/css" rel="StyleSheet" href="css/style.css" />
<script language="JavaScript" type="text/javascript" src="../sys/js/jquery.js"></script>
</head>
<body>
<div id="head">
	<div id="logo"></div>
	<ul id="progressbar">
		<li class="progressbar-li"><img src="img/loader-progressbar.gif" style="display:none;" id="ajaxLoader3" /></li>
	</ul>
</div>
<div id="subhead"></div>


<div id="container2">

	<h3>Создание базы данных ...</h3>
	<img src="img/ajax_loader.gif" style="display:none;" id="ajaxLoader" />
	<div style="height:250px; overflow-y:scroll;" id="queries"></div>


	<br />
	<br />
	<br />
	<br />



</div>

<div id="footer2"></div>
<script type="text/javascript">

function doQueries() {
	$('#ajaxLoader').show();
	$.get('do_queries.php?', function(data){
		$('#queries').html(data);
		$('#ajaxLoader').hide();
		progressBar(3);
		
		$('#queries').scrollTop(1500);
	});
}
function progressBar(step) {
	$('#ajaxLoader3').show();
	$.get('progressbar.php?step='+step, function(data){
		$('#ajaxLoader3').hide();
		$('#progressbar').html(data);
	});
}
progressBar(2);
doQueries();
$.get('ping.php');
</script>
</body>
</html>