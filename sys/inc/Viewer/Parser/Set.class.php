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

        $this->parser->setEnv('set_left');
        $left = $this->parser->getExpression()->parsePrimaryExpression();

        $this->parser->getStream()->expect(Fps_Viewer_Token::OPERATOR_TYPE);

        $this->parser->setEnv('set_right');
        $right = $this->parser->getExpression()->parsePrimaryExpression();

        $this->parser->setEnv(false);
		$this->parser->setStack($left->getValue());

		return new Fps_Viewer_Node_Set($left, $right);
	}
}