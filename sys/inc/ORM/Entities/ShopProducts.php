<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    ShopProducts Entity           |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod      2014/05/15                    |
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
class ShopProductsEntity extends FpsEntity
{
	
	protected $id;
	protected $stock_id;
	protected $stock_description;
	protected $attributes_group_id;
	protected $title;
	protected $clean_url_title;
	protected $description;
	protected $category_id;
	protected $vendor_id;
	protected $user_id;
	protected $date;
	protected $orders_cnt;
	protected $comments_cnt;
	protected $available;
	protected $commented;
	protected $view_on_home;
	protected $hide_not_exists;
	protected $article;
	protected $image;
	protected $price;
	protected $discount;
	protected $quantity;

	
	public function save($full = false)
	{
		$params = array(
			'stock_id' => intval($this->stock_id),
			'stock_description' => (string)$this->stock_description,
			'attributes_group_id' => intval($this->attributes_group_id),
			'title' => (string)$this->title,
			'clean_url_title' => (string)$this->clean_url_title,
			'description' => (string)$this->description,
			'category_id' => intval($this->category_id),
			'vendor_id' => intval($this->vendor_id),
			'user_id' => intval($this->user_id),
			'date' => (empty($this->date)) ? new Expr('NOW()') : $this->date,
            'orders_cnt' => intval($this->orders_cnt),
            'comments_cnt' => intval($this->comments_cnt),
            'available' => (!empty($this->available)) ? '1' : '0',
            'commented' => (!empty($this->commented)) ? '1' : '0',
            'view_on_home' => (!empty($this->view_on_home)) ? '1' : '0',
            'hide_not_exists' => (!empty($this->hide_not_exists)) ? '1' : '0',
            'article' => (string)$this->article,
            'image' => (string)$this->image,
			'price' => floatval($this->price),
			'discount' => intval($this->discount),
			'quantity' => intval($this->quantity),
		);
		if ($this->id) $params['id'] = $this->id;

		parent::save('shop_products', $params);
		
		if ($full === true) $this->__saveAttributes();
		return $this->id;
	}

	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$Register['DB']->delete('shop_products', array('id' => $this->id));
	}


    public function setPrice($price)
    {
        $this->price = floatval($price);
    }


    /**
     * Returns the product price without the active discounts.
     * So the getPrice() & getFinal_price() returns different results if a product has discount(s).
     *
     * @return mixed
     */
    public function getFinal_price()
	{
		$discount = $this->getTotalDiscount();
		$price = (!empty($discount))
			? $this->price - ($this->price * ($discount / 100))
			: $this->price;
		return $price;
	}
	
	
	/**
	 * Algorithms:
	 * 1 - Priority: product > vendor > category
	 * 2 - Priority: product > category > vendor
	 * 3 - Amount: vendor + category + product
	 * 4 - Max: vendor | category | product
	 * 5 - Min: vendor | category | product
	 */
	public function getTotalDiscount()
	{
		$discount = intval($this->discount);
		$vendor_discount = (!empty($this->vendor) && is_object($this->vendor))
			? $this->vendor->getDiscount() : 0;
		$category_discount = (!empty($this->category) && is_object($this->category))
			? $this->category->getDiscount() : 0;
			
		$discounts_array = array($discount);
		if ($vendor_discount) $discounts_array[] = $discounts_array;
		if ($category_discount) $discounts_array[] = $category_discount;
		
		$algorithm = Config::read('shop.discount_algorithm');
		switch ($algorithm) {
			case 1:
				if (!$discount && !empty($vendor_discount))
					$discount = $vendor_discount;
				if (!$discount && !empty($category_discount))
					$discount = $category_discount;
				break;
			case 2:
				if (!$discount && !empty($category_discount))
					$discount = $category_discount;
				if (!$discount && !empty($vendor_discount))
					$discount = $vendor_discount;
				break;
			case 3:
				$discount += $vendor_discount + $category_discount;
				break;
			default:
			case 4:
				$discount = max($discounts_array);
				break;
			case 5:
				$discount = min($discounts_array);
				break;
		}
		
		return $discount;
	}


    /**
     * Get|Set the product.attributes_groups.attributes[<name>].content AS product.<name>
     *
     * @param $method
     * @param $params
     * @return null
     */
    public function __call($method, $params)
    {
        if (false !== strpos($method, 'set')) {
            $name = str_replace('set', '', $method);
            $name = strtolower($name);
            if (isset($this->$name)) $this->$name = $params[0];
            else {
                if (!empty($this->attributes) && $this->getAttributes()) {
                    foreach ($this->getAttributes() as $attr) {
                        if ($name === strtolower($attr->getTitle())) {
                            $attr->getContent()->setContent($params[0]);
                            $setted = true;
                        }
                    }
                }
                if (empty($setted)) $this->$name = $params[0];
            }


        } else if (false !== strpos($method, 'get')) {
            $name = str_replace('get', '', $method);
            $name = strtolower($name);
            if (isset($this->$name)) return $this->$name;
            else {
                if (!empty($this->attributes) && $this->getAttributes()) {
                    foreach ($this->getAttributes() as $attr) {
                        if ($name === strtolower($attr->getTitle()) && $attr->getContent())
                            return $attr->getContent()->getContent();
                    }
                }
                return null;
            }
        }
        return;
    }


    /**
     * Saves the all product attributes & their content
     */
    private function __saveAttributes()
    {
        if (empty($this->attributes)) return;
        foreach ($this->attributes as $attr) {
            if (!$attr->getContent()->getId() || $attr->getContent()->getChanged()) {
				if (!$attr->getContent()->getProduct_id()) 
					$attr->getContent()->setProduct_id($this->id);
				$attr->save(true);
            }
        }
    }
}