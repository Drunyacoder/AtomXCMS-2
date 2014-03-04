<?php
/**
 * @author      Brykin Andrey
 * @url         http://fapos.net
 * @version     1.1.0
 * @copyright   ©Andrey Brykin 2010 - 2013
 * @last mod.   2013/07/06
 *
 * Parse url path and get from him requested needed params
 * (module, action, etc.)
 */
Class Pather {

    public $Register;

	function __construct($Register) {
        $this->Register = $Register;

		$redirect = $this->Register['Config']->read('redirect');
		if (!empty($redirect)) {
			header('Location: ' . $this->Register['Config']->read('redirect') . '');
			die();
		}
		
		$url = (!empty($_GET['url'])) ? $this->decodeUrl($_GET['url']) : '';
		$params = $this->parsePath($url);
		$data = $this->callAction($params);
	}
	
	
	/**
	 *
	 */
	static public function parseRoutes($url)
	{
		$params = self::getRoutesRules();
		if (!empty($params) && is_array($params))
			return str_replace(array_keys($params), $params, $url);
		return $url;
	}
	
	
	/**
	 *
	 */
	static public function getRoutesRules()
	{
		$path = ROOT . '/sys/settings/routes.php';
		if (!file_exists($path)) return array();
		$params = include $path;
		return $params;
	}



	private function decodeUrl($url)
	{
		$params = self::getRoutesRules();
		if (!empty($params) && is_array($params))
			return str_replace($params, array_keys($params), $url);
		return $url; 
	}
	
	

    /**
     * @return array
     */
	function parsePath($url) {
		$pathParams = array();
		
		
		$url_params = parse_url('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		if (!empty($url_params['path']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
			if (false !== (strpos($url_params['path'], '//')) || substr($url_params['path'], -1) !== '/') {
				$url_params['path'] = preg_replace('#/+#', '/', $url_params['path'] . '/');
				$redirect_url = $url_params['path'] . ((!empty($url_params['query'])) ? '?' . $url_params['query'] : '');
				redirect($redirect_url);
			}
		}
		

		$url = rtrim($url, '/');
		if (empty($url)) {
			if ($this->Register['Config']->read('start_mod')) {
				$start_mod = $this->Register['Config']->read('start_mod');
				$pathParams = $this->parsePath($start_mod);
				return $pathParams;
			}
		} else {
			if ($this->Register['Config']->read('start_mod') && $url === $this->Register['Config']->read('start_mod')) {
				$this->Register['is_home_page'] = true;
			}
			
			$pathParams = explode('/', $url);
		}
		
		
		
		if (empty($pathParams)) {
			$pathParams = array(
				'pages',
				'index',
			);
		}
		
		// sort array(keys begins from 0)
		$pathParams = array_map(function($r){
			return trim($r);
		}, $pathParams);


		// Redirect from not HLU to HLU
		$Register = Register::getInstance();
		if (count($pathParams) >= 3 &&  $pathParams[1] == 'view' && $this->Register['Config']->read('hlu') == 1) {
			$hlufile = $Register['URL']->getTmpFilePath($pathParams[2], $pathParams[0]);

			if (file_exists($hlufile) && is_readable($hlufile)) {
				$hlustr = file_get_contents($hlufile);
				if (!empty($hlustr)) {
					$hlustr .= $this->Register['Config']->read('hlu_extention');
					header('HTTP/1.0 301 Moved Permanently');
					redirect('/' . $pathParams[0] . '/' . $hlustr);
				}
			}
			
		
		// inserted URL for Pages module
		} else if (count($pathParams) >= 1 && !file_exists(ROOT . '/modules/' . $pathParams[0])) {
			$pathParams = array(
				0 => 'pages',
				1 => 'index',
				2 => implode('/', $pathParams),
			);
		}


		return $pathParams;
	}

    

    /**
     * @param  $params
     * @return void
     */
	function callAction($params)
    {
		// if we have one argument, we get page if it exists or error
		if (!is_file(ROOT . '/modules/' . strtolower($params[0]) . '/index.php')) {
			$mat_id = $this->getHluId($params[0], 'pages');
			if ($mat_id && $this->Register['Config']->read('hlu') == 1) {
				$params = array(
					0 => 'pages',
					1 => 'index',
					2 => $mat_id,
				);
			} else {
				$params = array(
					0 => 'pages',
					1 => 'index',
					2 => $params[0],
				);
			}
		}

		
		include_once ROOT . '/modules/' . strtolower($params[0]) . '/index.php';
		$module = ucfirst($params[0]) . 'Module';
		if (!class_exists($module))  {
			$_GET['ac'] = 404;
			include_once ROOT . '/error.php';
			//die("Not found class " . h($module));
		}

	
		// Parse two and more arguments
		if (count($params) > 1) {
			// Human Like URL
			if ($this->Register['Config']->read('hlu_understanding') || $this->Register['Config']->read('hlu')) {
				$mat_id = $this->getHluId($params[1], $params[0]);
				if ($mat_id) {
					$params[1] = 'view';
					$params[2] = $mat_id;
				}
			}
		}


        $this->Register['dispath_params'] = $params;
		if (count($params) == 1) $params[] = 'index';
		$this->module = new $module($params);


		// Parse second argument
		if (count($params) > 1) {
			if (preg_match('#^_+#', $params[1])) {
				$_GET['ac'] = 404;
				include_once ROOT . '/error.php';
				//die('Access to action ' . h($params[1]) . ' is denied');
			}
			if (!method_exists($this->module, $params[1])) {
				$_GET['ac'] = 404;
				include_once ROOT . '/error.php';
				//die('Action ' . h($params[1]) . ' not found in ' . h($module) . ' Class.');
			}
		}


        $params = Plugins::intercept('before_call_module', $params);
		call_user_func_array(array($this->module, $params[1]), array_slice($params, 2));
	}


	/**
	 * Find relation string->id on Human Like Url
	 *
	 * @param string $string
	 * @param string $module
	 * @return int ID
	 */
	private function getHluId($string, $module) {
		$Register = Register::getInstance();
		$clean_str = substr($string, 0, strpos($string, '.'));
		
		$tmp_file = $Register['URL']->getTmpFilePath($clean_str, $module);
		if (!file_exists($tmp_file) || !is_readable($tmp_file)) return false;

		$id = file_get_contents($tmp_file);
		$id = (int)$id;
		return (is_int($id)) ? $id : false;
	}

}