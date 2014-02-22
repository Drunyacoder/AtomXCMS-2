<?php

class Fps_Viewer_Operator_BinaryNotIn
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
        $compiler->raw(' !in_array(');
        $this->left->compile($compiler);
        $compiler->raw(', ');
        $this->right->compile($compiler);
        $compiler->raw(') ');
    }
}