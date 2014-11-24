<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Email:        drunyacoder@gmail.com         |
| @Site:         http://atomx.net              |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @Subpackege    Split filter                  |
| @Copyright     ©Andrey Brykin 2010-2014      |
| @Last mod      2014/04/30                    |
|----------------------------------------------|
|											   |
| any partial or not partial extension         |
| CMS AtomX,without the consent of the         |
| author, is illegal                           |
|----------------------------------------------|
| Любое распространение                        |
| CMS AtomX или ее частей,                     |
| без согласия автора, является не законным    |
\---------------------------------------------*/

class Fps_Viewer_Filter_Split {

    private $params = array();

	public function compile($value, Fps_Viewer_CompileParser $compiler)
	{
        if (empty($this->params[0])) throw new Exception('First parameter is not exists in "Split" filter.');
        if (!is_callable($value)) throw new Exception('(Filter_Split):Value for filtering must be callable.');


        $compiler->raw('explode(');
        $this->params[0]->compile($compiler);
        $compiler->raw(', ');
        $value($compiler);
        if (isset($this->params[1])) {
            $compiler->raw(', ');
            $this->params[1]->compile($compiler);
        }
        $compiler->raw(')');
	}
	

    public function addParam($param)
    {
        $this->params[] = $param;
    }


	public function __toString()
	{
		$out = '[filter]:split' . "\n";
        $out .= '[params]:' . implode("<br>\n", $this->params) . "\n";
		return $out;
	}
}