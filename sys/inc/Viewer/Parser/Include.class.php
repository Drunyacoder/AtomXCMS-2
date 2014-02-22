<?php



class Fps_Viewer_Parser_include
{
	public $parser;


	
	public function __construct($parser)
	{
		$this->parser = $parser;
	}
	
	
	public function parse($token)
	{
		$this->parser->getStream()->next();
		$expr = $this->parser->getExpression()->parsePrimaryExpression(); // парсинг выражения


		return new Fps_Viewer_Node_Include($expr, $this->parser->getStack());
	}
}