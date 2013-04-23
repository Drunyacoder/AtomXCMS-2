<?php

class Fps_Viewer_Operator_BinaryDivis
{
	private $left;
	private $right;
	
	
	public function __construct($left, $right)
	{
		$this->left = $left;
		$this->right = $right;
	}
}