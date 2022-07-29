<?php

namespace ExpandCart\Foundation\Support\Factories\GetResponse;

use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzleClient;
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
    protected $endPoint = 'https://api.getresponse.com/v3/';

    /**
     * Set the api key.
     *
     * @param string $apiKey
     *
     * @return Factory
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get the api key.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Add a contact.
     *
     * @param array $parameters
     *
     * @return string|bool
     */
    public function addContact($parameters)
    {
        return $this->call('contacts', $parameters, 'POST');
    }

    /**
     * Get a contact by contact id.
     *
     * @param string $contactId
     *
     * @return string|bool
     */
    public function getContact($contactId)
    {
        return $this->call(('contacts/' . $contactId), [], 'GET');
    }

    /**
     * Update contact data.
     *
     * @param string $contactId
     * @param array $parameters
     *
     * @return string|bool
     */
    public function updateContact($contactId, $parameters)
    {
        return $this->call(('contacts/' . $contactId), $parameters, 'POST');
    }

    /**
     * Update contact tags.
     *
     * @param string $contactId
     * @param array $parameters
     *
     * @return string|bool
     */
    public function updateContactTags($contactId, $parameters)
    {
        return $this->call('contacts/' . $contactId . '/tags', $parameters, 'POST');
    }

    /**
     * Search for contacts by email.
     *
     * @param string $email
     *
     * @return string|bool
     */
    public function searchContactByEmail($email)
    {
        return $this->call('contacts', [
            'query' => ['email' => $email],
        ], 'GET');
    }

    /**
     * Insert a new tag.
     *
     * @param array $parameters
     *
     * @return string|bool
     */
    public function addTag($parameters)
    {
        return $this->call('tags', $parameters, 'POST');
    }

    /**
     * List all tags.
     *
     * @param array $parameters
     *
     * @return string|bool
     */
    public function getTags($parameters = [])
    {
        return $this->call('tags', $parameters, 'GET');
    }

    /**
     * Delete a tag.
     *
     * @param array $parameters
     *
     * @return string|bool
     */
    public function deleteTag($parameters)
    {
        return $this->call('tags/' . $parameters['tagId'], [], 'DELETE');
    }

    /**
     * List all campaigns.
     *
     * @param array $parameters
     *
     * @return string|bool
     */
    public function getCampaigns($parameters = [])
    {
        return $this->call('campaigns', $parameters, 'GET');
    }

    /**
     * Get request header.
     *
     * @return array
     */
    protected function getHeaders()
    {
        return [
            'X-Auth-Token' => 'api-key ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Perform the request.
     *
     * @param string $apiMethod
     * @param array $parameters
     * @param string $httpMethod
     *
     * @return string|bool
     */
    private function call($apiMethod, $parameters, $httpMethod)
    {
        $client = new GuzzleClient([
            'base_uri' => $this->endPoint,
            'headers' => $this->getHeaders()
        ]);

        $httpMethod = strtolower($httpMethod);

        try {

            switch ($httpMethod) {
                case 'get': {
                    if (empty($parameters) == false) {
                        $apiMethod = $apiMethod . '?' . http_build_query($parameters);
                    }
                    $result = $client->get($apiMethod);
                    break;
                }
                case 'post': {
                    $result = $client->post($apiMethod, [
                        'json' => $parameters
                    ]);
                    break;
                }
                case 'delete': {
                    $result = $client->delete($apiMethod);
                    break;
                }
                default: {
                    throw new \Exception('Invalid http method');
                }
            };

            return $result->getBody()->getContents();

        } catch (RequestException $e) {
            /*echo Psr7\str($e->getRequest()) . "<hr />";
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse()) . "<br />";
            }*/
            return false;
        }
    }
}