<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    ShopOrders Model             |
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
class ShopOrdersModel extends FpsModel
{
	public $Table = 'shop_orders';

    protected $RelatedEntities = array(
        'products' => array(
            'model' => array('shopOrdersProducts', 'shopProducts'),
            'type' => 'many_to_many',
            'foreignKey' => array('order_id', 'product_id'),
      	),
        'author' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'internalKey' => 'user_id',
      	),
        'delivery_type' => array(
            'model' => 'shopDeliveryTypes',
            'type' => 'has_one',
            'internalKey' => 'delivery_type_id',
      	),
    );
	
    protected $orderParams = array(
        'allowed' => array('date', 'author.name', 'status', 'total', 'delivery_type.title', 'first_name', 'first_name', 'price'),
        'default' => 'date',
    );
}