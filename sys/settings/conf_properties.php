<?php



// properties for system settings and settings that not linked to module
$settingsInfo = array(
	/* HLU */
	'hlu' => array(
		'hlu' => array(
			'type' => 'checkbox',
			'title' => __('Enable Human like URL'),
			'description' => '',
			'checked' => '1',
			'value' => '1',
		),
		'hlu_extention' => array(
			'type' => 'text',
			'title' => __('URL sufix'),
			'description' => __('For example .html'),
		),
		'hlu_understanding' => array(
			'type' => 'checkbox',
			'title' => __('Human like URL understanding'),
			'description' => __('URL will be generated as normal URL, but Human like URL will be work for compatibility with old links'),
			'checked' => '1',
			'value' => '1',
		),
	),
	
	/* SITEMAP */
	'sitemap' => array(
		'auto_sitemap' => array(
			'type' => 'checkbox',
			'title' => __('Sitemap autogeneration'),
			'description' => '',
			'checked' => '1',
			'value' => '1',
		),
	),

	/* SYS */
	'sys' => array(
		'template' => array(
			'type' => 'select',
			'title' => __('Template'),
			'description' => '',
			'options' => $templateSelect,
			'options_attr' => array(
				'onClick' => 'showScreenshot(\'%s\');',
			),
			'input_sufix' => '<img id="screenshot" style="border:1px solid #A3BAE9;" width="200px" height="200px" src="' . getImgPath($config['template']) . '" />',
		),
		'site_title' => array(
			'type' => 'text',
			'title' => __('Site name'),
			'description' => sprintf(__('Used in the template as %s'), '{{ site_title }}'),
		),
		'title' => array(
			'type' => 'textarea',
			'title' => __('Site title'),
			'description' => sprintf(__('Used in the template as %s'), '{{ meta_title }} | {{ title }}'),
		),
		'meta_keywords' => array(
			'type' => 'textarea',
			'title' => __('Site keywords'),
			'description' => sprintf(__('Used in the template as %s'), '{{ meta_keywords }}'),
		),
		'meta_description' => array(
			'type' => 'textarea',
			'title' => __('Site description'),
			'description' => sprintf(__('Used in the template as %s'), '{{ meta_description }}'),
		),
		'language' => array(
			'type' => 'select',
			'title' => __('Main language'),
			'description' => __('Used when language is not specified and in the admin panel'),
			'options' => call_user_func(function(){
				$langs = getPermittedLangs();
				$langs_ = array();
				foreach ($langs as $lang) $langs_[$lang] = $lang;
				return $langs_;
			}),
		),
		'permitted_languages' => array(
			'type' => 'text',
			'title' => __('Permitted languages'),
			'description' => sprintf(__('Example: "rus,eng,ger". Use a languages which are available on the site.<br>Now are available: %s'), implode(',', getPermittedLangs())),
		),
		'cookie_time' => array(
			'type' => 'text',
			'title' => __('Cookies life time(days)'),
			'description' => __('Cookies used for "remember me" option when user login'),
		),
		'redirect' => array(
			'type' => 'text',
			'title' => __('Auto redirect'),
			'description' => __('Use this option for redirect user from home page to, for example, forum or loads, or other site'),
		),
		'start_mod' => array(
			'type' => 'text',
			'title' => __('Entry point'),
			'description' => __('It is something like redirect but it isn\'t. You can type any URL from this site here and that page will be available as home page. For example "<b>news/view/1</b>"'),
		),
		'max_file_size' => array(
			'type' => 'text',
			'title' => __('Max an attach file size'),
			'help' => __('Byte'),
			'description' => __('which users can upload to the site. Used in those modules which havn\'t same setting'),
		),
		
		'img_preview_size' => array(
			'type' => 'text',
			'title' => __('Preview size'),
			'help' => 'Px',
			'description' => __('Auto scaled images'),
		),
		
		'min_password_lenght' => array(
			'type' => 'text',
			'title' => __('Minimum user password length'),
			'description' => '',
		),
		'admin_email' => array(
			'type' => 'text',
			'title' => __('Administrator email address'),
			'description' => __('Used in site mailing'),
		),
		'redirect_delay' => array(
			'type' => 'text',
			'title' => __('Delay before redirect'),
			'description' => __('When user does some action(for example add message), info message will be appeared and user will be redirected'),
		),
		'time_on_line' => array(
			'type' => 'text',
			'title' => __('Time during which the user is considered online'),
			'description' => '',
		),
		'open_reg' => array(
			'type' => 'select',
			'title' => __('Registration mode'),
			'description' => __('Allowed registration on the site'),
			'options' => array(
				'1' => __('Allowed'),
				'0' => __('Denied'),
			),
		),
		'email_activate' => array(
			'type' => 'checkbox',
			'checked' => '1',
			'value' => '1',
			'title' => __('Email accaunt activation'),
			'description' => '',
		),
		'debug_mode' => array(
			'type' => 'checkbox',
			'checked' => '1',
			'value' => '1',
			'title' => __('Enable debug'),
			'description' => '',
		),



		__('Which from the last materials will be displayed on home page'),
		'sub_news' => array(
			'type' => 'checkbox',
			'title' => __('News'),
			'description' => '',
			'checked' => '1',
			'value' => 'news',
			'fields' => 'latest_on_home',
		),
		'sub_stat' => array(
			'type' => 'checkbox',
			'title' => __('Stat'),
			'description' => '',
			'checked' => '1',
			'value' => 'stat',
			'fields' => 'latest_on_home',
		),
		'sub_loads' => array(
			'type' => 'checkbox',
			'title' => __('Loads'),
			'description' => '',
			'checked' => '1',
			'value' => 'loads',
			'fields' => 'latest_on_home',
		),
		'cnt_latest_on_home' => array(
			'type' => 'text',
			'title' => __('How many materials will be displayed on home'),
			'description' => '',
		),
		'announce_lenght' => array(
			'type' => 'text',
			'title' => __('Announce length') . ' ' . strtolower(__('On home')),
			'description' => '',
		),


		__('Common'),
		'cache' => array(
			'type' => 'checkbox',
			'title' => __('Cache'),
			'description' => __('If cache is enabled, the site will be load faster'),
			'checked' => '1',
			'value' => '1',
		),
		'templates_cache' => array(
			'type' => 'checkbox',
			'title' => __('Templetes cache'),
			'description' => __('If cache is enabled, the site will be load faster'),
			'checked' => '1',
			'value' => '1',
		),
		'cache_querys' => array(
			'type' => 'checkbox',
			'title' => 'Кэш SQl запросов',
			'description' => '(Кешировать ли результаты SQL запросов? Если кэш включен сайт будет работать быстрее
при большой нагрузке, но при маленькой его лучше выключить.)',
			'checked' => '1',
			'value' => '1',
		),
		'use_additional_fields' => array(
			'type' => 'checkbox',
			'title' => __('Enable additional fields'),
			'description' => __('Enable only you know how to use it'),
			'checked' => '1',
			'value' => '1',
		),
		'allow_html' => array(
			'type' => 'checkbox',
			'title' => __('Enable HTML in messages'),
			'description' => __('May be not secure. If you enable, setup it in users rules'),
			'checked' => '1',
			'value' => '1',
		),
		'allow_smiles' => array(
			'type' => 'checkbox',
			'title' => __('Enable smiles in messages'),
			'description' => __('Replaces special markers to images'),
			'checked' => '1',
			'value' => '1',
		),
	),

	/* SECURE */
	'secure' => array(
		'antisql' => array(
			'type' => 'checkbox',
			'title' => __('SQL injections monitoring'),
			'description' => sprintf(__('Loged into %s'), '/sys/logs/antisql.dat'),
			'checked' => '1',
			'value' => '1',
		),
		'anti_ddos' => array(
			'type' => 'checkbox',
			'title' => __('DDOS protection'),
			'description' => __('Reduces the risk of DDOS attack'),
			'checked' => '1',
			'value' => '1',
		),
		'request_per_second' => array(
			'type' => 'text',
			'title' => __('(DDOS)Max quantity of a queries'),
			'description' => __('per second from one IP range'),
		),
		'system_log' => array(
			'type' => 'checkbox',
			'title' => __('Enable activity log'),
			'description' => __('loged a users activities'),
			'checked' => '1',
			'value' => '1',
		),
		'max_log_size' => array(
			'type' => 'text',
			'title' => __('Max log size'),
			'description' => __('limit of the hard drive space for the log'),
            'help' => __('MB'),
            'onview' => array(
                'division' => 1000000,
            ),
            'onsave' => array(
                'multiply' => 1000000,
            ),
		),
		'autorization_protected_key' => array(
			'type' => 'checkbox',
			'title' => __('Protection from brute force password'),
			'description' => __('via the protection key sending'),
			'checked' => '1',
			'value' => '1',
		),
		'session_time' => array(
			'type' => 'text',
			'title' => __('Duration of the session in the admin panel'),
			'description' => __('if you has no activity during this time, you will have to login again'),
            'help' => __('Seconds'),
		),
	),

    /* COMMON */
    'rss' => array(
        'rss_lenght' => array(
            'type' => 'text',
            'title' => __('Max RSS announce length'),
            'description' => '',
      	),
        'rss_cnt' => array(
            'type' => 'text',
            'title' => __('Quantity of a materials in RSS'),
            'description' => '',
      	),

        __('In which modules RSS is enabled'),
        'rss_news' => array(
             'type' => 'checkbox',
             'title' => __('News'),
             'description' => '',
             'checked' => '1',
             'value' => '1',
       	),
        'rss_stat' => array(
             'type' => 'checkbox',
             'title' => __('Stat'),
             'description' => '',
             'checked' => '1',
             'value' => '1',
       	),
        'rss_loads' => array(
             'type' => 'checkbox',
             'title' => __('Loads'),
             'description' => '',
             'checked' => '1',
             'value' => '1',
       	),
    ),
	
	/* Watermark */
	'watermark' => array(
		'use_watermarks' => array(
			'type' => 'checkbox',
			'title' => __('Enable'),
			'value' => '1',
			'checked' => '1',
		),
		'watermark_type' => array(
			'type' => 'select',
			'title' => __('Type'),
			'description' => __('kind of watermark'),
			'options' => array(
				'1' => __('Text'),
				'0' => __('Image'),
			),
		),
		__('Watermark (image)'),
		'watermark_img' => array(
			'type' => 'file',
			'title' => __('Watermark (image)'),
			'input_sufix_func' => 'showWaterMarkImage',
			'onsave' => array(
				'func' => 'saveWaterMarkImage',
			),
		),

		__('Watermark (text)'),
		'watermark_text' => array(
			'type' => 'text',
			'title' => __('Watermark (text)'),
			'input_sufix_func' => 'showWaterMarkText',
		),
		'watermark_text_font' => array(
			'type' => 'select',
			'title' => __('Font'),
			'description' => '',
			'options' => $fontSelect,
		),
		'watermark_text_angle' => array(
			'type' => 'select',
			'title' => __('Rotation angle of the text'),
			'help' => __('Degrees'),
			'options' => array(
				'315' => '315',
				'270' => '270',
				'225' => '225',
				'180' => '180',
				'135' => '135',
				'90' => '90',
				'45' => '45',
				'0' => '0',
			),
		),
		'watermark_text_size' => array(
			'type' => 'text',
			'title' => __('Text size'),
			'help' => 'px',
		),
		'watermark_text_color' => array(
			'type' => 'select',
			'title' => __('Text color'),
			'options' => array(
				'000000' => array('value' => 'Черный', 'attr' => array('style' => 'color:#000000;')),
				'FF0000' => array('value' => 'Красный', 'attr' => array('style' => 'color:#FF0000;')),
				'008000' => array('value' => 'Зелёный', 'attr' => array('style' => 'color:#008000;')),
				'0000FF' => array('value' => 'Синий', 'attr' => array('style' => 'color:#0000FF;')),
				'00FFFF' => array('value' => 'Аква', 'attr' => array('style' => 'color:#00FFFF;')),
				'FF00FF' => array('value' => 'Розовый', 'attr' => array('style' => 'color:#FF00FF;')),
				'808080' => array('value' => 'Серый', 'attr' => array('style' => 'color:#808080;')),
				'00FF00' => array('value' => 'Лаймовый', 'attr' => array('style' => 'color:#00FF00;')),
				'800000' => array('value' => 'Темно-бордовый', 'attr' => array('style' => 'color:#800000;')),
				'000080' => array('value' => 'Темно-синий', 'attr' => array('style' => 'color:#000080;')),
				'808000' => array('value' => 'Оливковый', 'attr' => array('style' => 'color:#808000;')),
				'800080' => array('value' => 'Фиолетовый', 'attr' => array('style' => 'color:#800080;')),
				'c0c0c0' => array('value' => 'Серебряный', 'attr' => array('style' => 'color:#c0c0c0;')),
				'008080' => array('value' => 'Чирок', 'attr' => array('style' => 'color:#008080;')),
				'FFFFFF' => array('value' => 'Белый', 'attr' => array('style' => 'color:#FFFFFF;')),
				'FFFF00' => array('value' => 'Желтый', 'attr' => array('style' => 'color:#FFFF00;')),
			),
		),
		'watermark_text_border' => array(
			'type' => 'select',
			'title' => __('Text border color'),
			'options' => array(
                '000000' => array('value' => 'Черный', 'attr' => array('style' => 'color:#000000;')),
                'FF0000' => array('value' => 'Красный', 'attr' => array('style' => 'color:#FF0000;')),
                '008000' => array('value' => 'Зелёный', 'attr' => array('style' => 'color:#008000;')),
                '0000FF' => array('value' => 'Синий', 'attr' => array('style' => 'color:#0000FF;')),
                '00FFFF' => array('value' => 'Аква', 'attr' => array('style' => 'color:#00FFFF;')),
                'FF00FF' => array('value' => 'Розовый', 'attr' => array('style' => 'color:#FF00FF;')),
                '808080' => array('value' => 'Серый', 'attr' => array('style' => 'color:#808080;')),
                '00FF00' => array('value' => 'Лаймовый', 'attr' => array('style' => 'color:#00FF00;')),
                '800000' => array('value' => 'Темно-бордовый', 'attr' => array('style' => 'color:#800000;')),
                '000080' => array('value' => 'Темно-синий', 'attr' => array('style' => 'color:#000080;')),
                '808000' => array('value' => 'Оливковый', 'attr' => array('style' => 'color:#808000;')),
                '800080' => array('value' => 'Фиолетовый', 'attr' => array('style' => 'color:#800080;')),
                'c0c0c0' => array('value' => 'Серебряный', 'attr' => array('style' => 'color:#c0c0c0;')),
                '008080' => array('value' => 'Чирок', 'attr' => array('style' => 'color:#008080;')),
                'FFFFFF' => array('value' => 'Белый', 'attr' => array('style' => 'color:#FFFFFF;')),
                'FFFF00' => array('value' => 'Желтый', 'attr' => array('style' => 'color:#FFFF00;')),
			),
			'onsave' => array(
				'func' => 'saveWaterMarkText',
			),
		),

		__('Сompression settings'),
		'quality_jpeg' => array(
			'type' => 'select',
			'title' => sprintf(__('Quality %s'), '(JPEG)'),
			'description' => __('Default') . ' 75',
			'options' => array(
				'100' => '100 (' . __('best') . ')',
				'95' => '95',
				'90' => '90',
				'85' => '85',
				'80' => '80',
				'75' => '75',
				'70' => '70',
				'65' => '65',
				'60' => '60',
				'55' => '55',
				'50' => '50',
				'45' => '45',
				'40' => '40',
				'35' => '35',
				'30' => '30',
				'25' => '25',
				'20' => '20',
				'15' => '15',
				'10' => '10',
				'5' => '5',
				'0' => '0 (' . __('worse') . ')',
			),
		),
		'quality_png' => array(
			'type' => 'select',
			'title' => sprintf(__('Quality %s'), '(PNG)'),
			'options' => array(
				'9' => '9 (' . __('worse') . ')',
				'8' => '8',
				'7' => '7',
				'6' => '6',
				'5' => '5',
				'4' => '4',
				'3' => '3',
				'2' => '2',
				'1' => '1',
				'0' => '0 (' . __('best') . ')',
			),
		),
		
		__('Common'),
		'watermark_hpos' => array(
			'type' => 'select',
			'title' => __('Horizontal alignment of the watermark'),
			'options' => array(
				'3' => __('Right'),
				'2' => __('Center'),
				'1' => __('Left'),
			),
		),
		'watermark_vpos' => array(
			'type' => 'select',
			'title' => __('Vertical alignment of the watermark'),
			'options' => array(
				'3' => __('Bottom'),
				'2' => __('Center'),
				'1' => __('Top'),
			),
		),
		'watermark_alpha' => array(
			'type' => 'select',
			'title' => __('Watermark opacity'),
			'options' => array(
				'100' => '100',
				'95' => '95',
				'90' => '90',
				'85' => '85',
				'80' => '80',
				'75' => '75',
				'70' => '70',
				'65' => '65',
				'60' => '60',
				'55' => '55',
				'50' => '50',
				'45' => '45',
				'40' => '40',
				'35' => '35',
				'30' => '30',
				'25' => '25',
				'20' => '20',
				'15' => '15',
				'10' => '10',
				'5' => '5',
				'0' => '0 (' . __('full opacity') . ')',
			),
		),
	),
	
    /* AUTOTAGS */
    'autotags' => array(
        'autotags_active' => array(
			'type' => 'checkbox',
			'title' => __('Enable'),
			'value' => '1',
			'checked' => '1',
      	),
        'autotags_exception' => array(
            'type' => 'textarea',
            'title' => __('Exceptions'),
            'description' => __('Words that should not be counted'),
      	),
        'autotags_priority' => array(
            'type' => 'textarea',
            'title' => __('Priority'),
            'description' => __('Words that have priority'),
       	),
    ),
	
	/* Links */
	'links' => array(
		'use_noindex' => array(
			'type' => 'checkbox',
			'title' => __('Use noindex & nofollow'),
			'description' => __('disable a links indexing'),
			'value' => '1',
			'checked' => '1',
		),
		'redirect_active' => array(
			'type' => 'checkbox',
			'title' => __('Enable redirect'),
			'description' => __('check the availability of a domain in white or black lists'),
			'value' => '1',
			'checked' => '1',
		),
		'url_delay' => array(
			'type' => 'text',
			'title' => __('Delay before redirect'),
			'help' => __('Seconds'),
		),
        'blacklist_sites' => array(
            'type' => 'text',
			'title' => __('Black list'),
            'description' => __('domains that banned for automatic redirect'),
			'help' => __('Separated by comma'),
      	),
        'whitelist_sites' => array(
            'type' => 'text',
			'title' => __('White list'),
            'description' => __('domains for which the redirect carried without delay'),
			'help' => __('Separated by comma'),
       	),
	),
);
$sysMods = array(
	'sys',
	'hlu',
	'secure',
	'rss',
	'sitemap',
	'watermark',
	'autotags',
	'links',
);
$noSub = array(
	'sys',
	'hlu',
	'sitemap',
	'watermark',
	'autotags',
	'links',
);



if (!function_exists('saveWaterMarkImage')) {
	function saveWaterMarkImage($settings)
	{
		if (isImageFile($_FILES['watermark_img']['type'])) {
			$ext = strchr($_FILES['watermark_img']['name'], '.');
			if (move_uploaded_file($_FILES['watermark_img']['tmp_name'], ROOT . '/sys/img/watermark'.$ext)) {
				$settings['watermark_img'] = 'watermark'.$ext;
			}
		}
		return $settings;
	}
}

if (!function_exists('showWaterMarkImage')) {
	function showWaterMarkImage($settings)
	{
		$params = array(
			'style' => 'max-width:200px; max-height:200px;',
		);

		if (!empty($settings['watermark_img']) 
		&& file_exists(ROOT . '/sys/img/' . $settings['watermark_img'])) {
			return get_img('/sys/img/' . $settings['watermark_img'] . '?rand=' . rand(0,9999999), $params);
		}
		return '';
	}
}

if (!function_exists('saveWaterMarkText')) {
	function saveWaterMarkText($settings)
	{
		$font = ROOT . '/sys/fonts/' . $settings['watermark_text_font'];
		$size = isset($settings['watermark_text_size']) && is_numeric($settings['watermark_text_size']) ? intval($settings['watermark_text_size']) : 14;
		$angle = intval($settings['watermark_text_angle']);
		$text = $settings['watermark_text'];

		$delta = round($size / 50 + 1);

		// Вариант 1
		$text_size = imagettfbbox($size, $angle, $font, $text);

		// Вариант 2
		/*
		$text_size = imagettfbbox($size, 0, $font, $text);
		$rangle = deg2rad($angle);
		for ($i = 0; $i < 8; $i = $i + 2) {
			$x = $text_size[$i];
			$y = $text_size[$i + 1];
			$text_size[$i] = round($x * cos($rangle) + $y * sin($rangle));
			$text_size[$i + 1] = round(- $x * sin($rangle) + $y * cos($rangle));
		}
		*/
		
		$x_ar = array($text_size[0], $text_size[2], $text_size[4], $text_size[6]);
		$y_ar = array($text_size[1], $text_size[3], $text_size[5], $text_size[7]);

		unset($text_size);

		$img_w = round((max($x_ar) - min($x_ar)) * 1.025) + 10 * $delta;
		$img_h = round((max($y_ar) - min($y_ar)) * 1.025) + 10 * $delta;

		$x_center = array_sum($x_ar) / 4;
		$y_center = array_sum($y_ar) / 4;

		for ($i = 0; $i < 4; $i++) {
			$x_ar[$i] = round($x_ar[$i] + $img_w / 2 - $x_center);
			$y_ar[$i] = round($y_ar[$i] + $img_h / 2 - $y_center);
		}

		unset($x_center);
		unset($y_center);

		$pos_x = $x_ar[0];
		$pos_y = $y_ar[0];

		unset($x_ar);
		unset($y_ar);

		$img = imagecreatetruecolor($img_w, $img_h);
		
		$bg_color = imagecolorallocate($img, 254, 254, 254);

		$color = isset($settings['watermark_text_color']) ? hexdec($settings['watermark_text_color']) : 0xFFFFFF;
		$text_color = imagecolorallocate($img, ($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF);
		
		$color = isset($settings['watermark_text_border']) ? hexdec($settings['watermark_text_border']) : 0x000000;
		$border_color = imagecolorallocate($img, ($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF);

		imagecolortransparent($img, $bg_color);
		imagefilledrectangle($img, 0, 0, $img_w - 1, $img_h - 1, $bg_color);

		imagettftext($img, $size, $angle, $pos_x + $delta, $pos_y, $border_color, $font, $text);
		imagettftext($img, $size, $angle, $pos_x - $delta, $pos_y, $border_color, $font, $text);
		imagettftext($img, $size, $angle, $pos_x, $pos_y + $delta, $border_color, $font, $text);
		imagettftext($img, $size, $angle, $pos_x, $pos_y - $delta, $border_color, $font, $text);

		imagettftext($img, $size, $angle, $pos_x, $pos_y, $text_color, $font, $text);
		

		imagepng($img, ROOT . '/sys/img/watermark_text.png', 9);
		imagedestroy($img);
	}
}

if (!function_exists('showWaterMarkText')) {
	function showWaterMarkText($settings)
	{
		$params = array(
			'style' => 'max-width:200px; max-height:200px;',
		);
		$file = '/sys/img/watermark_text.png';
		if (file_exists(ROOT . $file)) {
			return get_img('/sys/img/watermark_text.png', $params);
		}
		return '';
	}
}