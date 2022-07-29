<?php

class ControllerProductDesignerClipart extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('module/product_designer');

        $this->document->setTitle($this->language->get('pd_categories'));

        $this->getList();
    }
    /*
     * Get categories for datatable
     */
    public function dtHandler() {
        $this->load->model('module/product_designer/clipart');
        $this->load->model('module/product_designer/settings');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'caid',
            1 => 'category_name',
            2 => 'category_image',
            3 => 'status',
            4 => '',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];


        $return = $this->model_module_product_designer_clipart->dtHandler($start, $length, $search, $orderColumn, $orderType);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of frecords
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $records,   // total data array
        );

        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function insert()
    {

        $this->load->language('module/product_designer');

        $this->document->setTitle($this->language->get('pd_category_and_cliparts'));

        $this->load->model('module/product_designer/clipart');

        $data['heading_title2'] = $this->language->get('heading_title2');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {


            if ($this->request->post['txtCategoryName'] == '') {
                $result_json['success'] = '0';
                $result_json['success_msg'] =  $this->language->get('pd_empty_category_name_error');

            }
            else{
                $this->model_module_product_designer_clipart->addCategory($this->request->post);
                $result_json['success'] = '1';
                $result_json['success_msg'] =  $this->language->get('text_category_success');
            }
            $this->response->setOutput(json_encode($result_json));
            return;
        }
        else{
            $this->getForm();
        }
    }

    public function update()
    {
        $this->load->language('module/product_designer');

        $this->document->setTitle($this->language->get('pd_category_and_cliparts'));

        $this->load->model('module/product_designer/clipart');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            if ($this->request->post['txtCategoryName'] == '') {
                $result_json['success'] = '0';
                $result_json['success_msg'] =  $this->language->get('pd_empty_category_name_error');
            }
            else {
                $this->model_module_product_designer_clipart->editCategory($this->request->get['caid'],$this->request->post);
                $result_json['success'] = '1';
                $result_json['success_msg'] =  $this->language->get('text_catergory_update_success');

            }
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->getForm();
    }

    public function dtDelete()
    {

        $this->load->language('module/product_designer');

        $this->load->model('module/product_designer/clipart');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];

        if ( is_array($id_s) )
        {
            foreach ($id_s as $cat_id)
            {
                if ( $this->model_module_product_designer_clipart->deleteCategory( (int) $cat_id ) )
                {
                    $result_json['success'] = '1';
                    $result_json['success_msg'] =  $this->language->get('text_success_delete');
                }
                else
                {
                    $result_json['success'] = '0';
                    $result_json['error'] = ':(';
                    break;
                }
            }
        }
        else
        {
            $cat_id = (int) $id_s;

            if ( $this->model_module_product_designer_clipart->deleteCategory($cat_id) )
            {
                $result_json['success'] = '1';
                $result_json['success_msg'] =  $this->language->get('text_success_delete');
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['error'] = ':(';
            }
        }
        $this->response->setOutput(json_encode($result_json));
        return;

    }

    private function getList()
    {
        $url = '';
        $this->load->model('module/product_designer/clipart');
        $this->load->model('module/product_designer/settings');

        $clipart = $this->model_module_product_designer_clipart;
        $settings = $this->model_module_product_designer_settings;

        $data['cancel'] = $this->url->link('module/product_designer', 'token=' . $this->session->data['token'], 'SSL');
        $data['pd_settings'] = $this->url->link('module/product_designer', 'token=' . $this->session->data['token'], 'SSL');

        $data['breadcrumbs'] = $settings->genereateBreadcrumbs([
            ['text' => 'text_home', 'href' => 'common/home', 'separator' => false],
            ['text' => 'text_module', 'href' => 'marketplace/home', 'separator' => ' :: '],
            ['text' => 'pd_heading_title', 'href' => 'module/product_designer', 'separator' => ' :: '],
            ['text' => 'pd_categories', 'href' => 'product_designer/clipart', 'separator' => ' :: '],
        ]);

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['insert'] = $this->url->link('product_designer/clipart/getForm', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $data['view_cliparts'] = $this->url->link('product_designer/clipart/showcatimage', 'token=' . $this->session->data['token'] . $url, 'SSL');

        $data['insert_clipart'] = $this->url->link('product_designer/clipart/clipartform', 'token=' . $this->session->data['token'] . $url, 'SSL');

        $data['categorylist'] = array();

        $results = $clipart->getAllCategoryList();

        $this->load->model('tool/image');

        foreach ($results as $result) {
            $category_image = $this->model_tool_image->resize('no_image.jpg', 35, 35);
            if(
                isset($result['category_image'])
                && ($result['category_image'] != 'no_image.jpg' && $result['category_image'] != '')
            ) {
                $category_image = $this->model_tool_image->resize("modules/pd_images/categories/" . $result['category_image'], 35, 35);
            }

            $data['categorylist'][] = array(
                'caid '              => $result['caid'],
                'category_name'     => $result['category_name'],
                'category_image'    => $category_image,
                'status'            => $result['status'],
                'href1'             => $this->url->link('product_designer/clipart/getform', 'token=' . $this->session->data['token'] . '&cid=' . $result['caid'] . $url, 'SSL'),
                'href2'             => $this->url->link('product_designer/clipart/delete', 'token=' . $this->session->data['token'] . '&cid=' . $result['caid'] . $url, 'SSL'),
                'href3'             => $this->url->link('product_designer/clipart/showcatimage', 'token=' . $this->session->data['token'] . '&cid=' . $result['caid'] . '&cname=' . $result['category_name'] . $url, 'SSL')
            );
        }

        $data['heading_title'] = $this->language->get('pd_category_and_cliparts');

        $data['text_form'] = $this->language->get('pd_category_and_cliparts');
        $data['text_active'] = $this->language->get('text_enabled');
        $data['text_inactive'] = $this->language->get('text_disabled');

        $data['text_no_results'] = $this->language->get('text_no_results');

        $data['column_title'] = $this->language->get('column_title');
        $data['column_Created'] = $this->language->get('column_Created');
        $data['column_Details'] = $this->language->get('column_Details');
        $data['column_Status'] = $this->language->get('column_Status');

        $data['button_insert'] = $this->language->get('button_insert');
        $data['button_delete'] = $this->language->get('button_delete');

         if (isset($this->session->data['errors'])) {
            $data['error_warning'] = $this->session->data['errors'];

             unset($this->session->data['errors']);
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $this->template = 'module/product_designer/category_list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data = $data;

        $this->response->setOutput($this->render());

    }

    public function getform()
    {

        $this->load->language('module/product_designer');

        $this->document->setTitle($this->language->get('pd_category_and_cliparts'));

        $data['heading_title'] = $this->language->get('pd_category_and_cliparts');

        $data['heading_title2'] = $this->language->get('heading_title2');

        $data['text_default'] = $this->language->get('text_default');

        $data['text_enabled'] = $this->language->get('text_enabled');

        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_title'] = $this->language->get('entry_title');

        $data['entry_image'] = $this->language->get('entry_image');

        $data['entry_description'] = $this->language->get('entry_description');

        $data['Status'] = $this->language->get('Status');

        $data['button_save'] = $this->language->get('button_save');

        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['token'] = $this->session->data['token'];

        $this->load->model('tool/image');

        $data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
        $data['thumb'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

        if (isset($this->session->data['errors'])) {
            $data['error_warning'] = $this->session->data['errors'];

            unset($this->session->data['errors']);
        } else {
            $data['error_warning'] = '';
        }
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
         if (isset($this->error['title'])) {
            $data['title'] = $this->error['title'];
        } else {
            $data['title'] = array();
        }

         if (isset($this->error['description'])) {
            $data['description'] = $this->error['description'];
        } else {
            $data['description'] = array();
        }

        $this->load->model('module/product_designer/clipart');
        $this->load->model('module/product_designer/settings');

        $clipart = $this->model_module_product_designer_clipart;
        $settings = $this->model_module_product_designer_settings;

        $data['breadcrumbs'] = $settings->genereateBreadcrumbs([
            ['text' => 'text_home', 'href' => 'common/home', 'separator' => false],
            ['text' => 'text_module', 'href' => 'marketplace/home', 'separator' => ' :: '],
            ['text' => 'pd_heading_title', 'href' => 'module/product_designer', 'separator' => ' :: '],
            ['text' => 'pd_categories', 'href' => 'product_designer/clipart', 'separator' => ' :: '],
        ]);

        $url = '';

        $this->load->model('tool/image');
        $data['category_image'] = $this->model_tool_image->resize('no_image.png', 150, 150);
        $data['img_hdn_1'] = '';

        /*Here we set the value for the update mode*/
        $data['category_name'] = '';

        $data['image_url_only'] = '';

        $data['status'] = 0;

        if (!isset($this->request->get['caid'])) {
            $data['action'] = $this->url->link('product_designer/clipart/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
        } else {
            $this->load->model('module/product_designer/clipart');
            $result = $this->model_module_product_designer_clipart->getCategoryById($this->request->get['caid']);

            $data['category_name'] = $result['category_name'];
            if(isset($result['category_image']) &&  $result['category_image'] != ''){
                $data['image_url_only'] = $result['category_image'];
                $data['thumb'] = $this->model_tool_image->resize($result['category_image'], 150, 150);
                $data['image'] = $this->model_tool_image->resize($result['category_image'], 150, 150);
                $data['img_hdn_1'] = $result['category_image'];
            }
            $data['status'] = $result['status'];
            $data['action'] = $this->url->link('product_designer/clipart/update', 'token=' . $this->session->data['token'] . '&caid=' . $this->request->get['caid'] . $url, 'SSL');
        }

        $data['cancel'] = $this->url->link('product_designer/clipart', 'token=' . $this->session->data['token'] . $url, 'SSL');

        $this->data = $data;

        $this->template = 'module/product_designer/insert_category.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

       // var_dump($data);die();
        $this->response->setOutput($this->render());
    }

    public function saveimage()
    {

        $this->load->language('module/product_designer');
        $this->load->model('module/product_designer/clipart');


        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            if (isset($this->request->post['caid']) && is_numeric($this->request->post['caid'])) {

                $data = [];
                $data['optcategory'] = $this->request->post['caid'];
                $data['image'] = $this->request->post['image'];

                if (!$this->model_module_product_designer_clipart->addClipartImage($data)) {
                    $result_json['success'] = '0';
                    $result_json['success_msg'] =  $this->language->get('pd_empty_clipart_image');

                }
                else{
                    $result_json['success'] = '1';
                    $result_json['success_msg'] =  $this->language->get('pd_clipart_uploaded_successfully');
                }
            } else {

                $result_json['success'] = '0';
                $result_json['success_msg'] =  $this->language->get('pd_clipart_uploaded_fail');
            }
            $this->response->setOutput(json_encode($result_json));
            return;
        }
        else{
            $this->clipartform();
        }

    }

    public function clipartform()
    {
        $this->load->language('module/product_designer');
        $this->document->setTitle($this->language->get('pd_cliparts'));
        $this->load->model('module/product_designer/clipart');
        $data['heading_title'] = $this->language->get('pd_cliparts');

        $data['heading_title2'] = $this->language->get('heading_title3');

        $data['text_default'] = $this->language->get('text_default');

        $data['text_enabled'] = $this->language->get('text_enabled');

        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_title'] = $this->language->get('entry_title');

        $data['Status'] = $this->language->get('Status');

        $data['button_save'] = $this->language->get('button_save');

        $data['button_cancel'] = $this->language->get('button_cancel');


        $data['token'] = $this->session->data['token'];

         if (isset($this->session->data['errors'])) {
            $data['error_warning'] = $this->session->data['errors'];

            unset($this->session->data['errors']);
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $url = '';
        $this->load->model('tool/image');
        $data['thumb'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
        $data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

        $this->load->model('module/product_designer/clipart');
        $this->load->model('module/product_designer/settings');

        $clipart = $this->model_module_product_designer_clipart;
        $settings = $this->model_module_product_designer_settings;

        $data['breadcrumbs'] = $settings->genereateBreadcrumbs([
            ['text' => 'text_home', 'href' => 'common/home', 'separator' => false],
            ['text' => 'text_module', 'href' => 'marketplace/home', 'separator' => ' :: '],
            ['text' => 'pd_heading_title', 'href' => 'product_designer/clipart', 'separator' => ' :: '],
            ['text' => 'pd_cliparts', 'href' => null, 'separator' => ' :: '],
        ]);

        /*here we load the clipart all category*/
        $data['categorylist'] = array();

        $results = $this->model_module_product_designer_clipart->getAllCategoryListASC();
        foreach ($results as $result) {
            $data['categorylist'][] = array(
                'caid'                  => $result['caid'],
                'category_name'     => $result['category_name'],
                'status'            => $result['status']
            );
        }


        if(isset($_GET['ccid'])){
            $data['ccid'] = $_GET['ccid'];
        } else {
            $data['ccid'] = '';
        }

        $data['caid'] = $this->request->get['caid'];

        if (isset($this->request->get['cid']) && isset($this->request->get['cname'])) {
            $url .= '&cid=' . $this->request->get['cid'];
            $url .= '&cname=' . $this->request->get['cname'];
        }

        if (!isset($this->request->get['imgid']) && !isset($this->request->get['ccid'])) {
            $data['action_image'] = $this->url->link('product_designer/clipart/saveimage', 'token=' . $this->session->data['token'] . $url, 'SSL');
        } else {
            $this->load->model('module/product_designer/clipart');
            //$result = $this->model_module_product_designer_clipart->getCategoryById($this->request->get['cid']);
            //$data['category_name'] = $result['category_name'];
            //$data['status'] = $result['status'];

            $data['action_image'] = $this->url->link('product_designer/clipart/updateimagecategory', 'token=' . $this->session->data['token'] . '&cid=' . $this->request->get['ccid'] . '&imgid=' . $this->request->get['imgid'] . $url, 'SSL');
        }

        $data['cancel'] = $this->url->link('product_designer/clipart/showcatimage', 'token=' . $this->session->data['token'] . $url, 'SSL');

        $this->template = 'module/product_designer/insert_clipart.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data = $data;

        $this->response->setOutput($this->render());
    }

    public function updateimagecategory()
    {
        $this->load->model('module/product_designer/clipart');
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->model_module_product_designer_clipart->updateImageCategory($this->request->get['imgid'],$this->request->post);
            $this->session->data['success'] = 'Update Image Category successfully';
            $url = '';
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->redirect($this->url->link('product_designer/clipart', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }

        $this->getForm();
    }

    public function dtClipDelete()
    {
        $this->load->model('module/product_designer/clipart');
        $this->load->language('module/product_designer');
        $this->model_module_product_designer_clipart->deleteImage($this->request->post['id']);
        $result_json['success'] = '1';
        $result_json['success_msg'] =  $this->language->get('text_clipart_delete_success');
        /*
        $url = '';
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $this->redirect($this->url->link('product_designer/clipart/showcatimage', 'token=' . $this->session->data['token'] . '&cid=' . $this->request->get['ccid'] . '&cname=' . $this->request->get['cname'] . $url, 'SSL'));
        */
        $this->response->setOutput(json_encode($result_json));
        return;

    }


    public function showcatimage()
    {

        $this->load->language('module/product_designer');

        $this->document->setTitle($this->language->get('pd_cliparts'));

        $this->load->model('module/product_designer/clipart');

        $data['heading_title'] = $this->language->get('pd_cliparts');

        $data['heading_title2'] = $this->request->get['cname'] . ' ' . $this->language->get('heading_title4');

        $data['text_default'] = $this->language->get('text_default');

        $data['text_enabled'] = $this->language->get('text_enabled');

        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_title'] = $this->language->get('entry_title');

        $data['Status'] = $this->language->get('Status');

        $data['button_save'] = $this->language->get('button_save');

        $data['button_cancel'] = $this->language->get('button_cancel');


        $data['token'] = $this->session->data['token'];

         if (isset($this->session->data['errors'])) {
            $data['error_warning'] = $this->session->data['errors'];

            unset($this->session->data['errors']);
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $url = '';

        $this->load->model('module/product_designer/clipart');
        $this->load->model('module/product_designer/settings');

        $clipart = $this->model_module_product_designer_clipart;
        $settings = $this->model_module_product_designer_settings;

        $data['breadcrumbs'] = $settings->genereateBreadcrumbs([
            ['text' => 'text_home', 'href' => 'common/home', 'separator' => false],
            ['text' => 'text_module', 'href' => 'marketplace/home', 'separator' => ' :: '],
            ['text' => 'pd_heading_title', 'href' => 'module/product_designer', 'separator' => ' :: '],
            ['text' => 'pd_cliparts', 'href' => 'product_designer/clipart', 'separator' => ' :: '],
        ]);

        if (!isset($this->request->get['imgid'])) {
            $data['action_image'] = $this->url->link('product_designer/clipart/saveimage', 'token=' . $this->session->data['token'] . $url, 'SSL');
        } else {
            $this->load->model('module/product_designer/clipart');
            //$result = $this->model_module_product_designer_clipart->getCategoryById($this->request->get['cid']);
            //$data['category_name'] = $result['category_name'];
            //$data['status'] = $result['status'];

            $data['action_image'] = $this->url->link('product_designer/clipart/saveimage', 'token=' . $this->session->data['token'] . '&cid=' . $this->request->get['cid'] . $url, 'SSL');
        }

        $data['caid'] = $this->request->get['caid_id'];

        if (isset($this->request->get['cid']) && isset($this->request->get['cname'])) {
            $url2  = '&cid=' . $this->request->get['cid'];
            $url2 .= '&cname=' . $this->request->get['cname'];
        }

        $data['cancel'] = $this->url->link('module/product_designer', 'token=' . $this->session->data['token'] . $url2, 'SSL');
        $data['insert'] = $this->url->link('product_designer/clipart/clipartform', 'token=' . $this->session->data['token'] . $url2, 'SSL');

        $this->template = 'module/product_designer/view_clipart.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data = $data;

        $this->response->setOutput($this->render());

    }

    public function dtClipHandler(){

        $this->load->model('module/product_designer/clipart');
        $this->load->model('module/product_designer/settings');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'imgid',
            1 => 'category_name',
            2 => 'image_name',
            3 => 'href2',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];


        $return = $this->model_module_product_designer_clipart->dtClipHandler($start, $length, $search, $orderColumn, $orderType);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $records,   // total data array
        );

        $this->response->setOutput(json_encode($json_data));
        return;
    }
}
