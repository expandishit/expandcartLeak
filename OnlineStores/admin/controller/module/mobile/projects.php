<?php

use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class ControllerModuleMobileProjects extends Controller
{
    public function auth()
    {
        $this->initializer([
            'module/mobile/settings',
            'module/mobile/projects',
        ]);

        $settings = $this->settings->getSettings();

        $data = $this->request->post;

        $this->notifications->setClientId(FIREBASE_CLIENTID);
        $this->notifications->setClientSecret(FIREBASE_CLIENTSECRET);
        $this->notifications->setRedirectUri(FIREBASE_REDIRECTURI);

        $link = $this->notifications->getAuthLink($data);

        $this->response->redirect($link);
        return;
    }

    public function insert()
    {
        $this->initializer([
            'module/mobile/settings',
            'module/mobile/projects',
        ]);

        $settings = $this->settings->getSettings();

        $code = $this->request->get['code'];

        $state = json_decode(base64_decode($this->request->get['state']), true);

        $response = [];

        $this->projects->setClientId(FIREBASE_CLIENTID);
        $this->projects->setClientSecret(FIREBASE_CLIENTSECRET);
        $this->projects->setRedirectUri(FIREBASE_REDIRECTURI);

        if ($projectId = $this->projects->publishNotification($code)) {

            $this->projects->insertSettings($projectId);

            $this->redirect($this->url->link('module/mobile/notifications', '', 'SSL'));
        }

        $this->response->setOutput(json_encode([
            'status' => 'ERR',
            'errors' => $this->notifications->getErrors()
        ]));
        return;
    }
}
