<?php
class ModelPaymentTabby extends Model {
    
    public function getPaymentObject(&$error, $payment_type){
        //1- Get order info
        $order_id = $this->session->data['order_id'];
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);
        $orderProducts = $this->model_checkout_order->getOrderProducts($order_id);
        $this->load->model('module/quickcheckout_fields');
        $custom_fields = $this->model_module_quickcheckout_fields->getOrderCustomFields($this->session->data['order_id'], true);
        
        if(!$this->customer->getId())
            $date_of_birth = $custom_fields['payment_address'][$this->config->get('tabby_dob_field_id')]['value'];
        else
            $date_of_birth = $this->customer->getDOB();

        //Tabby Settings..
        $settings = $this->config->get($payment_type);


        //Get Customer info
        $wishlist_count   = count($this->customer->getWishList());
        $registered_since = (new DateTime($this->customer->getDateAdded() ?: 'NOW' ))->format(DateTime::ATOM);
        $loyalty_level    = 10;

        //Get shipping Cost...
        $total = 0;
        $total_data = [];
        $taxes =  $this->cart->getTaxes();
        $this->load->model('total/shipping');
        $this->model_total_shipping->getTotal($total_data, $total, $taxes);
        $shipping_cost = $total_data[0]['value'];

        //Validate required data
        //validate Date Of Birth
        if( !empty($date_of_birth) && !$this->validateDate($date_of_birth) ){
            $this->session->data['error_tabby'] = $error['dob'] = 'Date Of Birth is in invalid format should be YYYY-MM-DD in hijri (ex: 1405-02-05)';
            return -1;
        }

        //2- BUILD A PAYMENT OBJECT
        $phone = $order_info['payment_telephone'] ?: $order_info['telephone'];
        $phoneCode = '+' . ($order_info['payment_phonecode'] ?: $order_info['shipping_phonecode']);
        $phone = str_replace($phoneCode, '', $phone);
        $phone = $phoneCode . $phone;
        
        // $phone = '+' . ($order_info['payment_phonecode'] ?: $order_info['shipping_phonecode']) . ($order_info['payment_telephone'] ?: $order_info['telephone']);

        $tabby_order_country_code = $this->_getOrderPaymentCountryCode($order_info['payment_country_id']);
        $tabby_order_currency_code = $this->_getOrderPaymentCurrency($tabby_order_country_code, $settings['account_currency']);
        
        $this->session->data['tabby_order_country_code'] = $tabby_order_country_code;
        
        $total = $this->currency->convertUsingRatesAPI($this->currency->currentValue($order_info['total']), $order_info['currency_code'], $tabby_order_currency_code);

        $shipping_cost = $this->currency->convertUsingRatesAPI($this->currency->currentValue($shipping_cost), $order_info['currency_code'], $tabby_order_currency_code);
        
        $tax_amount = (array_values($taxes)[0] * $order_info['total'] / 100);
        
        $tax_amount = $this->currency->convertUsingRatesAPI($this->currency->currentValue($tax_amount), $order_info['currency_code'], $tabby_order_currency_code);
        
        $buyer = [           
            'email' => $order_info['email'],
            'name'  => $order_info['firstname'] . ' ' . $order_info['lastname'],
            'phone' => $phone
        ];
        
        if(!empty($date_of_birth)){
            $buyer['dob'] = $date_of_birth;
        }
        
        $items = array_reduce($orderProducts, function($items, $item) use($order_info, $tabby_order_currency_code) {
            $image = \Filesystem::isExists('image/' . $item['image']) 
                ? \Filesystem::getUrl('image/' . $item['image'])
                : \Filesystem::getUrl('image/no_image.jpg?t=');
                
            $price = $this->currency->convertUsingRatesAPI($this->currency->currentValue($item['price']), $order_info['currency_code'], $tabby_order_currency_code);
            
            $items[] = [
                'title' => $item['name'], 
                'quantity' => (int)$item['quantity'], 
                'unit_price' => number_format((float)$price, 2, '.', ''),
                'reference_id' => (string)$item['product_id'],
                'image_url' => $image
            ];
            
            return $items;
        }, []);
                
        $payment = [
          'amount' => number_format((float)$total, 2, '.', ''),
          'buyer'  => $buyer,
          'buyer_history' => [
            'loyalty_level'    =>  $loyalty_level,
            'registered_since' =>  $registered_since,
            'wishlist_count'   =>  $wishlist_count,
          ],
          'currency'     => $tabby_order_currency_code,
          'description'  => 'order #' . $order_id,
          'order'        => [
            'items' => $items,
            'reference_id'    => (string)$order_id,
            'shipping_amount' => number_format((float)$shipping_cost, 2, '.', ''),
            'tax_amount'      => number_format((float)$tax_amount, 2, '.', ''),
          ],
          'order_history' => [
              [
                'amount' => number_format((float)$total, 2, '.', '') /*number_format($this->currency->currentValue($order_info['total']), 2, '.', '')*/,
                'currency' => $order_info['currency_code'],
                'buyer'  => $buyer,
                'items' => $items,
                'payment_method' => $order_info['payment_code'],
                'purchased_at'   => (new DateTime($order_info['date_added']))->format(DateTime::ATOM),
                'shipping_address' => [
                  'address' => $order_info['shipping_address_1']?: $order_info['shipping_address_2'],
                  'city'    => $order_info['shipping_city']
                ],
                'status' => 'complete',
              ],
          ],
          'shipping_address' => [
              'address' => $order_info['shipping_address_1']?: $order_info['shipping_address_2'],
              'city'    => $order_info['shipping_city']
          ]
        ];
        return $payment;
    }

    private function _getOrderPaymentCountryCode($payment_country_id){      
        $this->load->model('localisation/country');
        $country_code = $this->model_localisation_country->getCountry($payment_country_id)['iso_code_2'];
        
        switch ($country_code){
          case 'SA':
            return 'KSA';
          case 'AE':
            return 'UAE';
          case 'KW':
            return 'KWT';
          case 'BH':
            return 'BAH';
          default:
            return '';
        }
    }

    private function _getOrderPaymentCurrency($tabby_order_country_code, $account_currency){
      switch (strtoupper($tabby_order_country_code)){
          case 'KSA':
            return 'SAR';
          case 'UAE':
            return 'AED';
          case 'KWT':
            return 'KWD';
          case 'BAH':
            return 'BHD';
          default:
            return strtoupper($account_currency);
        }
    }

    private function validateDate($date, $format = 'Y-m-d'){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public function changeTabbyOrderStatus($order_id, $order_status_id){
        if($order_id && $order_status_id){
            $current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();
            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = '" . $current_date_time . "' WHERE order_id = '" . (int)$order_id . "'");
        }
    }

    public function addOrderHistory(array $data){
        $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$data['order_id'] . "', order_status_id = '" . (int)$data['order_status_id'] . "', notify = '" . $data['notify'] . "', comment = '" . $this->db->escape(strip_tags($data['comment'])) . "', user_id = 0, date_added = '" . date("Y-m-d H:i:s") . "'");

    }


    public function addTabbyCapture($details)
    {
        if (!$details)
            return false;

        $this->load->model('extension/payment_transaction');
        $order_id = ($details['payment']['order']['reference_id']) ?: 0;
        $details = json_encode($details);

        $this->model_extension_payment_transaction->insert([
            'order_id' => $order_id,
            'payment_method' => 'Tabby',
            'status' => 'Pending',
            'details' => $details,
        ]);
    }



}
