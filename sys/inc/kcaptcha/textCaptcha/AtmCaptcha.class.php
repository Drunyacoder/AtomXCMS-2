<?php
/*-----------------------------------------------\
| 												 |
|  @Author:       Andrey Brykin (Drunya)         |
|  @Email:        drunyacoder@gmail.com          |
|  @Site:         http://atomx.net			     |
|  @Version:      0.5.0                          |
|  @Project:      CMS AtomX                      |
|  @Package       CMS AtomX                      |
|  @Subpackege    AtmCaptcha Class               |
|  @Copyright     ©Andrey Brykin                 |
|  @Last mod.     2014/03/31                     |
|------------------------------------------------|
| 												 |
|  any partial or not partial extension          |
|  CMS AtomX,without the consent of the          |
|  author, is illegal                            |
|------------------------------------------------|
|  Любое распространение                         |
|  CMS AtomX или ее частей,                      |
|  без согласия автора, является не законным     |
\-----------------------------------------------*/





/**
 * Text captcha class
 *
 * @dependencies Config::__construct, Config::read
 */
class AtmCaptcha
{
	
	private $configPath = '/sys/settings/config.php';

	public function getContent() 
	{
		$this->getConfig();
		$questions = Config::read('secure.captcha_text_questions');
		$qkey = array_rand($questions);
		$text = $questions[$qkey];
		$w = 140;
		$h = 72;
		$font_size = 14;
	
		
		header("Content-type: image/png");
		$im = imagecreatetruecolor($w, $h);
		$transparent = imagecolorallocate($im, 0, 0, 0);
		$text_color = imagecolorallocate($im, 30, 30, 30);
		//imageSaveAlpha($im, true);
		imageColorTransparent($im, $transparent);
		imagestring($im, $font_size, ($w - (mb_strlen($text) * ($font_size / 2))) / 2, 30, $text, $text_color);
		imagepng($im);
		imagedestroy($im);
		
		return $qkey;
	}
	
	
	public function getTextContent($key) 
	{
		$this->getConfig();
		$questions = Config::read('secure.captcha_text_questions');
		$qkey = array_rand($questions);
		$text = $questions[$qkey];

		$_SESSION[$key] = $qkey;
		
		return $text;
	}
	
	
	private function getConfig()
	{
		$root = $root = dirname(__FILE__);
		while (!defined('ROOT')) {
			$root = dirname($root);
			if (file_exists($root . '/sys/settings/config.php')) define('ROOT', $root);
		}
	
		include_once ROOT . '/sys/inc/config.class.php';
		new Config(ROOT . $this->configPath);
	}
}
