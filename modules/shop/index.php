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

    private $storage;
	


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
        $this->_getCatsTree($category_id);


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
        $this->Model->bindModel('attaches');
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
			$entry_url = entryUrl($result, $this->module);
			$result->setEntry_url($entry_url);
			

			// Cut announce
			$announce = $this->Textarier->getAnnounce(
				$result->getMain(),
				$result,
				$this->Register['Config']->read('announce_lenght', $this->module));
			
			$result->setAnnounce($announce);
			$result->setCategory_url(get_url('/' . $this->module . '/category/' . $result->getCategory_id()));
			$result->setProfile_url(getProfileUrl($result->getAuthor()->getId()));


			//set users_id that are on this page
			$this->setCacheTag(array(
				'user_id_' . $result->getAuthor()->getId(),
				'record_id_' . $result->getId(),
			));
            if ($category_id)
                $this->setCacheTag(array('category_id_' . $category_id));
		}

		$source = $this->render('list.html', array('context' => array('entities' => $records)));
		
		//write int cache
		if ($this->cached)
			$this->Cache->write($source, $this->cacheKey, $this->cacheTags);

		return $this->_view($source);
	}
	

	/**
     * @param null|int $id
     */
	public function view ($id = null)
    {
		//turn access
		$this->ACL->turn(array($this->module, 'view_product'));
		$id = intval($id);
		if (empty($id) || $id < 1) redirect('/');


        $where = array("(quantity > 0 || hide_not_exists = '0')");
        $where['id'] = $id;

        $this->Model->bindModel('attributes_group');
        $this->Model->bindModel('attributes.content');
        $this->Model->bindModel('vendor');
        $this->Model->bindModel('category');
        $this->Model->bindModel('author');
        $this->Model->bindModel('attaches');
        $entity = $this->Model->getFirst($where);
		
		
		if (empty($entity)) redirect('/error.php?ac=404');
		if ($entity->getAvailable() == 0 && !$this->ACL->turn(array('other', 'can_see_hidden'), false)) 
			return $this->showInfoMessage(__('Permission denied'), '/' . $this->module . '/');
		if (!$this->ACL->checkCategoryAccess($entity->getCategory()->getNo_access())) 
			return $this->showInfoMessage(__('Permission denied'), '/' . $this->module . '/');


        Plugins::intercept('view_category', $entity->getCategory());

		// category block
		$this->_getCatsTree($entity->getCategory()->getId());
		// Comments && add comment form
		if (Config::read('comment_active', $this->module) == 1 
		&& $this->ACL->turn(array($this->module, 'view_comments'), false) 
		&& $entity->getCommented() == 1) {
			if ($this->ACL->turn(array($this->module, 'add_comments'), false)) 
				$this->comments_form  = $this->_add_comment_form($id);
			$this->comments  = $this->_get_comments($entity);
		}
		

		$tags = $entity->getTags();
		if (!empty($tags)) $this->addToPageMetaContext('tags', h($tags));
        $this->addToPageMetaContext('entity_title', h($entity->getTitle()));
        $this->addToPageMetaContext('category_title', h($entity->getCategory()->getTitle()));

		$navi = array();
		$navi['module_url'] = get_url('/' . $this->module . '/');
		$navi['category_url'] = get_url('/' . $this->module . '/category/' . $entity->getCategory()->getId());
		$navi['category_name'] = h($entity->getCategory()->getTitle());
		$navi['navigation'] = $this->_buildBreadCrumbs($entity->getCategory()->getId());
		$this->_globalize($navi);
		
		
		$markers = array();
		$markers['moder_panel'] = $this->_getAdminBar($entity);

		$entry_url = entryUrl($entity, $this->module);
		$markers['entry_url'] = $entry_url;
		$markers['main_text'] = $this->Textarier->parseBBCodes($entity->getDescription(), $entity);

		$entity->setAdd_markers($markers);


		$source = $this->render('material.html', array('context' => array('entity' => $entity)));
		
		return $this->_view($source);
	}


    public function basket()
    {
        //turn access
        if (!$this->ACL->turn(array($this->module, 'buy_product'), false))
            return $this->showInfoMessage(__('Permission denied'), $this->getModuleURL());

        $basket = $this->storage['basket'];

        $this->showAjaxResponse(array(
            'result' => 1,
            'data' => $basket,
        ));
    }


    public function add_to_basket($id = null, $quantity = null)
    {
        //turn access
        if (!$this->ACL->turn(array($this->module, 'buy_product'), false))
            return $this->showInfoMessage(__('Permission denied'), $this->getModuleURL());

        $id = intval($id);
        $quantity = (intval($quantity) > 0) ? intval($quantity) : 1;
        if (empty($id) || $id < 1)
            return $this->showInfoMessage(__('Empty ID'), $this->getModuleURL());


        $where = array("(quantity > 0 || hide_not_exists = '0')");
        $where['id'] = $id;


        $this->Model->bindModel('category');
        $entity = $this->Model->getFirst($where);


        if (empty($entity)) redirect('/error.php?ac=404');
        if ($entity->getAvailable() == 0 && !$this->ACL->turn(array('other', 'can_see_hidden'), false))
            return $this->showInfoMessage(__('Permission denied'), $this->getModuleURL());
        if (!$this->ACL->checkCategoryAccess($entity->getCategory()->getNo_access()))
            return $this->showInfoMessage(__('Permission denied'), $this->getModuleURL());


        $data = array(
            'id' => $entity->getId(),
            'title' => $entity->getTitle(),
            'price' => $entity->getFinal_price(),
            'quantity' => $quantity,
        );
        $basket = $this->storage['basket'];
        $push = true;

        if (count($basket['products'])) {
            foreach ($basket['products'] as &$product) {
                if ($data['id'] === $product['id']) {
                    $product['quantity'] += $data['quantity'];
                    $push = false;
                    break;
                }
            }
        }

        if ($push)
            array_push($basket['products'], $data);
        $basket['total'] += $data['price'] * $data['quantity'];


        $this->showAjaxResponse(array(
            'result' => 1,
            'data' => $basket,
        ));
    }


    public function remove_from_basket($id = null)
    {
        //turn access
        $this->ACL->turn(array($this->module, 'buy_product'));
        $id = intval($id);
        if (empty($id) || $id < 1) redirect($this->getModuleURL());


        $basket = $this->storage['basket'];
        if (count($basket['products'])) {
            foreach ($basket['products'] as $k => $product) {
                if ($product['id'] == $id) {
                    $basket['total'] -= ($product['price'] * $product['quantity']);
                    unset($basket['products'][$k]);
                }
            }
        }

        $this->showAjaxResponse(array(
            'result' => 1,
            'data' => $basket,
        ));
    }


    public function edit_basket($id = null, $quantity = 1)
    {
        //turn access
        $this->ACL->turn(array($this->module, 'buy_product'));
        $id = intval($id);
        $quantity = (intval($quantity) >= 1) ? intval($quantity) : 0;
        if (empty($id) || $id < 1) redirect($this->getModuleURL());


        $basket = $this->storage['basket'];
        if (count($basket['products'])) {
            foreach ($basket['products'] as $k => &$product) {
                if ($product['id'] == $id) {
                    if ($quantity == 0) {
                        $basket['total'] -= ($product['price'] * $product['quantity']);
                        unset($basket['products'][$k]);
                    } else {
                        $product['quantity'] = $quantity;
                        $basket['total'] -= ($product['quantity'] - $quantity) * $product['price'];
                    }
                }
            }
        }

        $this->showAjaxResponse(array(
            'result' => 1,
            'data' => $basket,
        ));
    }


    public function create_order_form()
    {
        //turn access
        $this->ACL->turn(array($this->module, 'buy_product'));


        $navi = array();
        $navi['navigation'] = $this->_buildBreadCrumbs();
        $this->_globalize($navi);
        $this->addToPageMetaContext('entity_title', __('Basket', $this->module));

        // category block
        $this->_getCatsTree();


        //$this->Model->bindModel('attributes_group');
        //$this->Model->bindModel('attributes.content');
        $this->Model->bindModel('vendor');
        $this->Model->bindModel('category');
        $this->Model->bindModel('author');
        $this->Model->bindModel('attaches');


        $errors = null;
        $entities = array();
        $total = 0;
        $basket = array();
        if (array_key_exists('basket', $this->storage)) {
            $basket = &$this->storage['basket'];
        }

        if (is_array($basket) && !empty($basket['products']) && count($basket['products'])) {
            $_total = 0;

            foreach ($basket['products'] as $basket_row) {
                $product = $this->Model->getById($basket_row['id']);
                $_total += $product->getFinal_price() * $basket_row['quantity'];
                $product->setQuantity($basket_row['quantity']);
                $entities[] = $product;
            }

            // Price might changed while user choosing a products
            if ($_total != $basket['total']) {
                $basket['total'] = $_total;
                $errors .= $this->Register['DocParser']->wrapErrors(__('Price of some products was changed', $this->module), true);
            }
            $total = $_total;
        }


        $fields = $this->Register['Validate']->getAndMergeFormPost($this->Register['action'], array(), true, true);
        $errors .= $this->Register['Validate']->getErrors();
        if (isset($_SESSION['FpsForm'])) unset($_SESSION['FpsForm']);

        $deliveryTypesModel = $this->Register['ModManager']->getModelInstance('shopDeliveryTypes');
        $delivery_types = $deliveryTypesModel->getCollection();


        $source = $this->render('order_form.html', array('context' => array(
            'entities' => $entities,
            'fields' => $fields,
            'delivery_types' => ($delivery_types) ? $delivery_types : array(),
            'total' => $total,
            'errors' => $errors,
        )));

        die($source);
        return $this->_view($source);
    }


    public function create_order()
    {
        $this->ACL->turn(array($this->module, 'buy_product'));

        $this->Model->bindModel('category');
        $basket = &$this->storage['basket'];
        $fields = Validate::getAndMergeFormPost($this->Register['action'], array(), true);
        $errors = null;


        if (!$basket['products']) {
            $errors .= $this->Register['Validate']->completeErrorMessage(__('You have to select at least one product', $this->module));

        } else {
            // Check if something was changed in the selected products
            $_total = 0;
            foreach ($basket['products'] as $row) {
                $product = $this->Model->getById($row['id']);

                if (!$product || $product->getQuantity() < $row['quantity']) {
                    $errors .= $this->Register['Validate']
                        ->completeErrorMessage(sprintf(__('Not enough quantity of "%s"', $this->module), h($row['title'])));
                    continue;
                }

                if ($product->getAvailable() == 0 || !$this->ACL->checkCategoryAccess($product->getCategory()->getNo_access()))
                    $errors .= $this->Register['Validate']
                        ->completeErrorMessage(sprintf(__('"%s" have been disabled for selling', $this->module), h($row['title'])));

                $_total += $product->getFinal_price() * $row['quantity'];
            }

            if (!errors && $_total != $basket['total']) {
                $errors .= $this->Register['Validate']
                    ->completeErrorMessage(__('Total price does not match. Some products might change the price.', $this->module));
            }
        }

        if (!empty($errors))
            $errors = $this->Register['DocParser']->wrapErrors($errors);
        else
            $errors = $this->Register['Validate']->check($this->Register['action']);


        if ($errors) {
            $_SESSION['FpsForm'] = $fields;
            $_SESSION['FpsForm']['errors'] = $errors;
            redirect($this->getModuleURL('create_order_form'));
        }


        $ordersEntity = $this->Register['ModManager']->getEntityInstance('shopOrders');
        $ordersProductsEntity = $this->Register['ModManager']->getEntityInstance('shopOrdersProducts');

        try {
            $order_data = array(
                'user_id' => ($_SESSION['user']) ? $_SESSION['user']['id'] : 0,
                'date' => new Expr('NOW()'),
                'total' => $basket['total'],
                'comment' => $fields['comment'],
                'delivery_address' => $fields['address'],
                'delivery_type_id' => $fields['delivery_type'],
                'telephone' => $fields['telephone'],
                'first_name' => trim(strstr($fields['name'], ' ', true)),
                'last_name' => trim(strstr($fields['name'], ' ')),
            );
            $order_id = $ordersEntity($order_data)->save();

            foreach ($basket['products'] as $row) {
                $ordersProductsEntity(array(
                    'order_id' => $order_id,
                    'product_id' => $row['id'],
                    'quantity' => $row['quantity'],
                ))->save();
            }

            // Clear basket
            $basket['total'] = 0;
            $basket['products'] = array();

            // for check access to complete_order page.
            $_SESSION['complete_order_id'] = $order_id;

            if ($this->Log) $this->Log->write('add order(' . $this->module . ')', 'id(' . $order_id . ')');
            redirect($this->getModuleURL('complete_order/' . $order_id));


        } catch (Exception $e) {
            $_SESSION['FpsForm'] = $fields;
            $_SESSION['FpsForm']['errors'] = $e->getMessage();
            redirect($this->getModuleURL('create_order_form'));
        }
    }


    public function complete_order($id = null)
    {
        $this->ACL->turn(array($this->module, 'buy_product'));
        $id = intval($id);
        if (empty($id) || $id < 1) redirect($this->getModuleURL());

        // Check the user access for this order
        if (empty($_SESSION['complete_order_id']) || $_SESSION['complete_order_id'] !== $id) {
            $this->showInfoMessage(__('Permission denied'), $this->getModuleURL());
        }

        $ordersModel = $this->Register['ModManager']->getModelInstance('shopOrders');
        $order = $ordersModel->getById($id);

        if (!$order)
            $this->showInfoMessage(__('Order not found', $this->module), $this->getModuleURL());


        $source = $this->render('complete_order.html', array('context' => array(
            'order' => $order,
        )));
        return $this->_view($source);
    }

	
	/**
	* add comment to entity
	*
	* @param $id (int)    entity ID
	* @return      info message
	*/
	public function add_comment($id = null)
    {
		include_once(ROOT . '/sys/inc/includes/add_comment.php');
	}
	
	
	/**
	* add comment form to entity
	*
	* @param $id (int)    entity ID
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
	* @param $id (int)    comment ID
	* @return      html form
	*/
	public function edit_comment_form($id = null)
    {
		include_once(ROOT . '/sys/inc/includes/edit_comment_form.php');
	}
	
	
	/**
	* update comment
	*
	* @param $id (int)    comment ID
	* @return      info message
	*/
	public function update_comment($id = null)
    {
		include_once(ROOT . '/sys/inc/includes/update_comment.php');
	}

	
	/**
	* get comments for entity
	*
	* @param $id (int)    entity ID
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
	* @param $id (int)    comment ID
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
     * RSS for news
	 *
     */
    public function rss() {
		include_once ROOT . '/sys/inc/includes/rss.php';
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
			'errors' => $this->Register['DocParser']->wrapErrors($errors),
			'result' => '0'
		));
		
		$attaches = downloadAtomAttaches($this->module);

        if ($attaches) {
            $last_ids = '';
            foreach ($attaches as $k => $attach)
                $last_ids += ($k > 0 ? ', ' : '') . $attach['id'];
            if ($this->Log) $this->Log->write('add attach[s](' . $this->module . ')', 'id[s](' . $last_ids . ')');
        }

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
				'errors' => $this->Register['DocParser']->wrapErrors($errors),
			));
		}
			
		if ($attach) {
			$filename = $attach->getFilename();
			if (!empty($filename) && file_exists(ROOT . '/sys/files/' . $this->module . '/' . $filename)) {
				_unlink(ROOT . '/sys/files/' . $this->module . '/' . $filename);
			}

            if ($attach->getIs_main() == 1) {
                $new_main = $attachModel->getFirst(array(
                    'id != ' + $id,
                    'entity_id' => $attach->getEntity_id(),
                ));
                if ($new_main)
                    $new_main->setIs_main('1')->save();
            }

			$attach->delete();

            if ($this->Log) $this->Log->write('delete attach(' . $this->module . ')', 'id(' . $id . ')');
		}
		$this->showAjaxResponse(array('result' => '1'));
	}
	
	
	public function as_main_attach($id)
	{
		$this->counter = false;
		if (!$this->ACL->turn(array($this->module, 'edit_products'), false)
		|| empty($_SESSION['user']['id'])) {
			$this->showAjaxResponse(array(
				'errors' => __('Permission denied'), 
				'result' => '0'
			));
		}

		
		$attachModel = $this->Register['ModManager']->getModelInstance($this->module . 'Attaches');
		$attach = $attachModel->getById(intval($id));
		
		$errors = array();
		
		if (!$attach) 
			$errors[] = __('Record not found');
			
		if (!empty($errors)) {
			$this->showAjaxResponse(array(
				'result' => '0', 
				'errors' => $errors,
			));
		}

        $main_attach = $attachModel->getCollection(array(
            'entity_id' => $attach->getEntity_id(),
            'is_main' => '1',
        ));
        if ($main_attach) {
            foreach ($main_attach as $row) {
                $row->setIs_main('0')->save();
            }
        }


        $attach->setIs_main('1')->save();

		$this->showAjaxResponse(array('result' => '1'));
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

        return '';
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

        $this->storage =& $_SESSION;
        if (!array_key_exists('basket', $this->storage))
            $this->storage['basket'] = array('products' => array(), 'total' => 0);

		parent::_beforeRender();
	}
	
	
	protected function _getValidateRules() 
	{
		$max_attach = Config::read('max_attaches', $this->module);
		if (empty($max_attach) || !is_numeric($max_attach)) $max_attach = 5;
		$rules = array(
			'create_order_form' => array(
				'name' => array(
					'required' => true,
					'max_lenght' => 50,
					'title' => 'Name',
				),
				'telephone' => array(
					'required' => true,
					'max_lenght' => 20,
					'pattern' => Validate::V_INT,
					'title' => 'Telephone',
				),
				'delivery_type' => array(
					'required' => true,
					'title' => 'Delivery type',
				),
                'address' => array(
                    'required' => true,
                    'max_lenght' => 200,
                    'title' => 'Address',
                ),
				'comment' => array(
					'required' => 'editable',
                    'max_lenght' => 1000,
                    'title' => 'Comment',
				),
			),
			'add_comment' => array(
				'login' => array(
					'required' => true,
					'pattern' => Validate::V_TITLE,
					'max_lenght' => 40,
				),
				'message' => array(
					'required' => true,
				),
				'captcha_keystring' => array(
					'pattern' => Validate::V_CAPTCHA,
					'title' => 'Kaptcha',
				),
			),
			'update_comment' => array(
				'login' => array(
					'required' => true,
					'pattern' => Validate::V_TITLE,
					'max_lenght' => 40,
				),
				'message' => array(
					'required' => true,
				),
				'captcha_keystring' => array(
					'pattern' => Validate::V_CAPTCHA,
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
}

