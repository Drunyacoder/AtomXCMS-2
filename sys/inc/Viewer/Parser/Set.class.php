<?php



class Fps_Viewer_Parser_Set
{
	public $parser;


	
	public function __construct($parser)
	{
		$this->parser = $parser;
	}
	
	
	public function parse($token)
	{
		
		$this->parser->getStream()->next();
		$node = $this->parser->getStream()->getCurrent();
		$this->parser->setEnv('set');
		$expr = $this->parser->getExpression()->parseExpression(); // парсинг выражения
		$this->parser->setEnv(false);

		$this->parser->setStack($node->getValue());
		return new Fps_Viewer_Node_Set($expr, $this->parser);
	}
}