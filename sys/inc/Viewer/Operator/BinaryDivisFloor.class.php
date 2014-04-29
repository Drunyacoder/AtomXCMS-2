<?php

class Fps_Viewer_Operator_BinaryDivisFloor
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
        $compiler->raw('floor(');
        $this->left->compile($compiler);
   		$compiler->raw(' / ');
        $this->right->compile($compiler);
        $compiler->raw(')');
   	}
}