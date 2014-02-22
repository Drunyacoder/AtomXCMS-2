<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Snippets Model                |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod      2014/02/21                    |
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
class SnippetsModel extends FpsModel
{
	public $Table = 'snippets';

    protected $RelatedEntities = array();

    public function getByName($name)
    {
        $Register = Register::getInstance();
        $entity = $this->getDbDriver()->select($this->Table, DB_FIRST, array('cond' => array('name' => $name)));
        if (!empty($entity[0])) {
            $entityClassName = $Register['ModManager']->getEntityNameFromModel(get_class($this));
            $entity = new $entityClassName($entity[0]);
            return $entity;
        }
        return false;
    }
}