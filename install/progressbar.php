<?php
@ini_set('display_errors', 0);
sleep(1);

$step = (isset($_GET['step'])) ? $_GET['step'] : '1';
if (!in_array($step, array(1, 2, 3))) $step = 1;

switch ($step) {
	case 1:
?>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<?php
		break;
	case 2:
?>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<?php
		break;
	case 3:
?>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<li class="act"></li>
<?php
		break;
	default:
	
		break;
}

?>