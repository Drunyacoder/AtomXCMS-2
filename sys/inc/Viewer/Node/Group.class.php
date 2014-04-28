<?php



class Fps_Viewer_Node_Group extends Fps_Viewer_Node_Expresion
{
	private $body = array();
	
	
	public function __construct($node)
	{
		$this->body = $node;
	}
	
	
	
	public function compile(Fps_Viewer_CompileParser $compiler)
	{

        if (is_array($this->filters) && count($this->filters)) {
            $this->parseFilters($compiler);
        } else {
            $compiler->raw('(');
            $this->body->compile($compiler);
            $compiler->raw(')');
        }
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