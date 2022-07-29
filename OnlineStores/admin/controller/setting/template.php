<?php

class ControllerSettingTemplate extends Controller
{
    private $error = array();

    private $templateWhiteList = array();

    public function __construct($registry) {
        parent::__construct($registry);

        if (PRODUCTID =="3" || PRODUCTID =="2" || PRODUCTID =="53"){
            $this->templateWhiteList =  $this->genericConstants['plans_limits'][PRODUCTID]['templates_whitelist'];
        }

    }

    public function getFilteredTemplates()
    {
        if ( $this->request->server['REQUEST_METHOD'] != 'POST' )
        {
            $this->index();
            return;
        }

        $this->language->load('setting/setting');
        $categories = $this->request->post['categories'] ?: null;

        $page = 1;
        $perPage = 9;
        $offset = 0;

        if ( isset( $this->request->get['page'] ) && $this->request->get['page'] > 1 )
        {
            $page = $this->request->get['page'];
            $offset = $filter['offset'] = ($page - 1) * $perPage;
            $filter['limit'] = $perPage;
        }

        $this->load->model('setting/template');

        $filter['categories'] = $categories;

        $templates = $this->model_setting_template->getTemplates( $filter, $this->language->get('code') );

        foreach ($templates['data'] as $key => $item){
            if(!array_key_exists(strtolower($item['CodeName']),$this->templateWhiteList)){
                if ( (PRODUCTID =="3" || PRODUCTID =="53" || PRODUCTID =="2") && ( $this->config->get('template_version') > 1.2 )){
                    $templates['data'][$key]['disabled']=1;
                }
                $templates['data'][$key]['premium']=1;
            }
        }

        $sort_order=array();
        foreach ($templates['data'] as $key => $value) {
            if(array_key_exists($key, $this->templateWhiteList)){
                $sort_order[$key] = $this->templateWhiteList['$key'];
                continue;
            }
            $sort_order[$key] = $value['disabled'];
        }
        array_multisort($sort_order, SORT_ASC,  $templates['data']);

        $currentTemplate = $this->config->get('config_template');

        $this->data['imageBase'] = HTTP_CATALOG . 'image/templates/';
        $this->data['templates'] = $templates['data'];
        $this->data['config_template'] = $currentTemplate;

        $get = $this->request->get;
        unset($get['page']);

        if ( $templates['total'] > $perPage )
        {
            $pagination = new Pagination();
            $pagination->total = $templates['total'];
            $pagination->page = $page;
            $pagination->limit = $perPage;
            $pagination->text = $this->language->get('text_pagination');
            $pagination->url = $this->url->link('setting/template', http_build_query($get) . '&page={page}', 'SSL');

            $this->data['pagination'] = $pagination->render();
        }

        $this->template = 'setting/template_list_snippet.expand';

        $output = $this->render_ecwig();

        $this->response->setOutput($output);

        return;

    }

    public function index()
    {
        $this->language->load('setting/setting');

        $this->initializer([
            'setting/template',
            'templates/external'
        ]);

        $this->document->setTitle($this->language->get('template_heading_title'));

        $this->data['direction'] = $this->language->get('direction');

        $this->load->model('setting/setting');
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('template_heading_title'),
            'href' => $this->url->link('setting/template', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['config_template'] = $this->config->get('config_template');

        $categories = null;
        if (isset($this->request->get['categories']) && count($this->request->get['categories']) > 0) {
            $categories = $this->data['selectedCategories'] = $this->request->get['categories'];
        }

        $page = 1;
        $perPage = 9999;
        $offset = 0;
        if (isset($this->request->get['page']) && $this->request->get['page'] > 1) {
            $page = $this->request->get['page'];

            $offset = ($page - 1) * $perPage;
        }

        $filter = [
            'categories' => $categories,
            'limit' => $perPage,
            'offset' => $offset
        ];

        $this->data['categories'] = [
            'general',
            'fashion',
            'business',
            'electronics',
            'sports',
            'nature',
            'food'
        ];

        $langauge_code = (in_array($this->language->get('code'),['ar','en'])) ? $this->language->get('code') : 'en';

        $templates = $this->template->getTemplates($filter, $langauge_code);

        $allTemplates = $this->template->getTemplates(null, $langauge_code);

        $installedExternalTemplates = $this->template->getInstalledExternalTemplates();

        $filter['installed'] = array_keys($installedExternalTemplates);

        $externalTemplates = $this->external->getTemplates($filter, $langauge_code);

        foreach ($externalTemplates['data'] as &$externalTemplate) {

            if ($installedExternalTemplate = $installedExternalTemplates[$externalTemplate['id']]) {
                $externalTemplate['internal_template_id'] = $installedExternalTemplate['id'];
                $externalTemplate['internal_theme_version'] = $installedExternalTemplate['theme_version'] ?: '1.0';
            } else {
                $externalTemplate['internal_template_id'] = null;
            }

        }

        $templates['data'] = array_merge($externalTemplates['data'], $templates['data']);
        $templates['total'] += $externalTemplates['total'];
        $allTemplates['data'] = array_merge($externalTemplates['data'], $allTemplates['data']);

        foreach ($templates['data'] as $key => $item){
            if(!array_key_exists(strtolower($item['CodeName']),$this->templateWhiteList)){
                if ( (PRODUCTID =="3" || PRODUCTID =="53" || PRODUCTID =="2") && ($this->config->get('template_version') > 1.2 ) ){
                    $templates['data'][$key]['disabled']=1;
                }
                $templates['data'][$key]['premium']=1;
            }
        }

        $sort_order=array();
        foreach ($templates['data'] as $key => $value) {
            if(array_key_exists($key, $this->templateWhiteList)){
                $sort_order[$key] = $this->templateWhiteList[$key];
                continue;
            }
            $sort_order[$key] = $value['disabled'];
        }

        array_multisort($sort_order, SORT_ASC,  $templates['data']);


        $this->data['currentTemplate'] = $allTemplates['data'][$this->config->get('config_template')];

        $this->data['templates']['nextgen'] = $templates['data'];

        $this->data['imageBase'] = HTTP_CATALOG . 'image/templates/';

        $this->data['externalTemplates'] = [];//$externalTemplates['data'];


        /*$directories = glob(DIR_CATALOG . 'view/theme/*', GLOB_ONLYDIR);*/

        $get = $this->request->get;
        unset($get['page']);

        $pagination = new Pagination();
        $pagination->total = $templates['total'];
        $pagination->page = $page;
        $pagination->limit = $perPage;
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('setting/template', http_build_query($get) . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();

        $this->template = 'setting/template.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'setting/setting')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return true;
    }

    public function applytemplate()
    {
        if (! array_key_exists($this->request->get['basename'],$this->templateWhiteList) &&
            ( PRODUCTID == "3" || PRODUCTID == "53" || PRODUCTID == "2" ) &&
            (  $this->config->get('template_version') > 1.2 )
        ){
            $this->error['warning'] = $this->language->get('not_allowed');
            $data['error'] = $this->error;
            $this->response->setOutput(json_encode($data));
            return;
        }

        $data = array();

        if ($this->validate()) {

            $tmpTemplate = $this->config->get('config_template');

            $this->load->model('setting/setting');
            $this->load->model('setting/template');

            $this->model_setting_setting->changeTemplate($this->request->get['basename']);

            $guide_status = $this->tracking->updateGuideValue("CUST_DESIGN");
            if ($guide_status !=1 ) {
                $data['guidestate'] = 'true';
            }


            //apply template event if not reset to default theme
            if(isset($this->request->get['resetToDefault']) && $this->request->get['resetToDefault'] == "false") {

                $template_info = $this->model_setting_template->getTemplateInfo(CURRENT_TEMPLATE);
                $template_name = isset($template_info['Name']) ? $template_info['Name'] : CURRENT_TEMPLATE;

                /***************** Start ExpandCartTracking #347694  ****************/

                // send mixpanel event apply template
                $this->load->model('setting/mixpanel');
                $this->model_setting_mixpanel->trackEvent('Apply Template', ['Template Name' => $template_name]);
                $this->model_setting_mixpanel->updateUser([
                    '$current template' => $template_name,
                ]);

                // send amplitude event apply theme
                $this->load->model('setting/amplitude');
                $this->model_setting_amplitude->trackEvent('Apply Template', ['Template Name' => $template_name]);
                $this->model_setting_amplitude->updateUser([
                    'current template' => $template_name,
                ]);
                /***************** End ExpandCartTracking #347694  ****************/
            }

            $data['success'] = "true";
            $data['refresh'] = '1';
            $data['previous'] = [
                'previewlink' => $this->url->link('templates/preview', 't=' . $tmpTemplate)->format()
            ];
        } else {
            $data['error'] = $this->error;
        }

        $this->response->setOutput(json_encode($data));
    }
}
