<?php

class ModelModulePricePerMeterSettings extends Model
{
    public function getSettings()
    {
        return $this->config->get('price_per_meter');
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
