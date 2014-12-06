<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Snippets Model                |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod      2014/03/09                    |
|----------------------------------------------|
|											   |
| any partial or not partial extension         |
| CMS AtomX,without the consent of the         |
| author, is illegal                           |
|----------------------------------------------|
| Любое распространение                        |
| CMS AtomX или ее частей,                     |
| без согласия автора, является не законным    |
\---------------------------------------------*/



/**
 *
 */
class SnippetsModel extends FpsModel
{
	public $Table = 'snippets';

    protected $RelatedEntities = array(
    );

	public function getByName($name)
	{
        $Register = Register::getInstance();
		$entity = $this->getDbDriver()->select($this->Table, DB_FIRST, array(
			'cond' => array(
				'name' => $name
			)
		));

		if (!empty($entity[0])) {
			$entityClassName = $Register['ModManager']->getEntityNameFromModel(get_class($this));
			$entity = new $entityClassName($entity[0]);
			return (!empty($entity)) ? $entity : false;
		}
		return false;
	}
}