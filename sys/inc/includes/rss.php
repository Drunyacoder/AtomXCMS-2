<?php

$cache_key = $this->module . '_rss';
$cache_tags = array(
	'module_' . $this->module,
	'action_rss',
);


$check = $this->Register['Config']->read('rss_' . $this->module, 'common');
if (!$check) redirect('/');


if ($this->Cache->check($cache_key)) {
	$html = $this->Cache->read($cache_key);
	
	
} else {
	$sitename = '/';
	if (!empty($_SERVER['SERVER_NAME'])) {
		$sitename = 'http://' . $_SERVER['SERVER_NAME'] . '';
	}
	
	$html = '<?xml version="1.0" encoding="UTF-8"?>';
	$html .= '<rss version="2.0">';
	$html .= '<channel>';
	$html .= '<title>' . h($this->Register['Config']->read('title', $this->module)) . '</title>';
	$html .= '<link>' . $sitename . $this->module . '/</link>';
	$html .= '<description>' . h($this->Register['Config']->read('description', $this->module)) . '</description>';
	$html .= '<pubDate>' . date('r') . '</pubDate>';
	$html .= '<generator>FPS RSS Generator (Fapos CMS)</generator>';

	
	$this->Model->bindModel('category');
	$this->Model->bindModel('author');
	$records = $this->Model->getCollection(
		array(), 
		array(
			'limit' => $this->Register['Config']->read('rss_cnt', 'common'),
		)
	);
	
	if (!empty($records) && is_array($records)) {
		$html .= '<lastBuildDate>' . date('r', strtotime($records[0]->getDate())) . '</lastBuildDate>';
		foreach ($records as $record) { 
			$html .= '<item>';
			$html .= '<link>' . $sitename . get_url(entryUrl($record, $this->module)) . '</link>';
			$html .= '<pubDate>' . date('r', strtotime($record->getDate())) . '</pubDate>';
			$html .= '<title>' . $record->getTitle() . '</title>';
			$html .= '<description><![CDATA[' . mb_substr($record->getMain(), 0, $this->Register['Config']->read('rss_lenght', 'common')) . '<br />';
			$html .= 'Автор: ' . $record->getAuthor()->getName() . '<br />]]></description>';
			$html .= '<category>' . $record->getCategory()->getTitle() . '</category>';
			$html .= '<guid>' . $sitename . $this->module . '/view/' . $record->getId() . '</guid>';
			$html .= '</item>';
		}
	}
	
	
	
	
	
	$html .= '</channel>';
	$html .= '</rss>';
	
	$this->Cache->write($html, $cache_key, $cache_tags);
}

echo $html; 

?>