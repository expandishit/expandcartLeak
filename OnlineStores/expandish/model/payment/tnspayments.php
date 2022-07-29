<?php

use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class ModelPaymentTNSPayments extends Model
{
    /**
     * The settings key string.
     *
     * @var string
     */
    protected $settingsGroup = 'tnspayments';


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
        $this->language->load_json('payment/tnspayments');

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
                'code' => $this->settingsGroup,
                'title' => $this->language->get('heading_title'),
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
        return $this->config->get($this->settingsGroup);
    }

    /**
     * Factory method to generate a new session id.
     *
     * @param array $settings
     * @return array|bool
     */
    public function generateSessionId($settings)
    {
        $client = new GuzzleClient([
            'base_uri' => 'https://secure.ap.tnspayments.com/api/nvp/version/61'
        ]);

        try {
            $operation = ($settings['environment']) ? 'AUTHORIZE' : 'PURCHASE' ;
            $billing_Address = ($settings['billing_Sddress']) ? 'MANDATORY' : 'HIDE' ;
            $order_summary = ($settings['order_summary']) ? 'SHOW' : 'HIDE' ;
            $result = $client->post('', [
                'form_params' => [
                    'apiOperation' => 'CREATE_CHECKOUT_SESSION',
                    'interaction.operation'=> $operation,
                    'apiPassword' => $settings['secret_hash'],
                    'interaction.returnUrl' => $this->url->link('payment/tnspayments/returnUrl'),
                    'apiUsername' => 'merchant.' . $settings['merchant_id'],
                    'merchant' => $settings['merchant_id'],
                    'order.id' => $settings['orderId'],
                    'order.amount' => $settings['total'],
                    'order.currency' => $settings['currency'],
                ]
            ]);

            parse_str($result->getBody(), $return);
        } catch(RequestException $e) {
            /*echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse()) . "\n";
            }*/
            exit;
        }

        return $return;
    }
}
