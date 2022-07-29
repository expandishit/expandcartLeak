<?php

class ModelMultisellerSubscriptions extends Model
{
    private $plan_table = DB_PREFIX . 'ms_subscriptions';
    private $description_table = DB_PREFIX . 'ms_subscriptions_description';
    private $seller_payment_table = DB_PREFIX . 'ms_seller_payments';
    private $seller_table = DB_PREFIX . 'ms_seller';

    const ENCRYPTION_KEY = 'E1X2P3A4N5D6C';

    public function getPlans()
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->plan_table . ' as p';
        $queryString[] = 'LEFT JOIN ' . $this->description_table . ' as d';
        $queryString[] = 'ON p.plan_id=d.plan_id';
        $queryString[] = 'WHERE d.language_id=' . $this->config->get('config_language_id');
        $queryString[] = 'AND p.plan_status=1';

        $data = $this->db->query(implode(' ', $queryString));

        array_walk($data->rows, function (&$value, $key) {
            $value['formated_price'] = $this->currency->format(
                number_format($value['price'], 2, '.', '')
            );
            $value['usd_price'] = $this->currency->convert(
                number_format($value['price'], 2, '.', ''), $this->config->get('config_currency'), 'USD'
            );
            $value['formatted_payment'] = $value['period'] . ' ' .
                $this->language->get('payment_type_' . $value['format']);
        });

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return null;
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

    public function selectPaymentBy($columns)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->seller_payment_table;
        if (isset($columns) and count($columns) > 0) {
            $where = [];
            foreach ($columns as $column => $value) {
                $where[] = $column . '=' . $value;
            }

            $queryString[] = 'WHERE ' . implode(' AND ', $where);
        }

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->rows;
        } else {
            return false;
        }
    }

    public function newSellerPayment($sellerId, $data)
    {
        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO ' . $this->seller_payment_table . ' SET';
        $fields[] = 'plan_id=' . $data['subscription_plan'];
        $fields[] = 'seller_id="' . $sellerId . '"';
        $fields[] = 'amount="' . $data['price'] . '"';
        $fields[] = 'payment_method="' . $data['payment_method'] . '"';

        $queryString[] = implode(',', $fields);

        $this->db->query(implode(' ', $queryString));
    }

    /**
     * Encrypt a string.
     *
     * @param string $sellerData
     *
     * @return string
     */
    public function encrypt($sellerData)
    {
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($sellerData, $cipher, self::ENCRYPTION_KEY, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, self::ENCRYPTION_KEY, $as_binary = true);
        $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);
        return $ciphertext;
    }

    /**
     * Decrypt a string.
     *
     * @param string $ciphertext
     *
     * @return string|bool
     */
    public static function decrypt($ciphertext)
    {
        $c = base64_decode($ciphertext);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, self::ENCRYPTION_KEY, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, self::ENCRYPTION_KEY, $as_binary = true);
        if (hash_equals($hmac, $calcmac)) {
            return $original_plaintext;
        }

        return false;
    }

    /**
     * Get all seller payments.
     *
     * @param int $sellerId
     *
     * @return string|bool
     */
    public function getSellerPayments($sellerId)
    {
        $query = [];

        $query[] = 'SELECT * FROM ' . $this->seller_payment_table . ' as payments';
        $query[] = 'INNER JOIN ' . $this->plan_table . ' plans';
        $query[] = 'ON payments.plan_id=plans.id';
        $query[] = 'WHERE seller_id=' . $sellerId;

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Get latest seller payment.
     *
     * @param int $sellerId
     *
     * @return string|bool
     */
    public function getLatestPayment($sellerId)
    {
        $query = $columns = [];

        $columns[] = 'payment_id';
        $columns[] = 'plan_id';
        $columns[] = 'seller_id';
        $columns[] = 'amount';
        $columns[] = 'payment_method';
        $columns[] = 'created_at as lastPaymentDate';

        $query[] = 'SELECT ' . implode(',', $columns) . ' FROM ' . $this->seller_payment_table;
        $query[] = ' WHERE payment_id=(select max(payment_id) from '. $this->seller_payment_table.' where seller_id="'.$sellerId.'")';

        $data = $this->db->query(implode(' ', $query));
        if ($data->num_rows) {
            return $data->row;
        }
        return false;
    }

    /**
     * Get plan by it's id.
     *
     * @param int $planId
     *
     * @return string|bool
     */
    public function getPlanById($planId)
    {
        $query = [];
        $query[] = 'SELECT * FROM ' . $this->plan_table . '';
        $query[] = 'WHERE plan_id="' . $planId . '"';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }
}
