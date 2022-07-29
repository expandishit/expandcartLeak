<?php

class ModelShippingSalasaSettings extends Model
{
    /**
     * the settings group string
     *
     * @var string
     */
    protected $settingsGroup = 'salasa';

    /**
     * @var array
     */
    protected $errors = null;

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->insertUpdateSetting(
            'shipping', [$this->settingsGroup => $inputs]
        );

        return true;
    }

    /**
     * Get payment settings.
     *
     * @return array|bool
     */
    public function getSettings()
    {
        return ($this->config->get($this->settingsGroup) ?: []);
    }

    /**
     * validate inputs
     *
     * @param array $data
     *
     * return bool
     */
    public function validate($data)
    {
        $this->language->load('shipping/salasa');
        if(!$data['name']){
            $this->errors['salasa_name_error'] = $this->language->get('entry_name_error');
        }
        if(!$data['account_id']){
            $this->errors['salasa_account_id_error'] = $this->language->get('entry_account_id_error');
        }
        if(!$data['account_key']){
            $this->errors['salasa_account_key_error'] = $this->language->get('entry_account_key_error');
        }
        if(!$data['warehouse']){
            $this->errors['salasa_warehouse_error'] = $this->language->get('entry_warehouse_error');
        }
        if(!$data['salasa_weight_rate_class_id']){
            $this->errors['salasa_rate_error'] = $this->language->get('entry_rate_error');
        }
        return $this->errors ? false : true;
    }

    /**
     * Push an error to the errors array.
     *
     * @param mixed $error
     *
     * @return void
     */
    public function setError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * Get all errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return is_array($this->errors) ? $this->errors : [];
    }
}
