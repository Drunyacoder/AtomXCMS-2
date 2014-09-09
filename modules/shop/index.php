<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Email:        drunyacoder@gmail.com         |
| @Site:         http://atomx.net              |
| @Version:      0.0.1                         |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @Subpackege    Shop Module                   |
| @Copyright     ©Andrey Brykin                |
| @Last mod      2014/06/25                    |
|----------------------------------------------|
|											   |
| any partial or not partial extension         |
| CMS Fapos,without the consent of the         |
| author, is illegal                           |
|----------------------------------------------|
| Любое распространение                        |
| CMS Fapos или ее частей,                     |
| без согласия автора, является не законным    |
\---------------------------------------------*/



/**
 *
 */
Class ShopModule extends Module {

	/**
	* @module_title  title of module
	*/
	public $module_title = 'Магазин';
	/**
	* @template  layout for module
	*/
	public $template = 'shop';
	/**
	* @module module indentifier
	*/
	public $module = 'shop';
	
	public $premoder_types = array('rejected', 'confirmed');
	


	/**
	 * Default NewsModule action
	 */
	public function index($category_id = null)
    {
		$category_id = intval($category_id);
		//turn access
		$this->ACL->turn(array($this->module, 'view_catalog'));
		
		
		if ($this->cached && $this->Cache->check($this->cacheKey)) {
			$source = $this->Cache->read($this->cacheKey);
			return $this->_view($source);
		}
		
		
		if (!empty($category_id)) {
			$categoryModel = $this->Register['ModManager']->getModelInstance('shopCategories');
			$category = $categoryModel->getById($category_id);
			if (!$category) 
				return $this->showInfoMessage(__('Can not find category'), '/' . $this->module . '/');
			$this->addToPageMetaContext('category_title', h($category->getTitle()));
		}
	
		
		$where = array("(quantity > 0 || hide_not_exists = '0')");
		// get products only from allowed categories (.no_access field)
		$where[] = $this->_getDeniSectionsCond($category_id);
		$where[] = $this->__getProductsFiltersCond();
		if (!$this->ACL->turn(array('other', 'can_see_hidden'), false)) {
			$where['available'] = 1;
		}
		if (!empty($tag)) {
			$tag = $this->Register['DB']->escape($tag);
			$where[] = "CONCAT(',', `tags`, ',') LIKE '%,{$tag},%'";
		}
		if (!$category_id) {
			$where['view_on_home'] = 1;
		}


        //формируем блок со списком  разделов
        $this->_getCatsTree(false);


		$total = $this->Model->getTotal(array('cond' => $where));
		list ($pages, $page) = pagination($total, Config::read('per_page', $this->module), '/' . $this->module . '/');
		$this->Register['pages'] = $pages;
		$this->Register['page'] = $page;
		$this->addToPageMetaContext('page', $page);

		
		$navi = array();
		$navi['add_link'] = '';
		$navi['navigation'] = $this->_buildBreadCrumbs();
		$navi['pagination'] = $pages;
		$navi['meta'] = __('Total materials') . $total;
		$this->_globalize($navi);
		
		
		$this->Model->bindModel('attributes_group');
		$this->Model->bindModel('attributes.content');
		$this->Model->bindModel('vendor');
		$this->Model->bindModel('category');
		$this->Model->bindModel('author');
        $params = array(
            'page' => $page,
            'limit' => $this->Register['Config']->read('per_page', $this->module),
            'order' => $this->Model->getOrderParam(),
        );
		$records = $this->Model->getCollection($where, $params);
		

		$filters = $this->__getProductsFilters($category_id);
		$filters .= $this->__getVendorsFilter($category_id);
		$this->_globalize(array('products_filters' => $filters));

		// create markers
		foreach ($records as $result) {
			$result->setModer_panel($this->_getAdminBar($result));
			$entry_url = get_url(entryUrl($result, $this->module));
			$result->setEntry_url($entry_url);
			

			// Cut announce
			$announce = $this->Textarier->getAnnounce($result->getMain()
				, $entry_url
				, 0 
				, $this->Register['Config']->read('announce_lenght', $this->module)
				, $result
			);
			$announce = $this->insertImageAttach($result, $announce);
			
			$result->setAnnounce($announce);
			$result->setCategory_url(get_url('/' . $this->module . '/category/' . $result->getCategory_id()));
			$result->setProfile_url(getProfileUrl($result->getAuthor()->getId()));


			//set users_id that are on this page
			$this->setCacheTag(array(
				'user_id_' . $result->getAuthor()->getId(),
				'record_id_' . $result->getId(),
			));
		}

		$source = $this->render('list.html', array('entities' => $records));
		
		//write int cache
		if ($this->cached)
			$this->Cache->write($source, $this->cacheKey, $this->cacheTags);

		return $this->_view($source);
	}
	
	
	public function upload_attaches()
	{
		$model = $this->Register['ModManager']->getModelInstance('ShopAttaches');
		$model->deleteNotRelated();
		
		$this->counter = false;
		if (!$this->ACL->turn(array($this->module, 'edit_products'), false)) {
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
	
	
	public function delete_attach($id)
	{
		$this->counter = false;
		if (!$this->ACL->turn(array($this->module, 'edit_products'), false)) {
			$this->showAjaxResponse(array(
				'errors' => __('Permission denied'), 
				'result' => '0'
			));
		}
			
		if (empty($_SESSION['user']['id'])) $this->showAjaxResponse(array());
		
		$attachModel = $this->Register['ModManager']->getModelInstance($this->module . 'Attaches');
		$attach = $attachModel->getById($id);
		
		$errors = '';
			
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
	
	
	public function as_main_attach($id)
	{
		$this->counter = false;
		if (!$this->ACL->turn(array($this->module, 'edit_products'), false)) {
			$this->showAjaxResponse(array(
				'errors' => __('Permission denied'), 
				'result' => '0'
			));
		}
			
		if (empty($_SESSION['user']['id'])) $this->showAjaxResponse(array());
		
		$attachModel = $this->Register['ModManager']->getModelInstance($this->module . 'Attaches');
		$attach = $attachModel->getById($id);
		
		$errors = array();
		
		if (!$attach) 
			$errors[] = __('Record not found');
			
		if (!empty($errors)) {
			$this->showAjaxResponse(array(
				'result' => '0', 
				'errors' => $this->Register['Validate']->wrapErrors($errors, true),
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
	

	private function __getProductsFiltersCond()
	{
		return $this->Model->getProductsFilterSubquery();
	}
	
	
	private function __getProductsFilters($category_id)
	{
		$data = $this->Model->getCategoryFilters($category_id);
		if (!$data) return '';

		$source = $this->render('filters.html', array('context' => $data));
		return $source;
	}
	
	
	private function __getVendorsFilter($category_id = null)
	{
		$data = $this->Model->getVendorsFilter($category_id);
		if (!$data) return '';
		
		$source = $this->render('filters.html', array('context' => $data));
		return $source;
	}
	


	/**
     * @param null|int $id
     */
	public function view ($id = null)
    {
		//turn access
		$this->ACL->turn(array($this->module, 'view_materials'));
		$id = intval($id);
		if (empty($id) || $id < 1) redirect('/');

		
		$this->Model->bindModel('attaches');
		$this->Model->bindModel('author');
		$this->Model->bindModel('category');
		$entity = $this->Model->getById($id);
		
		
		if (empty($entity)) redirect('/error.php?ac=404');
		if ($entity->getAvailable() == 0 && !$this->ACL->turn(array('other', 'can_see_hidden'), false)) 
			return $this->showInfoMessage(__('Permission denied'), '/' . $this->module . '/');
		if (!$this->ACL->checkCategoryAccess($entity->getCategory()->getNo_access())) 
			return $this->showInfoMessage(__('Permission denied'), '/' . $this->module . '/');
			
			
		if (!$this->ACL->turn(array('other', 'can_premoder'), false) && in_array($entity->getPremoder(), array('rejected', 'nochecked'))) {
			return $this->showInfoMessage(__('Permission denied'), '/' . $this->module . '/');
		}

        Plugins::intercept('view_category', $entity->getCategory());
			
		
		// Some gemor with add fields
		if (is_object($this->AddFields)) {
			$entity = $this->AddFields->mergeRecords(array($entity));
			$entity = $entity[0];
		}
		
		
		
		$max_attaches = $this->Register['Config']->read('max_attaches', $this->module);
		if (empty($max_attaches) || !is_numeric($max_attaches)) $max_attaches = 5;
		
		
		//category block
		$this->_getCatsTree($entity->getCategory()->getId());
		/* COMMENT BLOCK */
		if (Config::read('comment_active', $this->module) == 1 
		&& $this->ACL->turn(array($this->module, 'view_comments'), false) 
		&& $entity->getCommented() == 1) {
			if ($this->ACL->turn(array($this->module, 'add_comments'), false)) 
				$this->comments_form  = $this->_add_comment_form($id);
			$this->comments  = $this->_get_comments($entity);
		}
		

		$this->page_title = h($entity->getTitle()) . ' - ' . $this->page_title;
		$tags = $entity->getTags();
		$description = $entity->getDescription();
		if (!empty($tags)) $this->page_meta_keywords = h($tags);
		if (!empty($description)) $this->page_meta_description = h($description);
		
		$navi = array();
		$navi['module_url'] = get_url('/' . $this->module . '/');
		$navi['category_url'] = get_url('/' . $this->module . '/category/' . $entity->getCategory()->getId());
		$navi['category_name'] = h($entity->getCategory()->getTitle());
		$navi['navigation'] = $this->_buildBreadCrumbs($entity->getCategory()->getId());
		$this->_globalize($navi);
		
		
		$markers = array();
		$markers['moder_panel'] = $this->_getAdminBar($entity);
		$markers['profile_url'] = getProfileUrl($entity->getAuthor()->getId());
		
		
		$entry_url = get_url(entryUrl($entity, $this->module));
		$markers['entry_url'] = $entry_url;
		
		
		$announce = $entity->getMain();
		$announce = $this->Textarier->print_page($announce, $entity->getAuthor()->getStatus(), $entity->getTitle());
		$announce = $this->insertImageAttach($entity, $announce);


		$markers['main_text'] = $announce;
		$entity->setAdd_markers($markers);
		if ($entity->getTags()) $entity->setTags(explode(',', $entity->getTags()));
		
		
		$source = $this->render('material.html', array('entity' => $entity));
		
		
		$entity->setViews($entity->getViews() + 1);
		$entity->save();
		$this->DB->cleanSqlCache();
		
		return $this->_view($source);
	}



	/**
	 * Check user access and if all right
	 * delete record with geting ID.
	 *
	 * @param int $id
	 */
	public function delete($id = null)
    {
		$this->cached = false;
		$id = (int)$id;
		if ($id < 1) redirect('/');


		$target = $this->Model->getById($id);
		if (!$target) redirect('/');
		
		
		//turn access
		if (!$this->ACL->turn(array($this->module, 'delete_materials'), false) 
		&& (!empty($_SESSION['user']['id']) && $target->getAuthor_id() == $_SESSION['user']['id'] 
		&& $this->ACL->turn(array($this->module, 'delete_mine_materials'), false)) === false) {
			return showInfoMessage(__('Permission denied'), '/' . $this->module . '/');
		}
		
		
		//remove cache
		$this->Cache->clean(CACHE_MATCHING_TAG, array('module_' . $this->module, 'record_id_' . $id));
		$this->DB->cleanSqlCache();

		$target->delete();
		
		$user_id = (!empty($_SESSION['user']['id'])) ? intval($_SESSION['user']['id']) : 0;
		if ($this->Log) $this->Log->write('delete ' . $this->module, $this->module . ' id(' . $id . ') user id('.$user_id.')');
		return $this->showInfoMessage(__('Operation is successful'), '/' . $this->module . '/');
	}

	
	
	/**
	* add comment to entity
	*
	* @id (int)    entity ID
	* @return      info message
	*/
	public function add_comment($id = null)
    {
		include_once(ROOT . '/sys/inc/includes/add_comment.php');
	}
	
	
	/**
	* add comment form to entity
	*
	* @id (int)    entity ID
	* @return      html form
	*/
	private function _add_comment_form($id = null)
    {
		include_once(ROOT . '/sys/inc/includes/_add_comment_form.php');
		return $html;
	}
	
	
	
	/**
	* edit comment form to entity
	*
	* @id (int)    comment ID
	* @return      html form
	*/
	public function edit_comment_form($id = null)
    {
		include_once(ROOT . '/sys/inc/includes/edit_comment_form.php');
	}
	
	
	
	/**
	* update comment
	*
	* @id (int)    comment ID
	* @return      info message
	*/
	public function update_comment($id = null)
    {
		include_once(ROOT . '/sys/inc/includes/update_comment.php');
	}
	
	
	
	/**
	* get comments for entity
	*
	* @id (int)    entity ID
	* @return      html comments list
	*/
	private function _get_comments($entity = null)
    {
		include_once(ROOT . '/sys/inc/includes/_get_comments.php');
		return $html;
	}
	
	
	
	/**
	* delete comment
	*
	* @id (int)    comment ID
	* @return      info message
	*/
	public function delete_comment($id = null)
    {
		include_once(ROOT . '/sys/inc/includes/delete_comment.php');
	}



	public function set_rating($id = null)
    {
		include_once(ROOT . '/sys/inc/includes/set_rating.php');
	}		
	
	

	/**
	* @param int $id - record ID
	*
	* update date by record also up record in recods list
	*/
	public function upper($id)
    {
		//turn access
		$this->ACL->turn(array($this->module, 'up_materials'));
		$id = (int)$id;
		if ($id < 1) redirect('/' . $this->module . '/');

		
		$entity = $this->Model->getById($id);
		if (!$entity) redirect('/' . $this->module . '/');
		
		$entity->setDate(date("Y-m-d H-i-s"));
		$entity->save();
		return $this->showInfoMessage(__('Operation is successful'), '/' . $this->module . '/');
	}

	
	
	/**
	* @param int $id - record ID
	*
	* allow record be on home page
	*/
	public function on_home($id)
    {
		//turn access
		$this->ACL->turn(array($this->module, 'on_home'));
		$id = (int)$id;
		if ($id < 1) redirect('/' . $this->module . '/');

		
		$entity = $this->Model->getById($id);
		if (!$entity) redirect('/' . $this->module . '/');
		
		$entity->setView_on_home('1');
		$entity->save();
		return $this->showInfoMessage(__('Operation is successful'), '/' . $this->module . '/');
	}


	
	/**
	* @param int $id - record ID
	*
	* denied record be on home page
	*/
	public function off_home($id)
    {
		//turn access
		$this->ACL->turn(array($this->module, 'on_home'));
		$id = (int)$id;
		if ($id < 1) redirect('/' . $this->module . '/');

		
		$entity = $this->Model->getById($id);
		if (!$entity) redirect('/' . $this->module . '/');
		
		$entity->setView_on_home('0');
		$entity->save();
		return $this->showInfoMessage(__('Operation is successful'), '/' . $this->module . '/');
	}
	
	
	
	/**
	* @param int $id - record ID
	*
	* fix or unfix record on top on home page
	*/
	public function fix_on_top($id)
    {
		$this->ACL->turn(array($this->module, 'on_home'));
		$id = (int)$id;
		if ($id < 1) redirect('/' . $this->module . '/');

		$target = $this->Model->getById($id);
		if (!$target) redirect('/');
		
		$curr_state = $target->getOn_home_top();
		$dest = ($curr_state) ? '0' : '1';
		$target->setOn_home_top($dest);
		$target->save();
		return $this->showInfoMessage(__('Operation is successful'), '/' . $this->module . '/');
	}
	
	
	
	
	/**
	* @param int $id - record ID
	*
	* fix or unfix record on top on home page
	*/
	public function premoder($id, $type)
    {
		$this->ACL->turn(array('other', 'can_premoder'));
		$id = (int)$id;
		if ($id < 1) redirect('/' . $this->module . '/');
		
		if (!in_array((string)$type, $this->premoder_types)) 
			return $this->showInfoMessage(__('Some error occurred'), '/' . $this->module . '/');

		$target = $this->Model->getById($id);
		if (!$target) redirect('/');
		
		$target->setPremoder((string)$type);
		$target->save();
		return $this->showInfoMessage(__('Operation is successful'), '/' . $this->module . '/');
	}
	
	
	
	
	/**
	* @param array $record - assoc record array
	* @return string - admin buttons
	*
	* create and return admin bar
	*/
	protected function _getAdminBar($record)
    {
		$moder_panel = '';
        $uid = $record->getAuthor_id();
        $id = $record->getId();
		
		
		if ($this->ACL->turn(array('other', 'can_premoder'), false) && 'nochecked' == $record->getPremoder()) {
			$moder_panel .= get_link('', '/' . $this->module . '/premoder/' . $id . '/confirmed',
				array(
					'class' => 'fps-premoder-confirm', 
					'title' => 'Confirm', 
					'onClick' => "return confirm('" . __('Are you sure') . "')",
				)) . '&nbsp;';
			$moder_panel .= get_link('', '/' . $this->module . '/premoder/' . $id . '/rejected',
				array(
					'class' => 'fps-premoder-reject', 
					'title' => 'Reject', 
					'onClick' => "return confirm('" . __('Are you sure') . "')",
				)) . '&nbsp;';
		
		
		} else if ($this->ACL->turn(array('other', 'can_premoder'), false) && 'rejected' == $record->getPremoder()) {
			$moder_panel .= get_link('', '/' . $this->module . '/premoder/' . $id . '/confirmed',
				array(
					'class' => 'fps-premoder-confirm', 
					'title' => 'Confirm', 
					'onClick' => "return confirm('" . __('Are you sure') . "')",
				)) . '&nbsp;';
		}
		

		if ($this->ACL->turn(array($this->module, 'edit_materials'), false) 
		|| (!empty($_SESSION['user']['id']) && $uid == $_SESSION['user']['id']
		&& $this->ACL->turn(array($this->module, 'edit_mine_materials'), false))) {
			$moder_panel .= get_link('', '/' . $this->module . '/edit_form/' . $id, array('class' => 'fps-edit')) . '&nbsp;';
		}
		
		if ($this->ACL->turn(array($this->module, 'up_materials'), false)) {
			$moder_panel .= get_link('', '/' . $this->module . '/fix_on_top/' . $id,
				array('class' => 'fps-star', 'onClick' => "return confirm('" . __('Are you sure') . "')")) . '&nbsp;';
			$moder_panel .= get_link('', '/' . $this->module . '/upper/' . $id,
				array('class' => 'fps-up', 'onClick' => "return confirm('" . __('Are you sure') . "')")) . '&nbsp;';
		}
		
		if ($this->ACL->turn(array($this->module, 'on_home'), false)) {
				if ($record->getView_on_home() == 1) {
					$moder_panel .= get_link('', '/' . $this->module . '/off_home/' . $id, array(
						'class' => 'fps-on', 
						'onClick' => "return confirm('" . __('Are you sure') . "')",
					)) . '&nbsp;';
				} else {
					$moder_panel .= get_link('', '/' . $this->module . '/on_home/' . $id, array(
						'class' => 'fps-off',
						'onClick' => "return confirm('" . __('Are you sure') . "')",
					)) . '&nbsp;';
				}
		}
		
		if ($this->ACL->turn(array($this->module, 'delete_materials'), false) 
		|| (!empty($_SESSION['user']['id']) && $uid == $_SESSION['user']['id']
		&& $this->ACL->turn(array($this->module, 'delete_mine_materials'), false))) {
			$moder_panel .= get_link('', '/' . $this->module . '/delete/' . $id,
				array('class' => 'fps-delete', 'onClick' => "return confirm('" . __('Are you sure') . "')")) . '&nbsp;';
		}
		
		
		return $moder_panel;
	}


    /**
     * RSS for news
	 *
     */
    public function rss() {
		include_once ROOT . '/sys/inc/includes/rss.php';
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
        $this->Model = $this->Register['ModManager']->getModelInstance('shopProducts');
		parent::_beforeRender();
	}
	
	
	protected function _getValidateRules() 
	{
		$max_attach = Config::read('max_attaches', $this->module);
		if (empty($max_attach) || !is_numeric($max_attach)) $max_attach = 5;
		$rules = array(
			'add' => array(
				'title' => array(
					'required' => true,
					'max_lenght' => 250,
					'title' => 'Title',
				),
				'main_text' => array(
					'required' => true,
					'max_lenght' => Config::read('max_lenght', $this->module),
					'title' => 'Text',
				),
				'cats_selector' => array(
					'required' => true,
					'pattern' => V_INT,
					'max_lenght' => 11,
					'title' => 'Category',
				),
				'description' => array(
					'required' => 'editable',
				),
				'tags' => array(
					'required' => 'editable',
					'pattern' => V_TITLE,
				),
				'sourse' => array(
					'required' => 'editable',
					'pattern' => V_TITLE,
				),
				'sourse_email' => array(
					'required' => 'editable',
					'pattern' => V_MAIL,
				),
				'sourse_site' => array(
					'required' => 'editable',
					'pattern' => V_URL,
				),
				'files__attach' => array(
					'for' => array(
						'from' => 1,
						'to' => $max_attach,
					),
					'type' => 'image',
					'max_size' => Config::read('max_attaches_size', $this->module),
				),
				'commented' => array(),
				'available' => array(),
			),
			'update' => array(
				'title' => array(
					'required' => true,
					'max_lenght' => 250,
					'title' => 'Title',
				),
				'main_text' => array(
					'required' => true,
					'max_lenght' => Config::read('max_lenght', $this->module),
					'title' => 'Text',
				),
				'cats_selector' => array(
					'required' => true,
					'pattern' => V_INT,
					'max_lenght' => 11,
					'title' => 'Category',
				),
				'description' => array(
					'required' => 'editable',
				),
				'tags' => array(
					'required' => 'editable',
					'pattern' => V_TITLE,
				),
				'sourse' => array(
					'required' => 'editable',
					'pattern' => V_TITLE,
				),
				'sourse_email' => array(
					'required' => 'editable',
					'pattern' => V_MAIL,
				),
				'sourse_site' => array(
					'required' => 'editable',
					'pattern' => V_URL,
				),
				'files__attach' => array(
					'for' => array(
						'from' => 1,
						'to' => $max_attach,
					),
					'type' => 'image',
					'max_size' => Config::read('max_attaches_size', $this->module),
				),
				'commented' => array(),
				'available' => array(),
			),
			'add_comment' => array(
				'login' => array(
					'required' => true,
					'pattern' => V_TITLE,
					'max_lenght' => 40,
				),
				'message' => array(
					'required' => true,
				),
				'captcha_keystring' => array(
					'pattern' => V_CAPTCHA,
					'title' => 'Kaptcha',
				),
			),
			'update_comment' => array(
				'login' => array(
					'required' => true,
					'pattern' => V_TITLE,
					'max_lenght' => 40,
				),
				'message' => array(
					'required' => true,
				),
				'captcha_keystring' => array(
					'pattern' => V_CAPTCHA,
					'title' => 'Kaptcha',
				),
			),
			'upload_attaches' => array(
				'files__attach' => array(
					'for' => array(
						'from' => 1,
						'to' => $max_attach,
					),
					'type' => 'image',
					'max_size' => Config::read('max_attaches_size', $this->module),
				),
			),
		);
		
		return $rules;
	}	
}

