<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    BlogAddFields Model           |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod      2014/05/07                    |
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
class BlogAddFieldsModel extends FpsModel
{
	
    public $Table = 'blog_add_fields';

	
    protected $RelatedEntities = array(
        'content' => array(
            'model' => 'BlogAddContent',
            'type' => 'has_many',
            'foreignKey' => 'field_id',
      	),
    );
}