<?php

class ControllerModuleAliexpressDropshipping extends Controller
{
    public function index()
    {
        $this->language->load('module/aliexpress_dropshipping');

        $this->load->model('sale/order');

        $this->document->setTitle($this->language->get('aliexpress_dropshipping_heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/aliexpress_dropshipping', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->initializer([
            'module/aliexpress_dropshipping/settings',
        ]);

        $this->load->model('localisation/weight_class');

        $data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

        $this->load->model('user/user_group');
        $data['userGroups'] = $this->model_user_user_group->getUserGroups();

        $data['productTabs'] = array(
            'links',
            'attribute',
            'option',
            'recurring',
            'discount',
            'special',
            'reward',
            'design',
        );

        $configs = array(
            'module_wk_dropship_status',
            // general
            'wk_dropship_user_group',
            'wk_dropship_front_end_contact',
            'wk_dropship_price_rule_priority',
            'wk_dropship_price_calculation',
            'wk_dropship_complete_order_status',
            'wk_dropship_google_api_key',
            // product
            'wk_dropship_warehouse_manager_add_product',
            'wk_dropship_direct_to_store',
            'wk_dropship_product_mass_upload',
            'wk_dropship_can_edit_product',
            'wk_dropship_edited_product_status',
            'wk_dropship_mass_upload_limit',
            'wk_dropship_product_tabs',
            // Aliexpress
            'wk_dropship_aliexpress_username',
            'wk_dropship_aliexpress_token',
            'wk_dropship_aliexpress_quantity',
            'wk_dropship_aliexpress_store',
            'wk_dropship_aliexpress_language',
            'wk_dropship_aliexpress_review_status',
            'wk_dropship_aliexpress_keyword',
            'wk_dropship_aliexpress_price_type',
            'wk_dropship_aliexpress_real_sync',
            'wk_dropship_aliexpress_default_weight',
            'wk_dropship_aliexpress_default_weight_class',
        );

        foreach ($configs as $key => $config) {
            if (isset($this->request->post[$config])) {
                $data[$config] = $this->request->post[$config];
            } else {
                $data[$config] = $this->config->get($config);
            }
        }

        if (
            !is_null($data['wk_dropship_aliexpress_language']) &&
            !is_array($data['wk_dropship_aliexpress_language'])
            ) {
            $data['wk_dropship_aliexpress_language'] = [$data['wk_dropship_aliexpress_language']];
        }

        $data['pricingRulePriority'] = array(
            $this->language->get('text_indi_cate'),
            $this->language->get('text_parent_cate'),
            $this->language->get('text_all_cate'),
        );

        $data['pricingCalculation'] = array(
            $this->language->get('text_all_applied_cate'),
            $this->language->get('text_only_parent_cate'),
            $this->language->get('text_lowest_first_cate'),
            $this->language->get('text_lowest_last_cate'),
        );

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('localisation/currency');
        $data['currencies'] = $this->model_localisation_currency->getCurrencies();

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->load->model('setting/store');
        $data['stores'] = $this->model_setting_store->getStores();

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $this->template = 'module/aliexpress_dropshipping/settings.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateSettings()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            $this->response->setOutput(json_encode(['errors' => true]));

            return;
        }

        $this->initializer([
            'aliexpress' => 'module/aliexpress_dropshipping/settings'
        ]);

        $this->load->model('setting/setting');

        $attributeGroupIdIndex = 'wk_dropship_aliexpress_attribute_group_id';

        $attributeGroupExists = $this->aliexpress->checkAttributeGroupExists(
            $this->config->get($attributeGroupIdIndex)
        );

        if (!isset($this->request->post['wk_dropship_aliexpress_language'])) {
            $this->request->post['wk_dropship_aliexpress_language'] = null;
        }

        $postedLanguages = $this->request->post['wk_dropship_aliexpress_language'];

        if (is_array($postedLanguages) == false && strlen($postedLanguages) > 1) {

            $postedLanguages = [$postedLanguages];

        } elseif (is_array($postedLanguages) || count($postedLanguages) < 1) {

            $this->load->model('localisation/language');
            $languages = $this->model_localisation_language->getLanguages();

            $postedLanguages = array_column($languages, 'language_id');
        }

        if (!$this->config->get($attributeGroupIdIndex) || !$attributeGroupExists) {
            foreach ($postedLanguages as $postedLanguage) {
                $this->request->post[$attributeGroupIdIndex] = $this->aliexpress->addAlliexpressAttrGroup(
                    $postedLanguage
                );
            }
        } else {
            $this->request->post[$attributeGroupIdIndex] = $this->config->get($attributeGroupIdIndex);
        }

        $this->request->post['wk_dropship_aliexpress_store'] = '0';

        $this->model_setting_setting->editSetting('module_wk_dropship', $this->request->post);
        $this->model_setting_setting->editSetting('wk_dropship', $this->request->post);

        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_settings_success');
        $result_json['success'] = '1';

        $this->response->setOutput(json_encode($result_json));

        return;
    }

    public function install()
    {
        $this->initializer(['module/aliexpress_dropshipping/settings']);
        $this->settings->install();
    }

    public function uninstall()
    {
        $this->initializer(['module/aliexpress_dropshipping/settings']);
        $this->settings->uninstall();
    }
}
