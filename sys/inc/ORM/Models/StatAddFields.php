<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    StatAddFields Model           |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/04/25                    |
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
class StatAddFieldsModel extends FpsModel
{
	
    public $Table = 'stat_add_fields';

	
    protected $RelatedEntities = array(
        'content' => array(
            'model' => 'StatAddContent',
            'type' => 'has_many',
            'foreignKey' => 'field_id',
      	),
    );
}