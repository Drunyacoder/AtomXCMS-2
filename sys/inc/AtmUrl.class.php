<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Fps URL class                 |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod.     2014/02/09                    |
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
 * @version       1.0.0
 * @author        Andrey Brykin
 * @url           http://fapos.net
 */
class AtmUrl {

	public function getEntryUrl($material, $module){
		$matId = $material->getId();
		$matTitle = $material->getTitle();
		
		
		if (empty($matId)) 
			trigger_error('Empty material ID', E_USER_ERROR);
			
		if (Config::read('hlu') != 1 || empty($matTitle)) {
			$url = $module . '/view/' . $matId;
			return $url;
		}
		
		// extention
		$extention = '';
		$hlu_extention = Config::read('hlu_extention');
		if (!empty($hlu_extention)) {
			$extention = $hlu_extention;
		}
		
		// URL pattern
		$pattern = '/' . $module . '/%s' . $extention;
		
		
		// Check tmp file with assocciations and build human like URL
		clearstatcache();
		$tmp_file = $this->getTmpFilePath($matId, $module);
		
		
		if (file_exists($tmp_file) && is_readable($tmp_file)) {
			$title = file_get_contents($tmp_file);
			if (!empty($title)) {			
			
				$tmp_file_2 = $this->getTmpFilePath($title, $module);
				if (!file_exists($tmp_file_2)) {
					file_put_contents($tmp_file_2, $matId);
				}
				return h(sprintf($pattern, $title));
			}
		}
		
		
		$title = $this->translit($matTitle);
		$title = strtolower(preg_replace('#[^a-z0-9]#i', '_', $title));
		
		
		// Colission protect
		$tmp_file_title = $this->getTmpFilePath($title, $module);
		while (file_exists($tmp_file_title)) {
			$collision = file_get_contents($tmp_file_title);
			if (!empty($collision) && $collision != $matId) {
				$title .= '_';
				$tmp_file_title = $this->getTmpFilePath($title, $module);
			
			} else {
				$tmp_file_title_flag = true;
				break;
			}
		}

		
		file_put_contents($tmp_file, $title);
		if (empty($tmp_file_title_flag)) 
			file_put_contents($this->getTmpFilePath($title, $module), $matId);
		return h(sprintf($pattern, $title));
	}
	
	
	/**
	 * Only create HLU URL by title
	 *
	 * @param stirng title
	 * @return string
	 */
	public function getUrlByTitle($title) {
		$title = $this->translit($title);
		$title = strtolower(preg_replace('#[^a-z0-9]#i', '_', $title));
		$hlu_extention = Config::read('hlu_extention');
		return $title . $hlu_extention;
	}
	
	
	public function getTmpFilePath($filename, $module) {
		$padStr = str_pad($filename, 8, 0, STR_PAD_LEFT);
		$dir1 = substr($padStr, 0, 4);

		$tmp_dir = ROOT . '/sys/tmp/hlu_' . $module . '/' . $dir1 . '/';
		$tmp_file = $tmp_dir . $filename . '.dat';
		touchDir($tmp_dir, 0777);
		
		return $tmp_file;
	}
	
		
	/**
	 * Translit. Convert cirilic chars to 
	 * latinic chars.
	 *
	 * @param string $str
	 * @return string 
	 */
	public function translit($str) {
		$cirilic = array('й', 'ц', 'у', 'к', 'е', 'н', 'г', 'ш', 'щ', 'з', 'х', 'ъ', 'ф', 'ы', 'в', 'а', 'п', 'р', 'о', 'л', 'д', 'ж', 'э', 'я', 'ч', 'с', 'м', 'и', 'т', 'ь', 'б', 'ю', 'ё', 'Й', 'Ц', 'У', 'К', 'Е', 'Н', 'Г', 'Ш', 'Щ', 'З', 'Х', 'Ъ', 'Ф', 'Ы', 'В', 'А', 'П', 'Р', 'О', 'Л', 'Д', 'Ж', 'Э', 'Я', 'Ч', 'С', 'М', 'И', 'Т', 'Ь', 'Б', 'Ю', 'Ё');
		$latinic = array('i', 'c', 'u', 'k', 'e', 'n', 'g', 'sh', 'sh', 'z', 'h', '', 'f', 'y', 'v', 'a', 'p', 'r', 'o', 'l', 'd', 'j', 'e', 'ya', 'ch', 's', 'm', 'i', 't', '', 'b', 'yu', 'yo', 'i', 'c', 'u', 'k', 'e', 'n', 'g', 'sh', 'sh', 'z', 'h', '', 'f', 'y', 'v', 'a', 'p', 'r', 'o', 'l', 'd', 'j', 'e', 'ya', 'ch', 's', 'm', 'i', 't', '', 'b', 'yu', 'yo');
		
		return str_replace($cirilic, $latinic, $str);
	}


	/**
	 * Deprecated
	 */
    public function check($url) {
        if (empty($url) && $url === '/') return true;
        $url_params = parse_url('http://' . $_SERVER['HTTP_HOST'] . $url);
        if (!empty($url_params['path']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
            return (false === (strpos($url_params['path'], '//')) &&
                (preg_match('#(\.[\w\-_]+|((%20|\s)+\w+$)#umi', $url_params['path']) ||
                preg_match('#/[^/\?&\.\s(%20)]+/$#umi', $url_params['path'])));
        }
        return true;
    }


    /**
     * @param $url
     * @return string
     */
    public static function checkAndRepair($url) {
        $url_params = parse_url('http://' . $_SERVER['HTTP_HOST'] . $url);
        if (!empty($url_params['path'])) {

            // if path doesn't like file(has extension), add slash at the end
            $url_params['path'] = rtrim($url_params['path'], '/');
            if (!preg_match('#(\.[\w\-_]+$)|((%20|\s)+\w+$)#umi', $url_params['path']))
                $url_params['path'] .= '/';

            if (false !== (strpos($url_params['path'], '//'))) {
                $url_params['path'] = preg_replace('#/+#', '/', $url_params['path']);
            }
        }
        $url = $url_params['path']
            . ((!empty($url_params['query'])) ? '?' . $url_params['query'] : '')
            . ((!empty($url_params['fragment'])) ? '#' . $url_params['fragment'] : '');
        return $url;
    }


    /**
     * @param $url
     * @param bool $notRoot
     * @return mixed
     */
    public function __invoke($url, $notRoot = false, $useLang = true) {
        if ($notRoot || substr($url, 0, 7) === 'http://') return Pather::parseRoutes($url);
		
        $lang = getLang();
		$def_lang = Config::read('language');
		$root = (
			$useLang && 
			!preg_match('#^(/?sys/.+|/image/.+|/?template/|/?admin)#', $url) &&
			$lang !== $def_lang
		) 
			? '/' . WWW_ROOT . $lang . '/'
			: '/' . WWW_ROOT;
			
		$url = $root . $url;
        $url = self::checkAndRepair($url);
        return Pather::parseRoutes($url);
    }
}

