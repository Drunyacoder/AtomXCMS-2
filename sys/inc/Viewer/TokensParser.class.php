<?php



class Fps_Viewer_TokensParser
{
	
	private $delimiters;
	private $regexes;
	private $state;
	private $states;
	private $position;
	private $positions;
	private $cursor;
	private $code;
	private $linenum;
	private $end;
	private $tokens;
	private $brackets;
	private $filename;

	
	
    const STATE_DATA            = 0;
    const STATE_BLOCK           = 1;
    const STATE_VAR             = 2;
    const STATE_STRING          = 3;

    const REGEX_NAME            = '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/uA';
    const REGEX_NUMBER          = '/[0-9]+(?:\.[0-9]+)?/uA';
    const REGEX_STRING          = '/"([^#"\\\\]*(?:\\\\.[^#"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/uAs';
    const REGEX_DQ_STRING_DELIM = '/"/uA';
    const REGEX_DQ_STRING_PART  = '/[^#"\\\\]*(?:(?:\\\\.|#(?!\{))[^#"\\\\]*)*/uAs';
    const PUNCTUATION           = '()[]{}?:.,|';
	
	
	public function __construct($code = '')
	{
		mb_internal_encoding("ASCII");
		mb_internal_encoding("UTF-8");

		$this->delimiters = array(
			'tag_var' => array('{{', '}}'),
			'tag_block' => array('{%', '%}'),
		);
		
		$this->regexes = array(
			'lex_var' => '#\s+' . preg_quote($this->delimiters['tag_var'][1], '/') . '#uA',
			'lex_block' => '#\s+(?:' . preg_quote($this->delimiters['tag_block'][1]) . '|' . preg_quote($this->delimiters['tag_block'][1]) . ')#uA',
			'lex_start' => '#(' . preg_quote($this->delimiters['tag_var'][0]) . '|' . preg_quote($this->delimiters['tag_block'][0]) . ')\s#us',
			'operators' => '#not in(?=[\s()])|and(?=[\s()])|not(?=[\s()])|in(?=[\s()])|\<\=|\>\=|\=\=|or(?=[\s()])|\!\=|%|\>|\+|-|\<|\=|\*#uA',
		);
	}
	
	
	
	
	public function parseTokens($code, $filename = null)
	{
		$this->state = self::STATE_DATA;
		$this->code = $this->prepareCode($code);
		$this->linenum = 1;
		$this->end = strlen($this->code);
		$this->tokens = array();
		$this->position = -1;
		$this->cursor = 0;
		

		
		

        // find all token starts in one go
        preg_match_all($this->regexes['lex_start'], $this->code, $matches, PREG_OFFSET_CAPTURE);
        $this->positions = $matches;
		while ($this->cursor < $this->end) {
			switch ($this->state) {
				case self::STATE_DATA:
					$this->lexData();
					break;
					
				case self::STATE_BLOCK:
					$this->lexBlock();
					break;

				case self::STATE_VAR:
					$this->lexVar();
					break;

				case self::STATE_STRING:
					$this->lexString();
					break;

			}
		}
        $this->pushToken(Fps_Viewer_Token::EOF_TYPE);
		
        if (!empty($this->brackets)) {
            list($expect, $lineno) = array_pop($this->brackets);
            throw new Exception(sprintf('Unclosed "%s"', $expect), $lineno, $this->filename);
        }
		

        return new Fps_Viewer_TokenStream($this->tokens, $this->filename);
	}
	
	
	
	
    protected function lexString()
    {
        if (preg_match($this->regexes['interpolation_start'], $this->code, $match, null, $this->cursor)) {
            $this->brackets[] = array($this->options['interpolation'][0], $this->lineno);
            $this->pushToken(Fps_Viewer_Token::INTERPOLATION_START_TYPE);
            $this->moveCursor($match[0]);
            $this->pushState(self::STATE_INTERPOLATION);

        } else if (preg_match(self::REGEX_DQ_STRING_PART, $this->code, $match, null, $this->cursor) && strlen($match[0]) > 0) {
            $this->pushToken(Fps_Viewer_Token::STRING_TYPE, stripcslashes($match[0]));
            $this->moveCursor($match[0]);

        } else if (preg_match(self::REGEX_DQ_STRING_DELIM, $this->code, $match, null, $this->cursor)) {

            list($expect, $lineno) = array_pop($this->brackets);
            if ($this->code[$this->cursor] != '"') {
                throw new Exception(sprintf('Unclosed "%s"', $expect), $lineno, $this->filename);
            }

            $this->popState();
            ++$this->cursor;

            return;
        }
    }
	
	
	
	
    protected function lexVar()
    {
        if (empty($this->brackets) && preg_match($this->regexes['lex_var'], $this->code, $match, null, $this->cursor)) {
            $this->pushToken(Fps_Viewer_Token::VAR_END_TYPE);
            $this->moveCursor($match[0]);
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }
	
	
	
	
	private function lexData()
	{
		if ($this->position === count($this->positions[1]) - 1) {
            $this->pushToken(Fps_Viewer_Token::TEXT_TYPE, substr($this->code, $this->cursor));
            $this->cursor = $this->end;

            return;
		}
		
        // Find the first token after the current cursor
        $position = $this->positions[0][++$this->position];
		
		
        while ($position[1] < $this->cursor) {
            if ($this->position == count($this->positions[0]) - 1) {
                return;
            }
            $position = $this->positions[0][++$this->position];
        }
		

        // push the template text first
        $text = $textContent = substr($this->code, $this->cursor, $position[1] - $this->cursor);
        if (isset($this->positions[2][$this->position][0])) {
            $text = rtrim($text);
        }
	
        $this->pushToken(Fps_Viewer_Token::TEXT_TYPE, $text);
        $this->moveCursor($textContent.$position[0]);

		
        switch ($this->positions[1][$this->position][0]) {
            case $this->delimiters['tag_block'][0]:
                $this->pushToken(Fps_Viewer_Token::BLOCK_START_TYPE);
                $this->pushState(self::STATE_BLOCK);
                break;

            case $this->delimiters['tag_var'][0]:
                $this->pushToken(Fps_Viewer_Token::VAR_START_TYPE);
                $this->pushState(self::STATE_VAR);
                break;
        }
	}
	
	
	
	
    protected function lexBlock()
    {
        if (empty($this->brackets) && preg_match($this->regexes['lex_block'], $this->code, $match, null, $this->cursor)) {
            $this->pushToken(Fps_Viewer_Token::BLOCK_END_TYPE);
            $this->moveCursor($match[0]);
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }
	
	
	
	
    protected function lexExpression()
    {
        // whitespace
        if (preg_match('/\s+/uA', $this->code, $match, null, $this->cursor)) {
            $this->moveCursor($match[0]);

            if ($this->cursor >= $this->end) {
                throw new Exception(sprintf('Unexpected end of file: Unclosed "%s"', $this->state === self::STATE_BLOCK ? 'block' : 'variable'));
            }
        }

        // operators
        if (preg_match($this->regexes['operators'], $this->code, $match, null, $this->cursor)) {
            $this->pushToken(Fps_Viewer_Token::OPERATOR_TYPE, $match[0]);
            $this->moveCursor($match[0]);
        }
        // names
        elseif (preg_match(self::REGEX_NAME, $this->code, $match, null, $this->cursor)) {
            $this->pushToken(Fps_Viewer_Token::NAME_TYPE, $match[0]);
            $this->moveCursor($match[0]);
        }
        // numbers
        elseif (preg_match(self::REGEX_NUMBER, $this->code, $match, null, $this->cursor)) {
            $number = (float) $match[0];  // floats
            if (ctype_digit($match[0]) && $number <= PHP_INT_MAX) {
                $number = (int) $match[0]; // integers lower than the maximum
            }
            $this->pushToken(Fps_Viewer_Token::NUMBER_TYPE, $number);
            $this->moveCursor($match[0]);
        }
        // punctuation
        elseif (false !== mb_strpos(self::PUNCTUATION, $this->code[$this->cursor])) {
            // opening bracket
            if (false !== mb_strpos('([{', $this->code[$this->cursor])) {
                $this->brackets[] = array($this->code[$this->cursor], $this->lineno);
            }
            // closing bracket
            elseif (false !== mb_strpos(')]}', $this->code[$this->cursor])) {
                if (empty($this->brackets)) {
                    //throw new Exception(sprintf('Unexpected "%s"', $this->code[$this->cursor]), $this->lineno, $this->filename);
					die('Error. Line ' . $this->linenum);
                }

                list($expect, $lineno) = array_pop($this->brackets);
                if ($this->code[$this->cursor] != strtr($expect, '([{', ')]}')) {
                    throw new Exception(sprintf('Unclosed "%s"', $expect), $lineno, $this->filename);
                }
            }

            $this->pushToken(Fps_Viewer_Token::PUNCTUATION_TYPE, $this->code[$this->cursor]);
            ++$this->cursor;
        }
        // strings
        elseif (preg_match(self::REGEX_STRING, $this->code, $match, null, $this->cursor)) {
            $this->pushToken(Fps_Viewer_Token::STRING_TYPE, stripcslashes(substr($match[0], 1, -1)));
            $this->moveCursor($match[0]);
        }
        // opening double quoted string
        elseif (preg_match(self::REGEX_DQ_STRING_DELIM, $this->code, $match, null, $this->cursor)) {
            $this->brackets[] = array('"', $this->lineno);
            $this->pushState(self::STATE_STRING);
            $this->moveCursor($match[0]);
        }
        // unlexable
        else {
            throw new Exception(sprintf('Unexpected character "%s"', $this->code[$this->cursor]), $this->lineno, $this->filename);
        }
    }
	
	
	
	
	private function pushToken($type, $value = '') {
        // do not push empty text tokens
        if (Fps_Viewer_Token::TEXT_TYPE === $type && '' === $value) {
            return;
        }

        $this->tokens[] = new Fps_Viewer_Token($type, $value, $this->linenum);
	}
	
	
	
	
	private function prepareCode($code)
	{
		return str_replace(array("\r\n", "\r"), "\n", $code);
	}
	
	
	
	
    protected function moveCursor($text)
    {
        $this->cursor += strlen($text);
        $this->lineno += mb_substr_count($text, "\n");
    }

	


    protected function pushState($state)
    {
        $this->states[] = $this->state;
        $this->state = $state;
    }	

	
	
	
    protected function popState()
    {
        if (0 === count($this->states)) {
            throw new Exception('Cannot pop state without a previous state');
        }

        $this->state = array_pop($this->states);
    }
	
}