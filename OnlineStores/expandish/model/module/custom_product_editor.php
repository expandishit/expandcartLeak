<?php

class ModelModuleCustomProductEditor extends Model
{
    /**
     * Check is App Active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->isInstalled() && (int) $this->getSettings()['status'] === 1;
    }

    /**
     * Custom Editor Settings
     *
     * @return array
     */
    public function getSettings()
    {
        $settings =  $this->config->get('custom_product_editor') ?? [];
        return array_merge(['status' => 0], $settings);
    }

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return \Extension::isInstalled('custom_product_editor');
    }
}
