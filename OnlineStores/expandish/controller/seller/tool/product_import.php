<?php

include DIR_SYSTEM.'library/PHPExcel.php';


class ControllerSellerToolProductImport extends ControllerSellerAccount
{

    private $error = [];

    public function index()
    {
        // initialize the import settings
        if (isset($this->request->get['language_id'])) {
            $this->data['language_id'] = $this->request->get['language_id'];
        } else {
            $this->data['language_id'] = null;
        }

        if (isset($this->request->get['option'])) {
            $this->data['option'] = $this->request->get['option'];
        } else {
            $this->data['option'] = null;
        }

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->data['submit_link'] = $this->url->link('seller/tool/product_import/import');

        // set template
        $this->template = 'default/template/multiseller/tool/product_import.tpl';

        $this->response->setOutput($this->render(true));
    }

    public function import()
    {
        $this->load->language('multiseller');

        $this->validate_form($this->request->post);

        if (empty($this->error)) {
            $filename = $this->upload_product_file();
            if ($filename) {
                // save uploaded settings in session
                $this->session->data['uploaded_seller_id'] = $this->customer->getId();
                $this->session->data['uploaded_product_file_name'] = $filename;
                $this->session->data['language_id'] = $this->request->post['language_id'];
                $this->session->data['option'] = $this->request->post['option'];
            }
        }

        $this->response->setOutput(json_encode(array(
            'status' => empty($this->error),
            'error' => $this->error,
        )));
    }

    public function mapping_form()
    {
        $this->load->language('multiseller');

        $status = $this->check_product_file_structure($this->session->data['uploaded_product_file_name']);

        if (is_array($status)) {
            $this->data['upload_file_fields'] = $status[2];
            $this->data['unmatching_fields'] = $status[1];
            $this->data['fields_uploaded_file'] = $status[0];
            $this->data['form_return_status'] = $this->error ? $this->error : $this->language->get('ms_text_file_uploaded_success');
            $this->data['submit_link'] = $this->url->link('seller/tool/product_import/process_uploaded_file');

            $this->template = 'default/template/multiseller/tool/import/mapping_form.tpl';

            $this->response->setOutput($this->render(true));
            return;
        }

        $this->process_uploaded_file();
    }

    public function process_uploaded_file()
    {
        $this->process_uploaded_product_file(
            $this->session->data['uploaded_seller_id'],
            $this->session->data['uploaded_product_file_name'],
            $this->session->data['language_id']
        );

        $this->response->setOutput(json_encode(array(
            'status' => 1,
        )));
    }

    private function validate_form($data)
    {
        if (!isset($this->request->files['import']) || $this->request->files['import']['name'] == "") {
            $this->error[] = $this->language->get('ms_import_error_warning');
        }

        if ($this->error)
            return false;

        return true;
    }

    private function upload_product_file()
    {
        // make sure that the temp directory is exist before uploading.
        if (!is_dir(TEMP_DIR_PATH)) {
            mkdir(TEMP_DIR_PATH);
        }

        $file = basename($this->request->files['import']['name']);
        $filename = TEMP_DIR_PATH . $file;

        $extension = pathinfo($filename);

        $file_extension = $extension['extension'];

        if ($extension['basename'] && ($file_extension == 'xlsx' || $file_extension == 'xls' || $file_extension == 'csv')) {
            move_uploaded_file($this->request->files['import']['tmp_name'], $filename);
            return $filename;
        }

        $this->error[] = $this->language->get('ms_import_error_warning');
        return false;
    }

    private function check_product_file_structure($inputFileName)
    {

        $extension = pathinfo($inputFileName);
        $file_extension = $extension['extension'];
        try {
            if ($file_extension == 'csv') {
                $inputFileType = 'CSV';
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            } else {
                $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
            }
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        $this->load->model('multiseller/tool/product_import');

        $product_file_structure_file_name = "";

        switch ($this->session->data['option']) {
            case '0':
                $product_file_structure_file_name = "no_options_product_file_structure.json";
                break;
            case '1':
                $product_file_structure_file_name = "product_file_structure.json";
                break;
            case '2':
                $product_file_structure_file_name = "advanced_options_product_file_structure.json";
                break;
        }

        $expand_product_file_structure_file = file_get_contents(__DIR__ . "/" . $product_file_structure_file_name);
        $file_structure = json_decode($expand_product_file_structure_file, true);
        $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);

        // Check the last import process status.
        $import_data = $this->model_multiseller_tool_product_import->checkImportProcessStatus();

        $mapping_fields = [];
        $uploaded_file_fields = $allDataInSheet[1];
        $expand_file_fields = $file_structure;

        switch ($this->data->session['option']) {
            case '0':
                unset($expand_file_fields['AV']);
                unset($expand_file_fields['AW']);
                break;
        }

        if (count($import_data) > 0 && $import_data['file_mapping'] != "default") {
            $last_product_file_structure = json_decode($import_data['file_mapping'], true);

            foreach ($last_product_file_structure as $cell => $value) {
                if ($value != "0" && array_key_exists($value, $uploaded_file_fields)) {
                    $mapping_fields[$cell] = $value;
                }
            }
            return array($mapping_fields, $expand_file_fields, $uploaded_file_fields);
        }

        // If it's the first time to import products.
        $mapping_fields = array_intersect($expand_file_fields, $uploaded_file_fields);

        foreach ($mapping_fields as $cell => $value) {
            $mapping_fields[$cell] = $cell;
        }

        return array($mapping_fields, $expand_file_fields, $uploaded_file_fields);
    }

    private function process_uploaded_product_file($seller_id, $file, $language_id)
    {

        $this->load->model('multiseller/tool/product_import');

        $file_name = $file;
        $extension = pathinfo($file);
        $file_extension = $extension['extension'];

        $store_id = $this->config->get('config_store_id') ? $this->config->get('config_store_id') : 0;

        // Insert a new record in import_files_tb.
        $data = array(
            'id' => null,
            'filename' => $file,
            'import_type' => 'product',
            'import_date' => date("Y-m-d H:i:s"),
            'file_mapping' => 'default',
            'import_status' => 0,
        );

        if (isset($this->request->post['mapping_form']) && $this->request->post['mapping_form'] == "true") {
            $product_file_structure_user_defined = $this->request->post['product'];
            $data['file_mapping'] = json_encode($product_file_structure_user_defined);
        }

        $file_id = $this->model_multiseller_tool_product_import->add_import_file($data);

        // Run background process.
        $file_location = DIR_SYSTEM . 'library/import_file.php';

        $options = $this->session->data['option'];

        // To check the error fired from importProduct.php
        // 1. remove >/dev/null 2>&1 &
        // 2. put echo before shell_exec
        // 3. put die() after shell_exec
        $storecode = STORECODE;

        // shell_exec("php $file_location \"$file_name\" $file_id $language_id $store_id $options $storecode $seller_id >/dev/null 2>&1 &");
        shell_exec("php $file_location \"$file_name\" $file_id $language_id $store_id $options $storecode $seller_id");
    }
}
