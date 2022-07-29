<?php

use ExpandCart\Foundation\Support\Facades\GetResponseFactory as GetResponse;

class ModelModuleGetResponseSettings extends Model
{
    /**
     * The settings key string
     *
     * @var string
     */
    protected $settingsKey = 'get_response';

    /**
     * @var array
     */
    protected $settings = null;

    /**
     * Get settings.
     *
     * @return array|bool
     */
    public function getSettings()
    {
        return $this->settings = ($this->config->get($this->settingsKey) ?: []);
    }

    /**
     * Check if the module is active or not.
     *
     * @return bool
     */
    public function isActive()
    {
        if (!$this->settings) {
            $this->getSettings();
        }

        if (
            isset($this->settings['api_key']) == false ||
            empty($this->settings['api_key'])
        ) {
            return false;
        }

        return $this->settings['status'];
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
        if (!$this->settings) {
            $this->getSettings();
        }

        return isset($this->settings[$tag . '_tag_id']) ? true : false;
    }

    /**
     * a factory method to add a new contact.
     *
     * @param array $data
     * @param mixed $tags
     *
     * @return string|bool
     */
    public function addContact($data, $tags)
    {
        if (!$this->settings) {
            $this->getSettings();
        }

        if (is_array($tags) == false) {
            $tags = [$tags];
        }

        $contact = [];

        $contact['name'] = $data['firstname'] . ' ' . $data['lastname'];
        $contact['email'] = $data['email'];
        $contact['campaign'] = [
            'campaignId' => $this->settings['campaign_id']
        ];

        foreach ($tags as $tag) {
            $contact['tags'][] = [
                'tagId' => $this->settings[$tag . '_tag_id']
            ];
        }

        return GetResponse::setApiKey($this->settings['api_key'])->addContact($contact);
    }

    /**
     * a factory method to check if an account is exists.
     *
     * @param string $email
     *
     * @return string|bool
     */
    public function accountExists($email)
    {
        if (!$this->settings) {
            $this->getSettings();
        }

        $response = GetResponse::setApiKey($this->settings['api_key'])->searchContactByEmail($email);

        $response = json_decode($response, true);

        if (empty($response) == false) {

            $contact = GetResponse::setApiKey($this->settings['api_key'])->getContact($response[0]['contactId']);

            return json_decode($contact, true);
        }

        return false;
    }

    /**
     * a factory method to update a contact tags.
     *
     * @param string $contactId
     * @param mixed $tags
     *
     * @return string|bool
     */
    public function updateContactTags($contactId, $tags)
    {
        if (!$this->settings) {
            $this->getSettings();
        }

        if (is_array($tags) == false) {
            $tags = [$tags];
        }

        $contact = [];

        foreach ($tags as $tag) {
            $contact['tags'][] = [
                'tagId' => $this->settings[$tag . '_tag_id']
            ];
        }

        return GetResponse::setApiKey($this->settings['api_key'])->updateContactTags($contactId, $contact);
    }

    /**
     * a factory method to update a contact.
     *
     * @param array $data
     * @param mixed $tags
     *
     * @return string|bool
     */
    public function updateContact($data, $tags)
    {
        if (!$this->settings) {
            $this->getSettings();
        }

        $contact = [];

        $contact['name'] = $data['name'];
        $contact['email'] = $data['email'];
        $contact['campaign'] = [
            'campaignId' => $data['campaign']['campaignId']
        ];

        foreach ($tags as $tag) {
            $contact['tags'][] = [
                'tagId' => $this->settings[$tag . '_tag_id']
            ];
        }

        return GetResponse::setApiKey($this->settings['api_key'])->updateContact($data['contactId'], $contact);
    }
}
