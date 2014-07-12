<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    ShopProducts Model            |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod      2014/05/15                    |
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
class ShopProductsModel extends FpsModel
{
	public $Table = 'shop_products';

    protected $RelatedEntities = array(
        'attributes_group' => array(
            'model' => 'shopAttributesGroups',
            'type' => 'has_one',
            'internalKey' => 'attributes_group_id',
      	),
        'attributes' => array(
            'model' => array('shopAttributesGroups', 'shopAttributes'),
            'type' => 'has_many_through',
            'foreignKey' => array('attributes_group_id', 'group_id'),
        ),
        'vendor' => array(
            'model' => 'shopVendors',
            'type' => 'has_one',
            'internalKey' => 'vendor_id',
        ),
        'author' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'internalKey' => 'user_id',
        ),
        'category' => array(
            'model' => 'shopCategories',
            'type' => 'has_one',
            'internalKey' => 'category_id',
        ),
        'comments' => array(
            'model' => 'Comments',
            'type' => 'has_many',
            'foreignKey' => 'entity_id',
            'additionCond' => array("module = 'shop'"),
        ),
    );

    protected $orderParams = array(
        'allowed' => array('title', 'category.title', 'date', 'author.name', 'vendor.title', 'orders_cnt', 'comments_cnt', 'price', 'discount'),
        'default' => 'date',
    );
	
	
	public function getCategoryFilters($category_id)
	{
		if (empty($category_id) || !is_int($category_id)) return array();
		
		$attrs_groups_ids = $this->getOneField('DISTINCT(attributes_group_id)', array(
			'category_id' => intval($category_id),
		));
		if (!$attrs_groups_ids) return array();
		

		$where = array(
			'joins' => array(
				array(
					'table' => 'shop_attributes',
					'type' => '',
					'alias' => 'b',
					'cond' => array('b.group_id = a.id'),
				),
				array(
					'table' => 'shop_attributes_content',
					'type' => 'LEFT',
					'alias' => 'c',
					'cond' => array('c.attribute_id = b.id'),
				),
			),
			'alias' => 'a',
			'fields' => array('DISTINCT (c.content)', 'b.title as title', 'b.label as label'),
			'cond' => array('a.id' => $attrs_groups_ids),
		);
		$data = $this->getDbDriver()->select('shop_attributes_groups', DB_ALL, $where);
		if (!$data) return array();
		
		
		$output = array();
		$filters = $this->getCurrentFilters();
		foreach ($data as $row) {
			if (!array_key_exists($row['title'], $output))
				$output[$row['title']] = array(
					'label' => $row['label'],
					'params' => array(),
				);
			$output[$row['title']]['params'][] = array(
				'id' => $row['content'],
				'content' => $row['content'],
				'checked' => (!empty($filters[$row['title']]) && 
							in_array($row['content'], $filters[$row['title']]))
								? true : false,
			);
		}
		
		return $output;
	}
	
	
	public function getVendorsFilter($category_id = null)
	{
		$data = array();
		$conditions = (!empty($category_id)) ? array('category_id' => intval($category_id)) : array();
		$vendors_where = array(
			'joins' => array(
				array(
					'table' => 'shop_vendors',
					'type' => '',
					'alias' => 'b',
					'cond' => array('b.id = a.vendor_id'),
				),
			),
			'alias' => 'a',
			'cond' => $conditions,
			'fields' => array('b.*'),
			'group' => 'b.id',
		);
		
		$data = $this->getDbDriver()->select($this->getTable(), DB_ALL, $vendors_where);
		if (!$data) return array();
		
		
		$filters = $this->getCurrentFilters();
		$output = array('vendor' => array(
			'label' => __('Vendor'),
			'params' => array(),
		));
		foreach ($data as $row) {
			$output['vendor']['params'][] = array(
				'id' => $row['id'],
				'content' => $row['title'],
				'checked' => (!empty($filters['vendor']) && 
							in_array($row['id'], $filters['vendor']))
								? true : false,
			);
		}
		
		return $output;
	}
	
	
	public function getProductsFilterSubquery()
	{
		$filters = $this->getCurrentFilters();
		$where = array(
			'joins' => array(
				array(
					'table' => 'shop_attributes',
					'type' => '',
					'alias' => 'b',
					'cond' => array('b.id = a.attribute_id'),
				),
			),
			'fields' => array('DISTINCT(a.product_id)'),
			'cond' => array(),
			'alias' => 'a',
		);
		
		
		$query = array();
		foreach ($filters as $field => $values) {
			if (empty($values) || !is_array($values)) continue;
			
			if ($field === 'vendor') {
				$query['vendor_id'] = $values;
				continue;
			}
			
			$where_ = $where;
			$where_['cond'][] = array(
				'b.title' => $field,
				'a.content' => $values,
			);
			$query[] = 'id IN (' . $this->getDbDriver()->renderQuery('shop_attributes_content', $where_) . ')';
		}

		return $query;
	}
	
	
	public function getCurrentFilters()
	{
		return (!empty($_GET['filters']) && is_array($_GET['filters']))
			? (array)$_GET['filters'] : array();
	}
	
}