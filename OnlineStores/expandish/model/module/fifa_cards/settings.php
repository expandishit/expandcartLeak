<?php

class ModelModuleFifaCardsSettings extends Model
{
    public function getSettings()
    {
        return $this->config->get('fifa_cards');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1 && \Extension::isInstalled('fifa_cards')) {
            return true;
        }

        return false;
    }
}
