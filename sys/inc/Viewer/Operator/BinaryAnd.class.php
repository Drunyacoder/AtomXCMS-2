<?php

class Fps_Viewer_Operator_BinaryAnd
{
	private $left;
	private $right;


	public function __construct($left, $right)
	{
		$this->left = $left;
		$this->right = $right;
	}


    public function compile(Fps_Viewer_CompileParser $compiler)
   	{
   		$this->right->compile($compiler);
   		$compiler->raw(' && ');
   		$this->left->compile($compiler);
   	}
}