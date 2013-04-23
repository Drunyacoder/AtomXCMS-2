<?php

class Fps_Viewer_TreesParser
{

	protected $stream;
	protected $expressionParser;
	protected $tokenParsers;
	protected $nodesTree;
	protected $env;
	protected $currentValue;
	public $stack = array();

	

	
	public function __construct()
	{
		$this->expressionParser = new Fps_Viewer_ExpresionParser($this);
		$this->tokenParsers = array(
			'if' => 'Fps_Viewer_Parser_If',
			'for' => 'Fps_Viewer_Parser_For',
		);
	}
	
	
	public function parse(Fps_Viewer_TokenStream $stream, $test = null)
	{
		$this->stream = $stream;
		$rv = array();
		
		
		while (!$this->stream->isEOF()) {
			switch ($this->getCurrentToken()->getType()) {
                case Fps_Viewer_Token::TEXT_TYPE:
                    $token = $this->stream->next();
					$this->setCurrentValue($token->getValue());
                    $rv[] = $this->setNode(new Fps_Viewer_Node_Text($token->getValue()));
                    break;

                case Fps_Viewer_Token::VAR_START_TYPE:
                    $token = $this->stream->next();
                    $expr = $this->expressionParser->parseExpression();
                    $this->stream->expect(Fps_Viewer_Token::VAR_END_TYPE);
                    $rv[] = $expr;
                    break;

                case Fps_Viewer_Token::BLOCK_START_TYPE:
                    $this->stream->next();
                    $token = $this->getCurrentToken();
					
		
					if (null !== $test && call_user_func($test, $token)) {
						return $rv;
					}
					$subparser = $this->getTokenParser($token->getValue());
					//if (!$subparser) break;
					
					
					//pr($this->getCurrentToken());
                    $node = $subparser->parse($token);
                    if (null !== $node) {
                        $rv[] = $node;
                    }
                    break;

                default:
					$token = $this->stream->next();
                    // TODO
			}
		}
		
		$this->nodesTree = $rv;
		return new Fps_Viewer_NodeTree($rv);
	}
	
	
	
	public function getStack()
	{
		return $this->stack;
	}
	
	
	
	public function setStack($key)
	{
		$this->stack[$key] = true;
	}
	
	
	
	
	private function getTokenParser($value)
	{
		if (!array_key_exists($value, $this->tokenParsers)) {
			//TODO
			return '';
		}
		//echo '>';
		//var_dump($value);
		return new $this->tokenParsers[$value]($this);
	}
	
	
	
	public function getExpression()
	{
		return $this->expressionParser;
	}
	
	
	
    public function getStream()
    {
        return $this->stream;
    }
	
	
	
	
    public function getCurrentToken()
    {
        return $this->stream->getCurrent();
    }

	
	

	
	public function getEnv()
	{
		return $this->env;
	}
	
	
	
	
	public function setEnv($env)
	{
		$this->env = $env;
	}
	
	
	
	public function setCurrentValue($value)
	{
		$this->currentValue = $value;
	}
	
	
	
	public function getCurrentValue()
	{
		return $this->currentValue;
	}
	
	
	
	public function setPrint($node)
	{
		return new Fps_Viewer_Node_Print($node);
	}
	
	
	
	
	public function setNode($node, $inFunc = false)
	{
		if (!$node instanceof Fps_Viewer_Node_Var
			&& !$node instanceof Fps_Viewer_Node_Const
			&& !$node instanceof Fps_Viewer_Node_Text
			&& !$node instanceof Fps_Viewer_Node_Function
		) {
			return $node;
		}

		
		switch ($this->getEnv()) {
			case 'if':
			case 'for':
				if ($node instanceof Fps_Viewer_Node_Var) $node->setDef(true);
				//$node = new Fps_Viewer_Node($node);
				break;
				
			case 'for2';
				//$node = new Fps_Viewer_Node($node);
				break;
				
			default:
				if ($node instanceof Fps_Viewer_Node_Var) $node->setDef(true);
				if (!$inFunc) $node = $this->setPrint($node);
				//$node = new Fps_Viewer_Node($node);
				break;
		}
		
		return $node;
	}

}