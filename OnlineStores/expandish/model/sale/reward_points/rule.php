<?php
/**
 * Created by PhpStorm.
 * User: ANH To
 * Date: 10/6/14
 * Time: 10:35 AM
 */

class ModelSaleRewardPointsRule extends Model
{
    public function validate($field, $op, $value)
    {
        switch($field)
        {
            case 'subtotal':
                $sub_total = $this->cart->getSubTotal();

                if (isset($this->session->data['vouchers']) && $this->session->data['vouchers']) {
                    foreach ($this->session->data['vouchers'] as $voucher) {
                        $sub_total += $voucher['amount'];
                    }
                }

                return $this->doComparison($sub_total, $op, $value);
                break;
            case 'quantity':
                $quantity = $this->cart->countProducts();

                return $this->doComparison($quantity, $op, $value);
                break;

            case 'weight':
                $weight = $this->cart->getWeight();

                return $this->doComparison($weight, $op, $value);
                break;
	        case 'payment_method':
				if(isset($this->session->data['payment_method']))
				{
					return $this->doComparison($this->session->data['payment_method']['code'], $op, $value);
				}
		        break;
	        case 'shipping_method':
		        if(isset($this->session->data['shipping_method']))
		        {
			        return $this->doComparison($this->session->data['shipping_method']['code'], $op, $value.".".$value);
		        }
		        break;
	        case 'shipping_postcode':
		        if(isset($this->session->data['shipping_postcode']))
		        {
			        return $this->doComparison($this->session->data['shipping_postcode'], $op, $value.".".$value);
		        }
		        break;
	        case 'shipping_region':
	        case 'shipping_state_province':
		        if(isset($this->session->data['shipping_zone_id']))
		        {
			        return $this->doComparison($this->session->data['shipping_zone_id'], $op, $value.".".$value);
		        }
		        break;
	        case 'shipping_country':
		        if(isset($this->session->data['shipping_country_id']))
		        {
			        return $this->doComparison($this->session->data['shipping_country_id'], $op, $value.".".$value);
		        }
		        break;
        }

        return false;
    }

	public function doComparison($a, $operator, $b)
	{
		switch ($operator) {
			case '<':
				return ($a < $b);
				break;
			case '<=':
				return ($a <= $b);
				break;
			case '=':
				return ($a == $b);
				break; // SQL way
			case '==':
			case '{}':
			case '()':
				return ($a == $b);
				break;
			case '!=':
			case '!{}':
			case '!()':
			case '=!':
				return ($a != $b);
				break;
			case '>=':
				return ($a >= $b);
				break;
			case '>':
				return ($a > $b);
				break;
		}
	}
}
