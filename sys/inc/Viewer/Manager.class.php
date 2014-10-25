<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.2                           |
| @Project:      AtomX CMS                     |
| @Package       AtomX CMS                     |
| @subpackege    VpsViewer class               |
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



class Fps_Viewer_Manager
{

	protected $loader;

    protected $templateRoot;
	
	protected $layout = 'default';

    /**
     * If the Viewer can't find a file in <viewer_root_dir>/<layout>,
     * he tried find it in <viewer_root_dir>/<defaultLayout>
     * or in <viewer_root_dir>/ if <defaultLayout> is empty.
     *
     * @var string
     */
    protected $defaultLayout = 'default';

	protected $tokensParser;

	protected $treesParser;

	protected $compileParser;

	protected $nodesTree;

    private $markersData = array();
	
	

	public function __construct(Fps_Viewer_Loader $loader)
	{
        $this->loader = $loader;
		if (!empty($this->loader->templateRoot)) $this->templateRoot = $this->loader->templateRoot;
		if (!empty($this->loader->layout)) $this->layout = $this->loader->layout;
		if (isset($this->loader->defaultLayout)) $this->defaultLayout = $this->loader->defaultLayout;


		$this->tokensParser = new Fps_Viewer_TokensParser($this->loader);
		$this->treesParser = new Fps_Viewer_TreesParser($this->loader);
		$this->compileParser = new Fps_Viewer_CompileParser($this->loader);
	}


	public function setLayout($layout)
	{
		$this->layout = trim($layout);
	}


    public function setDefaultLayout($layout)
    {
        $this->defaulLayout = trim($layout);
    }


	public function view($fileName, $context = array())
	{
		$filePath = null;
		$cached = false;
		$fileSource = $this->getTemplateFile($fileName, $filePath);
        $filePath = str_replace(ROOT, '', $filePath);

		
		// Process plugins hook. 
		// It is DI for Plugins::intercept() method.
		if (!empty($this->loader->pluginsCallback) 
		&& is_callable($this->loader->pluginsCallback)) {
			$fileSource = call_user_func(
				$this->loader->pluginsCallback, 
				'before_view', 
				$fileSource
			);
		}
		
		$start = getMicroTime();
		$data = $this->parseTemplate($fileSource, $context, $filePath, $cached);
        $took = getMicroTime($start);
		
		call_user_func(
			$this->loader->debugCallback,
			array('Templates', 'Compile time', 'Cached'), 
			array($filePath, $took, ($cached ? 'From cache' : 'Compiled'))
		);
		
		return $data;
	}


	public function prepareContext($context)
	{
		return array_merge($this->markersData, $context);
	}


    public function registerCustomFunction($function_name, $function)
    {
        Fps_Viewer_FunctionsStorage::registerFunction($function_name, $function);
    }


    public function customFunctionExists($function_name)
    {
        return Fps_Viewer_FunctionsStorage::functionExists($function_name);
    }


    public function runCustomFunction($function_name, $args)
    {
        array_unshift($args, $function_name);
        return call_user_func_array('Fps_Viewer_FunctionsStorage::run', $args);
    }

	
	public function getTemplateFilePath($fileName)
	{
		$path = $this->templateRoot . '%s' . '/' . $fileName;

		if (file_exists(sprintf($path, $this->layout)))
            $path = sprintf($path, $this->layout);
		else $path = sprintf($path, $this->defaultLayout);
		
		$path = preg_replace('#([\\/])+#', '\\1', $path);
		return $path;
	}

	
	public function parseTemplate($code, $context, $filePath = '', &$cached = false)
	{
		$key = md5($code);
        // preprocess snippets
		if (is_object($this->loader->snippetsParser)) {
			$this->loader->snippetsParser->setSource($code);
			$this->loader->snippetsParser->preprocess();
		}


		if ($this->loader->cache 
		&& call_user_func($this->loader->cache['check'], $key)) {
			$sourceCode = call_user_func($this->loader->cache['read'], $key);
			$cached = true;
			
		} else {
		
            try {
                $this->treesParser->cleanStack();
                $tokens = $this->getTokens($code, $filePath);
                $nodes = $this->getTreeFromTokens($tokens);
                $this->compileParser->clean();
                $this->compileParser->setTmpClassName($this->getTmpClassName($code));
                $this->compile($nodes);
                $sourceCode = $this->compileParser->getOutput();
                //pr(h($sourceCode)); die();

            } catch (Exception $e) {
                throw new Exception('Parse template error ('
                    . (!empty($filePath) ? h($filePath) : 'Undefined') . ':'
                    . $e->getCode() . '): ' . $e->getMessage());
            }

			
			if ($this->loader->cache) {
				call_user_func($this->loader->cache['write'], $sourceCode, $key);
			}
		}
		
		$output = $this->executeSource($sourceCode, $context);
		
        // replace snippets markers
		if (is_object($this->loader->snippetsParser)) {
			$output = $this->loader->snippetsParser->replace($output);
		}
        
		return $output;
	}


    public function setMarkers($markers)
    {
        $this->markersData = array_merge($this->markersData, $markers);
    }


    private function executeSource($source, $context)
    {
        $context = $this->prepareContext($context);
        ob_start();
        eval('?>' . $source);
        $output = ob_get_clean();
        return $output;
    }


    private function getTmpClassName($code)
    {
        return 'Fps_Viewer_Template_' . md5($code . rand());
    }


    private function getTokens($code, $filePath = '')
	{
		return $this->tokensParser->parseTokens($code, $filePath);
	}


    private function getTreeFromTokens($tokens)
	{
		return $this->treesParser->parse($tokens);
	}


    private function compile($nodes)
	{
		return $this->compileParser->compile($nodes);
	}


    private function getTemplateFile($fileName, &$returnPath = null)
    {
        $returnPath = $this->getTemplateFilePath($fileName);
        return file_get_contents($returnPath);
    }
}