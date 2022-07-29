<?php

namespace Api\Http\Controllers\Setting;

use Api\Http\Controllers\Controller;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class TemplateController extends Controller
{
    /**
     * @var
     */
    private $template, $setting;

    /**
     * @var int[]
     */
    private $templateWhiteList = ['wonder' => -4, 'manymore' => -3, 'souq' => -2, 'welldone' => -1];

    /**
     * TemplateController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->template = $this->container['template'];
        $this->setting = $this->container['setting'];
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function index(Request $request, Response $response, $args)
    {
        $parameters = $request->getParsedBody();
        $filter['categories'] = $parameters['categories'];

        $installedExternalTemplates = $this->template->getInstalledExternalTemplates();

        $filter['installed'] = array_keys($installedExternalTemplates);

        $templates = $this->template->getTemplates($filter);

        $allTemplates = $this->template->getTemplates(null);

        $externalTemplates = $this->template->getExternalTemplates($filter);

        $categories = $this->template->categories();

        $current_template = $this->template->getStoreTemplate(); // Current template info

        $image_base = HTTP_CATALOG . 'image/templates/'; // Base url of Template

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

        // Check if template is premium
        foreach ($templates['data'] as $key => $item){
            if ($item['custom_template'])
                $templateimage = HTTP_CATALOG . str_replace("../", "", $item['Image']);
            else
                $templateimage = $image_base . $item['CodeName'] . '.jpg';
            $templates['data'][$key]['Image'] = $templateimage;
            if(!array_key_exists(strtolower($item['CodeName']),$this->templateWhiteList)){
                if ( PRODUCTID == '3' || PRODUCTID =="2" && ( $current_template['version'] > 1.1 )){
                    $templates['data'][$key]['disabled'] = 1;
                }
                $templates['data'][$key]['premium']=1;
            }
        }

        // Sort Templates
        $sort_order=array();
        foreach ($templates['data'] as $key => $value) {
            if(array_key_exists($key, $this->templateWhiteList)){
                $sort_order[$key] = $this->templateWhiteList[$key];
                continue;
            }
            $sort_order[$key] = $value['disabled'];
        }

        array_multisort($sort_order, SORT_ASC,  $templates['data']);

        // Get free templates
        $premium_templates = array();
        $free_templates = array();
        foreach ($templates['data'] as $template)
        {
            if (isset($template['premium']) && $template['premium'] == 1)
            {
                $premium_templates[] = $template;
            }
            else
            {
                $free_templates[] = $template;
            }
        }

        return $response->withJson([
            'status' => $this->status[1],'data' =>[
                'current_template' => $current_template['name'],
                'categories' => $categories,
                'free_templates' => $free_templates,
                'premium_templates' => $premium_templates,
                //'templates' => $templates['data']
            ]
        ], 200);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function apply(Request $request, Response $response, $args)
    {
        $parameters = $request->getParsedBody();
        $current_template = $this->template->getStoreTemplate();

        if (!isset($parameters['template']) || empty($parameters['template']))
        {
            return $response->withJson([
                'status' => 'validation error','message' => 'template name is required'
            ], 406);
        }

        // Validate store subscription
        if (! array_key_exists($parameters['template'] ,$this->templateWhiteList) &&
            ( PRODUCTID == "3" || PRODUCTID == "2" ) &&
            (  $current_template['version'] > 1.1 )
        ){
            return $response->withJson([
                'status' => $this->status[1],'message' => 'Not allowed to apply this template'
            ], 403);
        }

        // Validate Template existence
        if (!$this->template->checkTemplateExistence($parameters['template']))
        {
            return $response->withJson([
                'status' => 'not found','message' => 'template not found'
            ], 407);
        }

        // Apply template
        if ($this->template->apply($parameters['template']))
        {
            $guide = $this->setting->getGuideValue('GETTING_STARTED');
            if (!isset($guide['CUST_DESIGN']) || $guide['CUST_DESIGN'] !=1){
                $this->setting->editGuideValue('GETTING_STARTED', 'CUST_DESIGN', '1');
                $this->setting->editGuideValue('ASSISTANT', 'CUST_DESIGN', '1');
            }
            return $response->withJson([
                'status' => 'success','message' => 'Template applied successfully', 'template' => $parameters['template']
            ], 200);
        }

        return $response->withJson([
            'status' => 'failed','message' => 'failed to apply template'
        ], 408);
    }
    
    /**
     * Force update custom template API
     *
     * @param Request $request
     * @param Response $response
     * @param [type] $args
     * @return void
     */
    public function forceUpdateTemplate(Request $request, Response $response, $args)
    {
        $parameters = $request->getParsedBody();
        
        $url = (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) ? 'https://' : 'http://';
        $url.= rtrim(DOMAINNAME) . '/admin/templates/update';
        
        $curlClient = $this->container['registry']->get('curl_client');
        
        $updateResponse = $curlClient->request('POST', $url, [], ['template_id' => $parameters['template_id'], 'force_update' => 1,]);
        $updateResponseContent = $updateResponse->getContent();
        
        if ($updateResponse->ok()) {
            $data = $updateResponseContent;
        } else {
            $data = ['status' => 'ERR', 'error' => 'UNKNOWN_ERROR'];
        }
        
        return $response->withJson($data, 200);
        
    }
}
