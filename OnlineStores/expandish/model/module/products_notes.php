<?php

class ModelModuleProductsNotes extends Model
{
    public function getSettings()
    {
        return $this->config->get('products_notes');
    }

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled(): bool
    {
        return \Extension::isInstalled('products_notes');
    }

    /**
     * Check is App Active
     *
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->isInstalled() && (int) $this->getSettings()['status'] === 1;
    }
}
