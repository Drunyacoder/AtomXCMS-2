<?php
/*-----------------------------------------------\
| 												 |
|  @Author:       Andrey Brykin (Drunya)         |
|  @Version:      1.5.6                          |
|  @Project:      CMS                            |
|  @package       CMS Fapos                      |
|  @subpackege    Pages Module                   |
|  @copyright     ©Andrey Brykin 2010-2013       |
|  @last mod      2013/04/07                     |
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



Class PagesModule extends Module {

	/**
	* @module_title  title of module
	*/
	public $module_title = 'Страницы';
	/**
	* @template  layout for module
	*/
	public $template = 'pages';
	/**
	* @module module indentifier
	*/
	public $module = 'pages';


	
	/**
	* default action
	*/
	function index($id = null, $s =null, $x = null) {
	
		//$this->render('main.html', array()); die();
	
	
		//if isset ID - we need load page with this ID
		if (!empty($id)) {
			if (is_int($id)) {
				$id = (int)$id;
				if ($id < 2)  redirect('/pages/');
				
				$page = $this->Model->getById($id);
				
				
			} else {
				if (!preg_match('#^[\da-z_\-.]+$#i', $id))  redirect('/pages/');
			
				$page = $this->Model->getByUrl($id);
				if (empty($page)) return $this->showInfoMessage(__('Can not find this page'), '/');
			}
		
		
			
			
			$this->page_title = $page->getName();
			$this->page_meta_keywords = $page->getMeta_keywords();
			$this->page_meta_description = $page->getMeta_description();
			$this->template = ($page->getTemplate()) ? $page->getTemplate() : 'default';
			$source = $page->getContent();
			$source = $this->renderString($source, array('entity' => $page));
		
		
			// Tree line
			$navi['navigation'] = get_link(__('Home'), '/');
			$cnots = explode('.', $page->getPath());
			if (false !== ($res = array_search(1, $cnots))) unset($cnots[$res]);
			if (!empty($cnots)) {
				$ids = "'" . implode("', '", $cnots) . "'";
				$pages = $this->Model->getCollection(array(
					"`id` IN (" . $ids . ")"
				), array(
					'order' => 'path',
				));
				
				if (!empty($pages) && is_array($pages)) {
					foreach($pages as $p) {
						$navi['navigation'] .= __('Separator') . get_link(__($p->getName()), '/' . $p->getId());
					}
				}
			}
			$navi['navigation'] .= __('Separator') . h($page->getName());
			$this->_globalize($navi);
			
			return $this->_view($source);

			
		//may be need view latest materials	
		} else {
			$this->page_title = $this->Register['Config']->read('title');
			$latest_on_home = $this->Register['Config']->read('latest_on_home');
			$navi = null; //vsyakiy sluchay:)
			
			
			//if we want view latest materials on home page
			if (is_array($latest_on_home) && count($latest_on_home) > 0) {
			
				// Navigation Block
				$navi = array();
				$navi['add_link'] = ($this->ACL->turn(array('news', 'add_materials'), false)) 
					? get_link(__('Add material'), '/news/add_form') : '';
				$navi['navigation'] = get_link(__('Home'), '/');
				$this->_globalize($navi);
			
			
				if ($this->cached && $this->Cache->check($this->cacheKey)) {
					$html = $this->Cache->read($this->cacheKey);
					return $this->_view($html);
				}


				//create SQL query
                $entities = $this->Model->getEntitiesByHomePage($latest_on_home);

					
                //if we have records
                if (count($entities) > 0) {

                    // Get users(authors)
                    $uids = array();
                    $mod_mats = array('news' => array(), 'stat' => array(), 'loads' => array());
                    foreach ($entities as $key => $mat) {
                        $uids[] = $mat->getAuthor_id();
                        switch ($mat->getSkey()) {
                            case 'news':
                                $mod_mats['news'][$key] = $mat;
                                break;
                            case 'stat':
                                $mod_mats['stat'][$key] = $mat;
                                break;
                            case 'loads':
                                $mod_mats['loads'][$key] = $mat;
                                break;
                        }
                    }


                    $uids = '(' . implode(', ', $uids) . ')';
                    $uModelClassName = $this->Register['ModManager']->getModelNameFromModule('users');
                    $uModel = new $uModelClassName;
                    $authors = $uModel->getCollection(array('`id` IN ' . $uids));


                    // Merge records with additional fields
                    if (is_object($this->AddFields)) {
                        if (!empty($mod_mats['news']) && count($mod_mats['news']) > 0)
                            $mod_mats['news'] = $this->AddFields->mergeRecords($mod_mats['news'], false, 'news');
                        if (!empty($mod_mats['stat']) && count($mod_mats['stat']) > 0)
                            $mod_mats['stat'] = $this->AddFields->mergeRecords($mod_mats['stat'], false, 'stat');
                        if (!empty($mod_mats['loads']) && count($mod_mats['loads']) > 0)
                            $mod_mats['loads'] = $this->AddFields->mergeRecords($mod_mats['loads'], false, 'loads');
                    }


                    $all_attaches = array('news' => array(), 'stat' => array());
                    foreach ($mod_mats as $module => $mats) {
                        if (count($mats) > 0 && ($module == 'news' || $module == 'stat')) {
                            $attach_ids = array();
                            foreach ($mats as $mat) {
                                $attach_ids[] = $mat->getId();
                            }


                            $ids = implode(', ', $attach_ids);
                            $attModelClassName = $this->Register['ModManager']->getModelNameFromModule($module . 'Attaches');
                            $attModel = new  $attModelClassName;
                            $attaches = $attModel->getCollection(array('`entity_id` IN ('.$ids.')'));
							
                            foreach ($mats as $mat) {
                                if ($attaches) {
                                    foreach ($attaches as $attach) {
                                        if ($mat->getId() == $attach->getEntity_id()) {
                                            $currAttaches = $mat->getAttaches();
                                            if (is_array($currAttaches)) {
                                                $currAttaches[] = $attach;
                                            } else {
                                                $currAttaches = array($attach);
                                            }

                                            $mat->setAttaches($currAttaches);
                                        }
                                    }
                                }
                            }
                        }
                    }


                    $entities = $mod_mats['news'] + $mod_mats['stat'] + $mod_mats['loads'];
                    ksort($entities);


                    //if we have materials for view on home page (now we get their an create page)
                    $info = null;
                    foreach ($entities as $result) {
                        foreach ($authors as $author) {
                            if ($result->getAuthor_id() == $author->getId()) {
                                $result->setAuthor($author);
                                break;
                            }
                        }


                        // Create and replace markers
                        $markers = array();
                        $this->Register['current_vars'] = $result;

                        //moder panel
                        $markers['moder_panel'] = $this->_getAdminBar($result->getId(), $result->getSkey());
                        $entry_url = get_url(entryUrl($result, $result->getSkey()));
                        $markers['entry_url'] = $entry_url;


                        $matattaches = ($result->getAttaches() && count($result->getAttaches()))
                        ? $result->getAttaches() : array();
                        $announce = $result->getMain();

						
                        $announce = $this->Textarier->getAnnounce($announce, $entry_url, 0,
                            $this->Register['Config']->read('announce_lenght'), $result);
						
						
                        if (count($matattaches) > 0) {
                            $attachDir = ROOT . '/sys/files/' . $result->getSkey() . '/';
                            foreach ($matattaches as $attach) {
							
							
                                if ($attach->getIs_image() == 1 && file_exists($attachDir . $attach->getFilename())) {
									$announce = $this->insertImageAttach(
										$announce, 
										$attach->getFilename(), 
										$attach->getAttach_number(),
										$result->getSkey()
									);
                                }
                            }
                        }
						
                        $markers['announce'] = $announce;

						$markers['profile_url'] = get_url(getProfileUrl($result->getAuthor_id()));

                        $markers['module_title'] = $this->Register['Config']->read('title', $result->getSkey());
                        $result->setAdd_markers($markers);


                        //set users_id that are on this page
                        $this->setCacheTag(array(
                            'module_' . $result->getSkey(),
                            'record_id_' . $result->getId(),
                        ));
                    }

                    $html = $this->render('list.html', array('entities' => $entities));


                    //write int cache
                    if ($this->cached)
                        $this->Cache->write($html, $this->cacheKey, $this->cacheTags);
                }

	
				if (empty($html)) $html = __('Materials not found');
				return $this->_view($html);
			}
			return $this->_view(__('Materials not found'));
		}
	}


	
	
	/**
	* @param int $id - record ID
	* @param string $module - module
	* @return string - admin buttons
	*
	* create and return admin bar
	*/
	protected function _getAdminBar($id, $module) {
		$moder_panel = '';
		if ($this->ACL->turn(array($module, 'edit_materials'), false)) {
			$moder_panel .= get_link('', '/' . $module . '/edit_form/' . $id, array('class' => 'fps-edit')) . '&nbsp;';
		}
		if ($this->ACL->turn(array($module, 'up_materials'), false)) {
			$moder_panel .= get_link('', '/' . $module . '/upper/' . $id, array(
				'class' => 'fps-up',
				'onClick' => "return confirm('" . __('Are you sure') . "')",
			)) . '&nbsp;';
		}
		if ($this->ACL->turn(array($module, 'on_home'), false)) {
			$moder_panel .= get_link('', '/' . $module . '/off_home/' . $id, array(
				'class' => 'fps-on',
				'onClick' => "return confirm('" . __('Are you sure') . "')",
			)) . '&nbsp;';
		}
		if ($this->ACL->turn(array($module, 'delete_materials'), false)) {
			$moder_panel .= get_link('', '/' . $module . '/delete/' . $id, array(
				'class' => 'fps-delete',
				'onClick' => "return confirm('" . __('Are you sure') . "')",
			)) . '&nbsp;';
		}

		return $moder_panel;
	}

	
}
