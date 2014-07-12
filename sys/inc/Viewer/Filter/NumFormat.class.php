<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Email:        drunyacoder@gmail.com         |
| @Site:         http://atomx.net              |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @Subpackege    NumFormat filter              |
| @Copyright     ©Andrey Brykin 2010-2014      |
| @Last mod      2014/04/29                    |
|----------------------------------------------|
|											   |
| any partial or not partial extension         |
| CMS Fapos,without the consent of the         |
| author, is illegal                           |
|----------------------------------------------|
| Любое распространение                        |
| CMS Fapos или ее частей,                     |
| без согласия автора, является не законным    |
\---------------------------------------------*/

class Fps_Viewer_Filter_NumFormat {

    private $params = array();

	public function compile($value, Fps_Viewer_CompileParser $compiler)
	{
        if (empty($this->params[0])) throw new Exception('First parameter is not exists in "NumFormat" filter.');
        if (!is_callable($value)) throw new Exception('(Filter_NumFormat):Value for filtering must be callable.');


        $compiler->raw('number_format(');
        $value($compiler);
        $compiler->raw(', ');
        foreach ($this->params as $k => $param) {
            $param->compile($compiler);
            if (($k + 1) < count($this->params)) $compiler->raw(', ');
        }
        $compiler->raw(')');
	}
	

    public function addParam($param)
    {
        $this->params[] = $param;
    }


	public function __toString()
	{
		$out = '[filter]:num_format' . "\n";
        $out .= '[params]:' . implode("<br>\n", $this->params) . "\n";
		return $out;
	}
}