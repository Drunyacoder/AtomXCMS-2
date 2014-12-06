<?php
/*
@Subpackege    Append filter
@Site:         http://cms.modos189.ru/
@Package       AtomM CMS
*/

class Fps_Viewer_Filter_Append {

    private $params = array();

	public function compile($value, Fps_Viewer_CompileParser $compiler)
	{
        if (empty($this->params[0])) throw new Exception('First parameter is not exists in "Append" filter.');
        if (!is_callable($value)) throw new Exception('(Filter_Append):Value for filtering must be callable.');
        
        $compiler->raw('call_user_func(function($arr, $value) {$arr[] = $value;return $arr;},');
        $value($compiler);
        $compiler->raw(',');
        $this->params[0]->compile($compiler);
        $compiler->raw(')');
    }
	

    public function addParam($param)
    {
        $this->params[] = $param;
    }


	public function __toString()
	{
		$out = '[filter]:append' . "\n";
        $out .= '[params]:' . implode("<br>\n", $this->params) . "\n";
		return $out;
	}
}