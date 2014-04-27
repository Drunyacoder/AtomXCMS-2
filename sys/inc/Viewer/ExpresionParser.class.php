<?php



class Fps_Viewer_ExpresionParser
{
	
	private $parser;
	private $binaryOperators;
	private $inFunc = 0;
	private $inIfDefinition = 0;

	
	
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
			'not in' => 'Fps_Viewer_Operator_BinaryNotIn',
			'and' => 'Fps_Viewer_Operator_BinaryAnd',
			'or' => 'Fps_Viewer_Operator_BinaryOr',
		);
	}
	
	
	public function parseExpression($precedence = 0)
	{
		$node = $this->parsePrimaryExpression();
		$currToken = $this->parser->getStream()->getCurrent();
		

		switch ($currToken->getType()) {
			case Fps_Viewer_Token::OPERATOR_TYPE:
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
        $this->inFunc++;
		if (!empty($type) && !array_key_exists($type, $this->binaryOperators)) {
			throw new Exception("Operator type '$type' is not exists.");
		}
		
		$stream = $this->parser->getStream();

		// if use IF with only one parametr ( if($var) )
		if ($stream->getCurrent()->getType() == Fps_Viewer_Token::BLOCK_END_TYPE) {
			//$right = $this->parsePrimaryExpression();
            $this->inFunc--;
			return new $this->binaryOperators['==']($left, null, true);
		}
		
		$stream->next();
		$token = $stream->getCurrent();
		
		
		// This is tmp var seting when foreach array
		if ('for_definition' === $this->parser->getEnv()) {
			$this->parser->setStack($left->getValue());
		}
		$right = $this->parsePrimaryExpression();
        $this->inFunc--;
		
		if ('for_definition' === $this->parser->getEnv() && $type === 'in') {
			return new $this->binaryOperators[$type]($left, $right, $this->parser->getEnv());
		}
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

							if (in_array($token->getValue(), $this->parser->getStack())) {
							    $node->setTmpContext($token->getValue());
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
					
				// Groups
                } else if ($token->test(Fps_Viewer_Token::PUNCTUATION_TYPE, '(')) {
					$this->inFunc++;
					$this->parser->getStream()->next();
                    $expr = $this->parseExpression();
					$node = new Fps_Viewer_Node_Group($expr);
					$this->parser->getStream()->next();
					$this->inFunc--;
                } else {
                    throw new Exception("Unexpected token type.");
                }
        }

		
        $node = $this->postfixExpression($node);


        // >2 parameters in IF block
        if ($this->parser->getCurrentToken()->test(Fps_Viewer_Token::OPERATOR_TYPE, array_keys($this->binaryOperators))) {
            $node = $this->parseOperatorExpression(
                $this->parser->setNode($node, $this->inFunc + 1),
                $this->parser->getCurrentToken()->getValue()
            );
        }

		
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
                
				//concat
				} elseif ('~' == $token->getValue()) { 
					$this->parser->getStream()->next();
					$this->inFunc++;
					$expr = $this->parsePrimaryExpression();
					$node = new Fps_Viewer_Node_Concat($this->parser->setNode($node, $this->inFunc));
					$node->addElement($expr);
					$this->inFunc--;
                } else {
                    break;
                }
            } else {
                break;
            }
        }

        return $node;
	}
	
	
	public function parseFilterExpression($node) {
		while($this->parser->getStream()->getCurrent()->test(Fps_Viewer_Token::PUNCTUATION_TYPE, array('|'))) {
			$this->parser->getStream()->next();
			$filterName = $this->parser->getStream()->getCurrent()->getValue();
			$filterName = 'Fps_Viewer_Filter_' . ucfirst($filterName);
			if (class_exists($filterName)) {
				$node->addFilter(new $filterName);
			}
			$this->parser->getStream()->next();
		}
		return $node;
	}
	
	
	public function parseSubscriptExpression($node)
	{
		$stream = $this->parser->getStream();
		$stream->getCurrent()->test(Fps_Viewer_Token::PUNCTUATION_TYPE, array('[', '.'));
		$punctuation_value = $stream->getCurrent()->getValue();
		
		switch ($punctuation_value) {
			case '[':
				$stream->next();
				$token = $stream->getCurrent();
				if (
					$token->test(Fps_Viewer_Token::NUMBER_TYPE) || 
					$token->test(Fps_Viewer_Token::STRING_TYPE)
				) {
					$node->addAttr($token->getValue());
				} else if ($token->test(Fps_Viewer_Token::NAME_TYPE)) {
					$node->addAttr(new Fps_Viewer_Node_Var($token->getValue()));
				}
				//$stream->expect(Fps_Viewer_Token::NUMBER_TYPE);
				$stream->next();
				$stream->next();
				break;
			case '.':
				$stream->next();
				$token = $stream->getCurrent();
				$stream->expect(Fps_Viewer_Token::NAME_TYPE);
				$node->addAttr($token->getValue());
				break;
		}
		
		return $this->postfixExpression($node);
	}
	
	
	public function getFunctionNode($func)
	{
		$this->parser->getStream()->next();
		$node = $this->parser->getStream()->getCurrent();

		$this->inFunc++;

		if (')' === $node->getValue()) {
			$this->parser->getStream()->next();
			$this->inFunc--;
			return new Fps_Viewer_Node_Function($func);
		}
		
		$expr = new Fps_Viewer_Node_Function($func); //$this->parsePrimaryExpression()
        $expr->addParam($this->parsePrimaryExpression());

        while ($this->parser->getStream()->getCurrent()->test(Fps_Viewer_Token::PUNCTUATION_TYPE, array(','))) {
            $this->parser->getStream()->next();
            $param = $this->parsePrimaryExpression();
            $expr->addParam($param);
        }

		$this->inFunc--;

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
		$param = $this->parser->getStream()->getCurrent();
		$this->parser->getStream()->next();
		
		$expr = new Fps_Viewer_Node_Text($param->getValue());

		return $expr;
    }
}