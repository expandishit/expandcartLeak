<?php
/**
 * Created by ANH To.
 * User: anhto
 * Date: 10/3/14
 * Time: 21:55
 */

class ModelTotalEarnPoint extends Model
{
	CONST DISABLED = "0";
	CONST ENABLED  = "1";
    public function getTotal(&$total_data, &$total, &$taxes)
    {
        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

        if($this->config->get('rwp_enabled_module') == self::DISABLED || !$queryRewardPointInstalled->num_rows)
	    {
		    return false;
	    }

        $this->language->load('rewardpoints/index');
        $this->load->model('rewardpoints/catalogrule');
        $reward_point = 0;
        foreach ($this->cart->getProducts() as $product)
        {
            $reward_point += $product['quantity'] * $this->model_rewardpoints_catalogrule->getPoints($product['product_id']);
        }

	    if(isset($this->session->data['shopping_cart_point']) && count($this->session->data['shopping_cart_point']) > 0)
	    {
		    foreach($this->session->data['shopping_cart_point'] as $rule_id => $cart_point)
		    {
			    $reward_point += (int)$cart_point;
		    }
	    }
		if($reward_point > 0)
		{
			$total_data[] = array(
				'code'       => 'earn_point',
				'title'      => $this->language->get('text_earn_point'),
				'text'       => number_format($reward_point).' '.$this->config->get('text_points_'.$this->language->get('code')),
				'value'      => $reward_point,
				'sort_order' => $this->config->get('earn_point_sort_order')
			);
		}
    }
}
