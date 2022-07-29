<?php
/**
 * User: Anh TO
 * Date: 10/3/14
 * Time: 4:54 PM
 */

class ModelRewardPointsCatalogRule extends Model
{
    public function getRule($rule_id)
    {
        $table_catalog_rules = DB_PREFIX."catalog_rules";

        $sql = "SELECT * FROM $table_catalog_rules WHERE rule_id = $rule_id AND status = '1'";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function getPoints($product_id){
        $this->load->model('catalog/product');
        $table_product_reward_rules = DB_PREFIX."product_to_reward";
        $reward_point_default = $this->getDefaultRewardPoint($product_id);
        $sql = "SELECT * FROM $table_product_reward_rules WHERE product_id = $product_id";
        $query = $this->db->query($sql);
        $reward_point = $reward_point_default;

        if(count($query->rows) > 0)
        {
            foreach ($query->rows as $rule)
            {
                $rule_data = $this->getRule($rule['rule_id']);
	            if(isset($rule_data['rule_id']))
	            {
		            $customer_group = unserialize($rule_data['customer_group_ids']);

		            if(!$this->customer->isLogged())
		            {
			            $current_customer_group = 99;
		            }
		            else
		            {
			            $current_customer_group = $this->customer->getCustomerGroupId();
		            }
                    /** Check time */
                    $time_now = time();
                    $start_date = $end_date = "";
                    if (!empty($rule_data['end_date']) || !empty($rule_data['start_date'])) {
                        if(!empty($rule_data['start_date']))
                            $start_date = strtotime($rule_data['start_date']);
                        if(!empty($rule_data['end_date']))
                            $end_date = strtotime($rule_data['end_date']);

                        if ((!empty($end_date) && $time_now > $end_date) || (!empty($start_date) && $time_now < $start_date)) {
                            /** Have no open day */
                            continue;
                        }
                    }

		            /** Check customer group */
		            if(!in_array($current_customer_group, $customer_group))
		            {
			            /** Not match with customer group in rule */
			            continue;
		            }

		            $reward_point += $rule['reward_point'];
	            }

            }
        }

        return $reward_point;
    }
    protected function getDefaultRewardPoint($product_id){
        if ($this->customer->isLogged()) {
            $customer_group_id = $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }
        $reward_point_default = $this->db->query("SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = $product_id AND customer_group_id = '" . (int)$customer_group_id . "'");
	    if(isset($reward_point_default->row['points']) && $reward_point_default->row['points'] > 0){
            return $reward_point_default->row['points'];
        }
        return 0;
    }
    public function updatePointToCustomer($order_info)
    {
        $this->db->query("UPDATE ".DB_PREFIX."customer_reward SET status = 1 WHERE order_id = {$order_info['order_id']}");
    }
}
