<?php

use ExpandCart\Foundation\Support\Facades\Url;

class ControllerShippingSmsa extends Controller
{

    private $order, $smsa;

    private $error = array();

    private function init($models)
    {
        // TODO modularize this.
        foreach ($models as $model) {
            $this->load->model($model);
            $object = explode('/', $model);
            $object = end($object);
            $model = str_replace('/', '_', $model);
            $this->$object = $this->{"model_" . $model};
        }

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);
            unset($this->session->data['errors']);
        }
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $this->language->load('shipping/smsa');
    }


    private function validate()
    {
        if ( ! $this->user->hasPermission('modify', 'shipping/smsa') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ( ! $this->request->post['smsa_wsdl'] )
        {
            $this->error['smsa_wsdl'] = $this->language->get('error_field_cant_be_empty');
        }

        if ( ! $this->request->post['smsa_passkey'] )
        {
            $this->error['smsa_passkey'] = $this->language->get('error_field_cant_be_empty');
        }

        if ( ! $this->request->post['smsa_first5'] )
        {
            $this->error['smsa_first5'] = $this->language->get('error_field_cant_be_empty');
        }

        if ( ! $this->request->post['smsa_after5'] )
        {
            $this->error['smsa_after5'] = $this->language->get('error_field_cant_be_empty');
        }

        if ( $this->request->post['smsa_customs'] < 0 || $this->request->post['smsa_customs'] > 1)
        {
            $this->error['smsa_customs'] = $this->language->get('error_invalid_customs');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }


    public function index()
    {
        $this->init([]);

        $this->load->model('shipping/smsa');
        $this->load->model('setting/setting');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'smsa', true);

            $this->model_setting_setting->editSetting('smsa', $this->request->post);
            $this->install();

            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->data['breadcrumbs'] = $this->model_shipping_smsa->getBreadcrumbs([
            ['text' => 'text_home', 'href' => Url::addPath(['common', 'home'])->format(), 'separator' => false],
            [
                'text' => 'text_shipping',
                'href' => Url::addPath(['extension', 'shipping'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => 'heading_title_smsa',
                'href' => Url::addPath(['shipping', 'smsa'])->format(),
                'separator' => ' :: '
            ],
        ]);

        $this->data['cancel'] = $this->url->link('shipping/smsa', '', 'SSL');

        $this->model_shipping_smsa->setConfig('smsa_wsdl', $this->data['data']);
        $this->model_shipping_smsa->setConfig('smsa_first5', $this->data['data']);
        $this->model_shipping_smsa->setConfig('smsa_after5', $this->data['data']);
        $this->model_shipping_smsa->setConfig('smsa_passkey', $this->data['data']);
        $this->model_shipping_smsa->setConfig('smsa_customs', $this->data['data']);
        $this->model_shipping_smsa->setConfig('smsa_status', $this->data['data']);
        $this->model_shipping_smsa->setConfig('smsa_sort_order', $this->data['data']);
        $this->model_shipping_smsa->setConfig('smsa_geo_zone_id', $this->data['data']);

        if (count($this->request->post) > 0) {
            $this->data['data'] = array_merge($this->data['data'], $this->request->post);
        }

        $this->data['errors'] = array();

        $this->document->setTitle($this->language->get('heading_title_smsa'));

        $this->load->model('setting/setting');

        $this->load->model('localisation/geo_zone');

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->template = 'shipping/smsa.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data['links'] = [
            'submit' => Url::addPath(['shipping', 'smsa'])->format(),
            'cancel' => Url::addPath(['extension', 'shipping'])->format(),
            'viewOrders' => Url::addPath(['shipping', 'smsa', 'viewOrders'])->format(),
        ];
        $this->response->setOutput($this->render_ecwig());
    }

    public function viewOrders()
    {
        $this->init([
            'shipping/smsa',
            'sale/order'
        ]);

        $this->data['breadcrumbs'] = $this->model_shipping_smsa->getBreadcrumbs([
            ['text' => 'text_home', 'href' => Url::addPath(['common', 'home'])->format(), 'separator' => false],
            [
                'text' => 'text_shipping',
                'href' => Url::addPath(['extension', 'shipping'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => 'heading_title_smsa',
                'href' => Url::addPath(['shipping', 'smsa'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => 'heading_title_list_orders',
                'href' => '',
                'separator' => ' :: '
            ],
        ]);

        $this->data['links'] = [
            'updateStatus' => $this->url->link(
                'shipping/smsa/updateStatus',
                '',
                'SSL'
            ),
            'settings' => $this->url->link(
                'shipping/smsa',
                '',
                'SSL'
            ),
            'viewOrder' => $this->url->link(
                'sale/order/info',
                '',
                'SSL'
            ),
        ];

        $this->data['orders'] = $this->smsa->getOrders();

        $this->template = 'shipping/smsa_order_list.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->document->addScript('view/javascript/shipping/smsa_order.js');

        $this->document->setTitle($this->language->get('heading_title_list_orders'));

        $this->response->setOutput($this->render());
    }

    public function createShipment()
    {
        $orderId = (isset($this->request->get['order_id']) ? $this->request->get['order_id'] : null);

        if ( ! isset($orderId) )
        {
            $result_json['error'] = $this->language->get('error_invalid_order_id');

            $this->response->setOutput( json_encode($result_json) );

            return;
        }

        $this->init([
            'shipping/smsa',
            'sale/order'
        ]);

        $settings = $this->smsa->getSettings();

        $url = '&order_id=' . $orderId;

        if ( isset($settings['smsa_status']))
        {
            $orderInfo = $this->smsa->getOrderInfo($orderId);

            $this->smsa->setWsdl($settings['smsa_wsdl']);

            if ( isset($orderInfo['order_id']) )
            {

                if ( $this->smsa->wsdl )
                {
                    $this->smsa->setRefNo(
                        $this->smsa->generateRefNo($orderInfo['order_id'])
                    )->createShipment($orderInfo, $this->smsa->wsdl, $settings['smsa_passkey'],$settings['smsa_customs']);

                    if ( ! ($shipmentCode = $this->smsa->getResponse()) )
                    {
                        $result_json['error'] = $this->smsa->errors;

                        $this->response->setOutput( json_encode($result_json) );

                        return;
                    }

                    $this->smsa->addShipmentInfo($orderInfo, $shipmentCode,$settings['smsa_customs']);
                    $result_json['success_msg'] = $this->language->get('message_shipment_created_successfully');
                    $this->response->setOutput( json_encode($result_json) );
                    return;

                }
                else
                {
                    $result_json['error'] = $this->language->get('error_invalid_wsdl_or_not_exists');
                    $this->response->setOutput( json_encode($result_json) );
                    return;
                }

            }
            else
            {
                $result_json['error'] = $this->language->get('error_no_order_exists');
                $this->response->setOutput( json_encode($result_json) );
                return;
            }

        }
        else
        {
            $result_json['error'] = $this->language->get('error_enable_the_app');
            $this->response->setOutput( json_encode($result_json) );
            return;
        }

        return;
    }

    public function updateStatus()
    {
        $orderId = (isset($this->request->get['order_id']) ? $this->request->get['order_id'] : null);

        if (!isset($orderId)) {

            $this->session->data['errors'][] = $this->language->get('error_invalid_order_id');

            $this->redirect(
                $this->url->link(
                    'hipping/smsa/viewOrders',
                    '',
                    'SSL'
                )
            );
        }

        $this->init([
            'shipping/smsa',
            'sale/order'
        ]);

        $settings = $this->smsa->getSettings();

        if (isset($settings['smsa_status']) && $settings['smsa_status'] == 1) {
            $shipmentInfo = $this->smsa->getShipmentInfo($orderId);

            $this->smsa->setWsdl($settings['smsa_wsdl']);

            if (isset($shipmentInfo['shipment_id'])) {
                $status = $this->smsa
                    ->setRefNo($shipmentInfo['ref_no'])
                    ->setAWB($shipmentInfo['shipment_code'])
                    ->setPassKey($settings['smsa_passkey'])
                    ->getStatus();

                if (!$status) {
                    die(json_encode(['status' => 'error', 'errors' => $this->smsa->errors]));
                    exit;
                }

                if ($shipmentInfo['shipment_status'] != $status) {
                    $this->order->addOrderHistory($shipmentInfo['order_id'], [
                        'order_status_id' => $status,
                        'notify' => 0,
                        'comment' => $this->language->get('smsa_shipment_status_' . $status)
                    ]);
                }

                die(json_encode([
                    'status' => $status,
                    'statusCode' => $status,
                    'statusString' => $this->language->get('smsa_shipment_status_' . $status)
                    ]));
                exit;


            } else {
                die(json_encode([
                    'status' => 'error', 'errors' => [
                        $this->language->get('error_no_shipments')
                    ]
                ]));
                exit;
            }

        } else {
            die(json_encode([
                'status' => 'error', 'errors' => [$this->language->get('error_enable_the_app')]
            ]));
            exit;
        }
    }

    public function install()
    {
        $this->init(['shipping/smsa']);

        $this->smsa->install();
    }

    public function shipmentDetails(){
        $this->language->load('shipping/smsa');
        $this->language->load('sale/order');
        // set Page Title
        $this->document->setTitle($this->language->get('entry_smsa'));
        $this->load->model('sale/order');
        $this->load->model("shipping/smsa");

        $order_id = $this->request->get['order_id'];
        $awb = $this->request->get['awb'];

        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => Url::addPath(['common', 'home'])->format(),
                'separator' => false
            ],
            [
                'text' => $this->language->get('view_order'),
                'href' => Url::addPath(['sale', 'order/info?order_id='.$order_id])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('entry_smsa'),
                'href' => Url::addPath(['shipping', 'smsa'])->format(),
                'separator' => ' :: '
            ],
        ];

        $this->data['smsa_label_url']  =  $this->url->link('shipping/smsa/getShipmentPdf?order_id='.$order_id.'&awb='.$awb, '', 'SSL');
        $order_data = $this->model_sale_order->getOrder($order_id);
        $shipmentDetails = $this->model_shipping_smsa->getShipmentInfo($order_id);
        
        $shipment_info = array();
        $shipment_info['shipping_firstname'] = $order_data['shipping_firstname'];
        $shipment_info['shipping_address_1'] = $order_data['shipping_address_1'];
        $shipment_info['shipping_city'] = $order_data['shipping_city'];
        $shipment_info['shipping_country'] = $order_data['shipping_country']; 
        $shipment_info['shipping_method'] = $order_data['shipping_method'];
        $shipment_info['awb'] = $awb;
        $shipment_info['ref_number'] = $shipmentDetails['ref_no'];

        $this->data['shipment_info'] = $shipment_info;
   
        $this->template = 'shipping/smsa/details.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function getShipmentPdf(){
        $this->load->model("shipping/smsa");

        $order_id = $this->request->get['order_id'];
        $awb = $this->request->get['awb'];
        $settings = $this->model_shipping_smsa->getSettings();

        $this->model_shipping_smsa->setWsdl($settings['smsa_wsdl']);
        $this->model_shipping_smsa->setAWB($awb);
        $this->model_shipping_smsa->setPassKey($settings['smsa_passkey']);
        $this->model_shipping_smsa->getPDF();
    }
}
