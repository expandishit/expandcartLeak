<?php

class ModelPaymentMoyasar extends Model
{
    /**
     * The settings key string.
     *
     * @var string
     */
    protected $settingsKey = 'moyasar';

    /**
     * @var string
     */
    protected $callbackUrl;

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
        $this->language->load_json('payment/moyasar');

        $settings = $this->getSettings();

        $status = true;

        if (!isset($settings['status']) || $settings['status'] != 1) {
            $status = false;
        }

        if ($settings['geo_zone_id'] != 0 && $this->getGeoZone($settings['geo_zone_id'], $address) == false) {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => $this->settingsKey,
                'title' => $this->language->get('moyasar_heading_title'),
                'sort_order' => $settings['sort_order']
            );
        }

        return $method_data;
    }

    /**
     * Get all geo zones by array of zones ids.
     *
     * @param array $zones
     * @param array $address
     *
     * return array|bool
     */
    public function getGeoZone($zones, $address)
    {
        if (is_array($zones) == false) {
            $zones = [$zones];
        }

        $query = [];
        $query[] = 'SELECT * FROM zone_to_geo_zone';
        $query[] = 'WHERE geo_zone_id IN (' . implode(',', $zones) . ')';
        $query[] = 'AND country_id = "' . (int)$address['country_id'] . '"';
        $query[] = 'AND (zone_id = "' . (int)$address['zone_id'] . '" OR zone_id = "0")';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
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
     * Set api key.
     *
     * @param string $secretKey
     *
     * @return void
     */
    public function setApiKey($secretKey)
    {
        Moyasar\Client::setApiKey($secretKey);
    }

    /**
     * Set callback uri.
     *
     * @param string $callbackUrl
     *
     * @return void
     */
    public function setCallbackUrl($callbackUrl)
    {
        $this->callbackUrl = $callbackUrl;
    }

    /**
     * Factory method to create the payment.
     *
     * @param array $data
     *
     * @return array
     */
    public function createPayment($data)
    {
        $card = [
            "type" => "creditcard",
            "name" => $data['name'],
            "number" => $data['number'],
            "cvc" => $data['cvc'],
            "month" => $data['month'],
            "year" => $data['year']
        ];
        
        $payment = Moyasar\Payment::create(
            $data['amount'],
            $card,
            $data['order_name'],
            $data['currency'],
            $this->callbackUrl
        );

        return $payment;
    }

    public function fetch($id)
    {
        return Moyasar\Payment::fetch($id);
    }
}
