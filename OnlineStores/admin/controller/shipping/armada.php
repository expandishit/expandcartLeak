<?php

class ControllerShippingArmada extends Controller
{
    public function install(){
        $this->load->model('shipping/armada');
        $this->model_shipping_armada->install();
    }
    
    public function uninstall(){
        $this->load->model('shipping/armada');
        $this->model_shipping_armada->uninstall();
    }
    
    private $error = array();

    public function index()
    {
        $this->language->load('shipping/armada');
        $this->load->model('setting/setting');
        
        //after the new update that happened in 
        // dash board in case we need to run any method
        // we can only run it from inxed function       
        // action is using to handel this case
        
        if(!empty($this->request->get['action']))
        {
            $this->{$this->request->get['action']}();
        }
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            if (!$this->validate()) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }

            $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'armada', true);

            foreach($this->request->post as $key => $value) {
                $this->request->post[$key] = trim($value);
            }

            $this->model_setting_setting->editSetting('armada', $this->request->post);

            $this->tracking->updateGuideValue('SHIPPING');
                        
            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('armada_success_save');
            $result_json['success'] = '1';
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->document->setTitle($this->language->get('armada_title'));

        /**
         * Breadcrumbs
         */
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_shipping'),
            'href' => $this->url->link('extension/shipping', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('armada_title'),
            'href' => $this->url->link('shipping/armada', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'action' => $this->url->link('shipping/armada', '', 'SSL'),
            'cancel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];

        $this->template = 'shipping/armada.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data['cancel'] = $this->url->link('extension/shipping', '', 'SSL');

        $this->data['armada_payment_methods'] = [
            ['value' => 'cod', 'label' => $this->language->get('armada_cod')],
            ['value' => 'paid', 'label' => $this->language->get('armada_paid')]
        ];

        $this->data['armada_status'] = $this->config->get('armada_status');
        $this->data['armada_test_mode'] = $this->config->get('armada_test_mode');
        $this->data['armada_api_key'] = $this->config->get('armada_api_key');
        $this->data['armada_platform_name'] = $this->config->get('armada_platform_name');
        $this->data['armada_shipping_rate'] = $this->config->get('armada_shipping_rate');

        $this->response->setOutput($this->render());
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'shipping/armada')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['armada_api_key'])) {
            $this->error['error'] = $this->language->get('armada_error_api_key');
        }

        if (empty($this->request->post['armada_platform_name'])) {
            $this->error['error'] = $this->language->get('armada_error_platform_name');
        }

        return $this->error ? false : true;
    }
    
    public function uploadAreasXlsx(){
        // make sure that the temp directory is exist before uploading.
       if (!\Filesystem::isDirExists("temp/armada")) {
            \Filesystem::createDir("temp/armada");
            \Filesystem::setPath("temp/armada")->changeMod('writable');
        }
        
        $file = 'armada_shipping_cost'.'.'.end(explode('.', $this->request->files['import']['name']));
        move_uploaded_file($this->request->files['import']['tmp_name'], TEMP_DIR_PATH .'/armada/'. $file);
        
    }
    
    public function importAreasData() {
        if (\Filesystem::isExists("temp/armada/armada_shipping_cost.xls")) {
            $this->load->model('shipping/armada');
            include_once DIR_SYSTEM . 'library/PHPExcel.php';

            $objPHPExcel = PHPExcel_IOFactory::load(TEMP_DIR_PATH . '/armada/armada_shipping_cost.xls');
            $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
            unset($allDataInSheet[1]); //remove first row that stor labels

            if (!empty($allDataInSheet[2]['A'])) {
                $this->model_shipping_armada->deleteArmadaShippingCost();
                foreach ($allDataInSheet as $rowData) {

                    if (!empty($rowData['A'])) {
                        $data['area_id'] = $rowData['A'];
                        $data['price'] = $rowData['C'];
                        $this->model_shipping_armada->addArmadaShippingCost($data);
                    }
                }
            }

            if (\Filesystem::isExists("temp/armada/armada_shipping_cost.xls")) {
                \Filesystem::deleteFile("temp/armada/armada_shipping_cost.xls");
            }

        }
    }

    public function exportAreasExcel() {

        $this->language->load('shipping/armada');

        $this->load->model('localisation/area');

        $data = $this->model_localisation_area->getAreasLocalized();

        $i = 1;

        include_once DIR_SYSTEM . 'library/PHPExcel.php';

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $this->language->get('armada_customer_area_id'))
                ->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $this->language->get('armada_customer_area'))
                ->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $this->language->get('armada_customer_area_cost'))
                ->getColumnDimension('C')->setAutoSize(true);

        foreach ($data as $row) {
            $i++;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['area_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, html_entity_decode($row['name'], ENT_QUOTES, 'UTF-8'));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, 0);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $filname = $this->language->get('heading_title') .'Pricing'. '-' . time() . '.xls';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $filname);
        header('Cache-Control: max-age=0');

        ob_end_clean();
        ob_start();

        $objWriter->save('php://output');
        exit();
    }

}