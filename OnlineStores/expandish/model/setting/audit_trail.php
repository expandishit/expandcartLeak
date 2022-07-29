<?php

class ModelSettingAuditTrail extends Model
{
    /**
     *  settings key.
     *
     * @var string
     */
    protected $settingsGroup = 'audit_trail';

    /**
     * Get  settings.
     *
     * @return array|null
     */
    public function getSettings()
    {
        return $this->config->get($this->settingsGroup);
    }

    public function getPageStatus($page)
    {
        $settings = $this->getSettings();

        if(count($settings) > 0 && isset($settings['pages']) && in_array($page,$settings['pages'])){
            return  true ;
        }
        return false;
    }
}
