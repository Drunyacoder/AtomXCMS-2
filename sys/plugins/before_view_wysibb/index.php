<?php


class WysiBB {
	public function common($params) {
		$markers = array(
			'editor_head' => null,
			'editor_body' => null,
			'editor_buttons' => null,
			'editor_text' => null,
			'editor_forum_text' => null,
			'editor_forum_quote' => null,
			'editor_forum_name' => null,
		);
		
		$editor_set = array();

		include 'config.php';
		
		if ($editor_set && is_array($editor_set) && count($editor_set)) {
			$number = 0;
			foreach ($editor_set as $index => $editor) {
				if (isset($editor['default']) && $editor['default']) {
					$number = $index;
					break;
				}
			}
			$editor = $editor_set[$number];
			foreach ($markers as $marker => $value) {
				$markers[$marker] = !empty($editor[$marker]) ? $editor[$marker] : null;
			}
		}
		
		foreach ($markers as $marker => $value) {
			$params = preg_replace('#{{\s*' . $marker . '\s*}}#i', $value, $params);
		}
		return $params;
	}

}
