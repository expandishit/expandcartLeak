<?php

class ModelShippingDhlSettings extends Model
{
    /**
     * Shipping method settings key.
     *
     * @var string
     */
    protected $settingsGroup = 'dhl';

    /**
     * @var array
     */
    protected $errors = [];

    private static $settings = null;

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return void
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->insertUpdateSetting(
            'shipping', [$this->settingsGroup => $inputs]
        );
    }

    /**
     * Get payment settings.
     *
     * @return array|null
     */
    public function getSettings()
    {
        if (!self::$settings) {
            self::$settings = $this->config->get($this->settingsGroup);
        }

        return self::$settings;
    }

    /**
     * Validate form inputs.
     *
     * @param array $data
     *
     * return bool
     */
    public function validate($data)
    {
        if (isset($data['username']) == false || $data['username'] == '') {
            $this->errors[] = $this->language->get('username_error');
        }

        if (isset($data['password']) == false || $data['password'] == '') {
            $this->errors[] = $this->language->get('password_error');
        }

        if (isset($data['app_id']) == false || $data['app_id'] == '') {
            $this->errors[] = $this->language->get('app_id_error');
        }

        if (isset($data['app_token']) == false || $data['app_token'] == '') {
            $this->errors[] = $this->language->get('app_token_error');
        }

        if (isset($data['developer_id']) == false || $data['developer_id'] == '') {
            $this->errors[] = $this->language->get('developer_id_error');
        }

        if (isset($data['developer_password']) == false || $data['developer_password'] == '') {
            $this->errors[] = $this->language->get('developer_password_error');
        }

        if (isset($data['ekp_number']) == false || $data['ekp_number'] == '') {
            $this->errors[] = $this->language->get('ekp_number_error');
        }

        if (empty($this->errors)) {
            return true;
        }

        return false;
    }

    /**
     * Get all errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return array_merge([
            'warning' => $this->language->get('invalid_form_inputs')
        ], $this->errors);
    }

    /**
     * Get Sender Informations.
     *
     * @return array
     */
    public function getSenderData()
    {
        $ActiveLanguageCode = $this->config->get('config_language');
        $sender = [];

        $sender['name'] = $this->config->get('config_name')[$ActiveLanguageCode];
        $sender['address'] = $this->config->get('config_address')[$ActiveLanguageCode];
        $sender['street_number'] = '';
        $sender['postalcode'] = '';
        $sender['country_id'] = $this->config->get('config_country_id');
        $sender['email'] = (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));
        $sender['phone'] = $this->config->get('config_telephone');
        $sender['contact_person'] = '';

        return $sender;
    }

    /**
     * Prepare reciever information array.
     *
     * @param array $orderInfo
     *
     * @return array
     */
    public function getReceiverData($orderInfo)
    {
        $receiver = [];

        $receiver['name'] = implode(' ', [$orderInfo['firstname'], $orderInfo['lastname']]);
        $receiver['address'] = $orderInfo['address'];
        $receiver['street_number'] = '';
        $receiver['postalcode'] = $orderInfo['shipping_postcode'];
        $receiver['address'] = $orderInfo['shipping_address_1'];
        $receiver['country_id'] = $orderInfo['shipping_country_id'];
        $receiver['email'] = $orderInfo['email'];
        $receiver['phone'] = $orderInfo['telephone'];
        $receiver['contact_person'] = '';

        return $receiver;
    }

    /**
     * Get user credentials informations.
     *
     * @return array
     */
    public function getUserCredentials()
    {
        $settings = $this->getSettings();

        return [
            'username' => $settings['username'],
            'password' => $settings['password'],
            'ekp_number' => $settings['ekp_number'],
            'app_id' => $settings['app_id'],
            'app_token' => $settings['app_token'],
            'developer_id' => $settings['developer_id'],
            'developer_password' => $settings['developer_password'],
        ];
    }

    /**
     * Get dhl status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getSettings()['status'];
    }

    /**
     * Get environment.
     *
     * @return int
     */
    public function getEnvironment()
    {
        return $this->getSettings()['environment'];
    }

    /**
     * Insert new shipment.
     *
     * @param array $shipmentDetails
     *
     * @return void
     */
    public function newShipment($shipmentDetails)
    {
        $query = $fields = [];

        $query[] = 'INSERT INTO dhl_shipments SET';

        $fields[] = 'order_id="' . $shipmentDetails['order_id'] . '"';
        $fields[] = 'shipment_number="' . (int)$shipmentDetails['shipment_number'] . '"';
        $fields[] = 'label="' . urlencode($shipmentDetails['label']) . '"';
        $fields[] = 'shipment_status="0"';

        $query[] = implode(',', $fields);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Get shipment info by order id.
     *
     * @param int $orderId
     *
     * @return array|bool
     */
    public function checkShipmentByOrderId($orderId)
    {
        $query = [];

        $query[] = 'SELECT * FROM dhl_shipments';
        $query[] = 'WHERE order_id="' . (int) $orderId . '"';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }
}
