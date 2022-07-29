<?php

class ControllerModuleProductDesigner extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('module/product_designer');
        $this->load->language('marketplace/app');

        $this->document->setTitle($this->language->get('pd_heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->model_setting_setting->editSetting('product_designer', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/home', '', 'SSL'));
        }

        $data['token'] = null;

        /*Add Directory*/
        // TODO
        // $direc = DIR_CATALOG . 'view/product_designer_theme/';
        $direc = ONLINE_STORES_PATH . 'OnlineStores/image/'; //substr(DIR_IMAGE, 0, (-strlen(STORECODE)-1));
        $direc = $direc . 'modules/product_designer/';
        $data['themesDir'] = HTTP_CATALOG . 'image/modules/product_designer/';
        $dh = opendir($direc);
        $data['files'][] = array(
            'tempname' => '--select--',
        );

        while (false !== ($filename = readdir($dh))) {
            if($filename !='.' && $filename !='..'){
                $data['files'][] = array(
                    'tempname' => $filename,
                );
            }
        }

        $data['font_coll'] = array();
        if ($handle = opendir(DIR_CATALOG . 'view/theme/' . $this->getThemeName() . '/template/designit/font')) {
            while (false !== ($entry = readdir($handle))) {
                if(isset($entry) && $entry !='.' && $entry !='..'){
                    $font_name = explode('.',$entry);
                    if(isset($font_name)){
                        $data['font_coll'][] = array(
                            'font'        => $font_name[0],
                            'f_name'    => $entry
                        );
                    }
                }
            }
            closedir($handle);
        }

        /********************************************/

         if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['image'])) {
            $data['error_image'] = $this->error['image'];
        } else {
            $data['error_image'] = array();
        }

        $this->load->model('module/product_designer/settings');
        $settings = $this->model_module_product_designer_settings;

        $data['breadcrumbs'] = $settings->genereateBreadcrumbs([
            ['text' => 'text_home', 'href' => 'common/home', 'separator' => false],
            ['text' => 'text_module', 'href' => 'marketplace/home', 'separator' => ' :: '],
            ['text' => 'pd_heading_title', 'href' => 'module/product_designer', 'separator' => ' :: '],
            ['text' => 'pd_settings', 'href' => 'module/product_designer', 'separator' => ' :: '],
        ]);


        //var_dump($data);
       // die();
        $data['action'] = $this->url->link('module/product_designer/updateSettings', '', 'SSL');
        $data['insert'] = $this->url->link('product_designer/clipart/insert', '', 'SSL');
        $data['action_upgrade'] = $this->url->link('module/product_designer/upgrade', '', 'SSL');
        $data['add_category'] = $this->url->link('product_designer/clipart/addCategory', '', 'SSL');

        $data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');

        $data['settingsLink'] = $this->url->link('product_designer/clipart', '', 'SSL');

        $data['themeBackground'] = '';

        $data['modules'] = array();

        if (isset($this->request->post['tshirt_module'])) {
            $data['modules'] = $this->request->post['tshirt_module'];
        } elseif ($this->config->get('tshirt_module')) {
            $data['modules'] = $this->config->get('tshirt_module');
        }
        $data['tshirt_module']=$settings->getSettings(STORECODE)['tshirt_module'];

        $data['http_catalog'] = HTTP_CATALOG;

        $data['themeBackground'] = $data['themesDir'] . $data['modules']['template'];

        /*Sample image work here***********************************************************************************/
        $this->load->model('tool/image');

        $noImage = $this->model_tool_image->resize('no_image.jpg', 150, 150);

        $data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
        if (isset($data['modules']['image_1'])) {
            $data['img_1'] = $this->model_tool_image->resize($data['modules']['image_1'], 150, 150);
            $data['img_hdn_1'] = $data['modules']['image_1'];
        } else {
            $data['img_1'] = $noImage;
            $data['img_hdn_1'] = "no_image.jpg";
        }

        if (isset($data['modules']['image_2'])) {
            $data['img_2'] = $this->model_tool_image->resize($data['modules']['image_2'], 150, 150);
            $data['img_hdn_2'] = $data['modules']['image_2'];
        } else {
            $data['img_2'] = $noImage;
            $data['img_hdn_2'] = "no_image.jpg";
        }

        if (isset($data['modules']['image_3'])) {
            $data['img_3'] = $this->model_tool_image->resize($data['modules']['image_3'], 150, 150);
            $data['img_hdn_3'] = $data['modules']['image_3'];
        } else {
            $data['img_3'] = $noImage;
            $data['img_hdn_3'] = "no_image.jpg";
        }

        if (isset($data['modules']['image_4'])) {
            $data['img_4'] = $this->model_tool_image->resize($data['modules']['image_4'], 150, 150);
            $data['img_hdn_4'] = $data['modules']['image_4'];
        } else {
            $data['img_4'] = $noImage;
            $data['img_hdn_4'] = "no_image.jpg";
        }

        if (isset($data['modules']['image_5'])) {
            $data['img_5'] = $this->model_tool_image->resize($data['modules']['image_5'], 150, 150);
            $data['img_hdn_5'] = $data['modules']['image_5'];
        } else {
            $data['img_5'] = $noImage;
            $data['img_hdn_5'] = "no_image.jpg";
        }

        if (isset($data['modules']['image_6'])) {
            $data['img_6'] = $this->model_tool_image->resize($data['modules']['image_6'], 150, 150);
            $data['img_hdn_6'] = $data['modules']['image_6'];
        } else {
            $data['img_6'] = $noImage;
            $data['img_hdn_6'] = "no_image.jpg";
        }


        /*****************************************************************************************************************/


        $this->load->model('design/layout');

        $data['layouts'] = $this->model_design_layout->getLayouts();


        // $data['header'] = $this->load->controller('common/header');
        // $data['column_left'] = $this->load->controller('common/column_left');
        // $data['footer'] = $this->load->controller('common/footer');

        $this->template = 'module/product_designer/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data = $data;

        //var_dump($this->data);
        //die();
        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            $response['success'] = '0';
            $response['success_msg'] = "";
            $this->response->setOutput(json_encode($response));
            return;
        }

        $this->load->model('module/product_designer/settings');

        $model = $this->model_module_product_designer_settings;

        if (!$model->validateSettings($this->request->post, [])) {
            $response['success'] = '0';
            $response['success_msg'] = $this->error;
            $this->response->setOutput(json_encode($response));
            return;
        }

        $model->updateSettings($this->request->post);

        $this->load->language('module/product_designer');

        $response['success'] = '1';
        $response['success_msg'] = $this->language->get('text_success');
        $this->response->setOutput(json_encode($response));
    }

    public function updateOrderPage()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            return 'error';
        }

        $this->load->model('module/product_designer/settings');

        $model = $this->model_module_product_designer_settings;

        // print_r($_FILES);exit;

        $origImages = $_POST['orig_images'];

        $dids = $_POST['did'];

        $files = array();

        foreach ($dids as $key => $did) {
            $files[$key] = $model->resolveFilesArray(
                $_FILES, $origImages[$did], $did
            );

            $tshirtDesign = $model->getTshirtDesignById($did);

            $_files = $model->uploadFiles($files[$key], $tshirtDesign);

            $model->updateTshirtDesign($_files, $did);
        }
    }

    public function deleteFont()
    {
        if(isset($this->request->get['fname'])){
            $dir = DIR_CATALOG . 'view/theme/' . $this->getThemeName() . '/template/designit/font/';
            if(is_dir($dir)){
                @unlink($dir . $this->request->get['fname']);
                echo $this->buildFontHtml();
            }
            else {
                echo 0;
            }
        }

    }

    public function uploadFont()
    {
        $filename = $_FILES["uploadfile"]["name"];
        $dir = DIR_CATALOG . 'view/theme/' . $this->config->get('config_template') . '/template/designit/font/' . $filename;
        if(move_uploaded_file($_FILES["uploadfile"]["tmp_name"],$dir)){
            echo $this->buildFontHtml();
        }
        else{
            echo 0;
        }

    }

    public function buildFontHtml()
    {
        $shtml = "<table id='module' class='table table-striped table-bordered table-hover'><tr><td><b>Font name</b></td><td><div id='sdel' style='position:absolute;margin-left:50px;display:none;'><b>Please wait.....</b></div></td></tr>";
        if ($handle = opendir(DIR_CATALOG . 'view/theme/' . $this->config->get('config_template') . '/template/designit/font')) {
            while (false !== ($entry = readdir($handle))) {
                if(isset($entry) && $entry !='.' && $entry !='..'){
                    $font_name = explode('.',$entry);
                    if(isset($font_name)){
                        $shtml .= "<tr><td>" . $font_name[0] ."</td><td align='center'><a class='btn btn-danger' title='' data-toggle='tooltip' onclick=deleteFont('".$entry."'); data-original-title='Delete Font'><i class='fa fa-minus-circle'></i></a></td></tr>";
                    }
                }
            }
        closedir($handle);
        }

        return $shtml . '<table>';
    }
    public function getThemeName()
    {
        $theme = 'default';
        $this->load->model('setting/setting');
        $setting_info = $this->model_setting_setting->getSetting('theme_default', 0);
        if(isset($setting_info['theme_default_directory'])){
            $theme = $setting_info['theme_default_directory'];
        }
        return $theme;
    }

    private function init()
    {
        $this->load->model('module/product_designer/settings');

        $this->facade = $this->model_module_product_designer_settings;
    }

    public function install()
    {
        $this->init();

        $this->facade->install();
    }

    public function upgrade()
    {
        $this->init();

        $this->facade->upgrade();
    }

    public function uninstall()
    {
        $this->init();

        $this->facade->uninstall();
    }
}
