<?php  
if (!empty($_GET['ac']) && is_numeric($_GET['ac'])) {
	$headers = array(
		'404' => "HTTP/1.0 404 Not Found",
		'403' => "HTTP/1.0 403 Forbidden You don't have permission to access / on this server.",
	);
	if (!empty($headers[$_GET['ac']])) {
		header($headers[$_GET['ac']]);
	}
}
?>


<?php if (!empty($_GET['ac']) && $_GET['ac'] == 'hack') { ?>
	
	<table width="100%" height="100%" align="center" valign="center"><tr><td align="center">
		<span style="color:orange;font-size:30px">
			Система распознала вас как ХАКЕРА!
		</span>
		<br />
		<span style="color:#A09484;font-size:10px">
			Если Вы увидели это сообщение по ошибке,<br />
			ну что ж, мы сожалеем.<br />
			Обратитесь к администрации сайта.
		</span>
	</td></tr></table>
	
<?php } elseif (!empty($_GET['ac']) && $_GET['ac'] == '403') { ?>

	<table width="100%" height="100%" align="center" valign="center"><tr><td align="center"><span style="color:orange;font-size:30px">
	Извините, но доступ к этой странице закрыт. <br />
	Попробуйте перейти на другую страницу.
	</span></td></tr></table>
	
<?php } elseif (!empty($_GET['ac']) && $_GET['ac'] == '404') { ?>

	<table width="100%" height="100%" align="center" valign="center"><tr><td align="center"><span style="color:orange;font-size:30px">
	Извините, но данная страница не найдена. <br />
	Попробуйте перейти на другую страницу.
	</span></td></tr></table>


<?php } elseif (!empty($_GET['ac']) && $_GET['ac'] == 'ban') { ?>

	<table width="100%" height="100%" align="center" valign="center"><tr><td align="center"><span style="color:orange;font-size:30px">
		<span style="color:orange;font-size:30px">
			По ходу ты в БАНе
		</span>
		<br />
		<span style="color:#A09484;font-size:10px">Попробуй обратиться к Админу.</span>
	</span></td></tr></table>

	
<?php } else { ?>

	<table width="100%" height="100%" align="center" valign="center"><tr><td align="center"><span style="color:orange;font-size:30px">
	Извините, но произошла ошибка. <br />
	Попробуйте перейти на другую страницу или свяжитесь с администрацией.
	</span></td></tr></table>

<?php } ?>


<?php die(); ?>