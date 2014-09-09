<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Email:        drunyacoder@gmail.com         |
| @Site:         http://atomx.net              |
| @Version:      1.0.0                         |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @Subpackege    Shop Module                   |
| @Copyright     ©Andrey Brykin                |
| @Last mod      2014/08/25                    |
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


class ShopSettingsController
{

    public $module = 'shop';

    /**
     * Page title
     * @var string
     */
    public $pageTitle;

    /**
     * The left side bread crumbs
     * @var string
     */
    public $pageNav;

    /**
     * The right side bread crumbs
     * @var string
     */
    public $pageNavr;

    public $currentUrl;
	
	public $allowed_filetypes = array('image/jpeg', 'image/png', 'image/gif');


    public function __construct()
    {
        $this->currentUrl = $_SERVER['REQUEST_URI'];
        $Register = Register::getInstance();
        $Register['Validate']->setRules($this->_getValidateRules());
    }


    public function getCurrentUrl($filter = false)
    {
        $url = $this->currentUrl;
        if (!empty($filter)) {
            if (is_string($filter)) {
                $url = preg_replace('#(\??' . preg_quote($filter) . '=[^&]*[&]?)#i', '', $url);
            } else if (is_array($filter)) {
                foreach ($filter as $key) {
                    $url = preg_replace('#(\??' . preg_quote($key) . '=[^&]*[&]?)#i', '', $url);
                }
            }
        }
        return $url;
    }


    public function getUrl($url = false)
    {
        return get_url($url = '/admin/' . $this->module . '/' . $url);
    }


    public function catalog()
    {
        $Register = Register::getInstance();
        $this->pageTitle = __('Shop') . ' / ' . __('Catalog management');
        $this->pageNav = __('Shop') . ' / ' . __('Catalog management');
        $content = '';
        $productsModel = $Register['ModManager']->getModelInstance('shopProducts');


        $total = $productsModel->getTotal();
        list ($pages, $page) = pagination($total, Config::read('per_page', $this->module), $this->getCurrentUrl('page'));
        $filters = $this->__getProductsFilters();

        $where = array();
        if (!empty($_GET['filters']) && is_array($_GET['filters'])) {
            foreach ($_GET['filters'] as $k => $v) {
                $where[$k] = $v;
            }
        }


        $productsModel->bindModel('attributes_group.attributes.content');
        $productsModel->bindModel('vendor');
        $productsModel->bindModel('category');
        $params = array(
            'page' => $page,
            'limit' => Config::read('per_page', $this->module),
            'order' => $productsModel->getOrderParam(),
        );
        $entities = $productsModel->getCollection($where, $params);
		

        $pages = '<div class="pages">' . $pages . '</div>';
        $content .= "<div class=\"list\">
			<div class=\"title\">{$pages}</div>
			<div class=\"add-cat-butt\" onClick=\"window.location.href='" . $this->getUrl('edit_product') . "'\">
			<div class=\"add\"></div>" . __('Add product') . "</div>
			<table cellspacing=\"0\" class=\"grid\"><tr>
			<th width=\"\">" . getOrderLink(array('title', __('Title'))) . "</th>
			<th width=\"15%\">" . getOrderLink(array('category.title', __('Category'))) . "</th>
			<th width=\"15%\">" . getOrderLink(array('date', __('Date'))) . "</th>
			<th width=\"15%\">" . getOrderLink(array('vendor.title', __('Vendor'))) . "</th>
			<th width=\"5%\">" . getOrderLink(array('orders_cnt', __('Orders'))) . "</th>
			<th width=\"5%\">" . getOrderLink(array('comments_cnt', __('Comments'))) . "</th>
			<th width=\"5%\">" . getOrderLink(array('price', __('Price'))) . "</th>
			<th width=\"5%\">" . getOrderLink(array('discount', __('Discount'))) . "</th>
			<th width=\"110px\" colspan=\"\">" . __('Action') . "</th></tr>";


        foreach ($entities as $entity) {
            /*
            $status_info = $Register['ACL']->get_user_group($entity->getAuthor()->getStatus());
            $status = $status_info['title'];
            $color = (!empty($status_info['color'])) ? $status_info['color'] : '';
            */
            $content .= "<tr>
                        <td>" . h($entity->getTitle()) . "</td>
						<td>" . h($entity->getCategory()->getTitle()) . "</td>
						<td>" . h($entity->getDate()) . "</td>
						<td>" . h($entity->getVendor()->getTitle()) . "</td>
						<td>" . h($entity->getOrders_cnt()) . "</td>
						<td>" . h($entity->getComments_cnt()) . "</td>
						<td style=\"white-space:nowrap;\">" . number_format($entity->getPrice(), 2, ',', ' ') 
							. (($entity->getFinal_price() != $entity->getPrice()) 
							? '<span class="top-index red">' . number_format($entity->getFinal_price(), 2, ',', ' ') . '</span>' : '') 
							. "</td>
						<td>" . h($entity->getDiscount()) . "</td>
						<td colspan=\"\">
						<a class=\"edit\" title=\"" . __('Edit') . "\" href='" . $this->getUrl('edit_product/' . $entity->getId()) . "'></a>
						<a class=\"delete\" title=\"" . __('Delete') . "\" href='" . $this->getUrl('delete_product/' . $entity->getId()) . "'></a>
						<a class=\"statistics\" title=\"" . __('Statistics') . "\" href='" . $this->getUrl('statistics/product/' . $entity->getId()) . "'></a>
						</td>
						</tr>";
        }
        $content .= '</table></div>';

        return $filters . $content . $filters;
    }


    public function edit_product($id = null)
    {
        $id = intval($id);
        $Register = Register::getInstance();
        if (!$Register['ACL']->turn(array($this->module, 'edit_products'), false)) {
            $_SESSION['errors'] = __('Permission denied');
            redirect($this->getUrl('catalog'));
        }

        $content = '';
		$fields = '';
        $this->pageTitle = __('Shop') . ' / ' . __('Editing product');
        $this->pageNav = __('Shop') . ' / ' . __('Editing product');
        $this->pageNavr = __('Editing product') . ' | [<a href="' . $this->getUrl('catalog') . '">' . __('Catalog') . '</a>]';

        $categoriesModel = $Register['ModManager']->getModelInstance('shopCategories');
        $vendorsModel = $Register['ModManager']->getModelInstance('shopVendors');
        $attrsGroupsModel = $Register['ModManager']->getModelInstance('shopAttributesGroups');
        $productsModel = $Register['ModManager']->getModelInstance('shopProducts');
        $productsModel->bindModel('attributes.content');
        $productsModel->bindModel('attaches');
        
		if ($id) {
			$entity = $productsModel->getById($id);
			if (!$entity) {
				$_SESSION['errors'] = __('Record not found');
				redirect($this->getUrl('catalog'));
			}
		} else {
			$entity = $Register['ModManager']->getEntityInstance('shopProducts');
		}
		
		
		// Change attributes group
		if (!empty($_GET['attr_group_id'])) {
			$curr_group_id = $entity->getAttributes_group_id();
			if (empty($curr_group_id) || $curr_group_id != intval($_GET['attr_group_id'])) {
				$attrsModel = $Register['ModManager']->getModelInstance('shopAttributes');
				$attrs = $attrsModel->getCollection(array('group_id' => intval($_GET['attr_group_id'])));
				if ($id) {
					$attrsModel->delete(array('product_id' => $id));
					$entity->setAttributes_group_id(intval($_GET['attr_group_id']));
					$entity->save();
					$entity = $productsModel->getById($id);
				} else {
					$entity->setAttributes_group_id(intval($_GET['attr_group_id']));
					$entity->setAttributes($attrs);
				}
			}
		}


		if (!empty($_POST)) {
            $errors = $Register['Validate']->check(__FUNCTION__);
            if (!empty($errors)) {
                $_SESSION['errors'] = $Register['Validate']->wrapErrors($errors);
                redirect($this->getUrl('edit_product/' . $id));
            }

			
			if ($entity->getAttributes()) {
				foreach ($entity->getAttributes() as $attr_) {
                    if ($attr_->getType() === 'image') {
                        if (!empty($_FILES['attributes_' . $attr_->getTitle()])) {
                            $attr_->getContent()->setContent($_FILES['attributes' . $attr_->getTitle()]);
                        }
                    } else {
                        $attr_->getContent()->setContent(@$_POST['attributes'][$attr_->getTitle()]);
                    }
				}
			}
		
            if ($entity->getTitle() != $_POST['title'])
                $entity->setTitle($_POST['title']);
            if ($entity->getDescription() != $_POST['description'])
                $entity->setDescription($_POST['description']);
            if ($entity->getArticle() != $_POST['article'])
                $entity->setArticle($_POST['article']);
            if ($entity->getPrice() != $_POST['price'])
                $entity->setPrice($_POST['price']);
            if ($entity->getDiscount() != $_POST['discount'])
                $entity->setDiscount($_POST['discount']);
			if ($entity->getCategory_id() != $_POST['category_id'])
				$entity->setCategory_id($_POST['category_id']);
			if ($entity->getVendor_id() != $_POST['vendor_id'])
				$entity->setVendor_id($_POST['vendor_id']);
			if ($entity->getAttributes_group_id() != $_POST['attributes_group_id'])
				$entity->setAttributes_group_id($_POST['attributes_group_id']);
			if (isset($_POST['quantity'])) 
				$entity->setQuantity(intval($_POST['quantity']));
			$entity->setCommented((!empty($_POST['commented']) ? '1' : '0'));
			$entity->setAvailable((!empty($_POST['available']) ? '1' : '0'));
			$entity->setView_on_home((!empty($_POST['view_on_home']) ? '1' : '0'));
			$entity->setHide_not_exists((!empty($_POST['hide_not_exists']) ? '1' : '0'));
			
			if ($entity->save(true)) {
				$_SESSION['message'] = __('Operation is successful');
				redirect($this->getUrl('edit_product/' . $entity->getId()));
			}
			$_SESSION['errors'] = __('Some error occurred');
			redirect($this->getCurrentUrl());
		}

		
        // product attributes
		$fields .= '<input type="hidden" name="attributes[]" value="" />';
        if ($entity->getAttributes()) {
            foreach ($entity->getAttributes() as $attr) {
                $attr_content = ($attr->getContent())
                    ? $attr->getContent()->getContent()
                    : '';
                $fields .= '<div class="setting-item highlight">
                            <div class="left">
                            ' . h($attr->getLabel()) . '
                            </div>
                            <div class="right">'
                                . $attr->getInputField()
                            . '</div>
                            <div class="clear"></div>
                        </div>';
            }
        }
		
        // product image
		$attaches = array();
		$main_image = '';
		$slider_images = '';
		foreach ($attaches as $attach) {
			//if ()
		}
		$fields .= '<div class="setting-item">
						<div class="left">
						' . __('Image') . '
						</div>
						<div class="right">
							<div id="main-image" class="main-image"></div>
							<div class="upload-area">
								<div class="drag-area">' . __('Add') . '</div>
								<input id="attach" multiple="multiple" type="file" name="attach[]" />
							</div>
							<div class="clear"></div>
							<div class="products-images"><div class="viewport" id="attaches-list"></div></div>
						</div>
						<div class="clear"></div>
					</div>';

        // product fields
        $attrs = array('title', 'description', 'article', 'price', 'discount', 'commented', 'available', 'view_on_home', 'hide_not_exists', 'quantity');
        $checkboxes = array('commented', 'available', 'view_on_home', 'hide_not_exists');
        foreach ($attrs as $attr) {
            $getter = 'get' . ucfirst($attr);
            if (in_array($attr, $checkboxes)) {
                $fid = 'checkbox-' . $attr;
                $input = '<input id="' . $fid . '" type="checkbox" name="' . $attr . '" value="1"'
                    . (($entity->$getter()) ? 'checked="checked"' : '') . ' />'
                    . '<label for="' . $fid . '"></label>';
            } else {
                $input = '<input type="text" name="' . $attr . '" value="' . h($entity->$getter()) . '" />';
            }
            $fields .= '<div class="setting-item">
                            <div class="left">
                            ' . __(ucfirst($attr), 'shop') . '
                            </div>
                            <div class="right">
                                ' . $input . '
                            </div>
                            <div class="clear"></div>
                        </div>';
        }

        // product fields related to foreign table (select)
        $foreign_models = array(
            'category_id' => array(
                'label' => __('Category'),
                'model' => $categoriesModel,
            ),
            'vendor_id' => array(
                'label' => __('Vendor'),
                'model' => $vendorsModel,
            ),
            'attributes_group_id' => array(
                'label' => __('Attributes group'),
                'model' => $attrsGroupsModel,
            ),
        );
        foreach ($foreign_models as $field => $params) {
			$getter = 'get' . ucfirst($field);
            $foreign_entities = $params['model']->getCollection();
            $fields .= '<div class="setting-item">
                                <div class="left">
                                ' . $params['label'] . '
                                </div>
                                <div class="right"><select name="' . $field . '">';

			$is_selected = false;
            if ($foreign_entities) {
                foreach ($foreign_entities as $fentity) {
					$change_attrs_group = false;
					if ($field === 'attributes_group_id' &&
						!empty($_GET['attr_group_id']) &&
						$fentity->getId() == intval($_GET['attr_group_id'])) $change_attrs_group = true;
						
                    if ($entity->$getter() === $fentity->getId() || $change_attrs_group) {
						$selected = ' selected="selected"';
						$is_selected = true;
					} else {
						$selected = '';
					}
					
                    $fields .= '<option' . $selected . ' value="' . $fentity->getId()
                        . '">' . h($fentity->getTitle()) . '</option>';
                }
            }
			
			if (!$entity->$getter() && !$is_selected) {
				$fields .= '<option selected="selected" value=""> </option>';
			}
			
            $fields .= '</select></div>
                            <div class="clear"></div>
                        </div>';
        }


		$content .= '<script type="text/javascript">
			$(function(){
				$(\'select[name="attributes_group_id"]\').change(function(e){
					window.location.href = "' . $this->getCurrentUrl(array('attr_group_id')) . '?attr_group_id="+$(e.target).val();
				});
				
				var parseResponse = function(module, data) {
					var $mainImage = $("#main-image"),
						main;
				
					if (typeof data.errors != "undefined" && data.errors.length > 0) {
						showHelpWin(data.errors, "' . __('Error') . '");
						return;
					}
					
					if (typeof data == "undefined" || data.length == 0 || data == false) {
						showHelpWin("Files not found", "Information");
						return;
					}
					
					var getTitle = function(data) {
						return data.filename;
					}
				
					console.log($mainImage.html());
					if ($mainImage.html() == \'\') {
						$(data).each(function(key, value){
							if (value.is_main == 1) main = value;	
						});
					}
					$(data).each(function(key, value) {
						if ($mainImage.html() == \'\') {
							if ((main && main.id == value.id) || key == 0) {
								$("#main-image").html(\'<img data-id="\'+value.id+\'" title="\'+getTitle(value)+\'" src="/image/shop/\'+value.filename+\'/150/" />\');
							}
						}
						
						var content = \'<div class="attach-item">\'
							+ \'<img title="\'+getTitle(value)
							+\'" src="/image/shop/\'+value.filename+\'/100/" />\'
							+\'<input type="hidden" name="attaches[]" value="\'+value.id+\'" />\'
							+\'<div class="atmshop-del-attach">' . __('Delete') . '</div><div class="atmshop-asmain-attach">' . __('As main') . '</div>\'
							+\'</div>\';
						$("#attaches-list").append(content);
					});
					if (!$("#attaches-list").parent().is(":visible")) {
						$("#attaches-list").parent().slideDown();
					}	
					$("#attaches-list").width($("#attaches-list img").length * 110 + "px" );
				};
				AtomX.initMultiFileUploadHandler("shop", parseResponse);
				$(".atmshop-del-attach").live("click", function(){
					var attachId = $(this).parent().find("input").val();
					$.get("' . get_url('/shop/delete_attach/') . '\'+ attachId +\'/", function(data){
						if (data.result != 1) return; 
						if ($("#main-image img").data("id") == attachId) {
							$("#main-image img").attr("src", "/image/shop/"+$("#attaches-list div:eq(0) input").val()+"/150/");
						}
						$(this).parent().remove();
						$("#attaches-list").width($("#attaches-list img").length * 110 + "px" );
					});
				});
				$(".atmshop-asmain-attach").live("click", function(){
					var attachId = $(this).parent().find("input").val();
					$.get("' . get_url('/shop/as_main_attach/') . '\'+ attachId +\'/", function(data){
						if (data.result != 1) return; 
						$("#main-image img").attr("src", "/image/shop/"+attachId+"/150/");
					});
				});
			});
			</script>';
        $content .= '<div class="warning">' . __('Highlighted rows are related to the attributes group.') . '</div>';
        $content .= '<form method="POST" action="' . $this->getCurrentUrl() . '" enctype="multipart/form-data">
            <div class="list">
                <div class="title">' . $this->pageNav . '</div>
                <div class="level1">
                    <div class="items">
                        ' . $fields . '
                        <div class="setting-item">
                            <div class="left">
                            </div>
                            <div class="right">
                                <input class="save-button" type="submit" name="send" value="' . __('Save') . '" />
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
            </div>
            </form>';

        return $content;
    }


    public function delete_product($id)
    {
        $id = intval($id);
        $Register = Register::getInstance();
        if (!$Register['ACL']->turn(array($this->module, 'delete_products'), false)) {
            $_SESSION['errors'] = __('Permission denied');
            redirect($this->getUrl('catalog'));
        }

        $productsModel = $Register['ModManager']->getModelInstance('shopProducts');
        $productsModel->bindModel('attributes.content');
        $entity = $productsModel->getById($id);
        if (!$entity) {
            $_SESSION['errors'] = __('Record not found');
            redirect($this->getUrl('catalog'));
        }

        if ($entity->getAttributes()) {
            foreach ($entity->getAttributes() as $attr) {
                $attr->getContent()->delete();
                $attr->delete();
            }
        }
        $entity->delete();

        $_SESSION['message'] = __('Operation is successful');
        redirect($this->getUrl('catalog'));
    }


    public function categories()
    {
        $popups = '';
        $content = '';
        $Register = Register::getInstance();
        $categoriesModel = $Register['ModManager']->getModelInstance('shopCategories');
        $acl_groups = $Register['ACL']->get_group_info();

        $this->pageTitle = __('Shop') . ' / ' . __('Categories management');
        $this->pageNav = __('Shop') . ' / ' . __('Categories management');


        $all_categories = $categoriesModel->getCollection(array(), array(
            'fields' => array(
                'a.*',
                "(SELECT COUNT(*) FROM " . $Register['DB']->getFullTableName('shop_products') . " WHERE category_id = a.id) as cnt",
            ),
            'alias' => 'a',
        ));

        $cat_selector = '<select name="id_sec" id="cat_secId">';
        $cat_selector .= '<option value="0">&nbsp;</option>';
        foreach ($all_categories as $result) {
            $cat_selector .= '<option value="' . $result->getId() . '">' . h($result->getTitle()) . '</option>';
        }
        $cat_selector .= '</select>';


        $cats_tree = $this->getTreeNode($all_categories);
		$content .= $this->buildCatsList($cats_tree, $all_categories);
        $popups .='<div id="addCat" class="popup">
			<div class="top">
				<div class="title">' . __('Adding category') . '</div>
				<div onClick="closePopup(\'addCat\');" class="close"></div>
			</div>
			<form action="' . $this->getUrl('category_save') . '" method="POST">
			<div class="items">
				<div class="item">
					<div class="left">
						' . __('Parent section') . ':
					</div>
					<div class="right">' . $cat_selector . '</div>
					<div class="clear"></div>
				</div>
				<div class="item">
					<div class="left">
						' . __('Title') . ':
					</div>
					<div class="right">
						<input type="hidden" name="type" value="cat" />
						<input type="text" name="title" /></div>
					<div class="clear"></div>
				</div>
				<div class="item">
					<div class="left">
						' . __('Discount') . ' (%):
					</div>
					<div class="right">
						<input type="text" name="discount" /></div>
					<div class="clear"></div>
				</div>
				<div class="item">
					<div class="left">
						' . __('Access for') . ':
					</div>
					<div class="right">
						<table class="checkbox-collection"><tr>';
        $n = 0;
        $inp_id = md5(rand(0, 99999) . $n);
        if ($acl_groups && is_array($acl_groups)) {
            foreach ($acl_groups as $id => $group) {
                if (($n % 3) == 0) $popups .= '</tr><tr>';
                $popups .= '<td><input id="' . $inp_id . '" type="checkbox" name="access[' . $id . ']" value="' . $id
                    . '"  checked="checked" /><label for="' . $inp_id . '">' . h($group['title']) . '</label></td>';
                $n++;
            }
        }
        $popups .= '</tr></table>
					</div>
					<div class="clear"></div>
				</div>

				<div class="item submit">
					<div class="left"></div>
					<div class="right" style="float:left;">
						<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
					</div>
					<div class="clear"></div>
				</div>
			</div>
			</form>
		</div>';

        return $popups . $content;
    }


    public function category_save($id = null)
    {
        $id = intval($id);
        $Register = Register::getInstance();
        $acl_groups = $Register['ACL']->get_group_info();
        $model = $Register['ModManager']->getModelInstance($this->module . 'Categories');

        $errors = $Register['Validate']->check(__FUNCTION__);
        if (!empty($errors)) {
            $_SESSION['errors'] = $Register['Validate']->wrapErrors($errors);
            redirect($this->getUrl('categories'));
        }


        // edit or create
        if (!empty($id)) {
            $entity = $model->getById($id);
            if (!$entity) {
                $_SESSION['errors'] = $Register['Validate']->wrapErrors(__('Record not found'), true);
                redirect($this->getUrl('categories'));
            }
        } else {
            $entity = $Register['ModManager']->getEntityInstance('shopCategories');
        }

        // check parent category
        $target_section = $model->getById(intval($_POST['parent_id']));
        if (!$target_section) {
            $_SESSION['errors'] = $Register['Validate']->wrapErrors(__('Parent section not found'), true);
            redirect($this->getUrl('categories'));
        }
        $path = $target_section->getPath();
        $path = (!empty($path))
            ? $path . intval($_POST['parent_id']) . '.'
            : intval($_POST['parent_id']) . '.';
        $entity->setParent_id(intval($_POST['parent_id']));
        $entity->setPath($path);


        $no_access = array();
        if ($acl_groups && is_array($acl_groups)) {
            foreach ($acl_groups as $gid => $group) {
                if (!array_key_exists($gid, $_POST['access'])) {
                    $no_access[] = $gid;
                }
            }
        }
        $no_access = (count($no_access)) ? implode(',', $no_access) : '';
        if ($no_access !== '') $no_access = New Expr($no_access);


        $entity->setTitle(trim($_POST['title']));
        $entity->setNo_access($no_access);
        $entity->setDiscount((!empty($_POST['discount']) ? intval($_POST['discount']) : 0));

        if ($entity->save()) {
            $_SESSION['message'] = __('Operation is successful');
            redirect($this->getUrl('categories'));
        }
        $_SESSION['errors'] = __('Some error occurred');
        redirect($this->getUrl('categories'));
    }

	
    public function category_delete($id = null)
    {
        $id = intval($id);
        $Register = Register::getInstance();
        if (!$Register['ACL']->turn(array($this->module, 'categories_management'), false)) {
            $_SESSION['errors'] = __('Permission denied');
            redirect($this->getUrl('categories'));
        }

        $model = $Register['ModManager']->getModelInstance('shopCategories');
        $entity = $model->getById($id);
        if (!$entity) {
            $_SESSION['errors'] = __('Record not found');
            redirect($this->getUrl('categories'));
        }

		$total = $model->getTotal();
		if ($total <= 1) {
			$_SESSION['errors'] = $Register['Validate']->wrapErrors(__('You can\'t remove the last category'), true);
			redirect($this->getUrl('categories'));
		}
		
		
		$deleteCategory = function($catObj) use ($Register) {
			$model = $Register['ModManager']->getModelInstance('shopProducts');
			$products = $model->getCollection(array('category_id' => $catObj->getId()));
			if ($products) {
				foreach ($products as $r) {
					$r->delete();
				}
			}
			$catObj->delete();
		};
		
		$childrens = $model->getCollection(array("CONCAT('.', path) LIKE '%.{$id}.%'"));
		if ($childrens) {
			foreach ($childrens as $row) {
				$deleteCategory($row);
			}
		}

        $deleteCategory($entity);

        $_SESSION['message'] = __('Operation is successful');
        redirect($this->getUrl('categories'));
    }

	
    public function category_on_home($id = null, $flag = 1)
    {
        $id = intval($id);
		$flag = ($flag) ? '1' : '0';
        $Register = Register::getInstance();
        if (!$Register['ACL']->turn(array($this->module, 'categories_management'), false)) {
            $_SESSION['errors'] = __('Permission denied');
            redirect($this->getUrl('categories'));
        }

        $model = $Register['ModManager']->getModelInstance('shopCategories');
        $entity = $model->getById($id);
        if (!$entity) {
            $_SESSION['errors'] = __('Record not found');
            redirect($this->getUrl('categories'));
        }
		
		
		$setCategory = function($catObj) use ($Register, $flag) {
			$Register['DB']->save('shop_products', 
							array('view_on_home' => $flag), 
							array('category_id' => $catObj->getId()));
			$catObj->setView_on_home($flag);
			$catObj->save();
		};
		
		$childrens = $model->getCollection(array("CONCAT('.', path) LIKE '%.{$id}.%'"));
		if ($childrens) {
			foreach ($childrens as $row) {
				$setCategory($row);
			}
		}

        $setCategory($entity);

        $_SESSION['message'] = __('Operation is successful');
        redirect($this->getUrl('categories'));
    }
	

    public function attributes_groups()
    {
        $popups = '';
        $content = '';
        $Register = Register::getInstance();
        $model = $Register['ModManager']->getModelInstance('shopAttributesGroups');
        $this->pageTitle = __('Shop') . ' / ' . __('Attributes groups management');
        $this->pageNav = __('Shop') . ' / ' . __('Attributes groups management');
		
		
		if (!empty($_POST)) {
			if (!$Register['ACL']->turn(array($this->module, 'attributes_groups_management'), false)) {
				$_SESSION['errors'] = __('Permission denied');
				redirect($this->getUrl('attributes_groups'));
			}
			
			$errors = $Register['Validate']->check(__FUNCTION__);
            if (!empty($errors)) {
                $_SESSION['errors'] = $Register['Validate']->wrapErrors($errors);
                redirect($this->getUrl('attributes_groups'));
            }
			
			
			// edit or create
			$id = (!empty($_POST['group_id'])) ? intval($_POST['group_id']) : false;
			if (!empty($id)) {
				$entity = $model->getById($id);
				if (!$entity) {
					$_SESSION['errors'] = $Register['Validate']->wrapErrors(__('Record not found'), true);
					redirect($this->getUrl('attributes_groups'));
				}
			} else {
				$entity = $Register['ModManager']->getEntityInstance('shopAttributesGroups');
			}
			
			
			$entity->setTitle(trim($_POST['title']));
			if ($entity->save()) {
				$_SESSION['message'] = __('Operation is successful');
				redirect($this->getUrl('attributes_groups'));
			}
			$_SESSION['errors'] = __('Some error occurred');
			redirect($this->getUrl('attributes_groups'));
		}
		
		
		$popups .= '<div id="add_group" class="popup">
				<div class="top">
					<div class="title">' . __('Attributes group adding') . '</div>
					<div onClick="closePopup(\'add_group\');" class="close"></div>
				</div>
				<form action="' . $this->getUrl('attributes_groups') . '" method="POST">
				<div class="items">
					<div class="item">
						<div class="left">
							' . __('Title') . ':
						</div>
						<div class="right">
						<input type="text" name="title" value="" />
						<input type="hidden" name="group_id" value="" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="item submit">
						<div class="left"></div>
						<div class="right" style="float:left;">
							<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
						</div>
						<div class="clear"></div>
					</div>
				</div>
				</form>
			</div>';

        $attrs_groups = $model->getCollection();
        $content .= '<div class="list">
		<div class="title">' . __('Attributes groups') . '</div>
		<div class="add-cat-butt" onClick="openPopup(\'add_group\');">
		<div class="add"></div>' . __('Add attributes group') . '</div>
		<div class="level1">
			<div class="head">
				<div class="title">' . __('Title') . '</div>
				<div class="buttons">
				</div>
				<div class="clear"></div>
			</div>
			<div class="items">';
        if (count($attrs_groups) > 0) {
            foreach ($attrs_groups as $group) {
				$popups .= '<div id="' . $group->getId() . '_group" class="popup">
				<div class="top">
					<div class="title">' . __('Attributes group editing') . '</div>
					<div onClick="closePopup(\'' . $group->getId() . '_group\');" class="close"></div>
				</div>
				<form action="' . $this->getUrl('attributes_groups') . '" method="POST">
				<div class="items">
					<div class="item">
						<div class="left">
							' . __('Title') . ':
						</div>
						<div class="right">
						<input type="text" name="title" value="' . $group->getTitle() . '" />
						<input type="hidden" name="group_id" value="' . $group->getId() . '" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="item submit">
						<div class="left"></div>
						<div class="right" style="float:left;">
							<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
						</div>
						<div class="clear"></div>
					</div>
				</div>
				</form>
			</div>';
				
			
                $content .= '<div class="level2">
					<div class="number">' . $group->getId() . '</div>
					<div class="title">' . h($group->getTitle()) . '</div>
					<div class="buttons">';

                $content .= '<a href="javascript:void(0);" onClick="openPopup(\'' . $group->getId() . '_group\');" class="edit" title="' . __('Edit') . '"></a>';
                $content .= '<a href="' . $this->getUrl('attributes_group_edit/' . $group->getId()) . '" class="edit-list" title="' . __('Attributes editing') . '"></a>';
                $content .= '<a title="' . __('Delete') . '" href="' . $this->getUrl('attributes_group_delete/' . $group->getId())
                                . '" class="delete" onClick="return _confirm();"></a>
                            </div>
                        <div class="posts"></div>
                    </div>';
            }
        } else {
            $content .= __('Records not found');
        }
        $content .= '</div></div></div>';

        return $popups . $content;
    }


    public function attributes_group_edit($id = null)
    {
        $id = intval($id);
        $popups = '';
        $content = '';
        $Register = Register::getInstance();
        $this->pageTitle = __('Shop') . ' / ' . __('Attributes group editing');
        $this->pageNav = __('Shop') . ' / ' . __('Attributes group editing');
        $this->pageNavr = get_link(__('Attributes groups management'), $this->getUrl('attributes_groups'));
        $attributesModel = $Register['ModManager']->getModelInstance('shopAttributes');
        $model = $Register['ModManager']->getModelInstance('shopAttributesGroups');
        $model->bindModel('attributes');
		$types = $attributesModel->allowedTypes;

        $entity = $model->getById($id);
        if (!$entity) {
            $_SESSION['errors'] = $Register['Validate']->wrapErrors(__('Record not found'), true);
            redirect($this->getUrl('attributes_groups'));
        }
		
		// Adding or edit an attribute
		if (!empty($_POST)) {
			if (!$Register['ACL']->turn(array($this->module, 'attributes_groups_management'), false)) {
				$_SESSION['errors'] = __('Permission denied');
				redirect($this->getUrl('attributes_group_edit/' . $id));
			}
				
			$errors = $Register['Validate']->check(__FUNCTION__);
			if (!empty($errors)) {
				$_SESSION['errors'] = $Register['Validate']->wrapErrors($errors);
				redirect($this->getUrl('attributes_group_edit/' . $id));
			}
			
			$attr_id = (!empty($_POST['attribute_id'])) ? intval($_POST['attribute_id']) : 0;
			if (!empty($attr_id)) {
				$add_attr = $model->getById($attr_id);
				if (!$add_attr) {
					$_SESSION['errors'] = $Register['Validate']->wrapErrors(__('Record not found'), true);
					redirect($this->getUrl('attributes_group_edit/' . $id));
				}
			} else {
				$add_attr = $Register['ModManager']->getEntityInstance('shopAttributes');
			}
			
			
			$add_attr->setTitle($_POST['title']);
			$add_attr->setLabel((!empty($_POST['label'])) ? $_POST['label'] : $_POST['title']);
			$add_attr->setType($_POST['type']);
			$add_attr->setGroup_id($id);
			$add_attr->setIs_filterable((!empty($_POST['is_filterable'])) ? '1' : '0');
			$add_attr->setParams((!empty($_POST['params'])) ? $_POST['params'] : '');
				
			if ($add_attr->save()) {
				$_SESSION['message'] = __('Operation is successful');
				redirect($this->getUrl('attributes_group_edit/' . $id));
			}
			$_SESSION['errors'] = __('Some error occurred');
			redirect($this->getUrl('attributes_group_edit/' . $id));
		}
		
		// Deleting an attribute
		if (isset($_GET['del_attr']) && !empty($_GET['attr_id'])) {
			if (!$Register['ACL']->turn(array($this->module, 'attributes_groups_management'), false)) {
				$_SESSION['errors'] = __('Permission denied');
				redirect($this->getUrl('attributes_group_edit/' . $id));
			}
			
			$entity = $attributesModel->getById(intval($_GET['attr_id']));
			$entity->delete();
			
			$_SESSION['message'] = __('Operation is successful');
			redirect($this->getUrl('attributes_group_edit/' . $id));
		}


        $popups .= '<div id="add_attr" class="popup">
				<div class="top">
					<div class="title">' . __('Attribute adding') . '</div>
					<div onClick="closePopup(\'add_attr\');" class="close"></div>
				</div>
				<form action="' . $this->getUrl('attributes_group_edit/' . $id) . '" method="POST">
				<div class="items">
					<div class="item">
						<div class="left">
							' . __('Title') . ':
						</div>
						<div class="right">
						<input type="text" name="title" value="" />
						<input type="hidden" name="attribute_id" value="" />
						</div>
						<div class="clear"></div>
					</div>
                    <div class="item">
                        <div class="left">
                            ' . __('Label') . ':
                        </div>
                        <div class="right"><input type="text" name="label" value="" /></div>
                        <div class="clear"></div>
                    </div>
                    <div class="item">
                        <div class="left">
                            ' . __('Type') . ':
                        </div>
                        <div class="right"><select name="type">';
		foreach ($types as $type) {
			$popups .= '<option value="' . $type . '">' . $type . '</option>';
		}				
		$popups .= '</select></div>
                        <div class="clear"></div>
                    </div>
                    <div class="item">
                        <div class="left">
                            ' . __('Parameters') . ' (JSON):
                        </div>
                        <div class="right"><textarea style="height:50px;" name="params"></textarea></div>
                        <div class="clear"></div>
                    </div>
                    <div class="item">
                        <div class="left">
                            ' . __('Is filterable') . ':
                        </div>
                        <div class="right"><input id="add_attr_isf" type="checkbox" name="is_filterable" value="1" />
                        <label for="add_attr_isf"></label></div>
                        <div class="clear"></div>
                    </div>
					<div class="item submit">
						<div class="left"></div>
						<div class="right" style="float:left;">
							<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
						</div>
						<div class="clear"></div>
					</div>
				</div>
				</form>
			</div>';

        $attrs = $entity->getAttributes();
        $content .= "<div class=\"list\">
			<div class=\"title\">" . __('Attributes group editing') . " (" . h($entity->getTitle()) . ")</div>
			".'<div class="add-cat-butt" onClick="openPopup(\'add_attr\');"><div class="add"></div>' . __('Add attribute') . '</div>'."
			<table cellspacing=\"0\" style=\"width:100%;\" class=\"grid\"><tr>
			<th width=\"20%\">" . __('Title') . "</th>
			<th width=\"20%\">" . __('Label') . "</th>
			<th width=\"10%\">" . __('Type') . "</th>
			<th width=\"\">" . __('Parameters') . "</th>
			<th width=\"10%\">" . __('Is filterable') . "</th>
			<th width=\"80px\">" . __('Action') . "</th></tr>";
        if (count($attrs) > 0) {
            foreach ($attrs as $attr) {
                $popups .= '<div id="' . $attr->getId() . '_attr" class="popup">
				<div class="top">
					<div class="title">' . __('Attribute editing') . '</div>
					<div onClick="closePopup(\'' . $attr->getId() . '_attr\');" class="close"></div>
				</div>
				<form action="' . $this->getUrl('attributes_group_edit/' . $id) . '" method="POST">
				<div class="items">
					<div class="item">
						<div class="left">
							' . __('Title') . ':
						</div>
						<div class="right">
						<input type="text" name="title" value="' . h($attr->getTitle()) . '" />
						<input type="hidden" name="attribute_id" value="' . $attr->getId() . '" />
						</div>
						<div class="clear"></div>
					</div>
                    <div class="item">
                        <div class="left">
                            ' . __('Label') . ':
                        </div>
                        <div class="right"><input type="text" name="label" value="' . h($attr->getLabel()) . '" /></div>
                        <div class="clear"></div>
                    </div>
                    <div class="item">
                        <div class="left">
                            ' . __('Type') . ':
                        </div>
                        <div class="right"><select name="type">';
				foreach ($types as $type) {
					$popups .= '<option value="' . $type . '"' . (($type === $attr->getType()) 
					? ' selected="selected"' : '') . '>' . $type . '</option>';
				}		
				$popups .= '</select></div>
                        <div class="clear"></div>
                    </div>
                    <div class="item">
                        <div class="left">
                            ' . __('Parameters') . ' (JSON):
                        </div>
                        <div class="right"><textarea style="height:50px;" name="params">' . h($attr->getParams(true)) . '</textarea></div>
                        <div class="clear"></div>
                    </div>
                    <div class="item">
                        <div class="left">
                            ' . __('Is filterable') . ':
                        </div>
                        <div class="right"><input id="add_attr_isf_'.$attr->getId().'" type="checkbox" name="is_filterable" value="1"'
                        . (($attr->getIs_filterable() == 1) ? ' checked="checked"' : '') . ' /><label for="add_attr_isf_'
                        .$attr->getId().'"></label></div>
                        <div class="clear"></div>
                    </div>
					<div class="item submit">
						<div class="left"></div>
						<div class="right" style="float:left;">
							<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
						</div>
						<div class="clear"></div>
					</div>
				</div>
				</form>
			</div>';


                $content .= "<tr>
                        <td>" . h($attr->getTitle()) . "</td>
						<td>" . h($attr->getLabel()) . "</td>
						<td>" . h($attr->getType()) . "</td>
						<td>" . h($attr->getParams(true)) . "</td>
						<td>" . h($attr->getIs_filterable()) . "</td>
						<td colspan=\"\">
						<a class=\"edit\" title=\"" . __('Edit') . "\" onClick=\"openPopup('" . $attr->getId() . "_attr');\" href='javascript:void(0);'></a>
						<a class=\"delete\" title=\"" . __('Delete') . "\" onClick=\"return confirm('" . __('Are you sure?') . "')\" href='" . $this->getUrl('attributes_group_edit/'
                        . $id . "?del_attr=1&attr_id=" . $attr->getId()) . "'></a>
						</td>
						</tr>";
            }
        } else {
            $content .= __('Records not found');
        }
        $content .= '</table></div>';
		

        return $popups . $content;
    }


    public function attributes_group_delete($id = null)
    {
        $id = intval($id);
        $Register = Register::getInstance();
        if (!$Register['ACL']->turn(array($this->module, 'attributes_groups_management'), false)) {
            $_SESSION['errors'] = __('Permission denied');
            redirect($this->getUrl('attributes_groups'));
        }

        $contentModel = $Register['ModManager']->getModelInstance('shopAttributesContent');
        $model = $Register['ModManager']->getModelInstance('shopAttributesGroups');
        $model->bindModel('attributes');
        $entity = $model->getById($id);
        if (!$entity) {
            $_SESSION['errors'] = __('Record not found');
            redirect($this->getUrl('attributes_groups'));
        }

        if ($entity->getAttributes()) {
            foreach ($entity->getAttributes() as $attr) {
				$content = $contentModel->getCollection(array('attribute_id' => $attr->getId()));
				if ($content) {
					foreach ($content as $row) $row->delete();
				}
                $attr->delete();
            }
        }
        $entity->delete();

        $_SESSION['message'] = __('Operation is successful');
        redirect($this->getUrl('attributes_groups'));
    }


	public function delivery($id = null)
	{
		$id = intval($id);
        $Register = Register::getInstance();
        $this->pageTitle = __('Shop') . ' / ' . __('Delivery types management');
        $this->pageNav = __('Shop') . ' / ' . __('Delivery types management');
        $content = '';
        $popups = '';
        $model = $Register['ModManager']->getModelInstance('shopDeliveryTypes');
		
		
		if (!empty($_POST)) {
			if (!$Register['ACL']->turn(array($this->module, 'delivery_types_management'), false)) {
				$_SESSION['errors'] = __('Permission denied');
				redirect($this->getUrl('delivery'));
			}
			
            $errors = $Register['Validate']->check(__FUNCTION__);
            if (!empty($errors)) {
                $_SESSION['errors'] = $Register['Validate']->wrapErrors($errors);
                redirect($this->getUrl('delivery'));
            }
			
			// edit or create
			if (!empty($id)) {
				$entity = $model->getById($id);
				if (!$entity) {
					$_SESSION['errors'] = $Register['Validate']->wrapErrors(__('Record not found'), true);
					redirect($this->getUrl('delivery'));
				}
			} else {
				$entity = $Register['ModManager']->getEntityInstance('shopDeliveryTypes');
			}
			
			$entity->setTitle(trim($_POST['title']));
			$entity->setPrice((!empty($_POST['price']) ? floatval($_POST['price']) : 0));
			$entity->setTotal_for_free((!empty($_POST['total_for_free']) ? intval($_POST['total_for_free']) : 0));
			
			if ($entity->save()) {
				$_SESSION['message'] = __('Operation is successful');
				redirect($this->getUrl('delivery'));
			}
			$_SESSION['errors'] = __('Some error occurred');
			redirect($this->getUrl('delivery'));
		}
		
		
		$deliveries = $model->getCollection();
        $popups .= '<div id="addCat" class="popup">
			<div class="top">
				<div class="title">' . __('Delivery type adding') . '</div>
				<div onClick="closePopup(\'addCat\');" class="close"></div>
			</div>
			<form action="' . $this->getUrl('delivery') . '" method="POST">
			<div class="items">
				<div class="item">
					<div class="left">
						' . __('Title') . ':
					</div>
					<div class="right"><input type="text" name="title" value="" /></div>
					<div class="clear"></div>
				</div>
				<div class="item">
					<div class="left">
						' . __('Price') . ':
					</div>
					<div class="right">
						<input type="text" name="price" value="0" /></div>
					<div class="clear"></div>
				</div>
				<div class="item">
					<div class="left">
						' . __('Total price for free delivery') . ':
					</div>
					<div class="right">
						<input type="text" name="total_for_free" value="0" /></div>
					<div class="clear"></div>
				</div>
				<div class="item submit">
					<div class="left"></div>
					<div class="right" style="float:left;">
						<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
					</div>
					<div class="clear"></div>
				</div>
			</div>
			</form>
		</div>';

        $content .= '<div class="list">
		<div class="title">' . __('Delivery types management') . '</div>
		<div class="add-cat-butt" onClick="openPopup(\'addCat\');"><div class="add"></div>' . __('Add delivery type') . '</div>
		<div class="level1">
			<div class="head">
				<div class="title">' . __('Delivery type') . '</div>
				<div class="buttons">
				</div>
				<div class="clear"></div>
			</div>
			<div class="items">';
        if (count($deliveries) > 0) {
            foreach ($deliveries as $row) {
				$popups .= '<div id="' . $row->getId() . '_edit" class="popup">
					<div class="top">
						<div class="title">' . __('Delivery type editing') . '</div>
						<div onClick="closePopup(\'' . $row->getId() . '_edit\');" class="close"></div>
					</div>
					<form action="' . $this->getUrl('delivery/' . $row->getId()) . '" method="POST">
					<div class="items">
						<div class="item">
							<div class="left">
								' . __('Title') . ':
							</div>
							<div class="right"><input type="text" name="title" value="' . h($row->getTitle()) . '" /></div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Price') . ':
							</div>
							<div class="right">
								<input type="text" name="price" value="' . floatval($row->getPrice()) . '" /></div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Total for free delivery') . ':
							</div>
							<div class="right">
								<input type="text" name="total_for_free" value="' . intval($row->getTotal_for_free()) . '" /></div>
							<div class="clear"></div>
						</div>
						<div class="item submit">
							<div class="left"></div>
							<div class="right" style="float:left;">
								<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
							</div>
							<div class="clear"></div>
						</div>
					</div>
					</form>
				</div>';
				
				$content .= '<div class="level2">
						<div class="number">' . $row->getId() . '</div>
						<div class="title">' . h($row->getTitle()) . '</div>
						<div class="buttons">';
				$content .= '<a href="javascript://" class="edit" title="Edit" onClick="openPopup(\'' . $row->getId() . '_edit\');"></a>
					 <a title="Delete" href="' . $this->getUrl('delivery_delete/' . $row->getId())
					. '" class="delete" onClick="return _confirm();"></a>
					</div>
				</div>';
			}
        }
        $content .= '</div></div></div>';
		return $popups . $content;
	}
	
	
	public function delivery_delete($id = null)
	{
        $id = intval($id);
        $Register = Register::getInstance();
        if (!$Register['ACL']->turn(array($this->module, 'delivery_types_management'), false)) {
            $_SESSION['errors'] = __('Permission denied');
            redirect($this->getUrl('delivery'));
        }

        $model = $Register['ModManager']->getModelInstance('shopDeliveryTypes');
        $entity = $model->getById($id);
        if (!$entity) {
            $_SESSION['errors'] = __('Record not found');
            redirect($this->getUrl('delivery'));
        }
        $entity->delete();

        $_SESSION['message'] = __('Operation is successful');
        redirect($this->getUrl('delivery'));
	}


	public function vendors($id = null)
	{
		$id = intval($id);
        $Register = Register::getInstance();
        $this->pageTitle = __('Shop') . ' / ' . __('Vendors management');
        $this->pageNav = __('Shop') . ' / ' . __('Vendors management');
        $content = '';
        $popups = '';
        $model = $Register['ModManager']->getModelInstance('shopVendors');
		$model->bindModel('products');
		
		
		if (!empty($_POST)) {
			if (!$Register['ACL']->turn(array($this->module, 'vendors_management'), false)) {
				$_SESSION['errors'] = __('Permission denied');
				redirect($this->getUrl('vendors'));
			}
			
            $errors = $Register['Validate']->check(__FUNCTION__);
            if (!empty($errors)) {
                $_SESSION['errors'] = $Register['Validate']->wrapErrors($errors);
                redirect($this->getUrl('vendors'));
            }
			
			// edit or create
			if (!empty($id)) {
				$entity = $model->getById($id);
				if (!$entity) {
					$_SESSION['errors'] = $Register['Validate']->wrapErrors(__('Record not found'), true);
					redirect($this->getUrl('vendors'));
				}
			} else {
				$entity = $Register['ModManager']->getEntityInstance('shopVendors');
			}
			
			$entity->setTitle(trim($_POST['title']));
			$entity->setDescription((!empty($_POST['description']) ? trim($_POST['description']) : ''));
			$old_discount = $entity->getDiscount();
			$entity->setDiscount((!empty($_POST['discount']) ? intval($_POST['discount']) : 0));
			$old_view_on_home = $entity->getView_on_home();
			$entity->setView_on_home((!empty($_POST['view_on_home']) ? '1' : '0'));
			$entity->setHide_not_exists((!empty($_POST['hide_not_exists']) ? '1' : '0'));
			if (!empty($_POST['logo_image_delete']) && $entity->getLogo_image()) {
				if (file_exists(ROOT . '/sys/files/' . $this->module . '/vendors/' . $entity->getLogo_image()))
					@unlink(ROOT . '/sys/files/' . $this->module . '/vendors/' . $entity->getLogo_image());
				$entity->setLogo_image();
			}
			if (!empty($_FILES['logo_image'])) {
				$dest_path = ROOT . '/sys/files/' . $this->module . '/vendors/' . $_FILES['logo_image']['name'];
				if (@move_uploaded_file($_FILES['logo_image']['tmp_name'], $dest_path)) {
					@chmod($dest_path, 0777);
					$entity->setLogo_image($_FILES['logo_image']['name']);
				}
			}

			
			if ($entity->save()) {
				$_SESSION['message'] = __('Operation is successful');
				redirect($this->getUrl('vendors'));
			}
			$_SESSION['errors'] = __('Some error occurred');
			redirect($this->getUrl('vendors'));
		}
		
		
		$vendors = $model->getCollection();
        $popups .= '<div id="addCat" class="popup">
			<div class="top">
				<div class="title">' . __('Vendor adding') . '</div>
				<div onClick="closePopup(\'addCat\');" class="close"></div>
			</div>
			<form action="' . $this->getUrl('vendors') . '" method="POST" enctype="multipart/form-data">
			<div class="items">
				<div class="item">
					<div class="left">
						' . __('Title') . ':
					</div>
					<div class="right"><input type="text" name="title" value="" /></div>
					<div class="clear"></div>
				</div>
				<div class="item">
					<div class="left">
						' . __('Description') . ':
					</div>
					<div class="right"><textarea name="description"></textarea></div>
					<div class="clear"></div>
				</div>
				<div class="item">
					<div class="left">
						' . __('Discount') . ' (%):
					</div>
					<div class="right">
						<input type="text" name="discount" value="0" /></div>
					<div class="clear"></div>
				</div>
				<div class="item">
					<div class="left">
						' . __('View on home') . ':
					</div>
					<div class="right">
						<input type="checkbox" name="view_on_home" value="1" checked="checked" id="add-voh" />
						<label for="add-voh"></label>
					</div>
					<div class="clear"></div>
				</div>
				<div class="item">
					<div class="left">
						' . __('Hide not exists') . ':
					</div>
					<div class="right">
						<input type="checkbox" name="hide_not_exists" value="1" id="add-hne" />
						<label for="add-hne"></label>
					</div>
					<div class="clear"></div>
				</div>
				<div class="item">
					<div class="left">
						' . __('Logo') . ':
					</div>
					<div class="right">
						<input type="file" name="logo_image"/>
					</div>
					<div class="clear"></div>
				</div>
				<div class="item submit">
					<div class="left"></div>
					<div class="right" style="float:left;">
						<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
					</div>
					<div class="clear"></div>
				</div>
			</div>
			</form>
		</div>';

        $content .= "<div class=\"list\">
			<div class=\"title\">" . __('Vendors management') . "</div>
			<div class=\"add-cat-butt\" onClick=\"openPopup('addCat');\"><div class=\"add\"></div>" . __('Add vendor') . "</div>
			<table cellspacing=\"0\" style=\"width:100%;\" class=\"grid\"><tr>
			<th width=\"\">" . getOrderLink(array('title', __('Title'))) . "</th>
			<th width=\"30%\">" . getOrderLink(array('description', __('Description'))) . "</th>
			<th width=\"20%\">" . __('Logo') . "</th>
			<th width=\"10%\">" . getOrderLink(array('discount', __('Discount'))) . "</th>
			<th width=\"15%\">" . getOrderLink(array('view_on_home', __('View on home'))) . "</th>
			<th width=\"10%\">" . getOrderLink(array('hide_not_exists', __('Hide not exists'))) . "</th>
			<th width=\"85px\" colspan=\"\">" . __('Action') . "</th></tr>";
        if (count($vendors) > 0) {
            foreach ($vendors as $row) {
				$popups .= '<div id="' . $row->getId() . '_edit" class="popup">
					<div class="top">
						<div class="title">' . __('Vendor editing') . '</div>
						<div onClick="closePopup(\'' . $row->getId() . '_edit\');" class="close"></div>
					</div>
					<form action="' . $this->getUrl('vendors/' . $row->getId()) . '" method="POST" enctype="multipart/form-data">
					<div class="items">
						<div class="item">
							<div class="left">
								' . __('Title') . ':
							</div>
							<div class="right"><input type="text" name="title" value="' . h($row->getTitle()) . '" /></div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Description') . ':
							</div>
							<div class="right">
								<textarea name="description">' . h($row->getDescription()) . '</textarea></div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Discount') . ' (%):
							</div>
							<div class="right">
								<input type="text" name="discount" value="' . intval($row->getDiscount()) . '" /></div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('View on home') . ':
							</div>
							<div class="right">
								<input type="checkbox" name="view_on_home" value="1"' 
								. (($row->getView_on_home()) ? ' checked="checked"' : '') 
								. ' id="add-voh-' . $row->getId() . '"/>
								<label for="add-voh-' . $row->getId() . '"></label>
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Hide not exists') . ':
							</div>
							<div class="right">
								<input type="checkbox" name="hide_not_exists" value="1"' 
								. (($row->getHide_not_exists()) ? ' checked="checked"' : '') 
								. '  id="add-hne-' . $row->getId() . '"/>
								<label for="add-hne-' . $row->getId() . '"></label>
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Logo') . ':
							</div>
							<div class="right">
								<input type="file" name="logo_image"/>'
								. (($row->getLogo_image()) 
									? '<input type="checkbox" name="logo_image_delete" value="1"'  
										. ' id="del-logo-' . $row->getId() . '"/>
										<label for="del-logo-' . $row->getId() . '">'.__('Delete file').'</label>' 
									: '')
							. '</div>
							<div class="clear"></div>
						</div>
						<div class="item submit">
							<div class="left"></div>
							<div class="right" style="float:left;">
								<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
							</div>
							<div class="clear"></div>
						</div>
					</div>
					</form>
				</div>';
				
				$content .= "<tr>
							<td>" . h($row->getTitle()) . "</td>
							<td>" . h($row->getDescription()) . "</td>
							<td align=\"center\">" . (($row->getLogo_image())
								? '<a class="gallery" href="' . WWW_ROOT . '/sys/files/shop/' . $row->getLogo_image() . '">
									<img src="' . WWW_ROOT . '/image/shop+vendors/' . $row->getLogo_image() . '/100/" /></a>'
								: ' - ') . "</td>
							<td>" . intval($row->getDiscount()) . "</td>
							<td>" . ($row->getView_on_home() ? __('Yes') : __('No')) . "</td>
							<td>" . ($row->getHide_not_exists() ? __('Yes') : __('No')) . "</td>
							<td colspan=\"\">
							<a class=\"edit\" title=\"" . __('Edit') . "\" href=\"javascript:void(0);\" onClick=\"openPopup('" . $row->getId() . "_edit');\"></a>
							<a class=\"delete\" title=\"" . __('Delete') . "\" href='" . $this->getUrl('vendor_delete/' . $row->getId()) . "'></a>
							</td>
							</tr>";
			}
        }
        $content .= '</table></div>';
		return $popups . $content;
	}
	
	
	public function vendor_delete($id = null)
	{
        $id = intval($id);
        $Register = Register::getInstance();
        if (!$Register['ACL']->turn(array($this->module, 'vendors_management'), false)) {
            $_SESSION['errors'] = __('Permission denied');
            redirect($this->getUrl('vendors'));
        }

        $model = $Register['ModManager']->getModelInstance('shopVendors');
        $entity = $model->getById($id);
        if (!$entity) {
            $_SESSION['errors'] = __('Record not found');
            redirect($this->getUrl('vendors'));
        }
		if ($entity->getLogo_image() 
		&& file_exists(ROOT . '/sys/files/' . $this->module . '/vendor/' . $entity->getLogo_image()))
			@unlink(ROOT . '/sys/files/' . $this->module . '/vendor/' . $entity->getLogo_image());
        $entity->delete();

        $_SESSION['message'] = __('Operation is successful');
        redirect($this->getUrl('vendors'));
	}


	public function orders($id = null)
	{
		$id = intval($id);
		$per_page = 50;
        $Register = Register::getInstance();
        $this->pageTitle = __('Shop') . ' / ' . __('Orders management');
        $this->pageNav = __('Shop') . ' / ' . __('Orders management');
        $content = '';
        $popups = '';
        $model = $Register['ModManager']->getModelInstance('shopOrders');
		
		
        $total = $model->getTotal();
        list ($pages, $page) = pagination($total, Config::read($per_page, $this->module), $this->getCurrentUrl('page'));
        $filters = $this->__getOrdersFilters();
        $where = array();
        if (!empty($_GET['filters']) && is_array($_GET['filters'])) {
            foreach ($_GET['filters'] as $k => $v) {
                $where[$k] = $v;
            }
        }
		
		
		$model->bindModel('author');
		$model->bindModel('delivery_type');
		$model->bindModel('products');
        $params = array(
            'page' => $page,
            'limit' => $per_page,
            'order' => $model->getOrderParam(),
        );
		$orders = $model->getCollection();
		
		
        $content .= "<div class=\"list\">
			<div class=\"title\">" . __('Orders management') . "</div>
			<div class=\"add-cat-butt\" onClick=\"window.location.href='" .  
			$this->getUrl('order_edit') . "';\"><div class=\"add\"></div>" . __('Add order') . "</div>
			<table cellspacing=\"0\" style=\"width:100%;\" class=\"grid\"><tr>
			<th width=\"13%\">" . getOrderLink(array('date', __('Date'))) . "</th>
			<th width=\"7%\">" . getOrderLink(array('user', __('User'))) . "</th>
			<th width=\"5%\">" . getOrderLink(array('status', __('Status'))) . "</th>
			<th width=\"7%\">" . getOrderLink(array('total', __('Total'))) . "</th>
			<th width=\"15%\">" . getOrderLink(array('delivery_type', __('Delivery type'))) . "</th>
			<th width=\"\">" . __('Products') . "</th>
			<th width=\"7%\">" . getOrderLink(array('first_name', __('First name'))) . "</th>
			<th width=\"7%\">" . getOrderLink(array('last_name', __('Last name'))) . "</th>
			<th width=\"110px\" colspan=\"\">" . __('Action') . "</th></tr>";
        if (count($orders) > 0) {
            foreach ($orders as $row) {
				$popups .= '<div id="' . $row->getId() . '_edit" class="popup">
					<div class="top">
						<div class="title">' . __('View order') . '</div>
						<div onClick="closePopup(\'' . $row->getId() . '_edit\');" class="close"></div>
					</div>
					<form action="' . $this->getUrl('orders/' . $row->getId()) . '" method="POST" enctype="multipart/form-data">
					<div class="items">
						<div class="item">
							<div class="left">
								' . __('Date') . ':
							</div>
							<div class="right">' . AtmDateTime::getDate($row->getDate(), 'Y-m-d H:i:s') . '</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('User') . ':
							</div>
							<div class="right">' . h(($row->getAuthor()) ? $row->getAuthor()->getName() : '-') . '</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Status') . ':
							</div>
							<div class="right">' . $row->getStatus() . '</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Total') . ':
							</div>
							<div class="right">' . number_format($row->getTotal(), 2, ',', ' ') . '</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Delivery type') . ':
							</div>
							<div class="right">' . h(($row->getDelivery_type()) ? $row->getDelivery_type()->getTitle() : '-') . '</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('First name') . ':
							</div>
							<div class="right">' . h($row->getFirst_name()) . '</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Last name') . ':
							</div>
							<div class="right">' . h($row->getLast_name()) . '</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Telephone') . ':
							</div>
							<div class="right">' . h($row->getTelephone()) . '</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Delivery address') . ':
							</div>
							<div class="right">' . h($row->getDelivery_address()) . '</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								' . __('Comment') . ':
							</div>
							<div class="right"><textarea disabled="disabled">' . h($row->getComment()) . '</textarea></div>
							<div class="clear"></div>
						</div>
						<div class="item submit">
							<div class="left"></div>
							<div class="right" style="float:left;">
								<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
							</div>
							<div class="clear"></div>
						</div>
					</div>
					</form>
				</div>';
				
				$content .= "<tr>
							<td>" . AtmDateTime::getDate($row->getDate(), 'Y-m-d H:i:s') . "</td>
							<td>" . h(($row->getAuthor()) ? $row->getAuthor()->getName() : '-') . "</td>
							<td align=\"center\">" . $row->getStatus() . "</td>
							<td align=\"center\">" . number_format($row->getTotal(), 2, ',', ' ') . "</td>
							<td>" . h(($row->getDelivery_type()) ? $row->getDelivery_type()->getTitle() : '-') . "</td>
							<td>"; 
				if ($row->getProducts()) {
					$randColor = function(){
						$colors = array('d60100', 'd85700', 'cda902', 'd6d700', '8cd304', '00d402', '23579d', '5b84ba');
						return $colors[array_rand($colors)];
					};
					foreach ($row->getProducts() as $product) {
						$content .= '<a href="' . $this->getUrl('edit_product/' . $product->getId()) 
							. '" style="text-shadow:none; color:#' . $randColor() . '">' 
							. h($product->getTitle()) . '</a><br>';
					}
					$content = substr($content, 0, -4);
				} else {
					$content .= '-';
				}
				$content .= "</td>
							<td>" . h($row->getFirst_name()) . "</td>
							<td>" . h($row->getLast_name()) . "</td>
							<td colspan=\"\">
							<a class=\"view\" title=\"" . __('View') . "\" href=\"javascript:void(0);\" onClick=\"openPopup('" . $row->getId() . "_edit');\"></a>
							<a class=\"edit\" title=\"" . __('Edit') . "\" href=\"" . $this->getUrl('order_edit/' . $row->getId()) . "\"></a>
							<a class=\"delete\" title=\"" . __('Delete') . "\" href='" . $this->getUrl('order_delete/' . $row->getId()) . "'></a>
							</td>
							</tr>";
			}
        }
        $content .= '</table></div>';
		return $popups . $content;
	}
	
	
	public function order_edit($id = null)
	{
		$id = intval($id);
        $Register = Register::getInstance();
        $this->pageTitle = __('Shop') . ' / ' . __('Order editing');
        $this->pageNav = __('Shop') . ' / ' . __('Order editing');
        $content = '';
        $popups = '';
        $model = $Register['ModManager']->getModelInstance('shopOrders');
		$model->bindModel('author');
		$model->bindModel('delivery_type');
		$model->bindModel('products');
		if ($id > 0) {
			$entity = $model->getById($id);
            if (!$entity) {
                $_SESSION['errors'] = $Register['Validate']->wrapErrors(__('Record not found'), true);
                redirect($this->getUrl('orders'));
            }
		} else {
			$entity = $Register['ModManager']->getEntityInstance('shopOrders');
		}


		if (!empty($_POST)) {
			if (!$Register['ACL']->turn(array($this->module, 'orders_management'), false)) {
				$_SESSION['errors'] = __('Permission denied');
				redirect($this->getUrl('order_edit/' . $id));
			}
			
			$errors = $Register['Validate']->check(__FUNCTION__);
			if (!empty($errors)) {
				$_SESSION['errors'] = $Register['Validate']->wrapErrors($errors);
				redirect($this->getUrl('order_edit/' . $id));
			}
			
			$entity->setDate(AtmDateTime::getDate($_POST['date'], 'Y-m-d H:i:s'));
			$entity->setStatus($_POST['status']);
			$entity->setTotal(floatval($_POST['total']));
			$entity->setDelivery_type_id(intval($_POST['delivery_type_id']));
			$entity->setDelivery_address(trim($_POST['delivery_address']));
			$entity->setTelephone(intval($_POST['telephone']));
			$entity->setFirst_name(trim($_POST['first_name']));
			$entity->setLast_name(trim($_POST['last_name']));
			$products = $entity->getProducts();
			$deleted_products = array();
			if ($products) {
				foreach ($products as $k => $pr) {
					if (!empty($_POST['products']) && in_array($pr->getId(), $_POST['products'])) {
						unset($_POST['products'][array_search($pr->getId(), $_POST['products'])]);
						continue;
					}
					
					if (count($products) == 1) {
						$_SESSION['errors'] = $Register['Validate']->wrapErrors(__('You can\'t remove the last product in order'), true);
						redirect($this->getUrl('order_edit/' . $id));
					}
					$deleted_products[] = $pr;
					unset($products[$k]);
				}
			}
			if (empty($products) && empty($_POST['products'])) {
				$_SESSION['errors'] = $Register['Validate']->wrapErrors(__('You can\'t seve an order without products'), true);
				redirect($this->getUrl('order_edit/' . $id));
			}
			
			if ($entity->save()) {
				if (!empty($deleted_products)) {
					foreach ($deleted_products as $pr) {
						$pr->delete();
					}
				}
				if (!empty($_POST['products'])) {
					foreach ($_POST['products'] as $pr) {
						$pr_ = $Register['ModManager']->getEntityInstance('shopOrdersProducts');
						$pr_->setOrder_id($entity->getId());
						$pr_->setProduct_id(intval($pr));
						$pr_->save();
					}
				}
				
				$_SESSION['message'] = __('Operation is successful');
				redirect($this->getUrl('order_edit/' . $id));
			}
			$_SESSION['errors'] = __('Some error occurred');
			redirect($this->getUrl('order_edit/' . $id));
		}
		
		
		$popups .= '<div id="product_add" class="popup">
			<div class="top">
				<div class="title">' . __('Adding product to order') . '</div>
				<div onClick="closePopup(\'product_add\');" class="close"></div>
			</div>
			<form action="" method="POST">
			<div class="items">
				<div class="item">
					<div class="left">
						' . __('Title') . ':
					</div>
					<div class="right">
						<input id="autocomplete_inp" type="text" name="add_product_title" value=""/>
						<input type="hidden" name="add_product_id" value=""/>
						<input type="hidden" name="add_product_price" value=""/>
					</div>
				<script>
				$(\'#autocomplete_inp\').keypress(function(e){
					var inp = $(this);
					if (inp.val().length < 2) return;
					setTimeout(function(){
						
						$.get(\'' . $this->getUrl('find_product/') . '\'+inp.val(), {}, function(response){
						
							var prs = $.parseJSON(response);
							$(prs).each(function(k, pr){
								var json = JSON.stringify(pr);
								console.log(pr);
								console.log(json);
								$(\'#add_product_list\').html(
									$(\'#add_product_list\').html() 
									+ \'<li onClick=\\\'addProductToOrderForm(\'+ json +\')\\\'><a>\' + pr.title + \'</a></li>\');
									if (k >= (prs.length - 1)) {
										$(\'#add_product_list\').html(\'<ul>\'+$(\'#add_product_list\').html() + \'</ul>\');
									}
							});
							
						});
						
					}, 500);

				});
				function addProductToOrderForm(product) {
					$(\'input[name="add_product_title"]\').val(product.title);
					$(\'input[name="add_product_id"]\').val(product.id);
					$(\'input[name="add_product_price"]\').val(product.price);
					$(\'#add_product_list\').html(\'\');
					
				}
				function addProductToOrder(e) {
					var evt = e ? e:window.event;
					if (evt.preventDefault) evt.preventDefault();
					evt.returnValue = false;
				
					if (!$(\'input[name="add_product_title"]\').val().length || 
					!$(\'input[name="add_product_id"]\').val().length ||
					!$(\'input[name="add_product_price"]\').val().length) {
						alert(\'' . __('Choose product') . '\');
						return false;
					}
					var title = $(\'input[name="add_product_title"]\').val(),
						id = $(\'input[name="add_product_id"]\').val(),
						price = $(\'input[name="add_product_price"]\').val(),
						quantity = ($(\'input[name="quantity"]\').val() > 0) ? parseInt($(\'input[name="quantity"]\').val()) : 1;
					
					for (var i = 0; i < quantity; i++) {
						var uniqid = id + \'-\' + Math.floor(Math.random() * 1000);
						$(\'#order_products\').html($(\'#order_products\').html()
							+ \'<div data-price="\' + price + \'" id="product_\' + uniqid + \'">'
							. '<a href="' . $this->getUrl('edit_product/') 
							. '\' + id + \'" style="text-shadow:none; color:#aaa">' 
							. '\' + title + \'</a><input type="hidden" name="products[]" value="\' + id + \'">' 
							. '<a class="delete" style="margin-bottom:-7px;" onClick="deleteProduct(\\\'\' + uniqid + \'\\\')"></a></div>\');
						
							var new_total = parseInt($(\'input[name="int_total"]\').val()) + parseInt(price);
							$(\'input[name="total"]\').val(number_format(new_total, 2, ".", ""));
							$(\'input[name="int_total"]\').val(new_total);
						
					}
					setTimeout(function(){
						closePopup(\'product_add\');
						$(\'#autocomplete_inp\').val(\'\');
					}, 1);
					return false;
				}
				function deleteProduct(id) {
					$(\'#product_\'+id).hide(1000, function(){
						$(\'input[name="total"]\').val(
							number_format(
								parseInt($(\'input[name="int_total"]\').val()) - parseInt($(\'#product_\'+id).data(\'price\'))
							, 2, ".", "")
						);
						setTimeout(function(){
							$(\'input[name="int_total"]\').val(parseInt($(\'input[name="int_total"]\').val()) - parseInt($(\'#product_\'+id).data(\'price\')));
						}, 0);
					});
				}
				function number_format( number, decimals, dec_point, thousands_sep ) {	// Format a number with grouped thousands
					var i, j, kw, kd, km;
					// input sanitation & defaults
					if( isNaN(decimals = Math.abs(decimals)) ){
						decimals = 2;
					}
					if( dec_point == undefined ){
						dec_point = ",";
					}
					if( thousands_sep == undefined ){
						thousands_sep = ".";
					}

					i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

					if( (j = i.length) > 3 ){
						j = j % 3;
					} else{
						j = 0;
					}

					km = (j ? i.substr(0, j) + thousands_sep : "");
					kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
					//kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).slice(2) : "");
					kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");
					return km + kw + kd;
				}
				</script>
					<div class="clear"></div>
				</div><div  id="add_product_list"></div>
				<div class="item">
					<div class="left">
						' . __('Quantity') . ':
					</div>
					<div class="right"><input type="text" name="quantity" value="1" /></div>
					<div class="clear"></div>
				</div>
				<div class="item submit">
					<div class="left"></div>
					<div class="right" style="float:left;">
						<input type="submit" onClick="addProductToOrder(event)" value="' . __('Save') . '" name="send" class="save-button" />
					</div>
					<div class="clear"></div>
				</div>
			</div>
			</form>
		</div>';
		
        $content .= '<script type="text/javascript" src="' . WWW_ROOT . '/sys/js/datepicker/datepicker.js"></script>
			<link type="text/css" rel="StyleSheet" href="' . WWW_ROOT . '/sys/js/datepicker/datepicker.css" />
			<script type="text/javascript">
			$(document).ready(function(){
				$(\'.tcal\').datetimepicker({
					timepicker:true,
					format:\'Y-m-d H:i:s\',
					closeOnDateSelect: false,
					timepickerScrollbar: true
				});
			});
			</script>
			<form action="' . $this->getUrl('order_edit/' . $id) . '" method="POST" enctype="multipart/form-data">
			<div class="list">
			<div class="title">' . __('Order editing') . '</div>
			<div class="add-cat-butt" onClick="openPopup(\'product_add\');"><div class="add"></div>' . __('Add product') . '</div>
			<div class="level1">
				<div class="items">
					<div class="setting-item">
						<div class="left">' . __('Date') . '</div>
						<div class="right">
							<input class="tcal" type="text" name="date" value="' 
							. (($entity->getDate()) ? AtmDateTime::getDate($entity->getDate(), 'Y-m-d H:i:s') : date('Y-m-d H:i:s')) . '" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">' . __('Status') . '</div>
						<div class="right">
							<select name="status">';
		foreach (array('process', 'delivery', 'complete') as $r) {
			$content .= '<option value="' . $r . '"' 
			. (($entity->getStatus() && $entity->getStatus() === $r) ? ' selected="selected"' : '') 
			. '>' . ucfirst($r) . '</option>';
		}		
		$content .= '</select>
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">' . __('Total') . '</div>
						<div class="right">
							<input type="text" name="total" value="' . number_format($entity->getTotal(), 2, '.', '') . '" />
							<input type="hidden" name="int_total" value="' . floatval($entity->getTotal()) . '" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">' . __('Delivery type') . '</div>
						<div class="right">
							' . $this->getModelFilter('shopDeliveryTypes', 'delivery_type_id', $entity->getDelivery_type_id(), true) . '
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">' . __('Delivery address') . '</div>
						<div class="right">
							<input type="text" name="delivery_address" value="' . h($entity->getDelivery_address()) . '" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">' . __('Telephone') . '</div>
						<div class="right">
							<input type="text" name="telephone" value="' . intval($entity->getTelephone()) . '" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">' . __('First name') . '</div>
						<div class="right">
							<input type="text" name="first_name" value="' . h($entity->getFirst_name()) . '" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">' . __('Last name') . '</div>
						<div class="right">
							<input type="text" name="last_name" value="' . h($entity->getLast_name()) . '" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">' . __('Comment') . '</div>
						<div class="right">
							<input type="text" name="comment" value="' . h($entity->getComment()) . '" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">' . __('Products') . '</div>
						<div class="right" id="order_products">';
		if ($entity->getProducts()) {
			$randColor = function(){
				$colors = array('d60100', 'd85700', 'cda902', 'd6d700', '8cd304', '00d402', '23579d', '5b84ba');
				return $colors[array_rand($colors)];
			};
			foreach ($entity->getProducts() as $product) {
				$uniqid = $product->getId() . '-' . rand();
				$content .= '<div data-price="' . $product->getPrice() . '" id="product_' . $uniqid . '"><a href="' 
					. $this->getUrl('edit_product/' . $product->getId()) 
					. '" style="text-shadow:none; color:#' . $randColor() . '">' 
					. h($product->getTitle()) . '</a><input type="hidden" name="products[]" value="' 
					. $product->getId() . '"/>
					<a class="delete" style="margin-bottom:-7px;" onClick="deleteProduct(\'' . $uniqid . '\')"></a></div>';
			}
		}		
		$content .= '</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left"></div>
						<div class="right">
							<input class="save-button" type="submit" name="send" value="' . __('Save') . '" />
						</div>
						<div class="clear"></div>
					</div>
				</div></div></div></form>';
        
		return $popups . $content;
	}
	
	
	public function order_delete($id = null)
	{
        $id = intval($id);
        $Register = Register::getInstance();
        if (!$Register['ACL']->turn(array($this->module, 'orders_management'), false)) {
            $_SESSION['errors'] = __('Permission denied');
            redirect($this->getUrl('orders'));
        }

        $model = $Register['ModManager']->getModelInstance('shopOrders');
        $ordersProductsModel = $Register['ModManager']->getModelInstance('shopOrdersProducts');
        $entity = $model->getById($id);
        if (!$entity) {
            $_SESSION['errors'] = __('Record not found');
            redirect($this->getUrl('orders'));
        }
		
		$products = $ordersProductsModel->getCollection(array('order_id' => $id));
		if ($products) {
			foreach ($products as $row) {
				$row->delete();
			}
		}
        $entity->delete();

        $_SESSION['message'] = __('Operation is successful');
        redirect($this->getUrl('orders'));
	}
	
	
	public function find_product($title)
	{
		$title = trim($title);
		if (empty($title)) die('');
		

		$Register = Register::getInstance();
		$model = $Register['ModManager']->getModelInstance('shopProducts');
		$entities = $model->getCollection(array(
			"title LIKE '$title%'"
		), array(
			'limit' => 100,
		));

		if (!empty($entities)) {
			foreach ($entities as  &$entity) $entity = $entity->asArray();
			
			die(json_encode($entities));
		}
		die('');
	}
	
	
    public function statistics($type = false, $id = false)
    {
		if ($type && !$id) $type = false;
		$Register = Register::getInstance();
		$this->pageTitle = __('Shop') . ' / ' . __('Statistics');
		$this->pageNav = __('Shop') . ' / ' . __('Statistics');
		$content = '';
		
		if (!$type) {
			$deliveryModel = $Register['ModManager']->getModelInstance('shopDeliveryTypes');
			$ordersProductsModel = $Register['ModManager']->getModelInstance('shopOrdersProducts');
			$ordersModel = $Register['ModManager']->getModelInstance('shopOrders');
		   
			list ($date1, $date2) = $this->getDateRange('orders-by-dates');
			$data = $ordersModel->getCollection(array(
				"a.date BETWEEN '{$date1}' AND '{$date2}'",
			), array(
				'joins' => array(
					array(
						'table' => 'shop_orders',
						'alias' => 'b',
						'cond' => array("a.id = b.id", "a.status = 'process'"),
						'type' => 'LEFT',
					),
					array(
						'table' => 'shop_orders',
						'alias' => 'c',
						'cond' => array("a.id = c.id", "a.status = 'complete'"),
						'type' => 'LEFT',
					),
					array(
						'table' => 'shop_orders',
						'alias' => 'd',
						'cond' => array("a.id = d.id", "a.status = 'delivery'"),
						'type' => 'LEFT',
					),
				),
				'fields' => array(
					'a.date',
					'COUNT( a.id ) AS all_cnt',
					'COUNT( b.id ) AS process_cnt',
					'COUNT( c.id ) AS complete_cnt',
					'COUNT( d.id ) AS delivery_cnt',
				),
				'alias' => 'a',
				'group' => 'a.date',
				'order' => 'a.date DESC',
				/*'limit' => 20,*/
			));
			$orders_st_date = array(0 => array(), 1 => array(), 2 => array(), 3 => array());
			$orders_st_date_ticks = array();
			if (!empty($data)) {
				foreach ($data as $k => $row) {
					$row = $row->asArray();
					$orders_st_date[0][$k] = intval($row['all_cnt']); // all
					$orders_st_date[1][$k] = intval($row['process_cnt']); // process
					$orders_st_date[2][$k] = intval($row['delivery_cnt']); // delivery
					$orders_st_date[3][$k] = intval($row['complete_cnt']); // complete
					$orders_st_date_ticks[$k] = AtmDateTime::getDate($row['date'], "Y/m/d");
				}
			}

			list ($date1, $date2) = $this->getDateRange('top-products');
			$data = $ordersProductsModel->getCollection(array(
				"a.order_id IN (SELECT id FROM shop_orders WHERE date BETWEEN '{$date1}' AND '{$date2}')",
			), array(
				'joins' => array(
					array(
						'table' => 'shop_products',
						'alias' => 'b',
						'cond' => array("a.product_id = b.id"),
						'type' => '',
					),
				),
				'fields' => array(
					'a.product_id',
					'b.title',
					'COUNT(a.id) as cnt',
				),
				'alias' => 'a',
				'group' => 'a.product_id',
				'order' => '`cnt` DESC',
				'limit' => 30,
			));
			$top_products = array();
			$top_products_ticks = array();
			if (!empty($data)) {
				foreach ($data as $k => $row) {
					$row = $row->asArray();
					$top_products[$k] = intval($row['cnt']);
					$top_products_ticks[$k] = h($row['title']);
				}
			}
			

			list ($date1, $date2) = $this->getDateRange('orders-total');
			$data = $ordersModel->getCollection(array(
				"date BETWEEN '{$date1}' AND '{$date2}'",
			), array(
				'fields' => array(
					"DATE_FORMAT(date, '%Y-%m-%d') AS date",
					'SUM(total) AS total',
				),
				'group' => 'date',
				'order' => 'date ASC',
			));
			$orders_total = array();
			$orders_total_ticks = array();
			$dateObj = new DateTime($date1);
			$prev_date = (string)$dateObj->modify('-1 day')->format('Y-m-d');
			if (!empty($data)) {
				foreach ($data as $k => $row) {
					$row = $row->asArray();
					$check_date = (string)$dateObj->modify('+1 day')->format('Y-m-d');
					
					while ($check_date < $row['date']) {
						$orders_total[] = array($check_date, 0);
						$orders_total_ticks[] = $check_date;
						$check_date = (string)$dateObj->modify('+1 day')->format('Y-m-d');
					}

					$orders_total[] = array($row['date'], intval($row['total']));
					$orders_total_ticks[] = $row['date'];
					$check_date = $row['date'];
				}
			}

			$delivery_types = array();
			$d_data = $deliveryModel->getCollection(array(), array('fileds' => array('id', 'title')));
			if ($d_data) {
				foreach ($d_data as $r) {
					$r = $r->asArray();
					$delivery_types[] = h($r['title']);
				}
			}
			list ($date1, $date2) = $this->getDateRange('delivery-types');
			$data = $ordersModel->getCollection(array(
				"date BETWEEN '{$date1}' AND '{$date2}'",
			), array(
				'joins' => array(
					array(
						'table' => 'shop_delivery_types',
						'type' => 'LEFT',
						'alias' => 'b',
						'cond' => array('a.delivery_type_id = b.id'),
					),
				),
				'fields' => array(
					"DATE_FORMAT(a.date, '%Y-%m-%d') AS date",
					'COUNT(a.delivery_type_id) as cnt',
					'b.title',
				),
				'alias' => 'a',
				'group' => 'a.date, a.delivery_type_id',
				'order' => 'a.date ASC',
			));
			$deliveries = array_fill(0, count($delivery_types), array());
			$deliveries_ticks = array();
			$dateObj = new DateTime($date1);
			$prev_date = (string)$dateObj->modify('-1 day')->format('Y-m-d');
			if (!empty($data)) {
				foreach ($data as $k => $row) {
					$row = $row->asArray();
					$d_type_key = array_search(h($row['title']), $delivery_types);
					if ($d_type_key === false) $delivery_types[] = 'Unknown';
				}
				foreach ($data as $k => $row) {
					$row = $row->asArray();
					if (empty($row['title'])) $row['title'] = 'Unknown';
					$d_type_key = array_search(h($row['title']), $delivery_types);
					$check_date = (string)$dateObj->modify('+1 day')->format('Y-m-d');
					

					
					while ($check_date < $row['date']) {
						$deliveries = array_map(function($n) use ($deliveries_ticks){
							if (!array_key_exists(count($deliveries_ticks), $n)) $n[count($deliveries_ticks)] = 0;
							return $n;
						}, $deliveries);
						$deliveries[$d_type_key][count($deliveries_ticks)] = 0;
						$deliveries_ticks[] = $check_date;
						$check_date = (string)$dateObj->modify('+1 day')->format('Y-m-d');
					}
					
					$date_key = array_search($row['date'], $deliveries_ticks);
					if ($date_key === false) $date_key = count($deliveries_ticks);
					
					$deliveries = array_map(function($n) use ($deliveries_ticks){
						if (!array_key_exists(count($deliveries_ticks), $n)) $n[count($deliveries_ticks)] = 0;
						return $n;
					}, $deliveries);
					$deliveries[$d_type_key][$date_key] = intval($row['cnt']);
					if (!in_array($row['date'], $deliveries_ticks)) 
						$deliveries_ticks[] = $row['date'];
					$check_date = $row['date'];
				}
			}
		}
		//pr($deliveries_ticks); pr($deliveries); die();
        ob_start();
        ?>
		<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jqplot/graphlib.js"></script>
		<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jqplot/plugins/jqplot.canvasTextRenderer.min.js"></script>
		<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js"></script>
		<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jqplot/plugins/jqplot.barRenderer.min.js"></script>
		<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
		<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>
		<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jqplot/plugins/jqplot.pointLabels.min.js"></script>
		<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/jqplot/plugins/jqplot.highlighter.min.js"></script>
		<!--<script type="text/javascript" src="/sys/js/jqplot_plugins/jqplot.bubbleRenderer.min.js"></script>-->
		<link href="/sys/js/jqplot/style.css" type="text/css" rel="stylesheet">
		<script type="text/javascript" src="<?php echo WWW_ROOT ?>/sys/js/datepicker/datepicker.js"></script>
		<link type="text/css" rel="StyleSheet" href="<?php echo WWW_ROOT ?>/sys/js/datepicker/datepicker.css" />
		<script type="text/javascript">
		$(document).ready(function(){
			$('.tcal').datetimepicker({
				timepicker:false,
				format:'Y/m/d',
				closeOnDateSelect: true
			});
		});
		</script>
		<?php
        $content .= ob_get_clean();
		
		if ($type === 'product') {
			$content .= $this->getProductStatistics($id);
		}
		
		if (!$type):
			ob_start();
			?>
			<div class="list">
				<div class="title"><?php echo __('Orders') ?></div>
				<div class="level1">
					<div class="head">
						<div class="title"><?php echo __('Orders by dates') ?></div>
						<?php echo $this->getDateRangeSelector('orders-by-dates') ?>
					</div>
					<div class="graph-wrapper"><div  class="graph-container" id="chart1"></div></div>
					<div class="title"><?php echo __('Top products') ?>
						<?php echo $this->getDateRangeSelector('top-products') ?>
						<div class="descr"><?php echo __('Top products by period') ?></div>
					</div>
					<div class="graph-wrapper"><div  class="graph-container" id="chart2"></div></div>
					<div class="title"><?php echo __('Orders total summ') ?>
						<?php echo $this->getDateRangeSelector('orders-total') ?>
						<div class="descr"><?php echo __('Orders total summ per day') ?></div>
					</div>
					<div class="graph-wrapper"><div  class="graph-container" id="chart4"></div></div>
					<div class="title"><?php echo __('Delivery') ?>
						<?php echo $this->getDateRangeSelector('delivery-types') ?>
						<div class="descr"><?php echo __('Deivery types by days') ?></div>
					</div>
					<div class="graph-wrapper"><div  class="graph-container" id="chart5"></div></div>
				</div>
			</div>
			<script type="text/javascript">
					$(document).ready(function(){
					  plot1 = $.jqplot('chart1', <?php echo json_encode($orders_st_date) ?>, {
						stackSeries: false,
						captureRightClick: true,
						seriesDefaults:{
						  renderer:$.jqplot.BarRenderer,
						  rendererOptions: {
							  /*barMargin: 20,*/
							  highlightMouseDown: true
						  },
						  pointLabels: {show: true, stackedValue: false}
						},
						axes: {
						  xaxis: {
							  renderer: $.jqplot.CategoryAxisRenderer,
							  ticks: <?php echo json_encode($orders_st_date_ticks) ?>
						  },
						  yaxis: {
							padMin: 0,
							tickOptions: {formatString: '%d'}
						  }
						},
						legend: {
							show: true,
							location: 'e',
							placement: 'inside',
							labels: ['total', 'process', 'delivery', 'complete']
						}
					  });
					  plot2 = $.jqplot('chart2', [<?php echo json_encode($top_products) ?>], {
						stackSeries: false,
						captureRightClick: true,
						seriesDefaults:{
						  renderer:$.jqplot.BarRenderer,
						  rendererOptions: {
							  barMargin: 20,
							  varyBarColor: true,
							  highlightMouseDown: true
						  },
						  pointLabels: {show: true, stackedValue: false}
						},
						axes: {
						  xaxis: {
							  renderer: $.jqplot.CategoryAxisRenderer,
							  ticks: <?php echo json_encode($top_products_ticks) ?>
						  },
						  yaxis: {
							padMin: 0,
							min: 0,
							tickOptions: {formatString: '%d'}
						  }
						}
					  });
					  plot4 = $.jqplot('chart4', [<?php echo json_encode($orders_total) ?>], {
						seriesDefaults:{
						  pointLabels: {show: true, stackedValue: false}
						},
						axes: {
						  xaxis: {
							  renderer: $.jqplot.DateAxisRenderer,
							  tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
							   tickOptions: {angle: 30, fontSize: '10px'},
							  autoscale:true,
							  ticks: <?php echo json_encode($orders_total_ticks) ?>
						  },
						  yaxis: {
							autoscale:true,
							padMin: 0,
							min: 0,
							tickOptions: {formatString: '%d'}
						  }
						},
						highlighter: {
							show: true,
							sizeAdjust: 7.5,
							formatString: 'On %s was made orders for %s total'
						},
						series: [
							{
								lineWidth:2,
								fill: true,
								fillAndStroke: true,
								color:'#4bb2c5',
								fillColor: '#4bb2c5',
								fillAlpha: 0.3,
								label:'Orders total',
								markerOptions: { style:'circle'}
							}
						]
					  });
					  plot5 = $.jqplot('chart5', <?php echo json_encode($deliveries) ?>, {
						stackSeries: false,
						captureRightClick: true,
						seriesDefaults:{
						  renderer:$.jqplot.BarRenderer,
						  rendererOptions: {
							  highlightMouseDown: true
						  },
						  pointLabels: {show: true, stackedValue: false}
						},
						axes: {
						  xaxis: {
							  renderer: $.jqplot.CategoryAxisRenderer,
							  tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
							  tickOptions: {angle: 30, fontSize: '10px'},
							  ticks: <?php echo json_encode($deliveries_ticks) ?>
						  },
						  yaxis: {
							padMin: 0,
							tickOptions: {formatString: '%d'}
						  }
						},
						legend: {
							show: true,
							location: 'e',
							placement: 'inside',
							labels: <?php echo json_encode($delivery_types) ?>
						}
					  });
					});
			</script>
			<?php
			$content .= ob_get_clean();
		endif;
        return $content;
    }
	
	
	private function getProductStatistics($id = null)
	{
        $Register = Register::getInstance();
        $this->pageTitle = __('Shop') . ' / ' . __('Product statistics');
        $this->pageNav = __('Shop') . ' / ' . __('Product statistics');
		$content = '';
		$ordersModel = $Register['ModManager']->getModelInstance('shopOrders');
		
		list ($date1, $date2) = $this->getDateRange('product-history');
		$id = 1;
        $data = $ordersModel->getCollection(array(
			"c.id = $id",
			"a.date BETWEEN '{$date1}' AND '{$date2}'",
		), array(
            'joins' => array(
                array(
                    'table' => 'shop_orders_products',
                    'alias' => 'b',
                    'cond' => array("a.id = b.order_id"),
                    'type' => 'LEFT',
                ),
                array(
                    'table' => 'shop_products',
                    'alias' => 'c',
                    'cond' => array("c.id = b.product_id"),
                    'type' => '',
                ),
            ),
            'fields' => array(
                "DATE_FORMAT(a.date, '%Y-%m-%d') as date",
                'COUNT(c.id) AS cnt',
            ),
            'alias' => 'a',
            'group' => 'a.date',
			'order' => 'a.date ASC',
        ));
		$product_history = array();
		$product_history_ticks = array();
		$dateObj = new DateTime($date1);
		$prev_date = (string)$dateObj->modify('-1 day')->format('Y-m-d');
        if (!empty($data)) {
            foreach ($data as $k => $row) {
                $row = $row->asArray();
				$check_date = (string)$dateObj->modify('+1 day')->format('Y-m-d');
				
				while ($check_date < $row['date']) {
					$product_history[] = array($check_date, 0);
					$product_history_ticks[] = $check_date;
					$check_date = (string)$dateObj->modify('+1 day')->format('Y-m-d');
				}

                $product_history[] = array($row['date'], intval($row['cnt']));
                $product_history_ticks[] = $row['date'];
				$check_date = $row['date'];
            }
        }
        ob_start();
        ?>
		<div class="list">
			<div class="title"><?php echo __('Product orders') ?></div>
			<div class="level1">
				<div class="head">
					<div class="title"><?php echo sprintf(__('Orders for "%s"'), 'Razor Game PC ') ?></div>
					<?php echo $this->getDateRangeSelector('product-history') ?>
				</div>
				<div class="graph-wrapper"><div  class="graph-container" id="chart3"></div></div>
				
			</div>
		</div>
		<script type="text/javascript">
				$(document).ready(function(){
				  plot3 = $.jqplot('chart3', [<?php echo json_encode($product_history) ?>], {
					seriesDefaults:{
					  pointLabels: {show: true, stackedValue: false}
					},
					axes: {
					  xaxis: {
						  renderer: $.jqplot.DateAxisRenderer,
						  tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
						   tickOptions: {angle: 30, fontSize: '10px'},
						  autoscale:true,
						  ticks: <?php echo json_encode($product_history_ticks) ?>
					  },
					  yaxis: {
						autoscale:true,
						padMin: 0,
						min: 0,
						tickOptions: {formatString: '%d'}
					  }
					},
					highlighter: {
						show: true,
						sizeAdjust: 7.5,
						formatString: 'On %s was ordered %s these products'
					},
					series: [
						{
							lineWidth:2,
							fill: true,
							fillAndStroke: true,
							color:'#96c703',
							fillColor: '#b6e723',
							fillAlpha: 0.5,
							label:'Orders',
							markerOptions: { style:'circle'}
						}
					]
				  });
				});
		</script>
		<?php
        $content .= ob_get_clean();
        return $content;
	}
	
	
	private function getDateRange($id = null)
	{
		$id = (!empty($id)) ? '-' . $id : '';
		$from = (!empty($_POST['dr_from' . $id])) 
			? date('Y-m-d', strtotime($_POST['dr_from' . $id])) 
			: date('Y-m-d', time() - (86400 * 20));
		$to = (!empty($_POST['dr_to' . $id])) 
			? date('Y-m-d', strtotime($_POST['dr_to' . $id])) 
			: date('Y-m-d');
		return array($from, $to);
	}
	
	
	private function getDateRangeSelector($id = null)
	{
		$id = (!empty($id)) ? '-' . $id : '';
		$from = (!empty($_POST['dr_from' . $id])) ? h(trim($_POST['dr_from' . $id])) : date('Y/m/d', time() - (86400 * 20));
		$to = (!empty($_POST['dr_to' . $id])) ? h(trim($_POST['dr_to' . $id])) : date('Y/m/d');
		$content = '<div class="buttons long">
						<form id="date_range' . $id . '" action="" method="POST">
						<label>From: </label> 
						<input class="tcal" id="dr_from' . $id . '" type="text" name="dr_from' . $id . '" value="' . $from . '" />
						<label>To: </label> 
						<input class="tcal" id="dr_to' . $id . '" type="text" name="dr_to' . $id . '" value="' . $to . '" />
						<input type="submit" name="send" class="save-button" value="' . __('Apply') . '" />
						</form>
					</div><div class="clear"></div>';
		return $content;
	}


    private function __getProductsFilters()
    {
        // Categories
        $category_filter = $this->getModelFilter('shopCategories', 'category_id');
        // Vendors
        $vendor_filter = $this->getModelFilter('shopVendors', 'vendor_id');
        // Attributes group
        $attr_group_filter = $this->getModelFilter('shopAttributesGroups', 'attributes_group_id');

        $content = '<div class="warning clean"><form action="'
            . $this->getCurrentUrl(array('category_id', 'vendor_id', 'attributes_group_id', 'filters')) . '" type="GET">' . "\n"
            . '<div class="float-block"><h3>' . __('Category') . '</h3>' . $category_filter . "</div>\n"
            . '<div class="float-block"><h3>' . __('Vendor') . '</h3>' . $vendor_filter . "</div>\n"
            . '<div class="float-block"><h3>' . __('Attributes group') . '</h3>' . $attr_group_filter . "</div>\n"
            . '<input type="hidden" name="order" value="' . @$_GET['order'] . '" />' . "\n"
            . '<input class="save-button" type="submit" value="' . __('Apply') . '" /></form><div class="clear"></div></div>' . "\n";

        return $content;
    }


    private function __getOrdersFilters()
    {
        // Categories
        $delivery_type_filter = $this->getModelFilter('shopDeliveryTypes', 'delivery_type_id');
        // Vendors
        $status_filter = '<select name="filters[status]">' 
			. '<option value="process">Process</option>'
			. '<option value="delivery">Delivery</option>'
			. '<option value="complete">Complete</option>'
			. '</select>';


        $content = '<div class="warning clean"><form action="'
            . $this->getCurrentUrl(array('delivery_type_id', 'status', 'filters')) . '" type="GET">' . "\n"
            . '<div class="float-block"><h3>' . __('Delivery type') . '</h3>' . $delivery_type_filter . "</div>\n"
            . '<div class="float-block"><h3>' . __('Status') . '</h3>' . $status_filter . "</div>\n"
            . '<input type="hidden" name="order" value="' . @$_GET['order'] . '" />' . "\n"
            . '<input class="save-button" type="submit" value="' . __('Apply') . '" /></form><div class="clear"></div></div>' . "\n";

        return $content;
    }
	

    private function getModelFilter($model_name, $field_name, $selected = false, $as_single_field = false)
    {
        $Register = Register::getInstance();
        $model = $Register['ModManager']->getModelInstance($model_name);
        $entities = $model->getCollection();
        $filter = '';
		$name = ($as_single_field === true) ? $field_name : 'filters[' . $field_name . ']';
        if ($entities) {
            foreach ($entities as $entity) {
                $filter .= '<option value="' . $entity->getId() . '"' 
					. (($selected && $selected === $entity->getId()) ? ' selected="selected"' : '') 
					. '>' . h($entity->getTitle()) . '</option>';
            }
        }
        if (!empty($filter))
            $filter = '<select name="' . $name . '">' . $filter . '</select>';
        return $filter;
    }


    private function getTreeNode($array, $id = false) {
        $out = array();
        foreach ($array as $key => $val) {
            if ($id === false && !$val->getParent_id()) {
                $out[$val->getId()] = array(
                    'category' => $val,
                    'subcategories' => $this->getTreeNode($array, $val->getId()),
                );
                unset($array[$key]);
            } else {

                if ($val->getParent_id() == $id) {
                    $out[$val['id']] = array(
                        'category' => $val,
                        'subcategories' => $this->getTreeNode($array, $val->getId()),
                    );
                    unset($array[$key]);
                }
            }
        }
        return $out;
    }


    private function buildCatsList($catsTree, $catsList, $indent = '') {
        $popups = '';
        $content = '';
        $Register = Register::getInstance();
        $acl_groups = $Register['ACL']->get_group_info();

        foreach ($catsTree as $node) {
            $cat = $node['category'];
            $no_access = ($cat->getNo_access() !== '') ? explode(',', $cat->getNo_access()) : array();

            $_catList = (count($catsList)) ? $catsList : array();
            $cat_selector = '<select name="parent_id" id="cat_secId">';
            $cat_selector .= '<option value="0">&nbsp;</option>';
            foreach ($_catList as $selector_result) {
                if ($selector_result->getId() === $cat->getId()) continue;
                $selected = ($cat->getParent_id() === $selector_result->getId()) ? ' selected="selected"' : '';
                $cat_selector .= '<option value="' . $selector_result->getId()
                    . '"' . $selected . '>' . h($selector_result->getTitle()) . '</option>';
            }
            $cat_selector .= '</select>';

            $content .= '<div class="level2">
					<div class="number">' . $cat->getId() . '</div>
					<div class="title">' . $indent . h($cat->getTitle()) . '</div>
					<div class="buttons">';

            $content .= '<a class="' . (($cat->getView_on_home() == 1) ? 'off-home' : 'on-home') . '" title="On the Home" href="'
                . $this->getUrl('category_on_home/' . $cat->getId() . '/' . (($cat->getView_on_home() == 1) ? '1' : '0'))
                . '" onClick="return _confirm();"></a>';

            $content .= '<a href="javascript://" class="edit" title="Edit" onClick="openPopup(\'' . $cat->getId() . '_cat\');"></a>
				 <a title="Delete" href="' . $this->getUrl('category_delete/' . $cat->getId())
                . '" class="delete" onClick="return _confirm();"></a>
				</div>
			<div class="posts">' . $cat->getCnt() . '</div>
		</div>';

            $popups .=	'<div id="' . $cat->getId() . '_cat" class="popup">
				<div class="top">
					<div class="title">' . __('Category editing') . '</div>
					<div onClick="closePopup(\'' . $cat->getId() . '_cat\');" class="close"></div>
				</div>
				<form action="' . $this->getUrl('category_save/' . $cat->getId()) . '" method="POST">
				<div class="items">
					<div class="item">
						<div class="left">
							' . __('Parent section') . ':
						</div>
						<div class="right">' . $cat_selector . '</div>
						<div class="clear"></div>
					</div>
					<div class="item">
						<div class="left">
							' . __('Title') . ':
						</div>
						<div class="right"><input type="text" name="title" value="' . h($cat->getTitle()) . '" /></div>
						<div class="clear"></div>
					</div>
                    <div class="item">
                        <div class="left">
                            ' . __('Discount') . ' (%):
                        </div>
                        <div class="right">
                            <input type="text" name="discount" value="' . intval($cat->getDiscount()) . '" /></div>
                        <div class="clear"></div>
                    </div>
					<div class="item">
						<div class="left">
							' . __('Access for') . ':
						</div>
						<div class="right"><table class="checkbox-collection"><tr>';
            $n = 0;
            if ($acl_groups && is_array($acl_groups)) {
                foreach ($acl_groups as $id => $group) {
                    if (($n % 3) == 0) $popups .= '</tr><tr>';
                    $checked = (in_array($id, $no_access)) ? '' : ' checked="checked"';

                    $inp_id = md5(rand(0, 99999) . $n);

                    $popups .= '<td><input id="' . $inp_id . '" type="checkbox" name="access[' . $id . ']" value="' . $id
                        . '"' . $checked . '  /><label for="' . $inp_id . '">' . h($group['title']) . '</label></td>';
                    $n++;
                }
            }
            $popups .= '</tr></table></div>
						<div class="clear"></div>
					</div>

					<div class="item submit">
						<div class="left"></div>
						<div class="right" style="float:left;">
							<input type="submit" value="' . __('Save') . '" name="send" class="save-button" />
						</div>
						<div class="clear"></div>
					</div>
				</div>
				</form>
			</div>';


            if (count($node['subcategories'])) {
                $content .= $this->buildCatsList($node['subcategories'], $catsList, $indent . '<div class="cat-indent">&nbsp;</div>');
            }
        }

        return $content;
    }


    protected function _getValidateRules()
    {
        $Register = Register::getInstance();
        $max_attach = Config::read('max_attaches', $this->module);
        if (empty($max_attach) || !is_numeric($max_attach)) $max_attach = 5;
        $rules = array(
            'edit_product' => array(
                'title' => array(
                    'required' => true,
                    'max_lenght' => 250,
                    'title' => __('Title'),
                ),
                'description' => array(
                    'required' => false,
                    'max_lenght' => Config::read('max_lenght', $this->module),
                    'title' => __('Description'),
                ),
                'article' => array(
                    'required' => false,
                    'max_lenght' => 20,
                    'title' => __('Article', 'shop'),
                ),
                'price' => array(
                    'required' => true,
                    'max_lenght' => 11,
                    'pattern' => $Register['Validate']::V_FLOAT,
                    'title' => __('Price'),
                ),
                'discount' => array(
                    'required' => false,
                    'max_lenght' => 2,
                    'pattern' => $Register['Validate']::V_INT,
                    'title' => __('Discount'),
                ),
                'category_id' => array(
                    'required' => true,
                    'max_lenght' => 11,
                    'pattern' => $Register['Validate']::V_INT,
                    'title' => __('Category'),
                ),
                'vendor_id' => array(
                    'required' => true,
                    'max_lenght' => 11,
                    'pattern' => $Register['Validate']::V_INT,
                    'title' => __('Vendor'),
                ),
                'attributes_group_id' => array(
                    'required' => true,
                    'max_lenght' => 11,
                    'pattern' => $Register['Validate']::V_INT,
                    'title' => __('Attributes group'),
                ),
                'commented' => array(
                    'title' => __('Allow comments'),
                ),
                'available' => array(
                    'title' => __('Available for viewing'),
                ),
                'view_on_home' => array(
                    'title' => __('View on home'),
                ),
                'hide_not_exists' => array(
                    'title' => __('Hide not exists'),
                ),
            ),
			'attributes_groups' => array(
				'title' => array(
                    'required' => true,
                    'max_lenght' => 250,
                    'title' => __('Title'),
				),
			),
			'attributes_group_edit' => array(
				'title' => array(
                    'required' => true,
                    'max_lenght' => 250,
                    'title' => __('Title'),
				),
				'label' => array(
                    'required' => true,
                    'max_lenght' => 250,
                    'title' => __('Label'),
				),
				'type' => array(
                    'required' => true,
                    'max_lenght' => 10,
                    'title' => __('Type'),
				),
				'params' => array(
                    'required' => false,
                    'max_lenght' => 1000,
                    'title' => __('Params'),
				),
				'is_filterable' => array(
                    'required' => false,
                    'title' => __('Is filterable'),
				),
			),
            'delivery' => array(
                'title' => array(
                    'required' => true,
                    'max_lenght' => 250,
                    'title' => __('Title'),
                ),
                'price' => array(
                    'required' => false,
                    'pattern' => $Register['Validate']::V_INT,
                    'title' => __('Price'),
                ),
                'total_for_free' => array(
                    'required' => false,
                    'pattern' => $Register['Validate']::V_INT,
                    'title' => __('Total for free delivery'),
                ),
            ),
            'vendors' => array(
                'title' => array(
                    'required' => true,
                    'max_lenght' => 250,
                    'title' => __('Title'),
                ),
                'description' => array(
                    'required' => false,
                    'title' => __('Description'),
                ),
                'discount' => array(
                    'required' => false,
                    'pattern' => $Register['Validate']::V_INT,
                    'title' => __('Discount'),
                ),
                'view_on_home' => array(),
                'hide_not_exists' => array(),
                'files__logo_image' => array(
                    'type' => 'image',
                    'max_size' => Config::read('max_attaches_size', $this->module),
				),
            ),
            'order_edit' => array(
                'date' => array(
                    'required' => true,
                    'pattern' => $Register['Validate']::V_DATETIME,
                ),
                'status' => array(
                    'required' => false,
                    'title' => __('Status'),
                ),
                'total' => array(
                    'required' => false,
                    'pattern' => $Register['Validate']::V_FLOAT,
                    'title' => __('Total'),
                ),
                'delivery_type_id' => array(
                    'required' => true,
                    'pattern' => $Register['Validate']::V_INT,
                    'title' => __('Delivery type'),
				),
                'delivery_address' => array(
                    'required' => false,
                    'title' => __('Delivery address'),
				),
                'telephone' => array(
                    'required' => true,
					'pattern' => $Register['Validate']::V_INT,
                    'title' => __('Telephone'),
				),
                'first_name' => array(
                    'required' => true,
					'pattern' => $Register['Validate']::V_TITLE,
                    'title' => __('First name'),
				),
                'last_name' => array(
                    'required' => true,
					'pattern' => $Register['Validate']::V_TITLE,
                    'title' => __('Last name'),
				),
            ),
        );

        return $rules;
    }
}

/* Поиск материалов в ассортименте
LEFT JOIN shop_attributes_content aattrs ON aattrs.product_id = a.id
LEFT JOIN shop_attributes_content battrs ON battrs.product_id = b.id
JOIN shop_attributes atr ON aattrs.attribute_id = atr.id
WHERE a.stock_id IS NOT NULL AND a.stock_id != 0 AND (aattrs.attribute_id = battrs.attribute_id AND aattrs.content != battrs.content)
GROUP BY a.id

- Search the different attributes content(for create filters)
SELECT DISTINCT (
c.content
), a.title, b.title
FROM shop_attributes_groups a
JOIN shop_attributes b ON b.group_id = a.id
LEFT JOIN shop_attributes_content c ON c.attribute_id = b.id
WHERE a.id
IN ( 1, 2 )
*/