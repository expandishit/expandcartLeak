<?php

/**
 *   Controller Class for EButler Module
 *
 * @author Fayez.
 */
class ControllerModuleEButler extends Controller
{
    public $route = 'module/ebutler';
    public $module = 'ebutler';

    public function index()
    {

        if (!\Extension::isInstalled("{$this->module}") || !$this->user->hasPermission("modify", "module/{$this->module}")) {
            return $this->forward('error/permission');
        }

        $this->load->model('setting/setting');
        $this->load->language("module/{$this->module}");
		$this->load->model('localisation/order_status');
        $this->load->model('module/quickcheckout_fields');
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_modules'),
            'href' => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get("heading_title"),
            'href' => $this->url->link("module/{$this->module}", '', 'SSL'),
            'separator' => ' :: '
        );
        
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $this->data['entry_sending_order_status'] = $this->language->get('entry_sending_order_status');
        $this->data['custom_fields'] = $this->model_module_quickcheckout_fields->getFields();

        $this->data["{$this->module}_data"]['ebutler_app_shipping_location_qna'] =$this->config->get('ebutler_app_shipping_location_qna');
        $this->data["{$this->module}_data"]['ebutler_app_status'] =$this->config->get('ebutler_app_status');
        $this->data["{$this->module}_data"]['ebutler_api_key'] = $this->config->get('ebutler_api_key');
        $this->data["{$this->module}_data"]['ebutler_sending_order_status'] = $this->config->get('ebutler_sending_order_status');
        $this->data["{$this->module}_data"]['ebutler_order_pending'] = $this->config->get('ebutler_order_pending');
        $this->data["{$this->module}_data"]['ebutler_order_in_schedule'] = $this->config->get('ebutler_order_in_schedule');
        $this->data["{$this->module}_data"]['ebutler_order_pending_payment'] = $this->config->get('ebutler_order_pending_payment');
        $this->data["{$this->module}_data"]['ebutler_order_pending_delivery'] = $this->config->get('ebutler_order_pending_delivery');
        $this->data["{$this->module}_data"]['ebutler_order_in_route'] = $this->config->get('ebutler_order_in_route');
        $this->data["{$this->module}_data"]['ebutler_order_rejected'] = $this->config->get('ebutler_order_rejected');
        $this->data["{$this->module}_data"]['ebutler_order_pending_pickup'] = $this->config->get('ebutler_order_pending_pickup');
        $this->data["{$this->module}_data"]['ebutler_order_preparing_order'] = $this->config->get('ebutler_order_preparing_order');
        $this->data["{$this->module}_data"]['ebutler_order_delivered'] = $this->config->get('ebutler_order_delivered');
        $this->data["{$this->module}_data"]['ebutler_order_canceled'] = $this->config->get('ebutler_order_canceled');
        $this->data["{$this->module}_data"]['ebutler_order_delayed'] = $this->config->get('ebutler_order_delayed');
        $this->data["{$this->module}_data"]['ebutler_order_claimed'] = $this->config->get('ebutler_order_claimed');
        $this->data["{$this->module}_data"]['ebutler_qna_buildingnumber'] = $this->config->get('ebutler_qna_buildingnumber');
        $this->data["{$this->module}_data"]['ebutler_qna_streetnumber'] = $this->config->get('ebutler_qna_streetnumber');
        $this->data["{$this->module}_data"]['ebutler_qna_zonenumber'] = $this->config->get('ebutler_qna_zonenumber');


        $this->data['save_app_settings'] = $this->url->link("module/{$this->module}/saveAppSettings", '', 'SSL');
        $this->data['order_details_link'] = $this->url->link("module/{$this->module}/orderDetails", '', 'SSL');
        $this->data['order_sync_url'] = $this->url->link("common/webhooks/ebutlerOrderSyncWebhook", '', 'SSL');
        $this->data['order_sync_url_text'] = $this->url->link("common/webhooks/ebutlerOrderSyncWebhook", '', 'SSL');
        
        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['is_delivery_slots'] = (\Extension::isInstalled("delivery_slot") && $this->config->get('delivery_slot')['status'] == "1");
        $this->data['delivery_slots_warning'] = $this->language->get('delivery_slots_warning');

        $this->template = "module/{$this->module}.expand";

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }


    public function saveAppSettings() {
     
        $this->load->language("module/{$this->module}");
        $this->load->model('setting/setting');
        
        $ebutler_app_status = $this->request->post["{$this->module}_app_status"];
        $ebutler_api_key = $this->request->post["{$this->module}_api_key"];
        $ebutler_sending_order_status = $this->request->post["{$this->module}_sending_order_status"];
        $ebutler_order_pending = $this->request->post["{$this->module}_order_pending"];
        $ebutler_order_in_schedule = $this->request->post["{$this->module}_order_in_schedule"];
        $ebutler_order_pending_payment = $this->request->post["{$this->module}_order_pending_payment"];
        $ebutler_order_pending_delivery = $this->request->post["{$this->module}_order_pending_delivery"];
        $ebutler_order_in_route = $this->request->post["{$this->module}_order_in_route"];
        $ebutler_order_rejected = $this->request->post["{$this->module}_order_rejected"];
        $ebutler_order_pending_pickup = $this->request->post["{$this->module}_order_pending_pickup"];
        $ebutler_order_preparing_order = $this->request->post["{$this->module}_order_preparing_order"];
        $ebutler_order_delivered = $this->request->post["{$this->module}_order_delivered"];
        $ebutler_order_canceled = $this->request->post["{$this->module}_order_canceled"];
        $ebutler_order_delayed = $this->request->post["{$this->module}_order_delayed"];
        $ebutler_order_claimed = $this->request->post["{$this->module}_order_claimed"];
        $ebutler_app_shipping_location_qna = $this->request->post["{$this->module}_app_shipping_location_qna"];

        $ebutler_qna_buildingnumber = $this->request->post["{$this->module}_qna_buildingnumber"];
        $ebutler_qna_streetnumber = $this->request->post["{$this->module}_qna_streetnumber"];
        $ebutler_qna_zonenumber = $this->request->post["{$this->module}_qna_zonenumber"];

        
        $ebutler_settings['ebutler_app_status'] = $this->config->get('ebutler_app_status');
        $ebutler_settings['ebutler_api_key'] = $this->config->get('ebutler_api_key');
        $ebutler_settings['ebutler_sending_order_status'] = $this->config->get('ebutler_sending_order_status');
        $ebutler_settings['ebutler_order_pending'] = $this->config->get('ebutler_order_pending');
        $ebutler_settings['ebutler_order_in_schedule'] = $this->config->get('ebutler_order_in_schedule');
        $ebutler_settings['ebutler_order_pending_payment'] = $this->config->get('ebutler_order_pending_payment');
        $ebutler_settings['ebutler_order_pending_delivery'] = $this->config->get('ebutler_order_pending_delivery');
        $ebutler_settings['ebutler_order_in_route'] = $this->config->get('ebutler_order_in_route');
        $ebutler_settings['ebutler_order_rejected'] = $this->config->get('ebutler_order_rejected');
        $ebutler_settings['ebutler_order_pending_pickup'] = $this->config->get('ebutler_order_pending_pickup');
        $ebutler_settings['ebutler_order_preparing_order'] = $this->config->get('ebutler_order_preparing_order');
        $ebutler_settings['ebutler_order_delivered'] = $this->config->get('ebutler_order_delivered');
        $ebutler_settings['ebutler_order_canceled'] = $this->config->get('ebutler_order_canceled');
        $ebutler_settings['ebutler_order_delayed'] = $this->config->get('ebutler_order_delayed');
        $ebutler_settings['ebutler_order_claimed'] = $this->config->get('ebutler_order_claimed');
        $ebutler_settings['ebutler_app_shipping_location_qna'] = $this->config->get('ebutler_app_shipping_location_qna');

        $ebutler_settings['ebutler_qna_buildingnumber'] = $this->config->get('ebutler_qna_buildingnumber');
        $ebutler_settings['ebutler_qna_streetnumber'] = $this->config->get('ebutler_qna_streetnumber');
        $ebutler_settings['ebutler_qna_zonenumber'] = $this->config->get('ebutler_qna_zonenumber');

        
        $ebutler_settings = array_merge(
            $ebutler_settings, 
            compact(
                'ebutler_app_status',
                'ebutler_api_key',
                'ebutler_sending_order_status',
                'ebutler_order_pending',
                'ebutler_order_in_schedule',
                'ebutler_order_pending_payment',
                'ebutler_order_pending_delivery',
                'ebutler_order_in_route',
                'ebutler_order_rejected',
                'ebutler_order_pending_pickup',
                'ebutler_order_preparing_order',
                'ebutler_order_delivered',
                'ebutler_order_canceled',
                'ebutler_order_delayed',
                'ebutler_order_claimed',
                'ebutler_app_shipping_location_qna',
                'ebutler_qna_buildingnumber',
                'ebutler_qna_streetnumber',
                'ebutler_qna_zonenumber'
            )
        );

        $this->model_setting_setting->editSetting($this->module, $ebutler_settings);
        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
        $result_json['success'] = '1';
        return $this->response->setOutput(json_encode($result_json));
    }


    public function syncEbutlerOrder($orderInfo, $ebutler_products) {
        if(\Extension::isInstalled('delivery_slot') && $this->config->get('delivery_slot')['status'] == 1)
        {
            $this->load->model('module/delivery_slot/slots');
            $orderDeliverySlot = $this->model_module_delivery_slot_slots->getOrderDeliverySlot($orderInfo['order_id']);
            
            $orderSlotDetails = '';

            if(!empty($orderDeliverySlot))
                $orderSlotDetails = $this->model_module_delivery_slot_slots->getSlot($orderDeliverySlot['ds_delivery_slot_id']);
        }
        $this->load->model('module/quickcheckout_fields');
        $this->data['custom_fields'] = $this->model_module_quickcheckout_fields->getOrderCustomFields($orderInfo['order_id']);
        $ebutler_products = array_map(function ($product) use ($orderInfo){
            return [
                "index" => $product['product_id'],
                "name" => $product['name'],
                "quantity" => (int)$product['quantity'],
                "price" => $this->currency->convertUsingRatesAPI($product['price'], $orderInfo['currency_code'], 'QAR')

            ];
        }, $ebutler_products);

        $ebutler_lat = 0;
        $ebutler_lng = 0;

        if ($orderInfo['shipping_address_location']) {
            $shipping_address_location = $orderInfo['shipping_address_location'];
            $shipping_address_location = json_decode($shipping_address_location, true);
            $ebutler_lat = $shipping_address_location['lat'];
            $ebutler_lng = $shipping_address_location['lng'];
        }
        if($this->config->get('ebutler_app_shipping_location_qna') == true){

            $this->load->model('module/quickcheckout_fields');
            $custom_fields = $this->model_module_quickcheckout_fields->getOrderCustomFields($orderInfo['order_id'], $this->config->get("config_language"));
            if($custom_fields && $custom_fields['shipping_address']){
                foreach ($custom_fields['shipping_address'] as $fld){
                    if($fld['field_id'] == $this->config->get('ebutler_qna_zonenumber'))
                        $zone_number = $fld['value'];
                    else if($fld['field_id'] == $this->config->get('ebutler_qna_streetnumber'))
                        $street_number = $fld['value'];
                    else if($fld['field_id'] == $this->config->get('ebutler_qna_buildingnumber'))
                        $building_number = $fld['value'];
                }
            }

            $delivery_address = [
                "nickname" => $orderInfo['shipping_firstname'] . " " . $orderInfo['shipping_lastname'],
                "locationType"=>"nationalAddress",
                "zone_number" => $zone_number ?? '',
                "street_number"=> $street_number ?? '',
                "building_number"=> $building_number ?? '',
            ];


        }else{
            $delivery_address = [
                "nickname" => $orderInfo['shipping_firstname'] . " " . $orderInfo['shipping_lastname'],
                "pin_link" => "https://www.google.com/maps?q=${ebutler_lat},${ebutler_lng}",
                "Pin_lat" => $ebutler_lat,
                "Pin_lng" => $ebutler_lng
            ];
        }

        $payment_method = 'prepaid';
        if( in_array($orderInfo['payment_code'], ['ccod', 'cod']) ){
            $payment_method = 'cash';
        }elseif ($orderInfo['payment_code'] == 'ebutler' ) {
            $payment_method = 'online';
        }



        $total_data = [];

        $taxes =  $this->cart->getTaxes();

        $this->load->model('total/sub_total');
        $this->model_total_sub_total->getTotal($sub_total_data, $temp_total , $taxes);

        $this->load->model('total/shipping');
        $this->model_total_shipping->getTotal($total_shipping_data, $temp_total , $taxes);

        $dateObject = new DateTime( str_replace("-", "/",  $orderDeliverySlot["delivery_date"]));

        $date = $dateObject->format("Y-m-d");

        $pickupDateObj = new DateTime( str_replace("-", "/",  $orderInfo["date_added"]));

        $pickupDate = $pickupDateObj->format("Y-m-d");

        $pickupTimeObj = new DateTime( str_replace("-", "/",  $orderInfo["date_added"]));

        $pickupTime = $pickupTimeObj->format("H:i");

        $ebutler_order = [
            "integrationType" => "order",
            "orderData" => [
                "first_name"  => $orderInfo['firstname'],
                "last_name"   => $orderInfo['lastname'] ? $orderInfo['lastname'] : $orderInfo['firstname'],
                
                "phone"       => $orderInfo['telephone'],
                "country_code"=> $orderInfo['payment_phonecode'] ?: $orderInfo['shipping_phonecode'],
                
                "email"       => $orderInfo['email'],
                "pickup_date" =>  $pickupDate,  //date("Y-m-d", strtotime($orderInfo['date_added'])),
                "pickup_time" =>  $pickupTime, //date("H:i", strtotime($orderInfo['date_added'])),
                
                "delivery_date"      =>   $date, //date("Y-m-d", strtotime($orderDeliverySlot['delivery_date']) ),
                "delivery_slot_from" => $orderSlotDetails['time_start_formated'],
                "delivery_slot_to"   => $orderSlotDetails['time_end_formated'],

                "invoice_number"  => $orderInfo['invoice_prefix'] . $orderInfo['invoice_no'],

                "invoiced_amount" => $this->currency->convertUsingRatesAPI($sub_total_data[0]['value'], $orderInfo['currency_code'], 'QAR'), //sub-total
                "delivery_amount" => $this->currency->convertUsingRatesAPI($total_shipping_data[0]['value'], $orderInfo['currency_code'], 'QAR'), //shipping cost
                "total_amount"    => $this->currency->convertUsingRatesAPI($orderInfo['total'], $orderInfo['currency_code'], 'QAR'), //total
                
                "payment_method"  => $payment_method,
                "source"          => "e_commerce_website",
                "delivery_address"=> $delivery_address,
                "items"           => $ebutler_products,
                "delivery_action" => "delivery_location",
                "delivery_type"   => "scheduled",
                "is_gift"         => (isset($this->session->data["payment_address"]["send_as_gift"]) && $this->session->data["payment_address"]["send_as_gift"] == 1) ? 1 : 0,
                "order_remarks"   => $orderInfo["comment"] ?? "",
                "is_test" => True
            ]
        ];

        if(isset($this->session->data["ebutler_payment_code"])) {
            $ebutler_order["orderData"]["paymentCode"] = $this->session->data["ebutler_payment_code"];

            unset($this->session->data["ebutler_payment_code"]);

        }


        
        $API_KEY = $this->config->get('ebutler_api_key');

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://saas-api.e-butler.com/integration/order_payment",            
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($ebutler_order),
            CURLOPT_HTTPHEADER => [
                "cache-control: no-cache",
                "content-type: application/json",
                "saasapikey: " . $API_KEY
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response);
    }

    public function updateEbutlerOrderId($ebutler_order_id, $order_id) {
        $this->db->query("UPDATE `order` SET ebutler_order_id='{$ebutler_order_id}' where order_id='{$order_id}'");
    }

    public function updateEbutlerOrderStatus($status_id, $ebutler_order_id) {
        $order_id = $this->db->query("SELECT `order_id` FROM `order` WHERE ebutler_order_id='{$ebutler_order_id}'")->row['order_id'];
        $app_dir = str_replace( 'system/', 'admin/', DIR_SYSTEM );
        require_once $app_dir."controller/sale/order.php";
        $sale_order_controller = new ControllerSaleOrder( $this->registry );
        $sale_order_controller->history($status_id, $order_id, true);
    }

    public function install()
    {

        $this->db->query("ALTER TABLE `order` ADD COLUMN `ebutler_order_id` VARCHAR(100)");
    }


    public function uninstall()
    {

        $this->db->query("ALTER TABLE `order` DROP COLUMN `ebutler_order_id`");
    }

}
