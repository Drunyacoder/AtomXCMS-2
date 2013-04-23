<?php


/**
 * Represents a token stream.
 *
 */
class Fps_Viewer_TokenStream
{
    protected $tokens;
    protected $current;
    protected $filename;

    /**
     * Constructor.
     *
     * @param array  $tokens   An array of tokens
     * @param string $filename The name of the filename which tokens are associated with
     */
    public function __construct(array $tokens, $filename = null)
    {
        $this->tokens     = $tokens;
        $this->current    = 0;
        $this->filename   = $filename;
    }

    /**
     * Returns a string representation of the token stream.
     *
     * @return string
     */
    public function __toString()
    {
        return implode("\n", $this->tokens);
    }

    /**
     * Sets the pointer to the next token and returns the old one.
     *
     * @return Twig_Token
     */
    public function next()
    {
        if (!isset($this->tokens[++$this->current])) {
            throw new Exception('Unexpected end of template', -1, $this->filename);
        }

        return $this->tokens[$this->current - 1];
    }

    /**
     * Tests a token and returns it or throws a syntax error.
     *
     * @return Twig_Token
     */
    public function expect($type, $value = null, $message = null)
    {
        $token = $this->tokens[$this->current];
        if (!$token->test($type, $value)) {
            $line = $token->getLine();
            throw new Exception(sprintf('%s Unexpected token "%s" of value "%s" ("%s" expected%s)',
					$message ? $message.'. ' : '', 
					$token->getType(), 
					$token->getValue(), 
					$type, 
					$value ? sprintf(' with value "%s"', $value) : ''
				),
                $line
				//, $this->filename
            );
        }
        $this->next();

        return $token;
    }

    /**
     * Looks at the next token.
     *
     * @param integer $number
     *
     * @return Twig_Token
     */
    public function look($number = 1)
    {
        if (!isset($this->tokens[$this->current + $number])) {
            throw new Exception('Unexpected end of template', -1, $this->filename);
        }

        return $this->tokens[$this->current + $number];
    }

    /**
     * Tests the current token
     *
     * @return bool
     */
    public function test($primary, $secondary = null)
    {
        return $this->tokens[$this->current]->test($primary, $secondary);
    }

    /**
     * Checks if end of stream was reached
     *
     * @return bool
     */
    public function isEOF()
    {
        return $this->tokens[$this->current]->getType() === Fps_Viewer_Token::EOF_TYPE;
    }

    /**
     * Gets the current token
     *
     * @return Twig_Token
     */
    public function getCurrent()
    {
        return $this->tokens[$this->current];
    }

    /**
     * Gets the filename associated with this stream
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }
}
