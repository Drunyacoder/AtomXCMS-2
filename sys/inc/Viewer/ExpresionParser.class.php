<?php



class Fps_Viewer_ExpresionParser
{
	
	private $parser;
	private $binaryOperators;
	private $inFunc = false;

	
	
	public function __construct(Fps_Viewer_TreesParser $parser)
	{
		$this->parser = $parser;
		$this->binaryOperators = array(
			'==' => 'Fps_Viewer_Operator_BinaryEqual',
			'!=' => 'Fps_Viewer_Operator_BinaryNotEqual',
			'>=' => 'Fps_Viewer_Operator_BinaryMore',
			'<=' => 'Fps_Viewer_Operator_BinaryLess',
			'+' => 'Fps_Viewer_Operator_BinarySumm',
			'-' => 'Fps_Viewer_Operator_BinarySubtrac',
			'*' => 'Fps_Viewer_Operator_BinaryMult',
			'/' => 'Fps_Viewer_Operator_BinaryDivis',
			'%' => 'Fps_Viewer_Operator_BinaryMod',
			'in' => 'Fps_Viewer_Operator_BinaryIn',
			'notin' => 'Fps_Viewer_Operator_BinaryNotIn',
			'and' => 'Fps_Viewer_Operator_BinaryAnd',
		);
	}
	
	
	public function parseExpression($precedence = 0)
	{

		$node = $this->parsePrimaryExpression();
		$currToken = $this->parser->getStream()->getCurrent();
		

	
		switch ($currToken->getType()) {
			case Fps_Viewer_Token::OPERATOR_TYPE:
				if ('for2' === $this->parser->getEnv()) $this->parser->setEnv('for');
				
				
				$node = $this->parseOperatorExpression($node, $currToken->getValue());
				
				break;
			case Fps_Viewer_Token::BLOCK_END_TYPE:
				$node = $this->parseOperatorExpression($node, NULL);
				break;
		}
		return $node;
	}
	
	
	public function parseOperatorExpression($left, $type)
	{

		
		if (!array_key_exists($type, $this->binaryOperators)) {
			//TODO
		}
		
		$stream = $this->parser->getStream();

		// if use IF with only one parametr ( if($var) )
		if ($stream->getCurrent()->getType() == Fps_Viewer_Token::BLOCK_END_TYPE) {
			$right = $this->parsePrimaryExpression();
			return new $this->binaryOperators['==']($left, $right);
		}
		
		$stream->next();
		$token = $stream->getCurrent();
		

		

		
		
		if (!$token->test(array(Fps_Viewer_Token::VAR_START_TYPE, Fps_Viewer_Token::NUMBER_TYPE, Fps_Viewer_Token::STRING_TYPE))) {
			// TODO
		}
		
		
		// This is tmp var seting when foreach array 
		// (hwere we must use foreach->value, and not variable from current context)
		if ('for_body' === $this->parser->getEnv() || 'for' === $this->parser->getEnv()) {
			$this->parser->setStack($left->getValue());
		}
		$right = $this->parsePrimaryExpression();
		return new $this->binaryOperators[$type]($left, $right);
	}
	
	
	
    public function parsePrimaryExpression()
    {
        $token = $this->parser->getCurrentToken();

		
        switch ($token->getType()) {
            case Fps_Viewer_Token::NAME_TYPE:
                $this->parser->getStream()->next();
                switch ($token->getValue()) {
                    case 'true':
                    case 'TRUE':
                        $node = new Fps_Viewer_Node_Const(true);
                        break;

                    case 'false':
                    case 'FALSE':
                        $node = new Fps_Viewer_Node_Const(false);
                        break;

                    case 'none':
                    case 'NONE':
                    case 'null':
                    case 'NULL':
                        $node = new Fps_Viewer_Node_Const(null);
                        break;

                    default:
                        if ('(' === $this->parser->getCurrentToken()->getValue()) {
                            $node = $this->getFunctionNode($token->getValue());
                        } else {
                            $node = new Fps_Viewer_Node_Var($token->getValue());
							// Foreach->value use. Where we create new context, 
							// which work only inside FOR
                            //pr($this->parser->getEnv());
                            //pr($this->parser->getEnv());
							if (array_key_exists($token->getValue(), $this->parser->getStack())) {
                                //if ('for_body' === $this->parser->getEnv() || 'for' === $this->parser->getEnv()) {
								//    $node->setTmpContext($token->getValue());
                                //}
								
                                //if ('if' != $this->parser->getEnv() && false != $this->parser->getEnv()) {
								    $node->setTmpContext($token->getValue());
                                //}
							}
                        }
						break;
                }
                break;

            case Fps_Viewer_Token::NUMBER_TYPE:
                $this->parser->getStream()->next();
                $node = new Fps_Viewer_Node_Const($token->getValue());
                break;
				
            case Fps_Viewer_Token::BLOCK_END_TYPE:
                $node = new Fps_Viewer_Node_Const(true);
                break;

            case Fps_Viewer_Token::STRING_TYPE:
                $node = $this->parseStringExpression();
                break;

            default:
                if ($token->test(Fps_Viewer_Token::PUNCTUATION_TYPE, '[')) {
                    $node = $this->parseArrayExpression();
                } else {
                    // TODO
                }
        }

        $node = $this->postfixExpression($node);
		return $this->parser->setNode($node, $this->inFunc);
    }
	
	
	public function postfixExpression($node)
	{
        while (true) {
            $token = $this->parser->getCurrentToken();
            if ($token->getType() == Fps_Viewer_Token::PUNCTUATION_TYPE) {
                if ('.' == $token->getValue() || '[' == $token->getValue()) {
                    $node = $this->parseSubscriptExpression($node);
                } elseif ('|' == $token->getValue()) {
                    $node = $this->parseFilterExpression($node);
                } else {
                    break;
                }
            } else {
                break;
            }
        }

        return $node;
	}
	
	
	public function parseSubscriptExpression($node)
	{
		$stream = $this->parser->getStream();
		$stream->next();
		$token = $stream->getCurrent();
		$stream->expect(Fps_Viewer_Token::NAME_TYPE);
		$node->addAttr($token->getValue());
		//$stream->next();

		return $this->postfixExpression($node);
	}
	
	
	public function getFunctionNode($func)
	{
		$this->parser->getStream()->next();
		$param = $this->parser->getStream()->getCurrent();

		$this->inFunc = true;

		if (')' === $param->getValue()) return new Fps_Viewer_Node_Text($func . '()');
		
		$expr = new Fps_Viewer_Node_Function($func, $this->parseExpression());
		$this->inFunc = false;
		//pr($this->parser->getStream()); die();
		$this->parser->getStream()->next();
		return $expr;
	}
	
	
    public function parseArrayExpression()
    {
        $stream = $this->parser->getStream();
        $stream->expect(Fps_Viewer_Token::PUNCTUATION_TYPE, '[', 'An array element was expected');

        $node = new Fps_Viewer_Node_Array(array(), $stream->getCurrent()->getLine());
        $first = true;
        while (!$stream->test(Fps_Viewer_Token::PUNCTUATION_TYPE, ']')) {
            if (!$first) {
                $stream->expect(Fps_Viewer_Token::PUNCTUATION_TYPE, ',', 'An array element must be followed by a comma');

                // trailing ,?
                if ($stream->test(Fps_Viewer_Token::PUNCTUATION_TYPE, ']')) {
                    break;
                }
            }
            $first = false;

            $node->addElement($this->parseExpression());
        }
        $stream->expect(Fps_Viewer_Token::PUNCTUATION_TYPE, ']', 'An opened array is not properly closed');

        return $node;
    }


	
    public function parseStringExpression()
    {
		//$this->parser->getStream()->next();
		$param = $this->parser->getStream()->getCurrent();
		$this->parser->getStream()->next();
		
		$expr = new Fps_Viewer_Node_Text($param->getValue());
		
		//$this->parser->getStream()->next();
		return $expr;
    }
}