<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    NewsAddFields Model           |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2012/03/02                    |
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
class NewsAddFieldsModel extends FpsModel
{
	
    public $Table = 'news_add_fields';

	
    protected $RelatedEntities = array(
        'content' => array(
            'model' => 'NewsAddContent',
            'type' => 'has_many',
            'foreignKey' => 'field_id',
      	),
    );
}