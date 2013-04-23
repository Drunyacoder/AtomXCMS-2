<?php



class Fps_Viewer_Parser_If
{

	public $tests;
	public $else;
	public $parser;
	public $end;

	
	public function __construct($parser)
	{
		$this->parser = $parser;
	}
	
	
	public function parse($token)
	{

		$this->parser->setEnv('if');
		$this->parser->getStream()->next();
		$expr = $this->parser->getExpression()->parseExpression(); // парсинг выражения
		

		
		$this->parser->getStream()->expect(Fps_Viewer_Token::BLOCK_END_TYPE); // если не текущий токен не соответствует типу, выполнение прерывается
		$this->parser->setEnv(false);
		$body = $this->parser->parse($this->parser->getStream(), array($this, 'continueWork')); // парсинг текста(шаблона)
		
		
		
		$tests = array($expr, $body);
		$else = null;
		$end = false;
	
		
		while (false === $end) {
			switch ($this->parser->getStream()->next()->getValue()) {
				case 'else':
					$this->parser->getStream()->expect(Fps_Viewer_Token::BLOCK_END_TYPE);
					$else = $this->parser->parse($this->parser->getStream(), array($this, 'endWork'));
					break;
				
				case 'elseif':
					$expr = $this->parser->getExpression()->parseExpression();
					$this->parser->getStream()->expect(Fps_Viewer_Token::BLOCK_END_TYPE);
					$tests[] = $expr;
					$tests[] = $this->parser->parse($this->parser->getStream(), array($this, 'continueWork'));
					break;
				
				case 'endif':
					$end = true;
					break;
			}
		}
		

		return new Fps_Viewer_Node_If($tests, $else);
	}
	
	
	public function continueWork($token)
    {
        return $token->test(array('elseif', 'else', 'endif'));
    }

    public function endWork(Fps_Viewer_Token $token)
    {
        return $token->test(array('endif'));
    }
	
}