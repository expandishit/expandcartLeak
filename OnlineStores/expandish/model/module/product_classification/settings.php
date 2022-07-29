<?php

class ModelModuleProductClassificationSettings extends Model
{


    public function getSettings()
    {
        return $this->config->get('product_classification');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }
}
