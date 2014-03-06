<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    AtmDateTime class             |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod.     2014/02/15                    |
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
class AtmDateTime {

	public static function getDate($date, $format = 'Y-m-d H:i:s') {
		$user_timezone = (!empty($_SESSION['user']) && !empty($_SESSION['user']['timezone']))
			? $_SESSION['user']['timezone']
			: '+00';
		
		$dateObj = new DateTime($date);
		$dateObj->modify($user_timezone . ' hour');
		return $dateObj->format($format);
	}


    public static function getSimpleDate($date, $format = 'j M \a\t G:i') {
        $user_timezone = (!empty($_SESSION['user']) && !empty($_SESSION['user']['timezone']))
            ? $_SESSION['user']['timezone']
            : '+00';

        $result = '';
        $dateObj = new DateTime($date);
        $currentDate = new DateTime();
        $diff = $dateObj->diff($currentDate);


        $y = $diff->y; $m = $diff->m; $d = $diff->d; $h = $diff->h; $i = $diff->i; $s = $diff->s;

		
        if (!empty($y)) {
            $result = $dateObj->format('j M Y');

        } else if (!empty($m) || (!empty($d) && $d > 1)) {
            $dateObj->modify($user_timezone . ' hour');
            $result = $dateObj->format($format);

        } else if (!empty($d) && $d == 1) {
            $dateObj->modify($user_timezone . ' hour');
            $result = sprintf(__('Yesterday at %s'), $dateObj->format('G:i'));

        } else if (!empty($h)) {
            switch ($h) {
                case 1:
                    $result = sprintf(__('%s hour ago'), $h);
                    break;
                case 2:
                case 3:
                    $result = sprintf(__('%s hours ago'), $h);
                    break;
                default:
                    $dateObj->modify($user_timezone . ' hour');
                    $result = sprintf(__('Today at %s'), $dateObj->format('G:i'));
                    break;
            }
        } else if (!empty($i)) {
            $diff_ = (strlen($i) == 2) ? substr($i, 1) : $i;
            switch ($diff_) {
                case 1:
                    $result = sprintf(__('%s minute ago'), $i);
                    break;
                case 2:
                case 3:
                case 4:
                    $result = sprintf(__('%s minutes ago(<=4)'), $i);
                    break;
                default:
                    $result = sprintf(__('%s minutes ago(>4)'), $i);
                    break;
            }
        } else if (!empty($s)) {
            $diff_ = (strlen($s) == 2) ? substr($s, 1) : $s;
            switch ($diff_) {
                case 1:
                    $result = sprintf(__('%s second ago'), $s);
                    break;
                case 2:
                case 3:
                case 4:
                    $result = sprintf(__('%s seconds ago(<=4)'), $s);
                    break;
                default:
                    $result = sprintf(__('%s seconds ago(>4)'), $s);
                    break;
            }
        }
		
		
		$translate = array(
			'Jan' => __('Jan'),
			'Feb' => __('Feb'),
			'Mar' => __('Mar'),
			'Apr' => __('Apr'),
			'May' => __('May'),
			'Jun' => __('Jun'),
			'Jul' => __('Jul'),
			'Aug' => __('Aug'),
			'Sept' => __('Sept'),
			'Oct' => __('Oct'),
			'Nov' => __('Nov'),
			'Dec' => __('Dec'),
			'at' => __('at'),
		);
		$result = str_replace(array_keys($translate), $translate, $result);

        return $result;
    }
}

