<?php
/**
 * Created by ANH To.
 * Date: 10/2/14
 * Time: 11:37
 */

class ModelPromotionsRewardPoints extends Model
{
    CONST ENABLED = 1;
    CONST DISABLED = 0;

	protected $type_rewarded = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12);
	protected $type_redeemed = array(0); // redeemed points type is = zero
	public function addShoppingCartRules($data)
	{
		$data['conditions_serialized'] = base64_encode(serialize($data['rule']));
		$data['customer_group_ids'] = serialize($data['customer_group_ids']);
		$sql = "INSERT INTO ".DB_PREFIX."shopping_cart_rules SET ".
			"`rule_id` = null, ".
			"`name` = '".$data['name']."',".
			"`description` = '".$this->db->escape($data['description'])."',".
			"`conditions_serialized` = '".$data['conditions_serialized']."',".
			"`store_view` = '".(isset($data['store_view']) ? $data['store_view'] : 0)."',".
			"`customer_group_ids` = '".$data['customer_group_ids']."',".
			"`start_date` = '".$data['start_date']."',".
			"`end_date` = '".$data['end_date']."',".
			"`actions` = '".$data['actions']."',".
			"`reward_per_spent` = '".$data['reward_per_spent']."',".
			"`reward_point` = '".$data['reward_point']."',".
			"`rule_position` = '".(isset($data['rule_position']) ? $data['rule_position'] : 0)."',".
			"`stop_rules_processing` = '".(isset($data['stop_rules_processing']) ? $data['stop_rules_processing'] : 1)."',".
			"`status` = '".$data['status']."'";

		$this->db->query($sql);
		/** DISPATCH_EVENT:MODEL_AFTER_ADD_DATA_SHOPPING_CART_RULE */
	}

	public function updateShoppingCartRules($data)
	{
		$data['conditions_serialized'] = base64_encode(serialize($data['rule']));
		$data['customer_group_ids'] = serialize($data['customer_group_ids']);
		$sql = "UPDATE ".DB_PREFIX."shopping_cart_rules SET ".
			"`name` = '".$data['name']."',".
			"`description` = '".$this->db->escape($data['description'])."',".
			"`conditions_serialized` = '".$data['conditions_serialized']."',".
			"`store_view` = '".(isset($data['store_view']) ? $data['store_view'] : 0)."',".
			"`customer_group_ids` = '".$data['customer_group_ids']."',".
			"`start_date` = '".$data['start_date']."',".
			"`end_date` = '".$data['end_date']."',".
			"`actions` = '".$data['actions']."',".
			"`reward_per_spent` = '".$data['reward_per_spent']."',".
			"`reward_point` = '".$data['reward_point']."',".
			"`rule_position` = '".(isset($data['rule_position']) ? $data['rule_position'] : 0)."',".
			"`stop_rules_processing` = '".(isset($data['stop_rules_processing']) ? $data['stop_rules_processing'] : 1)."',".
			"`status` = '".$data['status']."' ".
			"WHERE rule_id = '".$data['rule_id']."'";

		$this->db->query($sql);
		/** DISPATCH_EVENT:MODEL_AFTER_UPDATE_DATA_SHOPPING_CART_RULE */
	}

    public function addCatalogRules($data)
    {
        $data['conditions_serialized'] = base64_encode(serialize($data['rule']));
        $data['customer_group_ids'] = serialize($data['customer_group_ids']);
        $sql = "INSERT INTO ".DB_PREFIX."catalog_rules SET ".
                    "`rule_id` = null, ".
                    "`name` = '".$data['name']."',".
                    "`description` = '".$this->db->escape($data['description'])."',".
                    "`conditions_serialized` = '".$data['conditions_serialized']."',".
                    "`store_view` = '".(isset($data['store_view']) ? $data['store_view'] : 0)."',".
                    "`customer_group_ids` = '".$data['customer_group_ids']."',".
                    "`start_date` = '".$data['start_date']."',".
                    "`end_date` = '".$data['end_date']."',".
                    "`actions` = '".$data['actions']."',".
                    "`reward_per_spent` = '".$data['reward_per_spent']."',".
                    "`reward_point` = '".$data['reward_point']."',".
                    "`rule_position` = '".(isset($data['rule_position']) ? $data['rule_position'] : 0)."',".
                    "`stop_rules_processing` = '".(isset($data['stop_rules_processing']) ? $data['stop_rules_processing'] : 0)."',".
                    "`status` = '".$data['status']."'";

        $this->db->query($sql);
	    $rule_id = $this->db->getLastId();
	    /** DISPATCH_EVENT:MODEL_AFTER_ADD_DATA_CATALOG_RULE */
	    if(!empty($data['apply_rule']))
	    {
		    $this->load->model('catalog/rule');

		    $this->model_catalog_rule->applyRule($data['rule']['conditions'], $rule_id);
	    }
    }

    public function updateCatalogRules($data)
    {
        $data['conditions_serialized'] = base64_encode(serialize($data['rule']));
        $data['customer_group_ids'] = serialize($data['customer_group_ids']);
        $sql = "UPDATE ".DB_PREFIX."catalog_rules SET ".
            "`name` = '".$data['name']."',".
            "`description` = '".$this->db->escape($data['description'])."',".
            "`conditions_serialized` = '".$data['conditions_serialized']."',".
            "`store_view` = '".(isset($data['store_view']) ? $data['store_view'] : 0)."',".
            "`customer_group_ids` = '".$data['customer_group_ids']."',".
            "`start_date` = '".$data['start_date']."',".
            "`end_date` = '".$data['end_date']."',".
            "`actions` = '".$data['actions']."',".
            "`reward_per_spent` = '".$data['reward_per_spent']."',".
            "`reward_point` = '".$data['reward_point']."',".
            "`rule_position` = '".(isset($data['rule_position']) ? $data['rule_position'] : 0)."',".
            "`stop_rules_processing` = '".(isset($data['stop_rules_processing']) ? $data['stop_rules_processing'] : 0)."',".
            "`status` = '".$data['status']."' ".
            "WHERE rule_id = '".$data['rule_id']."'";
        $this->db->query($sql);
	    /** DISPATCH_EVENT:MODEL_AFTER_UPDATE_DATA_CATALOG_RULE */
        if(!empty($data['apply_rule']))
        {
            $this->load->model('catalog/rule');

            $this->model_catalog_rule->applyRule($data['rule']['conditions'], $data['rule_id']);
        }
    }

    public function addSpendingRules($data)
    {
        $data['conditions_serialized'] = base64_encode(serialize($data['rule']));
        $data['customer_group_ids'] = serialize($data['customer_group_ids']);
        $sql = "INSERT INTO ".DB_PREFIX."spending_rules SET ".
            "`rule_id` = null, ".
            "`name` = '".$data['name']."',".
            "`description` = '".$this->db->escape($data['description'])."',".
            "`conditions_serialized` = '".$data['conditions_serialized']."',".
            "`store_view` = '".(isset($data['store_view']) ? $data['store_view'] : 0)."',".
            "`customer_group_ids` = '".$data['customer_group_ids']."',".
            "`start_date` = '".$data['start_date']."',".
            "`end_date` = '".$data['end_date']."',".
            "`actions` = '".$data['actions']."',".
            "`reward_per_spent` = '".$data['reward_per_spent']."',".
            "`reward_point` = '".$data['reward_point']."',".
            "`rule_position` = '".(isset($data['rule_position']) ? $data['rule_position'] : 0)."',".
            "`stop_rules_processing` = '".(isset($data['stop_rules_processing']) ? $data['stop_rules_processing'] : 0)."',".
            "`status` = '".$data['status']."'";

	    $this->db->query($sql);
	    /** DISPATCH_EVENT:MODEL_AFTER_ADD_DATA_SPENDING_RULE */
    }

	public function addBehaviorRules($data)
	{
		$data['customer_group_ids'] = serialize($data['customer_group_ids']);
		$sql = "INSERT INTO ".DB_PREFIX."behavior_rules SET ".
			"`rule_id` = null, ".
			"`name` = '".$data['name']."',".
			"`store_view` = '".(isset($data['store_view']) ? $data['store_view'] : 0)."',".
			"`customer_group_ids` = '".$data['customer_group_ids']."',".
			"`actions` = '".$data['actions']."',".
			"`reward_point` = '".$data['reward_point']."',".
			"`status` = '".$data['status']."'";

		$this->db->query($sql);
		/** DISPATCH_EVENT:MODEL_AFTER_ADD_DATA_BEHAVIOR_RULE */
	}

	public function updateBehaviorRules($data)
	{
		$data['customer_group_ids'] = serialize($data['customer_group_ids']);
		$sql = "UPDATE ".DB_PREFIX."behavior_rules SET ".
			"`name` = '".$data['name']."',".
			"`store_view` = '".(isset($data['store_view']) ? $data['store_view'] : 0)."',".
			"`customer_group_ids` = '".$data['customer_group_ids']."',".
			"`actions` = '".$data['actions']."',".
			"`reward_point` = '".$data['reward_point']."',".
			"`status` = '".$data['status']."' ".
			"WHERE rule_id = '".$data['rule_id']."'";
		$this->db->query($sql);
		/** DISPATCH_EVENT:MODEL_AFTER_UPDATE_DATA_BEHAVIOR_RULE */
	}

    public function updateSpendingRules($data)
    {
        $data['conditions_serialized'] = base64_encode(serialize($data['rule']));
        $data['customer_group_ids'] = serialize($data['customer_group_ids']);
        $sql = "UPDATE ".DB_PREFIX."spending_rules SET ".
            "`name` = '".$data['name']."',".
            "`description` = '".$this->db->escape($data['description'])."',".
            "`conditions_serialized` = '".$data['conditions_serialized']."',".
            "`store_view` = '".(isset($data['store_view']) ? $data['store_view'] : 0)."',".
            "`customer_group_ids` = '".$data['customer_group_ids']."',".
            "`start_date` = '".$data['start_date']."',".
            "`end_date` = '".$data['end_date']."',".
            "`actions` = '".$data['actions']."',".
            "`reward_per_spent` = '".$data['reward_per_spent']."',".
            "`reward_point` = '".$data['reward_point']."',".
            "`rule_position` = '".(isset($data['rule_position']) ? $data['rule_position'] : 0)."',".
            "`stop_rules_processing` = '".(isset($data['stop_rules_processing']) ? $data['stop_rules_processing'] : 0)."',".
            "`status` = '".$data['status']."' ".
            "WHERE rule_id = '".$data['rule_id']."'";
        $this->db->query($sql);
	    /** DISPATCH_EVENT:MODEL_AFTER_UPDATE_DATA_SPENDING_RULE */
    }

    public function getCatalogRule($rule_id)
    {
        $table_catalog_rules = DB_PREFIX."catalog_rules";

        $sql = "SELECT * FROM $table_catalog_rules WHERE rule_id = $rule_id";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function getCatalogRuleID()
    {
        $table_catalog_rules = DB_PREFIX."catalog_rules";

        $sql = "SELECT rule_id FROM $table_catalog_rules order by rule_id desc limit 1";
        $query = $this->db->query($sql);
        if($query->num_rows > 0)
            return $query->row['rule_id'];
        else
            return false;
    }

	public function getBehaviorRule($rule_id)
	{
		$table_behavior_rules = DB_PREFIX."behavior_rules";

		$sql = "SELECT * FROM $table_behavior_rules WHERE rule_id = $rule_id";
		$query = $this->db->query($sql);
		return $query->row;
	}

    public function dtSpendingRulesHandler($start=0, $length=10, $search = null, $orderColumn="rule_id", $orderType="ASC") {
        $query = "SELECT * FROM " . DB_PREFIX . "spending_rules";
        //$query = ;
        $total = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $search = $this->db->escape($search);
            $where .= "(name like '%{$search}%' OR status = '{$search}' OR start_date like '%{$search}%' 
                        OR end_date like '%{$search}%' )";
            $query .= " WHERE " . $where;
        }

        $totalFiltered = $this->db->query($query)->num_rows;
        $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $results = $this->db->query($query)->rows;
        $this->load->language('module/reward_points_pro');

        foreach ($results as $key => $result)
        {
            $results[$key]['status_text'] = $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
        }

        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        return $data;
    }
    public function getSpendingRule($rule_id)
    {
        $table_catalog_rules = DB_PREFIX."spending_rules";

        $sql = "SELECT * FROM $table_catalog_rules WHERE rule_id = $rule_id";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function dtShoppingCartRulesHandler($start=0, $length=10, $search = null, $orderColumn="rule_id", $orderType="ASC") {
        $query = "SELECT * FROM " . DB_PREFIX . "shopping_cart_rules";
        //$query = ;
        $total = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $search = $this->db->escape($search);
            $where .= "(name like '%{$search}%' OR status = '{$search}' OR start_date like '%{$search}%' 
                        OR end_date like '%{$search}%' )";
            $query .= " WHERE " . $where;
        }

        $totalFiltered = $this->db->query($query)->num_rows;
        $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $results = $this->db->query($query)->rows;
        $this->load->language('module/reward_points_pro');

        foreach ($results as $key => $result)
        {
            $results[$key]['status_text'] = $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
        }

        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        return $data;
    }

	public function getShoppingCartRule($rule_id)
	{
		$table_shopping_cart_rules = DB_PREFIX."shopping_cart_rules";

		$sql = "SELECT * FROM $table_shopping_cart_rules WHERE rule_id = $rule_id";
		$query = $this->db->query($sql);
		return $query->row;
	}

    public function dtCatalogRulesHandler($start=0, $length=10, $search = null, $orderColumn="rule_id", $orderType="ASC") {
        $query = "SELECT * FROM " . DB_PREFIX . "catalog_rules";
        //$query = ;
        $total = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $search = $this->db->escape($search);
            $where .= "(name like '%{$search}%' OR status = '{$search}' OR start_date like '%{$search}%' 
                        OR end_date like '%{$search}%' )";
            $query .= " WHERE " . $where;
        }

        $totalFiltered = $this->db->query($query)->num_rows;
        $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $results = $this->db->query($query)->rows;
        $this->load->language('module/reward_points_pro');

        foreach ($results as $key => $result)
        {
            $results[$key]['status_text'] = $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
        }

        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        return $data;
    }

    public function getCatalogRules($data = array())
    {
        $table_catalog_rules = DB_PREFIX."catalog_rules";

        $sql = "SELECT * FROM $table_catalog_rules ";

        $sort_data = array(
            'rule_id',
            'rule_name',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY rule_id";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        $query = $this->db->query($sql);
        return $query->rows;
    }

	public function getSpendingRules($data = array())
	{
		$table_spending_rules = DB_PREFIX."spending_rules";

		$sql = "SELECT * FROM $table_spending_rules ";

		$sort_data = array(
			'rule_id',
			'rule_name',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY rule_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);
		return $query->rows;
	}

    public function dtCustomerBehaviorRulesHandler($start=0, $length=10, $search = null, $orderColumn="rule_id", $orderType="ASC") {

	    $this->load->model('promotions/reward_points_transactions');
        $this->load->language('module/reward_points_pro');

	    $query = "SELECT * FROM " . DB_PREFIX . "behavior_rules";
        //$query = ;
        $total = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $search = $this->db->escape($search);
            $where .= "(name like '%{$search}%' OR status = '{$search}' OR reward_point like '%{$search}%' )";
            $query .= " WHERE " . $where;
        }

        $totalFiltered = $this->db->query($query)->num_rows;
        $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $results = $this->db->query($query)->rows;

        foreach ($results as $key => $result)
        {
            $results[$key]['actions'] = $this->model_promotions_reward_points_transactions->behaviorToText($result['actions']);
            $results[$key]['status_text'] = $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
        }

        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        return $data;
    }

	public function getBehaviorRules($data = array())
	{
		$table_behavior_rules = DB_PREFIX."behavior_rules";

		$sql = "SELECT * FROM $table_behavior_rules ";

		$sort_data = array(
			'rule_id',
			'rule_name',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY rule_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getShoppingCartRules($data = array())
	{
		$table_shopping_cart_rules = DB_PREFIX."shopping_cart_rules";

		$sql = "SELECT * FROM $table_shopping_cart_rules ";

		$sort_data = array(
			'rule_id',
			'rule_name',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY rule_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);
		return $query->rows;
	}

    public function dtTransactionHistoryHandler($data,$start=0, $length=10, $search = null, $orderColumn="rule_id", $orderType="ASC") {

        $table_customer = DB_PREFIX."customer";
        $table_customer_reward = DB_PREFIX."customer_reward";
        
        $totalQuery = "SELECT count(cr.customer_reward_id) as totalRedeemedPoints FROM $table_customer_reward as cr LEFT JOIN $table_customer as c ON cr.customer_id = c.customer_id";
        
        $total = $this->db->query($totalQuery)->row['totalRedeemedPoints'];
        
        $query = "SELECT cr.*, c.* FROM $table_customer_reward as cr
			    LEFT JOIN $table_customer as c ON cr.customer_id = c.customer_id";
        
//        $total = $this->db->query($query)->num_rows; we already execute it in line 594
        $where = "";

        if(isset($data['start_date']) && isset($data['end_date']))
        {
            $data['start_date'] = date('Y-m-d', strtotime("-1 day", strtotime($data['start_date'])));
            $data['end_date']   = date('Y-m-d', strtotime("+1 day", strtotime($data['end_date'])));

            if(strtotime($data['start_date']) < strtotime($data['end_date']))
            {
                $where= " cr.date_added >= '".$data['start_date']."' AND cr.date_added <= '".$data['end_date']."'";
                $query .= " WHERE " . $where;
            }
        }

        if(isset($data['start_date']) && !isset($data['end_date']))
        {
            $data['start_date'] = date('Y-m-d', strtotime("-1 day", strtotime($data['start_date'])));
            $where = " cr.date_added >= '".$data['start_date']."'";
            $query .= " WHERE " . $where;
        }
        if(!isset($data['start_date']) && isset($data['end_date']))
        {
            $data['end_date'] = date('Y-m-d', strtotime("+1 day", strtotime($data['end_date'])));
            $where= " cr.date_added <= '".$data['end_date']."'";
            $query .= " WHERE " . $where;
        }

        if (!empty($search)) {
            $search = $this->db->escape($search);
            $where .= " and (c.email like '{$this->db->escape($search)}' OR cr.status = '{$this->db->escape($search)}' )";
            $query .=  $where;
        }

        $totalFiltered = $this->db->query($query)->num_rows;
        $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $results = $this->db->query($query)->rows;
        $this->load->language('module/reward_points_pro');
        $this->load->model('sale/customer');
        $this->load->model('sale/order');

        foreach ($results as $key => $result)
        {
            if($result['customer_id']!=0){
                $customer = $this->model_sale_customer->getCustomer($result['customer_id']);
                $customer_name = $customer['firstname']." ".$customer['lastname'];
                $customer_email=$customer['email'];
            }
            else{
                if($result['order_id'] != 0) {
                    $order = $this->model_sale_order->getOrder($result['order_id']);
                    $customer_name = $order['firstname']." ". $order['lastname'];
                    $customer_email = $order['email'];
                }
            }
            $results[$key]['customer_name']=$customer_name;
            $results[$key]['customer_email']=$customer_email;
            $results[$key]['points']=($result['points'] > 0 ? '+' : '').$result['points'];
            $results[$key]['status_text']=($result['status'] == '1' ? $this->language->get('Complete') : ($result['cr.status'] == '2' ? $this->language->get('Expired') : $this->language->get('Pending')));
        }

        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        return $data;
    }

	public function getTransactions($data = array())
	{
		$table_customer = DB_PREFIX."customer";
		$table_customer_reward = DB_PREFIX."customer_reward";

		$sql = "SELECT cr.* FROM $table_customer_reward as cr
			    LEFT JOIN $table_customer as c ON cr.customer_id = c.customer_id";

		$sort_data = array(
			'customer_reward_id'
		);

		if(isset($data['start_date']) && empty($data['start_date']))
		{
			unset($data['start_date']);
		}

		if(isset($data['end_date']) && empty($data['end_date']))
		{
			unset($data['end_date']);
		}

		$sql .= " WHERE 1 ";
		if(isset($data['start_date']) && isset($data['end_date']))
		{
			$data['start_date'] = date('Y-m-d', strtotime("-1 day", strtotime($data['start_date'])));
			$data['end_date']   = date('Y-m-d', strtotime("+1 day", strtotime($data['end_date'])));

			if(strtotime($data['start_date']) < strtotime($data['end_date']))
			{
				$sql .= " AND cr.date_added >= '".$data['start_date']."' AND cr.date_added <= '".$data['end_date']."'";
			}
		}

		if(isset($data['start_date']) && !isset($data['end_date']))
		{
			$data['start_date'] = date('Y-m-d', strtotime("-1 day", strtotime($data['start_date'])));
			$sql .= " AND cr.date_added >= '".$data['start_date']."'";
		}
		if(!isset($data['start_date']) && isset($data['end_date']))
		{
			$data['end_date']   = date('Y-m-d', strtotime("+1 day", strtotime($data['end_date'])));
			$sql .= "AND cr.date_added <= '".$data['end_date']."'";
		}


		/*
		if(isset($data['filter_email']) && !empty($data['filter_email']))
		{
			$sql .= " AND c.email = '".$this->db->escape($data['filter_email'])."'";
		}

		if(isset($data['filter_status']) && $data['filter_status'] != '')
		{
			$sql .= " AND cr.status = '".$this->db->escape($data['filter_status'])."'";
		}

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY cr.date_added";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " DESC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
        */
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getAllTotalRewards($data){
		$table_customer_reward = DB_PREFIX."customer_reward";
		$total_rewarded = "SUM(IF(transaction_type IN (".implode(",", $this->type_rewarded)."), points, 0)) as total_rewarded";
		$total_redeemed = "SUM(IF(transaction_type IN (".implode(",", $this->type_redeemed)."), IF(points < 0, points * -1, points), 0)) as total_redeemed";
		$total_order    = "COUNT(DISTINCT IF(order_id != 0, order_id, null)) as total_order";

		$sql = "SELECT $total_redeemed, $total_rewarded, $total_order FROM $table_customer_reward";
		if(isset($data['start_date']) && empty($data['end_date']))
		{
			unset($data['start_date']);
		}

		if(isset($data['end_date']) && empty($data['end_date']))
		{
			unset($data['end_date']);
		}

		if(isset($data['start_date']) && isset($data['end_date']))
		{
			$data['start_date'] = date('Y-m-d', strtotime("-1 day", strtotime($data['start_date'])));
			$data['end_date']   = date('Y-m-d', strtotime("+1 day", strtotime($data['end_date'])));

			if(strtotime($data['start_date']) < strtotime($data['end_date']))
			{
				$sql .= " WHERE";
				$sql .= " date_added >= '".$data['start_date']."' AND date_added <= '".$data['end_date']."'";
			}
		}

		if(isset($data['start_date']) && !isset($data['end_date']))
		{
			$data['start_date'] = date('Y-m-d', strtotime("-1 day", strtotime($data['start_date'])));
			$sql .= " WHERE";
			$sql .= " date_added >= '".$data['start_date']."'";
		}
		if(!isset($data['start_date']) && isset($data['end_date']))
		{
			$data['end_date']   = date('Y-m-d', strtotime("+1 day", strtotime($data['end_date'])));
			$sql .= " WHERE";
			$sql .= "date_added <= '".$data['end_date']."'";
		}

		$sql .= " ORDER BY date_added DESC";

		$query = $this->db->query($sql);

		return $query->row;
	}

    public function getTotalCatalogRules(){
        $table_catalog_rules = DB_PREFIX."catalog_rules";

        $sql = "SELECT COUNT(*) AS total FROM $table_catalog_rules ";
        $query = $this->db->query($sql);

        return $query->row['total'];
    }

	public function getTotalSpendingRules(){
		$table_spending_rules = DB_PREFIX."spending_rules";

		$sql = "SELECT COUNT(*) AS total FROM $table_spending_rules ";
		$query = $this->db->query($sql);

		return $query->row['total'];
	}
	public function getTotalBehaviorRules(){
		$table_behavior_rules = DB_PREFIX."behavior_rules";

		$sql = "SELECT COUNT(*) AS total FROM $table_behavior_rules ";
		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalShoppingCartRules(){
		$table_shopping_cart_rules = DB_PREFIX."shopping_cart_rules";

		$sql = "SELECT COUNT(*) AS total FROM $table_shopping_cart_rules ";
		$query = $this->db->query($sql);

		return $query->row['total'];
	}
	public function getTotalTransactions($data){
		$table_customer = DB_PREFIX."customer";
		$table_customer_reward = DB_PREFIX."customer_reward";

		$sql = "SELECT COUNT(*) AS total FROM $table_customer_reward as cr
			    LEFT JOIN $table_customer as c ON cr.customer_id = c.customer_id";

		if(isset($data['start_date']) && empty($data['end_date']))
		{
			unset($data['start_date']);
		}

		if(isset($data['end_date']) && empty($data['end_date']))
		{
			unset($data['end_date']);
		}
		$sql .= " WHERE 1 ";
		if(isset($data['start_date']) && isset($data['end_date']))
		{
			$data['start_date'] = date('Y-m-d', strtotime("-1 day", strtotime($data['start_date'])));
			$data['end_date']   = date('Y-m-d', strtotime("+1 day", strtotime($data['end_date'])));

			if(strtotime($data['start_date']) < strtotime($data['end_date']))
			{
				$sql .= " AND cr.date_added >= '".$data['start_date']."' AND cr.date_added <= '".$data['end_date']."'";
			}
		}

		if(isset($data['start_date']) && !isset($data['end_date']))
		{
			$data['start_date'] = date('Y-m-d', strtotime("-1 day", strtotime($data['start_date'])));
			$sql .= " AND cr.date_added >= '".$data['start_date']."'";
		}
		if(!isset($data['start_date']) && isset($data['end_date']))
		{
			$data['end_date']   = date('Y-m-d', strtotime("+1 day", strtotime($data['end_date'])));
			$sql .= "AND cr.date_added <= '".$data['end_date']."'";
		}

		if(isset($data['filter_email']) && !empty($data['filter_email']))
		{
			$sql .= " AND c.email = '".$this->db->escape($data['filter_email'])."'";
		}

		if(isset($data['filter_status']) && $data['filter_status'] != '')
		{
			$sql .= " AND cr.status = '".$this->db->escape($data['filter_status'])."'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function deleteCatalogRule($rule_id)
	{
		$this->db->query("DELETE FROM ".DB_PREFIX."catalog_rules WHERE rule_id = '" . (int)$rule_id . "'");
	}

	public function deleteShoppingCartRule($rule_id)
	{
		$this->db->query("DELETE FROM ".DB_PREFIX."shopping_cart_rules WHERE rule_id = '" . (int)$rule_id . "'");
	}

	public function deleteSpendingRule($rule_id)
	{
		$this->db->query("DELETE FROM ".DB_PREFIX."spending_rules WHERE rule_id = '" . (int)$rule_id . "'");
	}

	public function deleteBehaviorRule($rule_id)
	{
		$this->db->query("DELETE FROM ".DB_PREFIX."behavior_rules WHERE rule_id = '" . (int)$rule_id . "'");
	}
}
