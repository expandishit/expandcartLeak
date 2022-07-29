<?php

namespace Api\Models;

use Psr\Container\ContainerInterface;

class Installation extends ParentModel
{
    /**
     * @var
     */
    private $load;
    /**
     * @var
     */
    private $registry;
    /**
     * @var
     */
    private $languagecodes;
    /**
     * @var
     */
    private $languageids;
    /**
     * @var
     */
    private $config;
    /**
     * @var
     */
    private $db;

    /**
     * Installation constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->registry = $container['registry'];
        $this->config = $container['config'];
        $this->load = $container['loader'];
        $this->languagecodes = $container['languagecodes'];
        $this->languageids = $container['languageids'];
        $this->db = $container['db'];
    }

    /**
     * @return array
     */
    public function getInstallationInfo()
    {
        $response = null;
        $this->load->language->load('common/installation');
        $selling_channels_data = [
            'Social_Media'          =>'Social Media',
            'Marketplaces'          =>'Marketplaces',
            'Retail_Store'          =>'Retail Store',
            'Website'               =>'Website',
            'All_Channels'          =>'All Channels',
            'Not_Selling'           =>'Not Selling',
            'Building_for_Client'   =>'Building for Client',
            'Research_Purposes'     =>'Not Selling',
        ];

        $product_source_data = [
            'Dropshipping'          =>'Dropshipping',
            'Own_Products'          =>'Own Products',
            'Retail'                =>'Retail',
            'Multi_Merchant'        =>'Multi Merchant',
            'Do_Not_Know'           =>'Do Not Know'
        ];

        $product_types_data = [
            'food_drink'            =>'food & drink',
            'clothes_shoes'         =>'clothes & shoes',
            'health_beauty'         =>'health & beauty',
            'tech_products'         =>'Tech Products',
            'Kids_and_baby'         =>'Kids & baby',
            'Home_living'           =>'Home & living',
            'Classes_events'        =>'Classes and events',
            'Arts_crafts'           =>'Arts & Crafts',
            'Jewelry_accessories'   =>'Jewelry & accessories',
            'Fitness_wellness'      =>'Fitness & wellness',
            'Sport'                 =>'Sport',
            'Pet_supplies'          =>'Pet supplies',
            'others'                =>'others'
        ];

        foreach ($selling_channels_data as $key => $value){
            $response['selling_channel'][$key]['locale'] = $this->load->language->get('text_channel_' . $key);
            $response['selling_channel'][$key]['value'] = $value;
        }

        foreach ($product_source_data as $key => $value){
            $response['product_source'][$key]['locale'] = $this->load->language->get('text_channel_' . $key);
            $response['product_source'][$key]['value'] = $value;
        }

        foreach ($product_types_data as $key => $value){
            $response['product_types'][$key]['locale'] = $this->load->language->get('text_storeName_' . $key);
            $response['product_types'][$key]['value'] = $key;
        }

        return $response;

    }

    /**
     * @param $data
     * @return bool
     */
    public function store($data)
    {
        $this->load->model('setting/setting');
        $this->registry->get('model_setting_setting')->insertUpdateSetting('config', $data);
        $this->registry->get('model_setting_setting')->editGuideValue("SIGNUP", "QUESTIONER", "1");
        return true;
    }

    /**
     * @param $data
     * @return bool
     */
    public function updateUserMixpanel($data)
    {
        $this->load->model('setting/mixpanel');
        $this->registry->get('model_setting_mixpanel')->trackEvent('Complete Onboard');
        if ($this->registry->get('model_setting_mixpanel')->updateUser($data))
            return true;
        return false;
    }

    /**
     * @param $data
     * @return bool
     */
    public function updateUserAmplitude($data)
    {
        $this->load->model('setting/amplitude');
        $this->registry->get('model_setting_amplitude')->trackEvent('Complete Onboard');
        if ($this->registry->get('model_setting_amplitude')->updateUser($data))
            return true;
        return false;
    }

    /**
     *
     */
    public function applyDefaultTemplate()
    {
        $this->load->model('templates/template');
        $codeName = $this->config->get('config_template');
        $template = $this->registry->get('model_templates_template')->getTemplateByConfigName($codeName);
        if($template['external_template_id']){
            if (file_exists(DIR_CUSTOM_TEMPLATE) == false || is_writable(DIR_CUSTOM_TEMPLATE) == false) {
                mkdir(DIR_CUSTOM_TEMPLATE, 0777);
                chmod(DIR_CUSTOM_TEMPLATE, 0777);
            }
            $theme = EXTERNAL_THEMES_PATH . $codeName . '.zip';
            $this->load->model('templates/archive');
            $this->registry->get('model_templates_archive')->open($theme);
            $this->registry->get('model_templates_archive')->extract(DIR_CUSTOM_TEMPLATE)->close();
            rename(DIR_CUSTOM_TEMPLATE . $codeName . '/schema.json', DIR_CUSTOM_TEMPLATE . $codeName . '/' . $codeName .'.json');
            $this->registry->get('model_templates_template')->applyTemplate($template);
        }
        else
        {
            $this->load->model('setting/setting');
            $this->registry->get('model_setting_setting')->changeTemplate($codeName);
        }
    }

    public function gettingStartedInfo()
    {
        $this->load->model('setting/setting');
        $getting_started =  $this->registry->get('model_setting_setting')->getGuideValue("GETTING_STARTED");
        if ($getting_started['ADD_DOMAIN'] != 1 ){
            $this->load->model('setting/domainsetting');
            $domain_count = $this->registry->get('model_setting_domainsetting')->countDomain();
            if ($domain_count > 0){
                $this->registry->get('model_setting_setting')->editGuideValue('GETTING_STARTED', 'ADD_DOMAIN', '1');
                $getting_started['ADD_DOMAIN'] = 1;
            }
        }
        return $getting_started;
    }
}