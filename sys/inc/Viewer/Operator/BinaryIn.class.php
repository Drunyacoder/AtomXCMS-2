<?php

class Fps_Viewer_Operator_BinaryIn
{
	private $left;
	private $right;
	
	
	public function __construct($left, $right)
	{
		$left->setTmpContext($left);
		$this->left = $left;
		$this->right = $right;
	}
	
	
	
	
	public function compile(Fps_Viewer_CompileParser $compiler)
	{
		$this->right->compile($compiler);
		$compiler->raw(' as ');
		$this->left->compile($compiler);
	}
	
	
	
	
	public function __toString()
	{
		$out = '[left]:' . $this->left . "\n";
		$out .= '[right]:' . $this->right . "\n";
		return $out;
	}
}