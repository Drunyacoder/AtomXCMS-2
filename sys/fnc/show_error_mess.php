<?php

##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      0.7                            ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    Show errors messages function  ##
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

// Вспомогательная функция - выдает сообщение об ошибке
// и делает редирект на нужную страницу с задержкой
function showErrorMessage( $message = '', $error = '', $redirect = false, $queryString = '' ) {
	if ($redirect === true) {
		header('Refresh: ' . Config::read('redirect_delay') . '; url=http://' . $_SERVER['SERVER_NAME'] . get_url($queryString));
	}
	$View = new Fps_Viewer_Manager();
	$data['info_message'] = $message;
	$data['error_message'] = Config::read('debug_mode') ? $error : null;
	$html = $View->view('infomessagegrand.html', array('data' => $data));
	echo $html;
}

?>