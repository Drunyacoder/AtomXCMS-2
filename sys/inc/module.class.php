<?php
/*-----------------------------------------------\
| 												 |
|  @Author:       Andrey Brykin (Drunya)         |
|  @Email:        drunyacoder@gmail.com          |
|  @Site:         http://fapos.net			     |
|  @Version:      1.6.2                          |
|  @Project:      CMS                            |
|  @Package       CMS Fapos                      |
|  @Subpackege    Module Class                   |
|  @Copyright     ©Andrey Brykin 		         |
|  @Last mod.     2014/03/14                     |
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


class Module {

	/**
	* @page_title title of the page
	*/
	public $page_title = '';
	/**
	* @var string 
	*/
	public $page_meta_keywords;
	/**
	* @var string
	*/
	public $page_meta_description;
	/**
	* @template layout for module
	*/
	public $template = 'default';
	/**
	* @categories list of categories
	*/
	public $categories = '';
	/**
	* @module_title title for module
	*/
	public $module_title = '';
	/**
	* @module current module
	*/
	public $module = '';
	/**
	* @cacheTags Cache tags
	*/
	public $cacheTags = array();
	/**
	* @cached   use the cache engine
	*/
	protected $cached = true;
	
	/**
	* @var (str)   comments block
	*/
	protected $comments = '';
	
	/**
	* @var (str)   add comments form
	*/
	protected $comments_form = '';
	
	/**
	* @var    database object
	*/
	protected $DB;
	/**
	* uses for work with actions log
	*
	* @var    logination object
	*/
	protected $Log;
	/**
	* uses for work with parser (chuncks, snippets, global markers ...)
	*
	* @var    parser object
	*/
	protected $Parser;
	
	/**
	* Access control list
	*
	* @var object
	*/
	protected $ACL;
	
	/**
	 * Object for work with text
	 */
	protected $Textarier;
	
	/**
	 * @var object
	 */
	protected $AddFields = false; 
	
	/**
	 * if true - counter not worck
	 *
	 * @var boolean
	 */
	public $counter = true;
	
	/**
	 * Use wrapper?
	 *
	 * @var boolean
	 */
	protected $wrap = true;

    /**
     * @var object
     */
    public $Register;
	
	/**
	 * @var object
	 */
	public $Model;

    /**
     * @var array
     */
    private $pageMetaContext = array(
        'module' => '',
        'category_title' => '',
        'entity_title' => '',
        'page' => '',
    );
	 
	
	
	/**
	 * @var array
	 */
	protected $globalMarkers = array(
		'module' => '',
		'navigation' => '',
		'pagination' => '',
		'meta' => '',
		'add_link' => '',
		'comments_pagination' => '',
		'comments' => '',
        'comments_form' => '',
		'fps_curr_page' => 1,
		'fps_pagescnt' => 1,
	);
	
	
	
	
	/**
	 * @param array $params - array with modul, action and params
	 *
	 * Initialize needed objects adn set needed variables
	 */
    public function __construct($params)
    {
        $this->Register = Register::getInstance();
        $this->Register['module'] = $params[0];
        $this->Register['action'] = $params[1];
        $this->Register['params'] = $params;
		
		if (is_callable(array($this, '_getValidateRules')))
			$this->Register['Validate']->setRules($this->_getValidateRules());
		$this->Register['Validate']->setModule($this->module);
        $this->setModel();
		
        //init needed objects. Core...
		// Use for templater (layout)
		$this->template = $this->module;
        $viewerLoader = new Fps_Viewer_Loader(array('layout' => $this->template));
		$this->View = new Fps_Viewer_Manager($viewerLoader);
		$this->Parser = new Document_Parser;
		$this->Parser->templateDir = $this->template;
		
		$this->DB = $this->Register['DB'];
		$this->Textarier = new PrintText;
		if (Config::read('secure.system_log')) $this->Log = new Logination;

		// init aditional fields
		if ($this->Register['Config']->read('use_additional_fields')) {
			$this->AddFields = new FpsAdditionalFields;
			$this->AddFields->module = $this->module;
		}

		
		//init Access Control List
		$this->ACL = $this->Register['ACL'];
		$this->_beforeRender();
		
		if ($this->Register['Config']->read('active', $params[0]) == 0) {
			if ('chat' === $params[0]) die('Этот модуль отключен');
			return $this->showInfoMessage('Этот модуль отключен', '/');
		}
		
		$this->page_title = ($this->Register['Config']->read('title', $this->module))
            ? h(Config::read('title', $this->module)) : h(Config::read('title'));
		$this->params = $params;
		
		//cache
		$this->Cache = new Cache;
		if ($this->Register['Config']->read('cache') == 1) {
			$this->cached = true;
			$this->cacheKey = $this->getKeyForCache($params);
			$this->setCacheTag(array('module_' . $params[0]));
			if (!empty($params[1])) $this->setCacheTag(array('action_' . $params[1]));
		} else {
			$this->cached = false;
		}
		
		//meta tags
		$this->page_meta_keywords = h(Config::read('keywords', $this->module));
		$this->page_meta_description = h(Config::read('description', $this->module));
		
		if (empty($this->page_meta_keywords)) {
			$this->page_meta_keywords = h(Config::read('meta_keywords'));
		}
		if (empty($this->page_meta_description)) {
			$this->page_meta_description = h(Config::read('meta_description'));
		}
	}
	
	
	
	protected function setModel()
	{
		$class = ucfirst($this->module) . 'Model';
		if (class_exists($class)) $this->Model = new $class();
	}
	
	
	/**
	 * Uses for before render
	 * All code in this function will be worked before
	 * begin render page and launch controller(module)
	 *
	 * @return none
	 */
	protected function _beforeRender()
    {
        $this->addToPageMetaContext('module', ucfirst($this->module));

        if (
            (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST')
            || substr($_SERVER['PHP_SELF'], 1, 5) === 'admin'
            || in_array($this->module, array('chat', 'rating'))
        ) {
            $this->counter = false;
        }

		if (isset($_SESSION['page'])) unset($_SESSION['page']);
		if (isset($_SESSION['pagecnt'])) unset($_SESSION['pagecnt']);
	}
	

	/**
	 * Uses for after render
	 * All code in this function will be worked after
	 * render page.
	 *
	 * @return none
	 */
	protected function _afterRender()
    {
		// Cron
		if (Config::read('auto_sitemap')) {
			fpsCron('createSitemap', 86400);
		}
		
		
		/*
		* counter ( if active )
		* and if we not in admin panel (see _beforeRender())
		*/
		if ($this->counter === false) return;
		

        include_once ROOT . '/modules/statistics/index.php';
        if (Config::read('active', 'statistics') == 1) {
            StatisticsModule::index();
        } else {
            StatisticsModule::viewOffCounter();
        }
	}
	

	/**
	* @param string $content  data for parse and view
	* @access   protected
	*/
	public function _view($content)
    {
        $Register = Register::getInstance();

		if (!empty($this->template) && $this->wrap == true) {
            Plugins::intercept('before_parse_layout', $this);
			

			$this->View->setLayout($this->template);
			$markers = $this->getGlobalMarkers(file_get_contents($this->View->getTemplateFilePath('main.html')));
            $markers['content'] = $content;
			
			
			// Cache global markers
			if ($this->cached) {
				if ($this->Cache->check($this->cacheKey . '_global_markers')) {
					$gdata = $this->Cache->read($this->cacheKey . '_global_markers');
					$this->globalMarkers = array_merge($this->globalMarkers, unserialize($gdata));
				} else {
					$gdata = serialize($this->globalMarkers);
					$this->Cache->write($gdata, $this->cacheKey . '_global_markers', $this->cacheTags);
				}
			}
			
			
			$boot_time = round(getMicroTime() - $Register['fps_boot_start_time'], 4);
			$markers = array_merge($markers, array('boot_time' => $boot_time));
			
			$output = $this->render('main.html', $markers);
		} else {
            $output = $content;
		}

		$this->_afterRender();
		echo $output;

		if (Config::read('debug_mode') == 1) {
            echo AtmDebug::getBody();
		}
	}
	

	public function render($fileName, array $markers = array())
	{
        $additionalMarkers = $this->getGlobalMarkers();
        $this->_globalize($additionalMarkers);
		$source = $this->View->view($fileName, array_merge($markers, $this->globalMarkers));
		return $source;
	}
	
	
	public function renderString($string, array $markers = array())
	{
        $additionalMarkers = $this->getGlobalMarkers();
        $this->_globalize($additionalMarkers);
		$source = $this->View->parseTemplate($string, array_merge($markers, $this->globalMarkers));
		return $source;
	}


    protected function getGlobalMarkers($html = '')
    {
        $Register = Register::getInstance();
		$markers1 = $this->Parser->getGlobalMarkers($html);
        $markers2 = array(
            'module' => $this->module,
            'action' => $Register['action'],
            'params' => $Register['params'],
            'title' => $this->getCompleteMetaTag($this->page_title),
            'meta_title' => $this->getCompleteMetaTag($this->page_title),
            'meta_description' => $this->getCompleteMetaTag($this->page_meta_description),
            'meta_keywords' => $this->getCompleteMetaTag($this->page_meta_keywords),
            'module_title' => $this->module_title,
            'categories' => $this->categories,
            'comments' => $this->comments,
			'comments_form' => $this->comments_form,
            'fps_curr_page' => (!empty($Register['page'])) ? intval($Register['page']) : 1,
            'fps_pagescnt' => (!empty($Register['pagescnt'])) ? intval($Register['pagescnt']) : 1,
            'fps_user' => (!empty($_SESSION['user'])) ? $_SESSION['user'] : array(),
			'is_home_page' => $Register['is_home_page'] ? true : false,
        );
		$markers = array_merge($markers1, $markers2);
        //pr($markers);
        return $markers;
    }


    /**
     * Save markers. Get list of markers
     * and content wthat will be instead markers
     * Before view this markers will be replaced in
     * all content
     *
     * @param array $markers - market->value
     * @return none
     */
	protected function _globalize($markers = array())
    {
		$this->globalMarkers = array_merge($this->globalMarkers, $markers);
	}
	

	/**
	* @param  mixed $tag
    * @return boolean
	*/
	public function setCacheTag($tag)
    {
		if ((Config::read('cache') == true || Config::read('cache') == 1) && $this->cached === true) {
			if (is_array($tag)) {
				foreach ($tag as $_tag) {
					$this->setCacheTag($_tag);
				}
			} else {
				$this->cacheTags[] = $tag;
				return true;
			}
		}
		return false;
	}
	

	/**
	* create unique id for cache file
     *
	* @param array $params <module>[ action [ param1 [ param2 ]]]
    * @return string
	*/
	private function getKeyForCache($params)
    {
		$cacheId = '';
		foreach ($params as $value) {
			if (is_array($value)) {
				foreach ($value as $_value) {
					$cacheId = $cacheId . $_value . '_';
				}
				continue;
			}
			$cacheId = $cacheId . $value . '_';
		}
		
		if (!empty($_GET['page']) && is_numeric($_GET['page'])) {
				$cacheId = $cacheId . intval($_GET['page']) . '_';
		}
		if (!empty($_GET['order'])) {
			$order = (string)$_GET['order'];
			if (!empty($order)) {
				$order = substr($order, 0, 10);
				$cacheId .= $order . '_';
			}
		}
		if (!empty($_GET['asc'])) {
			$cacheId .= 'asc_';
		}
		$cacheId = (!empty($_SESSION['user']['status'])) ? $cacheId . $_SESSION['user']['status'] : $cacheId . 'guest';
		return $cacheId;
	}
	

	/**
	 * Build categories list ({CATEGORIES})
	 *
	 * @param mixed $cat_id
	 * @return string.
	 */
	protected function _getCatsTree($cat_id = false, $url_sufix = '')
	{
		// Check cache
		if ($this->cached && $this->Cache->check('category_' . $this->cacheKey)) {
			$this->categories = $this->Cache->read('category_' . $this->cacheKey);
			return;
		}
	
	
		// get mat id
		$id = (!empty($cat_id)) ? intval($cat_id) : false;
		if ($id < 1) $id = false;
		
	
		// Get current action
		if (empty($this->params[1])) $action = 'index';
		else $action = trim($this->params[1]);
		$output = '';
		
		
		// type o tree
		if (!empty($id)) {
			switch ($action) {
				case 'category':
				case 'view':
					$conditions = array('parent_id' => intval($id));
					$cats = $this->DB->select($this->module . '_categories', DB_ALL,
					array('cond' => $conditions));
					break;
				default:
					break;
			}
		}
		if (empty($cats)) {
            if ($id) {
                $current_cat = $this->DB->select($this->module . '_categories', DB_ALL, array(
                    'cond' => array(
                        'id' => $id,
                    ),
                ));
            }
			if (!empty($current_cat[0])) {
				$cats = $this->DB->select($this->module . '_categories', DB_ALL, array(
					'cond' => array(
						'parent_id' => $current_cat[0]['parent_id'],
					),
				));
			} else {
				$cats = $this->DB->select($this->module . '_categories', DB_ALL, array(
					'cond' => array(
						'parent_id = 0 OR parent_id IS NULL',
					),
				));
			}
		}
		
		
		// Build list
		if (count($cats) > 0) {
			foreach ($cats as $cat) {
				$output .= '<li>' . get_link(h($cat['title']), '/' . $this->module
                        . '/category/' . $cat['id'] . $url_sufix) . '</li>';
			}
		}
		
		
		$this->categories = '<ul class="atm-categories">' . $output . '</ul>';
		
		if ($this->cached)
			$this->Cache->write($this->categories, 'category_' . $this->cacheKey
			, array('module_' . $this->module, 'category_block'));
	}
	

	protected  function _buildBreadCrumbs($cat_id = false)
    {
		$tree = array();
		$output = '<ul>';
		
		// Check cache
		if ($this->cached && $this->Cache->check('category_tree_' . $this->cacheKey)) {
			$tree = $this->Cache->read('category_tree_' . $this->cacheKey);
			$tree = json_decode($tree, true);
			return $tree;
		} else {
			$tree = $this->DB->select($this->module . '_categories', DB_ALL);
			
			if ($this->cached)
				$this->Cache->write(json_encode($this->categories), 'category_tree_' . $this->cacheKey
				, array('module_' . $this->module, 'category_block'));
		}
		
		
		if (!empty($tree) && count($tree) > 0) {
			$output = $this->_buildBreadCrumbsNode($tree, $cat_id);
			return $output;
		}
		return '';
	}
	
	
	/**
	 * Build bread crumbs
	 * Use separator for separate links
	 * 
	 * @param array $tree
	 * @param mixed $cat_id
	 * @param mixed $parent_id
	 * @return string
	 */
	protected  function _buildBreadCrumbsNode($tree, $cat_id = false, $parent_id = false)
    {
		$output = '';
		
		
		if (empty($cat_id)) {
			$output = h($this->module_title);	
			
		} else {
			foreach ($tree as $key => $node) {
				if ($node['id'] == $cat_id  && !$parent_id) {
					$output = h($node['title']);
					if (!empty($node['parent_id']) && $node['parent_id'] != 0) {
						$output = $this->_buildBreadCrumbsNode($tree, $cat_id, $node['parent_id']) . $output;
					}
					break;
				} else if ($parent_id && $parent_id == $node['id']) {
					$output = get_link(h($node['title']), '/' . $this->module . '/category/' . $node['id']) . __('Separator');
					if (!empty($node['parent_id']) && $node['parent_id'] != 0) {
						$output = $this->_buildBreadCrumbsNode($tree, $cat_id, $node['parent_id']) . $output;
					}
					break;
				}
			}
			
			if (!$parent_id)
				$output = get_link(h($this->module_title), '/' . $this->module . '/') . __('Separator') . $output;
		}
		
		if (true && !$parent_id) $output = get_link(__('Home'), '/') . __('Separator') . $output;
		return $output;
	}
	

	/**
	 * Build categories list for select input
	 *
	 * @param array $cats
	 * @param mixed $curr_category
	 * @param mixed $id
	 * @param string $sep
	 * @return string
	 */
	protected function _buildSelector($cats, $curr_category = false, $id = false, $sep = '- ')
    {
		$out = '';
		foreach ($cats as $key => $cat) {
			$parent_id = $cat->getParent_id();
			if (($id === false && empty($parent_id))
			|| (!empty($id) && $parent_id == $id)) {
				$out .= '<option value="' . $cat->getId() . '" ' 
				. (($curr_category !== false && $curr_category == $cat->getId()) ? "selected=\"selected\"" : "") 
				. '>' . $sep . h($cat->getTitle()) . '</option>';
				unset($cats[$key]);
				$out .= $this->_buildSelector($cats, $curr_category, $cat->getId(), $sep . '- ');
			}
		}
		
		return $out;
	}


    public function showInfoMessage($message, $queryString = null, $requestIsOk = false)
	{
		// AJAX request
		if (isset($_GET['ajax'])) {
			$data = array();
			$data['redirect'] = get_url($queryString);
			if ($requestIsOk) $data = array('result' => $message);
			else $data = array('errors' => wrap_errors($message));
			return $this->showAjaxResponse($data);
		}
		
		
		header( 'Refresh: ' . $this->Register['Config']->read('redirect_delay') . '; url=http://' . $_SERVER['SERVER_NAME'] . get_url($queryString));
		$output = $this->render('infomessagegrand.html', array('data' => array('info_message' => $message, 'error_message' => null)));
		echo $output;
		die();
	}

	
	public function upload_attaches()
	{
		$this->counter = false;
		$key = ($this->module === 'forum') ? 'add_posts' : 'add_materials';
		if (!$this->ACL->turn(array($this->module, $key), false) ||
		!$this->ACL->turn(array($this->module, 'use_attaches'), false)) {
			$this->showAjaxResponse(array(
				'errors' => __('Permission denied'), 
				'result' => '0'
			));
		}
		
		
		$attachModel = $this->Register['ModManager']->getModelInstance($this->module . 'Attaches');
		$errors = '';
		
		if (!empty($_FILES) && is_array($_FILES)) {
			$cnt = 0;
			$new_file_size = 0;
			foreach ($_FILES as $name => $file) {
				if (preg_match('#^attach\d+$#', $name)) {
					$cnt++;
					$new_file_size += $file['size'];
				}
			}
		}
		if ($cnt > Config::read('max_attaches', $this->module))
			$errors .= '<li>' . sprintf(__('You can upload only %s file(s)'), Config::read('max_attaches', $this->module)) . '</li>';
		
		if (!empty($_SESSION['user']['id'])) {
			$old_files_size = $attachModel->getUserOveralFilesSize($_SESSION['user']['id']);
			$overal_files_size = intval($new_file_size) + $old_files_size;
			$max_overal_size = Config::read('max_all_attaches_size', $this->module) * 1024 * 1024;
		} else {
			$overal_files_size = intval($new_file_size);
			$max_overal_size = Config::read('max_guest_attaches_size', $this->module) * 1024 * 1024;
		}
		
		if ($overal_files_size > $max_overal_size)
			$errors .= '<li>' . sprintf(__('Max overal files size is %s Mb'), $max_overal_size / 1024 / 1024) . '</li>';
		
		
		$errors .= $this->Register['Validate']->check($this->Register['action']);
		if (!empty($errors)) $this->showAjaxResponse(array(
			'errors' => $this->Register['Validate']->wrapErrors($errors), 
			'result' => '0'
		));
		
		$attaches = downloadAtomAttaches($this->module);
		$this->showAjaxResponse($attaches);
	}
	
	
	public function get_attaches()
	{
		$this->counter = false;
		if (empty($_SESSION['user']['id'])) $this->showAjaxResponse(array());
		$user_id = $_SESSION['user']['id'];
		
		$attachModel = $this->Register['ModManager']->getModelInstance($this->module . 'Attaches');
		$attachModel->bindModel('user');
		$attaches = $attachModel->getCollection(array('user_id' => $user_id));
		if ($attaches) {
			foreach ($attaches as $k => &$attach) {
				// delete collizions
				if (!file_exists(ROOT . '/sys/files/' . $this->module . '/' . $attach->getFilename())) {
					$attach->delete();
					unset($attaches[$k]);
				} else {
					$attach = $attach->asArray();
				}
			}
		}
		$this->showAjaxResponse($attaches);
	}
	
	
	public function delete_attach($id)
	{
		$this->counter = false;
		if (!$this->ACL->turn(array($this->module, 'delete_attaches'), false)) {
			$this->showAjaxResponse(array(
				'errors' => __('Permission denied'), 
				'result' => '0'
			));
		}
			
		if (empty($_SESSION['user']['id'])) $this->showAjaxResponse(array());
		$user_id = $_SESSION['user']['id'];
		
		$attachModel = $this->Register['ModManager']->getModelInstance($this->module . 'Attaches');
		$attach = $attachModel->getById($id);
		
		$errors = '';
		if ($user_id !== $attach->getUser_id())
			$errors .= '<li>' . __('Permission denied') . '</li>';
			
		if (!empty($errors)) {
			$this->showAjaxResponse(array(
				'result' => '0', 
				'errors' => $this->Register['Validate']->wrapErrors($errors),
			));
		}
			
		if ($attach) {
			$filename = $attach->getFilename();
			if (!empty($filename) && file_exists(ROOT . '/sys/files/' . $this->module . '/' . $filename)) {
				_unlink(ROOT . '/sys/files/' . $this->module . '/' . $filename);
			}
			$attach->delete();
		}
		$this->showAjaxResponse(array('result' => '1'));
	}
	
	
	
	/**
	 * Replace image marker
	 */
	public function insertImageAttach($entity, $announce, $module = null)
	{
		$attachment = null;
		$module = (!empty($module)) ? $module : $this->module;
       
		$sizex = $this->Register['Config']->read('img_size_x', $module);
		$sizey = $this->Register['Config']->read('img_size_y', $module);
		$sizex = intval($sizex);
		$sizey = intval($sizey);
		$style = ' style="max-width:' . $sizex . 'px; max-height:' . $sizey . 'px;"';
	   
        $attaches = ($module == 'forum') ? $entity->getAttacheslist() : $entity->getAttaches();
		

        if (!empty($attaches) && count($attaches) > 0) {
            $attachDir = ROOT . '/sys/files/' . $module . '/';
			
            foreach ($attaches as $attach) {
				if (file_exists($attachDir . $attach->getFilename())) {
				
				
					if ($attach->getIs_image() == 1) {
						$announce = str_replace('{IMAGE' . $attach->getAttach_number() . '}'
							, '<a class="gallery" href="' . get_url('/sys/files/' . $module . '/' . $attach->getFilename()) 
							. '"><img' . $style . ' alt="' . h($entity->getTitle()) . '" title="' . h($entity->getTitle()) 
							. '" title="" src="' . get_url('/image/' . $module . '/' . $attach->getFilename()) . '" /></a>'
							, $announce);
							
							
					} else {
						$attachment .= __('Attachment') . $attach->getAttach_number() 
							. ': ' . get_img('/sys/img/file.gif', array('alt' => __('Open file'), 'title' => __('Open file'))) 
							. '&nbsp;' . get_link(($attach->getSize() / 1000) .' Kb', '/forum/download_file/' 
							. $attach->getFilename(), array('target' => '_blank')) . '<br />';
					}
				}
            }
        }
		
		if (!empty($attachment)) $entity->setAttachment($attachment);
		
		if (preg_match_all('#\{ATTACH(\d+)(\|(\d+))?(\|(left|right))?(\|([^\|]+))?\}#ui', $announce, $matches)) {
			$ids = array();
			$sizes = array();
			$floats = array();
			$descriptions = array();
			foreach ($matches[1] as $key => $id) {
				$ids[] = $id;
				$sizes[$id] = (!empty($matches[3][$key])) ? intval($matches[3][$key]) : false;
				$floats[$id] = (!empty($matches[5][$key])) ? 'float:' . $matches[5][$key] . ';' : false;
				$descriptions[$id] = (!empty($matches[7][$key])) ? $matches[7][$key] : false;
			}
			$ids = implode(', ', $ids);
			

			$attaches = $entity->getAttaches();
			if ($attaches) {
				foreach ($attaches as $attach) {
					
					if ($attach->getIs_image() == 1) {
						$style_ = (array_key_exists($attach->getId(), $sizes) && !empty($sizes[$attach->getId()])) 
							? ' style="width:' . $sizes[$attach->getId()] . 'px;' . $floats[$attach->getId()] . '"'
							: $style;
						$size = (array_key_exists($attach->getId(), $sizes) && !empty($sizes[$attach->getId()]))
							? '/' . $sizes[$attach->getId()]
							: '';
						$descr = (!empty($descriptions[$attach->getId()])) 
							? '<div class="atm-img-description">' . h($descriptions[$attach->getId()]) . '</div>' 
							: '';
						
						$announce = preg_replace('#\{ATTACH' . $attach->getId() . '[^\}]*\}#ui', 
							'<a class="gallery" href="' . get_url('/sys/files/' . $module . '/' . $attach->getFilename()) 
							. '"><img' . $style_ . ' alt="' . h($entity->getTitle()) . '" title="' . h($entity->getTitle()) 
							. '" title="" src="' . get_url('/image/' . $module . '/' . $attach->getFilename()) . $size . '" />' 
							. $descr .  '</a>',
							$announce);
					} else {
						$announce = preg_replace('#\{ATTACH' . $attach->getId() . '[^\}]*\}#', 
							__('Attachment') . $attach->getAttach_number() 
							. ': ' . get_img('/sys/img/file.gif', array('alt' => __('Open file'), 'title' => __('Open file'))) 
							. '&nbsp;' . get_link(($attach->getSize() / 1000) .' Kb', '/forum/download_file/' 
							. $attach->getFilename(), array('target' => '_blank')) . '<br />',
							$announce);
					}
				}
			}
		}
	
		return $announce;
	}
	
	
	
	// Функция возвращает путь к модулю
    public function getModuleURL($page = null)
	{
		$url = '/' . $this->module . '/' . $page;
		$url = str_replace('//', '/', $url);
		return $url;
	}
	
	
	
	// get path to tmp files of current module
	public function getTmpPath($file = null)
	{
		$path = '/sys/tmp/' . $this->module . '/' . (!empty($file) ? $file : '');
		return $path;
	}
	
	
	
	public function showAjaxResponse($array)
	{
        header('Content-type: application/json');
		echo json_encode($array); die();
	}


    public function getEntryId($id)
    {
        // HLU title of the entity
        if (!is_numeric($id)) {
            $clean_url_extention = Config::read('hlu_extention');
            $id = $this->Model->getIdByHluTitle(str_replace($clean_url_extention, '', $id));
        }
        return intval($id);
    }
	
	
	protected function _getDeniSectionsCond($categoryId = null, $group = null)
	{
		$group = (!$group && !empty($_SESSION['user']['status'])) ? $_SESSION['user']['status'] : 0;
		$sectionModel = $this->Register['ModManager']->getModelInstance($this->module . 'Categories');
		
		$where = array("CONCAT(',', `no_access`, ',') NOT LIKE '%,$group,%'");
		if (intval($categoryId) > 0) {
			$where['OR'] = array(
				"CONCAT('.', `path`) LIKE '%." . intval($categoryId) . ".%'",
				'id' => intval($categoryId),
			);
		}
		
		$deni_sections = $sectionModel->getCollection($where);

		$ids = array();
		if ($deni_sections) {
			foreach ($deni_sections as $deni_section) {
				$ids[] = $deni_section->getId();
			}
		}
		$ids = (count($ids)) ? implode(', ', $ids) : 'NULL';
		return "`category_id` IN ({$ids})";
	}
	

    protected function addToPageMetaContext($key, $value)
    {
        if (array_key_exists($key, $this->pageMetaContext))
            $this->pageMetaContext[$key] = $value;
    }


    protected function getCompleteMetaTag($tag)
    {
        try {
            $tag = $this->View->parseTemplate($tag, $this->pageMetaContext);
        } catch (Exception $e) {
            $tag = preg_replace('#(\{.*\})#i', '', $tag);
        }
        return $tag;
    }
}