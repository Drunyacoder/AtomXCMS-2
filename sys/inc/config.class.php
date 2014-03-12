<?php
##################################################
##												##
## @Author:       Andrey Brykin (Drunya)        ##
## @Version:      1.1                           ##
## @Project:      CMS                           ##
## @package       CMS Fapos                     ##
## @subpackege    Config class                  ##
## @copyright     ©Andrey Brykin 2010-2014      ##
## @last mod.     2014/01/10                    ##
##################################################


##################################################
##												##
## any partial or not partial extension         ##
## CMS Fapos,without the consent of the         ##
## author, is illegal                           ##
##################################################
## Любое распространение                        ##
## CMS Fapos или ее частей,                     ##
## без согласия автора, является не законным    ##
##################################################


/**
* Uses for read | write | clean settings
*
* @author     Andrey Brykin
* @version    1.0
* @link       http://atomx.net
*/
class Config {


    static public $settings;


    public function __construct($path = null)
    {
		if ($path) {
			include ($path);
			self::$settings = $set;
		}
    }
	
	
	/**
	* writing settings
	*
	* @param array $set - settings for save
	*/
	static public function write($set) {
		if ($fopen=@fopen(ROOT . '/sys/settings/config.php', 'w')) {
            $data = '<?php ' . "\n" . '$set = ' . var_export($set, true) . "\n" . '?>';
			fputs($fopen, $data);
			fclose($fopen);
			return true;
		}
		return false;
	}
	
	
	/**
	* read settings
	* Examples:
	* Config::read(param, module)
	* Config::read(module.param)
	*
	* @param string $title - title of setting
	* @param string $module - parent module of setting
	*/
	public static function read($title, $module = null) {
		$set = self::$settings;
		if ($title == 'all') return $set;

		if (!empty($module)) {
			if (isset($set[$module][$title])) return $set[$module][$title];
		} else {
			
			if (false !== strpos($title, '.')) {
				$params = explode('.', $title);
				$obj = new self();
				return $obj->__find($set, $params);
				
			} else {
				if (isset($set[$title])) return $set[$title];
			}
		}
		return null;
	}
	
	
	
	/**
	 * Find value in global config
	 *
	 * @Recursive
	 * @param array $conf
	 * @param array $params
	 */
	private function __find($conf, $params) {
		$first_param = array_shift($params);
		if (!isset($conf[$first_param])) return null;
		
		// last key - only return value
		if (count($params) == 0) 
			return $conf[$first_param];
		
		// not last key - one more iteration
		return $this->__find($conf[$first_param], $params);
	}
	
}
