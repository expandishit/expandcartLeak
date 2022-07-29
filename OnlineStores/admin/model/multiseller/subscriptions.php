<?php

class ModelMultisellerSubscriptions extends Model
{
    private $plan_table = DB_PREFIX . 'ms_subscriptions';
    private $description_table = DB_PREFIX . 'ms_subscriptions_description';
    private $seller_table = DB_PREFIX . 'ms_seller';
    private $payments_table = DB_PREFIX . 'ms_seller_payments';

    public $errors = [];

    public function getList()
    {
        $queryString = 'SELECT * FROM ' . $this->description_table . ' as d';
        $queryString .= ' INNER JOIN ' . $this->plan_table . ' as p';
        $queryString .= ' ON d.plan_id=p.plan_id';
        $queryString .= ' WHERE language_id=' . $this->config->get('config_language_id');

        $data = $this->db->query($queryString);

        if ($data->num_rows > 0) {
            return ['data' => $data->rows, 'count' => $data->num_rows];
        }

        return false;
    }

    public function newPlan($data)
    {
        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO ' . $this->plan_table . ' SET';

        $fields[] = 'plan_status=' . $this->resolveValue($data['status']);
        $fields[] = 'price=' . $this->resolveValue($data['price']);
        $fields[] = 'maximum_products=' . $this->resolveValue($data['maximum_products']);
        $fields[] = 'period=' . $this->resolveValue($data['period']);
        $fields[] = 'format=' . $this->resolveValue($data['format']);

        $queryString[] = implode(',', $fields);

        $this->db->query(implode(' ', $queryString));

        return $this->db->getLastId();
    }

    public function newPlanDescription($data, $lastId)
    {
        foreach ($data as $id => $language) {
            $queryString = $fields = [];
            $queryString[] = 'INSERT INTO ' . $this->description_table . ' SET';

            $fields[] = 'plan_id=' . $lastId;
            $fields[] = 'language_id=' . $this->resolveValue($id);
            $fields[] = 'title=' . $this->resolveValue($language['title']);
            $fields[] = 'description=' . $this->resolveValue($language['description']);

            $queryString[] = implode(',', $fields);

            $this->db->query(implode(' ', $queryString));
        }
    }

    private  function resolveValue($value)
    {
        return is_numeric($value) ? $value : '"' . $value . '"';
    }

    public function getPlan($id)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->plan_table . ' AS plans';

        $queryString[] = 'LEFT JOIN ' . $this->description_table . ' AS descriptions';
        $queryString[] = 'on plans.plan_id=descriptions.plan_id';
        $queryString[] = 'WHERE plans.plan_id=' . $id;
//        $queryString[] = 'AND descriptions.language_id=' . $this->config->get('config_language_id');

        $data = $this->db->query(implode(' ', $queryString));

        /*
         * We went with this function instead the internal PHP function `array_diff` because array_diff
         * ignores the returned elements with value = 1
         * */
        $arrayDiff = function ($data) {
            $newDataSet = [];

            $row = $data->row;
            $rows = $data->rows;

            $newDataSet['plan_id'] = $row['plan_id'];
            $newDataSet['plan_status'] = $row['plan_status'];
            $newDataSet['price'] = $row['price'];
            $newDataSet['maximum_products'] = $row['maximum_products'];
            $newDataSet['format'] = $row['format'];
            $newDataSet['period'] = $row['period'];
            $newDataSet['discount'] = $row['discount'];

            foreach ($rows as $row) {
                $newDataSet['details'][$row['language_id']] = $row;
            }

            return [
                'num_rows' => $data->num_rows,
                'data' => $newDataSet
            ];
        };

        $newDataSet = $arrayDiff($data);

        if ($newDataSet['num_rows'] > 0) {
            return $newDataSet['data'];
        } else {
            return false;
        }
    }

    public function updatePlan($id, $data)
    {
        $queryString = $fields = [];

        $queryString[] = 'UPDATE ' . $this->plan_table . ' SET';

        $fields[] = 'plan_status=' . $this->resolveValue($data['status']);
        $fields[] = 'price=' . $this->resolveValue($data['price']);
        $fields[] = 'maximum_products=' . $this->resolveValue($data['maximum_products']);
        $fields[] = 'period=' . $this->resolveValue($data['period']);
        $fields[] = 'format=' . $this->resolveValue($data['format']);

        $queryString[] = implode(', ', $fields);

        $queryString[] = 'WHERE plan_id=' . $this->resolveValue($id);

        $this->db->query(implode(' ', $queryString));
    }

    public function updatePlanDescription($id, $data)
    {
        foreach ($data as $language => $row) {
            $queryString = $fields = [];

            $queryString[] = 'UPDATE ' . $this->description_table . ' SET';

            $fields[] = 'title=' . $this->resolveValue($row['title']);
            $fields[] = 'description=' . $this->resolveValue($row['description']);

            $queryString[] = implode(', ', $fields);

            $queryString[] = 'WHERE plan_id=' . $this->resolveValue($id);
            $queryString[] = 'AND language_id=' . $language;

            $this->db->query(implode(' ', $queryString));
        }
    }

    public function deletePlan($id)
    {
        $queryString  = [];
        $queryString[] = 'DELETE ' . $this->plan_table . ',' . $this->description_table;
        $queryString[] = 'FROM ' . $this->plan_table . ',' . $this->description_table;
        $queryString[] = 'WHERE ' . $this->plan_table . '.plan_id=' . $id;
        $queryString[] = 'AND ' . $this->description_table . '.plan_id=' . $id;

        $this->db->query(implode(' ', $queryString));
    }

    public function listDetailedPlansBySeller($sellerId)
    {
        $querySyting = [];
        $querySyting[] = 'SELECT * FROM ' . $this->payments_table . ' AS pt';
        $querySyting[] = 'LEFT JOIN ' . $this->plan_table . ' AS pn';
        $querySyting[] = 'ON pt.plan_id=pn.plan_id';
        $querySyting[] = 'LEFT JOIN ' . $this->description_table . ' AS dn';
        $querySyting[] = 'ON dn.plan_id=pn.plan_id';
        #$querySyting[] = 'LEFT JOIN ' . $this->seller_table . ' AS sl';
        #$querySyting[] = 'ON dn.plan_id=sl.subscription_plan';
        $querySyting[] = 'WHERE pt.seller_id=' . $sellerId;
        $querySyting[] = 'AND dn.language_id=' . $this->config->get('config_language_id');

        $querySyting = implode(' ', $querySyting);

        $data = $this->db->query($querySyting);

        return $data->rows;
    }

    public function validate($data)
    {
        $plan = $data['plans'];
        $details = $data['details'];


        if (!preg_match('#^[0-9]+[0-9\,\.]*[0-9]*$#', $plan['price'])) {
            $this->errors[] = $this->language->get('ms_errors_invalid_price_field');
        }

        foreach ($details as $detail) {
            if (!$detail['title'] || strlen($detail['title']) === 0) {
                $this->errors[] = $this->language->get('ms_errors_invalid_title_field');
            }
        }

        if (count($this->errors) == 0) {

            return true;
        }
        $this->errors['warning'] = $this->language->get('error_warning');
        return false;
    }
    
    public function getPlanBySellerId($seller_id)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->plan_table . ' as p';
        $queryString[] = 'LEFT JOIN ' . $this->seller_table . ' as s';
        $queryString[] = 'ON p.plan_id=s.subscription_plan';
        $queryString[] = 'WHERE s.seller_id=' . $seller_id;
        $queryString[] = 'AND p.plan_status=1';

        $data = $this->db->query(implode(' ', $queryString));

        array_walk($data->rows, function (&$value, $key) {
            $value['formated_price'] = $this->currency->format(
                number_format($value['price'], 2, '.', '')
            );
            $value['formatted_payment'] = $value['period'] . ' ' .
                $this->language->get('payment_type_' . $value['format']);
        });

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return null;
    }
}
