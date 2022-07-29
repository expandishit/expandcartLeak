<?php

namespace ExpandCart\Foundation\Support\Factories\Mailchimp;

use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

final class Factory
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $listId;

    /**
     * @var string
     */
    protected $subscriberHash;

    /**
     * @var string
     */
    protected $subscriptionStatus;

    /**
     * @var string
     */
    protected $dc;

    /**
     * Constructor.
     *
     * @param string $apiKey
     * @param string $username
     * @param string $listId
     * @param string $subscriberHash
     * @param string $subscriptionStatus
     * @param string $dc
     *
     * @return void
     */
    public function __construct($apiKey, $username, $listId, $subscriberHash, $subscriptionStatus, $dc = false)
    {
        $this->username = $username;

        $this->apiKey = $apiKey;

        $this->listId = $listId;

        $this->subscriberHash = $subscriberHash;

        if (isset($subscriptionStatus) == false || empty($subscriptionStatus)) {
            $this->subscriptionStatus = 'pending';
        } else {
            $this->subscriptionStatus = $subscriptionStatus;
        }

        $this->dc = (isset($dc) == false || empty($dc)) ? $this->resolveDc($apiKey) : $dc;
    }

    /**
     * Set the api key.
     *
     * @param string $apiKey
     *
     * @return void
     */
    public function setApikey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Resolve dc from a given api key.
     *
     * @param string $apiKey
     *
     * @return void
     */
    public function resolveDc($apiKey)
    {
        return explode('-', $apiKey)[1];
    }

    /**
     * Set the username.
     *
     * @param string $username
     *
     * @return void
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Set the list id.
     *
     * @param string $listId
     *
     * @return void
     */
    public function setListId($listId)
    {
        $this->listId = $listId;
    }

    /**
     * Set the subscriber hash.
     *
     * @param string $subscriberHash
     *
     * @return void
     */
    public function setSubscriberHash($subscriberHash)
    {
        $this->subscriberHash = $subscriberHash;
    }

    /**
     * Resolve the base uri.
     *
     * @return string
     */
    public function getBaseUri()
    {
        return sprintf('https://%s.api.mailchimp.com/3.0/', $this->dc);
    }

    /**
     * Add new subscriber.
     *
     * @param array $data
     * @param array $tags
     *
     * @return array|bool
     */
    public function addNewSubscriber($data, $tags)
    {
        $client = new Client([
            'base_uri' => $this->getBaseUri()
        ]);

        if (is_array($tags) == false) {
            $tags = [$tags];
        }

        try {
            $result = $client->post(sprintf('lists/%s/members', $this->listId), [
                'json' => [
                    'email_address' => $data['email'],
                    'status' => $this->subscriptionStatus,
                    'tags' => $tags
                ],
                'auth' => [
                    $this->username, $this->apiKey
                ]
            ]);

            return json_decode($result->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * Check if an subscriber is exists or not.
     *
     * @return array|bool
     */
    public function subscriberExists()
    {
        $client = new Client([
            'base_uri' => $this->getBaseUri()
        ]);

        try {
            $result = $client->get(sprintf('lists/%s/members/%s', $this->listId, $this->subscriberHash), [
                'auth' => [
                    $this->username, $this->apiKey
                ]
            ]);

            return json_decode($result->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * Update a subscriber tags list.
     *
     * @param array $tags
     *
     * @return array|bool
     */
    public function updateSubscriberTags($tags)
    {
        $client = new Client([
            'base_uri' => $this->getBaseUri()
        ]);

        try {
            $result = $client->post(sprintf('lists/%s/members/%s/tags', $this->listId, $this->subscriberHash), [
                'json' => [
                    'tags' => $tags
                ],
                'auth' => [
                    $this->username, $this->apiKey
                ]
            ]);

            return json_decode($result->getBody()->getContents(), true);
        } catch (RequestException $e) {
            echo Psr7\str($e->getRequest()) . "<hr />";
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse()) . "<br />";
            }
            exit;
            return false;
        }
    }
}
