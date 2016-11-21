<?php
/*-----------------------------------------------\
| 												 |
|  @Author:       Andrey Brykin (Drunya)         |
|  @Version:      1.7.2                          |
|  @Project:      CMS                            |
|  @package       CMS AtomX                      |
|  @subpackege    Forum Module                   |
|  @copyright     ©Andrey Brykin                 |
|  @last mod.     2014/03/13                     |
|------------------------------------------------|
|  any partial or not partial extension          |
|  CMS AtomX,without the consent of the          |
|  author, is illegal                            |
|------------------------------------------------|
|  Любое распространение                         |
|  CMS AtomX или ее частей,                      |
|  без согласия автора, является не законным     |
\-----------------------------------------------*/


/**
* forum functionaly
*
* @author      Andrey Brykin 
* @package     CMS AtomX
* @subpackage  Forum module
* @link        http://cms.develdo.com
*/
Class ForumModule extends Module {

	/**
	* @module_title  title of module
	*/
	public $module_title = 'Форум';
	/**
	* @template  layout for module
	*/
	public $template = 'forum';
	/**
	* @module module indentifier
	*/
	public $module = 'forum';
	
	/**
	 * Wrong extention for download files
	 */
	private $denyExtentions = array('.php', '.phtml', '.php3', '.html', '.htm', '.pl', '.PHP', '.PHTML', '.PHP3', '.HTML', '.HTM', '.PL', '.js', '.JS');
	
	
	
	/**
	 * @return main forum page content
	 */
	public function index($cat_id = null) 
	{
		//turn access
		$this->ACL->turn(array('forum', 'view_forums_list'));

        $this->addToPageMetaContext('entity_title', __('Forums list'));
		
		// navigation block
		$markers = array();
		$markers['navigation'] = get_link(__('Home'), '/') . __('Separator') 
		. get_link(__('Forums list'), '/forum/') . "\n";
		$markers['pagination'] = '';
		$markers['add_link'] = '';
		$markers['meta'] = '';
		$this->_globalize($markers);
		
		
		if ($this->cached && $this->Cache->check($this->cacheKey)) {
			$html = $this->Cache->read($this->cacheKey) . $this->_get_stat();
			return $this->_view($html);
		} 
		
		

		$conditions = array();
		if (!empty($cat_id) && is_numeric($cat_id)) {
			$cat_id = (int)$cat_id;
			if ($cat_id > 0) {
				$conditions['id'] = $cat_id;
			}
		}
		
		//get forums categories records
		$catsModel = $this->Register['ModManager']->getModelName('ForumCat');
		$catsModel = new $catsModel;
		$cats = $catsModel->getCollection(array($conditions), array('order' => 'previev_id'));
		if (empty($cats)) {
			$html = __('No categories') . "\n" . $this->_get_stat();
			return $this->_view($html);
		}
		//pr($cats); die();

		

		
		
		$conditions = (!empty($conditions)) ? array('in_cat' => $cat_id) : array();
		//$conditions[] = array("`parent_forum_id` IS NULL OR `parent_forum_id` = '0'");
		$conditions[] = array(
            'or' => array("`parent_forum_id` IS NULL", 'parent_forum_id' => 0),
        );
		$this->Model->bindModel('last_theme');
		$this->Model->bindModel('subforums');
		$_forums = $this->Model->getCollection($conditions, array(
			'order' => 'pos',
		));
		$_forums = $this->Model->addLastAuthors($_forums);
		
		

		//pr($_forums); die();
		
		//sort forums and subforums
		//after this we will be have $categories array with all cats, forum and subforums
		$forums = array();
		$categories = array();
		if (count($_forums) > 0) {
			foreach ($_forums as $forum) {
				$forums[$forum->getIn_cat()][] = $forum;
			}
		}
		
		
		foreach ($cats as $category) {
			$categories[$category->getId()] = $category;
			$categories[$category->getId()]->setForums(array());
			if (array_key_exists($category->getId(), $forums)) {
				$categories[$category->getId()]->setForums($forums[$category->getId()]);
				unset($forums[$category->getId()]); //clean memory
			} else {
				unset($categories[$category->getId()]); //we needen't empty categories
			}
		}

		
	
		foreach ($categories as $cat) {
			$cat->setCat_url(get_url('/forum/index/' . $cat->getId()));
			$forums = $cat->getForums();	
			if (!empty($forums)) {
				foreach ($forums as $forum) {
					$subforums = $forum->getSubforums();
					//forming forums
					$forum = $this->_parseForumTable($forum);
				}
			} else {
				$info = __('Subforum is empty');
			}
		}
		

		$source = $this->render('catlist.html', array('forum_cats' => $categories));
		$source .= $this->_get_stat();
		
		
		//write to cache ( only if records detected )
		if ($this->cached)
			$this->Cache->write($source, $this->cacheKey, $this->cacheTags);	
		
		
		return $this->_view($source);
	}

	

	/**
	 * @param array $forum
	 * @retrun string HTML forum table wiht replaced markers
	 */
	private function _parseForumTable($forum) 
	{
		// Summ posts and themes
		if ($forum->getSubforums() && count($forum->getSubforums()) > 0) {
			foreach ($forum->getSubforums() as $subforum) {
				$forum->setPosts($forum->getPosts() + $subforum->getPosts());
				$forum->setThemes($forum->getThemes() + $subforum->getThemes());
			}
		}
		

		$forum->setForum_url(get_url('/forum/view_forum/' . $forum->getId()));

		
		
		//выводим название темы в которой было добавлено последнее сообщение и дату его добавления
		if ($forum->getLast_theme_id() < 1) {
			$last_post = __('No posts');
			
		} else {
			
			// That situation can be in subforums
			if (!$forum->getLast_theme() || !$forum->getLast_author()) {
				$themesClass = $this->Register['ModManager']->getModelInstance('Themes');
				$themesClass->bindModel('last_author');
				$theme = $themesClass->getById($forum->getLast_theme_id());
				if ($theme) {
					$forum->setLast_theme($theme);
					$forum->setLast_author($theme->getLast_author());
				}
			}
		
		
		
			$last_post_title = (mb_strlen($forum->getLast_theme()->getTitle()) > 30) 
			? mb_substr($forum->getLast_theme()->getTitle(), 0, 30) . '...' : $forum->getLast_theme()->getTitle();
			
			
			$last_theme_author = __('Guest');
			if ($forum->getLast_author()) {
				$last_theme_author = get_link(h($forum->getLast_author()->getName()), 
				getProfileUrl($forum->getLast_author()->getId()), array('title' => __('To profile')));
			}
			
			
			$last_post = AtmDateTime::getDate($forum->getLast_theme()->getLast_post()) . '<br>' . get_link(h($last_post_title), 
				'/forum/view_theme/' . $forum->getLast_theme()->getId() . '?page=999', 
				array('title' => __('To last post')))
			    . __('Post author') . $last_theme_author;
		}
		$forum->setLast_post($last_post);
		

		
		// Ссылка "Править форум"
		$admin_bar = '';
		if ($this->ACL->turn(array('forum', 'replace_forums'), false)) {
			$admin_bar .= get_link('', 'forum/forum_up/' . $forum->getId(), array(
				'class' => 'fps-up'
			)) . '&nbsp;' . get_link('', 'forum/forum_down/' .$forum->getId(), array(
				'class' => 'fps-down'
			)) . '&nbsp;';
		}
		
		if ($this->ACL->turn(array('forum', 'edit_forums'), false)) {
			$admin_bar .= get_link('', 'forum/edit_forum_form/' . $forum->getId(), array(
				'class' => 'fps-edit'
			)) . '&nbsp;';
		}
		
		if ($this->ACL->turn(array('forum', 'delete_forums'), false)) {
			$admin_bar .= get_link('', 'forum/delete_forum/' . $forum->getId(),
			array(
				'class' => 'fps-delete', 
				'onClick' => "return confirm('" . __('Are you sure') . "')",
			)) . '&nbsp;';
		}
		$forum->setAdmin_bar($admin_bar);
		
		
		/* forum icon */
		$forum_icon = get_url('/sys/img/guest.gif');
		if (file_exists(ROOT . '/sys/img/forum_icon_' . $forum->getId() . '.jpg')) {
			$forum_icon = get_url('/sys/img/forum_icon_' . $forum->getId() . '.jpg');
		}
		$forum->setIcon_url($forum_icon);
		return $forum;
	}
	

	
	/**
	 * View threads list (forum)
	 */
	public function view_forum($id_forum = null) 
	{
		$id_forum = intval($id_forum);
		if (empty($id_forum) || $id_forum < 1) redirect('/forum/');
		
		//turn access
		$this->ACL->turn(array('forum', 'view_forums'));
		
		
		//who is here
		$who = array();	
		$dir = ROOT . '/sys/logs/forum/';
		$forumFile = $dir . $id_forum . '.dat';
		if (!file_exists($dir)) mkdir($dir, 0777, true);
		if (file_exists($forumFile)) {
			$who = unserialize(file_get_contents($forumFile));
		}
		
		
		if (isset($_SESSION['user'])) {
			if (!isset($who[$_SESSION['user']['id']])) {
				$who[$_SESSION['user']['id']]['profile_link'] = get_link(h($_SESSION['user']['name']), getProfileUrl($_SESSION['user']['id']));
				$who[$_SESSION['user']['id']]['expire'] = time() + 1000;
			}
		}
		
		
		$who_is_here = '';
		if (!empty($who)) {
			foreach ($who as $key => $val) {
				if ($val['expire'] < time()) {
					unset($who[$key]);
					continue;
				}
				$who_is_here .= $val['profile_link'] . ', ';
			}
		}
		file_put_contents($forumFile, serialize($who));
		//$context = array('who_is_here', substr($who_is_here, 0, -2));

		
		//are we have cache?
		if ($this->cached && $this->Cache->check($this->cacheKey)) {
			$source = $this->Cache->read($this->cacheKey);
		} else {

		
			// count themes for page nav
			$this->Model->bindModel('subforums');
			$this->Model->bindModel('category');
			$this->Model->bindModel('last_theme');
			$forum = $this->Model->getById($id_forum);
			if (empty($forum)) {
				return $this->showInfoMessage(__('Can not find forum'), '/forum/');
			}

			
			// Check access to this forum. May be locked by pass or posts count
			$this->__checkForumAccess($forum);
            $this->addToPageMetaContext('entity_title', h($forum->getTitle()));
			$forum_moderators = $this->ACL->getForumModerators($id_forum);
			if (!empty($forum_moderators) && is_array($forum_moderators))
				$forum->setModerators($forum_moderators);

			
			// reply link
			$addLink = ($this->ACL->turn(array('forum', 'add_themes'), false)) 
			? get_link(
				__('New topic'), 
				'/forum/add_theme_form/' . $id_forum,
				array('class' => 'fps-add-button forum')
			) : '';
			
			

			$themesClassName = $this->Register['ModManager']->getModelName('Themes');
			$themesClass = new $themesClassName;
			$themesClass->bindModel('author');
			$themesClass->bindModel('last_author');
			//$themes = $themesClass->getCollection(array('id_forum' => $id_forum));
			$total = $themesClass->getTotal(array('cond' => array('id_forum' => $id_forum)));
		
			
			
            list($pages, $page) = pagination(
				$total, 
				$this->Register['Config']->read('themes_per_page', 'forum'), 
				'/forum/view_forum/' . $id_forum
			);
            $this->addToPageMetaContext('page', $page);
			
			
			$themes = $themesClass->getCollection(
				array(
					'id_forum' => $id_forum
				), array(
					'page' => $page,
					'limit' => $this->Register['Config']->read('themes_per_page', 'forum'),
					'order' => 'important DESC, last_post DESC',
				)
			);
			
			
			// Nav block
			$markers = array();
			$markers['navigation'] = get_link(__('Forums list'), '/forum/') . __('Separator') 
			. get_link(h($forum->getTitle()), '/forum/view_forum/' . $id_forum);
			$markers['pagination'] = $pages;
			$markers['add_link'] = $addLink;
			$markers['meta'] = '';
			$this->_globalize($markers);
			
			
			$subforums = $forum->getSubforums();
			if (count($subforums) > 0) {
				foreach($subforums as $subforum) {
					$subforum = $this->_parseForumTable($subforum);
				}
	
				$forum->setCat_name(__('Subforums title'));
			}
		
			
			
			
			$cnt_themes_here = count($themes);
			if ($cnt_themes_here > 0 && is_array($themes)) {
				foreach ($themes as $theme) {
				
					$theme = $this->__parseThemeTable($theme);
					
					//set cache tags
					$this->setCacheTag(array(
						'theme_id_' . $theme->getId(),
					));
				}			
				$this->setCacheTag(array(
					'forum_id_' . $id_forum,
				));
				
				
			}
			
			$forum->setCount_themes_here($cnt_themes_here);
			$forum->setWho_is_here(substr($who_is_here, 0, -2));
			//$forum->setCount_themes(count($themes));


			$source = $this->render('themes_list.html', array(
				'themes' => $themes,
				'forum' => $forum,
			));
			
			
			//write cache
			if ($this->cached)
				$this->Cache->write($source, $this->cacheKey, $this->cacheTags);
		}
		
		


		return $this->_view($source);
	}

	
	
	/**
	 * Check access to this forum. 
	 * May be locked by pass or posts count
	 *
	 * @param array $forum
	 */
	private function __checkForumAccess($forum) 
	{
		if (!$forum->getLock_passwd() && !$forum->getLock_posts()) return;
		
		
		if ($forum->getLock_passwd()) {
			if (isset($_SESSION['access_forum_' . $forum->getId()])) {
				return;
				
			// if we have two generally meet one of them
			} else if ($forum->getLock_posts() && 
			(isset($_SESSION['user']['posts']) && $_SESSION['user']['posts'] >= $forum->getLock_posts())) {
				return;
				
			// Check sended password
			} else if (isset($_POST['forum_lock_pass'])) {
				if ($_POST['forum_lock_pass'] == $forum->getLock_passwd()) {
					$_SESSION['access_forum_' . $forum->getId()] = true;
					return;
				}
				$this->showInfoMessage(__('Wrong pass for forum'), '/forum/');
			} else {
				echo $this->render('forum_passwd_form.html', array());
				die();
			}
			
			
		// For lock by posts count
		} else if ($forum->getLock_posts()) {
			if (isset($_SESSION['user']['posts']) && $_SESSION['user']['posts'] >= $forum->getLock_posts()) {
				return;
			}
			$this->showInfoMessage(sprintf(__('locked forum by posts'), $forum->getLock_posts()), '/forum/');
		}
	}
	
	
	
	/**
	 * @param array $theme
	 * @param string $template
	 * If $template = FALSE, use default template file
	 * @retrun string HTML theme table with replaced markers
	 */
	private function __parseThemeTable($theme, $template = false) 
	{
		$htmltheme = null;
		
		//ICONS
		$themeicon = $this->__getThemeIcon($theme); 

		$theme->setTheme_url(entryUrl($theme, $this->module));

		
		//ADMINBAR 
		$adminbar = '';
		if ($this->ACL->turn(array('forum', 'edit_themes', $theme->getId_forum()), false) 
		|| (!empty($_SESSION['user']['id']) && $theme->getId_author() == $_SESSION['user']['id'] 
		&& $this->ACL->turn(array('forum', 'edit_mine_themes', $theme->getId_forum()), false))) {
			$adminbar .= get_link('', '/forum/edit_theme_form/' . $theme->getId(), array(
				'class' => 'fps-edit',
				'title' => 'Edit',
			));
		}
		
		
		if ($this->ACL->turn(array('forum', 'close_themes', $theme->getId_forum()), false)) {
			if ( $theme->getLocked() == 0 ) { // заблокировать тему
				$adminbar .= get_link('', '/forum/lock_theme/' . $theme->getId(), array(
					'class' => 'fps-close',
					'title' => 'Lock',
				));
			} else { // разблокировать тему
				$adminbar .= get_link('', '/forum/unlock_theme/' . $theme->getId(), array(
					'class' => 'fps-open',
					'title' => 'An lock',
				));
			}
		}
		
		
		if ($this->ACL->turn(array('forum', 'important_themes'), false)) {
			if ($theme->getImportant() == 1) {
				$adminbar .= get_link('', '/forum/unimportant/' . $theme->getId(), array(
					'class' => 'fps-unfix',
					'title' => 'Off on fix',
				));
			} else {
				$adminbar .= get_link('', '/forum/important/' . $theme->getId(), array(
					'class' => 'fps-fix',
					'title' => 'Important',
				));
			}
		}
		
		
		if ($this->ACL->turn(array('forum', 'delete_themes', $theme->getId_forum()), false) 
		|| (!empty($_SESSION['user']['id']) && $theme->getId_author() == $_SESSION['user']['id'] 
		&& $this->ACL->turn(array('forum', 'delete_mine_themes', $theme->getId_forum()), false))) {
			$adminbar .= get_link('', '/forum/delete_theme/' . $theme->getId(), array(
				'class' => 'fps-delete',
				'onClick' => "return confirm('" . __('Are you sure') . "')",
				'title' => 'Delete',
			));
		}
		$theme->setAdminbar($adminbar);
		
		
		//USER PROFILE
		$author_url = __('Guest');
		if ($theme->getId_author()) {
			$author_url = get_link(h($theme->getAuthor()->getName()), getProfileUrl($theme->getId_author()));
		}
		$theme->setAuthorUrl($author_url); 
	
		
		// Last post author
		$last_user = __('Guest');
		if ($theme->getId_last_author()) {
			$last_user = get_link(h($theme->getLast_author()->getName()), getProfileUrl($theme->getId_last_author()));
		}
		$last_page = get_link(__('To last'), '/forum/view_theme/' . $theme->getId() . '?page=99999');
		
		
		//NEAR PAGES
		$near_pages = '';
		if (($theme->getPosts() + 1) > $this->Register['Config']->read('posts_per_page', 'forum')) {
			$cnt_near_pages = ceil(($theme->getPosts() + 1) / $this->Register['Config']->read('posts_per_page', 'forum'));
			if ($cnt_near_pages > 1) {
				$near_pages .= '&nbsp;(';
				for ($n = 1; $n < ($cnt_near_pages + 1); $n++) { 
					if ($cnt_near_pages > 5 && $n > 3) {
						$near_pages .= '...&nbsp;' . get_link(($cnt_near_pages - 1), '/forum/view_theme/' . $theme->getId() . '?page=' 
									. ($cnt_near_pages - 1)) . '&nbsp;' . get_link($cnt_near_pages, '/forum/view_theme/' 
									. $theme->getId() . '?page=' . $cnt_near_pages) . '&nbsp;';				
						break;
					} else {
						if ($n > 5) break;
						$near_pages .= get_link($n, '/forum/view_theme/' . $theme->getId() . '?page=' . $n) . '&nbsp;';
					}
				}
				$near_pages .= ')';
			}
		}
		
		
		$theme->setLast_page($last_page);
		$theme->setLast_user($last_user);
		$theme->setThemeicon($themeicon);
		$theme->setFps_css_class(($theme->getImportant()) ? 'fps-theme-important' : '');
		$theme->setNear_pages($near_pages);
		$theme->setImportantly(($theme->getImportant() == 1) ? __('Important2') : '');

		return $theme;
	}
	

	
	/**
	 * Return theme icon
	 *
	 * @param array $theme
	 * @return string img HTML tag with URL to needed icon
	 */
	private function __getThemeIcon($theme) 
	{
		$hot_theme_limit = 20;
		
		if (isset($_SESSION['user'])) { // это для зарегистрированного пользователя
			// Если есть новые сообщения (посты) - только для зарегистрированных пользователей
			if (isset($_SESSION['newThemes']) and array_key_exists($theme->getId(), $_SESSION['newThemes'])) {
				if ($theme->getLocked() == 0) // тема открыта
					if ($theme->getPosts() > $hot_theme_limit)
						$themeicon = get_img('/template/'.$this->Register['Config']->read('template').'/img/folder_hot_new.gif'
						, array('width' => '19', 'height' => '18', 'alt' => __('New posts'), 'title' => __('New posts')));
					else
						$themeicon = get_img('/template/'.$this->Register['Config']->read('template').'/img/folder_new.gif'
						, array('width' => '19', 'height' => '18', 'alt' => __('New posts'), 'title' => __('New posts')));
				else // тема закрыта
					$themeicon = get_img('/template/'.$this->Register['Config']->read('template').'/img/folder_lock_new.gif'
					, array('width' => '19', 'height' => '18', 'alt' => __('New posts'), 'title' => __('New posts')));
						
						
			} else {
				if ( $theme->getLocked() == 0 ) // тема открыта
					if ($theme->getPosts() > $hot_theme_limit)
						$themeicon = get_img('/template/'.$this->Register['Config']->read('template').'/img/folder_hot.gif'
						, array('width' => '19', 'height' => '18', 'alt' => __('No new posts'), 'title' => __('No new posts')));
					else
						$themeicon = get_img('/template/'.$this->Register['Config']->read('template').'/img/folder.gif'
						, array('width' => '19', 'height' => '18', 'alt' => __('No new posts'), 'title' => __('No new posts')));
				else // тема закрыта
					$themeicon = get_img('/template/'.$this->Register['Config']->read('template').'/img/folder_lock.gif'
					, array('width' => '19', 'height' => '18', 'alt' => __('No new posts'), 'title' => __('No new posts')));
			}
			
			
		} else { // это для не зарегистрированного пользователя
			if ( $theme->getLocked() == 0 ) // тема открыта
				if ($theme->getPosts() > $hot_theme_limit)
					$themeicon = get_img('/template/'.$this->Register['Config']->read('template').'/img/folder_hot.gif'
					, array('width' => '19', 'height' => '18'));
				else
					$themeicon = get_img('/template/'.$this->Register['Config']->read('template').'/img/folder.gif'
					, array('width' => '19', 'height' => '18'));
			else // тема закрыта
				$themeicon = get_img('/template/'.$this->Register['Config']->read('template').'/img/folder_lock.gif'
				, array('width' => '19', 'height' => '18'));
		}
		
		return $themeicon;
	}
	

	
	/**
	 * Return posts list
	 */
	public function view_theme($id_theme = null) 
	{
		$id_theme = (int)$id_theme;
		if (empty($id_theme) || $id_theme < 1) redirect('/forum/');

		
		
		$themeModel = $this->Register['ModManager']->getModelInstance('Themes');
		$themeModel->bindModel('forum');
		$themeModel->bindModel('poll');
		$theme = $themeModel->getById($id_theme);

		
		if (!$theme->getForum())  return $this->showInfoMessage(__('Can not find forum'), '/forum/' );
		
		//turn access
		$this->ACL->turn(array('forum', 'view_themes'));

		
		// Check access to this forum. May be locked by pass or posts count
		$this->__checkForumAccess($theme->getForum());
		$id_forum = $theme->getId_forum();

		$this->__checkThemeAccess($theme);
		
		
		
		if ($this->cached && $this->Cache->check($this->cacheKey)) {
			$source = $this->Cache->read($this->cacheKey);
		} else {
		
			
			// Если запрошенной темы не существует - возвращаемся на форум
			if (empty($theme)) return $this->showInfoMessage(__('Topic not found'), '/forum/' );


			// Заголовок страницы (содержимое тега title)
            $this->addToPageMetaContext('entity_title', h($theme->getTitle()));
            $this->addToPageMetaContext('category_title', h($theme->getForum()->getTitle()));

			$markers = array();
			$markers['navigation'] = get_link(__('Forums list'), '/forum/') . __('Separator') . get_link($theme->getForum()->getTitle(), 
			'/forum/view_forum/' .  $id_forum) . __('Separator') . get_link($theme->getTitle(), '/forum/view_theme/' . $id_theme);
			if (!empty($description)) {
				$markers['navigation'] .= ' (' . $theme->getDescription() . ')';
			}
			
			
			// Page nav
			$postsModelName = $this->Register['ModManager']->getModelName('Posts');
			$postsModel = new $postsModelName;
			$total = $postsModel->getTotal(array('cond' => array('id_theme' => $id_theme)));
			
			if ($total === 0) {
				$this->__delete_theme($id_theme);
				return $this->showInfoMessage(__('Topic not found'), '/forum/view_forum/' . $id_forum );
			}
            list($pages, $page) = pagination($total, $this->Register['Config']->read('posts_per_page', 'forum'), '/forum/view_theme/' . $id_theme );
            $markers['pagination'] = $pages;
            $this->addToPageMetaContext('page', $page);
			
			
			// SELECT posts
			$postsModel->bindModel('author');
			$postsModel->bindModel('editor');
			$postsModel->bindModel('attacheslist');
			$posts = $postsModel->getCollection(array(
				'id_theme' => $id_theme,
			), array(
				'order' => 'time ASC, id ASC',
				'page' => $page,
				'limit' => $this->Register['Config']->read('posts_per_page', 'forum'),
			));

			
			
			// Ссылка "Ответить" (если тема закрыта - выводим сообщение "Тема закрыта")
			if ($theme->getLocked() == 0) {
				$markers['add_link'] = get_link(get_img('/template/' 
				. $this->Register['Config']->read('template').'/img/reply.png', array('alt' => __('Answer'), 
				'title' => __('Answer'))), '/forum/view_theme/' . $id_theme . '#sendForm');
			} else {
				$markers['add_link'] = ''; // __('Theme is locked')
				$markers['closed_theme'] = true;
			}
			
			
			if (!$this->ACL->turn(array('forum', 'add_posts', $theme->getId_forum()), false)) $markers['add_link'] = '';
			$markers['meta'] = '';
			$this->_globalize($markers);
			
			
			$post_num = (($page - 1) * $this->Register['Config']->read('posts_per_page', 'forum'));
			
			
			//serialize rating settings
			$settingsModelName = $this->Register['ModManager']->getModelName('UsersSettings');
			$settingsModel = new $settingsModelName;
			$rating_settings = $settingsModel->getCollection(array('type' => 'rating'));
			$rating_settings = (count($rating_settings) > 0) ? $rating_settings[0]->getValues() : ''; 
			
			
			$usersModel = $this->Register['ModManager']->getModelInstance('Users');
			$first_top = false;
			if ($page > 1 && $theme->getFirst_top() == '1') {
				$post = $postsModel->getCollection(array(
					'id_theme' => $id_theme,
				), array(
					'order' => 'time ASC, id ASC',
					'limit' => 1,
				));
				if (is_array($post) && count($post) == 1) {
					$posts = array_merge($post, $posts);
					$first_top = true;
				}
			}

			
			foreach ($posts as $post) {
				// Если автор сообщения (поста) - зарегистрированный пользователь
				$postAuthor = $post->getAuthor();
				if ($post->getId_author()) {

					// Аватар
					if (is_file(ROOT . '/sys/avatars/' . $post->getId_author() . '.jpg')) {
						$postAuthor->setAvatar(get_url('/sys/avatars/' . $post->getId_author() . '.jpg'));
					} else {
						$postAuthor->setAvatar(get_url('/sys/img/noavatar.png'));
					}


					// Статус пользователя
					$status = $this->ACL->get_group_info();
					$user_status = (array_key_exists($postAuthor->getStatus(), $status))
                        ? $status[$postAuthor->getStatus()] : $status[0];
					$postAuthor->setStatus_title($user_status['title']);
					

					// Рейтинг пользователя (по количеству сообщений)
					$rating = $postAuthor->getPosts();
					$rank_star = getUserRating($rating, $rating_settings);
					$postAuthor->setRank($rank_star['rank']);
					if ($postAuthor->getState()) $postAuthor->setRank($postAuthor->getState());
					$postAuthor->setUser_rank(get_img('/sys/img/' . $rank_star['img']));
					

					// Если автор сообщения сейчас "на сайте"
					$users_on_line = getOnlineUsers(); 
					if (isset($users_on_line) &&  isset($users_on_line[$post->getId_author()])) {
						$postAuthor->setStatus_on(__('Online'));
					} else {
						$postAuthor->setStatus_on(__('Offline'));
					}


					// Если пользователь заблокирован
					if ($postAuthor->getBlocked())
						$postAuthor->setStatus_on('<span class="statusBlock">' . __('Banned') . '</span>');



                // Если автор сообщения - незарегистрированный пользователь
				} else {
				    $postAuthor->setAvatar(get_url('/sys/img/noavatar.png'));
				    $postAuthor->setName(__('Guest'));
				}
				

				$message = $this->Textarier->parseBBCodes($post->getMessage(), $post);
				$post->setMessage($message);
				

				$signature = ($postAuthor->getSignature())
				? $this->Textarier->getSignature($postAuthor->getSignature(), $postAuthor->getStatus()) : '' ;
                $postAuthor->setSignature($signature);
				
				
				// If author is authorized user. 
				$email = '';
				$privat_message = '';
				$author_site = '';
                $user_profile = '';
				$icon_params = array('class' => 'user-details');
				
				
				if ($post->getId_author()) {
					$user_profile = '&nbsp;' . get_link(
						get_img(
							'/sys/img/icon_profile.gif', 
							array(
								'alt' => __('View profile'), 
								'title' => __('View profile')
							)
						), 
						getProfileUrl($post->getId_author()), 
						$icon_params
					);
					
					
					if (isset($_SESSION['user'])) {
						$email = '&nbsp;' . get_link(
							get_img(
								'/sys/img/icon_email.gif', 
								array('alt' => __('Send mail'), 'title' => __('Send mail'))
							), 
							'/users/send_mail_form/' . $post->getId_author(), 
							$icon_params
						);
						$privat_message = '&nbsp;' . get_link(
							get_img(
								'/sys/img/icon_pm.gif', 
								array('alt' => __('PM'), 'title' => __('PM'))
							), 
							'/users/pm_send_form/' . $post->getId_author(),
							$icon_params
						);
					}
					
					
					$author_site = ($post->getAuthor()->getUrl()) 
						? '&nbsp;' . get_link(
							get_img(
								'/sys/img/icon_www.gif', 
								array('alt' => __('Author site'), 'title' => __('Author site'))
							), 
							h($post->getAuthor()->getUrl()), 
							array_merge($icon_params, array('target' => '_blank')), true) 
						: '';
				}


				$post->getAuthor()->setAuthor_site($author_site);
				$post->getAuthor()->setProfile_url($user_profile);
				$post->getAuthor()->setEmail_url($email);
				$post->getAuthor()->setPm_url($privat_message);
				
				
				// Если сообщение редактировалось...
				if ($post->getId_editor()) {
					if ($post->getId_author() && $post->getId_author() == $post->getId_editor()) {
						$editor = __('Edit by author') . ' ' . AtmDateTime::getDate($post->getEdittime());
					} else {
						$status_info = $this->ACL->get_user_group($post->getEditor()->getStatus());
						$editor = __('Edited') . $post->getEditor()->getName() . '(' 
							. $status_info['title'] . ') ' . AtmDateTime::getDate($post->getEdittime());
					}
				} else {
					$editor = '';
				}
				$post->setEditor_info($editor);
				
				
				//edit and delete links
				$edit_link = '';
				$delete_link = '';
				if (!empty($_SESSION['user'])) {
				
					if ($this->ACL->turn(array('forum', 'edit_posts', $theme->getId_forum()), false) 
					|| (!empty($_SESSION['user']['id']) && $post->getId_author() == $_SESSION['user']['id'] 
					&& $this->ACL->turn(array('forum', 'edit_mine_posts', $theme->getId_forum()), false))) {
						$edit_link = get_link('', '/forum/edit_post_form/' . $post->getId(), array(
							'class' => 'fps-edit',
						));
					} 
					
					
					if ($this->ACL->turn(array('forum', 'delete_posts', $theme->getId_forum()), false) 
					|| (!empty($_SESSION['user']['id']) && $post->getId_author() == $_SESSION['user']['id'] 
					&& $this->ACL->turn(array('forum', 'delete_mine_posts', $theme->getId_forum()), false))) {
						$delete_link = get_link('', '/forum/delete_post/' . $post->getId(), array(
							'class' => 'fps-delete',
							'onClick' => "return confirm('" . __('Are you sure') . "')",
						));
					}
				}
				
				
				$on_top = get_link('', '#top', array('class' => 'fps-up'), true);
				$post->setOn_top_link($on_top);
				$post->setEdit_link($edit_link);
				$post->setDelete_link($delete_link);
				
				
				
				//message number
				if ($first_top) {
					$post->setPost_number(1);
					$first_top = false;
				} else {
					$post_num++;
					$post->setPost_number($post_num);
				}
				$post_number_url = 'http://' . $_SERVER['HTTP_HOST'] 
				. get_url('/' . $this->module . '/view_post/' . $post->getId(), true);
				$post->setPost_number_url($post_number_url);

				

				//set tags for cache
				$this->setCacheTag(array(
					'post_id_' . $post->getId(),
					'user_id_' . $post->getId_author(),
				));
			}
			$this->setCacheTag('theme_id_' . $id_theme);
			
			
			// Polls render
			$polls = $theme->getPoll();
			if (!empty($polls[0])) {
				$theme->setPoll($this->_renderPoll($polls[0]));
			} else {
				$theme->setPoll('');
			}
			
			
			
			$markers = array(
				'reply_form' => $this->add_post_form($theme),
			);
			$this->_globalize($markers);
			
			$source = $this->render('posts_list.html', array(
				'posts' => $posts,
				'theme' => $theme,
			));
			
			
			//write into cache
			if ($this->cached)
				$this->Cache->write($source, $this->cacheKey, $this->cacheTags);
		}

		
		// Если страницу темы запросил зарегистрированный пользователь, значит он ее просмотрит
		if ( isset( $_SESSION['user'] ) and isset( $_SESSION['newThemes'] ) ) {
			if ( count( $_SESSION['newThemes'] ) > 0 ) {
				if ( in_array( $id_theme, $_SESSION['newThemes'] ) ) {
					unset( $_SESSION['newThemes'][$id_theme] );
				}
			} else {
				unset( $_SESSION['newThemes'] );
			}
		} 
		
		
		$theme->setViews($theme->getViews() + 1);
		$theme->save();	
		$_SESSION['VIEW_PAGE'] = 'theme' . $id_theme;
		
		
		//clean cache
		$this->Cache->clean(CACHE_MATCHING_TAG, array('action_viev_forum', 'theme_id_' . $id_theme));
		return $this->_view($source);
	}
	
	
	
	private function __savePoll($theme) 
	{
		if (!empty($_POST['poll']) && !empty($_POST['poll_ansvers'])) {
			
			$ansvers = explode("\n", trim($_POST['poll_ansvers']));
			
			$variants = array();
			if (count($ansvers) && is_array($ansvers)) {
				foreach ($ansvers as $ansver) {
					$variants[] = array(
						'ansver' => $ansver,
						'votes' => 0,
					);
				}
			}
			
			
			$question = (!empty($_POST['poll_question'])) ? trim((string)$_POST['poll_question']) : '';
			
			
			$data = array(
				'variants' => json_encode($variants),
				'question' => $question,
				'theme_id' => $theme->getId(),
				'voted_users' => '',
			);
			
			
			$poll = new PollsEntity($data);
			$poll->save();
			return true;
		}
		return false;
	}

	
	
	protected function _renderPoll($poll) 
	{
		if (!$poll) {
		
		}
		
		
		$questions = json_decode($poll->getVariants(), true);
		if (!$questions && !is_array($questions)) {
		
		}
			
			
		$all_votes_summ = 0;
		foreach ($questions as $case) {
			$all_votes_summ += $case['votes']; 
		}
		
		// Find 1% value
		$percent = round($all_votes_summ / 100, 2);
		
		
		// Show percentage graph for each variant
		foreach ($questions as $k => $case) {
			$questions[$k] = array(
				'ansver' => h($case['ansver']),
				'votes'			=> $case['votes'],
				'percentage'  	=> ($case['votes'] > 0) ? round($case['votes'] / $percent) : 0,
				'ansver_id'  	=> $k + 1,
			);
			
			//$poll->setPercentage(round($case / $percent)); 
		}
		
		$poll->setVariants($questions);
		
		
		// Did user voted
		if (!empty($_SESSION['user'])) {
			$voted_users = explode(',', $poll->getVoted_users());
			if ($voted_users && is_array($voted_users)) {
				
				
				if (!in_array($_SESSION['user']['id'], $voted_users)) {
					$poll->setCan_voted(1);
				}
			}
		}
	
		
		return $this->render('polls.html', array('poll' => $poll));
	}
	
	
	
	/**
	 *
	 */
	public function vote_poll($id)
	{	
		if (empty($_SESSION['user'])) die('ERROR: permission denied');
	
		$id = (int)$id;
		if ($id < 1) die('ERROR: empty ID');
		
		
		$ansver_id = (!empty($_GET['ansver'])) ? (int)$_GET['ansver'] : 0;
		if ($ansver_id < 1) die('ERROR: empty ANSVER_ID');
		
	
		$pollModel = new PollsModel;
		$poll = $pollModel->getById($id);
		
		if (empty($poll)) die('ERROR: poll not found');
		
		$variants = json_decode($poll->getVariants(), true);
		if ($variants && is_array($variants)) {
			
			
			if (!array_key_exists($ansver_id - 1, $variants)) die('ERROR: wrong ansver');
			
			
			// Check user ability
			$voted_users = explode(',', $poll->getVoted_users());
			if (!empty($voted_users)) {
				if (in_array($_SESSION['user']['id'], $voted_users)) {
					die('ERROR: you already voted');
				} else {
					$voted_users[] = $_SESSION['user']['id'];
				}
				
			} else {
				$voted_users = array($_SESSION['user']['id']);
			}
			
			$poll->setVoted_users(implode(',', $voted_users));
			
			
			$variants[$ansver_id - 1]['votes']++;
			
			$poll->setVariants(json_encode($variants));
			$poll->save();
			
			
			
			
			// Create response data for AJAX request
			$all_votes_summ = 0;
			foreach ($variants as $case) {
				$all_votes_summ += $case['votes']; 
			}
			
			// Find 1% value
			$percent = round($all_votes_summ / 100, 2);
			
			
			// Show percentage graph for each variant
			foreach ($variants as $k => $case) {
				$variants[$k] = array(
					'ansver' 		=> h($case['ansver']),
					'votes'			=> $case['votes'],
					'percentage'  	=> ($case['votes'] > 0) ? round($case['votes'] / $percent) : 0,
					'ansver_id'  	=> $k + 1,
				);
			}
			
			die(json_encode($variants));
		}
		
		die('ERROR');
	}
	
	
	
	private function __checkThemeAccess($theme)
	{
		$fid = $theme->getForum()->getId();
		$rules = $theme->getGroup_access();
		$id = (!empty($_SESSION['user']['status'])) ? $_SESSION['user']['status'] : 0;
		
		foreach ($rules as $k => $v) if ('' === $v) unset($rules[$k]);
		
		if (in_array($id, $rules)) {
			return $this->showInfoMessage(__('Permission denied'), '/forum/view_forum/' . $fid );
		}
	}

	
	
	/**
	 * View last posts and last themes
	 * Build list with themes ordered by add date
	 *
	 * @return string html content
	 */
	public function last_posts()
    {
        $this->addToPageMetaContext('entity_title', __('Last update'));
    
		if ($this->cached && $this->Cache->check($this->cacheKey)) {
			$html = $this->Cache->read($this->cacheKey);
			return $this->_view($html);
        }
		
		
		// Page nav
		$nav = array();
		$themesModelName = $this->Register['ModManager']->getModelName('Themes');
		$themesModel = new $themesModelName;
		$total = $themesModel->getTotal();
		$perPage = $this->Register['Config']->read('themes_per_page', 'forum');
        list($pages, $page) = pagination($total, $perPage, '/forum/last_posts/');
        $nav['pagination'] = $pages;
        $this->addToPageMetaContext('page', $page);

		
		$cntPages = ceil($total / $perPage);
		$recOnPage = ($page == $cntPages) ? ($total % $perPage) : $perPage;
		$nav['navigation'] = get_link(__('Forums list'), '/forum/') . __('Separator') . __('Last update');
		$nav['meta'] = __('Count all topics') . $total . '. ' . __('Count visible') . $recOnPage;
		$this->_globalize($nav);
		
		if ($total < 1) return $this->_view(__('No topics'));
		
		
		
		//get records
		$themesModel->bindModel('forum');
		$themesModel->bindModel('author');
		$themesModel->bindModel('last_author');
		$themes = $themesModel->getCollection(array(), array(
			'order' => 'last_post DESC',
			'page' => $page,
			'limit' => $this->Register['Config']->read('themes_per_page', 'forum'),
		));
		
		
		foreach ($themes as $theme) {
			$theme_pf = get_link($theme->getForum()->getTitle(), '/forum/view_forum/' . $theme->getId_forum());
			$theme->setParent_forum($theme_pf);
			$theme = $this->__parseThemeTable($theme);
			
			//set cache tags
			$this->setCacheTag(array(
				'theme_id_' . $theme->getId(),
			));
		}
		
		
		// write into cache
		if ($this->cached) {
			$this->Cache->write($html, $this->cacheKey, $this->cacheTags);
		}
		
		//pr($themes); die();
		$source = $this->render('lastposts_list.html', array(
			'context' => array(
				'forum_name' => __('Last update'),
			),
			'themes' => $themes
		));
		$this->_view($source);
	}
	

	
	/**
	 * Create HTML form for edit forum and paste current values into inputs
	 */
	public function edit_forum_form($id_forum = null) {
		//check access
		$this->ACL->turn(array('forum', 'edit_forums'));
		if (!isset($_SESSION['user'])) redirect('/forum/');

		$id_forum = intval($id_forum);
		if (empty($id_forum) || $id_forum < 1) redirect('/forum/');


		// Получаем из БД информацию о форуме
		$forum = $this->Model->getById($id_forum);
		$action = get_url('/forum/update_forum/' . $id_forum);

		
		// Если при заполнении формы были допущены ошибки
		if (isset($_SESSION['FpsForm'])) {
			$errors      = $this->Register['Validate']->getErrors();
			$title       = h($_SESSION['FpsForm']['title']);
			$description = h($_SESSION['FpsForm']['description']);
			unset($_SESSION['FpsForm']);
		} else {
			$title       = h($forum->getTitle());
			$description = h($forum->getDescription());
		}
		
		
		// Считываем в переменную содержимое файла,
		// содержащего форму для редактирования форума
		$source = $this->render('editforumform.html', array(
			'context' => array(
				'errors' => !empty($errors) ? $errors : '',
				'action' => $action,
				'title' => $title,
				'description' => $description,
			),
		));

		
		// nav block
		$navi = array(
			'navigation' => get_link(__('Forums list'), '/forum/')
                . __('Separator') . __('Edit forum'),
		);
		$this->_globalize($navi);


		return $this->_view($source);
	}


	
	/**
	 * Get request and work for it. Validate data and update record
	 */
	public function update_forum($id_forum = null) {
		//check access
		$this->ACL->turn(array('forum', 'edit_forums'));
		if (!isset($_SESSION['user']))  redirect('/forum/');

		$id_forum = intval($id_forum);
		if (empty($id_forum) || $id_forum < 1) redirect('/forum/');

		
		$forum = $this->Model->getById($id_forum);
		if (!$forum) return $this->showInfoMessage(__('Can not find forum'), '/forum/');
		

		// Обрезаем переменные до длины, указанной в параметре maxlength тега input
		$title       = mb_substr($_POST['title'], 0, 120 );
		$description = mb_substr($_POST['description'], 0, 250 );
		$title       = trim($title);
		$description = trim($description);

		
		// Check fields fo empty values and valid chars
        $errors = $this->Register['Validate']->check($this->Register['action']);


		// if an errors
		if (!empty($errors)) {
			$_SESSION['FpsForm'] = array();
			$_SESSION['FpsForm']['errors'] = $errors;
			$_SESSION['FpsForm']['title'] = $title;
			$_SESSION['FpsForm']['description'] = $description;
			
			redirect('/forum/edit_forum_form/' . $id_forum);
		}
		
		
		$forum->setTitle($title);
		$forum->setDescription($description);
		$forum->save();

		//clean cache
		$this->Cache->clean(CACHE_MATCHING_TAG, array('forum_id_' . $id_forum));
		$this->DB->cleanSqlCache();
		if ($this->Log) $this->Log->write('editing forum', 'forum id(' . $id_forum . ')');
		return $this->showInfoMessage(__('Forum update is successful'), '/forum/' );
	}


	
	/**
	* raise forum
	*
	* @id_forum (int)    forum ID
	* @return           info message
	*/
	public function forum_up($id_forum = null) {
		//check access
		$this->ACL->turn(array('forum', 'replace_forums'));
		if (!isset($_SESSION['user']))  redirect('/forum/');
		$id_forum = intval($id_forum);
		if (empty($id_forum) || $id_forum < 1) redirect('/forum/');
		

		
		// upper forum
		$id_forum_up = intval($id_forum);
		$forum = $this->Model->getById($id_forum_up);
		if (!$forum) return $this->showInfoMessage(__('Can not find forum'), '/forum/');
		// upper position
		$order_up = $forum->getPos();
		
		
		
		$dforum = $this->Model->getFirst(array(
			'pos < ' . $order_up, 
			'in_cat' => $forum->getIn_cat()
		), array(
			'order' => 'pos DESC',
		));
		if (!$dforum) return $this->showInfoMessage(__('Forum is above all'), '/forum/' );
		
		
	
		// Порядок следования и ID форума, который находится выше и будет "опущен" вниз
		// ( поменявшись местами с форумом, который "поднимается" вверх )
		$id_forum_down = $dforum->getId();
		$order_down    = $dforum->getPos();
		
		// replace forums
		$dforum->setPos($order_up);
		$res1 = $dforum->save();
		
		$forum->setPos($order_down);
		$res2 = $forum->save();

		
		//clean cache
		$this->Cache->clean(CACHE_MATCHING_TAG, array('forum_id_' . $id_forum));
		$this->DB->cleanSqlCache();
		
		if ($this->Log) $this->Log->write('uping forum', 'forum id(' . $id_forum . ')');
		if ($res1 && $res2)
			return $this->showInfoMessage(__('Operation is successful'), '/forum/' );
		else
			return $this->showInfoMessage(__('Some error occurred'), '/forum/' );
	}


	
	/**
	* down forum
	*
	* @id_forum (int)    forum ID
	* @return           info message
	*/
	public function forum_down($id_forum = null) {
		//check access
		$this->ACL->turn(array('forum', 'replace_forums'));
		if (!isset($_SESSION['user']))  redirect('/forum/');
		$id_forum = intval($id_forum);
		if (!isset($id_forum)) redirect('/forum/');
	
	
		
		// downing forum
		$id_forum_down = $id_forum;
		$forum = $this->Model->getById($id_forum_down);
		if (!$forum) return $this->showInfoMessage(__('Can not find forum'), '/forum/');
		// upper position
		$order_down = $forum->getPos();
		
		
		$dforum = $this->Model->getFirst(array(
			'pos > ' . $order_down, 
			'in_cat' => $forum->getIn_cat()
		), array(
			'order' => 'pos',
		));
		if (!$dforum) return $this->showInfoMessage(__('Forum is below all'), '/forum/' );
		
	
		// Порядок следования и ID форума, который находится ниже и будет "поднят" вверх
		// ( поменявшись местами с форумом, который "опускается" вниз )
		$id_forum_up = $dforum->getId();
		$order_up    = $dforum->getPos();
		
		// replace forums
		$dforum->setPos($order_down);
		$res1 = $dforum->save();
		
		$forum->setPos($order_up);
		$res2 = $forum->save();

		
		//clean cache
		$this->Cache->clean(CACHE_MATCHING_TAG, array('forum_id_' . $id_forum));
		$this->DB->cleanSqlCache();
		
		if ($this->Log) $this->Log->write('down forum', 'forum id(' . $id_forum . ')');
		if ($res1 && $res2)
			return $this->showInfoMessage(__('Operation is successful'), '/forum/' );
		else
			return $this->showInfoMessage(__('Some error occurred'), '/forum/' );
	}


	
	/**
	* delete forum
	*
	* @id_forum (int)    forum ID
	* @return            info message
	*/
	public function delete_forum($id_forum = null) {
		//check access
		$this->ACL->turn(array('forum', 'delete_forums'));
		$id_forum = (int)$id_forum;
		if (empty($id_forum) || $id_forum < 1) redirect('/forum/');
		
	
		$forum = $this->Model->getById($id_forum);
		if (!$forum) return $this->showInfoMessage(__('Can not find forum'), '/forum/');
		
		
		// Можно удалить только форум, который не содержит тем (в целях безопасности)
		$themeModel = $this->Register['ModManager']->getModelInstance('Themes');
		$themes = $themeModel->getTotal(array('cond' => array('id_forum' => $id_forum)));
		if ($themes > 0) {
			return $this->showInfoMessage(__('Can not delete forum with themes'), '/forum/' );
		} else {
			$forum->delete();
		}
		
		//clean cache
		$this->Cache->clean(CACHE_MATCHING_TAG, array('forum_id_' . $id_forum));
		$this->DB->cleanSqlCache();
		if ($this->Log) $this->Log->write('delete forum', 'forum id(' . $id_forum . ')');
		return $this->showInfoMessage(__('Operation is successful'), '/forum/' );
	}


	
	/**
	* form per add theme into forum
	*
	* @id_forum (int)    forum ID
	* @return            html content
	*/
	public function add_theme_form($id_forum = null) {
		//check access
		$this->ACL->turn(array('forum', 'add_themes'));

		$id_forum = intval($id_forum);
		if (empty($id_forum) || $id_forum < 1) redirect('/forum/');
		$writer_status = (!empty($_SESSION['user']['status'])) ? $_SESSION['user']['status'] : 0;


		$forum = $this->Model->getById($id_forum);
		if (!$forum) redirect('/forum/');


		// Check access to this forum. May be locked by pass or posts count
		$this->__checkForumAccess($forum);
		
		
		$html    = '';
		
		// preview
		if (isset($_SESSION['viewMessage']) and !empty($_SESSION['viewMessage']['message'])) {
			$preview = $this->Parser->getPreview($_SESSION['viewMessage']['message']);

			$theme = h($_SESSION['viewMessage']['theme']);
			$description = h($_SESSION['viewMessage']['description']);
			$message = $_SESSION['viewMessage']['message'];
			$gr_access = $_SESSION['viewMessage']['gr_access'];	
			$first_top = $_SESSION['viewMessage']['first_top'];
			unset( $_SESSION['viewMessage'] );
		}

		// errors
		if (isset($_SESSION['FpsForm'])) {
			$errors = $this->Register['Validate']->getErrors();

			$theme   = h($_SESSION['FpsForm']['theme']);
			$description = h($_SESSION['FpsForm']['description']);
			$message = $_SESSION['FpsForm']['message'];
			$gr_access = $_SESSION['FpsForm']['gr_access'];
			$first_top = $_SESSION['FpsForm']['first_top'];
			unset($_SESSION['FpsForm']);
		}
		
		
		$markers = array(
			'errors' => !empty($errors) ? $errors : '',
			'preview' => !empty($preview) ? $preview : '',
			'action' => get_url('/forum/add_theme/' . $id_forum),
			'theme' => (!empty($theme)) ? $theme : '',
			'description' => (!empty($description)) ? $description : '',
			'main_text' => (!empty($message)) ? $message : '',
			'gr_access' => (!empty($gr_access)) ? $gr_access : array(),
			'first_top' => (!empty($first_top)) ? $first_top : '0',
		);

		$markers['users_groups'] = $this->Register['ACL']->getGroups();
		
		// nav block
		$navi = array();
		$navi['navigation'] = get_link(__('Forums list'), '/forum/') . __('Separator') 
			. get_link(h($forum->getTitle()), '/forum/view_forum/' . $id_forum);
		$this->_globalize($navi);
		
		
		$source = $this->render('addthemeform.html', array(
			'context' => $markers,
		));
		return $this->_view($source);
	}


	
	/**
	* add theme into forum
	*
	* @id_forum (int)    forum ID
	* @return            info message
	*/
	public function add_theme($id_forum = null) 
	{
		//check access
		$this->ACL->turn(array('forum', 'add_themes'));
		if (!isset($id_forum) || !isset($_POST['theme']) || !isset($_POST['main_text']))
            redirect('/forum/');

		$id_forum = intval($id_forum);
		if (empty($id_forum) || $id_forum < 1)
            redirect('/forum/');
		

		$forum = $this->Model->getById($id_forum);
		if (!$forum) redirect('/forum/');

		
		// Check access to this forum. May be locked by pass or posts count
		$this->__checkForumAccess($forum);
		
		$errors = $this->Register['Validate']->check($this->Register['action']);
		
		
		// cut lenght
		$theme   = mb_substr($_POST['theme'], 0, 55);
		$message = $_POST['main_text'];
		$theme   = trim($theme);
		$description = trim(mb_substr($_POST['description'], 0, 128)); 
		$message = trim($message);
		$first_top = isset($_POST['first_top']) ? '1' : '0';
		
		$gr_access = array();
		$groups = $this->Register['ACL']->getGroups();
		foreach ($groups as $grid => $grval) {
			if (isset($_POST['gr_access_' . $grid])) $gr_access[] = $grid;
		}
		
		
		// preview
		if (isset($_POST['viewMessage'])) {
			$_SESSION['viewMessage']['theme']   = $theme;
			$_SESSION['viewMessage']['description'] = $description; 
			$_SESSION['viewMessage']['message'] = $message;
			$_SESSION['viewMessage']['gr_access'] = $gr_access;
			$_SESSION['viewMessage']['first_top'] = $first_top;
			redirect('/forum/add_theme_form/' . $id_forum );
		}

	
		// errors
		if (!empty($errors)) {
			$_SESSION['FpsForm'] = array();
			$_SESSION['FpsForm']['errors'] = $errors;
			$_SESSION['FpsForm']['theme'] = $theme;
			$_SESSION['FpsForm']['description'] = $description;
			$_SESSION['FpsForm']['message'] = $message;
			$_SESSION['FpsForm']['gr_access'] = $gr_access;
			$_SESSION['FpsForm']['first_top'] = $first_top;
			redirect('/forum/add_theme_form/' . $id_forum );
		}
		
		$message = mb_substr($message, 0, Config::read('max_post_lenght', 'forum'));
		
		
		$user_id = (!empty($_SESSION['user'])) ? $_SESSION['user']['id'] : 0;
		
		$data = array(
			'title'          => $theme,
			'description'    => $description,
			'id_author'      => $user_id,
			'time'           => new Expr('NOW()'),
			'id_last_author' => $user_id,
			'last_post'      => new Expr('NOW()'),
			'id_forum'       => $id_forum, 
			'group_access'   => $gr_access, 
			'first_top'      => $first_top,
		);
		
		$theme = new ThemesEntity($data);
		$id_theme = $theme->save();
		$theme->setId($id_theme);

		
		// Check poll
		$this->__savePoll($theme);

		
		// add first post
		$postData = array(
			'message'        => $message,
			'id_author'      => $user_id,
			'time'           => new Expr('NOW()'),
			'edittime'       => new Expr('NOW()'),
			'id_theme'       => $id_theme 
		);
		$post = new PostsEntity($postData);
		$post_id = $post->save();
		
		
		/***** END ATTACH *****/
		$attaches_exists = 0;
		// Массив недопустимых расширений файла вложения
		$extentions = $this->denyExtentions;
		$img_extentions = array('.png','.jpg','.gif','.jpeg', '.PNG','.JPG','.GIF','.JPEG');

		
		for ($i = 1; $i < 6; $i++) {
			$attach_name = 'attach' . $i;
			if (!empty($_FILES[$attach_name]['name'])) {
				// Извлекаем из имени файла расширение
				$ext = strrchr($_FILES[$attach_name]['name'], ".");
                $ext = strtolower($ext);
				// Формируем путь к файлу
				if (in_array(strtolower($ext), $extentions))
					$file = $post_id . '-' . $i . '-' . date("YmdHi") . '.txt';
				else
					$file = $post_id . '-' . $i . '-' . date("YmdHi") . $ext;
				$is_image = (in_array(strtolower($ext), $img_extentions)) ? 1 : 0;
				// Перемещаем файл из временной директории сервера в директорию files
				if (move_uploaded_file($_FILES[$attach_name]['tmp_name'], ROOT . '/sys/files/forum/' . $file)) {
					chmod(ROOT . '/sys/files/forum/' . $file, 0644);
					$attach_file_data = array(
						'post_id'       => $post_id,
						'theme_id'      => $id_theme,
						'user_id'       => $user_id,
						'attach_number' => $i,
						'filename'      => $file,
						'size'          => $_FILES[$attach_name]['size'],
						'date'          => new Expr('NOW()'),
						'is_image'      => $is_image,
					);
					
					$attach = new ForumAttachesEntity($attach_file_data);
					if ($attach->save()) {
						$attaches_exists = 1;
					}
				}
			}
		}
		if ($attaches_exists == 1) {
			$postModel = $this->Register['ModManager']->getModelInstance('Posts');
			$post = $postModel->getById($post_id);
			$post->setAttaches(1);
			$post->save();
		}
		/***** END ATTACH *****/
		
		
		// Обновляем число оставленных сообщений и созданных тем
		if (!empty($_SESSION['user'])) {
			$userModel = $this->Register['ModManager']->getModelInstance('Users');
			$user = $userModel->getById($_SESSION['user']['id']);
			$user->setThemes($user->getThemes() + 1);
			$user->setPosts($user->getPosts() + 1);
			$user->save();
		}
		
		
		$forum = $this->Model->getById($id_forum);
		$forum->setThemes($forum->getThemes() + 1);
		$forum->setPosts($forum->getPosts() + 1);
		$forum->setLast_theme_id($id_theme);
		$forum->save();
		
		
		//clean cache
		$this->Cache->clean(CACHE_MATCHING_ANY_TAG, array(
			'user_id_' . $user_id,
			'forum_id_' . $id_forum,
		));
		
		$this->DB->cleanSqlCache();
		if ($this->Log) $this->Log->write('adding theme', 'theme id(' . $id_theme . '), post id(' . $post_id . ')');
		
		
		return $this->showInfoMessage(__('Operation is successful'), '/forum/view_theme/' . $id_theme );
	}


	
	/**
	* form per edit theme
	*
	* @id_forum (int)    theme ID
	* @return            html content
	*/
	public function edit_theme_form($id_theme = null) 
	{
		$id_theme = (int)$id_theme;
		if (empty($id_theme) || $id_theme < 1) redirect('/forum/');


		// Получаем из БД информацию о редактируемой теме
		$themeModel = $this->Register['ModManager']->getModelInstance('Themes');
		$themeModel->bindModel('author');
		$theme = $themeModel->getById($id_theme);
		if (!$theme) redirect('/forum/');
		
		
		$id_forum = $theme->getId_forum();
		
		
		//check access
		if (!$this->ACL->turn(array('forum', 'edit_themes', $theme->getId_forum()), false) 
		&& (empty($_SESSION['user']['id']) || $theme->getId_author() != $_SESSION['user']['id'] 
		|| !$this->ACL->turn(array('forum', 'edit_mine_themes', $theme->getId_forum()), false))) {
			return $this->showInfoMessage(__('Permission denied'), '/forum/view_forum/' . $id_forum );
		}

		
		// Если при заполнении формы были допущены ошибки
		if (isset($_SESSION['FpsForm'])) {
			$errors = $this->Register['Validate']->getErrors();

			$name = h($_SESSION['FpsForm']['theme']);
			$description = h($_SESSION['FpsForm']['description']);
			$gr_access = $_SESSION['FpsForm']['gr_access'];
			$first_top = $_SESSION['FpsForm']['first_top'];
			unset($_SESSION['FpsForm']);

        } else {

			$name = h($theme->getTitle());
			$description = h($theme->getDescription()); 
			$gr_access = $theme->getGroup_access(); 
			$first_top = $theme->getFirst_top();
		}

		
		// Формируем список форумов, чтобы можно было переместить тему в другой форум
		$forums = $this->Model->getCollection(array(), array('order' => 'pos'));
		if (!$forums) redirect('/forum/');


		$options = '';
		foreach ($forums as $forum) {
			if ($forum->getId() == $theme->getId_forum())
				$options = $options.'<option value="'.$forum->getId().'" selected>'.h($forum->getTitle()).'</option>'."\n";
			else
				$options = $options.'<option value="'.$forum->getId().'">'.h($forum->getTitle()).'</option>'."\n";
		}


		$author_name = ($theme->getId_author()) ? h($theme->getAuthor()->getName()) : __('Guest');
		$data = array(
			'errors' => !empty($errors) ? $errors : '',
			'action' => get_url('/forum/update_theme/' . $id_theme),
			'theme' => $name,
			'description' => $description,
			'author' => $author_name,
			'options' => $options,
			'gr_access' => (!empty($gr_access)) ? $gr_access : array(),
			'first_top' => (!empty($first_top)) ? $first_top : '0',
		);
			
		$data['users_groups'] = $this->ACL->getGroups();
		
		// nav block
		$navi = array();
		$navi['navigation'] = get_link(__('Forums list'), '/forum/')
            . __('Separator') . __('Edit theme');
		$this->_globalize($navi);
		
		
		$source = $this->render('editthemeform.html', array(
			'context' => $data,
		));
		return $this->_view($source);
	}


	
	/**
	* update theme
	*
	* @id_forum (int)    theme ID
	* @return            info message
	*/
	public function update_theme($id_theme = null) {
		
		// Если не переданы данные формы - функция вызвана по ошибке
		if (!isset($id_theme) || !isset($_POST['id_forum']) || !isset($_POST['theme'])) {
			redirect('/forum/');
		}

		$id_theme = (int)$id_theme;
		$id_forum = (int)$_POST['id_forum'];
		if ($id_theme < 1) redirect('/forum/');
		
		
		$themeModel = $this->Register['ModManager']->getModelInstance('Themes');
		$theme = $themeModel->getById($id_theme);
		if (!$theme) return $this->showInfoMessage(__('Theme does not exists'), '/forum/' );


		if (!empty($_POST['unite_theme'])) {
			$this->Register['Validate']->disableFieldCheck(array('theme', 'description', 'first_top'));
		}
		
		$errors = $this->Register['Validate']->check($this->Register['action']);
		
		
		// Обрезаем переменные до длины, указанной в параметре maxlength тега input
		$from_forum = $theme->getId_forum();
		$name = mb_substr($_POST['theme'], 0, 55);
		$name = trim($name);
		$description = trim(mb_substr($_POST['description'], 0, 128));
		$first_top = isset($_POST['first_top']) ? '1' : '0';		
		
		
		$gr_access = array();
		$groups = $this->ACL->getGroups();
		foreach ($groups as $grid => $grval) {
			if (isset($_POST['gr_access_' . $grid])) $gr_access[] = $grid;
		}
		
		
		// errors
		if (!empty($errors)) {
			$_SESSION['FpsForm'] = array();
			$_SESSION['FpsForm']['errors'] = $errors;
			$_SESSION['FpsForm']['theme'] = $name;
			$_SESSION['FpsForm']['description'] = $description;
			$_SESSION['FpsForm']['gr_access'] = $gr_access;
			$_SESSION['FpsForm']['first_top'] = $first_top;
			redirect('/forum/edit_theme_form/' . $id_theme);
		}
		
		
		
		//check access
		if (!$this->ACL->turn(array('forum', 'edit_themes', $theme->getId_forum()), false) 
		&& (empty($_SESSION['user']['id']) || $theme->getId_author() != $_SESSION['user']['id'] 
		|| !$this->ACL->turn(array('forum', 'edit_mine_themes', $theme->getId_forum()), false))) {
			return $this->showInfoMessage(__('Permission denied'), '/forum/view_theme/' . $id_theme );
		}
		
		
		if (!empty($_POST['unite_theme'])) {
			$this->unite_themes($id_theme);
		}
		
		
		// update theme
		$theme->setTitle($name);
		$theme->setDescription($description);
		if (!empty($id_forum)) $theme->setId_forum($id_forum);
		$theme->setGroup_access($gr_access);
		$theme->setFirst_top($first_top);
		$theme->save();
		
		
		//update forums info
		if (!empty($id_forum) && $from_forum != $id_forum) {
			$new_forum = $this->Model->getById($id_forum);
			if (!$new_forum) return $this->showInfoMessage(__('No forum for moving'), '/forum/');
			
			
			$postModel = $this->Register['ModManager']->getModelInstance('posts');
			$posts_cnt = $postModel->getTotal(array('cond' => array('id_theme' => $id_theme)));
			$from_forum_obj = $this->Model->getById($from_forum);
			$from_forum_obj->setPosts($from_forum_obj->getPosts() - $posts_cnt);
			$from_forum_obj->setThemes($from_forum_obj->getThemes() - 1);
			$from_forum_obj->save();
			
			
			$from_forum_obj = $this->Model->getById($id_forum);
			$from_forum_obj->setPosts($from_forum_obj->getPosts() + $posts_cnt);
			$from_forum_obj->setThemes($from_forum_obj->getThemes() + 1);
			$from_forum_obj->save();

			
			$this->Model->upLastPost($from_forum, $id_forum);
		}
		

				
		//clean cahce
		$this->Cache->clean(CACHE_MATCHING_ANY_TAG, array('theme_id_' . $id_theme));
		$this->DB->cleanSqlCache();
		if ($this->Log) $this->Log->write('editing theme', 'theme id(' . $id_theme . ')');
		return $this->showInfoMessage(__('Operation is successful'), '/forum/view_forum/' . $id_forum );
		
	}

	
	
	/**
	 * Deleting theme
	 */
	public function delete_theme($id_theme = null) {
		$id_theme = intval($id_theme);
		if (empty($id_theme) || $id_theme < 1) redirect('/forum/');
		

		
		// if theme moved into another forum 
		$themeModel = $this->Register['ModManager']->getModelInstance('Themes');
		$theme = $themeModel->getById($id_theme);
		if (!$theme) return $this->showInfoMessage(__('Topic not found'), '/forum/');
		
		
		//check access
		if (!$this->ACL->turn(array('forum', 'delete_themes', $theme->getId_forum()), false) 
		|| (!empty($_SESSION['user']['id']) && $theme->getId_author() == $_SESSION['user']['id'] 
		&& !$this->ACL->turn(array('forum', 'delete_mine_themes', $theme->getId_forum()), false))) {
			return $this->showInfoMessage(__('Permission denied'), '/forum/');
		}

		
		// delete colision ( this is paranoia )
		$this->Model->deleteCollisions();

		
		
		// Сперва мы должны удалить все сообщения (посты) темы;
		// начнем с того, что удалим файлы вложений
		$attachModel = $this->Register['ModManager']->getModelInstance('ForumAttaches');
		$postsModel = $this->Register['ModManager']->getModelInstance('Posts');
		$posts = $postsModel->getCollection(array(
			'id_theme' => $id_theme,
		));
		if ($posts) {
			foreach ($posts as $post) {
				// Удаляем файл, если он есть
				$attach_files = $attachModel->getCollection(array('post_id' => $post->getId()));
				if (count($attach_files) && is_array($attach_files)) {
					foreach ($attach_files as $attach_file) {
						if (file_exists(ROOT . '/sys/files/forum/' . $attach_file->getFilename())) {
							if (@unlink(ROOT . '/sys/files/forum/' . $attach_file->getFilename())) {
								$attach_file->delete();
							}
						}
					}
				}
				// заодно обновляем таблицу USERS
				if ($post->getId_author()) {
					$userModel = $this->Register['ModManager']->getModelInstance('Users');
					$user = $userModel->getById($post->getId_author());
					$user->setPosts($user->getPosts() - 1);
					$user->save();
				}
			}
		}
		
		
		$attach_files = $attachModel->getCollection(array('theme_id' => $id_theme));
		if ($attach_files) {
			foreach ($attach_files as $attach_file) {
				if (file_exists(ROOT . '/sys/files/forum/' . $attach_file->getFilename())) {
					if (@unlink(ROOT . '/sys/files/forum/' . $attach_file->getFilename())) {
						$attach_file->delete();
					}
				}
			}
		}
		
		
		
		//we must know id_forum
		$theme = $themeModel->getById($id_theme);
		//delete info
		$theme->delete();
		$postsModel->deleteByTheme($id_theme);
		
		
		// Poll
		$PollModel = $this->Register['ModManager']->getModelInstance('Polls');
		$poll = $PollModel->getCollection(array('theme_id' => $id_theme));
		if (count($poll) && is_array($poll)) {
			foreach ($poll as $p) {
				$p->delete();
			}
		}
		
		
		//update info
		if ($theme) {
			$this->Model->upThemesPostsCounters($theme, false);
		}
		
		//clean cache
		$this->Cache->clean(CACHE_MATCHING_ANY_TAG, array('theme_id_' . $id_theme,));
		$this->Cache->clean(CACHE_MATCHING_TAG, array('module_forum', 'action_index'));
		$this->DB->cleanSqlCache();
		if ($this->Log) $this->Log->write('delete theme', 'theme id(' . $id_theme . ')');
		return $this->showInfoMessage(__('Theme is deleted'), '/forum/view_forum/' . $theme->getId_forum() );
	}


	
	/**
	 * Close Theme
	 */
	public function lock_theme($id_theme = null) {
		$id_theme = (int)$id_theme;
		if ($id_theme < 1) redirect('/forum/');
		
		
		$postsModel = $this->Register['ModManager']->getModelInstance('Posts');
		$themesModel = $this->Register['ModManager']->getModelInstance('Themes');
		$theme = $themesModel->getById($id_theme);
		if (!$theme) return $this->showInfoMessage(__('Topic not found'), '/forum/' );
		
		
		$this->ACL->turn(array('forum', 'close_themes', $theme->getId_forum()));
		
		
		// Сначала заблокируем сообщения (посты) темы
		$posts = $postsModel->getCollection(array('id_theme' => $id_theme));
		if ($posts) {
			foreach ($posts as $post) {
				$post->setLocked('1');
				$post->save();
			}
		}

		// Теперь заблокируем тему
		$theme->setLocked('1');
		$theme->save();

		
		//clean cache
		$this->Cache->clean(CACHE_MATCHING_ANY_TAG, array('theme_id_' . $id_theme));
		$this->DB->cleanSqlCache();
		if ($this->Log) $this->Log->write('lock theme', 'theme id(' . $id_theme . ')');
		return $this->showInfoMessage(__('Theme is locked'), '/forum/view_forum/' . $theme->getId_forum() );
	}


	
	/**
	 * Unlocking Theme
	 */
	public function unlock_theme($id_theme = null) {
		$id_theme = (int)$id_theme;
		if ($id_theme < 1) redirect('/forum/');

		
		$postsModel = $this->Register['ModManager']->getModelInstance('Posts');
		$themesModel = $this->Register['ModManager']->getModelInstance('Themes');
		$theme = $themesModel->getById($id_theme);
		if (!$theme) return $this->showInfoMessage(__('Topic not found'), '/forum/' );
		
		
		$this->ACL->turn(array('forum', 'close_themes', $theme->getId_forum()));
		
		// Сначала заблокируем сообщения (посты) темы
		$posts = $postsModel->getCollection(array('id_theme' => $id_theme));
		if ($posts) {
			foreach ($posts as $post) {
				$post->setLocked('0');
				$post->save();
			}
		}

		// Теперь заблокируем тему
		$theme->setLocked('0');
		$theme->save();

		
		//clean cache
		$this->Cache->clean(CACHE_MATCHING_ANY_TAG, array('theme_id_' . $id_theme));
		$this->DB->cleanSqlCache();
		if ($this->Log) $this->Log->write('unlock theme', 'theme id(' . $id_theme . ')');
		return $this->showInfoMessage(__('Theme is open'), '/forum/view_forum/' . $theme->getId_forum() );
	}


	
	/**
	 * Create reply form
	 *
	 * @param array $theme Theme info
	 * @return string HTML reply form
	 */
    private function add_post_form($theme = null) {
        if (empty($theme)) return null;

        $id_theme = (int)$theme->getId();
        if ($id_theme < 1) return null;


        /** Turn a user access in template such as this function
         * calls as part of another action(For example: view_theme).
         *
         * $this->ACL->turn(array('forum', 'add_posts', $theme->getId_forum()));
         */


        $message = '';

        if (isset($_SESSION['viewMessage']) and !empty($_SESSION['viewMessage'])) {
            $preview = $this->Parser->getPreview($_SESSION['viewMessage']);
            $preview .= "<script>window.location.href=\"#preview\";</script>\n";

            $message = h($_SESSION['viewMessage']);
            unset($_SESSION['viewMessage']);
        }


        // Если при заполнении формы были допущены ошибки
        if (isset($_SESSION['FpsForm'])) {
            $errors = $this->Register['Validate']->getErrors();

            $message = h($_SESSION['FpsForm']['message']);
            unset($_SESSION['FpsForm']);
        }


        $source = $this->render('replyform.html', array(
            'context' => array(
                'action' => get_url('/forum/add_post/' . $id_theme),
                'message' => $message,
                'theme' => $theme,
                'errors' => !empty($errors) ? $errors : '',
                'preview' => !empty($preview) ? $preview : '',
            ),
        ));


        return $source;
    }

	
	
	/**
	 * Adding new record into posts table
	 *
	 * @param int $id_theme
	 */
	public function add_post($id_theme = null) 
	{
		if (empty($id_theme) || !isset($_POST['mainText'])) redirect('/forum/');
		$id_theme = (int)$id_theme;
		if ($id_theme < 1) redirect('/forum/');
		
		
		// Проверяем, не заблокирована ли тема?
		$themesModel = $this->Register['ModManager']->getModelInstance('Themes');
		$theme = $themesModel->getById($id_theme);
		if (!$theme) redirect('/forum/');
		if ($theme->getLocked() == 1)
			return $this->showInfoMessage(__('Can not write in a closed theme'), '/forum/view_theme/' . $id_theme );
			
			
		$this->ACL->turn(array('forum', 'add_posts', $theme->getId_forum()));
			
		
		// Check access to this forum. May be locked by pass or posts count
		$forum = $this->Model->getById($theme->getId_forum());
		if (!$forum)  return $this->showInfoMessage(__('Can not find forum'), '/forum/' );
		$this->__checkForumAccess($forum);
		
		
		$errors = $this->Register['Validate']->check($this->Register['action']);
		

		// Обрезаем сообщение (пост) до длины $set['forum']['max_post_lenght']
		$message = trim($_POST['mainText']);
		// Если пользователь хочет посмотреть на сообщение перед отправкой
		if (isset($_POST['viewMessage'])) {
			$_SESSION['viewMessage'] = $message;
			redirect('/forum/view_theme/' . $id_theme . '#bottom');
		}
		
		
		$gluing = true;
		for ($i = 1; $i < 6; $i++) {
			// if exists attach files we do not gluing posts
			if (!empty($_FILES['attach' . $i]['name'])) $gluing = false;	
		}
		
		
		// errors
		if (!empty($errors)) {
			$_SESSION['FpsForm'] = array();
			$_SESSION['FpsForm']['errors'] = $errors;
			$_SESSION['FpsForm']['message'] = $message;
			redirect('/forum/view_theme/' . $id_theme . '#bottom');
		}		

		
		$message = mb_substr($message, 0, Config::read('max_post_lenght', 'forum'));
		$id_user = (!empty($_SESSION['user'])) ? $_SESSION['user']['id'] : 0;
		// Защита от того, чтобы один пользователь не добавил
		// 100 сообщений за одну минуту
		if (isset($_SESSION['unix_last_post']) && (time() - $_SESSION['unix_last_post'] < 10) ) {
			return $this->showInfoMessage(__('Your message has been added'), '/forum/view_theme/' . $id_theme );
		}		
		
		
		//gluing posts
		if ($gluing === true) {
			$postsModel = $this->Register['ModManager']->getModelInstance('Posts');
			$prev_post = $postsModel->getCollection(array(
				'id_theme' => $id_theme,
			), array(
				'order' => 'time DESC, id DESC',
				'limit' => 1,
			));
			if ($prev_post) {
				$prev_post_author = $prev_post[0]->getId_author();
			}
			
			if (empty($prev_post_author)) $gluing = false;
			if ((mb_strlen($prev_post[0]->getMessage() . $message)) > Config::read('max_post_lenght', 'forum')) $gluing = false;
			if ($prev_post_author != $id_user || empty($id_user)) $gluing = false;
		}		
		
		
		
		if ($gluing === true) {
			$message = $prev_post[0]->getMessage() . "\n\n" . '[color=939494]' 
			. __('Added') . date("Y.m.d  H-i") . "[/color]\n\n" . $message;
			
			$prev_post[0]->setMessage($message);
			$prev_post[0]->setEdittime(new Expr('NOW()'));
			$prev_post[0]->save();
			
			$theme->setLast_post(new Expr('NOW()'));
			$theme->save();

			$forum->setLast_theme_id($id_theme);
			$forum->save();

			
			
		} else {   // NOT GLUING MESSAGE
			// Все поля заполнены правильно - выполняем запрос к БД
			$postsModel = $this->Register['ModManager']->getModelInstance('Posts');
			$post_data = array(
				'message'   => $message,
				'id_author' => $id_user,
				'time'      => new Expr('NOW()'),
				'id_theme'  => $id_theme
			);

			$post = new PostsEntity($post_data);
			$post_id = $post->save();

			if (empty($post_id)) return $this->showInfoMessage(__('Some error occurred'), '/forum/');
			$post->setId($post_id);
			
			
			$attaches_exists = 0;
			// Массив недопустимых расширений файла вложения
			$extentions = $this->denyExtentions;
			$img_extentions = array('.png','.jpg','.gif','.jpeg', '.PNG','.JPG','.GIF','.JPEG');
			$file_types = array('image/jpeg','image/jpg','image/gif','image/png');
			

			for ($i = 1; $i < 6; $i++) {
			
				$attach_name = 'attach' . $i;
				if (!empty($_FILES[$attach_name]['name'])) {
				
					// Извлекаем из имени файла расширение
					$ext = strrchr($_FILES[$attach_name]['name'], ".");
                    $ext = strtolower($ext);
					
					// Формируем путь к файлу
					if (in_array(strtolower($ext), $extentions) || empty($ext)) {
						$file = $post_id . '-' . $i . '-' . date("YmdHi") . '.txt';
					} else {
						$file = $post_id . '-' . $i . '-' . date("YmdHi") . $ext;
					}

					
					$is_image = '0';
					if (in_array($_FILES[$attach_name]['type'], $file_types)) {
						$is_image = '1';
					}

					// Перемещаем файл из временной директории сервера в директорию files
					if (move_uploaded_file($_FILES[$attach_name]['tmp_name'], ROOT . '/sys/files/forum/' . $file)) {
						@chmod(ROOT . '/sys/files/forum/' . $file, 0644);
						$attach_file_data = array(
							'post_id'       => $post_id,
							'theme_id'      => $id_theme,
							'user_id'       => $id_user,
							'attach_number' => $i,
							'filename'      => $file,
							'size'          => $_FILES[$attach_name]['size'],
							'date'          => new Expr('NOW()'),
						);
                        if($is_image) $attach_file_data['is_image'] = $is_image;
						
						$attach = new ForumAttachesEntity($attach_file_data);
						if ($attach->save()) {
							$attaches_exists = 1;
						}
					}
				}
			}
			
			
			if ($attaches_exists == 1) {
				$post->setAttaches(1);
				$post->save();
			}
			
			
			$cnt_posts_from_theme = $postsModel->getTotal(array('cond' => array('id_theme' => $id_theme)));
			$theme->setPosts(($cnt_posts_from_theme - 1));
			$theme->setLast_post(new Expr('NOW()'));
			$theme->setId_last_author($id_user);
			$theme->save();


			// speed spam protected
			$_SESSION['unix_last_post'] = time();

			
			// Обновляем количество сообщений для зарегистрированного пользователя
			if (isset($_SESSION['user'])) {
				$usersModel = $this->Register['ModManager']->getModelInstance('Users');
				$user = $usersModel->getById($id_user);
				$user->setPosts($user->getPosts() + 1);
				$user->save();
			}
			
			
			//update forum info
			$forum->setPosts($forum->getPosts() + 1);
			$forum->setLast_theme_id($id_theme);
			$forum->save();
		}		
		
		

		//clean cache
		$this->Cache->clean(CACHE_MATCHING_ANY_TAG, array('theme_id_' . $id_theme, 'user_id_' . $id_user));
		$this->DB->cleanSqlCache();
		
		
		if ($gluing === false) {
			if ($this->Log) $this->Log->write('adding post', 'post id(' . $post_id . '), theme id(' . $id_theme . ')');
			return $this->showInfoMessage(__('Your message has been added'), '/forum/view_theme/'
			. $id_theme . '?page=999#post' . $cnt_posts_from_theme );
		} else {
			if ($this->Log) $this->Log->write('adding post', 'post id(*gluing), theme id(' . $id_theme . ')');
			return $this->showInfoMessage(__('Your message has been added'), '/forum/view_theme/' 
			. $id_theme . '?page=999#post' . $prev_post[0]->getPosts());
		}
	}

		

	/**
	 * Create Edit post form
	 *
	 * @param int $id Post ID
	 */
	public function edit_post_form($id = null) {
		$id = (int)$id;
		if ($id < 1) redirect('/forum/');
		$writer_status = (!empty($_SESSION['user']['status'])) ? $_SESSION['user']['status'] : 0;


		// Получаем из БД сообщение
		$postModel = $this->Register['ModManager']->getModelInstance('Posts');
		$postModel->bindModel('attacheslist');
		$post = $postModel->getById($id);
		if (!$post)
			return $this->showInfoMessage(__('Some error occurred'),  '/forum/' );
		
		$id_theme = $post->getId_theme();


		// get theme for check access (by theme.id_forum)
		$themesModel = $this->Register['ModManager']->getModelInstance('Themes');
		$theme = $themesModel->getById($id_theme);
		if (!$theme)
			return $this->showInfoMessage(__('Topic not found'),  '/forum/' );
		
		
		//check access
		if (!$this->ACL->turn(array('forum', 'edit_posts', $theme->getId_forum()), false) 
		&& (!empty($_SESSION['user']['id']) && $post->getId_author() == $_SESSION['user']['id'] 
		&& $this->ACL->turn(array('forum', 'edit_mine_posts', $theme->getId_forum()), false)) === false) {
			return $this->showInfoMessage(__('Permission denied'), '/forum/');
		}

		
		$message = $post->getMessage();
		
		//if user vant preview message
		if (isset($_SESSION['viewMessage']) and !empty($_SESSION['viewMessage'])) {
			$preview = $this->Parser->getPreview($_SESSION['viewMessage']);
			$message = $_SESSION['viewMessage'];
			unset($_SESSION['viewMessage']);
		}


		// errors
		if (isset($_SESSION['FpsForm'])) {
			$errors = $this->Register['Validate']->getErrors();
			$message = $_SESSION['FpsForm']['message'];
			unset($_SESSION['FpsForm']);
		}

		
		$markers = array(
			'errors' => !empty($errors) ? $errors : '',
			'preview' => !empty($preview) ? $preview : '',
			'action' => get_url('/forum/update_post/' . $id),
			'message' => h($message),
		);
		
		
		/****  ATTACH  ****/
		$unlinkfiles = array('att1' => '', 'att2' => '', 'att3' => '', 'att4' => '', 'att5' => '',);
		if ($post->getAttaches()) {
			$attachModel = $this->Register['ModManager']->getModelInstance('ForumAttaches');
			$attach_files = $attachModel->getCollection(array('post_id' => $post->getId()));
			if ($attach_files) {
				foreach ($attach_files as $attach_file) {
					if (file_exists(ROOT . '/sys/files/forum/' . $attach_file->getFilename())) {
						$unlinkfiles['att'.$attach_file->getAttach_number()] = '<input type="checkbox" name="unlink' . $attach_file->getAttach_number() 
						. '" value="1" />&nbsp;' . __('Delete') ."\n";
					}
				}
			}
		}
		$markers['unlinkfiles'] = $unlinkfiles;
		/****  END  ATTACH  ****/

		// nav block
		$navi = array();
		$navi['navigation'] = get_link(__('Forums list'), '/forum/') . __('Separator')
		. get_link('Просмотр темы', '/forum/view_theme/' . $id_theme) . __('Separator') . __('Edit message');
		$this->_globalize($navi);
		
		
		setReferer();
		$source = $this->render('editpostform.html', array('context' => $markers));
		
		return $this->_view($source);
	}
	
	

	/**
	 * Update Post record
	 *
	 * @param int $id Post ID
	 */
	public function update_post($id = null) {
		// Если не переданы данные формы - значит функция была вызвана по ошибке
		if (empty($id) || !isset($_POST['mainText'])) redirect('/forum/');

		$id = (int)$id;
		if ($id < 1) redirect('/forum/');


		// Проверяем, имеет ли пользователь право редактировать это сообщение (пост)
		$postsModel = $this->Register['ModManager']->getModelInstance('Posts');
		$post = $postsModel->getById($id);
		if (!$post) return $this->showInfoMessage(__('Some error occurred'), '/forum/' );
		$id_theme = $post->getId_theme();
		
		
		// get theme for check access (by theme.id_forum)
		$themesModel = $this->Register['ModManager']->getModelInstance('Themes');
		$theme = $themesModel->getById($id_theme);
		if (!$theme)
			return $this->showInfoMessage(__('Topic not found'),  '/forum/' );
		
		
		//check access
		if (!$this->ACL->turn(array('forum', 'edit_posts', $theme->getId_forum()), false) 
		&& (!empty($_SESSION['user']['id']) && $post->getId_author() == $_SESSION['user']['id'] 
		&& $this->ACL->turn(array('forum', 'edit_mine_posts', $theme->getId_forum()), false)) === false) {
			return $this->showInfoMessage(__('Permission denied'), '/forum/');
		}
		
		
		$errors = $this->Register['Validate']->check($this->Register['action']);


		// Обрезаем сообщение до длины $set['forum']['max_post_lenght']
		$message = trim($_POST['main_text']);
        $user_id = (!empty($_SESSION['user'])) ? $_SESSION['user']['id'] : 0;

		// Preview
		if (isset($_POST['viewMessage'])) {
			$_SESSION['viewMessage'] = $message;
			redirect('/forum/edit_post_form/' . $id);
		}
		
		
		/* if an error */
		if (!empty($errors)) {
			$_SESSION['FpsForm'] = array();
			$_SESSION['FpsForm']['errors'] = $errors;
			$_SESSION['FpsForm']['message'] = $message;
			redirect('/forum/edit_post_form/' . $id);
		}


		
		/*****   ATTACH   *****/
		// Массив недопустимых расширений файла вложения
		$extentions = array('.php', '.phtml', '.php3', '.html', '.htm', '.pl');
		$allowed_types = array('image/jpeg','image/jpg','image/gif','image/png');
		$attachModel = $this->Register['ModManager']->getModelInstance('ForumAttaches');
		
		
		for ($i = 1; $i <= 5; $i++) {
			if (!empty($_POST['unlink' . $i]) || !empty($_FILES['attach' . $i]['name'])) {
				$unlink_file = $attachModel->getCollection(array(
					'post_id' => $id,
					'attach_number' => $i,
				), array('limit' => 1));

				
				if (!empty($unlink_file) && file_exists(ROOT . '/sys/files/forum/' . $unlink_file[0]->getFilename())) {
					if(unlink(ROOT . '/sys/files/forum/' . $unlink_file[0]->getFilename())) {	
						$unlink_file->delete(); 
					}
				}
			}
			
			$attach_name = 'attach' . $i;
			if (!empty($_FILES[$attach_name]['name'])) {
			
				// Извлекаем из имени файла расширение
				$ext = strrchr($_FILES[$attach_name]['name'], ".");
                $ext = strtolower($ext);
				// Формируем путь к файлу
				if (in_array( strtolower($ext), $extentions)) {
					$file = $id . '-' . $i . '-' . date("YmdHi") . '.txt';
				} else {
					$file = $id . '-' . $i . '-' . date("YmdHi") . $ext;
				}
				
				$is_image = 0;
				if (in_array($_FILES[$attach_name]['type'], $allowed_types)) {
					$is_image = 1;
				}
				
				
				// Перемещаем файл из временной директории сервера в директорию files
				if (move_uploaded_file($_FILES[$attach_name]['tmp_name'], ROOT . '/sys/files/forum/' . $file)) {
					chmod(ROOT . '/sys/files/forum/' . $file, 0644);
					$attach_file_data = array(
						'post_id'       => $id,
						'theme_id'      => $id_theme,
						'user_id'       => $user_id,
						'attach_number' => $i,
						'filename'      => $file,
						'size'          => $_FILES[$attach_name]['size'],
						'date'          => new Expr('NOW()'),
						'is_image'      => $is_image,
					);
					$newattach = new ForumAttachesEntity($attach_file_data);
					$newattach->save();
				}
				
			}
		}
		$attach_exists = $attachModel->getCollection(array('post_id' => $id));
		$attach_exists = ($attach_exists > 0) ? 1 : 0;
		/*****  END ATTACH   *****/

		
		
		// Все поля заполнены правильно - выполняем запрос к БД
		$message = mb_substr($message, 0, $this->Register['Config']->read('max_post_lenght', 'forum'));
		$post->setMessage($message);
		$post->setAttaches($attach_exists);
		$post->setId_editor($user_id);
		$post->setEdittime(new Expr('NOW()'));
		$post->save();
		

		//clean cache
		$this->Cache->clean(CACHE_MATCHING_ANY_TAG, array('post_id_' . $id));
		$this->DB->cleanSqlCache();
		if ($this->Log) $this->Log->write('editing post', 'post id(' . $id . '), theme id(' . $id_theme . ')');
		
		
		return $this->showInfoMessage(__('Operation is successful'), getReferer());
	}

	
	
	public function delete_posts_pack() {
		if (empty($_POST['ids']) || !is_array($_POST['ids'])) 
			return $this->showInfoMessage(__('Empty parameters'), '/' . $this->module . '/');
			
		foreach ($_POST['ids'] as $k => $id) {
			$id = intval($id);
			if ($id < 1) unset($_POST['ids'][$k]);
			$_POST['ids'][$k] = $id;
		}
		
		$this->delete_post($_POST['ids']);
	}
	

	
	/**
	* deleting post from forum
	* @id     post ID
	* @return none
	*/
	public function delete_post($id = null) {
		if (empty($id) || $id < 1) $this->showInfoMessage(__('Empty ID'), '/' . $this->module . '/');
		$ids = (is_array($id) && count($id)) ? $id : array($id);
		$ids = array_filter($ids, function(&$r){
			return ($r = intval($r)) > 0;
		});
		
		
		foreach ($ids as $id) {
			// Получаем из БД информацию об удаляемом сообщении - это нужно,
			// чтобы узнать, имеет ли право пользователь удалить это сообщение
			$postsModel = $this->Register['ModManager']->getModelInstance('Posts');
			$post = $postsModel->getById($id);
			if (!$post) $this->showInfoMessage(__('Post not found'), '/' . $this->module . '/');

					
			// get theme for check access (by theme.id_forum)
			$themesModel = $this->Register['ModManager']->getModelInstance('Themes');
			$theme = $themesModel->getById($post->getId_theme());
			if (!$theme)
				return $this->showInfoMessage(__('Topic not found'),  '/' . $this->module . '/');
			
			
			//check access
			if (!$this->ACL->turn(array('forum', 'delete_posts', $theme->getId_forum()), false) 
			&& (!empty($_SESSION['user']['id']) && $post->getId_author() == $_SESSION['user']['id'] 
			&& $this->ACL->turn(array('forum', 'delete_mine_posts', $theme->getId_forum()), false)) === false) {
				$this->showInfoMessage(__('Permission denied'), '/' . $this->module . '/');
			}


			// Удаляем файл, если он есть
			$attachModel = $this->Register['ModManager']->getModelInstance('ForumAttaches');
			if ($post->getAttaches()) {
				$attach_files = $attachModel->getCollection(array('post_id' => $id));
				if (count($attach_files) > 0) {
					foreach ($attach_files as $attach_file) {
						if (file_exists(ROOT . '/sys/files/forum/' . $attach_file->getFilename())) {
							if (@unlink(ROOT . '/sys/files/forum/' . $attach_file->getFilename())) {
								$attach_file->delete();
							}
						}
					}
				}
			}
			$post->delete();
			
			// Если это - единственное сообщение темы, то надо удалить и тему
			$postscnt = $postsModel->getTotal(array('cond' => array('id_theme' => $post->getId_theme())));
			
			
			if ($theme->getId_author()) {
				$userModel = $this->Register['ModManager']->getModelInstance('Users');
				$user = $userModel->getById($theme->getId_author());
			}
			
			
			if ($postscnt == 0) {
				if ($user) {
					// Прежде чем удалять тему, надо обновить таблицу TABLE_USERS
					$user->setThemes($user->getThemes() - 1); 
					$user->save();
				}
				
				$theme->delete();
				// Если мы удалили тему, то мы не можем в нее вернуться;
				// поэтому редирект будет на страницу форума, а не страницу темы
				$deleteTheme = true;
			}
			
			
			if ($user) {
				// Обновляем количество сообщений, оставленных автором сообщения ...
				$user->setPosts($user->getPosts() - 1);
				$user->save();
			}
			
			
			// ... и таблицу .themes
			if (!isset($deleteTheme)) {
				$lastPost = $postsModel->getCollection(array(
					'id_theme' => $post->getId_theme(),
				), array(
					'limit' => 1, 
					'order' => 'id DESC'
				));

				//$posts_cnt = $this->DB->select('posts', DB_COUNT, array('cond' => array('id_theme' => $post['id_theme'])));
				$posts_cnt = $postsModel->getTotal(array('cond' => array('id_theme' => $post->getId_theme())));
			
			
				$id_last_author = $lastPost[0]->getId_author();
				$last_post = $lastPost[0]->getTime();
				$theme->setId_last_author($id_last_author);
				$theme->setLast_post($last_post);
				$theme->setPosts($postscnt - 1);
				$theme->save();

			}

			
			//clean cache
			$cahceKey = array('post_id_' . $id);
			if (isset($deleteTheme)) $cahceKey[] = 'theme_id_' . $post->getId_theme();
			$this->Cache->clean(CACHE_MATCHING_ANY_TAG, $cahceKey);
			$this->DB->cleanSqlCache();
			if ($this->Log) $this->Log->write('delete post', 'post id(' . $id . '), theme id(' . $post->getId_theme() . ')');
			
			
			//update forum info
			$lastTheme = $themesModel->getCollection(array(
				'id_forum' => $theme->getId_forum(),
			), array(
				'limit' => 1,
				'order' => '`last_post` DESC',
			));
			if (!empty($lastTheme[0])) $lastTheme = $lastTheme[0];
			
			$forum = $this->Model->getById($theme->getId_forum());
		}
		
		
		
		if (isset($deleteTheme)) {
			$forum->setThemes($forum->getThemes() - 1);
			$forum->setPosts($forum->getPosts() - 1);
			
			if ($lastTheme) $forum->setLast_theme_id($lastTheme->getId());
			else $forum->setLast_theme_id(0);
			
			$forum->save();
			
			$this->showInfoMessage(
				__('Operation is successful'), 
				'/' . $this->module . '/view_forum/' . $theme->getId_forum(), 
				true);
			
		} else {
			$forum->setPosts($forum->getPosts() - 1);
			
			if ($lastTheme) $forum->setLast_theme_id($lastTheme->getId());
			else $forum->setLast_theme_id(0);
			
			$forum->save();
			$this->showInfoMessage(
				__('Operation is successful'),
				'/' . $this->module . '/view_theme/' . $theme->getId(),
				true);
		}
	}

		

	/**
	 * View post for users
	 *
	 * @param ind $user_id
	 * @return none
	 */
	public function user_posts($user_id) 
	{
        $this->addToPageMetaContext('entity_title', __('User messages'));
		$html = '';
    
		if ($this->cached && $this->Cache->check($this->cacheKey)) {
			$html = $this->Cache->read($this->cacheKey);
			return $this->_view($html);
        }
		
		
		
		$themesModel = $this->Register['ModManager']->getModelInstance('Themes');
		$total = $themesModel->getTotal(array('cond' => array('id_author' => $user_id)));
		$perPage = $this->Register['Config']->read('themes_per_page', 'forum');
        list($pages, $page) = pagination($total, $perPage, '/forum/user_posts/' . $user_id);
		
		
		// Page nav
		$nav = array();
        $nav['pagination'] = $pages;
        $this->addToPageMetaContext('page', $page);

		
		$recOnPage = ($page == $this->Register['pagecnt']) ? ($total % $perPage) : $perPage;
        if ($recOnPage > $total) $recOnPage = $total;
		$nav['navigation'] = get_link(__('Forums list'), '/forum/') . __('Separator') . __('User messages');
		$nav['meta'] = __('Count all topics') . $total . '. ' . __('Count visible') . $recOnPage;
		$this->_globalize($nav);
		
		if ($total < 1) return $this->_view(__('No topics'));
		
		
		
		//get records
		$themesModel->bindModel('author');
		$themesModel->bindModel('last_author');
		$themesModel->bindModel('postslist');
		$themesModel->bindModel('forum');
		$themes = $themesModel->getCollection(array(
			'id_author' => $user_id,
		), array(
			'order' => 'time DESC',
			'group' => 'id',
			'page' => $page,
			'limit' => $this->Register['Config']->read('themes_per_page', 'forum'),
		));
		

		foreach ($themes as $theme) {
			$parent_forum = get_link($theme->getForum()->getTitle()
				, '/forum/view_forum/' . $theme->getId_forum());
			$theme->setParent_forum($parent_forum);
			$theme = $this->__parseThemeTable($theme);

			
			//set cache tags
			$this->setCacheTag(array(
				'theme_id_' . $theme->getId(),
			));
		}			
		
		
		// write into cache
		if ($this->cached) {
			$this->Cache->write($html, $this->cacheKey, $this->cacheTags);
		}
		
		//pr($themes); die();
		$source = $this->render('lastposts_list.html', array(
			'context' => array(
				'forum_name' => __('User messages'),
			),
			'themes' => $themes
		));
		$this->_view($source);
	}
	

	
	/**
	* @return forum statistic block
	*/
	protected function _get_stat() {
		$markers = array();
		$result = $this->Model->getStats();
		
		
		if (!empty($result[0]['last_user_id']) && !empty($result[0]['last_user_name'])) {
			$markers['new_user'] = get_link(h($result[0]['last_user_name']), 
			getProfileUrl($result[0]['last_user_id']));
		}
		$markers['count_users'] = getAllUsersCount();
		$markers['count_posts'] = (!empty($result[0]['posts_cnt'])) ? $result[0]['posts_cnt'] : 0;
		$markers['count_themes'] = (!empty($result[0]['themes_cnt'])) ? $result[0]['themes_cnt'] : 0;

		
		$html = $this->render('get_stat.html', $markers);
		return $html;
	}


	
	public function download_file($file = null, $mimetype = 'application/octet-stream') {
		if (empty($file)) redirect('/');
		
		$path = ROOT . '/sys/files/forum/' . $file;
		if (!file_exists($path)) die(__('File not found'));
		$from = 0;
		$size = filesize($path);
		$to = $size;
		if (isset($_SERVER['HTTP_RANGE'])) {
			if (preg_match ('#bytes=-([0-9]*)#',$_SERVER['HTTP_RANGE'],$range)) {// если указан отрезок от конца файла
				$from = $size-$range[1];
				$to = $size;
			} elseif(preg_match('#bytes=([0-9]*)-#',$_SERVER['HTTP_RANGE'],$range)) {// если указана только начальная метка

				$from = $range[1];
				$to = $size;
			} elseif(preg_match('#bytes=([0-9]*)-([0-9]*)#',$_SERVER['HTTP_RANGE'],$range)) {// если указан отрезок файла

				$from = $range[1];
				$to = $range[2];
			}
			header('HTTP/1.1 206 Partial Content');

			$cr='Content-Range: bytes '.$from .'-'.$to.'/'.$size;
		} else
			header('HTTP/1.1 200 Ok');
		
		$etag = md5($path);
		$etag = substr($etag, 0, 8) . '-' . substr($etag, 8, 7) . '-' . substr($etag, 15, 8);
		header('ETag: "'.$etag.'"');
		header('Accept-Ranges: bytes');
		header('Content-Length: ' .($to-$from));
		if (isset($cr)) header($cr);
		header('Connection: close');	
		header('Content-Type: ' . $mimetype);
		header('Last-Modified: ' . gmdate('r', filemtime($path)));
		header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime($path))." GMT");
		header("Expires: ".gmdate("D, d M Y H:i:s", time() + 3600)." GMT");
		$f=fopen($path, 'rb');


		if (preg_match('#^image/#',$mimetype))
			header('Content-Disposition: filename="'.$file.'";');
		else
			header('Content-Disposition: attachment; filename="'.$file.'";');

		fseek($f, $from, SEEK_SET);
		$size = $to;
		$downloaded=0;
		while(!feof($f) and ($downloaded<$size)) {
			$block = min(1024*8, $size - $downloaded);
			echo fread($f, $block);
			$downloaded += $block;
			flush();
		}
		fclose($f);
	}

	
	
	public function important($id = null) {
		//turn access
		$this->ACL->turn(array('forum', 'important_themes'));
		$id = (int)$id;
		if ($id < 1) redirect('/forum/');
		
		
		$themesModel = $this->Register['ModManager']->getModelInstance('Themes');
		$theme = $themesModel->getById($id);
		if (!$theme) return $this->showInfoMessage(__('Some error occurred'), '/forum/');
		
		$theme->setImportant('1');
		$theme->save();

		/* clean cache DB*/
		$this->DB->cleanSqlCache();
		if ($this->Log) $this->Log->write('important post', 'post id(' . $id . '), theme id(' . $theme->getId() . ')');
		return $this->showInfoMessage(__('Operation is successful'), '/forum/view_forum/' . $theme->getId_forum());
	}
	
	
	
	public function unimportant($id = null) {
		//turn access
		$this->ACL->turn(array('forum', 'important_themes'));
		$id = (int)$id;
		if ($id < 1) redirect('/forum/');
		
		
		$themesModel = $this->Register['ModManager']->getModelInstance('Themes');
		$theme = $themesModel->getById($id);
		if (!$theme) return $this->showInfoMessage(__('Some error occurred'), '/forum/');
		
		$theme->setImportant('0');
		$theme->save();

		/* clean cache DB */
		$this->DB->cleanSqlCache();
		if ($this->Log) $this->Log->write('unimportant post', 'post id(' . $id . '), theme id(' . $theme->getId_forum() . ')');
		return $this->showInfoMessage(__('Operation is successful'), '/forum/view_forum/' . $theme->getId_forum());
	}
	

	
	//delete theme
	private function __delete_theme($id_theme) {
		$usersModel = $this->Register['ModManager']->getModelInstance('Users');
		$themesModel = $this->Register['ModManager']->getModelInstance('Themes');
		$postsModel = $this->Register['ModManager']->getModelInstance('Posts');
		$attachesModel = $this->Register['ModManager']->getModelInstance('ForumAttaches');
		
		
		//we must know id_forum
		$theme = $themesModel->getById($id_theme);
	
		// delete colision ( this is paranoia )
		$themes = $this->Model->deleteThemesPostsCollisions();


		// Сперва мы должны удалить все сообщения (посты) темы;
		// начнем с того, что удалим файлы вложений
		$posts = $postsModel->getCollection(array('id_theme' => $id_theme));
		if (count($posts) && is_array(posts)) {
			foreach ($posts as $file) {
				// Удаляем файл, если он есть
				$attach_files = $attachesModel->getCollection(array('post_id' => $file->getId()));
				if (count($attach_files) && is_array($attach_files)) {
					foreach ($attach_files as $attach_file) {
						if (file_exists(ROOT . '/sys/files/forum/' . $attach_file->getFilename())) {
							if (@unlink(ROOT . '/sys/files/forum/' . $attach_file->getFilename())) {
								$attach_file->delete();
							}
						}
					}
				}
				// заодно обновляем таблицу USERS
				if ($file->getId_author()) {
					$user = $usersModel->getById($file->getId_author());
					if ($user) {
						$user->setPosts($user->getPosts() - 1);
						$user->save();
					}
				}
			}
		}
		
		
		$attach_files = $attachesModel->getCollection(array('theme_id' => $id_theme));
		if (count($attach_files) && is_array($attach_files)) {
			foreach ($attach_files as $attach_file) {
				if (file_exists(ROOT . '/sys/files/forum/' . $attach_file->getFilename())) {
					if (@unlink(ROOT . '/sys/files/forum/' . $attach_file->getFilename())) {
						$attach_file->delete();
					}
				}
			}
		}
		
		
		if (count($theme) && is_array($theme)) {
			// Обновляем таблицу TABLE_USERS - надо обновить поле themes
			$themes_cnt = $themesModel->getTotal(array('cond' => array('id_author' => $theme->getId_author())));
			$posts_cnt = $postsModel->getTotal(array('cond' => array('id_author' => $theme->getId_author())));
			$user = $usersModel->getById($theme->getId_author());
			if ($user) {
				$user->setThemes($themes_cnt);
				$user->setPosts($posts_cnt);
				$user->save();
			}

			
			//update forum info
			$this->Model->updateForumCounters($theme->getId_forum());
		}
		//clean cache
		$this->Cache->clean(CACHE_MATCHING_ANY_TAG, array('theme_id_' . $id_theme,));
		$this->Cache->clean(CACHE_MATCHING_TAG, array('module_forum', 'action_index'));
		$this->DB->cleanSqlCache();
		if ($this->Log) $this->Log->write('delete theme(because error uccured)', 'theme id(' . $id_theme . ')');
	}	

	

	public function view_post($id_post) {
		$id_post = intval($id_post);
		if (empty($id_post) || $id_post < 1) redirect('/' . $this->module);

		$postsModel = $this->Register['ModManager']->getModelInstance('Posts');
		$post = $postsModel->getById($id_post);
		if (!$post) return $this->showInfoMessage(__('Some error occurred'), get_url('/' . $this->module));

		
		$id_theme = $post->getId_theme();
		$post_num = $postsModel->getTotal(
			array(
				'order' => 'id ASC',
				'cond' => array(
					'id_theme' => $id_theme,
					'id < ' . $id_post,
				),
			)
		);

		$page = floor($post_num / $this->Register['Config']->read('posts_per_page', $this->module)) + 1;
		$post_num++;

		redirect('/' . $this->module . '/view_theme/' . $id_theme . '?page=' . $page . '#post' . $post_num, 302);
	}


	
    public function move_posts($id_theme = null) {
        $id_theme = intval($id_theme);
        if ($id_theme < 1) redirect('/' . $this->module . '/');

        $themeModel = $this->Register['ModManager']->getModelInstance('Themes');
        $themeModel->bindModel('forum');
        $theme = $themeModel->getById($id_theme);
        if (empty($theme) || !$theme->getForum())
            return $this->showInfoMessage(__('Topic not found'), '/' . $this->module . '/');


        // turn access (send forum ID for check special rules)
        $this->ACL->turn(array($this->module, 'add_themes', $theme->getId_forum()));
        $this->ACL->turn(array($this->module, 'edit_themes', $theme->getId_forum()));


        if (empty($_POST['new_theme'])) {
            $this->Register['Validate']->disableFieldCheck(array('title', 'description', 'forum_id'));
        } else {
            $this->Register['Validate']->disableFieldCheck('theme');
        }
        $errors = $this->Register['Validate']->check($this->Register['action']);
		
        // Errors
        if (!empty($errors)) {
            $_SESSION['FpsForm'] = array_merge(array('title' => null, 'description' => null, 'forum_id' => null,
                'theme' => null, 'locked' => null, 'first_top' => null), $_POST);
            $_SESSION['FpsForm']['errors'] = $errors;
            redirect('/' . $this->module . '/move_posts_form/' . $id_theme);
        }


        $selected_posts = (!empty($_POST['posts_ids']) && is_array($_POST['posts_ids'])) ? $_POST['posts_ids'] : array();
        $selected_posts = array_filter($selected_posts, function(&$r){
            return intval($r) > 0;
        });
        if (!is_array($selected_posts) || !count($selected_posts))
            return $this->showInfoMessage(__('You must select posts'), '/' . $this->module . '/');

        $gr_access = array();
        $groups = $this->ACL->getGroups();
        foreach ($groups as $grid => $grval) {
            if (isset($_POST['gr_access_' . $grid]))
                $gr_access[] = $grid;
        }


        // If we need create a new theme
        if (!empty($_POST['new_theme'])) {
            $locked = (!empty($_POST['locked'])) ? 1 : 0;
            $first_top = (!empty($_POST['first_top'])) ? 1 : 0;
            $forum_id = intval($_POST['forum_id']);
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);

            if ($forum_id != $theme->getId_forum()) {
                // turn access (send forum ID for check special rules)
                $this->ACL->turn(array($this->module, 'add_themes', $forum_id));
                $this->ACL->turn(array($this->module, 'edit_themes', $forum_id));
            }


            $postsModel = $this->Register['ModManager']->getModelInstance('Posts');
            $first_post = $postsModel->getById(min($selected_posts));
            if (!$first_post)
                return $this->showInfoMessage(__('Some error occurred'), '/' . $this->module . '/view_theme/' . $id_theme);
            $last_post = $postsModel->getById(max($selected_posts));
            if (!$last_post)
                return $this->showInfoMessage(__('Some error occurred'), '/' . $this->module . '/view_theme/' . $id_theme);


            $data = array(
                'title' => $title,
                'description' => $description,
                'id_author' => $first_post->getId_author(),
                'time' => $first_post->getTime(),
                'id_last_author' => $last_post->getId_author(),
                'last_post' => $last_post->getTime(),
                'id_forum' => $forum_id,
                'posts' => (count($selected_posts) > 0) ? count($selected_posts) - 1 : 0,
                'group_access' => $gr_access,
                'first_top' => $first_top,
            );
            if ($this->ACL->turn(array($this->module, 'close_themes', $forum_id), false)) {
                $data['locked'] = $locked;
            }
			
            $themesEntity = $this->Register['ModManager']->getEntityName('Themes');
            $new_theme = new $themesEntity($data);
            $new_theme_id = $new_theme->save();
            if (!$new_theme_id)
                $this->showInfoMessage(__('Some error occurred'), '/' . $this->module . '/view_theme/' . $id_theme);
				
			$new_theme->setId($new_theme_id);

        } else {
			$new_theme_title = preg_replace('#\(\d+\)$#u', '', trim($_POST['theme']));
			$new_theme_title = $this->Register['DB']->escape($new_theme_title);
			$new_theme = $themeModel->getCollection(array("`title` LIKE '$new_theme_title'"));
			if (!empty($new_theme[0])) {
				$new_theme = $new_theme[0];
				$new_theme_id = $new_theme->getId();
			}
			
            if (empty($new_theme) || !$new_theme->getForum())
                return $this->showInfoMessage(__('Topic not found'), '/' . $this->module . '/');
        }
		
        $this->Model->movePostsToTheme($theme, $new_theme, $selected_posts);


        //clean cahce
        $tags = array_map(function($r){return 'post_id_' . $r;}, $selected_posts);
        $tags = array_merge(array('theme_id_' . $id_theme, 'theme_id_' . $new_theme_id,), $tags);
        $this->Cache->clean(CACHE_MATCHING_ANY_TAG, $tags);
        $this->DB->cleanSqlCache();

        if ($this->Log)
            $this->Log->write('split theme', 'theme id(' . $id_theme . ')');
        return $this->showInfoMessage(__('Operation is successful'), '/' . $this->module . '/view_theme/' . $new_theme_id);
    }


	
    public function move_posts_form($id_theme = null) {

        $id_theme = intval($id_theme);
        if ($id_theme < 1 || empty($_POST['ids'])) redirect('/' . $this->module . '/');

        $ids = array_filter($_POST['ids'], function(&$r){
            return ($r = intval($r)) > 0;
        });


        $themeModel = $this->Register['ModManager']->getModelInstance('Themes');
        $themeModel->bindModel('forum');
        $theme = $themeModel->getById($id_theme);

        if (empty($theme) || !$theme->getForum())
            return $this->showInfoMessage(__('Topic not found'), '/' . $this->module . '/');
		if ($theme->getPosts() == 0)
			return $this->showInfoMessage(__('Not enough posts'), '/' . $this->module . '/');
		
		
        // Формируем список форумов, чтобы можно было переместить тему в другой форум
		$forum_id = $theme->getId_forum();
        $forums = $this->Model->getCollection(array(), array('order' => 'pos'));
        if (!$forums)
            return $this->showInfoMessage(__('Can not find forum'), '/' . $this->module . '/');


        $options = '';
        foreach ($forums as $forum) {
            if ($forum->getId() == $theme->getId_forum())
                $options = $options . '<option value="' . $forum->getId() . '" selected>' . h($forum->getTitle()) . '</option>' . "\n";
            else
                $options = $options . '<option value="' . $forum->getId() . '">' . h($forum->getTitle()) . '</option>' . "\n";
        }
		
		
        $gr_access = array();
        $groups = $this->ACL->getGroups();
        foreach ($groups as $grid => $grval) {
            if (isset($_POST['gr_access_' . $grid]))
                $gr_access[] = $grid;
        }


        //turn access
        $this->ACL->turn(array($this->module, 'edit_themes', $theme->getId_forum()));

        // Check access to this forum. May be locked by pass or posts count
        $this->__checkForumAccess($theme->getForum());
        $this->__checkThemeAccess($theme);


        // Check for preview or errors
        $data = array('title' => null, 'description' => null, 'forum_id' => null,
            'locked' => null, 'first_top' => null, 'new_theme' => null, 'theme' => null, 'posts_ids' => null);
        $data = Validate::getCurrentInputsValues($data);
        $errors = $this->Register['Validate']->getErrors();
        if (isset($_SESSION['FpsForm'])) unset($_SESSION['FpsForm']);


        // Page nav
        $this->addToPageMetaContext('entity_title', __('Move posts') . ' - ' . h($theme->getTitle()));
        $markers = array();
        $markers['navigation'] = get_link(__('Home'), '/') . __('Separator')
            . get_link(__('Forums list'), '/' . $this->module . '/') . __('Separator')
            . get_link($theme->getForum()->getTitle(), '/' . $this->module . '/view_forum/' . $forum_id) . __('Separator')
            . get_link($theme->getTitle(), '/' . $this->module . '/view_theme/' . $id_theme) . __('Separator')
            . __('Move posts');
        $markers['meta'] = '';
        $this->_globalize($markers);


	
        // SELECT posts
        $postsModel = $this->Register['ModManager']->getModelInstance('Posts');
        $posts = $postsModel->getCollection(
            array("`id` IN (" . implode(', ', $ids) . ")"),
            array('order' => 'time ASC, id ASC'));
        if (empty($posts))
            return $this->showInfoMessage(__('Not enough posts'), '/' . $this->module . '/');

        foreach ($posts as $post) {
            $message = $this->Textarier->parseBBCodes($post->getMessage(), $post);
            $post->setMessage($message);
        }


        $context = array(
            'posts' => $posts,
            'theme' => $theme,
            'theme2' => $data,
            'action' => get_url('/' . $this->module . '/move_posts/' . $id_theme),
            'errors' => !empty($errors) ? $errors : '',
            'options' => $options,
            'gr_access' => $gr_access,
        );
        $source = $this->render('move_posts_form.html', array('context' => $context));

        return $this->_view($source);
    }


	
    public function unite_themes($id_theme = null) {
        $id_theme = intval($id_theme);
        if ($id_theme < 1) redirect('/' . $this->module . '/');

        $themeModel = $this->Register['ModManager']->getModelInstance('Themes');
        $themeModel->bindModel('forum');
        $themeModel->bindModel('poll');
        $theme = $themeModel->getById($id_theme);
		
        if (empty($theme) || !$theme->getForum())
            return $this->showInfoMessage(__('Topic not found'), '/' . $this->module . '/');


        // turn access (send forum ID for check special rules)
        $this->ACL->turn(array($this->module, 'add_themes', $theme->getId_forum()));
        $this->ACL->turn(array($this->module, 'edit_themes', $theme->getId_forum()));


		$new_theme_title = preg_replace('#\s*\(\d+\)$#u', '', trim($_POST['unite_theme']));
		$new_theme_title = $this->Register['DB']->escape($new_theme_title);
		$new_theme = $themeModel->getCollection(array("`title` LIKE '$new_theme_title'"));
		if (!empty($new_theme[0])) {
			$new_theme = $new_theme[0];
			$new_theme_id = $new_theme->getId();
		}
		
		if (empty($new_theme) || !$new_theme->getForum())
			return $this->showInfoMessage(__('Topic not found'), '/' . $this->module . '/');
        
		try {
			$this->Model->movePostsToTheme($theme, $new_theme, array(), true);
		} catch (Exception $e) {
			return $this->showInfoMessage($e->getMessage(), '/' . $this->module . '/');
		}


        //clean cahce
        $tags = array('theme_id_' . $id_theme, 'theme_id_' . $new_theme_id);
        $this->Cache->clean(CACHE_MATCHING_ANY_TAG, $tags);
        $this->DB->cleanSqlCache();

        if ($this->Log)
            $this->Log->write('split theme', 'theme id(' . $id_theme . ')');
        return $this->showInfoMessage(__('Operation is successful'), '/' . $this->module . '/view_theme/' . $new_theme_id);
    }
	

	
    public function search_themes() {
		$this->counter = false;
        $result = array();
        if (empty($_GET['name']))
            return $this->showAjaxResponse($result);
		
        $name = $this->Register['DB']->escape($_GET['name']);
        $params = array("`title` LIKE '%$name%'");
		
		$themesModel = $this->Register['ModManager']->getModelInstance('Themes');
        $entitys = $themesModel->getCollection($params, array('limit' => 20));

        if (is_array($entitys) && count($entitys))
            $result = array_map(function($row){
                return array(
                    'title' => h($row->getTitle()),
                    'id' => h($row->getId()),
                );
            }, $entitys);
        return $this->showAjaxResponse($result);
    }
	
	
	
	protected function _getValidateRules()
	{
		$max_attach = Config::read('max_attaches', $this->module);
		if (empty($max_attach) || !is_numeric($max_attach)) $max_attach = 5;
		
		$rules = array(
			'update_forum' => array(
                'title' => array(
                    'required' => true,
                    'max_lenght' => 150,
                    'pattern' => V_TITLE,
                ),
                'description' => array(
                    'required' => false,
                    'max_lenght' => 250,
                ),
            ),
			'move_posts' => array(
				'theme' => array(
					'required' => true,
					'max_lenght' => 250,
				),
				'title' => array(
					'required' => true,
					'max_lenght' => Config::read('max_post_lenght', 'forum'),
					'pattern' => V_TITLE,
				),
				'description' => array(
					'max_lenght' => 200,
				),
				'forum_id' => array(
					'required' => true,
					'pattern' => V_INT,
				),
			),
			'add_theme' => array(
				'theme' => array(
					'required' => true,
					'max_lenght' => 250,
					'pattern' => V_TITLE,
				),
				'main_text' => array(
					'required' => true,
					'max_lenght' => Config::read('max_post_lenght', 'forum'),
					'title' => 'Text',
				),
				'description' => array(
					'max_lenght' => 200,
				),
                'first_top' => array(
                    'pattern' => V_INT,
                ),
				'files__attach' => array(
					'for' => array(
						'from' => 1,
						'to' => $max_attach,
					),
					'type' => 'file',
					'max_size' => Config::read('max_file_size'),
				),
			),
			'update_theme' => array(
				'theme' => array(
					'required' => true,
					'max_lenght' => 200,
				),
				'description' => array(
					'max_lenght' => 250,
				),
				'first_top' => array(
					'pattern' => V_INT,
				),
				'unite_theme' => array(
					'max_lenght' => 200,
				),
			),
			'add_post' => array(
				'mainText' => array(
					'required' => true,
					'max_lenght' => Config::read('max_post_lenght', 'forum'),
				),
				'files__attach' => array(
					'for' => array(
						'from' => 1,
						'to' => $max_attach,
					),
					'type' => 'file',
					'max_size' => Config::read('max_file_size'),
				),
			),
			'update_post' => array(
				'mainText' => array(
					'required' => true,
					'max_lenght' => Config::read('max_post_lenght', 'forum'),
				),
				'files__attach' => array(
					'for' => array(
						'from' => 1,
						'to' => $max_attach,
					),
					'type' => 'file',
					'max_size' => Config::read('max_file_size'),
				),
			),
			'upload_attaches' => array(
				'files__attach' => array(
					'for' => array(
						'from' => 1,
						'to' => $max_attach,
					),
					'max_size' => Config::read('max_attaches_size', $this->module),
				),
			),
		);
		
		return $rules;
	}
}
