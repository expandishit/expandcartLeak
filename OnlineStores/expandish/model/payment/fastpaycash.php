<?php

class ModelPaymentFastpayCash extends Model
{

    /**
     * The settings key string.
     *
     * @var string
     */
    protected $settingsKey = 'fastpaycash';


    /**
     * Get method data array to parse it in checkout page.
     *
     * @param string $address
     * @param int $total
     *
     * @return array
     */
    public function getMethod($address, $total)
    {
        $this->language->load_json('payment/fastpaycash');

        $settings = $this->getSettings();

        $status = false;
        if ($this->validateSettings($settings)) {
            $status = true;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'fastpaycash',
                'title' => $this->language->get('text_title'),
                'sort_order' => $settings['sort_order']
            );
        }

        return $method_data;
    }

    /**
     * Validate payment method settings to check if client id and secret key is set or not.
     *
     * @param array $settings
     *
     * @return bool
     */
    public function validateSettings($settings)
    {
        if (is_array($settings) === false) {
            return false;
        }

        if (!isset($settings['store_password']) || $settings['store_password'] == '') {
            return false;
        }

        if (!isset($settings['merchant_no']) || $settings['merchant_no'] == '') {
            return false;
        }

        if (
            !isset($this->session->data['order_id']) ||
            $this->session->data['order_id'] == ''
        ) {
            return false;
        }

        return true;
    }

    /**
     * Return payment settings group using the key string.
     *
     * @return array|bool
     */
    public function getSettings()
    {
        return $this->config->get($this->settingsKey);
    }

    /**
     * Validates the incoming request to make sure that all parameters are represented and valid.
     *
     * @param array $post
     *
     * @return bool
     */
    public function validateNotificationRequest($post)
    {

        if (!isset($post['transaction_id']) || $post['transaction_id'] == '') {
            return false;
        }

        if (!isset($post['order_id']) || $post['order_id'] == '') {
            return false;
        }

        if (!isset($post['bill_amount']) || $post['bill_amount'] == '') {
            return false;
        }

        if (!isset($post['customer_account_no']) || $post['customer_account_no'] == '') {
            return false;
        }

        if (!isset($post['status']) || $post['status'] == '') {
            return false;
        }

        if (!isset($post['received_at']) || $post['received_at'] == '') {
            return false;
        }

        return true;
    }

    /**
     * Validates the bill amount and check if it is equals to the already stored amount.
     *
     * @param float $billAmount
     * @param float $amount
     *
     * return bool
     */
    public function validateAmount($billAmount, $amount)
    {
        if ($billAmount == $amount) {
            return true;
        }

        return false;
    }
}
