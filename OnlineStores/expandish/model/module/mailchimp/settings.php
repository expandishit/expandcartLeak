<?php

use ExpandCart\Foundation\Support\Factories\Mailchimp\Factory;

class ModelModuleMailchimpSettings extends Model
{
    /**
     * The settings key string
     *
     * @var string
     */
    protected $settingsKey = 'mailchimp';

    /**
     * @var array
     */
    protected static $settings = null;

    /**
     * Get settings.
     *
     * @return array|bool
     */
    public function getSettings()
    {
        if (!self::$settings) {
            self::$settings = ($this->config->get($this->settingsKey) ?: []);
        }

        return self::$settings;
    }

    /**
     * Check if the module is active or not.
     *
     * @return bool
     */
    public function isActive()
    {
        $this->getSettings();

        if (
            isset(self::$settings['api_key']) == false ||
            empty(self::$settings['api_key'])
        ) {
            return false;
        }

        return self::$settings['status'];
    }

    /**
     * Check if the module has a specific tag.
     *
     * @param string $tag
     *
     * @return bool
     */
    public function hasTag($tag)
    {
        $this->getSettings();

        return isset(self::$settings[$tag . '_tag']) ? true : false;
    }

    /**
     * Resolve the sucscriber hash
     *
     * @param string $email
     *
     * @return string
     */
    public function getSubscriberHash($email)
    {
        return md5(strtolower($email));
    }

    /**
     * Resolve the sucscriber hash
     *
     * @param array $data
     * @param mixed $data
     * @param string $subscriberHash
     *
     * @return array|bool
     */
    public function addNewSubscriber($data, $tag, $subscriberHash = null)
    {
        $settings = self::$settings;

        $mailchimp = new Factory(
            $settings['api_key'],
            $settings['username'],
            $settings['list_id'],
            $subscriberHash,
            $settings['subscription_status'],
            $settings['dc']
        );

        return $mailchimp->addNewSubscriber($data, $tag);
    }

    /**
     * Check if an subscriber is exists or not.
     *
     * @param string $email
     *
     * @return array|bool
     */
    public function subscriberExists($email)
    {
        $settings = self::$settings;

        $mailchimp = new Factory(
            $settings['api_key'],
            $settings['username'],
            $settings['list_id'],
            $this->getSubscriberHash($email),
            $settings['subscription_status'],
            $settings['dc']
        );

        return $mailchimp->subscriberExists();
    }

    /**
     * Update a subscriber tags list.
     *
     * @param array $tags
     * @param string $email
     *
     * @return array|bool
     */
    public function updateSubscriberTags($tags, $email)
    {
        $settings = self::$settings;

        $mailchimp = new Factory(
            $settings['api_key'],
            $settings['username'],
            $settings['list_id'],
            $this->getSubscriberHash($email),
            $settings['subscription_status'],
            $settings['dc']
        );

        return $mailchimp->updateSubscriberTags($tags);
    }
}
