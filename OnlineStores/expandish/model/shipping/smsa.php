<?php

class ModelShippingSmsa extends Model
{
    private $settingTable = DB_PREFIX . 'setting';
    private $smsaTable = DB_PREFIX . 'smsa_orders';

    public $smsaResponse;

    public $refNo;

    public $errors = [];

    public $wsdl;

    private $passKey;

    private $awb;

    private $shipmentStatus = [
        'DATA RECEIVED' => 1,
        'DATA SHIPPED' => 3,
    ];

    public function getQuote($address)
    {
        $this->language->load_json('shipping/smsa');

        $queryString = [];
        $queryString[] = "SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone";
        $queryString[] = "WHERE geo_zone_id = '" . (int)$this->config->get('smsa_geo_zone_id') . "'";
        $queryString[] = "AND country_id = '" . (int)$address['country_id'] . "'";
        $queryString[] = "AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')";

        $query = $this->db->query(implode(' ', $queryString));

        $weight = $this->cart->getWeight();
        $status = false;

        if ($weight > 0) {
            $status = true;
        }

        if (!$this->config->get('smsa_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = [];

        if ($status) {

            $weightAfter5 = 0;

            if ($weight > 5) {
                $weightAfter5 = $weight - 5;
            }

            $cost = $this->config->get('smsa_first5') + ($weightAfter5 * $this->config->get('smsa_after5'));

            $quote_data['smsa'] = [
                'code'         => 'smsa.smsa',
                'title'        => $this->language->get('text_description'),
                'cost'         => $cost,
                'tax_class_id' => 0,
                'text'         => $this->currency->format($cost)
            ];

            $method_data = [
                'code'       => 'smsa',
                'title'      => $this->language->get('text_title'),
                'quote'      => $quote_data,
                'sort_order' => $this->config->get('smsa_sort_order'),
                'error'      => false
            ];
        }
        return $method_data;
    }

    public function checkIfSMSAIsInstalled()
    {
        $sql_str= "show tables like '".$this->smsaTable."'";
        $result = $this->db->query($sql_str);

        if($result->num_rows){
            return true;
        }
        return false;
    }
    public function setAWB($awb)
    {
        $this->awb = $awb;

        return $this;
    }

    public function setPassKey($passKey)
    {
        $this->passKey = $passKey;

        return $this;
    }
    
    public function getPassKey()
    {
        return $this->passKey;
    }

    public function getAWB()
    {
        return $this->awb;
    }
    
    public function getWsdl()
    {
        return $this->wsdl;
    }

    public function getPDF()
    {
        $arguments = [
            'awbNo' => $this->getAWB(),
            'passkey' => $this->getPassKey()
        ];

        $client = new Client($this->getWsdl());

        $this->smsaResponse = $client->setFunction('getPDF')
            ->setArguments($arguments)
            ->call();

        return $this->smsaResponse->getPDFResult;
    }

    public function getSettings()
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM `' . $this->settingTable . '`';
        $queryString[] = 'WHERE `group`="smsa"';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return array_column($data->rows, 'value', 'key');
        }

        return false;
    }

    public function getShipmentInfo($orderId)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM `' . $this->smsaTable . '`';
        $queryString[] = 'WHERE order_id=' . $orderId;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }
}
