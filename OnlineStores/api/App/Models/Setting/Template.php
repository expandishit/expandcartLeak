<?php

namespace Api\Models\Setting;

use Psr\Container\ContainerInterface;

class Template
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
     * Template constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->registry = $container['registry'];
        $this->config = $container['config'];
        $this->load = $container['loader'];
        $this->languagecodes = $container['languagecodes'];
        $this->languageids = $container['languageids'];
    }

    /**
     * @return array
     */
    public function categories()
    {
        $this->load->language->load('setting/setting');
        $categories = [
            'general',
            'fashion',
            'business',
            'electronics',
            'sports',
            'nature'
        ];
        $response = array();
        foreach ($categories as $key => $category){
            $response[$key]['locale'] = $this->load->language->get('label_' . $category);
            $response[$key]['value'] = $category;
        }
        return $response;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function getTemplates($filter)
    {
        $this->load->model('setting/setting');
        $storeData = $this->registry->get('model_setting_setting')->getSetting('config');
        $this->load->model('setting/template');
        $templates = $this->registry->get('model_setting_template')->getTemplates($filter, $storeData['config_admin_language']);
        return $templates;
    }

    /**
     * @return mixed
     */
    public function getInstalledExternalTemplates()
    {
        $this->load->model('setting/template');
        $templates = $this->registry->get('model_setting_template')->getInstalledExternalTemplates();
        return $templates;
    }

    /**
     * @return mixed
     */
    public function getExternalTemplates($filter)
    {
        $this->load->model('setting/setting');
        $storeData = $this->registry->get('model_setting_setting')->getSetting('config');
        $this->load->model('templates/external');
        $templates = $this->registry->get('model_templates_external')->getTemplates($filter, $storeData['config_admin_language']);
        return $templates;
    }

    /**
     * @return mixed
     */
    public function getStoreTemplate()
    {
        $this->load->model('setting/setting');
        $storeData = $this->registry->get('model_setting_setting')->getSetting('config');
        $data['name'] = $storeData['config_template'];
        $data['version'] = 2;
        return $data;
    }

    /**
     * @param $code
     * @return mixed
     */
    public function checkTemplateExistence($code)
    {
        $this->load->model('setting/template');
        $template = $this->registry->get('model_setting_template')->getTemplateInfo($code);
        return $template;
    }

    /**
     * @param $code
     * @return bool
     */
    public function apply($code)
    {
        $this->load->model('templates/template');
        $template = $this->registry->get('model_templates_template')->getTemplateByConfigName($code);
        // If template is not custom
        if ($template['custom_template'] == 0)
        {
            $this->load->model('setting/setting');
            $this->registry->get('model_setting_setting')->changeTemplate($code);
        }
        elseif($template['custom_template'] == 1)
        {
            $this->registry->get('model_templates_template')->applyTemplate($template);
        }
        return true;
    }

}
