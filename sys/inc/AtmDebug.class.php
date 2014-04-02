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


    public static function addRow($meta, $row){
        if (is_array($meta)) {
            $head_row = $meta;
        } else {
            $head_row = array($meta);
            if (count($row) > 1) {
                $head_row = array_merge($head_row, array_fill(1, (count($row) - 1), ''));
            }
        }
        $title = $head_row[0];

        if (empty(self::$data[$title])) self::$data[$title] = array('meta' => $head_row, 'data' => array());
        self::$data[$title]['data'][] = $row;
    }


    public static function getBody(){
        $result = '';
        foreach (self::$data as $title => $data) {
            $result .= '<table border="1" style="width:90%; background:#eee; font-size:12px; color:#222; text-align:left; border-collapse:collapse;">';

            // head line
            if (is_array($data['meta']) && count($data['meta'])) {
                $result .= '<tr>';
                foreach ($data['meta'] as $cell) {
                    $result .= '<th style="background:#bdb; padding:7px;">' . h($cell) . '</th>';
                }
                $result .= '</tr>';
            }

            // information lines
            if (is_array($data['data']) && count($data['data'])) {
                foreach ($data['data'] as $row) {
                    $result .= '<tr>';
                    foreach ($row as $cell) {
                        $result .= '<td style="padding:7px;">' . h($cell) . '</td>';
                    }
                    $result .= '</tr>';
                }
            }

            $result .= '</table>';
        }

        return '<div style="clear:both;"></div>' . $result;
    }
	
	
	public static function log($data, $sufix = '')
	{
		$sufix = (string)$sufix;
		$path = ROOT . '/sys/logs/debug_log_' . $sufix . '.dat';
		$f = fopen($path, 'a+');
		fwrite($f, $data);
		fclose($f);
	}
}




