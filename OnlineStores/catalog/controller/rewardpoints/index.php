<?php

	/**
	 * Created by PhpStorm.
	 * User: Applehouse
	 * Date: 10/5/14
	 * Time: 8:08 PM
	 */
	class ControllerRewardPointsIndex extends Controller
	{
		CONST DISABLED = "0";
		CONST ENABLED  = "1";
		public function loadBlockRewardPoints()
		{
            $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

            if($this->config->get('rwp_enabled_module') == self::DISABLED || !$queryRewardPointInstalled->num_rows)
			{
				return false;
			}
			$this->language->load('rewardpoints/index');
			$this->load->model('rewardpoints/spendingrule');
			$this->load->model('rewardpoints/shoppingcartrule');
			$this->load->model('rewardpoints/catalogrule');

			$customer_reward_points = $this->customer->getRewardPoints();

			$this->data['max_redeem_point'] = 0;
			if ($customer_reward_points > 0) {
				$this->data['max_redeem_point'] = $this->session->data['max_redeem_point'];
			}

			$exchange_rate = explode("/", $this->config->get('currency_exchange_rate'));

			$html_awarded        = "";
			$total_reward_points = 0;

			foreach ($this->cart->getProducts() as $product) {
				$original_reward_point = $this->model_rewardpoints_catalogrule->getPoints($product['product_id']);
				if($original_reward_point > 0)
				{
					$reward_point          = $product['quantity'] * $original_reward_point;
					$total_reward_points += (int)$reward_point;
					$html_awarded .= "<li>" . $product['quantity'] . " * ".number_format($original_reward_point)." " . $this->config->get('text_points_'.$this->language->get('code')) . " ".$this->language->get('entry_for_product').": <b>" . $product['name'] . "</b></li>";
				}
			}
			if(isset($this->session->data['shopping_cart_point']) && count($this->session->data['shopping_cart_point']) > 0)
			{
				foreach($this->session->data['shopping_cart_point'] as $rule_id => $cart_point)
				{
					$rule = $this->model_rewardpoints_shoppingcartrule->getRule($rule_id);
					$total_reward_points += (int)$cart_point;
					$html_awarded .= "<li>".number_format($cart_point)." " . $this->config->get('text_points') . " (<b>" . $rule['name'] . "</b>)</li>";
				}
			}

			$this->data['html_awarded']           = $html_awarded;
			$this->data['total_reward_points']    = $total_reward_points;
			$this->data['customer_reward_points'] = number_format($customer_reward_points);

			$this->session->data['html_awarded']        = $html_awarded;
			$this->session->data['total_reward_points'] = $total_reward_points;
			$this->data['exchange_rate']          = array(
				'point' => $exchange_rate[0],
				'rate'  => $this->currency->format($exchange_rate[1], $this->currency->getCode()),
			);

			$data_rule_slider = array();
			if ($this->data['max_redeem_point'] > 10) {
				$step_rule = round($this->data['max_redeem_point'] / 10);
				for ($i = $step_rule; $i <= $this->data['max_redeem_point']; $i += $step_rule) {
					$data_rule_slider[] = $i;
				}
			}

			$points_to_checkout = (isset($this->session->data['points_to_checkout'])) ? $this->session->data['points_to_checkout'] : 0;
			if($points_to_checkout > $this->data['max_redeem_point'])
			{
				$points_to_checkout = 0;
				$this->session->data['points_to_checkout'] = 0;
			}

			$data_slider = array(
				'start'       => (int)$points_to_checkout,
				'step'        => 1,
				'min'         => 0,
				'max'         => (int)$this->data['max_redeem_point'],
				'rule_slider' => $data_rule_slider
			);

			$this->template = 'default/template/rewardpoints/checkout/block_rewardpoints.tpl';
			$this->children = array();

			$html_block = $this->render();

			$this->load->model('rewardpoints/helper');

			$this->data['totals']   =   $this->model_rewardpoints_helper->collectTotal();

			$this->template = 'default/template/rewardpoints/checkout/cart_total.tpl';
			$this->children = array();

			$html_cart_total = $this->render();
			header('Content-Type: application/json');
			echo json_encode(array(
				'html_block'      => $html_block,
				'html_cart_total' => $html_cart_total,
				'data_slider'     => $data_slider
			));
		}

		public function updateRedeemPoint()
		{
            $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

            if($this->config->get('rwp_enabled_module') == self::DISABLED || !$queryRewardPointInstalled->num_rows)
			{
				return false;
			}
			$this->load->model('rewardpoints/helper');

			$this->model_rewardpoints_helper->setPointsToCheckout($this->request->post['reward_point']);

			$this->data['totals']   =   $this->model_rewardpoints_helper->collectTotal();

			$this->template = 'default/template/rewardpoints/checkout/cart_total.tpl';
			$this->children = array();
			header('Content-Type: application/json');
			echo json_encode(array(
				'html'        => $this->render()
			));
		}

		public function getBlockRewardPointsDiscount()
		{
            $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

            if($this->config->get('rwp_enabled_module') == self::DISABLED || !$queryRewardPointInstalled->num_rows)
			{
				return false;
			}
			$this->language->load('rewardpoints/index');
			$this->load->model('rewardpoints/spendingrule');
			$this->model_rewardpoints_spendingrule->getSpendingPoints();
			$this->data['max_redeem_point'] = $this->session->data['max_redeem_point'];

			$data_rule_slider = array();
			if ($this->data['max_redeem_point'] > 10) {
				$step_rule = round($this->data['max_redeem_point'] / 10);
				for ($i = $step_rule; $i <= $this->data['max_redeem_point']; $i += $step_rule) {
					$data_rule_slider[] = $i;
				}
			}

			$points_to_checkout = (isset($this->session->data['points_to_checkout'])) ? $this->session->data['points_to_checkout'] : 0;

			$data_slider = array(
				'start'       => $points_to_checkout,
				'step'        => 1,
				'min'         => 0,
				'max'         => (int)$this->data['max_redeem_point'],
				'rule_slider' => $data_rule_slider
			);
			$this->data['language'] = $this->language;
			$this->data['config'] = $this->config;
			$this->template = 'default/template/rewardpoints/checkout/order/block_rewardpoints.tpl';
			$this->children = array();

			$html_block = $this->render();
			header('Content-Type: application/json');
			echo json_encode(array(
				'html'        => $html_block,
				'data_slider' => $data_slider
			));
		}

		public function quickUpdatePaymentMethod()
		{
			if (!isset($this->request->post['payment_method'])) {
				return;
			} elseif (!isset($this->session->data['payment_methods'][$this->request->post['payment_method']])) {
				return;
			}

			$this->session->data['payment_method'] = $this->session->data['payment_methods'][$this->request->post['payment_method']];
		}
	}
