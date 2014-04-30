<?php



class Fps_Viewer_Parser_For
{

	public $parser;


	
	public function __construct($parser)
	{
		$this->parser = $parser;
	}
	
	
	public function parse($token)
	{
		$this->parser->getStream()->next();

		
		$this->parser->setEnv('for_definition');
		$expr = $this->parser->getExpression()->parsePrimaryExpression();
        if ($this->parser->getStream()->test(Fps_Viewer_Token::PUNCTUATION_TYPE, array(','))) {
            $this->parser->getStream()->next();
            $expr2 = $this->parser->getExpression()->parsePrimaryExpression();
            $expr2->setKey($expr);
            $this->parser->setStack($expr->getValue());
            $expr = $expr2;
        }
		$this->parser->getStream()->expect(Fps_Viewer_Token::BLOCK_END_TYPE);
		$this->parser->setEnv('for_body');
		
		
		$body = $this->parser->parse($this->parser->getStream(), array($this, 'endWork')); 
		$this->parser->getStream()->next();

		//array_pop($this->parser->stack);
        $this->parser->setEnv(false);
		
		return new Fps_Viewer_Node_For($expr, $body);
	}
	
	

    public function endWork(Fps_Viewer_Token $token)
    {
        return $token->test(array('endfor'));
    }
	
}