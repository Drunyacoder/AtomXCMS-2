<?php

class Fps_Viewer_Manager
{

	
	protected $moduleTitle;
	protected $layout;
	protected $tokensParser;
	protected $treesParser;
	protected $compileParser;
	protected $nodesTree;
    private $markersData = array();
	
	

	public function __construct(Module $instance = null)
	{
		if (null !== $instance) {
			$this->moduleTitle = $instance->module;
			$this->layout = $instance->template;
		}
		
		$this->tokensParser = new Fps_Viewer_TokensParser();
		$this->treesParser = new Fps_Viewer_TreesParser();
		$this->compileParser = new Fps_Viewer_CompileParser();
	}
	
	
	
	public function setLayout($layout)
	{
		$this->layout = trim($layout);
	}
	
	
	public function setModuleTitle($title)
	{
		$this->moduleTitle = trim($title);
	}
	
	
	
	
	public function view($fileName, $context = array())
	{
		$fileSource = $this->getTemplateFile($fileName);
		
		// TODO
		$Register = Register::getInstance();
		
		// Maybe I need upgrade this code (TODO)
		$fileSource = Plugins::intercept('before_view', $fileSource);
		
		$fileSource = $Register['DocParser']->parseSnippet($fileSource);
		
		$data = $this->parseTemplate($fileSource, $context);
		
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
	
	
	
	
	private function getTemplateFile($fileName)
	{
		$path = $this->getTemplateFilePath($fileName);
		return file_get_contents($path);
	}
	
	
	
	
	public function getTemplateFilePath($fileName)
	{
		$Register = Register::getInstance();
		$path = ROOT . '/template/' . $Register['Config']->read('template') . '/html/' . '%s' . '/' . $fileName;
		if (file_exists(sprintf($path, $this->layout))) $path = sprintf($path, $this->layout);
		else $path = sprintf($path, 'default');
		
		return $path;
	}
	
	
	
	
	public function parseTemplate($code, $context)
	{
		$tokens = $this->getTokens($code);
		//pr($tokens); //die();
		$nodes = $this->getTreeFromTokens($tokens);
		//pr(h($nodes)); //die();
		$this->compileParser->clean();
		$this->compileParser->setTmpClassName($this->getTmpClassName($code));
		$this->compile($nodes);
		$sourceCode = $this->compileParser->getOutput();
		//pr(h($sourceCode)); //die();
		

		$output = $this->executeSource($sourceCode, $context);
		//pr($sourceCode); 
		//pr($context); die();
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