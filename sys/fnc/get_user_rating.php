<?php 
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      0.7                            ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    Geting user rating function    ##
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
* @param int posts
* @param string serialize rating settings
*/

function getUserRating($rating, $settings) {
	
	if (!is_numeric($rating)) return 'star0.gif';
	
	if (empty($settings) || !is_string($settings)) return 'star0.gif';
	
	$params = unserialize($settings);
	if (!is_array($params)) return 'star0.gif';
	
	if ( $rating < $params['cond1'] ) {
		$rank = $params['rat0'];
		$img = 'star0.gif';
	} else if ( $rating >= $params['cond1'] and $rating < $params['cond2'] ) {
		$img = 'star1.gif';
		$rank = $params['rat1'];
	} else if ( $rating >= $params['cond2'] and $rating < $params['cond3'] ) {
		$img = 'star2.gif';
		$rank = $params['rat2'];
	} else if ( $rating >= $params['cond3'] and $rating < $params['cond4'] ) {
		$img = 'star3.gif';
		$rank = $params['rat3'];
	} else if ( $rating >= $params['cond4'] and $rating < $params['cond5'] ) {
		$img = 'star4.gif';
		$rank = $params['rat4'];
	} else if ( $rating >= $params['cond5'] and $rating < $params['cond6'] ) {
		$img = 'star5.gif';
		$rank = $params['rat5'];
	} else if ( $rating >= $params['cond6'] and $rating < $params['cond7'] ) {
		$img = 'star6.gif';
		$rank = $params['rat6'];
	} else if ( $rating >= $params['cond7'] and $rating < $params['cond8'] ) {
		$img = 'star7.gif';
		$rank = $params['rat7'];
	} else if ( $rating >= $params['cond8'] and $rating < $params['cond9'] ) {
		$img = 'star8.gif';
		$rank = $params['rat8'];
	} else if ( $rating >= $params['cond9'] and $rating < $params['cond10'] ) {
		$img = 'star9.gif';
		$rank = $params['rat9'];
	} else {
		$img = 'star10.gif';
		$rank = $params['rat10'];
	}
	$result = array('rank' => $rank,
					'img' => $img);
	
	return $result;
	
}

?>