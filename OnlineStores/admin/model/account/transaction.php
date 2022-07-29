<?php

class ModelAccountTransaction extends Model
{
    /**
     * @var string
     */
    private $table = 'transactions';

    /**
     * @var string
     */
    private $database = '';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $live_id;

    /**
     * Insert into transactions table
     *
     * @param array $data
     *
     * @return int
     */
    public function insert(array $data) : int
    {
        $query = $columns = $values = [];
        $query[] = 'INSERT INTO %s';
        foreach ($data as $column => $value) {
            $columns[] = $column;
            $values[] = $value;
        }
        $query[] = '(`%s`)';
        $query[] = 'VALUES';
        $query[] = "('%s')";

        $this->db->query(vsprintf(implode(' ', $query), [
            $this->table,
            implode('`,`', $columns),
            implode("', '", $values)
        ]));

        $columns[] = 'whmcs_user_id';
        $values[] = WHMCS_USER_ID;
        $this->ecusersdb->query(vsprintf(implode(' ', $query), [
            'billing_' . $this->table,
            implode('`,`', $columns),
            implode("', '", $values)
        ]));

        if(isset($data['status']) && $data['status'] == 1)
            $this->trackConfirmPayment($data);

        return $this->db->getLastId();
    }

    /**
     * Select row based on transaction_id column
     *
     * @param string $transactionId
     *
     * @return array
     */
    public function selectByTransactionId($transactionId)
    {
        $query = 'SELECT * FROM %s WHERE transaction_id = "%s"';

        $data = $this->db->query(sprintf($query, $this->table, $transactionId));
        $livedata = $this->ecusersdb->query(sprintf($query, 'billing_' . $this->table, $transactionId));

        if ($livedata->num_rows) {
            $this->live_id = $livedata->row['id'];
        }

        if ($data->num_rows) {
            $this->id = $data->row['id'];
            return $data->row;
        }


        return false;
    }

    public function getTransaction($store_code = STORECODE,$payment_method="paypal")
    {
        $query = 'SELECT * FROM %s WHERE store_code = "%s" and (invoice_id is not null OR invoice_id != "" OR invoice_id != 0) and payment_method ="'.$payment_method.'" order by id desc limit 1';

        $data = $this->db->query(sprintf($query, $this->table, $store_code));
        $livedata = $this->ecusersdb->query(sprintf($query, 'billing_' . $this->table, $store_code));

        if ($livedata->num_rows) {
            $this->live_id = $livedata->row['id'];
        }

        if ($data->num_rows) {
            $this->id = $data->row['id'];
            return $data->row;
        }

        return false;
    }
    /**
     * update transaction by id
     *
     * @param array $data
     *
     * @return bool|int
     */
    public function update(array $data)
    {
        $query = $columns = [];
        $query[] = 'UPDATE %s SET';
        foreach ($data as $column => $value) {
            if (is_string($value)) {
                $columns[] = sprintf('%s = "%s"', $column, addslashes($value));
            } else if (is_int($value)) {
                $columns[] = sprintf('%s = %d', $column, $value);
            }
        }

        if (count($columns) < 1) {
            return false;
        }

        $query[] = implode(', ', $columns);

        $query[] = 'WHERE id = %d';

        $this->db->query(sprintf(implode(' ', $query), $this->table, $this->id));
        $this->ecusersdb->query(sprintf(implode(' ', $query), 'billing_' . $this->table, $this->live_id));

        if(isset($data['status']) && $data['status'] == 1)
            $this->trackConfirmPayment($data);

        return $this->id;
    }


    function trackConfirmPayment($data)
    {

        $plans_id = [3, 53, 6, 8];
        //get previous plan
        $previous_plan = $this->session->data['current_plan'];


        /***************** Start ExpandCartTracking #347715  ****************/
        $event_attributes = [
            '$price' => $data['amount'],
            '$revenue' => $data['amount'],
            'currency' => $data['currency'],
            'payment method' => $data['payment_method'],
            'subscription plan' => $data['plan_id'],
            'payment term' => $data['plan_type'],
            'revenueType' => 'billing',
        ];

        // send mixpanel add payment confirmed and update user products count
        $this->load->model('setting/mixpanel');
        $this->model_setting_mixpanel->trackEvent('Payment Confirmed', $event_attributes);
        $this->model_setting_mixpanel->trackRevenue($data['amount']);
        $this->model_setting_mixpanel->incrementProperty('$Purchases');
        $this->model_setting_mixpanel->updateUser([
            '$previous subscription plan' => $previous_plan,
            '$current subscription plan' => $data['plan_id'],
            '$payment term' => $data['plan_type'],
        ]);

        // send mixpanel payment confirmed event and update user products count
        $this->load->model('setting/amplitude');
        $this->model_setting_amplitude->trackEvent('Payment Confirmed', $event_attributes);
        $this->model_setting_amplitude->updateUser([
            'previous subscription plan'    => $previous_plan,
            'current subscription plan'     => $data['plan_id'],
            'payment term'                  => $data['plan_type'],
        ]);

        /***************** End ExpandCartTracking #347715  ****************/



        /***************** Start ExpandCartTracking #347736 #347737  ****************/

        //check if merchant upgrade or downgrade
        if ($previous_plan != $data['plan_id']) {
            if (array_search($previous_plan, $plans_id) > array_search($data['plan_id'], $plans_id))
                $event_name = "Downgrade Plan";
            else
                $event_name = "Upgrade Plan";

            $meta_data = [
                'Plan ID'       => $data['plan_id'],
                "Plan Name"     => $this->getPlaneNameByCode($data['plan_id']),
                'Payment Term'  => $data['plan_type']
            ];
            $this->model_setting_mixpanel->trackEvent($event_name, $meta_data);
            $this->model_setting_amplitude->trackEvent($event_name, $meta_data);

        }

        /***************** End ExpandCartTracking #347736 #347736  ****************/
    }

    public function getPlaneNameByCode($code){
        $plans = [
            3   => "Free",
            53  => "Professional",
            6   => "Ultimate",
            8   => "Enterprise",
        ];
        return $plans[$code] ?? "not exist";
    }

}
