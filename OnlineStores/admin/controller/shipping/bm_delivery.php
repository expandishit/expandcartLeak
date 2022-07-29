
<?php

use ExpandCart\Foundation\Providers\Extension;

class ControllerShippingBmDelivery extends Controller
{
    /**
     * The Bm Delivery model
     *
     * @var Object
     */
    private $shippingModel;

    /**
     * Constructor
     *
     * @param Registry $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language('shipping/bm_delivery');
        $this->shippingModel = $this->load->model('shipping/bm_delivery', ['return' => true]);
    }

    /**
     * The install hook
     *
     * @return void
     */
    public function install()
    {
        $this->shippingModel->install();
    }

    /**
     * Validation errors bag
     *
     * @var array
     */
    private $error = [];

    /**
     * The bm_delivery settings view
     *
     * @return void
     */
    public function index()
    {
        //save button - Ajax post request
        $this->data['action'] = $this->url->link('shipping/bm_delivery/save', '', 'SSL');
        $this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);

        $this->data['settings'] = $this->shippingModel->getSettings();
        $this->data['lang'] = $this->config->get('config_admin_language');

        $this->load->model('localisation/geo_zone');
        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->load->model('localisation/order_status');
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->load->model('localisation/tax_class');
        $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        // Breadcrumbs
        $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

        // prepare bm_delivery.expand view data
        $this->document->setTitle($this->language->get('heading_title'));
        $this->template = 'shipping/bm_delivery/settings.expand';
        $this->children = ['common/header', 'common/footer'];
        $this->response->setOutput($this->render());
    }

    /**
     * Save form data and Enable Extension after data validation.
     *
     * @return void
     */
    public function save()
    {
        if ($this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST') {
            // Validate form fields
            if (!$this->_validateSettingsForm($this->request->post['settings'])) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
            } else {
                //Save bm_delivery config data in settings table
                $this->shippingModel->updateSettings($this->request->post['settings']);

                $this->tracking->updateGuideValue('SHIPPING');

                $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success']  = '1';
            }

            $this->response->setOutput(json_encode($result_json));
        } else {
            $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
        }
    }
    
    /**
     * Display the creation form to fill in by the user.
     */
    public function createShipment()
    {
        if (!$this->user->hasPermission('modify', 'shipping/bm_delivery')) {
            $this->response->redirect($this->url->link('error/permission', '', 'SSL'));
            return;
        }

        if (!Extension::isInstalled('bm_delivery') or !$this->shippingModel->isActive()) {
            $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
            return;
        }

        $this->load->model('sale/order');

        $order = $this->model_sale_order->getOrder($this->request->get['order_id']);

        // Check if order has already shipped 
        if (!empty($order['tracking'])) {
            $this->session->data['error'] = $this->language->get('text_order_already_exist');
            $this->response->redirect($this->url->link('sale/order/info?order_id=' . $this->request->get['order_id'], '', true));
        }

        $this->data['order'] = $order;

        $this->data['shipper_email'] = $this->config->get('config_email');
        $this->data['shipper_address'] = $this->config->get('config_address')[$this->config->get('config_admin_language')];
        $this->data['shipper_telephone'] = $this->config->get('config_telephone');
        $this->data['shipper_country_id'] = $this->config->get('config_country_id');

        $this->data['areas'] = $this->shippingModel->getAreas();

        $this->load->model('localisation/country');
        $this->data['countries'] = $this->model_localisation_country->getCountries();

        $this->document->setTitle($this->language->get('create_heading_title'));
        //Breadcrumbs
        $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
        // Render Content
        $this->template = 'shipping/bm_delivery/shipment/create.expand';
        $this->children = ['common/header', 'common/footer'];
        $this->response->setOutput($this->render_ecwig());
    }

    /**
     * Store the order data in the shipping gateway DB via external APIs.
     */
    public function storeShipment()
    {
        if ($this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST') {
            $settings = $this->shippingModel->getSettings();

            // Validate form fields
            if (!empty($this->_validateShipmentForm())) {
                $result_json['success'] = '0';
                $result_json['errors']  = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }
            // create a order
            $this->load->model('sale/order');

            $order = $this->model_sale_order->getOrder($this->request->post['order_id']);

            if (!$order) {
                $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
                return;
            }

            $results = $this->shippingModel->createShipment([
                'company_sequence' =>"bm_delivery",
                'customer_address' => $this->request->post['customer_address'],
                'customer_mobile' => $this->request->post['customer_mobile'],
                'customer_name' => $this->request->post['customer_name'],
                'customer_area' => $this->request->post['customer_area'],
                'cost' => round($order['total'], 2),
                'login' => $settings['auth']['login'],
                'password' => $settings['auth']['password'],
                'db'=> $settings['auth']['db']
            ]);

            $content = $results->getContent();

            if ($results->ok() && empty($content['error'])) {
                // bm delivery order id
                $shipment_id = $content['result']['ID'];

                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->session->data['success'] = $this->language->get('text_shipment_created_successfully');

                // Add history record
                if (!empty($settings['after_creation_status'])) {
                    $this->model_sale_order->addOrderHistory($order['order_id'], [
                        'comment' => 'BM Delivery - shipment_id: ' . $shipment_id,
                        'order_status_id'  => $settings['after_creation_status'],
                    ]);
                }

                $this->model_sale_order->updateShippingTrackingNumber($order['order_id'], $shipment_id);

                // Redirect
                $result_json['redirect'] = '1';
                $result_json['to'] = '' . $this->url->link('sale/order/info?order_id=' . $order['order_id'], '', true);

                $this->response->setOutput(json_encode($result_json));
                return;
            }

            $result_json['success'] = '0';
            $result_json['errors']  = 'ERROR: ' . $content['error']['message'];

            $this->response->setOutput(json_encode($result_json));
            return;
        } else {
            $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
        }
    }

    /**
     * Validate settings form fields.
     *
     * @return bool
     */
    private function _validateSettingsForm(array $settings): bool
    {
        $this->load->language('shipping/bm_delivery');

        if (!$this->user->hasPermission('modify', 'shipping/bm_delivery')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!Extension::isInstalled('bm_delivery')) {
            $this->error['bm_delivery_not_installed'] = $this->language->get('error_not_installed');
        }

        if (!utf8_strlen($settings['auth']['db'])) {
            $this->error['auth_db'] = $this->language->get('error_auth_db');
        }

        if (!utf8_strlen($settings['auth']['login'])) {
            $this->error['auth_login'] = $this->language->get('error_auth_login');
        }

        if (!utf8_strlen($settings['auth']['password'])) {
            $this->error['auth_password'] = $this->language->get('error_auth_password');
        }

        if ($settings['price']['weight_general_rate'] <= 0) {
            $this->error['weight_general_rate'] = $this->language->get('error_weight_general_rate');
        }

        // check bm_delivery credentials
        if (empty($this->error)) {
            $results = $this->shippingModel->createAuthToken($settings['auth']['db'], $settings['auth']['login'], $settings['auth']['password']);

            $content = $results->getContent();
            $headers = $results->getHeaders();
            
            if (!$results->ok() || !empty($content['error'])) {
                $this->error['error_invalid_credentials'] = $this->language->get('error_invalid_credentials');
            } else {
                $this->request->post['settings']['auth']['session_id'] = $content['result']['session_id'];
                
                if (isset($headers['Set-Cookie'])) {
                    $cookies = [];
                    try {
                        foreach ($headers['Set-Cookie'] as $cookie) {
                            $cookieValue = \reset(explode('; ', $cookie));
                            if($cookieValue) {
                                list($key, $value) = explode('=', $cookieValue, 2);
                                $cookies[trim($key)] = trim($value);
                                
                            }
                        }
                    } catch (\Throwable $th) {
                        $cookies = [];
                    }
                    
                    $this->request->post['settings']['auth']['cookies'] = $cookies;
                }
                
            }
        }

        if (!empty($this->error)) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    /**
     * Check request is ajax
     *
     * @return boolean
     */
    private function _isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * Form the breadcrumbs array.
     *
     * @return Array $breadcrumbs
     */
    private function _createBreadcrumbs()
    {
        return [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', '', 'SSL')
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link('extension/shipping', 'type=shipping', true)
            ],
            [
                'text' => $this->language->get($this->request->get['order_id'] ? 'create_heading_title' : 'heading_title'),
                'href' => $this->url->link('shipping/bm_delivery', true)
            ],
        ];
    }

    private function _validateShipmentForm()
    {
        $data = $this->request->post;

        if (!$this->user->hasPermission('modify', 'shipping/bm_delivery')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($data['customer_name']) < 1)) {
            $this->error['customer_name'] = $this->language->get('error_customer_name');
        }

        if ((utf8_strlen($data['customer_mobile']) < 5)) {
            $this->error['customer_mobile'] = $this->language->get('error_customer_mobile');
        }

        if ((utf8_strlen($data['customer_address']) < 3)) {
            $this->error['customer_address'] = $this->language->get('error_customer_address');
        }

        if ((utf8_strlen($data['customer_area']) < 1)) {
            $this->error['customer_area'] = $this->language->get('error_customer_area');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error;
    }
}
