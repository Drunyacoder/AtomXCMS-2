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
class ShopAttributesEntity extends FpsEntity
{
	
	protected $id;
	protected $group_id;
	protected $title;
	protected $label;
	protected $type;
	protected $is_filterable;
	protected $params;

	
	public function save($full = false)
	{
        if ($full === true) $this->__saveContent();
		$params = array(
			'group_id' => intval($this->group_id),
			'title' => $this->title,
			'label' => $this->label,
			'type' => $this->type,
			'is_filterable' => intval($this->is_filterable),
			'params' => (is_array($this->params)) ? json_encode($this->params) : $this->params,
		);
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		return $Register['DB']->save('shop_attributes', $params);
	}


    public function getContent()
    {
        if (!isset($this->content)) {
            $contentEntity = new ShopAttributesContentEntity(array(
                'attribute_id' => $this->getId(),
                'product_id' => $this->getId(),
                'content' => '',
            ));
            $this->content = $contentEntity;
        }
        $this->content->parent = $this;
        return $this->content;
    }


    public function getParams($asJson = false)
    {
        if ($asJson === true) {
            return (is_array($this->params)) ? json_encode($this->params) : $this->params;
        }
        return (is_array($this->params)) ? $this->params : json_decode($this->params, true);
    }


    public function getInputField()
    {
        $attr_content = (is_object($this->content))
            ? $this->content->getContent()
            : '';
        switch ($this->type) {
            case 'text':
            default:
                $out = '<input type="text" name="attributes[' . h($this->title) . ']" value="' . h($attr_content) . '" />';
                break;
            case 'textarea':
                $out = '<textarea style="height:100px;" name="attributes[' . h($this->title) . ']">' . h($attr_content) . '</textarea>';
                break;
            case 'checkbox':
                $id = md5($this->id . rand() . rand());
                $out = '<input type="checkbox" name="attributes[' . h($this->title) . ']" value="1"'
                    . ((!empty($attr_content)) ? ' checked="checked"' : '') . ' id="' . $id . '"/>'
                    . '<label for="' . $id . '"></label>';
                break;
            case 'select':
                $out = '<select name="attributes[' . h($this->title) . ']">';
                if ($this->getParams()) {
                    foreach ($this->getParams() as $v) {
                        $out .= '<option value="' . h($v) . '"'
                            . (($v === $attr_content) ? ' selected="selected"' : '')
                            . '>' . h($v) . '</option>';
                    }
                }
                $out .= '</select>';
                break;
            case 'image':
                $image_url = (!empty($attr_content))
                    ? get_url(WWW_ROOT . '/image/shop/' . $attr_content . '/200/')
                    //? get_url(WWW_ROOT . '/sys/files/shop/' . $attr_content)
                    : false;
                $out = '<input type="file" name="attributes_' . h($this->title) . '" value="" />';
                if ($image_url)
                    $out .= '<img title="' . h($this->label) . '" label="' . h($this->label)
                        . '" src="' . $image_url . '">';
                break;
        }
        return $out;
    }

	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$Register['DB']->delete('shop_attributes', array('id' => $this->id));
	}


    private function __saveContent()
    {
        if (!$this->getContent()) return;
        if ($this->getContent()->getChanged() || !$this->getContent()->getId()) {
            $this->getContent()->save();
        }
    }
}