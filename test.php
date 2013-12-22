<?php
/*
<script type="text/javascript">
(function())();
</script>
*/

include_once '/sys/boot.php';




/*
$str = 'FotoSectionsEntity';
$str = preg_replace('#(?<!^)([A-Z]{1}[a-z]*?)#U', '_$1', $str);
pr($str);
*/


die();
class A {
	private $a;
	private $b;
	
	
	public function test() {
		$this->c = false;
		pr(get_object_vars($this));
	}
}


$obj = new A;
$obj->test();






die();
$array = array(
	'a1' => array(
		'a2' => array(
			'a3' => array(
				'test' => 'bla bla',
			),
		),
	),
);

$obj = new Test;
pr($obj->find($array, array('a1', 'a2', 'a3', 'test')));



class Test {
	
	public static function find($conf, $params) {
		$obj = new self();
		return $obj->__find($conf, $params);
	}
	
	
	private function __find($conf, $params) {
		if (count($params) == 1) 
			return $conf[array_shift($params)];
		
		return $this->__find($conf[array_shift($params)], $params);
	}
}