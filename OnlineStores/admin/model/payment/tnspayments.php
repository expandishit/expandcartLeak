<?php

class ModelPaymentTNSPayments extends Model
{
    /**
     * the settings group string
     *
     * @var string
     */
    protected $settingsGroup = 'tnspayments';

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
            'payment', [$this->settingsGroup => $inputs]
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
        if (!isset($data['merchant_id']) || strlen($data['merchant_id']) < 1) {
            $this->setError(
                $this->language->get('invalid_merchant_id')
            );
        }

        if (!isset($data['secret_hash']) || strlen($data['secret_hash']) < 1) {
            $this->setError(
                $this->language->get('invalid_secret_hash')
            );
        }

        if (count($this->errors) > 0) {
            $this->errors['warning'] = $this->language->get('invalid_inputs');

            return false;
        }

        return true;
    }

    public function setError($error)
    {
        $this->errors[] = $error;
    }

    public function getErrors()
    {
        return is_array($this->errors) ? $this->errors : [];
    }
}
