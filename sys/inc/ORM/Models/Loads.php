<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Loads Model                   |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/05/04                    |
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
class LoadsModel extends FpsModel
{
	public $Table = 'loads';

    protected $RelatedEntities = array(
        'author' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'foreignKey' => 'author_id',
      	),
        'category' => array(
            'model' => 'LoadsSections',
            'type' => 'has_one',
            'foreignKey' => 'category_id',
        ),
        'comments_' => array(
            'model' => 'LoadsComments',
            'type' => 'has_many',
            'foreignKey' => 'entity_id',
        ),
        'attaches' => array(
            'model' => 'LoadsAttaches',
            'type' => 'has_many',
            'foreignKey' => 'entity_id',
        ),
    );


}