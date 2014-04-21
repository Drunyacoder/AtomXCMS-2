<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      0.1                           |
| @Project:      AtomX CMS                     |
| @Package       VpsViewer                     |
| @subpackege    Loader                        |
| @copyright     ©Andrey Brykin                |
| @last mod.     2014/03/12                    |
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
 * Class Fps_Viewer_Loader
 */
class Fps_Viewer_Loader
{
    /**
     * If isn't set, will be used "default" dir in current template.
     * Example: /template/current/html/MODULE/filename.html
     *
     * @var string
     */
    public $layout;

    /**
     * Used for change "[~ ID ~]" to URLs.
     * If isn't set, "[~ ID ~]" won't changed.
     *
     * @var object
     */
    public $pagesModel;

    /**
     * Used for process plugins.
     * If isn't set, plugins won't parsed
     *
     * @var string(Class name)
     */
    public $pluginsController;

    /**
     * Used for parse snippets.
     * If isn't set, snippets won't parsed
     *
     * @var object
     */
    public $snippetsParser;

    /**
     * Used for get something from @var::read();
     *
     * @var string
     */
    public $config;
	
	/**
	 * Used for debuging
	 *
	 * @var  string(Class name)
	 */
	public $debug;
	
	
	public $rootDir = 'default';
	
	public $cache = false;


	public function __construct(array $params = array())
	{
        if (class_exists('Register') && is_callable(array('Register', 'getInstance'))) {
            $Register = Register::getInstance();
            $this->layout = (!empty($params['layout'])) ? $params['layout'] : 'default';
            $this->pagesModel = $Register['ModManager']->getModelInstance('pages');
            $this->snippetsParser = (!empty($params['snippets_object'])) ? $params['snippets_object'] : new AtmSnippets;
            $this->pluginsController = (!empty($params['plugins_class'])) ? $params['plugins_class'] : 'Plugins';
            $this->config = (!empty($params['config_class'])) ? $params['config_class'] : 'Config';
            $this->debug = (!empty($params['debug_class'])) ? $params['debug_class'] : 'AtmDebug';
            $this->rootDir = (isset($params['root_dir'])) ? $params['root_dir'] : 'default';

			$cache = clone $Register['Cache'];
			$cache->prefix = 'template';
			$cache->cacheDir = ROOT . '/sys/cache/templates/';
			$cache->lifeTime = 86400;
			$this->cache = array(
				'check' => array($cache, 'check'),
				'read' => array($cache, 'read'),
				'write' => array($cache, 'write'),
			);
			
        }
	}
}