<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Email:        drunyacoder@gmail.com         |
| @Site:         http://atomx.net              |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @Subpackege    Match filter                  |
| @Copyright     ©Andrey Brykin 2010-2014      |
| @Last mod      2014/04/28                    |
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

class Fps_Viewer_Filter_Match {

    private $params = array();

	public function compile($value, Fps_Viewer_CompileParser $compiler)
	{
        if (empty($this->params[0])) throw new Exception('Regexp string is not exists in "Match" filter.');
        if (!is_callable($value)) throw new Exception('(Filter_Match):Value for filtering must be callable.');

        $compiler->raw('preg_match(');
        $this->params[0]->compile($compiler);
        $compiler->raw(', ');
        $value($compiler);
        $compiler->raw(')');
	}
	

    public function addParam($param)
    {
        $this->params[] = $param;
    }


	public function __toString()
	{
		$out = '[filter]:match' . "\n";
        $out .= '[params]:' . implode("<br>\n", $this->params) . "\n";
		return $out;
	}
}