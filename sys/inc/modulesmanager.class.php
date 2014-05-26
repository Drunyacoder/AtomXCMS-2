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
	
	
	public function getAllowedModules($action)
	{
		$path = $this->accessFile;
		include $path;
		return (!empty($FpsAllowModules[$action])) ? $FpsAllowModules[$action] : array();
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
        if (!class_exists($modelName)) throw new Exception("Model '$modelName' not found in ModelManager::getModelInstance()");
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
	
	
	public function getModulesList($onlyInstalled = false, $onlyActivated = false)
	{
		$modules = array();
		
		$pathes = glob(ROOT . '/modules/*', GLOB_ONLYDIR);
		if (!empty($pathes)) {
			foreach ($pathes as $path) {
				$module = substr(strrchr($path, '/'), 1);
                if ($onlyActivated && !$this->isActivated($module)) continue;
                $modules[] = $module;
			}
		}
		
		return $modules;
	}


    public function moduleExists($module)
    {
        return file_exists(ROOT . '/modules/' . $module . '/index.php');
    }


    public function getSettingsControllerClassName($module)
    {
        return ucfirst($module) . 'SettingsController';
    }


    public function getSettingsControllerPath($module)
    {
        return ROOT . '/modules/' . $module . '/settings.php';
    }


    public function getTemplateParts($module)
    {
        $pathToTemplate = ROOT . '/modules/' . $module . '/info.php';
        if (file_exists($pathToTemplate)) {
            include_once $pathToTemplate;
            return !empty($allowedTemplateFiles) ? $allowedTemplateFiles : array();
        }
        return array();
    }


    public function isInstall($module)
    {
        $modSettings = Config::read($module);
        return (!empty($modSettings) && is_array($modSettings));
    }


    public function isActivated($module)
    {
        return ($this->isInstall($module) && Config::read($module . '.active'));
    }
}