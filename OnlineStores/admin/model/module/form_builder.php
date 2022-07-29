<?php

class ModelModuleFormBuilder extends Model
{
    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public $errors = [];

    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(
            'form_builder', $inputs
        );

        return true;
    }

    public function install()
    {

    }

    public function uninstall()
    {

    }

    public function validate($inputs)
    {
        $this->load->model('localisation/language');
        $langs = $this->model_localisation_language->getLanguages();
        if( !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $inputs['settings']['email']) )
        {
            $this->errors['email'] = $this->language->get('error_email');
        }
        // check for form title and desc
        foreach ($langs as $lang) {
            foreach ($inputs['settings'][$lang['language_id']] as $key => $value) {
                if(empty($value)){
                    $this->errors["$key"] = $this->language->get('error_'.$key);
                }
            }
        }

        foreach ($inputs['settings']['fields'] as $field) {
            foreach ($field['title'] as $title) {
                if(empty($title)){
                    $this->errors['field_title'] = $this->language->get('error_field_title');
                }
            }
            if($field['type']=='select' || $field['type']=='radio' || $field['type']=='checkbox'){
                foreach ($field['values'] as $options) {
                    foreach ($options as $option) {
                        if(empty($option)){
                            $this->errors['field_option'] = $this->language->get('error_field_option');
                        }
                    }
                }
            }        
        }

        if (count($this->errors) == 0) {
            return true;    
        }
        $this->errors['warning'] = $this->language->get('error_warning');
        return false;
    }
}
