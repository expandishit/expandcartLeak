<?php

class ModelModuleRentalProductsSettings extends Model
{
    public function getSettings()
    {
        return $this->config->get('rental_products');
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
