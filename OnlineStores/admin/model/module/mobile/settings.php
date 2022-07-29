<?php

use ExpandCart\Foundation\Support\Facades\GetResponseFactory as GetResponse;

class ModelModuleMobileSettings extends Model
{
    /**
     * The settings group string
     *
     * @var string
     */
    protected $settingsGroup = 'firebase';

    /**
     * The settings key string
     *
     * @var string
     */
    protected $settingsKey = 'firebase_notifications';

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $redirectUri;

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
            $this->settingsGroup, [$this->settingsKey => $inputs]
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
     * Set the client id.
     *
     * @param string $clientId
     *
     * @return void
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * Set the client secret.
     *
     * @param string $clientSecret
     *
     * @return void
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * Set the redirect uri.
     *
     * @param string $uri
     *
     * @return void
     */
    public function setRedirectUri($uri)
    {
        $this->redirectUri = $uri;
    }
}
