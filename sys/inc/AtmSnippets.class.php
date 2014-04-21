<?php
/*---------------------------------------------\
|											   |
| Author:       Andrey Brykin (Drunya)         |
| Version:      1.2                            |
| Project:      CMS                            |
| package       CMS Fapos                      |
| subpackege    AtmSnippets class              |
| copyright     ©Andrey Brykin 2010-2014       |
| last mod.     2014/04/21                     |
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


/**
 * Class AtmSnippets
 */
class AtmSnippets {

    /**
     * @var Cache object
     */
    private $Cache;

    /**
     * @var array
     */
    private $snippets = array();

    /**
     * @var null|string
     */
    private $source = '';


    /**
     * @param null $tplSource
     */
    public function __construct(&$tplSource = null) {
        if (!empty($tplSource)) $this->source = &$tplSource;
        $Register = Register::getInstance();

        $this->Cache = clone $Register['Cache'];
        $this->Cache->prefix = 'snippet';
        $this->Cache->cacheDir = ROOT . '/sys/cache/snippets/';
        $this->Cache->lifeTime = 3600;
    }
	
	
	public function cleanCahe() {
		$this->Cache->clean();
	}
	
	
	public function setSource(&$tplSource) {
		$this->source = &$tplSource;
		return $this;
	}


    /**
     * @param null $tplSource
     * @return mixed|null|string
     */
    public function parse($tplSource = null) {
        if (!empty($tplSource)) {
            $this->snippets = array();
            $this->source = (string)$tplSource;
        }
        $this->preprocess();
        return $this->replace();
    }


    /**
     * @param null $tplSource
     * @return mixed|null|string
     */
    public function replace($tplSource = null) {
        $source = ($tplSource !== null) ? $tplSource : $this->source;
        if (count($this->snippets) < 1) return $source;

        $Register = Register::getInstance();
        $Model = $Register['ModManager']->getModelInstance('snippets');

		
		
        foreach ($this->snippets as $snippet) {
            $regex = '#\{\[([!]*)('.$snippet['hash'].$snippet['name'].')(\??.*)\]\}#U';
			
            preg_match_all($regex, $source, $mas);
			
			
            for ($ix_= 0; $ix_ < count($mas[2]); $ix_++) {
                // snippet params
                $params = array();
                if (!empty($mas[3][$ix_])) {
                    preg_match_all('#([\w]+)=([^&]+)#', $mas[3][$ix_], $matches);

                    if (!empty($matches)) {
                        foreach ($matches[1] as $k => $v) {
                            $params[$v] = $matches[2][$k];
                        }
                    }
                }

				
                if ($snippet['cached']) {
                    $cache_key = 'snippet_' . strtolower($snippet['name']);
                    $cache_key .= (!empty($_SESSION['user']['status'])) ? '_' . $_SESSION['user']['status'] : '_guest';

                    if ($this->Cache->check($cache_key)) {
                        $res = $this->Cache->read($cache_key);
                        $source = preg_replace('#' . preg_quote($mas[0][$ix_]) . '#', $res, $source);
                        continue;
                    }
                }


                // get snippet from data base
                $db_snippet = $Model->getByName($snippet['name']);

                // execute snippet and replace marker in template
                if ($db_snippet) {
                    ob_start();
                    eval($db_snippet->getBody());
                    $res = ob_get_contents();
                    ob_end_clean();
                    $source = preg_replace('#' . preg_quote($mas[0][$ix_]) . '#', $res, $source);
					
                    if ($snippet['cached'])
                        $this->Cache->write($res, $cache_key, array());
                }
            }
        }

        return $source;
    }


    /**
     * @return mixed
     */
    public function preprocess() {
        $this->__findBlocks()->__markBlocks();
		return $this;
    }


    /**
     * @return $this
     */
    private function __findBlocks() {
        preg_match_all('#\{\[([!]*)([\d\w]+?)(\??.*)\]\}#U', $this->source, $mas);

        for ($i = 0; $i < count($mas[2]); $i++) {
            $block_name = $mas[2][$i];
            $cached = ($mas[1][$i] === '!') ? false : true;


            $snippet_params = array(
                'name' => strtolower($block_name),
                'definition' => $mas[0][$i],
                'cached' => $cached,
                'params' => $mas[3][$i]
            );
            $snippet_params['hash'] = $this->__getBlockHash($snippet_params, $i);
            array_push($this->snippets, $snippet_params);
        }

        return $this;
    }


    /**
     * @return $this
     */
    private function __markBlocks() {
        if (count($this->snippets) < 1) return $this;

        foreach ($this->snippets as $snippet) {
            $snippet_marker = str_replace(
                $snippet['name'],
                $snippet['hash'] . $snippet['name'],
                $snippet['definition']);

            $this->source = str_replace(
                $snippet['definition'],
                $snippet_marker,
                $this->source);
        }
        return $this;
    }


    /**
     * @param array $snippet
     * @return string
     */
    private function __getBlockHash($snippet, $number) {
        return md5($snippet['definition']) . $number;
    }
}