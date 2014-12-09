<?php
/*-----------------------------------------------\
| 												 |
|  @Author:       Andrey Brykin (Drunya)         |
|  @Version:      1.0.2                          |
|  @Project:      CMS                            |
|  @package       CMS AtomX                      |
|  @subpackege    Module Installer  Class        |
|  @copyright     ©Andrey Brykin 2010-2014       |
|  @last mod.     2014/05/07                     |
\-----------------------------------------------*/

/*-----------------------------------------------\
| 												 |
|  any partial or not partial extension          |
|  CMS AtomX,without the consent of the          |
|  author, is illegal                            |
|------------------------------------------------|
|  Любое распространение                         |
|  CMS AtomX или ее частей,                      |
|  без согласия автора, является не законным     |
\-----------------------------------------------*/


/**
 *
 */
class FpsModuleInstaller
{

    private $modulesPath;
	
    private $module;
	
	private $DBQueriesFile;
	private $rulesFile;
	private $settingsFile;
	private $modulesAccessFile;
	private $templateFiles;

    /**
     *
     */
    public function __construct()
    {
        $this->modulesPath = ROOT . '/modules/';
		
        $this->DBQueriesFile = 'install_db.php';
        $this->OrmFiles = 'install_ORM';
        $this->rulesFile = 'install_groups_rules.php';
        $this->settingsFile = 'install_settings.php';
        $this->modulesAccessFile = 'install_modules_access.php';
        $this->templateFiles = 'install_template';
    }



    /**
     *
     */
    public function checkNewModules()
    {
		$check = array();
	
        $modules = glob($this->modulesPath . '*', GLOB_ONLYDIR);
        if (count($modules)) {
            foreach ($modules as $module) {
                $res = $this->checkModule($module);
				if (!$res) $check[$this->module] = $module;
            }
        }
		
		return $check;
    }




    public function checkModule($modulePath)
    {
		$modTitle = $this->getModuleTitleFromPath($modulePath);
		if (!$modTitle) return false; // TODO ERROR
		
		$this->module = $modTitle;
		$Register = Register::getInstance();
		return $Register['ModManager']->isInstall($modTitle);
    }

	
	private function getModuleTitleFromPath($modulePath)
	{
		$title = strrchr((string)$modulePath, '/');
		$title = substr($title, 1);
		return ($title) ? $title : false;
	}


    /**
     * Install new a modules.
     * If you has put a new module files to modules directory,
     * you can find that module in the left-side menu.
     * Choose "install" in the module dropdown menu to start install process.
     *
     * During the installation process a some files and settings will be import
     * to the Atom.
     * Files:
     * @module/install_template/html -> @atomx_root/template/@current_template/html/@module
     * @module/install_template/css -> @atomx_root/template/@current_template/css
     */
    public function installModule($module)
    {
		$Register = Register::getInstance();
		$instmodPath = ROOT . '/modules/' . $module . '/';

		if (file_exists($instmodPath)) {
			$instDbQueries = $instmodPath . $this->DBQueriesFile;
			$instOrm = $instmodPath . $this->OrmFiles;
			$instGroupsRules = $instmodPath . $this->rulesFile;
			$instSettings = $instmodPath . $this->settingsFile;
			$instModulesAccess = $instmodPath . $this->modulesAccessFile;
			$instTemplateFiles = $instmodPath . $this->templateFiles;

            try {
                // SETTINGS IMPORT -----------------------------------
                $this->importSettings($instSettings, $module);


                // DB INSTALL ----------------------------------------
                $this->importDBQueries($instDbQueries);
                $this->importModelsAndEntities($instOrm);


                // GROUPS RULES IMPORT -------------------------------
                $this->importGroupsRules($instGroupsRules, $module);


                // MODULES ACCESS IMPORT -------------------------------
                $this->importModulesAccess($instModulesAccess);


                // MODULES TEMPLATE IMPORT -------------------------------
                $this->importTemplateFiles($instTemplateFiles, $module);
            } catch (Exception $e) {
                throw new Exception('Module installation has been stoped (' . $e->getMessage() . ')');
            }
		}
    }
	
	
	
	/**
	 *
	 */
	public function importModulesAccess($path)
	{
		$Register = Register::getInstance();
	
		if (file_exists($path)) {
			include_once $path;
			if (!empty($FpsInstallAllowModules) && is_array($FpsInstallAllowModules)) {
				$CurrAccess = $this->getCurrentModulesAccess();
				$CurrAccess = array_merge_recursive($FpsInstallAllowModules, $CurrAccess);
                foreach ($CurrAccess as $k => $v) {
                    $CurrAccess[$k] = array_unique($v);
                }
				$this->saveModulesAccess($CurrAccess, $path);
			}
		}
	}
	
	
	private function saveModulesAccess($accessList, $path)
	{
		file_put_contents(ROOT . '/sys/settings/modules_access.php', '<?php ' . "\n" . '$FpsAllowModules = ' . var_export($accessList, true) . ";\n");
	}
	
	
	public function getCurrentModulesAccess()
	{
		$path = ROOT . '/sys/settings/modules_access.php';
		include $path;
		return (!empty($FpsAllowModules)) ? $FpsAllowModules : array();
	}
	
	
	
	/**
	 *
	 */
	public function importGroupsRules($path, $module)
	{
		$Register = Register::getInstance();
	
		if (file_exists($path)) {
			include_once $path;
			if (!empty($FpsInstallRules) && is_array($FpsInstallRules)) {
				$CurrRules = $Register['ACL']->getRules();
				$CurrRules = array_merge($FpsInstallRules, $CurrRules);
				$Register['ACL']->save_rules($CurrRules);
			}
		}
	}
	
	
	
	/**
	 *
	 */
	public function importSettings($path, $module)
	{
		$Register = Register::getInstance();
	
		if (!file_exists($path)) throw new Exception("File \"install_settings.php\" for module \"$module\" not found.");
        include_once $path;
        if (!empty($FpsInstallSettings) && is_array($FpsInstallSettings)) {
            $CurrSettings = $Register['Config']->read('all');
            $CurrSettings[$module] = $FpsInstallSettings;
            $Register['Config']->write($CurrSettings);
        }
	}
	
	
	/**
	 *
	 */
	public function importDBQueries($path)
	{
		$Register = Register::getInstance();
	
		if (file_exists($path)) {
			include_once $path;
			if (!empty($FpsInstallQueries) && is_array($FpsInstallQueries)) {
				foreach ($FpsInstallQueries as $query) {
					$Register['DB']->query($query);
				}
			}
		}
	}


    public function importModelsAndEntities($path)
    {
        if (file_exists($path . '/Models')) {
            copyr($path . '/Models', ROOT . '/sys/inc/ORM/Models/');
        }
        if (file_exists($path . '/Entities')) {
            copyr($path . '/Entities', ROOT . '/sys/inc/ORM/Entities/');
        }
    }


	public function importTemplateFiles($pathToTemplateFiles, $module)
	{
        if (file_exists($pathToTemplateFiles) && is_dir($pathToTemplateFiles)) {
            $templatePath = ROOT . '/template/' . getTemplateName();
            $dirs = glob($pathToTemplateFiles . '/*', GLOB_ONLYDIR);

            foreach ($dirs as $dir) {
                $dirName = basename($dir);

                // Copying HTML templates to the special module directory
                if ($dirName === 'html') {
                    touchDir($templatePath . '/html/' . $module, 0777);
                    copyr($dir, $templatePath . '/html/' . $module, 0777);
                    continue;
                }

                touchDir($templatePath . '/' . $dirName, 0777);
                copyr($dir, $templatePath . '/' . $dirName, 0777);
            }
        }
	}
}