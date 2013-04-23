<?php
##################################################
##												##
## @Author:       Andrey Brykin (Drunya)        ##
## @Version:      1.1                           ##
## @Project:      CMS                           ##
## @package       CMS Fapos                     ##
## @subpackege    Pages navigation function     ##
## @copyright     ©Andrey Brykin 2010-2011      ##
## @last mod.     2011/12/18                    ##
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


// Функция возвращает html меню для постраничной навигации
function pagination( $total, $perPage, $url )
{
    $cntPages = ceil( $total / $perPage );
    if ( isset($_GET['page']) ) {
        $page = (int)$_GET['page'];
        if ( $page < 1 ) $page = 1;
    } else {
        $page = 1;
    }
    if ($page > $cntPages) $page = $cntPages;
    $Register = Register::getInstance();
    $Register['pagescnt'] = $cntPages;



	if (file_exists(ROOT . '/template/' . Config::read('template') . '/customize/pagination.php')) {
		include_once(ROOT . '/template/' . Config::read('template') . '/customize/pagination.php');
		if (function_exists('custom_pagination')) {
			return array(call_user_func('custom_pagination', array($page, $cntPages, $url)), $page);
		}
	}

	if ($cntPages < 1) return array('', $page);


	$url = get_url($url);
	$html = '&nbsp;Страницы: ';
	// Проверяем нужна ли стрелка "В начало"
	if ( $page > 3 )
		$startpage = '<a class="pages" href="'.$url.'&page=1"><<</a> ... ';
	else
		$startpage = '';
	// Проверяем нужна ли стрелка "В конец"
	if ( $page < ($cntPages - 2) )
		$endpage = ' ... <a class="pages" href="'.$url.'&page='.$cntPages.'">>></a>';
	else
		$endpage = '';

	// Находим две ближайшие станицы с обоих краев, если они есть
	if ( $page - 2 > 0 )
		$page2left = ' <a class="pages" href="'.$url.'&page='.($page - 2).'">'.($page - 2).'</a>  ';
	else
		$page2left = '';
	if ( $page - 1 > 0 )
		$page1left = ' <a class="pages" href="'.$url.'&page='.($page - 1).'">'.($page - 1).'</a>  ';
	else
		$page1left = '';
	if ( $page + 2 <= $cntPages )
		$page2right = '  <a class="pages" href="'.$url.'&page='.($page + 2).'">'.($page + 2).'</a>';
	else
		$page2right = '';
	if ( $page + 1 <= $cntPages )
		$page1right = '  <a class="pages" href="'.$url.'&page='.($page + 1).'">'.($page + 1).'</a>';
	else
		$page1right = '';

	// Выводим меню
	$html = $html.$startpage.$page2left.$page1left.'<strong class="pages">'.$page.'</strong>'.
		  $page1right.$page2right.$endpage;



	return array($html, $page);
}



?>