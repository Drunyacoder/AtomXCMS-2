<?php



class Fps_Viewer_Node_Group
{
	private $body = array();

	private $filters = array();
	
	
	public function __construct($node)
	{
		$this->body = $node;
	}
	
	
	
	public function compile(Fps_Viewer_CompileParser $compiler)
	{
		$compiler->raw('(');
		if (is_array($this->filters) && count($this->filters)) {
			foreach ($this->filters as $filter) {
				$filter->compile($this->body, $compiler);
			}
		} else {
			$this->body->compile($compiler);
		}
		$compiler->raw(')');
	}
	
	
	
	public function addElement($element)
	{
		$this->keys[] = $element;
	}
	
	
	public function addFilter($filter)
	{
		$this->filters[] = $filter;
	}
	
	
	protected function _keysCallback($markers, $key)
	{
		return $key->compile($markers, true);
	}
	
	
	
	public function __toString()
	{
		$out = '[body]:' . "\n";
		$out .= get_class($this->body) . ':' . $this->body . "\n";
		
		$out .= '[filters]:';
		if (!empty($this->filters)) {
			$out .= '(' . "\n";
			foreach ($this->filters as $key => $node) {
				$out .= get_class($node) . ':' . $node . "\n";
			}
			$out .= ')' . "\n";
		}
		return $out;
	}
}