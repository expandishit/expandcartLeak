<?php

use ExpandCart\Foundation\Support\Factories\Google\ClientFactory;

class ModelModuleMobileProjects extends ModelModuleMobileSettings
{
    /**
     * Create the auth link.
     *
     * @param array $data
     *
     * @return string
     */
    public function getAuthLink($data)
    {
        return ClientFactory::createAuthUrl(
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri,
            [
                'domain' => HTTPS_SERVER,
                'storeCode' => STORECODE,
                'redirect' => 'project',
                'data' => $data,
            ]
        );
    }

    /**
     * Publish a new notification.
     *
     * @param string $code
     *
     * @return array
     */
    public function publishProject($code)
    {
        return ClientFactory::createFullProject(
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri,
            $code
        );
    }

    public function insertSettings($projectId)
    {
        return $this->updateSettings([
            'projectId' => $projectId
        ]);
    }
}
