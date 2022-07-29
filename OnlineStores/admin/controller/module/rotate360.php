<?php
// -----------------------------
// Social Slides for OpenCart 
// By Best-Byte
// www.best-byte.com
// -----------------------------

class ControllerModuleRotate360 extends Controller
{

    private $_name = 'rotate360';
    private $_version = '1.0';
    private $errors = array();
    private $result_json = array();

    public function index()
    {
        $this->load->model('marketplace/common');
        $market_appservice_status = $this->model_marketplace_common->hasApp('rotate360');
//        if (!$market_appservice_status['hasapp']) {
//            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
//            return;
//        }

        $this->load->language('module/' . $this->_name);

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data[$this->_name . '_version'] = $this->_version;

        $this->load->model('setting/setting');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            if ($this->validate()) {
                $this->model_setting_setting->insertUpdateSetting($this->_name, $this->request->post);
                $this->data['success_message'] = $this->result_json['success_msg'] = $this->language->get('text_success');
                $this->result_json['success'] = '1';
            } else {
                $this->result_json['success'] = '0';
                $this->result_json['error'] = $this->errors;
            }

            $this->response->setOutput(json_encode($this->result_json));
            return;
        }

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/' . $this->_name, '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['action'] = $this->url->link('module/' . $this->_name, '', 'SSL');

        $this->data['cancel'] = $this->url . 'marketplace/home?' . '';


        if (isset($this->request->post[$this->_name . '_enable_module'])) {
            $this->data[$this->_name . '_enable_module'] = $this->request->post[$this->_name . '_enable_module'];
        } else {
            $this->data[$this->_name . '_enable_module'] = $this->config->get($this->_name . '_enable_module');
        }

        if (isset($this->request->post[$this->_name . '_enable_animation'])) {
            $this->data[$this->_name . '_enable_animation'] = $this->request->post[$this->_name . '_enable_animation'];
        } else {
            $this->data[$this->_name . '_enable_animation'] = $this->config->get($this->_name . '_enable_animation');
        }

        if (isset($this->request->post[$this->_name . '_enable_detect_sub_sampling'])) {
            $this->data[$this->_name . '_enable_detect_sub_sampling'] = $this->request->post[$this->_name . '_enable_detect_sub_sampling'];
        } else {
            $this->data[$this->_name . '_enable_detect_sub_sampling'] = $this->config->get($this->_name . '_enable_detect_sub_sampling');
        }

        if (isset($this->request->post[$this->_name . '_enable_loop'])) {
            $this->data[$this->_name . '_enable_loop'] = $this->request->post[$this->_name . '_enable_loop'];
        } else {
            $this->data[$this->_name . '_enable_loop'] = $this->config->get($this->_name . '_enable_loop');
        }

        if (isset($this->request->post[$this->_name . '_frame_number'])) {
            $this->data[$this->_name . '_frame_number'] = $this->request->post[$this->_name . '_frame_number'];
        } else {
            $this->data[$this->_name . '_frame_number'] = $this->config->get($this->_name . '_frame_number');
        }

        if (isset($this->request->post[$this->_name . '_frame_time'])) {
            $this->data[$this->_name . '_frame_time'] = $this->request->post[$this->_name . '_frame_time'];
        } else {
            $this->data[$this->_name . '_frame_time'] = $this->config->get($this->_name . '_frame_time');
        }

        if (isset($this->request->post[$this->_name . '_framesX_number'])) {
            $this->data[$this->_name . '_framesX_number'] = $this->request->post[$this->_name . '_framesX_number'];
        } else {
            $this->data[$this->_name . '_framesX_number'] = $this->config->get($this->_name . '_framesX_number');
        }

        if (isset($this->request->post[$this->_name . '_framesY_number'])) {
            $this->data[$this->_name . '_framesY_number'] = $this->request->post[$this->_name . '_framesY_number'];
        } else {
            $this->data[$this->_name . '_framesY_number'] = $this->config->get($this->_name . '_framesY_number');
        }

        $this->template = 'module/' . $this->_name . '.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'module/' . $this->_name)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function insertImage()
    {
        $image = array();

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (!$this->request->post['product_id']) {
                $this->errors['product_id'] = 'product_id';
            } else {
                $image['product_id'] = $this->request->post['product_id'];
            }

            if (!$this->request->post['image_path']) {
                $this->errors['image_path'] = 'image_path';
            } else {
                $image['image_path'] = $this->request->post['image_path'];
            }

            if (!$this->request->post['image_name']) {
                $this->errors['image_name'] = 'image_name';
            } else {
                $image['image_name'] = $this->request->post['image_name'];
            }

            if (!isset($this->request->post['image_order'])) {
                $this->errors['image_order'] = 'image_order';
            } else {
                $image['image_order'] = $this->request->post['image_order'];
            }

            $this->load->model('module/rotate360');
            $result = $this->model_module_rotate360->insertImage($image);
            if ($result) {
                $this->result_json['success_message'] = 'image uploaded successfully';
                $this->result_json['success'] = '1';
            } else {
                $this->result_json['success'] = '0';
                $this->result_json['error'] = $this->errors;
            }
        } else {
            $this->result_json['success'] = '0';
            $this->result_json['error'] = $this->errors;
        }
        $this->response->setOutput(json_encode($this->result_json));
    }


    function deleteImage()
    {
        $image = array();

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (!$this->request->post['product_id']) {
                $this->errors['product_id'] = 'product_id';
            } else {
                $image['product_id'] = $this->request->post['product_id'];
            }

            if (!$this->request->post['image_path']) {
                $this->errors['image_path'] = 'image_path';
            } else {
                $image['image_path'] = $this->request->post['image_path'];
            }

            if (!$this->request->post['image_name']) {
                $this->errors['image_name'] = 'image_name';
            } else {
                $image['image_name'] = $this->request->post['image_name'];
            }

            $this->load->model('module/rotate360');
            $result = $this->model_module_rotate360->deleteImage($image);
            if ($result) {
                $this->data['success_message'] = $this->result_json['success_msg'] = $this->language->get('text_success');
                $this->result_json['success'] = '1';
            } else {
                $this->result_json['success'] = '0';
                $this->result_json['error'] = $this->errors;
            }
        } else {
            $this->result_json['success'] = '0';
            $this->result_json['error'] = $this->errors;
        }
        $this->response->setOutput(json_encode($this->result_json));
    }

    public function install()
    {
        $this->load->model('module/rotate360');
        $result = $this->model_module_rotate360->install();

    }

    public function uninstall() {
        $this->load->model('module/rotate360');
        $result = $this->model_module_rotate360->uninstall();
        if($result) {
            /*$rotate360Directory = rtrim(
                'image/data/' . str_replace(['../', '..\\', '..'], '', 'products/rotate360/'), '/'
            );
            $this->getChild('common/filemanager/deleteDirectory',$rotate360Directory);*/

            \Filesystem::delete('image/data/products/rotate360/');
        }
    }


}

?>
