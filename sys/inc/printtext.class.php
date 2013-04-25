<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.3.2                         |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Print lobrary                 |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/01/30                    |
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
 * This class uses for work with text
 * he can process by smiles, BB codes, cut string for preview, etc...
 *
 * @version      0.1
 * @author       Andrey Brykin
 * @package      CMS Fapos
 * @url          fapos.net (fapos project)
 */
class PrintText {

	public $bbCodes = array('php', 'left', 'right', 'center', 'code', 'html', 'xml', 'b', 'i', 's', 'u', 'quote', 'hide', 'size');
	
	
	/**
	 * @param string $str
	 * @param string $url - URL to full material
	 * @param int $start
	 * @param int $length - announce length
	 * @return string announce
	 *
	 * create announce and close opened bb tags
	 */
	public function getAnnounce($str, $url, $start = 0, $length = 500, $material = false) {
		// Announce tags
		$start_tag = mb_strpos($str, '[announce]');
		$end_tag = mb_strpos($str, '[/announce]');
		if (false !== $start_tag) $start_tag += 10;
		
		
		if (false !== $start_tag && false !== $end_tag && $end_tag > $start_tag) {
			$announce = mb_substr($str, $start_tag, ($end_tag - $start_tag));
		} else {
			// if no tags, use settings lenght
			$start = (int)$start;
			$length = (int)$length; 
			
			if ($length < 1) $length = 500;
			if ($start >= $length) $start = 0; 
			$announce = mb_substr($str, $start, $length);
			//if (!preg_match('#[a-zа-я]$#ui', $announce)) $announce = mb_substr($announce, 0, -1);
		}
		
		
		if (is_object($material)) {
			$ustatus = $material->getAuthor()->getStatus();
			$title = $material->getTitle();
		} else {
			$ustatus = $material;
			$title = false;
		}
		
		//pr($announce);
		$announce = $this->closeOpenTags($announce); 
		$announce = $this->print_page($announce, $ustatus, $title); 
		$announce .= ' ... <br /><div style="clear:both;"></div> <a class="fps-show-mat" href="' . get_url($url) . '">'.__('Show material').'</a>';
		return $announce;
	}
	
	
	/**
	 * Pareseand return user signature
	 *
	 * @param stirng $str
	 * @param int $uid
	 * @return string
	 */
	public function getSignature($str, $uid) {
		$str = htmlspecialchars($str);
		$str = nl2br($str);
		
		$Register = Register::getInstance();
		$ACL = $Register['ACL'];
		
		if ($ACL->turn(array('bbcodes', 'bb_s'), false, $uid))
			$str = $this->parseSBb($str);
		if ($ACL->turn(array('bbcodes', 'bb_u'), false, $uid))
			$str = $this->parseUBb($str);
		if ($ACL->turn(array('bbcodes', 'bb_b'), false, $uid))
			$str = $this->parseBBb($str);
		if ($ACL->turn(array('bbcodes', 'bb_i'), false, $uid))
			$str = $this->parseIBb($str);
		if ($ACL->turn(array('bbcodes', 'bb_img'), false, $uid))
			$str = $this->parseImgBb($str);
		if ($ACL->turn(array('bbcodes', 'bb_url'), false, $uid))
			$str = $this->parseUrlBb($str);

		return $str;
	}
	
	
	
	/**
	 * @param string $str
	 * @return string with closed tags
	 *
	 * close opened bb tags
	 */
	public function closeOpenTags($str) {
		preg_match_all('#\[/([\w\d]+)[^\]]*\]#u', $str, $cl_tags);
		$cl_tags = (!empty($cl_tags[1])) ? $cl_tags[1] : array();

		if (preg_match_all('#\[([\w\d]+)[^\]]*\]#u', $str, $tags)) {
			if (!empty($tags[1]) && count($cl_tags) != count($tags[1])) {
				$tags[1] = array_reverse($tags[1]);
				
				//through all tags
				foreach ($tags[1] as $tag) {
					$close = array_pop($cl_tags);
					if (!in_array($tag, $this->bbCodes)) continue;
					//if this tag havn't close pair
					if (empty($close) || $tag != $close) {
						$str .= '[/' . $tag . ']';
						$cl_tags[] = $close;
					}
				}
			}
		}
		return $str;
	}

	
	
	

	/**
	 * @param string $message
	 * @param int $ustatus
	 * @param string $title
	 * @return string
	 * 
	 * bb code process
	 */
	public function print_page($message, $ustatus = false, $title = false) {
        $register = Register::getInstance();

		// hook (for plugins)
		$message = Plugins::intercept('before_print_page', $message);
	
	
		// Announce tags
		$start_tag = mb_strpos($message, '[announce]');
		$end_tag = mb_strpos($message, '[/announce]');
		if (false !== $start_tag && false !== $end_tag && $end_tag > $start_tag) {
			$message = preg_replace('#\[announce\].+\[/announce\]#sui', '', $message);
		}
	
		
		// Разрезаем слишком длинные слова
		//$message = wordwrap($message, 70, ' ', 1); 
		//$message = preg_replace("#([^\s/\]\[]{100})#ui", "\\1 ", $message);	  
				  

		// Тэги - [code], [php], [sql]
		preg_match_all( "#\[php\](.+)\[\/php\]#uisU", $message, $matches );
		$cnt = count( $matches[0] );
		for ( $i = 0; $i < $cnt; $i++ ) {
			
			$matches[1][$i] = preg_replace('#^\s*<\?php(.*)\?>\s*$#uis', '$1', $matches[1][$i]);
			$matches[1][$i] = preg_replace('#^\s*<\?(.*)\?>\s*$#uis', '$1', $matches[1][$i]);
			$phpBlocks[] = '<div class="codePHP">' . $this->highlight_php_string('<?php ' . $matches[1][$i] . '?>', true ) . '</div>';
			
			/*
			$phpBlocks[] = '<div class="codePHP">' . geshi_highlight($matches[1][$i], 'php', '', true) . '</div>';
			*/
			// Вот над этим надо будет подумать - усовершенствовать рег. выражение
			$phpBlocks[$i] = str_replace( '<div class="codePHP"><br />', '<div class="codePHP">', $phpBlocks[$i]);
			$uniqidPHP = '[php_'.uniqid('').']';
			$uniqidsPHP[] = $uniqidPHP;
			$message = str_replace( $matches[0][$i], $uniqidPHP, $message ); 
		}

		$spaces = array( ' ', "\t" );
		$entities = array( '&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;' );

	
		
		
		preg_match_all( "#\[code\](.+)\[\/code\]#uisU", $message, $matches );
		$cnt = count( $matches[0] );
		for ( $i = 0; $i < $cnt; $i++ ) {
			$codeBlocks[] = '<div class="bbCodeBlock"><div class="bbCodeName" style="padding-left: 5px; font-weight: bold; font-size: 7pt;"><b>Code:</b></div><div class="codeMessage" style="border: 1px inset ; overflow: auto; max-height: 200px;">'.nl2br(str_replace($spaces, $entities, htmlspecialchars($matches[1][$i]))).'</div></div>';
			// Вот над этим надо будет подумать - усовершенствовать рег. выражение
			$codeBlocks[$i] = str_replace( '<div class="code"><br />', '<div class="code">', $codeBlocks[$i] );
			$uniqidCode = '[code_'.uniqid('').']';
			$uniqidsCode[] = $uniqidCode;
			$message = str_replace( $matches[0][$i], $uniqidCode, $message ); 
		}

		
		
		preg_match_all( "#\[sql\](.+)\[\/sql\]#isU", $message, $matches );
		$cnt = count( $matches[0] );
		for ( $i = 0; $i < $cnt; $i++ ) {
			$sqlBlocks[] = '<div class="codeSQL">' . $this->highlight_sql($matches[1][$i]) . '</div>';
			// Вот над этим надо будет подумать - усовершенствовать рег. выражение
			$sqlBlocks[$i] = str_replace( '<div class="codeSQL"><br />', '<div class="codeSQL">', $sqlBlocks[$i] );
			$uniqidSQL = '[sql_'.uniqid('').']';
			$uniqidsSQL[] = $uniqidSQL;
			$message = str_replace( $matches[0][$i], $uniqidSQL, $message ); 
		}

			
		
		preg_match_all( "#\[js\](.+)\[\/js\]#isU", $message, $matches );
		$cnt = count( $matches[0] );
		for ( $i = 0; $i < $cnt; $i++ ) {
			$jsBlocks[] = '<div class="codeJS">'.geshi_highlight($matches[1][$i], 'javascript', '', true).'</div>';
			// Вот над этим надо будет подумать - усовершенствовать рег. выражение
			$jsBlocks[$i] = str_replace( '<div class="codeJS"><code><br />', '<div class="codeJS"><code>', $jsBlocks[$i] );
			$uniqidJS = '[js_'.uniqid('').']';
			$uniqidsJS[] = $uniqidJS;
			$message = str_replace( $matches[0][$i], $uniqidJS, $message ); 
		} 

		preg_match_all( "#\[css\](.+)\[\/css\]#isU", $message, $matches );
		$cnt = count( $matches[0] );
		for ( $i = 0; $i < $cnt; $i++ ) {
			$cssBlocks[] = '<div class="codeCSS">'.geshi_highlight($matches[1][$i], 'css', '', true).'</div>';
			// Вот над этим надо будет подумать - усовершенствовать рег. выражение
			$cssBlocks[$i] = str_replace( '<div class="codeCSS"><code><br />', '<div class="codeCSS"><code>', $cssBlocks[$i] );
			$uniqidCSS = '[css_'.uniqid('').']';
			$uniqidsCSS[] = $uniqidCSS;
			$message = str_replace( $matches[0][$i], $uniqidCSS, $message ); 
		} 

		preg_match_all( "#\[html\](.+)\[\/html\]#isU", $message, $matches );
		$cnt = count( $matches[0] );
		for ( $i = 0; $i < $cnt; $i++ ) {
			$htmlBlocks[] = '<div class="codeHTML">'.geshi_highlight($matches[1][$i], 'html4strict', '', true).'</div>';
			// Вот над этим надо будет подумать - усовершенствовать рег. выражение
			$htmlBlocks[$i] = str_replace( '<div class="codeHTML"><br />', '<div class="codeHTML">', $htmlBlocks[$i] );
			$uniqidHTML = '[html_'.uniqid('').']';
			$uniqidsHTML[] = $uniqidHTML;
			$message = str_replace( $matches[0][$i], $uniqidHTML, $message ); 
		}	
		
		preg_match_all( "#\[xml\](.+)\[\/xml\]#isU", $message, $matches );
		$cnt = count( $matches[0] );
		for ( $i = 0; $i < $cnt; $i++ ) {
			$xmlBlocks[] = '<div class="codeHTML">'.geshi_highlight($matches[1][$i], 'xml', '', true).'</div>';
			// Вот над этим надо будет подумать - усовершенствовать рег. выражение
			$xmlBlocks[$i] = str_replace( '<div class="codeHTML"><br />', '<div class="codeHTML">', $xmlBlocks[$i] );
			$uniqidXML = '[xml_'.uniqid('').']';
			$uniqidsXML[] = $uniqidXML;
			$message = str_replace( $matches[0][$i], $uniqidXML, $message ); 
		}
		/*
		preg_match_all( "#\[img\][\s]*([\S]+)[\s]*\[\/img\]#isU", $message, $matches );
		foreach ( $matches[0] as $src ) {
		$img = file_get_contents( $src );
		file_put_contents( );
		}
		*/
		
		
		$ACL = $register['ACL'];
		if (!$ACL->turn(array('bbcodes', 'html'), false, $ustatus) 
		|| !Config::read('allow_html')) {
			$message = htmlspecialchars($message);
		}

		$message = $this->parseIBb($message);
		$message = $this->parseBBb($message);
		$message = $this->parseSBb($message);
		$message = $this->parseUBb($message);
		
		$message = preg_replace("#\[quote\](.+)\[\/quote\]#uisU",'<div class="bbQuoteBlock"><div class="bbQuoteName" style=""><b></b>Цитата</div><div class="quoteMessage" style="">\\1</div></div>',$message);
		$message = preg_replace("#\[quote=&quot;([-_ 0-9a-zа-я]{1,30})&quot;\](.+)\[\/quote\]#isuU", '<div class="bbQuoteBlock"><div class="bbQuoteName" style=""><b>\\1 пишет:</b></div><div class="quoteMessage" style="">\\2</div></div>', $message);
		$message = $this->parseImgBb($message, $title);
		
		
		$message = preg_replace("#\[color=red\](.+)\[\/color\]#uisU",'<span style="color:#FF0000">\\1</span>',$message);
		$message = preg_replace("#\[color=green\](.+)\[\/color\]#uisU",'<span style="color:#008000">\\1</span>',$message);
		$message = preg_replace("#\[color=blue\](.+)\[\/color\]#uisU",'<span style="color:#0000FF">\\1</span>',$message);
		$message = preg_replace("#\[color=\#?([0-9a-z]{3,6})\](.+)\[\/color\]#uisU",'<span style="color:#\\1">\\2</span>',$message);
		
		
		$message = preg_replace_callback("#\[list\]\s*((?:\[\*\].+)+)\[\/list\]#usiU",'getUnorderedList',$message);
		$message = preg_replace_callback("#\[list=([a|1])\]\s*((?:\[\*\].+)+)\[\/list\]#usiU", 'getOrderedList',$message);
		$message = $this->parseUrlBb($message);
		
		
		$message = preg_replace("#\[size=(\d+)\]([^\[]*)\[/size\]#uisU", '<span style="font-size:\\1px;">\\2</span>', $message);
		$message = preg_replace("#\[center\]([^\[]*)\[/center\]#uisU", '<span style="display:block;width:100%;text-align:center;">\\1</span>', $message);
		$message = preg_replace("#\[right\]([^\[]*)\[/right\]#uisU", '<span style="display:block;width:100%;text-align:right;">\\1</span>', $message);
		$message = preg_replace("#\[left\]([^\[]*)\[/left\]#uisU", '<span style="display:block;width:100%;text-align:left;">\\1</span>', $message);
		
		
		$message = preg_replace("#\[spoiler\](.+)\[/spoiler\]#suU", '<div onClick="if ($(this).next().css(\'display\') == \'none\') { $(this).next().toggle(1000); } else { $(this).next().toggle(1000); }" class="spoiler-open">' . __('Bb-spoiler open') . '</div><div class="spoiler-win">\\1</div>', $message);
		
		
		if (preg_match_all("#\[video\](http://(www\.)*youtube\.com/watch\?v=([\w-]+))\[/video\]#isU", $message, $match)) {
			if (!empty($match[1])) {
				foreach ($match[1] as $key => $url) {
					$message = str_replace('[video]' . $url . '[/video]', 
					'<object height="300" width="450" data="http://youtube.com/v/' . $match[3][$key] 
					. '" type="application/x-shockwave-flash" class="restrain" id="yui-gen54">'
					. '<param value="http://youtube.com/v/' . $match[3][$key] . '" name="movie"><param value="transparent" name="wmode">' 
					. '<!--[if IE 6]><embed width="400" height="300" type="application/x-shockwave-flash" src="http://youtube.com/v/' . $match[3][$key] . '" />'
					. '<![endif]--></object>', $message);
				}
			}
		}
		if (preg_match_all("#\[video\](http://(www\.)*rutube\.ru/tracks/[\d]+\.html\?v=([\w]+))\[/video\]#isU", $message, $match)) {
			if (!empty($match[1])) {
				foreach ($match[1] as $key => $url) {
					$message = str_replace('[video]' . $url . '[/video]', 
					'<object height="300" width="450" data="http://video.rutube.ru/' . $match[3][$key] 
					. '" type="application/x-shockwave-flash" class="restrain" id="yui-gen54">'
					. '<param value="http://video.rutube.ru/' . $match[3][$key] . '" name="movie"><param value="transparent" name="wmode">' 
					. '<!--[if IE 6]><embed width="400" height="300" type="application/x-shockwave-flash" src="http://video.rutube.ru/' 
					. $match[3][$key] . '" />'
					. '<![endif]--></object>', $message);
				}
			}
		}
		if (!empty($_SESSION['user']['id'])) {
			$message = preg_replace("#\[hide\](.*)\[/hide\]#isU", '\\1', $message);
		} else {
			$message = preg_replace("#\[hide\](.*)\[/hide\]#isU", '<div class="hide">Необходима авторизация. <a href="' . WWW_ROOT 
					. '/users/add_form/">Регистрация</a></div>', $message);
		}

		
		
		
		$message = nl2br( $message);
		//work for smile
		if (Config::read('allow_smiles')) {
			$message = $this->smile($message);
		}
		//return block
		if ( isset( $uniqidCode ) ) $message = str_replace( $uniqidsCode, $codeBlocks, $message );
		if ( isset( $uniqidPHP ) ) $message = str_replace( $uniqidsPHP, $phpBlocks, $message );
		if ( isset( $uniqidSQL ) ) $message = str_replace( $uniqidsSQL, $sqlBlocks, $message );
		if ( isset( $uniqidJS ) ) $message = str_replace( $uniqidsJS, $jsBlocks, $message );
		if ( isset( $uniqidCSS ) ) $message = str_replace( $uniqidsCSS, $cssBlocks, $message );
		if ( isset( $uniqidHTML ) ) $message = str_replace( $uniqidsHTML, $htmlBlocks, $message );
		if ( isset( $uniqidXML ) ) $message = str_replace( $uniqidsXML, $xmlBlocks, $message );

		// Над этим тоже надо будет подумать
		$message = str_replace( '</div><br />', '</div>', $message );

		

		return $message;
	}


	/**
	 * Additonal bb codes
	 */
	public function parseSBb($str) {
		return preg_replace("#\[s\](.+)\[\/s\]#isU", '<span style="text-decoration:line-through;">\\1</span>', $str);
	}
	public function parseUBb($str) {
		return preg_replace("#\[u\](.+)\[\/u\]#isU", '<u>\\1</u>', $str);
	}
	public function parseBBb($str) {
		return preg_replace("#\[b\](.+)\[\/b\]#isU", '<b>\\1</b>', $str);
	}
	public function parseIBb($str) {
		return preg_replace("#\[i\](.+)\[\/i\]#isU", '<i>\\1</i>', $str);
	}
	public function parseImgBb($str, $title = false) {
		$title = (false !== $title) ? h(preg_replace('#[^\w\dА-я ]+#ui', ' ', $title)) : '';
		$Register = Register::getInstance();
		
		
		if (!empty($_SESSION['module'])) {
			$sizex = $Register['Config']->read('img_size_x', $Register['dispath_params'][0]);
			$sizey = $Register['Config']->read('img_size_y', $Register['dispath_params'][0]);
			$sizex = intval($sizex);
			$sizey = intval($sizey);
			if (!empty($sizex) && !empty($sizey)) {
				$str = preg_replace("#\[img\][\s]*([^\"'\>\<\(\)]+)[\s]*\[\/img\]#isU"
				,'<a href="\\1" class="gallery"><img style="max-width:'.$sizex.'px; max-height:'.$sizey
				.'px;" src="\\1" alt="'.$title.'" title="'.$title.'" /></a>',$str);
				$str = preg_replace("#\[imgl\][\s]*([^\"'\>\<\(\)]+)[\s]*\[\/imgl\]#isU"
				,'<a style="float:left;" href="\\1" class="gallery"><img style="max-width:'.$sizex.'px; max-height:'
				.$sizey.'px;" src="\\1" alt="'.$title.'" title="'.$title.'" /><div class="clear"></div></a>',$str);
				return $str;
			}
		}
		$str = preg_replace("#\[img\][\s]*([^\"'\>\<\(\)]+)[\s]*\[\/img\]#isU"
		,'<a href="\\1" class="gallery"><img style="max-width:150px;" src="\\1" alt="'.$title.'" title="'.$title.'" /></a>',$str);
		$str = preg_replace("#\[imgl\][\s]*([^\"'\>\<\(\)]+)[\s]*\[\/imgl\]#isU"
		,'<a style="float:left;" href="\\1" class="gallery"><img style="max-width:150px;" src="\\1" alt="'.$title.'" title="'.$title.'" /></a>',$str);
		return $str;
	}
	public function parseUrlBb($str) {
		if (stripos($str, '[url') && stripos($str, '[/url]')) {
			$noindex = Config::read('use_noindex');
			$redirect = Config::read('redirect_active');
			$url = $redirect ? get_url('redirect.php?url=') : '';

			$str = preg_replace("#\[url\](http[s]*://[\w\d\-_.]*\.\w{2,}[\w\d\-_\\/.\?=\#&;%]*)\[\/url\]#iuU", ($noindex ? '<noindex>' : '') . '<a href="' . $url . '\\1" target="_blank"' . ($noindex ? ' rel="nofollow"' : '') . '>\\1</a>' . ($noindex ? '</noindex>' : ''), $str);
			$str = preg_replace("#\[url=(http[s]*://[\w\d\-_.]*\.\w{2,}[\w\d\-_\\/.\?=\#;&%]*)\]([^\[]*)\[/url\]#iuU", ($noindex ? '<noindex>' : '') . '<a href="' . $url . '\\1" target="_blank"' . ($noindex ? ' rel="nofollow"' : '') . '>\\2</a>' . ($noindex ? '</noindex>' : ''), $str);
		}
		if (stripos($str, '[gallery') && stripos($str, '[/gallery]')) {
			$str = preg_replace("#\[gallery=([\w\d\-_\\/.\?=\#;&%+]*)\]([^\[]*)\[/gallery\]#iuU", '<a href="\\1" class="gallery">\\2</a>', $str);
		}

		return $str;
	}

	
	/**
	 * @param string $str
	 * @return string 
	 *
	 * highlight php string 
	 */
	public function highlight_php_string($str) {
		$str = highlight_string($str, true);
		
		$cnt = 1;
		if (preg_match_all('#(<br />)#iu', $str, $count)) {
			$cnt = count($count[1]);
		}

		$box = '<div style="width:35px; float:left; padding-right:5px; text-align: right;" ><code>';
		for ($i = 1; $i < ($cnt + 2); $i++) {
			$box .= '&nbsp;' . $i . '&nbsp;<br />';
		}
		$box .= '</code></div>';
		
		$str = $box . '<div style="margin-left:40px;">' . $str . '</div>';
		
		return $str;
	}


	
	
	/**
	 * @param string $str
	 * @return string 
	 *
	 * smiles process 
	 */
	public function smile($str) {
		$str = Plugins::intercept('before_smiles_parse', $str);

		$Register = Register::getInstance();
		$path = $Register['Config']->read('smiles_set');
		$path = ROOT . '/sys/img/smiles/' . (!empty($path) ? $path : 'fapos') . '/info.php';
		
		include $path;
		
		$from = array();
		$to = array();
		if (isset($smilesList) && is_array($smilesList)) {
			foreach ($smilesList as $smile) {
				$from[] = $smile['from'];
				$to[] = '<img alt="' . $smile['from'] . '" title="' . $smile['from'] . '" src="' . WWW_ROOT . '/sys/img/smiles/fapos/' . $smile['to'] . '" />';
			}
		}
		$str = str_replace($from, $to, $str);
		
		return $str;
	}

	
	
	
	
	/**
	 * @param string $str
	 * @return string 
	 *
	 * highlight sql string 
	 */
	public function highlight_sql($sql) {
		$sql = preg_replace("#(\"|'|`)(.+?)\\1#i", "<span style='color:red'>\\0</span>", $sql );
		$sql = preg_replace("#\b(SELECT|INSERT|UPDATE|DELETE|ALTER|TABLE|DROP|CREATE|ADD|WHERE|MODIFY|CHANGE|AS|DISTINCT|IN|ASC|DESC|ORDER|BY|GROUP|SET|FROM|INTO|LIKE|NOT|REGEXP|MAX|AVG|SUM|COUNT|MIN|AND|OR|VALUES|INDEX|HAVING|NULL|ON|BETWEEN|UNION|CONCAT|LIMIT|ANY|ALL|KEY|INNER|LEFT|RIGHT|JOIN|IFNULL|DEFAULT|CHARSET|PRIMARY|ENGINE)\b#i", "<span style='color:teal;font-weight:bold'>\\1</span>", $sql );

		$spaces = array( ' ', "\t" );
		$entities = array( '&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;' );

		$sql = nl2br( str_replace( $spaces, $entities, $sql ) );
		$sql = str_replace( 'span&nbsp;style', 'span style', $sql );

		return $sql;
	}


	
}






function getUnorderedList( $matches )
{
	$list = '<ul>';
	$tmp = trim( $matches[1] );
	$tmp = substr( $tmp, 3 );
	$tmpArray = explode( '[*]', $tmp );	 
	$elements = '';
	foreach ( $tmpArray as $value ) {
		$elements = $elements.'<li>'.trim($value).'</li>';
	}
	$list = $list.$elements;
	$list = $list.'</ul>';
	return $list;
}

function getOrderedList( $matches )
{
	if ( $matches[1] == '1' )
		$list = '<ol type="1">';
	else
		$list = '<ol type="a">';
	$tmp = trim( $matches[2] );
	$tmp = substr( $tmp, 3 );
	$tmpArray = explode( '[*]', $tmp );

	$elements = '';
	foreach ( $tmpArray as $value ) {
		$elements = $elements.'<li>'.trim($value).'</li>';
	}
	$list = $list.$elements;
	$list = $list.'</ol>';
	return $list;
}





class Lingua_Stem_Ru {

    var $VERSION = "0.32";
    var $Stem_Caching = 0;
    var $Stem_Cache = array();
    var $VOWEL = '/аеиоуыэюя/u';
    var $PERFECTIVEGROUND = '/((ив|ивши|ившись|ыв|ывши|ывшись)|((?<=[ая])(в|вши|вшись)))$/u';
    var $REFLEXIVE = '/(с[яь])$/u';
    var $ADJECTIVE = '/(ее|ие|ые|ое|ими|ыми|ей|ий|ый|ой|ем|им|ым|ом|его|ого|ему|ому|их|ых|ую|юю|ая|яя|ою|ею)$/u';
    var $PARTICIPLE = '/((ивш|ывш|ующ)|((?<=[ая])(ем|нн|вш|ющ|щ)))$/u';
    var $VERB = '/((ила|ыла|ена|ейте|уйте|ите|или|ыли|ей|уй|ил|ыл|им|ым|ен|ило|ыло|ено|ят|ует|уют|ит|ыт|ены|ить|ыть|ишь|ую|ю)|((?<=[ая])(ла|на|ете|йте|ли|й|л|ем|н|ло|но|ет|ют|ны|ть|ешь|нно)))$/u';
    var $NOUN = '/(а|ев|ов|ие|ье|е|иями|ями|ами|еи|ии|и|ией|ей|ой|ий|й|иям|ям|ием|ем|ам|ом|о|у|ах|иях|ях|ы|ь|ию|ью|ю|ия|ья|я)$/u';
    var $RVRE = '/^(.*?[аеиоуыэюя])(.*)$/u';
    var $DERIVATIONAL = '/[^аеиоуыэюя][аеиоуыэюя]+[^аеиоуыэюя]+[аеиоуыэюя].*(?<=о)сть?$/u';

    function s(&$s, $re, $to)
    {
        $orig = $s;
        $s = preg_replace($re, $to, $s);
        return $orig !== $s;
    }

    function m($s, $re)
    {
        return preg_match($re, $s);
    }

    function stem_word($word)
    {
		mb_internal_encoding('UTF-8');
        $word = mb_strtolower($word);
		
        $word = str_replace('ё', 'е', $word);
        # Check against cache of stemmed words
        if ($this->Stem_Caching && isset($this->Stem_Cache[$word])) {
            return $this->Stem_Cache[$word];
        }
		
        $stem = $word;
        do {
          if (!preg_match($this->RVRE, $word, $p)) break;
          $start = $p[1];
          $RV = $p[2];
          if (!$RV) break;

          # Step 1
          if (!$this->s($RV, $this->PERFECTIVEGROUND, '')) {
              $this->s($RV, $this->REFLEXIVE, '');

              if ($this->s($RV, $this->ADJECTIVE, '')) {
                  $this->s($RV, $this->PARTICIPLE, '');
              } else {
                  if (!$this->s($RV, $this->VERB, ''))
                      $this->s($RV, $this->NOUN, '');
              }
          }

          # Step 2
          $this->s($RV, '/и$/u', '');

          # Step 3
          if ($this->m($RV, $this->DERIVATIONAL))
              $this->s($RV, '/ость?$/u', '');

          # Step 4
          if (!$this->s($RV, '/ь$/u', '')) {
              $this->s($RV, '/ейше?/u', '');
              $this->s($RV, '/нн$/u', 'н');
          }

          $stem = $start.$RV;
        } while(false);
        if ($this->Stem_Caching) $this->Stem_Cache[$word] = $stem;
        return $stem;
    }

    function stem_caching($parm_ref)
    {
        $caching_level = @$parm_ref['-level'];
        if ($caching_level) {
            if (!$this->m($caching_level, '/^[012]$/u')) {
                die(__CLASS__ . "::stem_caching() - Legal values are '0','1' or '2'. '$caching_level' is not a legal value");
            }
            $this->Stem_Caching = $caching_level;
        }
        return $this->Stem_Caching;
    }

    function clear_stem_cache()
    {
        $this->Stem_Cache = array();
    }
}


