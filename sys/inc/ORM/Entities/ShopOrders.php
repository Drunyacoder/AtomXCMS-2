<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    ShopOrders Entity             |
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
class ShopOrdersEntity extends FpsEntity
{
	
	protected $id;
	protected $user_id;
	protected $date;
	protected $status;
	protected $total;
	protected $comment;
	protected $delivery_address;
	protected $delivery_type_id;
	protected $telephone;
	protected $first_name;
	protected $last_name;

	
	public function save()
	{
		$params = array(
			'user_id' => intval($this->user_id),
			'date' => $this->date,
			'status' => (string)$this->status,
			'total' => floatval($this->total),
			'comment' => (string)$this->comment,
			'delivery_address' => (string)$this->delivery_address,
			'delivery_type_id' => intval($this->delivery_type_id),
			'telephone' => (string)$this->telephone,
			'first_name' => (string)$this->first_name,
			'last_name' => (string)$this->last_name,
		);
		if ($this->id) $params['id'] = $this->id;

		return parent::save('shop_orders', $params);
	}
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$Register['DB']->delete('shop_orders', array('id' => $this->id));
	}

	
    public function setTotal($total)
    {
        $this->total = floatval($total);
    }
}