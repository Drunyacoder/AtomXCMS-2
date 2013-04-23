<?php

class Fps_Viewer_Operator_BinaryEqual
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
        $this->left->compile($compiler);
		$compiler->raw(' == ');
        $this->right->compile($compiler);
    }


	
	public function __toString()
	{
		$out = get_class($this);
		$out .= "(\n";
		$out .= '[left]:' . (string)$this->left . "\n";
		$out .= '[right]:' . (string)$this->right . "\n";
		$out .= "\n)";
		return $out;
	}
}