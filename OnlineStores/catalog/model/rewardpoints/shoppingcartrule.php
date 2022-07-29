<?php

	/**
	 * Created by PhpStorm.
	 * User: ANH
	 * Date: 10/7/14
	 * Time: 10:09 PM
	 */
	class ModelRewardPointsShoppingCartRule extends Model
	{
		CONST ENABLED                         = 1;
		CONST DISABLED                        = 0;
		CONST SPEND_Y_TO_REDEEM_X_POINT       = 4;
		CONST FIXED_REWARDED_POINT_WHOLE_CART = 2;
        CONST STOP_PROCESSING_RULE            = 1;
        CONST START_PROCESSING_RULE           = 2;
        private $_stopFurtherRules =  false;
		public function getShoppingCartPoints()
		{
			$this->load->model('rewardpoints/helper');

			$grandTotal       = $this->model_rewardpoints_helper->getGrandTotal();

			$rules            = $this->getRules();
			$shopping_cart_point = array();

			foreach ($rules as $rule) {
                if($this->_stopFurtherRules){
                    continue;
                }
				$condition = unserialize(base64_decode($rule['conditions_serialized']));

				$customer_group = unserialize($rule['customer_group_ids']);

				if (!$this->customer->isLogged()) {
					$current_customer_group = 99;
				}
				else {
					$current_customer_group = $this->customer->getCustomerGroupId();
				}

                /** Check time */
                $time_now = time();
                $start_date = $end_date = "";
                if (!empty($rule['end_date']) || !empty($rule['start_date'])) {
                    if(!empty($rule['start_date']))
                        $start_date = strtotime($rule['start_date']);
                    if(!empty($rule['end_date']))
                        $end_date = strtotime($rule['end_date']);

                    if ((!empty($end_date) && $time_now >= $end_date) || (!empty($start_date) && $time_now <= $start_date)) {
                        /** Have no open day */
                        continue;
                    }
                }

				/** Check customer group */
				if (!in_array($current_customer_group, $customer_group)) {
					/** Not match with customer group in rule */
					continue;
				}

				if ($this->validateCondition($condition['conditions'])) {
					switch ($rule['actions']) {
						case self::FIXED_REWARDED_POINT_WHOLE_CART:
							$shopping_cart_point[$rule['rule_id']] = $rule['reward_point'];
							break;
						case self::SPEND_Y_TO_REDEEM_X_POINT:
							$shopping_cart_point[$rule['rule_id']] = round($grandTotal * $rule['reward_point'] / $rule['reward_per_spent']);
							break;
					}
                    if ($rule['stop_rules_processing'] == self::START_PROCESSING_RULE) {
                        $this->_stopFurtherRules = true;
                        break;
                    }
				}


			}

			$this->session->data['shopping_cart_point'] = $shopping_cart_point;
		}

		protected function validateCondition($condition)
		{
			$counters    = array();
			$_conditions = array();
			$data_filter = array();

			foreach ($condition as $counter => $rule) {
				if ((int)strpos($counter, "-") == 0) {
					$counters[ $counter ] = $rule;
				}
				else {
					$_conditions[ $rule['type'] ]['operator']  = $rule['operator'];
					$_conditions[ $rule['type'] ]['value']     = $rule['value'];
					$_conditions[ $rule['type'] ]['validated'] = $this->validate($rule['type'], false, $rule['value'], $rule['operator']);
				}
			}

			foreach ($counters as $main_cond) {
				if ($main_cond['aggregator'] == 'all' && $main_cond['value'] == 1) {
					$validate = true;
					foreach ($_conditions as $_cond) {
						if ($this->doComparison($_cond['validated'], "!=", $main_cond['value'])) {
							$validate = false;
							break;
						}
					}
				}
				if ($main_cond['aggregator'] == 'all' && !$main_cond['value']) {
					$validate = false;
					foreach ($_conditions as $_cond) {
						if ($this->doComparison($_cond['validated'], "!=", $main_cond['value'])) {
							$validate = true;
							break;
						}
					}
				}
                if ($main_cond['aggregator'] == 'any' && $main_cond['value'] == 1) {
                    $validate = false;
                    foreach ($_conditions as $_cond) {
                        if ($this->doComparison($_cond['validated'], "==", $main_cond['value'])) {
                            $validate = true;
                            break;
                        }
                    }
                }
                if ($main_cond['aggregator'] == 'any' && !$main_cond['value']) {
                    $validate = false;
                    foreach ($_conditions as $_cond) {
                        if ($this->doComparison($_cond['validated'], "==", $main_cond['value'])) {
                            $validate = true;
                            break;
                        }
                    }
                }

			}

			return $validate;
		}

		public function getRules()
		{
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "shopping_cart_rules WHERE status = " . self::ENABLED." ORDER BY rule_position DESC");

			return $query->rows;
		}

		public function getRule($rule_id)
		{
			$table_shopping_cart_rules = DB_PREFIX."shopping_cart_rules";

			$sql = "SELECT * FROM $table_shopping_cart_rules WHERE rule_id = $rule_id";
			$query = $this->db->query($sql);
			return $query->row;
		}

		protected function validate($model_value = "", $json_output = true, $value_selected = '', $operator)
		{
			$model_value = (empty($model_value)) ? $this->request->post['value'] : $model_value;

			if ($model_value != '') {
				if (strpos($model_value, "|") > -1 && strpos($model_value, "-") > -1) {
					$data   = explode("|", $model_value);
					$model  = $data[0];
					$data   = explode("-", $data[1]);
					$field  = $data[0];
					$type   = $data[1];
					$method = isset($data[2]) ? $data[2] : null;
				}
				else {
					$data  = explode("|", $model_value);
					$model = $data[0];
					$id    = $data[1];

                    switch($model){
                        case 'catalog/option':
                            $option_id = explode("|", $model_value);
                            if(isset($option_id[1]) && is_numeric($option_id[1]))
                                $field = 'option|'.$option_id[1];
                            else
                                return false;
                            break;
                        case 'catalog/attribute':
                            $attribute_group_id = explode("|", $model_value);
                            if(isset($attribute_group_id[1]) && is_numeric($attribute_group_id[1]))
                                $field = 'attribute_id|'.$attribute_group_id[1];
                            else
                                return false;
                            break;
                    }
				}

                if(
                    $model == 'catalog/product' ||
                    $model == 'catalog/category' ||
                    $model == 'catalog/attribute' ||
                    $model == 'catalog/option' ||
                    $model == 'catalog/manufacturer'
                ){
                    $model = "sale/reward_points/catalog";
                    $model_alias = "sale_reward_points_catalog";
                }

				$this->load->model($model);
				$model_alias = str_replace("/", "_", $model);

				return $this->{"model_" . $model_alias}->validate($field, $this->operatorToNature($operator), $value_selected);
			}
		}

		protected function operatorToNature($op)
		{
			$op = htmlspecialchars_decode($op);
			switch ($op) {
				case '>=':
				case '<=':
				case '>':
				case '<':
				case '==':
					return $op;
					break;
				case '{}':
				case '()':
					return "==";
					break;
				case '!{}':
				case '!()':
				case '=!':
					return "!=";
					break;
			}
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
