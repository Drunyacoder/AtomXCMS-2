<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.4.0                         |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Sitemap generator             |
| @copyright     ©Andrey Brykin 2010-2011      |
| @last mod      2011/11/06                    |
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
if (function_exists('set_time_limit')) @set_time_limit(0);
if (function_exists('ignor_user_abort')) @ignor_user_abort();





/**
 * @author      Andrey Brykin
 * @year        2011
 * @url         http://fapos.net
 * @package     Fapos CMS
 * @subpackage  Sitemap Generator
 * @copyright   ©Andrey Brykin
 */
class FpsSitemapGen {

	public $output;
	
	private $host;
	
	private $uniqUrl = array();
	
	private $DB;
	


	public function __construct($params = array()) {
		$this->host = $_SERVER['HTTP_HOST'] . '/';
		$this->uniqUrl[] = 'http://' . $this->host;
		$this->DB = FpsDataBase::get();
	}
	
	
	/**
	 * Create sitemap. Uses Data Base data and users groups rules.
	 * Sitemap urls must be allowed for search bots.
	 * 
	 */
	public function createMap($type = 'xml') {
		$this->getLinks();
		$this->finalizeLinks();
		
		
		if ($type === 'xml') {
			$this->output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
				. '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n"
				. 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n"
				. 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n"
				. 'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";
				
				foreach ($this->uniqUrl as $page) {
					if (substr($page, 0, 7) !== 'http://') $page = 'http://' . $page;
					$this->output .= '<url>' . "\n"
						. '<loc>' . $page . '</loc>' . "\n"
						. '<changefreq>daily</changefreq>' . "\n"
						. '</url>' . "\n";
				}
				
				$this->output .= '</urlset>';
		} else {
			//TODO
		}
		
		file_put_contents(ROOT . '/sitemap.xml', $this->output);
	}
	
	
	
	/**
	 * Delete dublicate and quote chars
	 */
	private function finalizeLinks() {
		$entities = array(
			'&' => '&amp;',
			'"' => '&quot;',
			'\'' => '&apos;',
			'<' => '&lt;',
			'>' => '&gt;',
		);
	
		$this->uniqUrl = array_unique($this->uniqUrl);
		foreach ($this->uniqUrl as $key => $link) {
			$link = trim($link, '/');
			$link = str_replace(array_keys($entities), $entities, $link);
			$this->uniqUrl[$key] = $link;
		}
	}
	
	
	
	/**
	 * build all posible links
	 */
	public function getLinks() {
		// single pages
		$this->uniqUrl[] = $this->host . 'search';
		$this->uniqUrl[] = $this->host . 'chat';
		
	
		// Unique pages (Module pages)
		if (Config::read('active', 'pages')) {
			$htmlpages = $this->DB->select('pages', DB_ALL, array());
			if (count($htmlpages) > 0) {
				foreach ($htmlpages as $htmlpage) {
					$this->uniqUrl[] = $this->host . $htmlpage['id'];
				}
			}
		}
		
		
		// news, stat, loads, foto
		$hluex = Config::read('hlu_extention');
		$hluactive = Config::read('hlu');
		foreach (array('news', 'stat', 'loads', 'foto') as $mkey) {
			if (Config::read('active', $mkey)) {
				$this->uniqUrl[] = $this->host . $mkey;
				if ($mkey != 'foto') $this->uniqUrl[] = $this->host . $mkey . '/rss';
			
				$entities = $this->DB->select($mkey, DB_ALL, array());
				$entitiesc = $this->DB->select($mkey . '_sections', DB_ALL, array());

				if (count($entitiesc) > 0) {
					foreach ($entitiesc as $entityc) {
						$action = '/category/';
						$this->uniqUrl[] = $this->host . $mkey . $action . $entityc['id'];
						
						
						$cntmat = 0;
						if (count($entities) > 0) {
							foreach ($entities as $val) {
								if ($val['category_id'] == $entityc['id']) {
									$cntmat++;
									continue;
								}
							}
							
							$per_page = Config::read('per_page', $mkey);
							$per_page = intval($per_page); 
							if ($per_page < 1) $per_page = 1;
							$pages = ceil($cntmat / $per_page);
							
							for ($i = 2; $i <= $pages; $i++) {
								$this->uniqUrl[] = $this->host . $mkey . $action . $entityc['id'] . '?page=' . $i;
							}
						}
					}
				}
				
				if (count($entities) > 0) {
					foreach ($entities as $entity) {
							
						$hlufile = ROOT . '/sys/tmp/hlu_' . $mkey . '/' . $entity['id'] . '.dat';
						if ($mkey != 'foto' && $hluactive == 1 && file_exists($hlufile)) {
							$hludata = file_get_contents($hlufile);
							if ($hludata) $this->uniqUrl[] = $this->host . $mkey . '/' . $hludata . $hluex;
						} else {
							$this->uniqUrl[] = $this->host . $mkey . '/view/' . $entity['id'];
						}
					}
				}
			}
		}
		
		
		// forum
		if (Config::read('active', 'forum')) {
			$this->uniqUrl[] = $this->host . 'forum';
			
			$cats = $this->DB->select('forum_cat', DB_ALL);
			$forums = $this->DB->select('forums', DB_ALL);
			$themes = $this->DB->select('themes', DB_ALL);

			if (count($cats) > 0) {
				foreach ($cats as $cat) {
					$this->uniqUrl[] = $this->host . 'forum/index/' . $cat['id'];
				}
			}
			
			if (count($forums) > 0) {
				foreach ($forums as $forum) {
					$this->uniqUrl[] = $this->host . 'forum/view_forum/' . $forum['id'];
					if ($forum['themes'] > 1) {
						$per_page = Config::read('themes_per_page', 'forum');
						$per_page = intval($per_page); 
						if ($per_page < 1) $per_page = 1;
						$pages = ceil($forum['themes'] / $per_page);
						
						for ($i = 2; $i <= $pages; $i++) {
							$this->uniqUrl[] = $this->host . 'forum/view_forum/' . $forum['id'] . '?page=' . $i;
						}
					}
				}
			}
			
			if (count($themes) > 0) {
				foreach ($themes as $theme) {
					$this->uniqUrl[] = $this->host . 'forum/view_theme/' . $theme['id'];
					$this->uniqUrl[] = $this->host . 'forum/view_theme/' . $theme['id'] . '?page=99999/';
					if ($theme['posts'] > 1) {
						$per_page = Config::read('posts_per_page', 'forum');
						$per_page = intval($per_page); 
						if ($per_page < 1) $per_page = 1;
						$pages = ceil($theme['posts'] / $per_page);
						
						for ($i = 2; $i <= $pages; $i++) {
							$this->uniqUrl[] = $this->host . 'forum/view_theme/' . $theme['id'] . '?page=' . $i;
						}
					}
				}
				
				$cnt = count($themes);
				$per_page = Config::read('themes_per_page', 'forum');
				$per_page = intval($per_page); 
				if ($per_page < 1) $per_page = 1;
				$pages = ceil($cnt / $per_page);
				
				$this->uniqUrl[] = $this->host . 'forum/last_posts/';
				for ($i = 2; $i <= $pages; $i++) {
					$this->uniqUrl[] = $this->host . 'forum/last_posts/?page=' . $i;
				}
			}
			
			// forum user_posts
			$users =  $this->DB->select('users', DB_ALL, array(
				'joins' => array(
					array(
						'type' => 'LEFT',
						'alias' => 'b',
						'table' => 'posts',
						'cond' => 'b.`id_author` = a.`id`',
					),
					array(
						'type' => 'LEFT',
						'alias' => 'c',
						'table' => 'themes',
						'cond' => 'c.`id` = b.`id_theme`',
					),
				),
				'alias' => 'a',
				'fields' => array(
					'`a`.`id` as id',
					'COUNT(DISTINCT(c.`id`)) as cnt',
				),
				'group' => '`a`.`id`',
			));
			if (count($users) > 0) {
				foreach ($users as $user) {
					$this->uniqUrl[] = $this->host . 'forum/user_posts/' . $user['id'];

					$per_page = Config::read('themes_per_page', 'forum');
					$per_page = intval($per_page); 
					if ($per_page < 1) $per_page = 1;
					$pages = ceil($user['cnt'] / $per_page);
					
					for ($i = 2; $i <= $pages; $i++) {
						$this->uniqUrl[] = $this->host . 'forum/user_posts/' . $user['id'] . '/?page=' . $i;
					}
				}
			}
		}
		
		
		// Users
		if (Config::read('active', 'users')) {
			$this->uniqUrl[] = $this->host . 'users';
			$this->uniqUrl[] = $this->host . 'users/add_form/';
			$this->uniqUrl[] = $this->host . 'users/login_form/';
			
			$users = $this->DB->select('users', DB_ALL);

			if (count($users) > 0) {
				foreach ($users as $user) {
					$this->uniqUrl[] = $this->host . 'users/info/' . $user['id'];
				}
				
				$per_page = Config::read('users_per_page', 'users');
				$per_page = intval($per_page); 
				if ($per_page < 1) $per_page = 1;
				$pages = ceil(count($users) / $per_page);
				
				for ($i = 2; $i <= $pages; $i++) {
					$this->uniqUrl[] = $this->host . 'users/index/?page=' . $i;
				}
			}
		}
	}

}




?>