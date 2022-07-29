<?php
/**
*   controller class for custom_fees_for_payment_method application
*   @author Michael
*/
class ControllerModuleCustomFeesForPaymentMethod extends Controller {

    private $langs, $errors;

    /**
    *   Index function to return the template or run $this->submit() if the form was submitted.
    *
    *   @return string $template the template html view or boolean if the operations were successful.
    */
    public function index() {
        
        // load app module
        $this->load->model('module/custom_fees_for_payment_method');

        // Update payment methods.
        $this->model_module_custom_fees_for_payment_method->update_payment_methods_table();

        // load app's language file.
        $this->language->load('module/custom_fees_for_payment_method');

        // get and set all langs
        $this->load->model('localisation/language');

        $this->langs = $this->model_localisation_language->getLanguages();

        //load tax classes
        $this->load->model('localisation/tax_class');
        $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        // check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if ( $this->validate() )
            {
                $this->submit();
                $result_json['success'] = '1';
                $result_json['success_msg']  = $this->language->get('text_success');
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->errors;
            }

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        // set document title
        $this->document->setTitle($this->language->get('heading_title'));

        // -==-==-==-==- breadcrumbs -==-==-==-==-
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/custom_fees_for_payment_method', '', 'SSL'),
            'separator' => ' :: '
        );

        // -==-==-==-==- end of breadcrumbs -==-==-==-==-

        // set tempplate
        $this->template = 'module/custom_fees_for_payment_method.expand';

        // links
        $this->data['action'] = $this->url->link('module/custom_fees_for_payment_method', '', 'SSL');
        $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');

        // set required variables and render the template.
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $extensions = $this->get_activated_payment_methods();
        $payment_methods = array();

        $this->data['current_currency'] = $this->config->get('config_currency');

        // now use the payment lagnauge files.
        $this->language->load('extension/payment');

        // Categories
        $this->load->model('catalog/category');
        $this->data['categories'] = $this->model_catalog_category->getCategories();
        
        foreach ($extensions as $key => $value) {
            $result = $this->model_module_custom_fees_for_payment_method->get_setting($value);
            $title = $this->language->get($value.'_title');

            $fees = explode(':', $result['fees']);

            $fees_value = $fees[0];
            $fees_pcg = 'no';

            if ( isset($fees[1]) && $fees[1] === 'pcg' )
            {
                $fees_pcg = 'yes';
            }
            
            $show_range = explode(':', $result['show_range']);

            $show_range_min = $show_range[0] ? $show_range[0] : 0;
            $show_range_max = $show_range[1] ? $show_range[1] : 0;

            $fees_range = explode(':', $result['fees_range']);

            $fees_range_min = $fees_range[0] ? $fees_range[0] : 0;
            $fees_range_max = $fees_range[1] ? $fees_range[1] : 0;

            $catgs_ids = unserialize($result['catgs_ids']);
            $catgs_case = $result['catgs_case'];

            $tax_class_id=$result['tax_class_id'];
            $payment_methods[$result['code']] = ['fees' => $fees_value, 'fees_pcg'=> $fees_pcg, 'title' => $title, 'show_range_min' => $show_range_min, 'show_range_max' => $show_range_max, 'fees_range_min' => $fees_range_min, 'fees_range_max' => $fees_range_max, 'catgs_ids' => $catgs_ids, 'catgs_case' => $catgs_case,'tax_class_id'=>$tax_class_id];
        }

        $this->data['payment_methods'] = $payment_methods;

        // send all langs to template data.
        $this->data['languages'] = $this->langs;
        $this->data['total_row_names'] = array();

        // Status
        $this->data['cffpm_status'] = $this->config->get('cffpm_status');

        foreach ($this->langs as $lang) {
            $this->data['total_row_names'][$lang['code']] = $this->model_module_custom_fees_for_payment_method->get_setting_from_setting('cffpm_total_row_name_'.$lang['code'])['value'];
        }

        $this->response->setOutput($this->render());
    }

    /**
     *  Adds values to template data.   
     *
     *   @param array $values an array of the values to add to the template data
     *   @return null
    */
    private function add_to_template_data($values = array())
    {
        foreach ($values as $value) {
            $this->data[$value] = $this->language->get($value);
        }
    }

    /**
    *   Validate Data
    *   @return boolean data valid or not.
    */
    private function validate()
    {
        $errors = array();

        $data = $this->format_data($this->request->post);
        foreach ($data as $code => $value) {

            // Fees Validation.
            if ( strlen($value['fees']) > 0 ) {
                
                if ( !ctype_digit($value['fees']) && !is_numeric($value['fees']) ) {
                    $errors[$code.'_fees'] = $this->language->get('error_not_a_number');
                }

                $fees = (float) $value['fees'];

                if ( $fees < 0 ) {
                    $errors[$code.'_fees'] = $this->language->get('error_val_less_than_zero');
                }
            } else {
                $errors[$code.'_fees'] = $this->language->get('error_empty_value');
            }

            // End of Fees Validation.

            // Show Range Minimum Validation.

            if ( strlen($value['show_range_min']) > 0 ) {

                $min = (float) $value['show_range_min'];

                if ( !ctype_digit($min) && !is_numeric($min) && $min != 0 ) {
                    $errors[$code.'_show_range_min'] = $this->language->get('error_not_a_number');

                } else if ( $min < 0 ) {
                    $errors[$code.'_show_range_min'] = $this->language->get('error_val_less_than_zero');
                }

            } else {
                $errors[$code.'_show_range_min'] = $this->language->get('error_empty_value');
            }

            // End of Show Range Minimum Validation.

            // Show Range Maximum Validation

            if ( strlen($value['show_range_max']) > 0 ) {

                if ( $value['show_range_max'] != 'no_max' ) {

                    if ( !ctype_digit($value['show_range_max']) && !is_numeric($value['show_range_max']) ) {
                        $errors[$code.'_show_range_max'] = $this->language->get('error_not_a_number');
                    }

                    if ( !isset($value['show_range_no_max']) ) {
                        $max = (float) $value['show_range_max'];

                        if ( $max < $min ) {
                            $errors[$code.'_show_range_max'] = $this->language->get('error_max_less_than_min');

                        } else if ( $max < 0 ) {
                            $errors[$code.'_show_range_max'] = $this->language->get('error_val_less_than_zero');
                        }
                    }
                }

            } else {
                $errors[$code.'_show_range_max'] = $this->language->get('error_empty_value');
            }
            // End of Show Range Maximum Validation

            // Fees Range Minimum Validation

            if ( strlen($value['fees_range_min']) > 0 ) {

                $min = (float) $value['fees_range_min'];

                if ( !ctype_digit($min) && !is_numeric($min) && $min != 0 ) {
                    $errors[$code.'_fees_range_min'] = $this->language->get('error_not_a_number');

                } else if ( $min < 0 ) {
                    $errors[$code.'_fees_range_min'] = $this->language->get('error_val_less_than_zero');
                }

            } else {
                $errors[$code.'_fees_range_min'] = $this->language->get('error_empty_value');
            }

            // End of Fees Range Minimum Validation

            // Fees Range Maximum Validation

            if ( strlen($value['fees_range_max']) > 0 ) {

                if ( $value['fees_range_max'] != 'no_max' ) {

                    if ( !ctype_digit($value['fees_range_max']) && !is_numeric($value['fees_range_max']) ) {
                        $errors[$code.'_fees_range_max'] = $this->language->get('error_not_a_number');
                    }

                    if ( !isset($value['fees_range_no_max']) ) {
                        $max = (float) $value['fees_range_max'];

                        if ( $max < $min ) {
                            $errors[$code.'_fees_range_max'] = $this->language->get('error_max_less_than_min');

                        } else if ( $max < 0 ) {
                            $errors[$code.'_fees_range_max'] = $this->language->get('error_val_less_than_zero');
                        }
                    }
                }

            } else {
                $errors[$code.'_fees_range_max'] = $this->language->get('error_empty_value');
            }

            // End of Fees Range Maximum Validation
        }

        $this->errors = $errors;

        if ( !empty($errors) ) {
            $this->data['error_warning'] = $this->language->get('error_an_error_happened');
            $this->data['errors'] = $errors;
            return false;
        }

        return true;
    }

    /**
    *   Function to run when the form is submitted
    *   @return boolean always true.
    */
    private function submit()
    {
        
        $data = $this->format_data($this->request->post);

        // status
        $this->model_module_custom_fees_for_payment_method->add_setting_to_setting('cffpm_status', $this->request->post['cffpm_status']);

        if ( $this->model_module_custom_fees_for_payment_method->add_settings($data) ) {

            foreach ($this->langs as $lang) {
                $total_name = $this->request->post['setting_total_row_name_'.$lang['code']];
                $this->model_module_custom_fees_for_payment_method->add_setting_to_setting('cffpm_total_row_name_'.$lang['code'], $total_name);
            }

            $this->data['success_message'] = $this->language->get('text_success');
        } else {
            $this->data['error_warning'] = $this->language->get('error_warning');
        }

        return true;
    }

    /**
    *   Format the data in a good structure. $payment_method_code => ['fees': $x, 'show_range': $y, 'fees_range': $z];
    *
    *   @param array $unformatted the data to be formatted.
    *   @return array $data the formatted data.
    */
    private function format_data($unformatted)
    {
        $data = array();
        foreach ($unformatted as $key => $value) {

            if (strpos($key, '_fees') !== false) {
                $code = str_replace('_fees', '', $key);
                $data[$code] = array();
                $data[$code]['fees'] = $value;

            } else if ( strpos($key, '_pcg_of_subtotal') !== false ) {

                if ( $value == 1 )
                {
                    $data[$code]['fees_pcg'] = 'yes';
                }

            } else if (strpos($key, '_show_range_min') !== false) {
                $code = str_replace('_show_range_min', '', $key);
                $data[$code]['show_range_min'] = $value;

            } else if (strpos($key, '_fees_range') !== false) {
                $code = str_replace('_fees_range', '', $key);
                $data[$code]['fees_range'] = $value;

            } else if ( strpos($key, '_show_range_no_max') !== false ) {

                $code = str_replace('_show_range_no_max', '', $key);
                if ( $value == 1 )
                {
                    $data[$code]['show_range_max'] = 'no_max';
                }

            } else if ( strpos($key, '_show_range_max') !== false ) {

                $code = str_replace('_show_range_max', '', $key);
                $data[$code]['show_range_max'] = $value;

            } else if ( strpos($key, '_feez_range_no_max') !== false ) {

                $code = str_replace('_feez_range_no_max', '', $key);
                if ( $value == 1 )
                {
                    $data[$code]['fees_range_max'] = 'no_max';
                }

            } else if ( strpos($key, '_feez_range_max') !== false ) {

                $code = str_replace('_feez_range_max', '', $key);
                $data[$code]['fees_range_max'] = $value;

            } else if ( strpos($key, '_feez_range_min') !== false ) {

                $code = str_replace('_feez_range_min', '', $key);
                $data[$code]['fees_range_min'] = $value;
            } else if ( strpos($key, '_catgs_ids') !== false ) {
                $code = str_replace('_catgs_ids', '', $key);
                $data[$code]['catgs_ids'] = array_unique($value);
            } else if ( strpos($key, '_catgs_case') !== false ) {
                $code = str_replace('_catgs_case', '', $key);
                $data[$code]['catgs_case'] = $value;
            }
            else if (strpos($key, '_tax_class_id') !== false) {
                $code = str_replace('_tax_class_id', '', $key);
                $data[$code]['tax_class_id'] = $value;
            } 
        }

        return $data;
    }

    /**
    *   Get all activated payment methods.
    *
    *   @return array $extensions.
    */
    private function get_activated_payment_methods()
    {
        $this->load->model('setting/extension');
        $extensions = $this->model_setting_extension->getInstalled('payment');

        foreach ($extensions as $key => $value) {
            // determine if the extension is installed or not.
            // same check is used on `admin/controller/extension/payment.php:112`.

            $settings = $this->config->get($value);
            if ($settings && is_array($settings) == true) {
                $status = $settings['status'];
            } else {
                $status = $this->config->get($value . '_status');
            }

            if (!$status) {
                unset($extensions[$key]);
            }
        }

        return $extensions;
    }

    public function install() 
    {
        $this->load->model('module/custom_fees_for_payment_method');
        $this->model_module_custom_fees_for_payment_method->install();
    }

    public function uninstall() 
    {
        $this->load->model('module/custom_fees_for_payment_method');
        $this->model_module_custom_fees_for_payment_method->uninstall();
    }
}
