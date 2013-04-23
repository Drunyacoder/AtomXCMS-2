<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    News Model                    |
| @copyright     ©Andrey Brykin 2010-2011      |
| @last mod      2012/02/27                    |
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
class NewsModel extends FpsModel
{
	public $Table = 'news';

    protected $RelatedEntities = array(
        'author' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'foreignKey' => 'author_id',
      	),
        'category' => array(
            'model' => 'NewsSections',
            'type' => 'has_one',
            'foreignKey' => 'category_id',
        ),
        'comments_' => array(
            'model' => 'NewsComments',
            'type' => 'has_many',
            'foreignKey' => 'entity_id',
        ),
        'attaches' => array(
            'model' => 'NewsAttaches',
            'type' => 'has_many',
            'foreignKey' => 'entity_id',
        ),
    );


}