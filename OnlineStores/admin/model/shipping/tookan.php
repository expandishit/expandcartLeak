<?php

class ModelShippingTookan extends Model
{
    /**
     * Shipping method settings key.
     *
     * @var string
     */
    protected $settingsGroup = 'tookan';

    /**
     * @var array
     */
    protected $errors = [];

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
        return $this->config->get($this->settingsGroup);
    }


    public function getErrors()
    {
        return [1, 2];
    }

    public function addShipmentDetails($orderId, $details, $status)
    {
        $query = $fields = [];

        $query[] = 'INSERT INTO  ' . DB_PREFIX . 'shipments_details SET';
        $fields[] = 'order_id="' . $orderId . '"';
        $fields[] = 'details=\'' . json_encode($details,JSON_UNESCAPED_UNICODE) . '\'';
        $fields[] = 'shipment_status="' . $status . '"';
        $fields[] = 'shipment_operator="tookan"';
        $query[] = implode(', ', $fields);
        $this->db->query(implode(' ', $query));
    }

    public function getShipmentDetails($orderId)
    {
       $result = $this->db->query("SELECT * FROM  " . DB_PREFIX . "shipments_details where `order_id` = $orderId AND `shipment_operator` = 'tookan' ");

       return $result->row;
      
    }

    public function deleteShipment($orderId)
    {
        $result = $this->db->query("DELETE FROM  " . DB_PREFIX . "shipments_details where `order_id` = $orderId AND `shipment_operator` = 'tookan' ");

        return $result->row;

    }
}
