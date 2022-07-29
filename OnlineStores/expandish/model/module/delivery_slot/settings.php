<?php

class ModelModuleDeliverySlotSettings extends Model
{

    public function getSettings()
    {
        return $this->config->get('delivery_slot');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    public function isRequired()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['required'] == 1) {
            return true;
        }

        return false;
    }

    public function isCutOff()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['cutoff'] == 1) {
            return true;
        }

        return false;
    }

}
