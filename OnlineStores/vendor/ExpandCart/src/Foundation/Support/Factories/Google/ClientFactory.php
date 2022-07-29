<?php

namespace ExpandCart\Foundation\Support\Factories\Google;

use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Messaging\{CloudMessage, Notification, MessageTarget, WebPushConfig};

final class ClientFactory
{
    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * Setup a new client.
     *
     * @param string $domain
     * @param string $token
     *
     * @return ClientFactory
     */
    public function setClient($domain, $token)
    {
        $this->client = new GuzzleClient([
            'base_uri' => $domain,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-type' => 'application/json'
            ]
        ]);

        return $this;
    }

    /**
     * Static factory method to create Auth Url.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectURI
     * @param mixed $state
     *
     * @return string
     */
    public static function createAuthUrl($clientId, $clientSecret, $redirectURI, $state)
    {
        if (is_array($state)) {
            $state = base64_encode(json_encode($state));
        }

        $client = new \Google_Client();
        $client->setClientId(trim($clientId));
        $client->setClientSecret(trim($clientSecret));
        $client->setRedirectUri($redirectURI);
        $client->setState($state);
        $client->setScopes([
            'https://www.googleapis.com/auth/cloud-platform'
        ]);
        return $client->createAuthUrl();
    }

    public function setServiceAccount($serviceAccount)
    {
        $this->serviceAccount = $serviceAccount;
    }

    /**
     * Publish a new message.
     *
     * @param array $devices
     * @param array $data
     *
     * @return array|bool
     */
    public static function pushNotification($devices, $data)
    {
        $serviceAccount = DIR_FIREBASE . 'serviceAccountKey.json';
        $firebase = (new Factory)
            ->withServiceAccount(ServiceAccount::fromJsonFile($serviceAccount))
            ->create();

        $messaging = $firebase->getMessaging();

        $message = CloudMessage::new()->withNotification($data['notification']); // title and body
            
        if ($data['data']) {
            $message = $message->withData($data['data']); // data optional
        }
    
        $sendReport = $messaging->sendMulticast($message, $devices);
        return true;
    }

    /**
     * Factory method to send message to a specific topic
     *
     * @param arary $data
     *
     * @return bool
     */
    public static function pushNotificationToTopic($data)
    {
        $serviceAccount = DIR_FIREBASE . 'serviceAccountKey.json';
        $firebase = (new Factory)
            ->withServiceAccount(ServiceAccount::fromJsonFile($serviceAccount))
            ->create();

        $messaging = $firebase->getMessaging();

        $message = CloudMessage::withTarget('topic', $data['topic_name'])
            ->withNotification($data)
            ->withApnsConfig([
                'payload' => [
                    'aps' => [
                        'sound' => 'default'
                    ],
                ]
            ])
            ->withAndroidConfig([
                'notification' => [
                    'sound' => 'default'
                ]
            ]);

        if ($data['data']) {
            $message = $message->withData($data['data']);
        }

        $messaging->send($message);

        return true;
    }

    /**
     * Publish a new message.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectURI
     * @param string $code
     * @param string $projectId
     * @param array $data
     *
     * @return array|bool
     */
    public static function publish($clientId, $clientSecret, $redirectURI, $code, $projectId, $data)
    {
        $client = new \Google_Client();
        $client->setClientId(trim($clientId));
        $client->setClientSecret(trim($clientSecret));
        $client->setRedirectUri($redirectURI);
        $client->addScope([
            'https://www.googleapis.com/auth/firebase.messaging',
        ]);

        $authenticateToken = $client->authenticate($code);

        $google = new GuzzleClient([
            'base_uri' => 'https://fcm.googleapis.com',
            'headers' => [
                'Authorization' => 'Bearer ' . $authenticateToken['access_token'],
                'Content-type' => 'application/json'
            ]
        ]);

        $message = [
            'validate_only' => false,
            'message' => [
                'topic' => $data['topic'],
                'name' => $data['name'],
                'notification' => [
                    'title' => $data['title'],
                    'body' => $data['body']
                ]
            ]
        ];

        if (in_array($data['type'], [0, 1])) {
            $message['message']['android'] = [
                'collapse_key' => 'aaaa',
                'priority' => 'normal',
                'ttl' => '3600s',
                'notification' => [
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'icon' => 'stock_ticker_update',
                    'color' => '#f45342',
                ]
            ];
        }

        if (in_array($data['type'], [0, 2])) {
            $message['message']['apns'] = [
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => $data['title'],
                            'body' => $data['body'],
                        ],
                        'badge' => 42,
                    ],
                ]
            ];
        }

        try {
            $result = $google->post('/v1/projects/' . $projectId . '/messages:send', [
                'json' => $message
            ]);

            return json_decode($result->getBody()->getContents(), true);
        } catch (RequestException $e) {
            /*echo Psr7\str($e->getRequest()) . "<hr />";
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse()) . "<br />";
            }*/

            return false;
        }
    }

    /**
     * Create a new project.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectURI
     * @param string $code
     *
     * @return array|bool
     */
    public function createStandaloneProject($clientId, $clientSecret, $redirectURI, $code)
    {
        $client = new \Google_Client();
        $client->setClientId(trim($clientId));
        $client->setClientSecret(trim($clientSecret));
        $client->setRedirectUri($redirectURI);
        $client->setScopes([
            'https://www.googleapis.com/auth/cloudplatformprojects',
            'https://www.googleapis.com/auth/firebase'
        ]);

        $authenticateToken = $client->authenticate($code);

        $google = new GuzzleClient([
            'base_uri' => 'https://cloudresourcemanager.googleapis.com/v1/projects',
            'headers' => [
                'Authorization' => 'Bearer ' . $authenticateToken['access_token'],
                'Content-type' => 'application/json'
            ]
        ]);

        $storeCode = strtolower(STORECODE);
        $projectId = 'project-' . $storeCode;

        $project = [
            'name' => $projectId,
            'projectId' => $projectId
        ];

        try {
            $result = $google->post('', [
                'json' => $project
            ]);

            $firebase = new GuzzleClient([
                'base_uri' => 'https://firebase.googleapis.com',
                'headers' => [
                    'Authorization' => 'Bearer ' . $authenticateToken['access_token'],
                    'Content-type' => 'application/json'
                ]
            ]);

            $firebaseResult = $firebase->post('/v1beta1/' . $projectId . ':addFirebase', [
                'json' => [
                    'timeZone' => 'America/Los_Angeles',
                    'regionCode' => 'US',
                    'locationId' => 'us-central'
                ]
            ]);

            return json_decode($result->getBody()->getContents(), true);
        } catch (RequestException $e) {
            /*echo Psr7\str($e->getRequest()) . "<hr />";
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse()) . "<br />";
            }*/

            return false;
        }
    }

    /**
     * Create a new android application.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectURI
     * @param string $code
     * @param string $projectId
     *
     * @return array|bool
     */
    public static function createStandaloneAndroidApp($clientId, $clientSecret, $redirectURI, $code, $projectId)
    {
        $client = new \Google_Client();
        $client->setClientId(trim($clientId));
        $client->setClientSecret(trim($clientSecret));
        $client->setRedirectUri($redirectURI);
        $client->addScope([
            'https://www.googleapis.com/auth/firebase',
        ]);

        $authenticateToken = $client->authenticate($code);

        $google = new GuzzleClient([
            'base_uri' => 'https://firebase.googleapis.com',
            'headers' => [
                'Authorization' => 'Bearer ' . $authenticateToken['access_token'],
                'Content-type' => 'application/json'
            ]
        ]);

        $application = [
            'displayName' => 'Andriod App for ' . STORECODE,
            'packageName' => 'ec.andriod.' . strtolower(STORECODE),
        ];

        try {
            $result = $google->post('/v1beta1/projects/' . $projectId . '/androidApps', [
                'json' => $application
            ]);

            return json_decode($result->getBody()->getContents(), true);
        } catch (RequestException $e) {
            /*echo Psr7\str($e->getRequest()) . "<hr />";
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse()) . "<br />";
            }*/

            return false;
        }
    }



    /**
     * Create a new ios application.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectURI
     * @param string $code
     * @param string $projectId
     *
     * @return array|bool
     */
    public static function createStandaloneIOSApp($clientId, $clientSecret, $redirectURI, $code, $projectId)
    {
        $client = new \Google_Client();
        $client->setClientId(trim($clientId));
        $client->setClientSecret(trim($clientSecret));
        $client->setRedirectUri($redirectURI);
        $client->addScope([
            'https://www.googleapis.com/auth/firebase',
        ]);

        $authenticateToken = $client->authenticate($code);

        $google = new GuzzleClient([
            'base_uri' => 'https://firebase.googleapis.com',
            'headers' => [
                'Authorization' => 'Bearer ' . $authenticateToken['access_token'],
                'Content-type' => 'application/json'
            ]
        ]);

        $application = [
            'displayName' => 'IOS App for ' . STORECODE,
            'bundleId' => 'ec.ios.' . strtolower(STORECODE),
        ];

        try {
            $result = $google->post('/v1beta1/projects/' . $projectId . '/iosApps', [
                'json' => $application
            ]);

            return json_decode($result->getBody()->getContents(), true);
        } catch (RequestException $e) {
            /*echo Psr7\str($e->getRequest()) . "<hr />";
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse()) . "<br />";
            }*/

            return false;
        }
    }

    /**
     * Create a full project with it's applications.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectURI
     * @param string $code
     *
     * @return string|bool
     */
    public function createFullProject($clientId, $clientSecret, $redirectURI, $code)
    {
        $client = new \Google_Client();
        $client->setClientId(trim($clientId));
        $client->setClientSecret(trim($clientSecret));
        $client->setRedirectUri($redirectURI);
        $client->addScope([
            'https://www.googleapis.com/auth/cloudplatformprojects',
            'https://www.googleapis.com/auth/firebase',
        ]);

        $token = $client->authenticate($code);

        $projectId = 'project-' . strtolower(STORECODE);

        try {
            $this->setClient('https://cloudresourcemanager.googleapis.com/v1/projects', $token)
                ->createProject($projectId);

            $this->setClient('https://firebase.googleapis.com', $token);

            $this->addFirebaseToProject($projectId);

            $this->createAndroidApp($projectId);

            return $projectId;
        } catch (RequestException $e) {
            /*echo Psr7\str($e->getRequest()) . "<hr />";
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse()) . "<br />";
            }*/

            return false;
        }
    }

    /**
     * Create a new project.
     *
     * @param string $projectId
     *
     * @return array
     */
    public function createProject($projectId)
    {
        $project = [
            'name' => $projectId,
            'projectId' => $projectId
        ];

        $result = $this->client->post('', [
            'json' => $project
        ]);

        return json_decode($result->getBody()->getContents(), true);
    }

    /**
     * Add firebase service to project.
     *
     * @param string $projectId
     *
     * @return array
     */
    public function addFirebaseToProject($projectId)
    {
        $result = $this->client->post('/v1beta1/' . $projectId . ':addFirebase', [
            'json' => [
                'timeZone' => 'America/Los_Angeles',
                'regionCode' => 'US',
                'locationId' => 'us-central'
            ]
        ]);

        return json_decode($result->getBody()->getContents(), true);
    }

    /**
     * Create an android application in a project.
     *
     * @param string $projectId
     *
     * @return array
     */
    public function createAndroidApp($projectId)
    {
        $application = [
            'displayName' => 'Andriod App for ' . STORECODE,
            'packageName' => 'ec.andriod.' . strtolower(STORECODE),
        ];

        $result = $this->client->post('/v1beta1/projects/' . $projectId . '/androidApps', [
            'json' => $application
        ]);

        return json_decode($result->getBody()->getContents(), true);
    }

    /**
     * Create an ios application in a project.
     *
     * @param string $projectId
     *
     * @return array
     */
    public function createIOSApp($projectId)
    {
        $application = [
            'displayName' => 'IOS App for ' . STORECODE,
            'bundleId' => 'ec.ios.' . strtolower(STORECODE),
        ];

        $result = $this->client->post('/v1beta1/projects/' . $projectId . '/iosApps', [
            'json' => $application
        ]);

        return json_decode($result->getBody()->getContents(), true);
    }
}
