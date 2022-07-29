<?php

class ModelModuleCategoryDroplist extends Model
{
    public function getSettings()
    {
        return $this->config->get('category_droplist');
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