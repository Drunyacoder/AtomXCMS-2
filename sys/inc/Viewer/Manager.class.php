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
	
	protected $layout = 'default';

	protected $tokensParser;

	protected $treesParser;

	protected $compileParser;

	protected $nodesTree;

    private $markersData = array();
	
	

	public function __construct(Fps_Viewer_Loader $loader)
	{
        $this->loader = $loader;
		if (!empty($this->loader->layout)) $this->layout = $this->loader->layout;

		$this->tokensParser = new Fps_Viewer_TokensParser($this->loader);
		$this->treesParser = new Fps_Viewer_TreesParser($this->loader);
		$this->compileParser = new Fps_Viewer_CompileParser($this->loader);
	}


	
	public function setLayout($layout)
	{
		$this->layout = trim($layout);
	}
	


	public function view($fileName, $context = array())
	{
		$filePath = null;
		$fileSource = $this->getTemplateFile($fileName, $filePath);
	
		
		if (!empty($this->loader->pluginsController) 
		&& is_callable(array($this->loader->pluginsController, 'intercept'))) {
			$fileSource = call_user_func(
				array($this->loader->pluginsController, 'intercept'), 
				'before_view', 
				$fileSource
			);
		}
		
		$start = getMicroTime();
		$data = $this->parseTemplate($fileSource, $context);
        $took = getMicroTime($start);
		
		call_user_func(
			array($this->loader->debug, 'addRow'),
			array('Templates', 'Compile time'), 
			array(str_replace(ROOT, '', $filePath), $took)
		);
		
		return $data;
	}



	private function executeSource($source, $context)
	{
		$context = $this->prepareContext($context);
		ob_start();
		eval('?>' . $source);
		$output = ob_get_clean();
		return $output;
	}



	public function prepareContext($context)
	{
		return array_merge($this->markersData, $context);
	}



	private function getTemplateFile($fileName, &$returnPath = null)
	{
		$returnPath = $this->getTemplateFilePath($fileName);
		return file_get_contents($returnPath);
	}
	

	
	public function getTemplateFilePath($fileName)
	{
		$template = call_user_func(array($this->loader->config, 'read'), 'template');
		$path = ROOT . '/template/' . $template . '/html/' . '%s' . '/' . $fileName;
		if (file_exists(sprintf($path, $this->layout))) $path = sprintf($path, $this->layout);
		else $path = sprintf($path, $this->loader->rootDir);
		
		return $path;
	}
	
	
	
	
	public function parseTemplate($code, $context)
	{
        // preprocess snippets
		$this->loader->snippetsParser->setSource($code);
        $this->loader->snippetsParser->preprocess();


		$this->treesParser->cleanStack();
		$tokens = $this->getTokens($code);
		$nodes = $this->getTreeFromTokens($tokens);
		$this->compileParser->clean();
		$this->compileParser->setTmpClassName($this->getTmpClassName($code));
		$this->compile($nodes);
		$sourceCode = $this->compileParser->getOutput();
		$output = $this->executeSource($sourceCode, $context);

        // replace snippets markers
        $output = $this->loader->snippetsParser->replace($output);

		return $output;
	}
	
	
	
	
	private function getTmpClassName($code)
	{
		return 'Fps_Viewer_Template_' . md5($code . rand());
	}
	


    public function setMarkers($markers)
    {
        $this->markersData = array_merge($this->markersData, $markers);
    }


	
	
	public function getTokens($code)
	{
		return $this->tokensParser->parseTokens($code);
	}
	
	
	
	
	public function getTreeFromTokens($tokens)
	{
		return $this->treesParser->parse($tokens);
	}
	
	
	
	public function compile($nodes)
	{
		return $this->compileParser->compile($nodes);
	}
}