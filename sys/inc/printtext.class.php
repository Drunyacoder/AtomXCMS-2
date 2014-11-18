<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.3.2                         |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
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
	 * @param object $material
	 * @param int $length - announce length
	 * @param int $offset
	 * @param string $module - just for translate to parseBBCodes
	 * @return string announce
	 *
	 * Create announce and close opened bb tags.
	 * Parse BB-codes and attaches markers.
	 */
	public function getAnnounce($str, $material = false, $length = 500, $offset = 0, $module = false) {
		// Announce tags
		$start_tag = mb_strpos($str, '[announce]');
		$end_tag = mb_strpos($str, '[/announce]');
		if (false !== $start_tag) $start_tag += 10;
		
		
		if (false !== $start_tag && false !== $end_tag && $end_tag > $start_tag) {
			$announce = mb_substr($str, $start_tag, ($end_tag - $start_tag));
		} else {
			// if no tags, use settings lenght
			$offset = (int)$offset;
			$length = (int)$length; 
			
			if ($length < 1) $length = 500;
			if ($offset >= $length) $offset = 0; 
			$announce = mb_substr($str, $offset, $length);
			//if (!preg_match('#[a-zа-я]$#ui', $announce)) $announce = mb_substr($announce, 0, -1);
		}


		$announce = $this->closeOpenTags($announce); 
		$announce = $this->parseBBCodes($announce, $material, $module);
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
	 * @param object $entity
	 * @param string $module - just for translate to insertImageAttach
	 * @return string
	 * 
	 * bb code process. 
	 * Parse BB-codes & attaches markers.
	 */
	public function parseBBCodes($message, $entity = false, $module = false) {
        $register = Register::getInstance();
		
		
		if (is_object($entity)) {
            if (is_object($entity->getAuthor())) {
                $ustatus = $entity->getAuthor()->getStatus();
                $title = $entity->getTitle();

            } else if ($entity->getStatus() != false) {
                $ustatus = $entity->getStatus();
            }

		} else if (is_array($entity)) {
			$ustatus = (!empty($entity['status'])) ? $entity['status'] : false;
			$title = (!empty($entity['title'])) ? $entity['title'] : false;
		}


		if (empty($ustatus) || !$ustatus) $ustatus = false;
		if (empty($title) || !$title) $title = false;
		
		
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
			$phpBlocks[] = '<div class="codePHP">' . $this->highlight_php_string('<?php ' . trim($matches[1][$i]) . '?>', true ) . '</div>';
			
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
			$codeBlocks[] = '<div class="bbCodeBlock"><div class="bbCodeName" style="padding-left: 5px; font-weight: bold; font-size: 7pt;"><b>Code:</b></div><div class="codeMessage" style="border: 1px inset ; overflow: auto; max-height: 200px;">'.nl2br(str_replace($spaces, $entities, htmlspecialchars(trim($matches[1][$i])))).'</div></div>';
			// Вот над этим надо будет подумать - усовершенствовать рег. выражение
			$codeBlocks[$i] = str_replace( '<div class="code"><br />', '<div class="code">', $codeBlocks[$i] );
			$uniqidCode = '[code_'.uniqid('').']';
			$uniqidsCode[] = $uniqidCode;
			$message = str_replace( $matches[0][$i], $uniqidCode, $message ); 
		}

		
		
		preg_match_all( "#\[sql\](.+)\[\/sql\]#isU", $message, $matches );
		$cnt = count( $matches[0] );
		for ( $i = 0; $i < $cnt; $i++ ) {
			$sqlBlocks[] = '<div class="codeSQL">' . $this->highlight_sql(trim($matches[1][$i])) . '</div>';
			// Вот над этим надо будет подумать - усовершенствовать рег. выражение
			$sqlBlocks[$i] = str_replace( '<div class="codeSQL"><br />', '<div class="codeSQL">', $sqlBlocks[$i] );
			$uniqidSQL = '[sql_'.uniqid('').']';
			$uniqidsSQL[] = $uniqidSQL;
			$message = str_replace( $matches[0][$i], $uniqidSQL, $message ); 
		}

			
		
		preg_match_all( "#\[js\](.+)\[\/js\]#isU", $message, $matches );
		$cnt = count( $matches[0] );
		for ( $i = 0; $i < $cnt; $i++ ) {
			$jsBlocks[] = '<div class="codeJS">'.geshi_highlight(trim($matches[1][$i]), 'javascript', '', true).'</div>';
			// Вот над этим надо будет подумать - усовершенствовать рег. выражение
			$jsBlocks[$i] = str_replace( '<div class="codeJS"><code><br />', '<div class="codeJS"><code>', $jsBlocks[$i] );
			$uniqidJS = '[js_'.uniqid('').']';
			$uniqidsJS[] = $uniqidJS;
			$message = str_replace( $matches[0][$i], $uniqidJS, $message ); 
		} 

		preg_match_all( "#\[css\](.+)\[\/css\]#isU", $message, $matches );
		$cnt = count( $matches[0] );
		for ( $i = 0; $i < $cnt; $i++ ) {
			$cssBlocks[] = '<div class="codeCSS">'.geshi_highlight(trim($matches[1][$i]), 'css', '', true).'</div>';
			// Вот над этим надо будет подумать - усовершенствовать рег. выражение
			$cssBlocks[$i] = str_replace( '<div class="codeCSS"><code><br />', '<div class="codeCSS"><code>', $cssBlocks[$i] );
			$uniqidCSS = '[css_'.uniqid('').']';
			$uniqidsCSS[] = $uniqidCSS;
			$message = str_replace( $matches[0][$i], $uniqidCSS, $message ); 
		} 

		preg_match_all( "#\[html\](.+)\[\/html\]#isU", $message, $matches );
		$cnt = count( $matches[0] );
		for ( $i = 0; $i < $cnt; $i++ ) {
			$htmlBlocks[] = '<div class="codeHTML">'.geshi_highlight(trim($matches[1][$i]), 'html4strict', '', true).'</div>';
			// Вот над этим надо будет подумать - усовершенствовать рег. выражение
			$htmlBlocks[$i] = str_replace( '<div class="codeHTML"><br />', '<div class="codeHTML">', $htmlBlocks[$i] );
			$uniqidHTML = '[html_'.uniqid('').']';
			$uniqidsHTML[] = $uniqidHTML;
			$message = str_replace( $matches[0][$i], $uniqidHTML, $message ); 
		}	
		
		preg_match_all( "#\[xml\](.+)\[\/xml\]#isU", $message, $matches );
		$cnt = count( $matches[0] );
		for ( $i = 0; $i < $cnt; $i++ ) {
			$xmlBlocks[] = '<div class="codeHTML">'.geshi_highlight(trim($matches[1][$i]), 'xml', '', true).'</div>';
			// Вот над этим надо будет подумать - усовершенствовать рег. выражение
			$xmlBlocks[$i] = str_replace( '<div class="codeHTML"><br />', '<div class="codeHTML">', $xmlBlocks[$i] );
			$uniqidXML = '[xml_'.uniqid('').']';
			$uniqidsXML[] = $uniqidXML;
			$message = str_replace( $matches[0][$i], $uniqidXML, $message ); 
		}
		
		
		$ACL = $register['ACL'];
		if (!$ustatus
            || !$ACL->turn(array('bbcodes', 'html'), false, $ustatus)
		    || !Config::read('allow_html')
        ) {
			$message = htmlspecialchars($message, ENT_NOQUOTES);
		}
	
		$message = $this->parseIBb($message);
		$message = $this->parseBBb($message);
		$message = $this->parseSBb($message);
		$message = $this->parseUBb($message);
		
		$message = preg_replace("#\[quote\](.+)\[\/quote\]#uisU",'<div class="bbQuoteBlock"><div class="bbQuoteName" style=""><b></b>Цитата</div><div class="quoteMessage" style="">\\1</div></div>',$message);
		$message = preg_replace("#\[quote=\"([-_ 0-9a-zа-я]{1,30})\"\](.+)\[\/quote\]#isuU", '<div class="bbQuoteBlock"><div class="bbQuoteName" style=""><b>\\1 пишет:</b></div><div class="quoteMessage" style="">\\2</div></div>', $message);
		$message = $this->parseImgBb($message, $title);
		
		
		$message = preg_replace("#\[color=red\](.+)\[\/color\]#uisU",'<span style="color:#FF0000">\\1</span>',$message);
		$message = preg_replace("#\[color=green\](.+)\[\/color\]#uisU",'<span style="color:#008000">\\1</span>',$message);
		$message = preg_replace("#\[color=blue\](.+)\[\/color\]#uisU",'<span style="color:#0000FF">\\1</span>',$message);
		$message = preg_replace("#\[color=\#?([0-9a-z]{3,6})\](.+)\[\/color\]#uisU",'<span style="color:#\\1">\\2</span>',$message);
		
		
		$message = preg_replace_callback("#\[list\]\s*((?:\[\*\].+)+)\[\/list\]#usiU",'getUnorderedList',$message);
		$message = preg_replace_callback("#\[list=([a|1])\]\s*((?:\[\*\].+)+)\[\/list\]#usiU", 'getOrderedList',$message);
		$message = $this->parseUrlBb($message);
		
		
		$message = preg_replace("#\[size=(\d+)\]([^\[]*)\[/size\]#uisU", '<span style="font-size:\\1%;">\\2</span>', $message);
		$message = preg_replace("#\[h([123456]{1})\]([^\[]*)\[/h[123456]{1}\][\n\r]*?#uisU", '<h\\1>\\2</h\\1>', $message);
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
		
		$message = $this->insertImageAttach($message, $entity, $module);

		return $message;
	}
	

	/**
	 * Replace an attaches markers.
	 *
	 * @param $message string
	 * @param $entity object
	 * @param $module string
	 */
	public function insertImageAttach($message, $entity, $module = null)
	{
		$Register = Register::getInstance();
		$attachment = null;
		$module = (!empty($module)) ? $module : $Register['module'];
		
		$attaches = ($module == 'forum') ? $entity->getAttacheslist() : $entity->getAttaches();
		if (!$attaches) return $message;
		
       
		$sizex = Config::read('img_size_x', $module);
		$sizey = Config::read('img_size_y', $module);
		$sizex = intval($sizex);
		$sizey = intval($sizey);
		$style = ' style="max-width:' . $sizex . 'px; max-height:' . $sizey . 'px;"';


        if (!empty($attaches) && count($attaches) > 0) {
            $attachDir = ROOT . '/sys/files/' . $module . '/';
			
            foreach ($attaches as $attach) {
				if (file_exists($attachDir . $attach->getFilename())) {
				
				
					if ($attach->getIs_image() == 1) {
						$message = str_replace('{IMAGE' . $attach->getAttach_number() . '}'
							, '<a class="gallery" href="' . get_url('/sys/files/' . $module . '/' . $attach->getFilename()) 
							. '"><img' . $style . ' alt="' . h($entity->getTitle()) . '" title="' . h($entity->getTitle()) 
							. '" title="" src="' . get_url('/image/' . $module . '/' . $attach->getFilename()) . '" /></a>'
							, $message);
							
							
					} else {
						$attachment .= __('Attachment') . $attach->getAttach_number() 
							. ': ' . get_img('/sys/img/file.gif', array('alt' => __('Open file'), 'title' => __('Open file'))) 
							. '&nbsp;' . get_link(($attach->getSize() / 1000) .' Kb', '/forum/download_file/' 
							. $attach->getFilename(), array('target' => '_blank')) . '<br />';
					}
				}
            }
        }
		
		if (!empty($attachment)) $entity->setAttachment($attachment);
		
		if (preg_match_all('#\{ATTACH(\d+)(\|(\d+))?(\|(left|right))?(\|([^\|\}]+))?\}#ui', $message, $matches)) {
			$sizes = array();
			$floats = array();
			$descriptions = array();
			foreach ($matches[1] as $key => $id) {
				$sizes[$id] = (!empty($matches[3][$key])) ? intval($matches[3][$key]) : false;
				$floats[$id] = (!empty($matches[5][$key])) ? 'float:' . $matches[5][$key] . ';' : false;
				$descriptions[$id] = (!empty($matches[7][$key])) ? $matches[7][$key] : false;
			}
			

			$attaches = $entity->getAttaches();
			if ($attaches) {
				foreach ($attaches as $attach) {
					
					if ($attach->getIs_image() == 1) {
						$style_ = (array_key_exists($attach->getId(), $sizes) && !empty($sizes[$attach->getId()])) 
							? ' style="width:' . $sizes[$attach->getId()] . 'px;' . $floats[$attach->getId()] . '"'
							: $style;
						$size = (array_key_exists($attach->getId(), $sizes) && !empty($sizes[$attach->getId()]))
							? '/' . $sizes[$attach->getId()]
							: '';
						$descr = (!empty($descriptions[$attach->getId()])) 
							? '<div class="atm-img-description">' . h($descriptions[$attach->getId()]) . '</div>' 
							: '';
						
						$message = preg_replace('#\{ATTACH' . $attach->getId() . '[^\}]*\}#ui', 
							'<a class="gallery" href="' . get_url('/sys/files/' . $module . '/' . $attach->getFilename()) 
							. '"><img' . $style_ . ' alt="' . h($entity->getTitle()) . '" title="' . h($entity->getTitle()) 
							. '" src="' . get_url('/image/' . $module . '/' . $attach->getFilename()) . $size . '" />'
							. $descr .  '</a>',
							$message);
					} else {
						$message = preg_replace('#\{ATTACH' . $attach->getId() . '[^\}]*\}#', 
							__('Attachment') . $attach->getAttach_number() 
							. ': ' . get_img('/sys/img/file.gif', array('alt' => __('Open file'), 'title' => __('Open file'))) 
							. '&nbsp;' . get_link(($attach->getSize() / 1000) .' Kb', '/forum/download_file/' 
							. $attach->getFilename(), array('target' => '_blank')) . '<br />',
							$message);
					}
				}
			}
		}
	
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
		}
		$styles = (!empty($sizex) && !empty($sizey)) 
			? ' style="max-width:' . $sizex . 'px; max-height:' . $sizey . 'px;"'
			: ' style="max-width:150px;"';
		
		if (preg_match_all("#\[img(\|([^\]]+))?\][\s]*([^\"'\>\<\(\)]+)[\s]*\[\/img\]#uisU", $str, $matches)) {
			foreach ($matches[0] as $key => $match) {
				$descr = (!empty($matches[2][$key])) 
					? '<div class="atm-img-description">' . h($matches[2][$key]) . '</div>' 
					: '';
				$str = preg_replace('#' . preg_quote($match) . '#uisU', 
					'<a href="' . h($matches[3][$key]) . '" class="gallery">
					<img' . $styles . ' src="' . h($matches[3][$key]) . '" alt="' . $title 
					. '" title="' . $title . '" />' . $descr . '</a>', $str);
			}
		}
		if (preg_match_all("#\[imgl(\|([^\]]+))?\][\s]*([^\"'\>\<\(\)]+)[\s]*\[\/imgl\]#uisU", $str, $matches)) {
			foreach ($matches[0] as $key => $match) {
				$descr = (!empty($matches[2][$key])) 
					? '<div class="atm-img-description">' . h($matches[2][$key]) . '</div>' 
					: '';
				$str = preg_replace('#' . preg_quote($match) . '#uisU', 
					'<a href="' . h($matches[3][$key]) . '" class="gallery" style="float:left;">'
					. '<img' . $styles . ' src="' . h($matches[3][$key]) . '" alt="' . $title 
					. '" title="' . $title . '" />' . $descr . '</a>', $str);
			}
		}
		return $str;
	}
	public function parseUrlBb($str) {
		if (false !== (stripos($str, '[url')) && false !== (stripos($str, '[/url]'))) {
			$noindex = Config::read('use_noindex');
			$redirect = Config::read('redirect_active');
			$url = $redirect ? get_url('redirect.php?url=') : '';

			$str = preg_replace("#\[url\](http[s]*://[\w\d\-_.]*\.\w{2,}[\w\d\-_\\/.\?=\#&;%]*)\[\/url\]#iuU", ($noindex ? '<noindex>' : '') . '<a href="' . $url . '\\1" target="_blank"' . ($noindex ? ' rel="nofollow"' : '') . '>\\1</a>' . ($noindex ? '</noindex>' : ''), $str);
			$str = preg_replace("#\[url=['\"]?(http[s]*://[\w\d\-_.]*\.\w{2,}[\w\d\-_\\/.\?=\#;&%]*)[/]*['\"]?\]([^\[]*)\[/url\]#iuU", ($noindex ? '<noindex>' : '') . '<a href="' . $url . '\\1" target="_blank"' . ($noindex ? ' rel="nofollow"' : '') . '>\\2</a>' . ($noindex ? '</noindex>' : ''), $str);
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
		for ($i = 1; $i < ($cnt + 1); $i++) {
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


