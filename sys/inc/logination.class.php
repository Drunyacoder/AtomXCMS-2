<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      0.7                            ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    Logination class               ##
## copyright     ©Andrey Brykin 2010-2011       ##
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
* Uses for read | write | clean system log
*
* @author     Andrey Brykin
* @version    0.1
* @link       http://cms.develdo.com
*/
class Logination {

	/**
	* directory for log files
	*
	* @var (str)
	*/
	public $logDir = 'system_log';
	/**
	* max size for log files
	*
	* @var (int)
	*/
	private $maxFileSize = 1000000;
	
	
	/**
	* init log
	* delete execess files ( bigest set[secure][max_log_size] )
	*
	* @return          none
	*/	
	public function __construct() {
		$max_log_size = Config::read('max_log_size', 'secure');
		/* we must no allow overflow */
		if ((int)$max_log_size > 0) {
			$log_files = glob(ROOT . '/sys/logs/' . $this->logDir . '/*.dat');
			$log_size = (!empty($log_files)) ? (count($log_files) * $this->maxFileSize) : 0;
			/* delete files and free space */
			while ($log_size > $max_log_size) {
				$first_file = array_shift($log_files);
				if (@unlink($first_file)) $log_size = ($log_size - $this->maxFileSize);
			}
		}
		/* create log dir if !exists */
		if (!file_exists(ROOT . '/sys/logs/' . $this->logDir)) mkdir(ROOT . '/sys/logs/' . $this->logDir, 0755, true);
	}
	
	
	/**
	* for write into log
	*
	* @param (str)     action.  be write into log
	* @return          none
	*/
	public function write($param, $comment = '') {
        $Register = Register::getInstance();

		clearstatcache();
		/* prepear data */
		$param_log = array();
		$param_log['date'] = date("Y-m-d H:i");
		$param_log['action'] = $param;
		$param_log['comment'] = $comment;
		if (!empty($_SESSION['user'])) {
			$param_log['user_id'] = $_SESSION['user']['id']; 
			$param_log['user_name'] = $_SESSION['user']['name'];
			//$statuses = $Register['ACL']->get_group_info();
			//$param_log['user_status'] = $statuses[$_SESSION['user']['status']];
			$param_log['user_status'] = (int)$_SESSION['user']['status'];
		}
		if (!empty($_SERVER['REMOTE_ADDR'])) $param_log['ip'] = $_SERVER['REMOTE_ADDR'];
		/* get file name for writing */
		$file_name = $this->getFileName();
		/* get records if exists */
		if (file_exists(ROOT . '/sys/logs/' . $this->logDir . '/' . $file_name)) {
			$log_data = unserialize(file_get_contents(ROOT . '/sys/logs/' . $this->logDir . '/' . $file_name));
			$log_data = array_merge($log_data, array(0 => $param_log));
		} else {
			$log_data = array();
			$log_data[] = $param_log;
		}
		$log_data = serialize($log_data);
		/* write... */
		$file = fopen(ROOT . '/sys/logs/' . $this->logDir . '/' . $file_name, 'w+');
		fwrite($file, $log_data);
		fclose($file);
		return;
	}	
		
	/**
	* read log file
	*
	* @filename (str)      file name
	* @return   (array)    file contents
	*/
	public function read($filename) {
		clearstatcache();
		$filename = ROOT . '/sys/logs/' . $this->logDir . '/' . $filename;
		if (file_exists($filename) && is_readable($filename)) {
			$data = file_get_contents($filename);
			if (!empty($data)) {
				$data = unserialize($data);
			} else {
				$data = null;
			}
		}
		return (!empty($data)) ? $data : false;
	}
		
		
	/**
	* clean all logs
	*
	* return      none
	*/
	public function clean() {
		$log_files = glob(ROOT . '/sys/logs/' . $this->logDir . '/*');
		if (!empty($log_files)) {
			foreach ($log_files as $file) {
				@unlink($file);
			}
		}
		return;
	}
	
	/**
	* geting file name for write
	*
	* @return (str)     filename
	*/
	private function getFileName($file_num = 1) {
		clearstatcache();
		$file_name = date("Y-m-d") . '_' . $file_num . '.dat';
		if (file_exists(ROOT . '/sys/logs/' . $this->logDir . '/' . $file_name)) {
			if (filesize(ROOT . '/sys/logs/' . $this->logDir . '/' . $file_name) > $this->maxFileSize) {
				$file_name = $this->getFileName(2);
			}
		}
		return $file_name;
	}
}

?>