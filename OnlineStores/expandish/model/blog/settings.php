<?php

class ModelBlogSettings extends Model
{
    /**
     * The main application setting group name.
     *
     * @var string
     */
    public $settingsIndex = 'flash_blog';

    /**
     * errors array.
     *
     * @var array
     */
    public $settings = [];

    /**
     * get the blog settings.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings = $this->config->get($this->settingsIndex);
    }

    /**
     * check if the blog extension is enabled.
     *
     * @return bool
     */
    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }
}
