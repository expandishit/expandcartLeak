<?php

class ModelNetworkMarketingSettings extends Model
{
    private $statusField = 'nm_status';

    public function getSettings()
    {
        return $this->config->get('network_marketing');
    }

    public function appStatus()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings[$this->statusField] == 1) {
            return true;
        }

        return false;
    }
}
