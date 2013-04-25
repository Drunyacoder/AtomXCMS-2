<?php



// properties for system settings and settings that not linked to module
$settingsInfo = array(
	/* HLU */
	'hlu' => array(
		'hlu' => array(
			'type' => 'checkbox',
			'title' => 'Включить ЧПУ',
			'description' => '',
			'checked' => '1',
			'value' => '1',
		),
		'hlu_extention' => array(
			'type' => 'text',
			'title' => 'Окончание URL',
			'description' => 'Например .html',
		),
		'hlu_understanding' => array(
			'type' => 'checkbox',
			'title' => 'Разбор ЧПУ',
			'description' => 'Новые ссылки будут обычными, но обращение через ЧПУ будет поддерживаться для работоспособности старых ссылок',
			'checked' => '1',
		),
	),
	
	/* SITEMAP */
	'sitemap' => array(
		'auto_sitemap' => array(
			'type' => 'checkbox',
			'title' => 'Автогенерация sitemap',
			'description' => '',
			'checked' => '1',
			'value' => '1',
		),
	),

	/* SYS */
	'sys' => array(
		'template' => array(
			'type' => 'select',
			'title' => 'Шаблон',
			'description' => '',
			'options' => $templateSelect,
			'options_attr' => array(
				'onClick' => 'showScreenshot(\'%s\');',
			),
			'input_sufix' => '<img id="screenshot" style="border:1px solid #A3BAE9;" width="200px" height="200px" src="' . getImgPath($config['template']) . '" />',
		),
		'site_title' => array(
			'type' => 'text',
			'title' => 'Название сайта',
			'description' => 'можно использовать в шаблонах как {{ site_title }}',
		),
		'title' => array(
			'type' => 'text',
			'title' => 'Заголовок сайта',
			'description' => 'можно использовать в шаблонах как {{ title }}',
		),
		'meta_keywords' => array(
			'type' => 'text',
			'title' => 'Ключевые слова сайта',
			'description' => 'можно использовать в шаблонах как {{ meta_keywords }}',
		),
		'meta_description' => array(
			'type' => 'text',
			'title' => 'Описание сайта',
			'description' => 'можно использовать в шаблонах как {{ meta_description }}',
		),
		'cookie_time' => array(
			'type' => 'text',
			'title' => 'Время "жизни" cookies в днях',
			'description' => 'в cookies сохраняются логин и пароль пользователя,
 если была выбрана опция "Автоматически входить при каждом посещении"',
		),
		'redirect' => array(
			'type' => 'text',
			'title' => 'Автоматическая переадресация',
			'description' => 'используйте эту опцию что бы перевести пользователя с главной страницы, например, на форум или каталог файлов, или на другой сайт',
		),
		'start_mod' => array(
			'type' => 'text',
			'title' => 'Точка входа',
			'description' => 'Это что-то похожее на переадресацию, но самой переадресации
не происходит. Другими словами сдесь Вы вводите адрес точки входа
и страница по этому адресу будет являться главной страницей сайта.
Вводите сюда только рабочие ссылки и только в пределах сайта. Пример "<b>news/view/1</b>"',
		),
		'max_file_size' => array(
			'type' => 'text',
			'title' => 'Максимальный размер файла вложения',
			'help' => 'Байт',
			'description' => 'которые пользователи смогут выгружать на сайте
Используется во всех модулях где нет собственой подобной настройки',
		),
		
		'img_preview_size' => array(
			'type' => 'text',
			'title' => 'Размер превью',
			'help' => 'Px',
			'description' => 'Автоматически уменьшеных изображений',
		),
		
		'min_password_lenght' => array(
			'type' => 'text',
			'title' => 'Минимальная длина пароля пользователя',
			'description' => '',
		),
		'admin_email' => array(
			'type' => 'text',
			'title' => 'Адрес электронной почты администратора',
			'description' => 'этот e-mail будет указан в поле FROM писем, которое один пользователь напишет
другому; этот же e-mail будет указан в письмах с просьбой активировать учетную
 запись или пароль (в случае его утери)',
		),
		'redirect_delay' => array(
			'type' => 'text',
			'title' => 'Задержка перед редиректом',
			'description' => 'когда пользователь выполняет какое-то действие (например, добавляет сообщение)
 ему выдается сообщение, что "Ваше сообщение было успешно добавлено" и делается
редирект на нужную страницу',
		),
		'time_on_line' => array(
			'type' => 'text',
			'title' => 'Время , в течение которого считается, что пользователь "on-line"',
			'description' => '',
		),
		'open_reg' => array(
			'type' => 'select',
			'title' => 'Режим регистрации',
			'description' => 'Определяет разрешена ли регистрация у Вас на сайте',
			'options' => array(
				'1' => 'Разрешена',
				'0' => 'Запрещена',
			),
		),
		'email_activate' => array(
			'type' => 'checkbox',
			'checked' => '1',
			'value' => '1',
			'title' => 'Требуется ли активация аккаунта по E-mail',
			'description' => '',
		),
		'debug_mode' => array(
			'type' => 'checkbox',
			'checked' => '1',
			'value' => '1',
			'title' => 'Вывод ошибок',
			'description' => '',
		),



		'Какие из последних материалов выводить на главной' => 'Какие из последних материалов выводить на главной',
		'sub_news' => array(
			'type' => 'checkbox',
			'title' => 'Новости',
			'description' => '',
			'checked' => '1',
			'value' => 'news',
			'fields' => 'latest_on_home',
		),
		'sub_stat' => array(
			'type' => 'checkbox',
			'title' => 'Статьи',
			'description' => '',
			'checked' => '1',
			'value' => 'stat',
			'fields' => 'latest_on_home',
		),
		'sub_loads' => array(
			'type' => 'checkbox',
			'title' => 'Загрузки',
			'description' => '',
			'checked' => '1',
			'value' => 'loads',
			'fields' => 'latest_on_home',
		),
		'cnt_latest_on_home' => array(
			'type' => 'text',
			'title' => 'Кол-во материалов на главной',
			'description' => '',
		),
		'announce_lenght' => array(
			'type' => 'text',
			'title' => 'Размер анонса на главной',
			'description' => '',
		),


		'Прочее' => 'Прочее',
		'cache' => array(
			'type' => 'checkbox',
			'title' => 'Кэш',
			'description' => '(Кешировать ли содержимое сайта? Если кэш включен сайт будет работать быстрее
при большой нагрузке, но при маленькой его лучше выключить.)',
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
			'title' => 'Использовать ли дополнительные поля на сайте',
			'description' => 'Замедлит работу сайта. Используйте только если знаете что это и как этим пользоваться.',
			'checked' => '1',
			'value' => '1',
		),
		'allow_html' => array(
			'type' => 'checkbox',
			'title' => 'Разрешить использование HTML в сообщениях',
			'description' => 'Таит угрозу. Включая эту возможность, настройте ее в правах групп.',
			'checked' => '1',
			'value' => '1',
		),
		'allow_smiles' => array(
			'type' => 'checkbox',
			'title' => 'Разрешить использование Смайлов в сообщениях',
			'description' => 'Использовать ли на сайте замену специальных меток на изображения(smiles).',
			'checked' => '1',
			'value' => '1',
		),
	),

	/* SECURE */
	'secure' => array(
		'antisql' => array(
			'type' => 'checkbox',
			'title' => 'Отслеживать попытки SQL иньекций через адресную строку',
			'description' => '(запись ведеться в /sys/logs/antisql.dat)',
			'checked' => '1',
			'value' => '1',
		),
		'anti_ddos' => array(
			'type' => 'checkbox',
			'title' => 'Анти DDOS',
			'description' => 'Анти DDOS защита: (Позволяет снизить риск DDOS атаки)',
			'checked' => '1',
			'value' => '1',
		),
		'request_per_second' => array(
			'type' => 'text',
			'title' => '(DDOS)Максимально допустимое кол-во запросов',
			'description' => '(за одну секунду, с одного диапазона IP адресов)',
		),
		'system_log' => array(
			'type' => 'checkbox',
			'title' => 'Лог действий',
			'description' => 'Вести ли лог действий: (фиксируются действия пользователей)',
			'checked' => '1',
			'value' => '1',
		),
		'max_log_size' => array(
			'type' => 'text',
			'title' => 'Максимально допустимый объем логов',
			'description' => 'Предел занимаемого логами дискового пространства',
		),
		'autorization_protected_key' => array(
			'type' => 'checkbox',
			'title' => 'Защита от перебора пароля',
			'description' => 'Посредством передачи защитного ключа',
			'checked' => '1',
			'value' => '1',
		),
		'session_time' => array(
			'type' => 'text',
			'title' => 'Длительность сессии в админ-панели',
			'description' => 'Если бездействовать в админ-панели больше отведеного времени, придется заново авторизоваться',
		),
	),

    /* COMMON */
    'common' => array(
        'rss_lenght' => array(
            'type' => 'text',
            'title' => 'Максимальная длина анонса RSS',
            'description' => '',
      	),
        'rss_cnt' => array(
            'type' => 'text',
            'title' => 'Количество материалов в RSS',
            'description' => '',
      	),

        'Для каких модулей включить RSS' => 'Для каких модулей включить RSS',
        'rss_news' => array(
             'type' => 'checkbox',
             'title' => 'Новости',
             'description' => '',
             'checked' => '1',
             'value' => '1',
       	),
        'rss_stat' => array(
             'type' => 'checkbox',
             'title' => 'Статьи',
             'description' => '',
             'checked' => '1',
             'value' => '1',
       	),
        'rss_loads' => array(
             'type' => 'checkbox',
             'title' => 'Каталог файлов',
             'description' => '',
             'checked' => '1',
             'value' => '1',
       	),
    ),
	
	/* Watermark */
	'watermark' => array(
		'use_watermarks' => array(
			'type' => 'checkbox',
			'title' => 'Разрешить использование',
			'value' => '1',
			'checked' => '1',
		),
		'watermark_type' => array(
			'type' => 'select',
			'title' => 'Тип',
			'description' => 'Определяет тип водяного знака.',
			'options' => array(
				'1' => 'Текст',
				'0' => 'Картинка',
			),
		),
		'Водяной знак (картинка)' => 'Водяной знак (картинка)',
		'watermark_img' => array(
			'type' => 'file',
			'title' => 'Водяной знак (картинка)',
			'input_sufix_func' => 'showWaterMarkImage',
			'onsave' => array(
				'func' => 'saveWaterMarkImage',
			),
		),

		'Водяной знак (текст)' => 'Водяной знак (текст)',
		'watermark_text' => array(
			'type' => 'text',
			'title' => 'Водяной знак (текст)',
			'input_sufix_func' => 'showWaterMarkText',
		),
		'watermark_text_font' => array(
			'type' => 'select',
			'title' => 'Шрифт',
			'description' => '',
			'options' => $fontSelect,
		),
		'watermark_text_angle' => array(
			'type' => 'select',
			'title' => 'Угол поворота текста',
			'help' => 'Градусы',
			'options' => array(
				'315' => '315',
				'270' => '270',
				'225' => '225',
				'180' => '180',
				'135' => '135',
				'90' => '90',
				'45' => '45',
				'0' => '0 (без поворота)',
			),
		),
		'watermark_text_size' => array(
			'type' => 'text',
			'title' => 'Размер текста',
			'help' => 'px',
		),
		'watermark_text_color' => array(
			'type' => 'select',
			'title' => 'Цвет текста',
			'options' => array(
				'000000' => 'Черный',
				'FF0000' => 'Красный',
				'008000' => 'Зелёный',
				'0000FF' => 'Синий',
				'00FFFF' => 'Аква',        
				'FF00FF' => 'Розовый',
				'808080' => 'Серый',        
				'00FF00' => 'Лаймовый',
				'800000' => 'Темно-бордовый',
				'000080' => 'Темно-синий',
				'808000' => 'Оливковый',
				'800080' => 'Фиолетовый',
				'c0c0c0' => 'Серебряный',
				'008080' => 'Чирок',
				'FFFFFF' => 'Белый',
				'FFFF00' => 'Желтый',
			),
		),
		'watermark_text_border' => array(
			'type' => 'select',
			'title' => 'Цвет контура текста',
			'options' => array(
				'000000' => 'Черный',
				'FF0000' => 'Красный',
				'008000' => 'Зелёный',
				'0000FF' => 'Синий',
				'00FFFF' => 'Аква',        
				'FF00FF' => 'Розовый',
				'808080' => 'Серый',        
				'00FF00' => 'Лаймовый',
				'800000' => 'Темно-бордовый',
				'000080' => 'Темно-синий',
				'808000' => 'Оливковый',
				'800080' => 'Фиолетовый',
				'c0c0c0' => 'Серебряный',
				'008080' => 'Чирок',
				'FFFFFF' => 'Белый',
				'FFFF00' => 'Желтый',
			),
			'onsave' => array(
				'func' => 'saveWaterMarkText',
			),
		),

		'Сохранение результатов' => 'Сохранение результатов',
		'quality_jpeg' => array(
			'type' => 'select',
			'title' => 'Качество картинки (JPEG)',
			'description' => 'Значение от 0 (наихудшее качество, минимальный размер) до 100 (наилучшее качество, максимальный размер). По умолчания используется значение 75.',
			'options' => array(
				'100' => '100 (наилучшее качество)',
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
				'0' => '0 (наихудшее качество)',
			),
		),
		'quality_png' => array(
			'type' => 'select',
			'title' => 'Качество картинки (PNG)',
			'description' => 'Значение от 0 (без сжатия) до 9 (наилучшее сжатие)',
			'options' => array(
				'9' => '9 (наилучшее сжатие)',
				'8' => '8',
				'7' => '7',
				'6' => '6',
				'5' => '5',
				'4' => '4',
				'3' => '3',
				'2' => '2',
				'1' => '1',
				'0' => '0 (без сжатия)',
			),
		),
		
		'Прочее' => 'Прочее',
		'watermark_hpos' => array(
			'type' => 'select',
			'title' => 'Горизонтальное выравнивание водяного знака',
			'options' => array(
				'3' => 'По правому краю изображения',
				'2' => 'По центру изображения',
				'1' => 'По левому краю изображения',
			),
		),
		'watermark_vpos' => array(
			'type' => 'select',
			'title' => 'Вертикальное выравнивание водяного знака',
			'options' => array(
				'3' => 'Снизу изображения',
				'2' => 'По центру изображения',
				'1' => 'Сверху изображения',
			),
		),
		'watermark_alpha' => array(
			'type' => 'select',
			'title' => 'Прозрачность водного знака',
			'description' => 'Значение от 0 (полностью прозрачный) до 100 (полностью непрозрачный).',
			'options' => array(
				'100' => '100 (полностью непрозрачный)',
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
				'0' => '0 (полностью прозрачный)',
			),
		),
	),
	
    /* AUTOTAGS */
    'autotags' => array(
        'autotags_active' => array(
			'type' => 'checkbox',
			'title' => 'Разрешить использование',
			'value' => '1',
			'checked' => '1',
      	),
        'autotags_exception' => array(
            'type' => 'text',
            'title' => 'Исключения',
            'description' => 'Слова которые не будут учитываться',
      	),
        'autotags_priority' => array(
            'type' => 'text',
            'title' => 'Приоритет',
            'description' => 'Слова с приоритетом',
       	),
    ),
	
	/* Links */
	'links' => array(
		'use_noindex' => array(
			'type' => 'checkbox',
			'title' => 'Использовать noindex и nofollow',
			'description' => 'Запрет индексации ссылок',
			'value' => '1',
			'checked' => '1',
		),
		'redirect_active' => array(
			'type' => 'checkbox',
			'title' => 'Включить переадресацию',
			'description' => 'Проверка на наличие домена в белом и черном списках',
			'value' => '1',
			'checked' => '1',
		),
		'url_delay' => array(
			'type' => 'text',
			'title' => 'Задержка перед переходом по ссылке',
			'help' => 'Секунд',
		),
        'blacklist_sites' => array(
            'type' => 'text',
			'title' => 'Черный список сайтов',
            'description' => 'Домены, на которые запрещен автоматический переход',
			'help' => __('Separated by comma'),
      	),
        'whitelist_sites' => array(
            'type' => 'text',
			'title' => 'Белый список сайтов',
            'description' => 'Домены, переход на которые осуществляется без задержек',
			'help' => __('Separated by comma'),
       	),
	),
);
$sysMods = array(
	'sys',
	'hlu',
	'secure',
	'common',
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
			return get_img('/sys/img/' . $settings['watermark_img'], $params);
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