<?php
class ModelPaymentMobipaySettings extends Model
{
    /**
     * the settings group.
     *
     * @var string
     */
    protected $settingsGroup = 'payment';
    /**
     * the settings key.
     *
     * @var string
     */
    protected $settingsKey = 'mobipay';
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
            $this->settingsGroup, [$this->settingsKey => $inputs]
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
        return ($this->config->get($this->settingsKey) ?: []);
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
        return true;
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