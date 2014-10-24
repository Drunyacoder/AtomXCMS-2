<?php



class Fps_Viewer_Node_Function
{

	private $func;
	private $params;


	public function __construct($func, $params = array())
	{
		$this->func = $func;
		$this->params = $params;
	}
	

    public function addParam($node) {
        array_push($this->params, $node);
    }
	
	
	public function compile(Fps_Viewer_CompileParser $compiler)
	{
		$compiler->raw("Fps_Viewer_FunctionsStorage::run('$this->func', ");
        while (count($this->params) > 0) {
            $node = array_shift($this->params);
            $compiler->raw($node->compile($compiler));
            if (count($this->params) > 0) $compiler->raw(", ");
        }
		$compiler->raw(")");
	}
	
	

	public function __toString()
	{
		$out = '[function_name]:' . $this->func . "\n";
		$out .= '[params]:' . implode("<br>\n", $this->params) . "\n";
		return $out;
	}
}