<?php
/**
 * User: Anh TO
 * Date: 10/3/14
 * Time: 4:50 PM
 */

class ModelRewardPointsHelper extends Model
{
	CONST DISABLED = "0";
	CONST ENABLED  = "1";
	public static $directorySeparator = '';
	private static $_cwd = '';

	public function displayRewardPointProductListing($product, $class = "", $language_text)
    {
        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

        if($this->config->get('rwp_enabled_module') == self::DISABLED || !$queryRewardPointInstalled->num_rows)
	    {
		    return "";
	    }

	    switch($language_text){
		    case 'text_category_earn_point':
                $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

                if($this->config->get('rwp_enabled_module') == self::DISABLED || !$queryRewardPointInstalled->num_rows)
			    {
				    return "";
			    }
			    break;
		    case 'text_product_earn_point':
                $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

                if($this->config->get('rwp_enabled_module') == self::DISABLED || !$queryRewardPointInstalled->num_rows)
			    {
				    return "";
			    }
			    break;
	    }
        $this->language->load('rewardpoints/index');
        $this->load->model('rewardpoints/catalogrule');

        $reward_point = $this->model_rewardpoints_catalogrule->getPoints($product['product_id']);

        $text = sprintf($this->language->get($language_text), number_format($reward_point), $this->config->get("text_points_{$this->language->get('code')}"));
        if($reward_point > 0)
            return "<div class='reward_point $class'><span class='icon'><img src=\"catalog/view/javascript/rewardpoints/image/icon_gift.png\" alt=\"\"/> $text</span></div>";
        return "<div class='reward_point'></div>";
    }

	public function setPointsToCheckout($points)
	{
		if($this->customer->isLogged())
		{
			$points = (float)$points;
			$grandTotal = $this->getGrandTotal();
			$customer_reward_points = (float)$this->customer->getRewardPoints();

			if($points > $customer_reward_points)
			{
				/** Hack point ;)) */
				$points = $customer_reward_points;
			}

			if(isset($this->session->data['max_redeem_points']) && $points > (float)$this->session->data['max_redeem_points'])
			{
				$points = $this->session->data['max_redeem_points'];
			}

			$this->session->data['points_to_checkout'] = $points;
		}
		return false;
	}

	public function exchangePointToMoney($points)
	{
		if ($points < 0) {
			return 0;
		}

		$rate  = explode("/", $this->config->get("currency_exchange_rate"));
		$money = ($points * 1.0 * $rate[1]) / $rate[0];

		return $money;
	}

	public function exchangeMoneyToPoint($money)
	{
		if ($money < 0) {
			return 0;
		}

		$rate  = explode("/", $this->config->get("currency_exchange_rate"));

        if (sizeof($rate) == 2) {
            $points = ($money * 1.0 * $rate[0]) / $rate[1];
        } else {
            $points = 0;
        }

		return $points;
	}

	public function getGrandTotal(&$total_data = array(), $except = array())
	{
		$this->load->model('setting/extension');
		$total = 0;
		$taxes = $this->cart->getTaxes();

		$sort_order = array();

		$results = $this->model_setting_extension->getExtensions('total');
		if(count($except) > 0){
			foreach ($results as $key => $value) {
				if(in_array($value['code'], $except)){
					unset($results[$key]);
				}
			}
		}
		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
			if($result['code'] == 'shipping') continue;
			if ($this->config->get($result['code'] . '_status')) {
				$this->load->model('total/' . $result['code']);

				$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
			}

			$sort_order = array();

			if(count($total_data) > 0)
			{
				foreach ($total_data as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $total_data);
			}
		}

		return $total;
	}

	public function collectTotal()
	{
		$this->getGrandTotal($total_data);
		return $total_data;
	}

	public function doComparison($a, $operator, $b)
	{
		switch ($operator) {
			case '<':
				return ($a <  $b);
				break;
			case '<=':
				return ($a <= $b);
				break;
			case '=':
				return ($a == $b);
				break; // SQL way
			case '==':
				return ($a == $b);
				break;
			case '!=':
				return ($a != $b);
				break;
			case '>=':
				return ($a >= $b);
				break;
			case '>':
				return ($a >  $b);
				break;
		}

		throw new Exception("The {$operator} operator does not exists", 1);
	}

	public function parseCRON(){
		$xmlFiles = glob(self::path(DIR_APPLICATION.'rewardpoints_cron/', true) . '*.xml');
		if(count($xmlFiles) > 0){
			$dom = new DOMDocument('1.0', 'UTF-8');
			foreach($xmlFiles as $modFileKey => $modFile) {
				if(file_exists($modFile)) {

					if(@$dom->load($modFile)) {
						$cron = $dom->getElementsByTagName('cron')->item(0);

						if($cron){
							$tasks = $cron->getElementsByTagName('run');
							foreach($tasks as $job){
								$node = $job->getElementsByTagName('model')->item(0);
								$model = $node->nodeValue;
								if(strpos($model, "::") > -1){
									$method = explode("::", $model);
									if(!empty($method[0])){
										$parseModel = "model_".str_replace("/", "_", $method[0]);
										$this->load->model($method[0]);
										if(method_exists($this->$parseModel, $method[1])){
											$this->$parseModel->{$method[1]}();
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	public static function path($path, $skip_real = false) {
		$tmp = self::$_cwd . $path;
		$realpath = $skip_real ? $tmp : self::_realpath($tmp);
		if(!$realpath) {
			return false;
		}
		return $realpath;
	}

	private static function _realpath($file) {
		$path = realpath($file);
		if(!file_exists($path)) {
			return false;
		}

		if(is_dir($path)) {
			$path = rtrim($path, self::$directorySeparator) . self::$directorySeparator;
		}

		return $path;
	}
}
