<?php
/*-----------------------------------------------\
| 												 |
|  @Author:       Andrey Brykin (Drunya)         |
|  @Version:      0.6                            |
|  @Project:      CMS                            |
|  @package       CMS Fapos                      |
|  @subpackege    Modules manager                |
|  @copyright     ©Andrey Brykin 2010-2012       |
|  @last mod.     2012/02/27                     |
\-----------------------------------------------*/

/*-----------------------------------------------\
| 												 |
|  any partial or not partial extension          |
|  CMS Fapos,without the consent of the          |
|  author, is illegal                            |
|------------------------------------------------|
|  Любое распространение                         |
|  CMS Fapos или ее частей,                      |
|  без согласия автора, является не законным     |
\-----------------------------------------------*/





class ModulesManager
{

	private $accessFile;
	
	
	public function __construct($path = false)
	{
		if ($path) {
			$this->accessFile = $path;
		} else {
			$this->accessFile = ROOT . '/sys/settings/modules_access.php';
		}
	}
	
	
	public function getAddFieldsAllowedModules()
	{
		$path = $this->accessFile;
		include $path;
		return (!empty($FpsAllowModules['addFields'])) ? $FpsAllowModules['addFields'] : array();
	}
	
	public function getAddFieldsAllowedModulesTitles()
	{
		$path = $this->accessFile;
		include $path;
		return (!empty($FpsAllowModules['addFields'])) ? $FpsAllowModules['addFieldsTitles'] : array();
	}
	
	
	public function getCategoriesAllowedModules()
	{
		$path = $this->accessFile;
		include $path;
		return (!empty($FpsAllowModules['categories'])) ? $FpsAllowModules['categories'] : array();
	}
	
	
	/**
	 * Maybe TODO
	 */
	public function getModelName($modelName)
	{
        return ucfirst($modelName) . 'Model';
		//$modelClassName = ucfirst(strtolower($module)) . 'Model';
        //$modelClassName = $this->_removeUnderLine($modelClassName);
		//return $modelClassName;
	}
	
	
	public function getModelInstance($modelName)
	{
		$modelName = $this->getModelName($modelName);
		return new $modelName;
	}


    public function getModelNameFromModule($moduleName)
    {
        $ModelName = $this->_removeUnderLine($moduleName);
        $ModelName = $this->getModelName($ModelName);
        return $ModelName;
    }

	
	public function getEntityNameFromModel($modelName)
	{
		//$entityClassName = ucfirst(strtolower($module)) . 'Entity';
        //$entityClassName = $this->_removeUnderLine($entityClassName);
        $entityClassName = str_replace('Model', 'Entity', $modelName);
		return $entityClassName;
	}



    public function getEntityName($entityName)
   	{
        return ucfirst($entityName) . 'Entity';
   	}


    public function getEntityInstance($entityName)
    {
        $className = $this->getEntityName($entityName);
        return new $className;
    }



    public function getModelNameFromEntity($entityName)
    {
        $modelName = str_replace('Entity', 'Model', $entityName);
        return $modelName;
    }


    private function _removeUnderLine($str)
    {
        $str = explode('_', $str);
        $str = array_map('ucfirst', $str);
        $str = implode('', $str);
        return $str;
    }
}