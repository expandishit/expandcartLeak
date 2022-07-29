<?php

class ControllerModulePrizeDraw extends Controller
{
    private $error;

    public function index()
    {
        $this->load->model('module/prize_draw');
        $this->language->load('module/prize_draw');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('module/prize_draw');
        //$data['prizes'] = $this->model_module_prize_draw->getPrizes();

        if (isset($this->request->post['prize_draw'])) {
            $data['prize_draw_module'] = $this->request->post['prize_draw_module'];
        } elseif ($this->config->get('prize_draw_module')) {
            $data['prize_draw_module'] = $this->config->get('prize_draw_module');
        }

        $this->load->model('localisation/order_status');
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->document->setTitle($this->language->get('prize_draw_heading_Stitle'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('prize_draw_heading_Stitle'),
            'href'      => $this->url->link('module/prize_draw', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/prize_draw/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/prize_draw/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function prizes()
    {
        $this->load->model('module/prize_draw');
        $this->language->load('module/prize_draw');
        
        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('module/prize_draw');
        //$data['prizes'] = $this->model_module_prize_draw->getPrizes();

        $this->document->setTitle($this->language->get('prize_draw_heading_Stitle'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('prize_draw_heading_title'),
            'href'      => $this->url->link('module/prize_draw', '', 'SSL'),
            'separator' => ' :: '
        );
        
        $this->template = 'module/prize_draw/prize_list.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/prize_draw/remove', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function customers()
    {
        $this->load->model('module/prize_draw');
        $this->language->load('module/prize_draw');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (!isset($this->request->get['prize_id'])) {
            $this->redirect($this->url->link('module/prize_draw/prizes', '', 'SSL'));
            return;
        }

        $this->load->model('module/prize_draw');
        $data['prize_id'] = $this->request->get['prize_id'];
        $prize_info = $this->model_module_prize_draw->getPrize($data['prize_id']);

        $this->document->setTitle($this->language->get('prize_draw_heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_Ltitle'),
            'href'      => $this->url->link(
                'module/prize_draw/prizes',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('prize_draw_customer_title').'[ '.$prize_info['title'].' ]',
            'href'      => $this->url->link('module/prize_draw', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/prize_draw/prize_customer_list.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    //insert new prize
    public function insert() {
        $this->load->model('module/prize_draw');
        $this->language->load('module/prize_draw');

        $this->document->setTitle($this->language->get('prize_draw_heading_title'));


        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( !$this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_module_prize_draw->addPrize($this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->getForm();
    }

    public function update() {
        $this->load->model('module/prize_draw');
        $this->language->load('module/prize_draw');

        $this->document->setTitle($this->language->get('prize_draw_heading_Ltitle'));

        if ($this->request->server['REQUEST_METHOD'] == 'POST')
        {
            if ( !$this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_module_prize_draw->editPrize($this->request->get['prize_id'], $this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->getForm();
    }

    public function dtDelete()
    {
        $this->language->load('module/prize_draw');
        $this->load->model('module/prize_draw');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $prize_connected = false;

        $id_s = $this->request->post['id'];

        if ( is_array($id_s) )
        {
            foreach ($id_s as $prize_id)
            {
                $dlt = $this->model_module_prize_draw->deletePrize($prize_id);

                if(!$dlt && !$prize_connected)
                    $prize_connected = true;
            }
        }
        else
        {
            $prize_id = (int) $id_s;
            $dlt = $this->model_module_prize_draw->deletePrize( $prize_id );
            if(!$dlt)
                $prize_connected = true;
        }

        if($prize_connected){
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_delete_connected_error');
        }else{
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success');
        }

        $this->response->setOutput(json_encode($result_json));
        return;

    }

    protected function getForm()
    {
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->error['title'])) {
            $this->data['error_title'] = $this->error['title'];
        } else {
            $this->data['error_title'] = array();
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_Ltitle'),
            'href'      => $this->url->link('module/prize_draw/prizes', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => !isset($this->request->get['prize_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('module/prize_draw/prizes', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['prize_products'] = [];
        if (!isset($this->request->get['prize_id'])) {
            $this->data['products'] = $this->model_module_prize_draw->getProducts();
            $this->data['action'] = $this->url->link('module/prize_draw/insert', '', 'SSL');
        } else {
            $this->data['products'] = $this->model_module_prize_draw->getProducts($this->request->get['prize_id']);
            $this->data['action'] = $this->url->link('module/prize_draw/update', 'prize_id=' . $this->request->get['prize_id'], 'SSL');
            $prize_products = $this->model_module_prize_draw->getPrizeProducts($this->request->get['prize_id']);
            $prize_products_ids = [];

            if(count($prize_products)){
                foreach ($prize_products as $prd){
                    $prize_products_ids[] =  $prd['product_id'];
                }
                $this->data['prize_products'] = $prize_products_ids;
            }

            $this->data['prize_id'] = $this->request->get['prize_id'];
        }

        $this->data['cancel'] = $this->url->link('module/prize_draw/prizes', '', 'SSL');

        if (isset($this->request->get['prize_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $prize_info = $this->model_module_prize_draw->getPrize($this->request->get['prize_id']);
        }

        $this->data['token'] = $this->session->data['token'];

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->post['prize_description'])) {
            $this->data['prize_description'] = $this->request->post['prize_description'];
        } elseif (isset($this->request->get['prize_id'])) {
            $this->data['prize_description'] = $this->model_module_prize_draw->getPrizeDescriptions($this->request->get['prize_id']);
        } else {
            $this->data['prize_description'] = array();
        }

        if (isset($this->request->post['path'])) {
            $this->data['path'] = $this->request->post['path'];
        } elseif (!empty($prize_info)) {
            $this->data['path'] = $prize_info['path'];
        } else {
            $this->data['path'] = '';
        }

        if (isset($this->request->post['image'])) {
            $this->data['image'] = $this->request->post['image'];
        } elseif (!empty($prize_info)) {
            $this->data['image'] = $prize_info['image'];
        } else {
            $this->data['image'] = '';
        }

        $this->load->model('tool/image');

        $this->data['thumb'] = $this->model_tool_image->resize($this->data['image'], 150, 150);

        if (isset($this->request->post['status'])) {
            $this->data['status'] = $this->request->post['status'];
        } elseif (!empty($prize_info)) {
            $this->data['status'] = $prize_info['status'];
        } else {
            $this->data['status'] = 0;
        }

        if (isset($this->request->post['start_date'])) {
            $this->data['start_date'] = $this->request->post['start_date'];
        } elseif (!empty($prize_info)) {
            $this->data['start_date'] = $prize_info['start_date'];
        } else {
            $this->data['start_date'] = '';
        }

        if (isset($this->request->post['end_date'])) {
            $this->data['end_date'] = $this->request->post['end_date'];
        } elseif (!empty($prize_info)) {
            $this->data['end_date'] = $prize_info['end_date'];
        } else {
            $this->data['end_date'] = '';
        }

        if (isset($this->request->post['dates_status'])) {
            $this->data['dates_status'] = $this->request->post['dates_status'];
        } elseif (!empty($prize_info)) {
            $this->data['dates_status'] = $prize_info['dates_status'];
        } else {
            $this->data['dates_status'] = 0;
        }

        $this->template = 'module/prize_draw/prize_form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    public function dtHandler() {
        $this->load->model('module/prize_draw');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 10;

        $columns = array(
            0 => 'id',
            1 => 'title',
            3 => 'status',
            4 => 'locale',
            5 => 'directory',
            6 => 'filename'
        );
        $orderColumn = $columns[$request['order'][0]['column']] ?? null;
        $orderType = $request['order'][0]['dir'] ?? null;

        $return = $this->model_module_prize_draw->dtHandler($start, $length, $search /*, $orderColumn, $orderType*/);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $this->load->model('tool/image');

        foreach ($records as $key => $record) {
            if($record['image']==""){
                $records[$key]['image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
            }
            else{
                $records[$key]['image'] = $this->model_tool_image->resize($record['image'], 150, 150);
            }
        }

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function dtCstHandler() {
        $this->load->model('module/prize_draw');
        $request = $_REQUEST;
        $prize_id = $request['prize_id'];

        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 10;

        $columns = array(
            0 => 'id',
            1 => 'name',
            3 => 'email',
            4 => 'phone'
        );

        $orderColumn = $columns[$request['order'][0]['column']] ?? null;
        $orderType = $request['order'][0]['dir'] ?? null;

        $return = $this->model_module_prize_draw->dtCstHandler($prize_id, $start, $length, $search /*, $orderColumn, $orderType*/);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function dtUpdateStatus()
    {
        $this->load->model("module/prize_draw");
        $this->load->model("setting/setting");
        $this->load->model("localisation/language");

        if (isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["id"];
            $status = $this->request->post["status"];

            $this->model_module_prize_draw->updatePrizeStatus($id, $status);

            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_updated_successfully');

        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->language->get('notification_unknoen_error');
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

    private function validateForm()
    {
        if ( !$this->user->hasPermission('modify', 'module/prize_draw') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        foreach ( $this->request->post['prize_description'] as $language_id => $value )
        {
            if ( (utf8_strlen($value['title']) < 2) || (utf8_strlen($value['title']) > 255) )
            {
                $this->error['title_' . $language_id] = $this->language->get('error_title');
            }
        }

        /*$sdate = $this->request->post['start_date'];
        $edate = $this->request->post['end_date'];

        if ( !$sdate )
        {
            $this->error['start_date'] = $this->language->get('error_start_date');
        }

        if ( !$edate )
        {
            $this->error['end_date'] = $this->language->get('error_end_date');
        }

        if ( $edate && $sdate && (strtotime($edate) < strtotime($sdate)))
        {
            $this->error['dates'] = $this->language->get('error_dates');
        }*/

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }

    public function updateSettings()
    {

        $this->load->model('setting/setting');
        $this->language->load('module/prize_draw');

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{
            if ( $this->validate() )
            {
                $postData = $this->request->post;

                $this->model_setting_setting->insertUpdateSetting('prize_draw', $postData);

                $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->error;
            }
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function install()
    {
        $this->load->model('module/prize_draw');

        $this->model_module_prize_draw->install();
    }

    public function uninstall()
    {
        $this->load->model('module/prize_draw');

        $this->model_module_prize_draw->uninstall();
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'module/prize_draw')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (!$this->error) {
            return true;
        } else {
            return false;
        }   
    }

    public function customer_code() {

        $this->load->model('module/prize_draw');
        $this->language->load('module/prize_draw');

        $this->data['title'] = $this->language->get('prize_draw_heading_Stitle');

        $this->data['card'] = [];

        if (isset($this->request->get['id'])) {
            $this->data['cards'] = $this->model_module_prize_draw->getCard($this->request->get['id'], 'customer');
        }else if(isset($this->request->get['prize_id'])){
            $this->data['cards'] = $this->model_module_prize_draw->getCard($this->request->get['prize_id'], 'prize');
        }

        $this->template = 'module/prize_draw/customer_code.expand';

        $this->base="common/base";

        $this->response->setOutput( $this->render_ecwig() );
    }
    /*public function assignProducts() {
        $json = array();
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (isset($this->request->post['prize_ids']) && isset($this->request->post['product_ids']) ) {
                $prize_ids = $this->request->post['prize_ids'];
                $product_ids = $this->request->post['product_ids'];

                if ($prize_ids && $product_ids) {
                    $prize_ids = explode(',', $prize_ids);
                    $product_ids = explode(',', $product_ids);

                    $this->load->model('module/prize_draw');

                    foreach ($prize_ids as $key => $pid) {

                        //Assign product to outlet
                        if(count($product_ids)){
                            foreach ($product_ids as $key => $prdid) {
                                $this->model_module_prize_draw->assignProduct($pid, $prdid);
                            }
                        }
                    }
                    $json['status'] = $this->language->get('text_success1');
                }else{
                    $json['status'] = 'failed, no ids';
                }
            }else{
                $json['status'] = 'failed, no data';
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }*/
}
