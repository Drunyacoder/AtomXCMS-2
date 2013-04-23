<?php
##################################################
##												##
## @Author:       Andrey Brykin (Drunya)        ##
## @Version:      0.8                           ##
## @Project:      CMS                           ##
## @package       CMS Fapos                     ##
## @subpackege    Config class                  ##
## @copyright     ©Andrey Brykin 2010-2011      ##
## @last mod.     2011/12/16                    ##
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
* @version    0.2
* @link       http://cms.develdo.com
*/
class Config {


    static public $settings;


    public function __construct($path)
    {
        include ($path);
        self::$settings = $set;
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
	*
	* @param string $title - title of setting
	* @param string $module - parent module of setting
	*/
	static public function read($title, $module = null) {
		$set = self::$settings;
		if ($title == 'all') return $set;

		if (!empty($module)) {
			if (isset($set[$module][$title])) return $set[$module][$title];
		} else {
			if (isset($set[$title])) return $set[$title];
		}
		return null;
	}
	
}
