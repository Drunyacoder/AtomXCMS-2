<?php

class Fps_Viewer_Operator_BinaryIn
{
	private $left;
	private $right;
	private $env;
	
	
	public function __construct($left, $right, $env = 'if')
	{
		$left->setTmpContext($left->getValue());
		$this->left = $left;
		$this->right = $right;
		$this->env = $env;
	}
	
	
	
	
	public function compile(Fps_Viewer_CompileParser $compiler)
	{
		if ($this->env === 'for_definition') {
			$this->right->compile($compiler);
			$compiler->raw(' as ');
			$this->left->compile($compiler);
		} else {
			$compiler->raw(' in_array(');
			$this->left->compile($compiler);
			$compiler->raw(', ');
			$this->right->compile($compiler);
			$compiler->raw(') ');
		}
	}
	
	
	
	
	public function __toString()
	{
		$out = '[left]:' . $this->left . "\n";
		$out .= '[right]:' . $this->right . "\n";
		return $out;
	}
}