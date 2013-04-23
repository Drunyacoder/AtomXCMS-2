<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Stat Model                    |
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
class StatModel extends FpsModel
{
	public $Table = 'stat';

    protected $RelatedEntities = array(
        'author' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'foreignKey' => 'author_id',
      	),
        'category' => array(
            'model' => 'StatSections',
            'type' => 'has_one',
            'foreignKey' => 'category_id',
        ),
        'comments_' => array(
            'model' => 'StatComments',
            'type' => 'has_many',
            'foreignKey' => 'entity_id',
        ),
        'attaches' => array(
            'model' => 'StatAttaches',
            'type' => 'has_many',
            'foreignKey' => 'entity_id',
        ),
    );


}