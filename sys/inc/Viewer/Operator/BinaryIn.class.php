<?php

class Fps_Viewer_Operator_BinaryIn
{
	private $key;
	private $value;
	private $right;
	private $env;
	
	
	public function __construct($value, $right, $env = 'if', $key = null)
	{
        if ($key) $this->setKey($key);
        $value->setTmpContext($value->getValue());
		$this->value = $value;
		$this->right = $right;
		$this->env = $env;
	}
	

    public function setKey($key)
    {
        $key->setTmpContext($key->getValue());
        $this->key = $key;
    }
	
	
	public function compile(Fps_Viewer_CompileParser $compiler)
	{
		if ($this->env === 'for_definition') {
			$this->right->compile($compiler);
			$compiler->raw(' as ');
            if (is_object($this->key)) {
                $this->key->compile($compiler);
                $compiler->raw(' => ');
            }
			$this->value->compile($compiler);
		} else {
			$compiler->raw(' in_array(');
			$this->value->compile($compiler);
			$compiler->raw(', ');
			$this->right->compile($compiler);
			$compiler->raw(') ');
		}
	}
	
	
	
	
	public function __toString()
	{
		$out = '[key]:' . $this->key . "\n";
		$out .= '[value]:' . $this->value . "\n";
		$out .= '[right]:' . $this->right . "\n";
		return $out;
	}
}