<?phpclass ChangeCategoryTemplate {    private $configPath = 'config.dat';	private $Register;	public function __construct($params = array()) {		$this->Register = Register::getInstance();	}			public function common($params, $hookName) {		$conf = $this->getConfig();		if (empty($conf['template']) || empty($conf['categories'])) return $params;		        if ($hookName === 'view_category') {            if (is_object($params) && $params instanceof StatCategoriesEntity) {                $path = $params->getPath();                if ((is_array($conf['categories']) && in_array($params->getId(), $conf['categories'])) ||                    preg_match('#\.('.preg_quote(implode("|", $conf['categories'])).')\.#', '.' . $path)) {                    Config::$settings['template'] = $conf['template'];                }            }        }        return $params;	}			private function getConfig()	{		return json_decode(file_get_contents(dirname(__FILE__) . '/' . $this->configPath), true);	}}