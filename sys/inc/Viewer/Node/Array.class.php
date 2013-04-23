<?php



class Fps_Viewer_Node_Array
{


	private $keys = array();

	public function __construct()
	{

	}
	
	
	
	public function compile(Fps_Viewer_CompileParser $compiler)
	{
		$compiler->raw('array(');
		if (is_array($this->keys) && count($this->keys)) {
			$i = 1;
			foreach ($this->keys as $key => $val) {
			
			
				$compiler->raw($key)->raw(' => ');
				$val->compile($compiler);
				if ($i < count($this->keys)) $compiler->raw(', ');
				
				
				$i++;
			}
		}
		$compiler->raw(")");
	}
	
	
	
	public function addElement($element)
	{
		$this->keys[] = $element;
	}
	
	
	
	protected function _keysCallback($markers, $key)
	{
		return $key->compile($markers, true);
	}
	
	
	
	public function __toString()
	{
		$out = '[keys]:' . implode(', ', $this->keys) . "\n";
		return $out;
	}
}