<?php

class ModelAccountAccount extends Model
{
    /**
     * @var string
     */
    private $apiUri = BILLING_API_URL;

    /**
     * @var string
     */
    private $username = BILLING_API_USERNAME;

    /**
     * @var string
     */
    private $password = BILLING_API_PASSWORD;

    /**
     * @var string
     */
    private $apiIdentifier = 'DPPwfOLuKT0CssjVfJFL8VC1Hh5x8gp4';

    /**
     * @var string
     */
    private $apiSecret = '0Qj7xx0othwVNOvhcVUJ2aoV4FoJ6ZOX';

    ///////////////////////////////////


    /**
     * @var string
     */
    private $table = 'accounts';

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
     * Insert into self::$table table
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

        return $this->db->getLastId();
    }

    /**
     * Select row based on storecode column
     *
     * @param string $storecode
     *
     * @return array
     */
    public function selectByStoreCode($storecode)
    {
        $query = 'SELECT * FROM %s WHERE storecode = "%s"';

        $data = $this->db->query(sprintf($query, $this->table, $storecode));
        $livedata = $this->ecusersdb->query(sprintf($query, 'billing_' . $this->table, $storecode));

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
     * update self::$table by id
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

        return $this->id;
    }

    public function updateByStoreCode(array $data)
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

        $query[] = "WHERE storecode = '%s'";

        $this->db->query(sprintf(implode(' ', $query), $this->table, STORECODE));
        $this->ecusersdb->query(sprintf(implode(' ', $query), 'billing_' . $this->table, STORECODE));

        return $this->id;
    }

    private $flows = [
        '3m-53m' => 'u',
        '3m-53a' => 'u',
        '3m-6m' => 'u',
        '3m-6a' => 'u',
        '3m-8m' => 'u',
        '3m-8a' => 'u',

        '53m-3m' => 'd',
        // '53m-53m' => 's',
        '53m-53a' => 'u',
        '53m-6m' => 'u',
        '53m-6a' => 'u',
        '53m-8m' => 'u',
        '53m-8a' => 'u',

        '53a-3m' => 'd',
        '53a-53m' => 'd',
        // '53a-53a' => 's',
        '53a-6m' => 'u',
        '53a-6a' => 'u',
        '53a-8m' => 'u',
        '53a-8a' => 'u',

        '6m-3m' => 'd',
        '6m-53m' => 'd',
        '6m-53a' => 'd',
        // '6m-6m' => 's',
        '6m-6a' => 'u',
        '6m-8m' => 'u',
        '6m-8a' => 'u',

        '6a-3m' => 'd',
        '6a-53m' => 'd',
        '6a-53a' => 'd',
        '6a-6m' => 'd',
        // '6a-6a' => 's',
        '6a-8m' => 'u',
        '6a-8a' => 'u',

        '8m-3m' => 'd',
        '8m-53m' => 'd',
        '8m-53a' => 'd',
        '8m-6m' => 'd',
        '8m-6a' => 'd',
        // '8m-8m' => 's',
        '8m-8a' => 'u',

        '8a-3m' => 'd',
        '8a-53m' => 'd',
        '8a-53a' => 'd',
        '8a-6m' => 'd',
        '8a-6a' => 'd',
        '8a-8m' => 'd',
        // '8a-8a' => 's',
    ];

    public function upgradeAccount($request, $account)
    {
        $flow = $this->detectFlow($request, $account);
        var_dump($flow->isValid());
        dd([$flow, $request]);

        if (!$flow->isValid()) {
            throw new \Exception('Invalid action');
        }

        if ($flow->direction == 'd') {
            throw new \Exception('downgrade is not supported');
        }

        $this->doUpgrade($flow, $account);
    }

    public function calcFirstPayment($request)
    {
        $this->load->model('account/invoice');

        $upgradeCost = $this->model_account_invoice->upgradeProduct(
            $flow->to['pid'],
            "stripe",
            $flow->to['plan'],
            $account['service_id'],
            true
        );

        $upgradeCost = substr(explode(' ', $upgradeCost->price)[0],1);

        return $request['amount'] - $upgradeCost;
    }

    public function calcNextDueDate()
    {
        return '2022-04-03';
    }

    public function detectFlow($request, $account)
    {
        return (new class ($request, $account, $this->flows) {
            private $flow;
            public $from = [];
            public $to = [];
            public $direction;
            private $valid = false;
            public function __construct($request, $account, $flows)
            {
                $this->from = [
                    'pid' => PRODUCTID,
                    'plan' => $account['plan_type'],
                    'init' => PRODUCTID . substr($account['plan_type'], 0, 1)
                ];
                $this->to = [
                    'pid' => $request['plan_id'],
                    'plan' => $request['plan_type'],
                    'init' => $request['plan_id'] . substr($request['plan_type'], 0, 1)
                ];
                $this->flow = vsprintf('%s-%s', [
                    $this->from['init'],
                    $this->to['init'],
                ]);

                if (isset($flows[$this->flow])) {
                    $this->direction = $flows[$this->flow];
                    $this->valid = true;
                }
            }

            public function isValid()
            {
                return $this->valid;
            }

            public function __toString()
            {
                return $this->flow;
            }

            public function detect()
            {
                return vsprintf('%sFrom%sTo%s', [
                    strtolower($this->direction),

                ]);
            }
        });
    }

    public function doUpgrade($flow, $account)
    {
        $firstPayment = $this->calcFirstPayment($request);

        $nextDueDate = $this->calcNextDueDate();

        $request = [
            'action' => 'UpdateClientProduct',
            'username' => $this->username,
            'password' => md5($this->password),
            'serviceid' => $account['service_id'],
            'pid' => $flow->to['pid'],
            'billingcycle' => $flow->to['plan'],
            'firstpaymentamount' => $firstPayment,
            // 'recurringamount' => '',
            'autorecalc' => true,
            'status' => 'Active',
            'nextduedate' => $nextDueDate,
            'responsetype' => 'json',
        ];

        $this->doRequest($request);

        return $firstPayment;
    }

    public function doRequest($request)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            throw new \Exception($cer);
            $response->error = $cer;
            return $response;
        }

        curl_close($ch);

        $response = json_decode($response);

        if ($response->result == 'success') {
            return $response;
        }

        return false;
    }
}
