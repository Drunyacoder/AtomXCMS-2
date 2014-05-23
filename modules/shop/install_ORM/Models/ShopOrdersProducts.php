<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    ShopOrdersProducts Model      |
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
class ShopOrdersProductsModel extends FpsModel
{
	public $Table = 'shop_orders_products';

    protected $RelatedEntities = array(
        'product' => array(
            'model' => 'shopProducts',
            'type' => 'has_one',
            'internalKey' => 'product_id',
      	),
    );
	

}