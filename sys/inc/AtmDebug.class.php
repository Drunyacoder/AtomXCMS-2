<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      0.9                            ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    Debug class                    ##
## copyright     ©Andrey Brykin 2010-2014       ##
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



Class AtmDebug {

	public static $data;
	
	
	public static function addRow($title, $row){
		if (empty(self::$data[$title])) self::$data[$title] = array();
		self::$data[$title][] = $row;
	}
	
	
	public static function getBody(){
		$result = '';
		foreach (self::$data as $title => $rows) {
			$result .= '<tr><th style="background:#d33;" colspan="10">' . h($title) . '</th></tr>';
			foreach ($rows as $row) {
				$result .= '<tr>';
				foreach ($row as $cell) {
					$result .= '<td>' . h($cell) . '</td>';
				}
				$result .= '</tr>';
			}
		}
		
		return '<div style="clear:both;"></div>'
			.'<table style="width:90%; background:#ccc; color:#222; text-align:left;">' . $result . '</table>';
	}
}



