<?php

use ExpandCart\Foundation\WebServices\Soap\Client;

class ModelShippingSmsa extends Model
{

    private $orderTable = DB_PREFIX . 'order';
    private $orderStatusTable = DB_PREFIX . 'order_status';
    private $languageTable = DB_PREFIX . 'language';
    private $settingTable = DB_PREFIX . 'setting';
    private $smsaTable = DB_PREFIX . 'smsa_orders';
    private $customerTable = DB_PREFIX . 'customer';

    public $smsaResponse;

    public $refNo;

    public $errors = [];

    public $wsdl;

    public $customs;

    private $passKey;

    private $awb;

    private $shipmentStatus = [
        'DATA RECEIVED' => 1,
        'DATA SHIPPED' => 3,
    ];

    public function setConfig($index, &$data)
    {
        if (isset($this->request->post[$index])) {
            $data[$index] = $this->request->post[$index];
        } else {
            $data[$index] = $this->config->get($index);
        }
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

    public function getBreadcrumbs($crumbs)
    {
        foreach ($crumbs as $crumb) {
            $data[] = [
                'text' => $this->language->get($crumb['text']),
                'href' => $crumb['href'],
                'separator' => $crumb['separator'],
            ];
        }

        return $data;
    }

    /**
     *
     *
     * TODO add a validation library to be used cross modules instead of creating a function within each class.
     * TODO complete the concrete functionality of this method.
     */
    public function validate($inputs, $rules)
    {
        return true;
    }

    public function getOrders()
    {
        $queryString = [];

        $queryString[] = 'SELECT *,o.order_id as orderId FROM `' . $this->orderTable . '` as o';
        $queryString[] = 'LEFT JOIN `' . $this->customerTable . '` as c';
        $queryString[] = 'ON o.customer_id=c.customer_id';
        $queryString[] = 'LEFT JOIN `' . $this->orderStatusTable . '` as os';
        $queryString[] = 'ON os.order_status_id=o.order_status_id';
        $queryString[] = 'LEFT JOIN `' . $this->smsaTable . '` as so';
        $queryString[] = 'ON so.order_id=o.order_id';
        $queryString[] = 'WHERE o.shipping_code="smsa.smsa" AND o.order_status_id > 0';
        $queryString[] = 'AND os.language_id=(%s)';

        $language = $this->config->get('config_admin_language');

        $subQuery = 'SELECT language_id from `' . $this->languageTable .'` where code="' . $language . '"';

        $data = $this->db->query(sprintf(implode(' ', $queryString), $subQuery));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    public function getOrderInfo($orderId)
    {
        $this->load->model('sale/order');
        $this->load->model('catalog/product');
        $orderInfo = $this->model_sale_order->getOrder($orderId);
        $orderProducts = $this->model_sale_order->getOrderProducts($orderId);

        foreach ($orderProducts as $key => $orderProduct) {
            $product = $this->model_catalog_product->getProduct($orderProduct['product_id']);
            $product['quantity'] = $orderProduct['quantity'];
            $orderInfo['products'][$key] = array_merge(
                $orderProduct,
                $product
            );
        }

        return $orderInfo;
    }

    public function setWsdl($wsdl)
    {
        if (!$wsdl) {
            $this->wsdl = null;
        }

        if (substr($wsdl, -5) != '?wsdl' && substr($wsdl, -4) == 'asmx') {
            $wsdl = $wsdl . '?wsdl';

            $this->wsdl = $wsdl;
        } else {
            $this->wsdl = null;
        }
    }

    public function getWsdl()
    {
        return $this->wsdl;
    }

    public function setRefNo($refNo)
    {
        $this->refNo = $refNo;

        return $this;
    }

    public function getRefNo()
    {
        return $this->refNo;
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

    public function setAWB($awb)
    {
        $this->awb = $awb;

        return $this;
    }

    public function getAWB()
    {
        return $this->awb;
    }

    public function createShipment($orderInfo, $wsdl, $passKey, $customs = 0)
    {
        $client = new Client($wsdl);

        $mobilePhone = null;

        if (isset($orderInfo['shipment_phone'])) {
            $mobilePhone = $orderInfo['shipment_phone'];
        } else if (isset($orderInfo['telephone'])) {
            $mobilePhone = $orderInfo['telephone'];
        }

        if (!$mobilePhone || $this->validateMobile($mobilePhone) === false) {
            return false;
        }

        $productsStats = $this->getOrderProductsStats($orderInfo['products']);

        // TODO check the smsa supported cities
        // TODO shipment type
        // TODO check payment amount options

        // Get State/Zone name in English 
		$this->load->model('localisation/zone');
		$zone_name = $this->model_localisation_zone->getZoneInLanguage($orderInfo['shipping_zone_id'],1);
        
        /** Set codAmt to 0 if the payment method is NOT COD ( cash on delivery) */
        $smsaCodAmt = strtolower( $orderInfo['payment_code'] ) != 'cod' ? 0 : $orderInfo['total'];

        $shipping_address = ! empty($orderInfo['shipping_address_format']) ? $orderInfo['shipping_address_format'] : $orderInfo['shipping_address_1'];
        $cName= $orderInfo['shipping_firstname'] . ' ' . $orderInfo['shipping_lastname'];
        if (empty(trim($cName)))
            $cName= $orderInfo['firstname'] . ' ' . $orderInfo['lastname'];
        $shipmentArguments = [
            'passKey' => $passKey,
            'refNo' => $this->refNo,
            'idNo' => $orderInfo['order_id'],
            'cName' => $cName,
            'cntry' => $orderInfo['shipping_iso_code_2'],
            'cCity' => ($zone_name['name'])?$zone_name['name']:$orderInfo['shipping_city'],
            'cMobile' => $mobilePhone,
            'cAddr1' => $shipping_address,
            'cAddr2' => $shipping_address,
            'shipType' => 'DLV',
            'PCs' => $productsStats['quantity'],
            'cEmail' => $orderInfo['email'],
            'codAmt' => $smsaCodAmt,
            'weight' => $productsStats['weight'],
            'itemDesc' => $productsStats['itemDesc'],
            'sentDate' => $orderInfo['date_modified'],
            'cZip' => '',
            'cPOBox' => '',
            'cTel1' => '',
            'cTel2' => '',
            'carrValue' => '',
            'carrCurr' => '',
            'custVal' => $customs,
            'custCurr' => '',
            'insrAmt' => '',
            'insrCurr' => '',
        ];

        $this->smsaResponse = $client->setFunction('addShipment')
            ->setArguments($shipmentArguments)
            ->call();

        return $this;
    }

    public function getStatus()
    {
        $arguments = [
            'awbNo' => $this->getAWB(),
            'passkey' => $this->getPassKey()
        ];

        $client = new Client($this->getWsdl());

        $this->smsaResponse = $client->setFunction('getStatus')
            ->setArguments($arguments)
            ->call();

        return $this->shipmentStatus[$this->smsaResponse->getStatusResult];
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
	$temp_filename = TEMP_DIR_PATH . 'output.pdf';
        header("Content-type:application/pdf");    
        file_put_contents($temp_filename , $this->smsaResponse->getPDFResult);
        header("Content-Disposition:attachment;filename='downloaded.pdf'");
        readfile($temp_filename);
        unlink($temp_filename);
        
        //return $this->shipmentStatus[$this->smsaResponse->getStatusResult];
    }

    public function generateRefNo($orderId)
    {
        return STORECODE . '_' . $orderId;
    }

    private function getOrderProductsStats($orderProducts){
        $names = array_column($orderProducts, 'name');
        //Check if item description is bigger than 250 chars
        if(strlen(json_encode($names,  JSON_UNESCAPED_UNICODE)) >= 250){
            //check each item if exceeds 15 chars then truncate
            foreach($names as $key=>$item){
                if(strlen($item) >= 15){
                    $names[$key] = substr($item,0,15);
                }
            }
        }
        return [
            'quantity' => array_sum(array_column($orderProducts, 'quantity')),
            'weight' => array_sum(array_column($orderProducts, 'weight')),
            'itemDesc' => json_encode($names,  JSON_UNESCAPED_UNICODE),
        ];
    }

    private function validateMobile($mobile)
    {
        if (strlen($mobile) >= 9) {
            return true;
        }

        return false;
    }

    public function getResponse()
    {
        if (!$this->smsaResponse) {

            $this->errors[] = $this->language->get('no_request');

            return false;
        }

        if (preg_match('#failed\s*\:\:\s*(.*?)#Ui', $this->smsaResponse->addShipmentResult, $error)) {

            $this->errors[] = $this->language->get('failed_request') . ' ' . $error[1];

            return false;
        }

        if (preg_match('#^(\d+)$#', $this->smsaResponse->addShipmentResult, $shipmentCode)) {
            if (isset($shipmentCode)) {
                $this->shipmentCode = $shipmentCode[1];

                return $this->shipmentCode;
            } else {
                $this->errors[] = $this->language->get('invalid_shippment_code');

                return false;
            }
        }

        $this->errors[] = $this->language->get('unknown_error');

        return false;
    }

    public function addShipmentInfo($order, $shipment, $customs = 0)
    {
        $queryString = $fields = [];

        $queryString[] = 'INSERT INTO ' . $this->smsaTable . ' SET';
        $fields[] = 'order_id=' . $order['order_id'];
        $fields[] = 'shipment_code="' . $this->db->escape($shipment) . '"';
        $fields[] = 'customs="' . $this->db->escape($customs) . '"';
        $fields[] = 'ref_no="' . $this->refNo . '"';
        $queryString[] = implode(', ', $fields);

        $this->db->query(implode(' ', $queryString));
    }

    public function getShipmentInfo($orederId)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM `' . $this->smsaTable . '`';
        $queryString[] = 'WHERE order_id=' . $orederId;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    public function install()
    {
        $installQueries = $columns = [];

        $installQueries[] = 'CREATE TABLE IF NOT EXISTS `' . $this->smsaTable . '` (';
        $columns[] = '`shipment_id` INT(11) NOT NULL AUTO_INCREMENT';
        $columns[] = '`order_id` INT(11) NOT NULL';
        $columns[] = '`shipment_code` VARCHAR(200) NOT NULL';
        $columns[] = '`customs` decimal(4,3) NOT NULL DEFAULT "0"';
        $columns[] = '`shipment_status` INT(2) NOT NULL DEFAULT "0"';
        $columns[] = '`ref_no` VARCHAR(200) NOT NULL';
        $columns[] = '`create_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP';
        $columns[] = 'PRIMARY KEY (`shipment_id`,`order_id`)';
        $installQueries[] = implode(', ', $columns);
        $installQueries[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        $this->db->query(implode(' ', $installQueries));
    }
}
