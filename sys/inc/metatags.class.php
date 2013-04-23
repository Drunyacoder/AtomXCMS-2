<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0.1                         |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Tag generator                 |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/02/07                    |
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


class MetaTags
{
	/**
	 *
	 */
	private $exceptions = array();
	
	/**
	 *
	 */
	private $priority = array();
	
	/**
	 *
	 */
	private $maxTags = 5;
	
	
	
	/**
	 * Return sorted tags for material
	 *
	 * @return array
	 */
	public function getTags($text)
	{
		$text = $this->clean($text);
		
		$words = $this->countWords($text);
		
		$tags = array_slice($words, 0, $this->maxTags);

		return $tags;
	}
	
	
	/**
	 * Clean text of material
	 *
	 * @return string
	 */
	public function clean($text)
	{
		$text = trim($text);
		$text = str_replace("\n", ' ', $text);
		$text = preg_replace('#([\s]{1,})#Umu', ' ', $text);
		$text = preg_replace('#([^a-zа-я\d\s]{1,})#Umiu', '', $text);
		
		$text = preg_replace('#IMAGE\d+#', '', $text);
		
		$text = $this->cleanTwoSimbols($text);
		$text = $this->cleanExceptions($text);

		return $text;
	}
	
	
	/**
	 * Count words of material
	 *
	 * @return array
	 */
	public function countWords($text)
	{
		$output = array();
		$words = explode(' ', $text);
		
		
		foreach ($words as $k => $w) {
			if (empty($w)) unset($words[$k]);
		}
		
		
		if (!empty($words)) {
			$prev = false;
		
			foreach ($words as $key => $word) {
				if (array_key_exists($word, $output)) $output[$word]++;
				else $output[$word] = 1;
				
				
				if ($prev !== false) {
					if (array_key_exists($prev . ' ' . $word, $output)) $output[$prev . ' ' . $word]++;
					else $output[$prev . ' ' . $word] = 1;
				}

				$prev = $word;
			}
		}
		
		
		if (!empty($this->priority)) {
			foreach ($this->priority as $priority_tag) {
				if (array_key_exists($priority_tag, $output)) {
					$output[$priority_tag] = ($output[$priority_tag] + 3);
				}
			}
		}
		
		
		arsort($output);
		return $output;
	}
	
	
	public function cleanTwoSimbols($text)
	{
		return preg_replace('#\s[a-zа-я]{1,2}\s#Umiu', ' ', $text);
	}
	
	
	public function cleanExceptions($text)
	{
		if (!empty($this->exceptions) && is_array($this->exceptions)) {
			$text = str_replace($this->exceptions, '', $text);
		}

		
		return $text;
	}

}