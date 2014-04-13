<?php
##################################################
##												##
## @Author:      Andrey Brykin (Drunya)         ##
## @Version:     1.0                            ##
## @Project:     CMS                            ##
## @package      CMS Fapos                      ##
## @subpackege   Show info message function     ##
## @copyright    ©Andrey Brykin 2010-2011       ##
## @last mod.    2012/04/12                     ##
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


// Вспомогательная функция - после выполнения пользователем каких-либо действий
// выдает информационное сообщение и делает редирект на нужную страницу с задержкой
function showInfoMessage( $message, $queryString = null ) 
{
	$Register = Register::getInstance();
	header( 'Refresh: ' . $Register['Config']->read('redirect_delay') . '; url=http://' . $_SERVER['SERVER_NAME'] . get_url($queryString));
	$html = file_get_contents( ROOT . '/template/' . getTemplateName() . '/html/default/infomessagegrand.html' );
	$html = str_replace( '{INFO_MESSAGE}', $message, $html );
	echo $html;
	die();
}

