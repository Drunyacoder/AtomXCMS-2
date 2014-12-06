<?php

##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      1.7.5                          ##
## Project:      CMS                            ##
## package       CMS AtomX                      ##
## subpackege    Document parser library        ##
## copyright     ©Andrey Brykin 2010-2014       ##
## last mod.     2014/02/21                     ##
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

/**
* Document parser
*
* Parse pages and data. Replaced chanks, snippets.
* Quote/unquote global tags.
*
* @author        Andrey Brykin
* @package       CMS AtomX
* @subpackage    Document parser
* @link          http://atomx.net
*/
class Document_Parser {

	/**
	 * @var object
	 */
	private $Cache;
	
	/**
	 * @var string 
	 */
	public $templateDir;

    /**
     * @var bool|int
     */
	private static $levels = false;

    /**
     * @var int
     */
    private $maxLevels = 3;

    /**
     * @var object
     */
    private $Register;





	/**
	 *
	 */
	public function __construct()
    {
        $this->Register = Register::getInstance();
		$this->Cache = new Cache;
		$this->Cache->prefix = 'block';
		$this->Cache->cacheDir = ROOT . '/sys/cache/blocks/';
		$this->Cache->lifeTime = 3600;
	}
	

    /**
     * @param $message
     * @param $entity (object|array)
     * @return mixed|string
     */
    public function getPreview($message, $entity = false)
    {
        $outputContent = '';
		
		if (!empty($_SESSION['viewMessage'])) {
			$viewer = $this->Register['Viewer'];
			$context = array(
				'message' => $this->Register['PrintText']->parseBBCodes($message, $entity),
			);
			$outputContent = $viewer->view('previewmessage.html', $context);
		}
        return $outputContent;
    }


    public function wrapErrors($errors, $preprocess = false)
    {
        if ($preprocess) {
            if (is_array($errors)) {
                foreach ($errors as $k => $error) {
                    $errors[$k] = $this->completeErrorMessage($error);
                }
            } else $errors = $this->completeErrorMessage($errors);
        }

        $viewer = $this->Register['Viewer'];
        return $viewer->view('infomessage.html', array('info_message' => $errors));
    }


    /**
     * Displays HTTP error page & sends the HTTP headers
     * with error code.
     *
     * @param int $code
     */
    public function showHttpError($code = 404)
    {
        $headers = array(
            '404' => "HTTP/1.0 404 Not Found",
            '403' => "HTTP/1.0 403 Forbidden You don't have permission to access / on this server.",
            'ban' => "HTTP/1.0 403 Forbidden You don't have permission to access / on this server.",
            'hack' => "HTTP/1.0 403 Forbidden You don't have permission to access / on this server.",
        );
        if (!empty($headers[$code])) {
            header($headers[$code]);
        }


        // Set a markers collection used globalMarkers & addition key - "code"
        // which displays HTTP error code.
        $markers = array_merge(array(
            'code' => (string)$code,
        ), (array)$this->getGlobalMarkers());


        $viewer = $this->Register['Viewer'];
        $output = $viewer->view('error.html', array('context' => $markers));

        die($output);
    }


	/**
	 * Parse snippets in template
	 * Examples:
	 * {[snippet_name]}
	 * {[!snippet_name]}
	 * {[!snippet_name?param1=value1&param2=value2]}
	 *
	 * @param       string $page
	 * @return      data with parsed snippets
	 */
	public function parseSnippet($page)
    {
		$SnippetsParser = new AtmSnippets($page);
        return $SnippetsParser->parse();
	}
	
		
	/**
	* @param       string $page
	* @return      data with parsed global tags
	*/
	public function getGlobalMarkers($page = '')
    {
        $Register = Register::getInstance();
		$markers = array();
		
		$markers['fps_wday'] = date("D");
		$markers['fps_date'] = date("d-m-Y");
		$markers['fps_time'] = date("H:i");
		$markers['fps_year'] = date("Y");
		$markers['version'] = FPS_VERSION;
		
		
		$path = $Register['Config']->read('smiles_set');
		$path = (!empty($path) ? $path : 'fapos');
		$markers['smiles_set'] = $path;
		$path = ROOT . '/sys/img/smiles/' . $path . '/info.php';
		include $path;
		
		if (isset($smilesList) && is_array($smilesList)) {
			$markers['smiles_list'] = (isset($smilesInfo) && isset($smilesInfo['show_count'])) ? array_slice($smilesList, 0, $smilesInfo['show_count']) : $smilesList;
		} else {
			$markers['smiles_list'] = array();
		}
		
		
		
		$markers['powered_by'] = '©AtomX CMS';
		$markers['site_title'] = Config::read('site_title');
		
		if (isset($_SESSION['user']) && isset($_SESSION['user']['name'])) {
			$markers['fps_user'] = $_SESSION['user'];
			$markers['personal_page_link'] = get_url(getProfileUrl($_SESSION['user']['id']));
			$markers['fps_user_name'] = $_SESSION['user']['name'];
			$userGroup = $Register['ACL']->get_user_group($_SESSION['user']['status']);
			$markers['fps_user_group'] = $userGroup['title'];
		} else {
			$userGroup = $Register['ACL']->get_user_group(0);
			$markers['personal_page_link'] = get_url('/users/add_form/');
			$markers['fps_user_name'] = __('Гость'); //TODO
			$markers['fps_user_group'] = $userGroup['title'];
			$markers['fps_user'] = array(
				'id' => 0,
				'name' => $markers['fps_user_name'],
				'group' => $markers['fps_user_group'],
				'avatar' => get_url('/template/' . getTemplateName() . '/img/noavatar.png', false, false),
			);
		}
		

        $markers['atm_users_groups'] = $Register['ACL']->getGroups();
		$markers['fps_admin_access'] = ($Register['ACL']->turn(array('panel', 'entry'), false)) ? '1' : '0';
		$markers['fps_user_id'] = (!empty($_SESSION['user']['id'])) ? $_SESSION['user']['id'] : 0;
		
		
		$online = getWhoOnline();
		$markers['all_online'] = ($online['users'] + $online['guests']);
		$markers['users_online'] = $online['users'];
		$markers['guests_online'] = $online['guests'];
		$markers['online_users_list'] = (!empty($_SESSION['online_users_list'])) ? $_SESSION['online_users_list'] : '';
		$markers['count_users'] = getAllUsersCount();
		
		$overal_stats = getOveralStat();
		$markers['max_online_all_time'] = (!empty($overal_stats['max_users_online'])) 
		? intval($overal_stats['max_users_online']) : 0;
		$markers['max_online_all_time_date'] = (!empty($overal_stats['max_users_online_date'])) 
		? h($overal_stats['max_users_online_date']) : 'Uncnown';
		
	
		if (strstr($page, '{{ fps_chat }}')) {
			include_once ROOT . '/modules/chat/index.php';
            $markers['fps_chat'] = '<div id="fpsChat" '
                . ' style="width:100%; height:400px; overflow:auto; margin:0px; padding:0px; border:none;"></div>';
			$markers['fps_chat'] .= ChatModule::add_form();
		}
		
		
		$markers['counter'] = get_url('/sys/img/counter.png?rand=' . rand(0,999999), false, false);
		$markers['template_path'] = get_url('/template/' . getTemplateName(), false, false);
		$markers['www_root'] = WWW_ROOT;
		$markers['lang'] = getLang();
		
		
		$markers['fps_rss'] = $this->getRss();
		
		if (false !== (strpos($page, '{{ mainmenu }}'))) {
			$markers['mainmenu'] = $this->builMainMenu();
		}
		
		// today borned users
		$today_born = getBornTodayUsers();
		$tbout = '';
		if (count($today_born) > 0) {
			$names = array();
			foreach ($today_born as $user) {
				$names[] = get_link($user['name'], getProfileUrl($user['id']));
			}
			$tbout = implode(', ', $names);
		}
		$markers['today_born_users'] = (!empty($tbout)) ? $tbout : __('No birthdays today');
		
		return 	$markers;
	}
	
	
	/**
	* @return     list with RSS links
	*/
	public function getRss()
    {
		$rss = '';
		if (Config::read('rss_news', 'common')) {
			$rss .= get_img('/sys/img/rss_icon_mini.png') . get_link(__('News RSS'), '/news/rss/') . '<br />';
		}
		if (Config::read('rss_stat', 'common')) {
			$rss .= get_img('/sys/img/rss_icon_mini.png') . get_link(__('Stat RSS'), '/stat/rss/') . '<br />';
		}
		if (Config::read('rss_loads', 'common')) {
			$rss .= get_img('/sys/img/rss_icon_mini.png') . get_link(__('Loads RSS'), '/loads/rss/') . '<br />';
		}
		
		return $rss;
	}
	
	
	/** DEPRECATED
	* @param      string $page
	* @param      string $modul - current module
	* @return     data with head menu
	*/
	public function headMenu($page, $modul=NULL)
    {
        $Register = Register::getInstance();
		$this->ACL = $this->Register['ACL'];
		
		$menu = get_link('Главная', '/');
		if(isset($_SESSION['user']['name'])) {
			$menu = $menu . get_link('Мой профиль', getProfileUrl($_SESSION['user']['id'])) 
			. get_link('Выход', '/users/logout/');
			$menu = $menu . get_link('Пользователи', '/users/index/');
			if ($modul == 'forum') {
			    $menu = $menu . get_link('Поиск', '/search/');
			}
			
			// Есть ли непрочитанные сообщения в папке "Входящие"?
			$cntNewMsg = UserAuth::countNewMessages();
			if ( $cntNewMsg < 1 ) {
				$menu = $menu . get_link('Личные&nbsp;сообщения', '/users/in_msg_box/');
			} else {
				$menu = $menu . get_link('Новые&nbsp;сообщения', '/users/in_msg_box/', array('class' => 'newMessages'));
			}
		} else {
			$menu = $menu . get_link('Регистрация', '/users/add_form/') . get_link('Вход', '/users/login_form/');
		}		
		
		if ( isset( $_SESSION['user']['name'] ) and $this->ACL->turn(array('panel', 'entry'), false)) {
		$menu = $menu . get_link('Админка', '/admin/', array('target' => '_blank'));
	    }
		
		$menu .= '<a onClick="add_favorite(this);" title="Добавить в закладки" href="javascript:void(0);" >В закладки</a>';
		
		$html = str_replace('{headmenu}', $menu, $page);
	
		return $html;
	}
	
	
	/**
     * @return string
     *
	 * Build menu which creating in Admin Panel
	 */
	public function builMainMenu()
    {
		$menu_conf_file = ROOT . '/sys/settings/menu.dat';	
		if (!file_exists($menu_conf_file)) return false;
		$menudata = unserialize(file_get_contents($menu_conf_file));
	
		
		if (!empty($menudata) && count($menudata) > 0) {
			$out = $this->buildMenuNode($menudata, 'class="fpsMainMenu"');
		} else {
			return false;
		}
		return $out;
	}


    /**
     * @param  $node
     * @param string $class
     * @return string
     */
	public function buildMenuNode($node, $class = 'class="atm-menu"')
    {
		$Register = Register::getInstance();
		$out = '<ul ' . $class . '>';
		foreach ($node as $point) {
			if (empty($point['title']) || empty($point['url'])) continue;
			
			$sub = '';
			if (!empty($point['sub']) && count($point['sub']) > 0) {
				$sub .= $this->buildMenuNode($point['sub']);
			}
			
			$active = (!empty($Register['module']) && preg_match('#^/'.$Register['module'].'#i', $point['url']))
				? ' active'
				: '';
			$subclass = (!empty($sub)) 
				? ' atm-menu-sub'
				: '';
			$out .= "<li class=\"{$active}{$subclass}\">";
			
			
			$out .= $point['prefix'];
			$target = (!empty($point['newwin'])) ? ' target="_blank"' : '';
			$out .= '<a href="' . get_url($point['url']) . '"' . $target . '>' . $point['title'] . '</a>';
			$out .= $point['sufix'];
			
			$out .= $sub;
			
			$out .= '</li>';
		}
		$out .= '</ul>';
		return $out;
	}


    private function completeErrorMessage($message) {
        return "<li>$message</li>\n";
    }
}

