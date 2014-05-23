<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Forum Entity                  |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/04/03                    |
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
 *
 */
class ShopAttributesContentEntity extends FpsEntity
{
	
	protected $id;
	protected $attribute_id;
	protected $product_id;
	protected $content;


    public function __construct($params = array())
    {
        if (!empty($params) && is_array($params)) {
            foreach ($params as $k => $value) {
                $funcName = 'set' . ucfirst($k);
                $this->$funcName($value);
            }
        }
    }


    public function setContent($content)
    {
        if (!empty($this->parent) && is_object($this->parent))
            $this->beforeSaveContent($content);
        else
            $this->content = $content;
        $this->changed = true;
    }

	
	public function save()
	{
		$params = array(
			'attribute_id' => intval($this->attribute_id),
			'product_id' => intval($this->product_id),
			'content' => $this->content,
		);
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		return $Register['DB']->save('shop_attributes_content', $params);
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$Register['DB']->delete('shop_attributes_content', array('id' => $this->id));
	}


    public function beforeSaveContent($content)
    {
        $attrObj = $this->parent;
        switch ($attrObj->getType()) {
            case 'text':
            case 'textarea':
            default:
                $this->content = trim($content);
                break;
            case 'checkbox':
                $this->content = (!empty($content)) ? '1' : '0';
                break;
            case 'select':
                if ($attrObj->getParams()) {
                    foreach ($attrObj->getParams() as $v) {
                        if ($v === trim($content)) {
                            $this->content = trim($content);
                            break(2);
                        }
                    }
                }
                throw new Exception('Unavailable point "' . h(trim($content))
                    . '" is selected for "' . h($attrObj->getTitle()) . '" attribute.');
                break;
            case 'image':
                if (!empty($_POST['attributes'][$attrObj->getTitle() . '_delete'])) {
                    if ($this->old_content) {
                        $path = ROOT . '/sys/files/shop/' . $this->old_content;
                        if (file_exists($path)) unlink($path);
                        $this->content = '';
                    }
                }
                if (!empty($content) && is_array($content)) {
                    $Register = Register::getInstance();
                    $params = $attrObj->getParams();
                    $_FILES['attach1'] = $content;

                    $Register['Validate']->setRules(array(
                        'upload_image' => array(
                            'files__attach1' => array(
                                'type' => 'image',
                                'max_size' => (!empty($params['max_size']))
                                        ? intval($params['max_size'])
                                        : Config::read('max_attaches_size', 'shop'),
                            ))));
                    $errors = $Register['Validate']->check('upload_image');
                    if (!empty($errors)) throw new Exception($errors);

                    $uploaded = downloadAtomAttaches('shop');
                    if (!empty($uploaded[0]) && !empty($uploaded[0]['filename']))
                        $this->content = $uploaded[0]['filename'];
                }
                break;
        }
    }

}