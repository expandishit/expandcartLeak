<?php
/**
 * Created by PhpStorm.
 * User: ANH To
 * Date: 10/6/14
 * Time: 9:37 PM
 */

class ModelTotalRedeemPoint extends Model
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
		if($this->customer->isLogged())
		{
			$this->language->load_json('rewardpoints/index');
			$this->load->model('rewardpoints/spendingrule');
			$this->load->model('rewardpoints/helper');

			if(isset($this->session->data['points_to_checkout']) && $this->session->data['points_to_checkout'] > 0)
			{
				$points_to_checkout = $this->session->data['points_to_checkout'];
				$points_to_money    = $this->model_rewardpoints_helper->exchangePointToMoney($points_to_checkout);

				$total -= $points_to_money;

				if($total < 0)
				{
					$total = 0;
				}

				if($points_to_money < 0)
				{
					$points_to_money = 0;
				}

				$total_data[] = array(
					'code'       => 'redeem_point',
					'title'      => sprintf($this->language->get('you_redeemed_points'), number_format($points_to_checkout), $this->config->get('text_points_'.$this->language->get('code'))),
					'text'       => "-".$this->currency->format($points_to_money, $this->currency->getCode()),
					'value'      => -$points_to_money,
					'sort_order' => $this->config->get('redeem_point_sort_order')
				);
			}
		}
	}
}
