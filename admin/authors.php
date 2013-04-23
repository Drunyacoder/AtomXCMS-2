<?php
/*-----------------------------------------------\
| 												 |
| @Author:       Andrey Brykin (Drunya)          |
| @Email:        drunyacoder@gmail.com           |
| @Site:         http://fapos.net                |
| @Version:      1.3                             |
| @Project:      CMS                             |
| @package       CMS Fapos                       |
| @subpackege    Authors list (Admin Part)       |
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



$pageTitle = $page_title = __('Dev. Team');
$pageNav = $page_title;
$pageNavr = '<span style="float:right;"><a href="javascript://" onClick="showHelpWin(\'Арбайтен! Арбайтен! Арбайтен!\', \'А никто и не мешает\')">' . __('I want to be here') . '</a></span>';
include_once ROOT . '/admin/template/header.php';
?>


	
<div class="list">
	<div class="title">Authors</div>
	<div class="level1">
		<div class="items">
			<div class="setting-item">
				<div class="center">
					<h3>Idea by</h3>
					Andrey Brykin (Drunya)
				</div>
			</div>
			<div class="setting-item">
				<div class="center">
					<h3>Programmers</h3>
					Andrey Brykin (Drunya)<br />
					Danilov Alexandr (modos189)
				</div>
			</div>
			<div class="setting-item">
				<div class="center">
					<h3>Testers and audit</h3>
					Andrey Konyaev (Ater)<br />
					Laguta Dmitry (ARMI)<br />
					Roman Maximov (r00t_san)<br />
					Alexandr Verenik (Wasja)<br />
					Danilov Alexandr (modos189)
				</div>
			</div>
			<div class="setting-item">
				<div class="center">
					<h3>Marketing</h3>
					Andrey Brykin (Drunya)<br />
					Andrey Konyaev (Ater)
				</div>
			</div>
			<div class="setting-item">
				<div class="center">
					<h3>Design and Templates</h3>
					Andrey Brykin (Drunya)<br />
					Alexandr Bognar (Krevedko)<br />
					Roman Maximov (r00t_san)<br />
					Laguta Dmitry (ARMI)
				</div>
			</div>
			<div class="setting-item">
				<div class="center">
					<h3>Specialists by Security</h3>
					Roman Maximov (r00t_san)
				</div>
			</div>
			<div class="setting-item">
				<div class="center">
					<h3>Additional Software</h3>
					Andrey Brykin (Drunya)<br />
					Alexandr Verenik (Wasja)
				</div>
			</div>
		</div>
	</div>
</div>


<?php
include_once 'template/footer.php';
?>