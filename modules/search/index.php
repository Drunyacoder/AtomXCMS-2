<?php
/*-----------------------------------------------\
| 												 |
|  @Author:       Andrey Brykin (Drunya)         |
|  @Version:      1.8                            |
|  @Project:      CMS                            |
|  @package       CMS Fapos                      |
|  @subpackege    Search Module                  |
|  @copyright     ©Andrey Brykin 2010-2013       |
|  @last mod.     2013/11/11                     |
\-----------------------------------------------*/

/*-----------------------------------------------\
| 												 |
|  any partial or not partial extension          |
|  CMS Fapos,without the consent of the          |
|  author, is illegal                            |
|------------------------------------------------|
|  Любое распространение                         |
|  CMS Fapos или ее частей,                      |
|  без согласия автора, является не законным     |
\-----------------------------------------------*/




class SearchModule extends Module {

	/**
	 * @module_title  title of module
	 */
	public $module_title = 'Поиск';

	/**
	 * @module module indentifier
	 */
	public $module = 'search';

	/**
	 * @var int
	 */
	private $minInputStr = 5;

	/**
	 * @var array
	 */
	public $tables = array('posts', 'stat', 'news', 'loads');

	/**
	 * @var boolean
	 */
	private $returnForm = true;

	/**
	 * @return string - $this->_view
	 *
	 * Doing search and build page with results
	 */
	public function index() {
		//check index
		$this->__checkIndex();


		$minInput = $this->Register['Config']->read('min_lenght', $this->module);
		if (!empty($minInput))
			$this->minInputStr = (int) $minInput;

		$html = null;
		$error = null;
		$results = null;

		if (isset($_POST['m'])) {
			$modules = array();
			foreach ($_POST['m'] as $m) {
				if ($m == 'forum' or $m == 'news'
						or $m == 'stat' or $m == 'loads')
					Array_push($modules, $m);
			}
		} else {
			$modules = array('forum', 'news', 'stat', 'loads');
		}
		$_SESSION['m'] = $modules;

		if (isset($_POST['search']) || isset($_GET['search'])) {
			$str = (isset($_POST['search'])) ? h($_POST['search']) : '';
			if (empty($str))
				$str = (isset($_GET['search'])) ? h($_GET['search']) : '';
			if (!is_string($str))
				$str = (string) $str;
			$str = trim($str);


			if (empty($str) || mb_strlen($str) < $this->minInputStr)
				$error = $error . '<li>' . sprintf(__('Very small query'), $this->minInputStr) . '</li>';


			if ($this->cached) {
				$this->cacheKey .= '_' . md5($str);
				if ($this->Cache->check($this->cacheKey)) {
					$html = $this->Cache->read($this->cacheKey);
					return $this->_view($html);
				}
			}

			$_SESSION['search_query'] = $str;
			if (!empty($error)) {
				$_SESSION['FpsForm'] = array();
				$_SESSION['FpsForm']['error'] = $error;
				redirect($this->getModuleURL());
			}

			
			$str = Plugins::intercept('before_search', $str);
			$results = $this->__search($str, $modules);
			$results = Plugins::intercept('search_results', $results);
			
			if (count($results) && is_array($results)) {
				foreach ($results as $result) {
					if (preg_match('#(.{0,100}' . $str . '.{0,100})#miu', $result->getIndex(), $match)) {
						$announce = $match[1];
					} else {
						$announce = mb_substr($result->getIndex(), 0, 150);
					}
					$title = h($result->getEntity_title());




					$announce = str_replace($str, '<strong>' . $str . '</strong>', h($announce));
					$entry_url = get_url('/' . $result->getModule() . $result->getEntity_view() . $result->getEntity_id());
					$result->setEntry_url($entry_url);
					$result->setTitle($title);
					$result->setAnnounce($announce);
				}
			} else {
				$error = __('No results'); // TODO
			}
		} else {
			$_SESSION['search_query'] = '';
		}



		// Nav block
		$nav = array();
		$nav['navigation'] = get_link(__('Home'), '/') . __('Separator') . $this->module_title;
		$this->_globalize($nav);


		if (!empty($str))
            $this->addToPageMetaContext('entity_title', $str);


		$this->returnForm = false;
		$form = $this->form();
		$source = $this->render('search_list.html', array('context' => array(
				'results' => $results,
				'form' => $form,
				'error' => $error,
				)));


		//write into cache
		if ($this->cached && !empty($str)) {
			//set users_id that are on this page
			$this->setCacheTag(array(
				'search_str_' . $str,
			));
			$this->cacheKey .= '_' . md5($str);
			$this->Cache->write($source, $this->cacheKey, $this->cacheTags);
		}

		return $this->_view($source);
	}

	/**
	 * @return string search form
	 */
	public function form() {
		$markers = array(
			'action' => $this->getModuleURL(),
			'search' => '',
			'forum' => '0',
			'news' => '0',
			'stat' => '0',
			'loads' => '0',
		);


		//if an errors
		if (isset($_SESSION['FpsForm'])) {
			$markers['info'] = $this->render('infomessage.html', array('context' => array(
					'message' => $_SESSION['FpsForm']['error'],
					)));
			unset($_SESSION['FpsForm']);
		}

		$markers['search'] = $_SESSION['search_query'];

		foreach ($_SESSION['m'] as $m) {
			$markers[$m] = 'checked';
		}

		$source = $this->render('search_form.html', array('context' => $markers));
		return ($this->returnForm) ? $this->_view($source) : $source;
	}

	/**
	 * @return boolean
	 */
	private function __checkIndex() {
		$meta_file = ROOT . $this->getTmpPath('meta.dat');
		if (file_exists($meta_file) && is_readable($meta_file)) {
			$meta = unserialize(file_get_contents($meta_file));
			if (!empty($meta['expire']) && $meta['expire'] > time()) {
				return true;
			} else {
				$this->__createIndex();
			}
		} else {
			touchDir(ROOT . $this->getTmpPath());
			$this->__createIndex();
		}

		$index_interval = intval($this->Register['Config']->read('index_interval', $this->module));
		if ($index_interval < 1)
			$index_interval = 1;
		$meta['expire'] = (time() + ($index_interval * 84000));
		file_put_contents($meta_file, serialize($meta));
		return true;
	}

	/**
	 * @param string $str
	 * @return array
	 *
	 * Send request and return search results
	 */
	private function __search($str, $modules) {
		$words = explode(' ', $str);
		$_words = array();
		foreach ($words as $key => $word) {
			$word = $this->__filterText($word);
			if (mb_strlen($word) < $this->minInputStr)
				continue;
			$_words[] = $word;
		}
		if (count($_words) < 1)
			return array();
		$string = resc(implode('* ', $_words) . '*');

		//query
		$limit = intval($this->Register['Config']->read('per_page', $this->module));
		if ($limit < 1)
			$limit = 10;
		$results = $this->Model->getSearchResults($string, $limit, $modules);
		return $results;
	}

	/**
	 *
	 *
	 * Create index for search engine
	 */
	private function __createIndex() {
		if (function_exists('ignore_user_abort'))
			ignore_user_abort();
		if (function_exists('set_time_limit'))
			set_time_limit(180);


		$this->Model->truncateTable();
		foreach ($this->tables as $table) {
			$className = $this->Register['ModManager']->getModelNameFromModule($table);
			$Model = new $className;
			
			for ($i = 0; $i < 10000; $i++) {
				$records = $Model->getCollection(array(), array('limit' => 100, 'page' => $i));
				if (empty($records)) break;


				if (count($records) && is_array($records)) {
					foreach ($records as $rec) {

						switch ($table) {
							case 'news':
							case 'stat':
							case 'loads':
								$text = $rec->getTitle() . ' ' . $rec->getMain() . ' ' . $rec->getTags();
								if (mb_strlen($text) < $this->minInputStr || !is_string($text))
									continue;
								$entity_view = '/view/';
								$module = $table;
								$entity_id = $rec->getId();
								break;

							case 'posts':
								$text = $rec->getMessage();
								$entity_view = '/view_theme/';
								$module = 'forum';
								$entity_id = $rec->getId_theme();
								break;

							case 'themes':
								break;

							default:
								$text = $rec->gettitle() . ' ' . $rec->getMain() . ' ' . $rec->getTags();
								if (mb_strlen($text) < $this->minInputStr || !is_string($text))
									continue;
								$entity_view = '/view/';
								$module = $table;
								break;
						}


						//we must update record if an exists
						$data = array(
							'index' => $text,
							'entity_id' => $entity_id,
							'entity_title' => $rec->getTitle(),
							'entity_table' => $table,
							'entity_view' => $entity_view,
							'module' => $module,
							'date' => new Expr('NOW()'),
						);
						$entity = new SearchEntity($data);
						$entity->save();
					}
				}
			}
		}
	}

	/**
	 * @param string $str
	 * @return string
	 *
	 * Cut HTML and BB tags. Also another chars
	 */
	private function __filterText($str) {
		$str = preg_replace('#<[^>]*>|\[[^\]]*\]|[,\.=\'"\|\{\}/\\_\+\?\#<>:;\)\(`\-0-9]#iu', '', $str);
		//$str = preg_replace('#(^| )[^ ]{1,2}( |$)#iu', ' ', $str);
		//$str_to_array = explode(' ', mb_strtolower($str));
		//$str_to_array = array_unique($str_to_array);
		//$str = implode(' ', $str_to_array);
		return (!empty($str)) ? $str : false;
	}

}