<?php
class ModelShippingDHLExpressSetting extends Model {

    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hittech_dhl_details_new` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` text NOT NULL,
            `tracking_num` text NOT NULL,
            `shipping_label` text COLLATE utf8_bin NOT NULL,
            `invoice` text COLLATE utf8_bin NOT NULL,
            `return_label` text COLLATE utf8_bin  NULL,
            `return_invoice` text COLLATE utf8_bin  NULL,
            `one` text COLLATE utf8_bin  NULL,
            `two` text COLLATE utf8_bin  NULL,
            `three` text COLLATE utf8_bin  NULL,
            PRIMARY KEY (`id`)
        )");
    }


    /**
     * Get Sender Informations.
     *
     * @return array
     */
    public function getSenderData()
    {
        $sender = [];

		$sender['name'] = $this->config->get('dhl_express_shipper_name');
		$sender['company'] = $this->config->get('dhl_express_company_name');
        $sender['address1'] = $this->config->get('dhl_express_address1');
		$sender['city'] = $this->config->get('dhl_express_city');
		$sender['state'] = $this->config->get('dhl_express_state');
        $sender['postalcode'] =  $this->config->get('dhl_express_postcode');
        $sender['country_id'] = $this->config->get('dhl_express_country_code');
        $sender['email'] = $this->config->get('dhl_express_email_addr');
        $sender['phone'] = $this->config->get('dhl_express_phone_num');
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
        $receiver['address1'] = $orderInfo['address'];
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
     * Get shipment info by order id.
     *
     * @param int $orderId
     *
     * @return array|bool
     */
    public function checkShipmentByOrderId($orderId)
    {
        $query = [];

        $query[] = 'SELECT * FROM hittech_dhl_details_new';
        $query[] = 'WHERE order_id="' . (int) $orderId . '"';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }
}