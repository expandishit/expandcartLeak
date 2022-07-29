<?php

class ControllerShippingSalasa extends Controller
{

    /**
     * This function is called by Salasa once the order is shipped
     * each store has a unique "key"
     * The function adds a new record in order history and change order status to shipped (shipped status_id = 3)
     * and it adds a comment containing all shipping info
     *
     */
    public function updateTrackingNumber()
    {
        $this->initializer([
            'shipping/salasa'
        ]);

        $settings = $this->salasa->getSettings();

        if (empty($this->request->get['key']) || $this->request->get['key'] != $settings['account_key']) {
            die('Not Authorized');
        }

        $order_id = $this->request->get['order_id'];
        $tracking_number = $this->request->get['tracking_number'];
        $carrier = $this->request->get['carrier'];
        $tracking_link = urldecode($this->request->get['tracking_link']);
        $result = $this->db->query("UPDATE `" . DB_PREFIX . "order` SET `tracking` = '{$tracking_number}' WHERE `order_id`= {$order_id}");

        if ($result) {
            $this->load->model('checkout/order');
            $history_comment = " شركة الشحن  " . $carrier . "  ،رقم التتبع  " . $tracking_number . "  ،رابط التتبع  " . $tracking_link . " . ";
            $this->model_checkout_order->addOrderHistory($order_id, 3, $history_comment);
            return "1";
        } else {
            return "0";
        }
    }

    /**
     * This function is called by Salasa once the order is delivered to customer
     * each store has a unique "key"
     * The function adds a new record in order history and change order status to Complete (Complete status_id = 5)
     */
    public function updateCompleted()
    {
        if (empty($this->request->get['key']) || $this->request->get['key'] != $settings['account_key']) {
            die('Not Authorized');
        }

        $order_id = $this->request->get['order_id'];
        $this->load->model('checkout/order');
        $this->model_checkout_order->addOrderHistory($order_id, 5, '');

        return "1";

    }

    /**
     * This function sends the order to OMS
     * each store has a unique "AccountID" and "key"
     * If Product has options , Main product SKU# should be empty
     * Each SKU should have one option type
     */
    private function send_order_to_salasa($order_id, $settings, $is_cod = false)
    {
        $order_info = $this->model_checkout_order->getOrder($order_id);
        $this->load->model('account/order');
        $order_products = $this->model_account_order->getOrderProducts($order_id);
        //$products = array();
        $total_no_shipment = 0;
        $total_weight = 0;
        //$this->load->model('catalog/product');
        foreach ($order_products as $product) {
            // $product_info = $this->model_catalog_product->getProduct($product['product_id']);
            // if ($product_info['sku'] != '') {
            //     $products[] = array(
            //         'Sku' => $product_info['sku'],
            //         'Qty' => $product['quantity'],
            //         'SoldPrice' => $product['price'],
            //         'Description' => $product['name'],
            //     );
            // } else {
            //     $query = $this->db->query("SELECT o.product_option_value_id FROM `" . DB_PREFIX . "order_option` o WHERE o.order_id =" . $order_id . " AND o.order_product_id = '" . $product['order_product_id'] . "' ORDER BY o.order_id DESC");
            //     $option_id = $query->row['product_option_value_id'];
            //     $option_query = $this->db->query("SELECT o.option_sku FROM `" . DB_PREFIX . "product_option_value` o WHERE o.product_option_value_id =" . $option_id);

            //     if ($option_query) {
            //         $option_sku = $option_query->row['option_sku'];
            //         $products[] = array(
            //             'Sku' => $option_sku,
            //             'Qty' => $product['quantity'],
            //             'SoldPrice' => $product['price'],
            //             'Description' => $product['name'],
            //         );
            //     }
            // }
            $total_no_shipment += $product['total'];
            $total_weight += $product['weight'];
        }
        $total_weight = $total_weight>0 ? $total_weight : 1;
        //$checkout_date = explode(' ', $order_info['date_added']);

        $data = array(
            'api_token' => $settings['api_key'],
            'orders' => [
                [
                    'merchant_id' => $settings['account_id'],
                    'merchant_key' => $settings['account_key'],
                    'customer_name' => $order_info['firstname'].' '.$this->orderInfo['lastname'],
                    'customer_telephone' =>  $order_info['telephone'],
                    'customer_email' => $order_info['email'],
                    'pickup_location_alias' =>  $settings['pickup_location_alias'],
                    'pickup_city' => $order_info['payment_city'],
                    'pickup_country' => $order_info['payment_country']['locale_name'], 
                    'pickup_address_1' =>$order_info['payment_address_1'], 
                    'pickup_address_2' => $order_info['payment_address_2'], 
                    'shipping_country' => $order_info['shipping_country'],
                    'shipping_city' => $order_info['shipping_city'],
                    'shipping_address_1' => $order_info['shipping_address_1'],
                    'shipping_address_2' => $order_info['shipping_address_2'],
                    'Shipping_address_3' => null,
                    'payment_type' => ($is_cod) ? 'cod' : 'paid',
                    'cod_amount' =>  $order_info['total'],
                    'declared_value' => 111,
                    'currency' => $order_info['currency_code'],
                    'weight' => $total_weight,
                    'dimension' => 10,
                    'preferred_courier' => null,
                    'service_type' => 'SDD',
                    'reference_number' => $settings['name'],
                    'reference_number_2' => null,
                    'reference_number_3' => null,
                    'shipping_zip' =>$this->orderInfo['shipping_postcode'],
                    'description' => 'new order',
                    'numberOfBoxes' => 1
                ]
            ]
        );
        // $data = array(
        //     'AccountID' => $settings['account_id'],
        //     'Key' => $settings['api_key'],
        //     'Warehouse' => 'sal1',
        //     'orders' => array(
        //         array(
        //             'orderNum' => '117-' . $order_info['order_id'],
        //             'orderShipMethod' => 'DLV',
        //             'CustComments' => $order_info['comment'],
        //             'custEmail' => $order_info['email'],
        //             'custPhone' => $order_info['telephone'],
        //             'custFName' => $order_info['firstname'],
        //             'custLName' => $order_info['lastname'],
        //             'custAddress1' => $order_info['shipping_address_1'],
        //             'custAddress2' => $order_info['shipping_address_2'],
        //             'custCity' => $order_info['shipping_city'],
        //             'custState' => $order_info['shipping_zone'],
        //             'custZip' => $order_info['shipping_postcode'],
        //             'custCountry' => $order_info['shipping_country'],
        //             'custBillFName' => $order_info['payment_firstname'],
        //             'custBillLName' => $order_info['payment_lastname'],
        //             'custBillCompany' => $order_info['payment_company'],
        //             'custBillAddress1' => $order_info['payment_address_1'],
        //             'custBillAddress2' => $order_info['payment_address_2'],
        //             'custBillCity' => $order_info['payment_city'],
        //             'custBillState' => $order_info['payment_zone'],
        //             'custBillZip' => $order_info['payment_postcode'],
        //             'custBillCountry' => $order_info['payment_country'],
        //             'orderCOD' => ($is_cod) ? $order_info['total'] : '', // check if COD
        //             'CheckoutDate' => (count($checkout_date)) ? $checkout_date[0] : date('Y-m-d'),
        //             'CartID' => $order_info['order_id'],
        //             'ShipmentSubtotal' => $total_no_shipment,
        //             'ShippingCharge' => $order_info['total'] - $total_no_shipment,
        //             'TotalCharge' => $order_info['total'],
        //             'items' => count($order_products),//
        //             'Detaill' => $products
        //         )

        //     )
        // );
        $curl = curl_init('http://delivery.salasa.co/api/createShipment');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
          'Content-Type: application/json',
        ]);

        // Execute cURL request with all previous settings
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response = json_decode($response);
        // Close cURL session
        curl_close($curl);

        // $postdata = http_build_query(array(
        //   //  'api_token' => $settings['token'],
        //   //'data' => $data
        //   'json' => $data
        // ));

        // $opts = array('http' =>
        //     array(
        //         'method' => 'POST',
        //         'header' => 'Content-type: application/x-www-form-urlencoded',
        //         'content' => $postdata
        //     )
        // );

        // $context = stream_context_create($opts);
        // $result = file_get_contents('http://delivery.salasa.co/api/createShipment', false, $context);

    }

    /**
     * This function is called by the Store once the order status changes to processing (processing status_id = 17)
     */
    public function on_order_added($route, $data, $return)
    {
        $this->initializer([
            'shipping/salasa'
        ]);

        $settings = $this->salasa->getSettings();

        $order_id = $data[0];
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);
        $is_cod = (stripos($order_info['payment_code'], 'cod') !== false) ? true : false;

        if ($order_info['order_status_id'] == $settings['status_code']) {
            self::send_order_to_salasa($order_id, $settings, $is_cod);

        }

    }

    private function call_api($url, $data)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HEADER, false);

        curl_setopt($curl, CURLOPT_POST, 1);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Length: " . strlen($data)));

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $x = curl_exec($curl);
        $x = json_decode($x);
        return $x;
    }

    /**
     * This function is called by Salasa once order is shipped
     * it updates products qty's and products options qty
     * if you want to stop updating inventory , please comment the function
     */
    public function updateQty()
    {

        $this->initializer([
            'shipping/salasa'
        ]);

        $settings = $this->salasa->getSettings();

        $page = 1;
        do {
            $data = array(
                'AccountID' => $settings['account_id'],
                'Key' => $settings['api_key'],
                'Warehouse' => 'sal1',
                'page' => $page);
            $data = json_encode($data);
            $url = 'http://integration.shipedge.com/API/Rest/Inventory/getProducts';
            $result = $this->call_api($url, $data);
            foreach ($result->Result as $product) {
                $query_result = $this->db->query("UPDATE `" . DB_PREFIX . "product` SET quantity = '{$product->quantity}' WHERE sku= '{$product->sku}'");
                $query = $this->db->query("UPDATE`" . DB_PREFIX . "product_option_value` SET quantity = '{$product->quantity}' WHERE option_sku = '{$product->sku}'");
            }
            $page++;
        } while (count($result->Result) >= 1);
    }
}
