<?php

class ControllerModuleProductAttachments extends Controller

{
    private $errors = [];
    public $module = "product_attachments";

    public function install()
    {
        $this->load->model('module/product_attachments');
        $this->model_module_product_attachments->install();
    }

    public function uninstall()
    {
        $this->load->model('module/product_attachments');
        $this->model_module_product_attachments->uninstall();
    }

    public function index()
    {
        $this->language->load('module/product_attachments');
        //$this->load->model('module/product_preparation_period');
        $this->load->model('localisation/language');

        $this->document->setTitle($this->language->get('product_attachments_title'));

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('product_attachments_text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('product_attachments_title'),
            'href'      => $this->url->link('module/product_attachments', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['settings_content'] = $this->getChild("module/{$this->module}/settings");
        $this->data['downloads_content'] = $this->getChild("module/{$this->module}/downloads");
        $this->data['active'] = $_GET['active'];

        $this->template = 'module/product_attachments/index.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function settings()
    {
        $this->language->load('module/product_attachments');
        //$this->load->model('module/product_preparation_period');
        $this->load->model('localisation/language');

        $this->data['product_attachments'] = $this->config->get('product_attachments');
        $this->data['submit_link'] = $this->url->link('module/product_attachments/saveSettings', '', 'SSL');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->template = 'module/product_attachments/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function saveSettings()
    {
        $this->language->load('module/product_attachments');

        if (!$this->validate()) {
            $json['success'] = '0';
            $json['errors'] = $this->errors;
        } else {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('product_attachments', $this->request->post);
            $json['success'] = '1';
            $json['success_msg'] = $this->language->get('success');
        }

        $this->response->setOutput(json_encode($json));
    }

    private function validate()
    {
        return empty($this->errors);
    }

    public function downloadsDtHandler() 
    {
        $this->load->model('catalog/download');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'download_id',
            1 => 'filename',
            2 => 'remaining',
            3 => 'mask',
            4 => 'date_added',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_catalog_download->dtHandler($start, $length, $search, $orderColumn, $orderType);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $records   // total data array
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function downloadsDtDelete()
    {
        $this->load->model('catalog/download');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];

        if ( is_array($id_s) )
        {
            foreach ($id_s as $download_id)
            {
                if ( $this->model_catalog_download->deleteDownload( (int) $download_id ) )
                {
                    $result_json['success'] = '1';
                    $result_json['success_msg'] = ':)';
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
            $download_id = (int) $id_s;

            if ( $this->model_catalog_download->deleteDownload($download_id) )
            {
                $result_json['success'] = '1';
                $result_json['success_msg'] = ':)';
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

    public function downloads() 
    {
        $this->language->load('catalog/download');

        $this->document->setTitle($this->language->get('heading_title'));
    
        $this->load->model('catalog/download');
        
        $this->getDownloadsList();
    }

    public function insert_download() 
    {
        $this->language->load('catalog/download');
    
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('catalog/download');
            
        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( !$this->validateDownloadsForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_catalog_download->addDownload($this->request->post);
            
            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
      
            $result_json['success'] = '1';
            
            $result_json['redirect'] = 1;
            $result_json['to'] = (string)$this->url->link(
                'module/product_attachments', 'active=downloads', 'SSL'
            );

            $this->response->setOutput(json_encode($result_json));
            
            return;
        }
    
        $this->getDownloadsForm();
    }

    public function update_download()
    {
        $this->language->load('catalog/download');

        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('catalog/download');
            
        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( !$this->validateDownloadsForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_catalog_download->editDownload($this->request->get['download_id'], $this->request->post);
            
            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
          
            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }
        
        $this->getDownloadsForm();
    }

    public function delete_download() 
    {
        $this->language->load('catalog/download');
 
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('catalog/download');
            
        if (isset($this->request->post['selected']) && $this->validateDeleteDownloads()) {     
            foreach ($this->request->post['selected'] as $download_id) {
                $this->model_catalog_download->deleteDownload($download_id);
            }
            
            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';
            
            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }
            
            $this->redirect($this->url->link(
                'moudle/product_attachments', 'active=downloads&token=' . $this->session->data['token'] . $url, 'SSL'
            ));
        }

        $this->getDownloadsList();
    }
    
    protected function getDownloadsList() 
    {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'dd.name';
        }
        
        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }
        
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
                
        $url = '';
            
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }
        
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }
                            
        $this->data['insert'] = $this->url->link('module/product_attachments/insert_download', 'active=downloads&token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['delete'] = $this->url->link('module/product_attachments/delete_download', 'active=downloads&token=' . $this->session->data['token'] . $url, 'SSL');    

        $this->data['downloads'] = array();

        $data = array(
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit')
        );

        $download_total = $this->model_catalog_download->getTotalDownloads();
    
        $results = $this->model_catalog_download->getDownloads($data);
 
        foreach ($results as $result) {
            $action = array();
                        
            $action[] = array(
                'text' => $this->language->get('text_edit'),
                'href' => $this->url->link('module/product_attachments/update_download', 'token=' . $this->session->data['token'] . '&download_id=' . $result['download_id'] . $url, 'SSL')
            );
                        
            $this->data['downloads'][] = array(
                'download_id' => $result['download_id'],
                'name'        => $result['name'],
                'remaining'   => $result['remaining'],
                'selected'    => isset($this->request->post['selected']) && in_array($result['download_id'], $this->request->post['selected']),
                'action'      => $action
            );
        }   
 
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }
        
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
        
            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }
        
        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }
        
        $this->data['sort_name'] = $this->url->link('module/product_attachments', 'active=downloads&token=' . $this->session->data['token'] . '&sort=dd.name' . $url, 'SSL');
        $this->data['sort_remaining'] = $this->url->link('module/product_attachments', 'active=downloads&token=' . $this->session->data['token'] . '&sort=d.remaining' . $url, 'SSL');
        
        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
                                                
        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $download_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('module/product_attachments', 'active=downloads&token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();

        $this->data['sort'] = $sort;
        $this->data['order'] = $order;
        $this->data['status']=$this->config->get('product_attachments')['status'];
        if ($download_total == 0){
            $this->template = 'module/product_attachments/downloads/empty.expand';
        }else{
            $this->template = 'module/product_attachments/downloads/download_list.expand';
        }
        $this->children = array(
            'common/header',
            'common/footer'
        );
                
        $this->response->setOutput($this->render());
    }
  
    protected function getDownloadsForm() 
    {
        $this->data['heading_title'] = $this->language->get('heading_title');
        
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }
        
        if (isset($this->error['name'])) {
            $this->data['error_name'] = $this->error['name'];
        } else {
            $this->data['error_name'] = array();
        }
        
        if (isset($this->error['filename'])) {
            $this->data['error_filename'] = $this->error['filename'];
        } else {
            $this->data['error_filename'] = '';
        }
        
        if (isset($this->error['mask'])) {
            $this->data['error_mask'] = $this->error['mask'];
        } else {
            $this->data['error_mask'] = '';
        }
                
        $url = '';
            
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }
        
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }
        
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/product_attachments', 'active=downloads&token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );
        
        $this->data['breadcrumbs'][] = array(
            'text'      => !isset($this->request->get['download_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('module/product_attachments', 'active=downloads&token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

        if (!isset($this->request->get['download_id'])) {
            $this->data['action'] = $this->url->link('module/product_attachments/insert_download', 'active=downloads&token=' . $this->session->data['token'] . $url, 'SSL');
        } else {
            $this->data['action'] = $this->url->link('module/product_attachments/update_download', 'active=downloads&token=' . $this->session->data['token'] . '&download_id=' . $this->request->get['download_id'] . $url, 'SSL');
        }
        
        $this->data['cancel'] = $this->url->link(
            'module/product_attachments', 'active=downloads&token=' . $this->session->data['token'] . $url, 'SSL'
        );
        
        $this->load->model('localisation/language');
        
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->get['download_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $download_info = $this->model_catalog_download->getDownload($this->request->get['download_id']);
        }

        $this->data['token'] = $this->session->data['token'];
  
        if (isset($this->request->get['download_id'])) {
            $this->data['download_id'] = $this->request->get['download_id'];
        } else {
            $this->data['download_id'] = 0;
        }
        
        if (isset($this->request->post['download_description'])) {
            $this->data['download_description'] = $this->request->post['download_description'];
        } elseif (isset($this->request->get['download_id'])) {
            $this->data['download_description'] = $this->model_catalog_download->getDownloadDescriptions($this->request->get['download_id']);
        } else {
            $this->data['download_description'] = array();
        } 

        if (isset($this->request->post['filename'])) {
            $this->data['filename'] = $this->request->post['filename'];
        } elseif (!empty($download_info)) {
            $this->data['filename'] = $download_info['filename'];
        } else {
            $this->data['filename'] = '';
        }
        
        if (isset($this->request->post['mask'])) {
            $this->data['mask'] = $this->request->post['mask'];
        } elseif (!empty($download_info)) {
            $this->data['mask'] = $download_info['mask'];       
        } else {
            $this->data['mask'] = '';
        }
        
        if (isset($this->request->post['remaining'])) {
            $this->data['remaining'] = $this->request->post['remaining'];
        } elseif (!empty($download_info)) {
            $this->data['remaining'] = $download_info['remaining'];
        } else {
            $this->data['remaining'] = 1;
        }
                      
        if (isset($this->request->post['update'])) {
            $this->data['update'] = $this->request->post['update'];
        } else {
            $this->data['update'] = false;
        }

        $this->template = 'module/product_attachments/downloads/download_form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
                
        $this->response->setOutput($this->render());    
    }

    private function validateDownloadsForm()
    {

        if ( !$this->user->hasPermission('modify', 'catalog/download') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }


        foreach ( $this->request->post['download_description'] as $language_id => $value )
        {
            if ( (utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 64) )
            {
                $this->error['name_' . $language_id] = $this->language->get('error_name');
            }
        }   

        if ( (utf8_strlen($this->request->post['filename']) < 3) || (utf8_strlen($this->request->post['filename']) > 128) )
        {
            $this->error['filename'] = $this->language->get('error_filename');
        }

        $fileTypes = explode(";", $this->config->get('config_file_extension_allowed'));
        $fileTypes = array_map('trim', $fileTypes);
        $fileType = strtolower($this->request->post['file_type']);
        preg_match('#.*?\.(.*?)\.[0-9a-z]#i', $this->request->post['filename'], $realType);

        if (!isset($fileType) || in_array($fileType, $fileTypes) == false || (
            isset($realType[1]) && $fileType != strtolower($realType[1])
        )) {
            $this->error['file_type'] = $this->language->get('error_filetype');
        }
        
        if ( !file_exists(DIR_DOWNLOAD . $this->request->post['filename']) && !is_file(DIR_DOWNLOAD . $this->request->post['filename']) )
        {
            // $this->error['filename'] = $this->language->get('error_exists');
        }
                
        if ( (utf8_strlen($this->request->post['mask']) < 3) || (utf8_strlen($this->request->post['mask']) > 128) )
        {
            $this->error['mask'] = $this->language->get('error_mask');
        }   
            
        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }

    protected function validateDeleteDownloads() 
    {
        if (!$this->user->hasPermission('modify', 'catalog/download')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }   
        
        $this->load->model('catalog/product');

        foreach ($this->request->post['selected'] as $download_id) {
            $product_total = $this->model_catalog_product->getTotalProductsByDownloadId($download_id);
    
            if ($product_total) {
                $this->error['warning'] = sprintf($this->language->get('error_product'), $product_total);   
            }   
        }   
                     
        if (!$this->error) {
            return true;
        } else {
            return false;
        } 
    }

    public function upload() 
    {
        $this->language->load('sale/order');
        
        $json = array();
        
        if (!$this->user->hasPermission('modify', 'catalog/download')) {
            $json['error'] = $this->language->get('error_permission');
        }   
        
        if (!isset($json['error'])) {   
            if (!empty($this->request->files['file']['name'])) {
                $filename = basename(html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8'));
                
                if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 128)) {
                    $json['error'] = $this->language->get('error_filename');
                }       
                
                // Allowed file extension types
                $allowed = array();
                
                $filetypes = explode(";", $this->config->get('config_file_extension_allowed'));
                
                foreach ($filetypes as $filetype) {
                    $allowed[] = trim($filetype);
                }

                $_filetype = strtolower(substr(strrchr($filename, '.'), 1));
                
                if (!in_array($_filetype, $allowed)) {
                    $json['error'] = $this->language->get('error_filetype');
                }   
                
                // Allowed file mime types      
                $allowed = array();
                
                $filetypes = explode(";", $this->config->get('config_file_mime_allowed'));
                
                foreach ($filetypes as $filetype) {
                    $allowed[] = trim($filetype);
                }
                                
                if (!in_array(strtolower($this->request->files['file']['type']), $allowed)) {
                    $json['error'] = $this->language->get('error_filetype');
                }
                            
                if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                    $json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
                }
                                    
                if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                    $json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
                }
            } else {
                $json['error'] = $this->language->get('error_upload');
            }
        }

        if (!isset($json['error'])) {

            $ext = md5(mt_rand());

            $fileType = substr(strrchr($filename, '.'), 1);
            
            $json['filename'] = $filename . '.' . $ext;
            $json['mask'] = $filename;
            $json['file_type'] = $fileType;
            /*$_f = new File(($filename . '.' . $ext), [
                'adapter' => 'local', 'base'=> DIR_DOWNLOAD,
                'adapter' => 'gcs', 'base'=> STORECODE . '/downloads',
            ]);
            $u = $_f->upload($this->request->files['file']['tmp_name']);*/
            $path = 'downloads/' . $json['filename'];
            \Filesystem::setPath($path)->upload($this->request->files['file']['tmp_name']);

            /*
            if (is_uploaded_file($this->request->files['file']['tmp_name']) && file_exists($this->request->files['file']['tmp_name'])) {
                $ext = md5(mt_rand());

                $fileType = substr(strrchr($filename, '.'), 1);
                
                $json['filename'] = $filename . '.' . $ext;
                $json['mask'] = $filename;
                $json['file_type'] = $fileType;
                
                move_uploaded_file($this->request->files['file']['tmp_name'], DIR_DOWNLOAD . $filename . '.' . $ext);
            }
            */
                        
            $json['success'] = $this->language->get('text_upload');
        }   
    
        $this->response->setOutput(json_encode($json));
    }

    public function autocomplete() 
    {
        $json = array();
        
        if (isset($this->request->get['filter_name'])) {
            $this->load->model('catalog/download');
            
            $data = array(
                'filter_name' => $this->request->get['filter_name'],
                'start'       => 0,
                'limit'       => 20
            );
            
            $results = $this->model_catalog_download->getDownloads($data);
                
            foreach ($results as $result) {
                $json[] = array(
                    'download_id' => $result['download_id'], 
                    'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
                );
            }       
        }

        $sort_order = array();
      
        foreach ($json as $key => $value) {
            $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->setOutput(json_encode($json));
    }

}
