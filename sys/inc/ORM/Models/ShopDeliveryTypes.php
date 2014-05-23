<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    ShopDeliveryTypes Model       |
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
class ShopDeliveryTypesModel extends FpsModel
{
	public $Table = 'shop_delivery_types';

    protected $RelatedEntities = array(
        'orders' => array(
            'model' => 'shopOrders',
            'type' => 'has_many',
            'foreignKey' => 'delivery_type_id',
      	),
    );
	

}