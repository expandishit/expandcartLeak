<?php

class ModelModuleMinimumDepositSettings extends Model
{


    public function getSettings()
    {
        return $this->config->get('minimum_deposit');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['md_status'] == 1) {
            return true;
        }

        return false;
    }
}
