<?php

##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      1.6.5                          ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    Document parser library        ##
## copyright     ©Andrey Brykin 2010-2013       ##
## last mod.     2013/02/17                     ##
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

/**
* Document parser
*
* Parse pages and data. Replaced chanks, snippets.
* Quote/unquote global tags.
*
* @author        Andrey Brykin
* @package       CMS Fapos
* @subpackage    Document parser
* @link          http://fapos.net
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
     * @var array
     */
    private $markes = array();



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
     * @return mixed|string
     */
    public function getPreview($message)
    {
        $outputContent = '';
		
		if (!empty($_SESSION['viewMessage'])) {
			$viewer = new Fps_Viewer_Manager;
			$context = array(
				'message' => $this->Register['PrintText']->print_page($message),
			);
			$outputContent = $viewer->view('previewmessage.html', $context);
		}
        return $outputContent;
    }


    /**
     * @return mixed|string
     */
    public function getErrors()
    {
		$viewer = new Fps_Viewer_Manager;
        $outputContent = '';
        if (!empty($_SESSION['FpsForm']['error'])) {
            $outputContent = $viewer->view('infomessage.html', array('info_message' => $_SESSION['FpsForm']['error']));
        }
        return $outputContent;
    }


	/**
	* @param       string $page
	* @return      data with parsed snippets
	*/
	public function parseSnippet($page)
    {
		$Register = Register::getInstance();
        $FpsDB = $Register['DB'];

        $tpl = preg_match_all('#\{\[([!]*)(\w+)\]\}#U', $page, $mas);
        for ($i= 0; $i < count($mas[2]); $i++) {
			$cached = true;
			$block_name = $mas[2][$i];
			if ($mas[1][$i] === '!') $cached = false;

			// Check cache
			if ($cached === true) {
				$cache_key = 'snippet_' . strtolower($block_name);
				$cache_key .= (!empty($_SESSION['user']['status'])) ? '_' . $_SESSION['user']['status'] : '_guest';
				
				if ($this->Cache->check($cache_key)) {
					$res = $this->Cache->read($cache_key);
					$page = str_replace($mas[0][$i], $res, $page);
					continue;
				}
			}
			
			
			// If no cache
			$sql = $FpsDB->select('snippets', DB_FIRST, array('cond' => array('name' => strtolower($block_name))));
			if (empty($sql[0])) continue;
            $limit = $sql[0];
			
			if (strtolower($block_name) == strtolower($limit['name'])) {
				ob_start();
				$str = eval($limit['body']);
				$res = ob_get_contents();
				ob_end_clean();
				$page = str_replace($mas[0][$i], $res, $page); 

				if ($cached === true) 
					$this->Cache->write($res, $cache_key, array());
			}
	    }
		return $page;
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
		
		
		
		$markers['powered_by'] = 'Fapos';
		$markers['site_title'] = Config::read('site_title');
		
		if (isset($_SESSION['user']) && isset($_SESSION['user']['name'])) {
			$markers['personal_page_link'] = get_url(getProfileUrl($_SESSION['user']['id']));
			$markers['fps_user_name'] = $_SESSION['user']['name'];
			$userGroup = $Register['ACL']->get_user_group($_SESSION['user']['status']);
			$markers['fps_user_group'] = $userGroup['title'];
		} else {
			$markers['personal_page_link'] = get_url('/users/add_form/');
			$markers['fps_user_name'] = 'Гость'; //TODO
			$markers['fps_user_group'] = 'Гости';
		}
		
		
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
			$chat_link = get_url('/chat/view_messages/');
			$markers['fps_chat'] = '<iframe id="fpsChat" src="' . $chat_link 
			. '" width="100%" height="400" style="overflow:auto; margin:0px; padding:0px; border:none;"></iframe>';
			$markers['fps_chat'] .= ChatModule::add_form();
		}
		
		
		$markers['counter'] = get_url('/sys/img/counter.png?rand=' . rand(0,999999));
		$markers['template_path'] = get_url('/template/' . Config::read('template'));
		$markers['www_root'] = WWW_ROOT;
		
		
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
	public function buildMenuNode($node, $class = 'class="fpsMainMenu"')
    {
		$out = '<ul ' . $class . '>';
		foreach ($node as $point) {
			if (empty($point['title']) || empty($point['url'])) continue;
			$out .= '<li>';
			
			
			$out .= $point['prefix'];
			$target = (!empty($point['newwin'])) ? ' target="_blank"' : '';
			$out .= '<a href="' . get_url($point['url']) . '"' . $target . '>' . $point['title'] . '</a>';
			$out .= $point['sufix'];
			
			if (!empty($point['sub']) && count($point['sub']) > 0) {
				$out .= $this->buildMenuNode($point['sub']);
			}
			
			$out .= '</li>';
		}
		$out .= '</ul>';
		return $out;
	}
	
}

