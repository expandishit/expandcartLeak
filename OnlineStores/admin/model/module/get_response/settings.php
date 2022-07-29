<?php

use ExpandCart\Foundation\Support\Facades\GetResponseFactory as GetResponse;

class ModelModuleGetResponseSettings extends Model
{
    /**
     * The settings key string
     *
     * @var string
     */
    protected $settingsKey = 'get_response';

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var array
     */
    protected $errors = null;

    /**
     * Update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->insertUpdateSetting(
            'module', [$this->settingsKey => $inputs]
        );

        return true;
    }

    /**
     * Get settings.
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

    /**
     * Install and apply the required DB changes.
     *
     * @return void
     */
    public function install()
    {

    }

    /**
     * To drop the application related changes.
     *
     * @return void
     */
    public function uninstall()
    {

    }

    /**
     * Set the api key.
     *
     * @param string $apiKey
     *
     * @return void
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get all tags.
     *
     * @return array
     */
    public function getTags()
    {
        if (!$this->apiKey) {
            return false;
        }

        return json_decode(GetResponse::setApiKey($this->apiKey)->getTags(), true);
    }

    /**
     * Get all campaigns.
     *
     * @return array
     */
    public function getCampaigns()
    {
        if (!$this->apiKey) {
            return false;
        }

        return json_decode(GetResponse::setApiKey($this->apiKey)->getCampaigns(), true);
    }
}
