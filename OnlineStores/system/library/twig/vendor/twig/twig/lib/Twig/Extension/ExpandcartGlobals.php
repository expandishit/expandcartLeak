<?php
class Twig_Extension_ExpandcartGlobals extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    protected $registry;
    protected $is_admin;
    protected $_globals;

    public function __construct(\Registry $registry) {
        global $categoriesData;
        $this->registry = $registry;
        $this->is_admin = defined('DIR_CATALOG');

        $document = $this->registry->get('document');
        $session = $this->registry->get('session');
        $expandish = $this->registry->get('expandish');
        $load = $this->registry->get('load');
        $url = $this->registry->get('url');
        $db = $this->registry->get('db');
        $queryMultiseller = $db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
        $IsMultiSellerInstalled = ($queryMultiseller->num_rows > 0);
        //        if($queryMultiseller->num_rows) {
//             = true;
//        } else {
//
//        }
        $request = array();
        $request['get'] = $_GET;
        $request['post'] = $_POST;
        
        if($this->registry->get('request')->get['__p'] != "")
            $template_name = $this->registry->get('request')->get['__p'];
        else
            $template_name = CURRENT_TEMPLATE;
        //$core_data = $this->registry->get('core_data');
        $globals = array(
            'document_title' => $document->getTitle(),
            'document_description' => strip_tags($document->getDescription()),
            'document_keywords' => $document->getKeywords(),
            'document_links' => $document->getLinks(),
            'document_styles' => $document->getStyles(),
            'document_scripts' => $document->getScripts(),
            'inline_scripts' => $document->getInlineScripts(),
            'ChildData' => $document->getChildData(),
            'session_data' => $session->data,
            'HTTP_SERVER' => HTTP_SERVER,
            'route' => isset($this->registry->get('request')->get['route']) ? $this->registry->get('request')->get['route'] : 'common/home',
            'is_multiseller_installed' => $IsMultiSellerInstalled,
            'Template_Name' => $template_name,
            'PackageId' => PRODUCTID,
            // '_customTemplate' => './expandish/view/custom/' . STORECODE . '/' . $template_name,
            '_customTemplate' => './' . CUSTOM_TEMPLATE_PATH . '/' . $template_name,
            'request' => $request,
            'expandish' => $expandish,
            'uses_twig_extends' => USES_TWIG_EXTENDS,
            'request_url' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            'BuildNumber' => BuildNumber
            //'header_data' => $expandish->getHeader(),
            //'footer_data' => $expandish->getFooter()
        );

        if(isset($expandish) && $expandish->getRoute() != 'common/style') {
            $globals['header_data'] = $expandish->getHeader();
            $globals['footer_data'] = $expandish->getFooter();

            /*if(is_array($core_data)) {
                $globals = array_merge($globals, $core_data);
            }*/

            if ($this->is_admin) {
                $user = $this->registry->get('user');
                $globals['user'] = $user;
                $globals['is_logged'] = $user->isLogged();
            } else {
                $customer = $this->registry->get('customer');
                $globals['customer'] = $customer;
                $globals['is_logged'] = $customer->isLogged();
                $affiliate = $this->registry->get('affiliate');
                $globals['affiliate'] = $affiliate;
                $globals['is_affiliate_logged'] = $affiliate->isLogged();
                $globals['cart'] = $this->registry->get('cart');
            }

            $globals['categories'] = $categoriesData;
        }

        //#########################template settings############################
        $load->model('extension/section', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $page_codename = 'templatesettings';
        $template_codename = $template_name;
        $page_route = '';

        $settingspage = $this->registry->get('model_extension_section')->getPage($template_codename, $page_codename, $page_route);

        $settingssections = $this->registry->get('model_extension_section')->getRegionSection($settingspage['page_id']);
        $templatesettings = array();
        foreach ($settingssections as $section) {
            $region_codename = $section['region_codename'];
            $section_codename = $section['section_codename'];
            $section_id = $section['section_id'];
            if(!isset($templatesettings[$section_codename])) {
                $templatesettings[$section_codename] = array();
            }

            if ($region_codename == 'styling') {
                $fields = $this->registry->get('model_extension_section')->getCollections($section_id, $this->registry->get('config')->get('config_language'));
                foreach($fields as $field) {
                    $templatesettings[$section_codename][$field['field_codename']] = $field['field_value'];
                    //$this->data[$field['field_codename']] = $field['field_value'];
                }
            }
        }
        $globals['templatesettings'] = $templatesettings;
        //#########################template settings############################

        $this->_globals = $globals;
    }

    public function getGlobals()
    {
        return $this->_globals;
    }

    // ...
}