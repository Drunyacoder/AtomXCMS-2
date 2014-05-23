<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Forum Model                   |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/05/21                    |
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
class ShopAttributesModel extends FpsModel
{
	public $Table = 'shop_attributes';
	
	public $allowedTypes = array('text', 'textarea', 'checkbox', 'select', 'image');

    protected $RelatedEntities = array(
        'content' => array(
            'model' => 'shopAttributesContent',
            'type' => 'has_one',
            'foreignKey' => 'attribute_id',
            'rootForeignKey' => 'product_id',
      	),
    );
	

}