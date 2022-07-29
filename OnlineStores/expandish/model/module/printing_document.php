<?php

class ModelModulePrintingDocument extends Model
{
    public function getSettings()
    {
        return $this->config->get('printing_document_module');
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
