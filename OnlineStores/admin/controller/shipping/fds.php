<?php

class ControllerShippingFds extends Controller
{
    private $error = array();

    public function index()
    {
        $this->language->load('shipping/fds');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('localisation/tax_class');
        $this->load->model('localisation/geo_zone');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_setting_setting->insertUpdateSetting('fds', $this->request->post);

            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        // ======================== breadcrumbs =============================

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_shipping'),
            'href' => $this->url->link('extension/shipping', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('shipping/fds', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['cancel'] = $this->url->link('shipping/fds', '', 'SSL');

        // ======================== /breadcrumbs =============================

        $this->data['fds'] = $this->config->get('fds');
        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
        $this->data['google_map_api_key'] = $this->config->get('google_map_api_key');
        $this->data['checkout_app_link'] = $this->url->link('module/quickcheckout', '', 'SSL');
        $this->data['action'] = $this->url->link('shipping/fds', '' , 'SSL');

        $this->template = 'shipping/fds.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    protected function validate()
    {
        if ( ! $this->user->hasPermission('modify', 'shipping/fds') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }

    public function createShipment()
    {   
        $this->language->load('shipping/fds');
        
        $orderId = $this->request->get['order_id'];
        $error = false;

        $this->initializer([
            'sale/order',
            'shipping/fds',
        ]);
        $order_info = $this->order->getOrder($orderId);

        $settings = $this->config->get('fds');

        $checkExists = $this->fds->checkExists($orderId);
        
        $message = '';
        if($checkExists){
            $error = true;
            $this->data['create'] = 0;
            $this->data['text_warning'] = $this->language->get('shipment_exists');
            $message .= 'Tracking Number: '.$checkExists;
        }else{
            if(!$settings['token']){
                $error = true;
                $this->data['text_warning'] = $this->language->get('shipment_failed');
                $message .= $this->language->get('token_missed');
            }

            if(!$settings['lat'] || !$settings['lng'] || !$settings['location']){
                $error = true;
                $this->data['text_warning'] = $this->language->get('shipment_failed');
                $message .= $this->language->get('location_missed');
            }

            $payment_address_location = json_decode($order_info['payment_address_location'], true);
            if(!$payment_address_location){
                $error = true;
                $this->data['text_warning'] = $this->language->get('shipment_failed');
                $message .= $this->language->get('destination_location_missed');
            }
        }

        $this->data['message'] = $message;
        /// Creat Shiping Proccess
        if(!$error){
            
            $fds_token = $settings['token'];
            $order_products = $this->order->getOrderProducts($orderId);
            
            $products = array();
            $total_no_shipment = 0;
            $total_weight = 0;

            $product_list = '';
            $quantity = 0;
            
            foreach ($order_products as $order_product) {
                $product_list .= $order_product['name']. ': ('.$order_product['quantity'].'),';
                $quantity += $order_product['quantity'];
                $total_weight += ($order_product['quantity'] * $order_product['weight']);
                $total_no_shipment += $order_product['total'];
            }

            $telephone = $order_info['telephone'] ? $order_info['telephone']:  '';

            $payment_address_location = json_decode($order_info['payment_address_location'], true);
            
            $data = array(
                'token'         => $fds_token,
                'weight'        => $total_weight,
                'phone'         => $telephone,
                'price'         => ceil($total_no_shipment),
                'to_location'   => $order_info['shipping_address_1']. ', '.$order_info['shipping_city']. ', '.$order_info['shipping_country'],
                'to_lat'        => $payment_address_location['lat'],
                'to_lng'        => $payment_address_location['lng'],
                'delivery_date' => date('Y-m-d'),
                'order_number'  => $order_info['order_id'],
                'product'       => $product_list,
                'quantity'      => $quantity,
                'from_location' => $settings['location'],
                'from_lat'      => $settings['lat'],
                'from_lng'      => $settings['lng']
            );
            
            $postdata = http_build_query($data);
            
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL,"http://services.field-solution.co/backend/client/shipment/create");
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
            $result = curl_exec($curl);

            curl_close($curl);
            $shippresult = json_decode($result, true);
          
            if (isset($shippresult['result_code'])) {

                $details['message']['en']   = $shippresult['message']['en'];
                $details['message']['ar']   = $shippresult['message']['ar'];
                
                //Success Creat Shipping
                if($shippresult['result_code'] == 0){
                     $this->data['traking'] = $shippresult['list'][0]['tracking_number'];              
                     $this->data['create'] = 1;

                     //Create tracking link
                    $track_url = $this->url->link('shipping/fds/tracking&trackingnumber='.$this->data['traking'], '', 'SSL');
                    $this->db->query("UPDATE " . DB_PREFIX . "`order` SET shipping_tracking_url = '" . $this->db->escape($track_url) . "' WHERE order_id = '" . (int)$orderId . "'");
                }else{
                    if($shippresult['flags']){
                        $message = '';
                        foreach ($shippresult['flags'] as $key => $value) {
                            $message .= $key.': '.$value[0].'<br/>';
                        }
                    }
                     $this->data['create'] = 0;
                     $this->data['text_warning'] = $this->language->get('shipment_failed');
                     $this->data['message'] = $message;
                }   
            }
        }
        ///////////////////////////////////

        $this->language->load('shipping/fds');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_shipping'),
            'href' => $this->url->link('extension/shipping', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('shipping/fds', '', 'SSL'),
            'separator' => ' :: '
        );
        
        $this->template = 'shipping/fds_info.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
        return;
    }
}
