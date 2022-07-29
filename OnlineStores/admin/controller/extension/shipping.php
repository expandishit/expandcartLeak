<?php

class ControllerExtensionShipping extends Controller
{

    public function index()
    {

        if ($this->request->get['card_name']){
            $this->session->data['card_name'] = $this->request->get['card_name'];
        }

        $this->load->model('setting/setting');
        $gettingStarted = $this->model_setting_setting->getGuideValue("GETTING_STARTED");

        if($gettingStarted['SHIPPING'] != 1){
            $count = count($this->get_activated_shipping_methods());
            if ($count > 1){
                $gettingStarted['SHIPPING'] = 1;
                $this->tracking->updateGuideValue('SHIPPING');
            }
        }
        $this->data['shipping_enabled'] = $gettingStarted['SHIPPING'];

        $this->language->load('extension/shipping');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/shipping', '', 'SSL'),
            'separator' => ' :: '
        );

        if (isset($this->session->data['successDe'])) {
            $this->data['success'] = $this->session->data['successDe'];

            unset($this->session->data['successDe']);
        } else {
            $this->data['success'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        if (isset($this->session->data['error'])) {
            $this->data['error'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } else {
            $this->data['error'] = '';
        }

        $this->initializer([
            'shipping' => 'extension/shipping'
        ]);


        //get expandship info
        $this->data['out_egypt'] 		= false;


        $whmcs			= new whmcs();
        $clientDetails 	= $whmcs->getClientDetails(WHMCS_USER_ID);
        if(!empty($clientDetails))
            $this->data['out_egypt']  = (strtoupper($clientDetails['countrycode']) != 'EG');

        $this->data['expandship'] = $this->config->get('expandship')??[];
        $this->data['shipping_gateway_type'] = $this->request->get['gateway_type'];
        $this->data['manual_gateway_url'] = $this->url->link('extension/shipping?gateway_type=manual', '', 'SSL');
        $this->data['third_gateway_url'] = $this->url->link('extension/shipping?gateway_type=3rd_party', '', 'SSL');

        $page = 1;
        $offset = 0;
        $perPage = 12;
        if (isset($this->request->get['page']) && $this->request->get['page'] > 1) {
            $page = $this->request->get['page'];

            $offset = ($page - 1) * $perPage;
        }

        $this->data['filterTypes'] = [];

        $installed = $types = $lookup = null;

        if (isset($this->request->get['types'])) {
            $this->data['filterTypes'] = $types = array_filter($this->request->get['types']);
        }

        if (isset($this->request->get['installed'])) {
            $this->data['installed'] = $installed = $this->request->get['installed'];
        }

        if (isset($this->request->get['lookup'])) {
            $this->data['lookup'] = $lookup = $this->request->get['lookup'];
        }

        $queryData = [
//            'start' => $offset,
//            'limit' => $perPage,
//            'installed' => $installed,
            'types' => $types,
            'lookup' => $lookup,
        ];

        $recommendedShippingMethodsData = $this->shipping->getRecommendedShippingMethods();

        foreach ($recommendedShippingMethodsData['data'] as $key => $extension) {
            $settingGroup = $this->config->get($extension['code']);
            $status = null;
            if ($settingGroup && is_array($settingGroup) === true) {
                $status = $settingGroup['status'];
            } else {
                $status = $this->config->get($extension['code'] . '_status');
            }

            $recommendedShippingMethodsData['data'][$key]['installed']=($status != null ?  : false);
            $recommendedShippingMethodsData['data'][$key]['supported_countries']= $this->getCountries($extension['supported_countries']);
            $recommendedShippingMethodsData['data'][$key]['description']=html_entity_decode($extension['description']);

        }

        $this->data['recommended_shipping_methods'] = $recommendedShippingMethodsData;

        $shippingMethodsData = $this->shipping->getShippingMethodsGrid(
            $queryData,
            $this->config->get('config_language_id')
        );
        $this->data['types'] = $this->shipping->getTypes();

        $shippingMethods = [];

        foreach ($shippingMethodsData['data'] as $key => $extension) {
            $supported_countries =  $this->getCountries($extension['supported_countries']);

            $settingGroup = $this->config->get($extension['code']);
            $status = null;
            if ($settingGroup && is_array($settingGroup) === true) {
                $status = $settingGroup['status'];
                $sortorder = $settingGroup['sort_order'];
            } else {
                $status = $this->config->get($extension['code'] . '_status');
                $sortorder = $this->config->get($extension['code'] . '_sort_order');
            }

            if (isset($this->request->get['enabled']) || isset($this->request->get['disabled']) || isset($this->request->get['installed'])) {
                if (isset($this->request->get['enabled']) && $this->request->get['enabled'] == 1) {

                    $this->data['enabled'] = $this->request->get['enabled'];

                    if ($status == 1) {
                        $shippingMethods[$key] = [
                            'code' => $extension['code'],
                            'title' => $extension['title'],
                            'description' => substr(html_entity_decode($extension['description']), 0, 300),
                            'image' => $extension['image'],
                            'image_alt' => $extension['image_alt'],
                            'installed' => ($status != null ? true : false),
                            'type' => $extension['type'],
                            'status' => $status,
                            'special_rate'=>$extension['special_rate'],
                            'id' => $extension['sm_id'],
                            'supported_countries'=>$supported_countries,
                            'price'=>$extension['price']
                        ];
                    }

                }

                if (isset($this->request->get['disabled']) && $this->request->get['disabled'] == 1) {

                    $this->data['disabled'] = $this->request->get['disabled'];
                    if (is_null($status) == false && $status == 0) {
                        $shippingMethods[$key] = [
                            'code' => $extension['code'],
                            'title' => $extension['title'],
                            'description' => substr(html_entity_decode($extension['description']), 0, 300),
                            'image' => $extension['image'],
                            'image_alt' => $extension['image_alt'],
                            'installed' => ($status != null ? true : false),
                            'type' => $extension['type'],
                            'status' => $status,
                            'special_rate'=>$extension['special_rate'],
                            'id' => $extension['sm_id'],
                            'supported_countries'=>$supported_countries,
                            'price'=>$extension['price']
                        ];
                    }

                }

                if (isset($this->request->get['installed']) && $this->request->get['installed'] == 1) {

                    $this->data['installed'] = $this->request->get['installed'];

                    if (is_null($status) == false) {
                        $shippingMethods[$key] = [
                            'code' => $extension['code'],
                            'title' => $extension['title'],
                            'description' => substr(html_entity_decode($extension['description']), 0, 300),
                            'image' => $extension['image'],
                            'image_alt' => $extension['image_alt'],
                            'installed' => ($status != null ? true : false),
                            'type' => $extension['type'],
                            'status' => $status,
                            'special_rate'=>$extension['special_rate'],
                            'id' => $extension['sm_id'],
                            'supported_countries'=>$supported_countries,
                            'price'=>$extension['price']
                        ];
                    }

                }

            } else {
                $shippingMethods[$key] = [
                    'code' => $extension['code'],
                    'title' => $extension['title'],
                    'description' => substr(html_entity_decode($extension['description']), 0, 300),
                    'image' => $extension['image'],
                    'image_alt' => $extension['image_alt'],
                    'installed' => ($status != null ? true : false),
                    'type' => $extension['type'],
                    'status' => $status,
                    'special_rate'=>$extension['special_rate'],
                    'id' => $extension['sm_id'],
                    'supported_countries'=>$supported_countries,
                    'price'=>$extension['price']
                ];
            }
        }

        //$shippingMethodsCount = count($shippingMethods);

        $sort_order=array();
        foreach ($shippingMethods as $key => $value) {
            $sort_order['installed'][$key] = $value['installed'];
            $sort_order['special_rate'][$key] = $value['special_rate'];
        }
        array_multisort($sort_order['installed'], SORT_DESC, $sort_order['special_rate'], SORT_DESC, $shippingMethods);

//        $this->data['shipping_methods'] = array_slice($shippingMethods, $offset, $perPage);

        $this->data['shipping_methods'] = $shippingMethods;

        $get = $this->request->get;
        unset($get['page']);
        unset($get['ajaxish']);

//        $pagination = new Pagination();
//        $pagination->total = $shippingMethodsCount;//$shippingMethods['totalFiltered'];
//        $pagination->page = $perPage;
//        $pagination->limit = $shippingMethodsCount;
//        $pagination->text = $this->language->get('text_pagination');
//        $pagination->url = $this->url->link('extension/shipping', http_build_query($get) . '&page={page}', 'SSL');
//
//        $this->data['pagination'] = $pagination->render();

        if ( isset( $this->request->get['ajaxish'] ) )
        {
            $this->template = 'extension/shipping_methods_snippet.expand';
        }
        else
        {
            $this->template = 'extension/shipping.expand';
            $this->children = array(
                'common/header',
                'common/footer'
            );
        }

        $this->response->setOutput($this->render_ecwig());
    }

    public function install()
    {
        $this->language->load('extension/shipping');

        if (!$this->user->hasPermission('modify', 'extension/shipping')) {
            $this->session->data['error'] = $this->language->get('error_permission');

            $this->redirect($this->url->link('extension/shipping', '', 'SSL'));
        } else {
            $this->load->model('setting/extension');
            $this->initializer(['extensionShipping' => 'extension/shipping']);

            if ($this->extensionShipping->isInstalled($this->request->get['extension'])) {
                $this->redirect(
                    $this->url->link(
                        'extension/shipping/activate',
                        'code=' . $this->request->get['extension'].
                        '&activated=0&delivery_company='.$this->request->get['delivery_company'], 'SSL')
                );
            }

            $this->model_setting_extension->install('shipping', $this->request->get['extension']);

            $this->load->model('user/user_group');

            $this->model_user_user_group->addPermission(
                $this->user->getId(),
                'access', 'shipping/' . $this->request->get['extension']
            );
            $this->model_user_user_group->addPermission(
                $this->user->getId(),
                'modify', 'shipping/' . $this->request->get['extension']
            );

            require_once(DIR_APPLICATION . 'controller/shipping/' . $this->request->get['extension'] . '.php');

            $class = 'ControllerShipping' . str_replace('_', '', $this->request->get['extension']);
            $class = new $class($this->registry);

            if (method_exists($class, 'install')) {
                $class->install();
            }

            $this->redirect(
                $this->url->link(
                    'extension/shipping/activate',
                    'code=' . $this->request->get['extension'].
                    '&activated=0&delivery_company='.$this->request->get['delivery_company'], 'SSL')
            );
        }
    }

    public function deactivate()
    {
        $this->language->load('extension/shipping');

        if (!$this->user->hasPermission('modify', 'extension/shipping')) {
            $this->session->data['error'] = $this->language->get('error_permission');

            $this->redirect($this->url->link('extension/shipping', '', 'SSL'));
        } else {
            $this->load->model('setting/setting');

            $modernShipping = $this->config->get($this->request->get['psid']);

            if ($modernShipping) {
                $this->load->model('extension/shipping');

                $this->model_extension_shipping->deleteSettings($this->request->get['psid']);

                require_once(DIR_APPLICATION . 'controller/shipping/' . $this->request->get['psid'] . '.php');

                $class = 'ControllerShipping' . str_replace('_', '', $this->request->get['psid']);
                $class = new $class($this->registry);

                if (method_exists($class, 'uninstall')) {
                    $class->uninstall();
                }

            } else {
                $this->model_setting_setting->deleteSetting($this->request->get['psid']);
            }

            $this->load->model('setting/extension');
            $this->model_setting_extension->uninstall('shipping', $this->request->get['psid']);

            $this->session->data['successDe'] = $this->language->get('entry_deactivate_success');

            $this->redirect($this->url->link('extension/shipping', '', 'SSL'));
        }
    }

    public function disable()
    {
        $data = array();

        $this->language->load('extension/shipping');

        if (!$this->user->hasPermission('modify', 'extension/shipping')) {
            $data['error'] = $this->language->get('error_permission');
        } else {
            $this->load->model('setting/setting');

            $modernShipping = $this->config->get($this->request->get['psid']);

            if ($modernShipping) {
                $modernShipping['status'] = "0";
                $this->model_setting_setting->insertUpdateSetting(
                    'shipping', [$this->request->get['psid'] => $modernShipping]
                );
            } else {
                $this->model_setting_setting->editSettingValue(
                    $this->request->get['psid'],
                    $this->request->get['psid'] . '_status', '0'
                );
            }

            $data['success'] = "true";
        }

        $this->response->setOutput(json_encode($data));
    }

    public function enable()
    {
        $data = array();

        $this->language->load('extension/shipping');

        if (!$this->user->hasPermission('modify', 'extension/shipping')) {
            $data['error'] = $this->language->get('error_permission');
        } else {
            $this->load->model('setting/setting');

            $modernShipping = $this->config->get($this->request->get['psid']);

            if ($modernShipping) {
                $modernShipping['status'] = "1";
                $this->model_setting_setting->insertUpdateSetting(
                    'shipping', [$this->request->get['psid'] => $modernShipping]
                );
            } else {
                $this->model_setting_setting->editSettingValue(
                    $this->request->get['psid'],
                    $this->request->get['psid'] . '_status', '1'
                );
            }

            $data['success'] = "true";
        }

        $this->response->setOutput(json_encode($data));
    }

    public function uninstall()
    {
        $this->language->load('extension/shipping');

        if (!$this->user->hasPermission('modify', 'extension/shipping')) {
            $this->session->data['error'] = $this->language->get('error_permission');

            $this->redirect($this->url->link('extension/shipping', '', 'SSL'));
        } else {
            $this->load->model('setting/extension');
            $this->load->model('setting/setting');

            $this->model_setting_extension->uninstall('shipping', $this->request->get['extension']);

            $this->model_setting_setting->deleteSetting($this->request->get['extension']);

            require_once(DIR_APPLICATION . 'controller/shipping/' . $this->request->get['extension'] . '.php');

            $class = 'ControllerShipping' . str_replace('_', '', $this->request->get['extension']);
            $class = new $class($this->registry);

            if (method_exists($class, 'uninstall')) {
                $class->uninstall();
            }

            $this->redirect($this->url->link('extension/shipping', '', 'SSL'));
        }
    }

    public function activate() {

        //$this->request->get['page']
        if (!isset($this->request->get['code']) ||empty($this->request->get['code'])){
            return;
        }
        $this->load->language('shipping/'.$this->request->get['code']);
        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('extension/shipping', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'    => !isset($this->request->get['activated']) ?
                $this->language->get('breadcrumb_insert') :
                $this->language->get('breadcrumb_update'),
            'href'      =>"",
            'separator' => ' :: '
        );

        $this->getForm();
    }

    public function getForm()
    {
        $code = $this->request->get['code'];
        if (count($this->request->post) > 0) {
            $this->data = array_merge($this->data, $this->request->post);
        }

        $this->data['cancel'] = $this->url->link('extension/shipping', '', 'SSL');

        $this->load->model('setting/store');

        $this->data['stores'] = $this->model_setting_store->getStores();

        //getShippingMethodData

        $this->load->model('extension/shipping');

        $shipping_method_data = $this->model_extension_shipping->getShippingMethodData($code);
        $shipping_method_data['supported_countries'] = $this->getCountries($shipping_method_data['supported_countries']);
        $shipping_method_data['description']=html_entity_decode($shipping_method_data['description']);
        $shipping_method_data['account_creation_steps']=html_entity_decode($shipping_method_data['account_creation_steps']);
        $shipping_method_data['company_requirements']= html_entity_decode($shipping_method_data['company_requirements']);
        $shipping_method_data['individual_requirements']= html_entity_decode($shipping_method_data['individual_requirements']);
        $this->data['shipping_method_data'] = $shipping_method_data;

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['store_url'] = HTTPS_CATALOG;
        } else {
            $this->data['store_url'] = HTTP_CATALOG;
        }

        $settingGroup = $this->config->get($code);
        $status = null;
        if ($settingGroup && is_array($settingGroup) === true) {
            $status = $settingGroup['status'];
        } else {
            $status = $this->config->get($code . '_status');
        }

        // installed = 0 or 1

        $activated= 0;
        if (! is_null($status) || $this->request->get['activated']){
            $activated= 1;
        }

        $this->data['activated'] = $activated;

        // delivery_company = 0 or 1

        $this->data['delivery_company'] = $this->request->get['delivery_company'];

        // link ex : http://qaz123.expandcart.com/admin/extension/shipping/activate?activated=1&delivery_company=0&code=pickup

        try {
            $this->data['shipping_form_inputs'] = $this->getChild('shipping/' . $code);
        }
        catch (\ExpandCart\Foundation\Exceptions\FileException $e) {
            $this->redirect($this->url->link('extension/shipping', '', 'SSL'));
        }

        $this->template = 'extension/shipping_form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    /**
     *   Get all activated shipping methods.
     *
     *   @return array $extensions.
     */
    private function get_activated_shipping_methods()
    {
        $this->load->model('setting/extension');
        $extensions = $this->model_setting_extension->getInstalled('shipping');

        foreach ($extensions as $key => $value) {
            // determine if the extension is installed or not.
            // same check is used on `admin/controller/extension/payment.php:112`.

            $settings = $this->config->get($value);
            if ($settings && is_array($settings) == true) {
                $status = $settings['status'];
            } else {
                $status = $this->config->get($value . '_status');
            }

            if (!$status) {
                unset($extensions[$key]);
            }
        }

        return $extensions;
    }
}
