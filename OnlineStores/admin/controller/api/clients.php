<?php

class ControllerApiClients extends Controller
{
    private $clients;

    public function init($models)
    {
        foreach ($models as $model) {
            $this->load->model($model);
            $object = explode('/', $model);
            $object = end($object);
            $model = str_replace('/', '_', $model);
            $this->$object = $this->{"model_" . $model};
        }
        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);
            unset($this->session->data['errors']);
        }
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }
        $this->language->load('api/clients');
    }

    public function browse()
    {
        $this->init([
            'api/clients'
        ]);

        $this->document->setTitle($this->language->get('api_clients_heading_title'));

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_modules'),
            'href' => $this->url->link(
                'marketplace/home',
                'token=' . $this->session->data['token'] . '&type=apps',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('api_clients_heading_title'),
            'href' => $this->url->link('api/clients/browse', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'newClient' => $this->url->link(
                'api/clients/insert',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'editClient' => $this->url->link(
                'api/clients/show',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'activateClient' => $this->url->link(
                'api/clients/activate',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'deactivateClient' => $this->url->link(
                'api/clients/deactivate',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
        ];

        $this->data['clients'] = $this->clients->getAllClients();

        $this->template = 'api/clients/browse.tpl';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    public function insert()
    {
        $this->init([
            'api/clients'
        ]);

        $clientId = $this->clients->generateClientId();

        $secretKey = $this->clients->generateSecretKey($clientId);

        if ($clientId && $secretKey) {
            $id = $this->clients->storeClient($clientId, $secretKey, 1);

            $this->redirect(
                $this->url->link(
                    'api/clients/show',
                    'token=' . $this->session->data['token'] . '&client_id=' . $id,
                    'SSL'
                )
            );

        }
    }

    public function show()
    {

        $clientId = (isset($this->request->get['client_id']) ? $this->request->get['client_id'] : null);

        if (!$clientId) {
            $this->session->data['errors'] = [$this->language->get('error_not_set_id')];
            $this->redirect(
                $this->url->link(
                    'api/clients/browse',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

        $this->init([
            'api/clients'
        ]);

        $this->document->setTitle($this->language->get('api_clients_create_client'));

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_modules'),
            'href' => $this->url->link(
                'marketplace/home',
                'token=' . $this->session->data['token'] . '&type=apps',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('api_clients_heading_title'),
            'href' => $this->url->link('api/clients/browse', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('api_clients_show_client'),
            'href' => $this->url->link(
                'api/clients/show',
                'token=' . $this->session->data['token'] . '&client_id=' . $clientId,
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'browseClients' => $this->url->link(
                'api/clients/browse',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
        ];

        $this->data['client'] = $this->clients->getClientById($clientId);

        $this->template = 'api/clients/show.tpl';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    public function activate()
    {
        $clientId = (isset($this->request->get['client_id']) ? $this->request->get['client_id'] : null);

        if (!$clientId) {
            $this->session->data['errors'] = [$this->language->get('error_not_set_id')];
            $this->redirect(
                $this->url->link(
                    'api/clients/browse',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

        $this->init([
            'api/clients'
        ]);

        $client = $this->clients->getClientById($clientId);

        if (!$client) {
            $this->session->data['errors'] = [$this->language->get('error_not_set_client')];
            $this->redirect(
                $this->url->link(
                    'api/clients/browse',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

        $this->clients->updateStatus($clientId, 1);

        $this->session->data['success'] = $this->language->get('success_activate_client');

        $this->redirect(
            $this->url->link(
                'api/clients/browse',
                'token=' . $this->session->data['token'],
                'SSL'
            )
        );
    }

    public function deactivate()
    {
        $clientId = (isset($this->request->get['client_id']) ? $this->request->get['client_id'] : null);

        if (!$clientId) {
            $this->session->data['errors'] = [$this->language->get('error_not_set_id')];
            $this->redirect(
                $this->url->link(
                    'api/clients/browse',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

        $this->init([
            'api/clients'
        ]);

        $client = $this->clients->getClientById($clientId);

        if (!$client) {
            $this->session->data['errors'] = [$this->language->get('error_not_set_client')];
            $this->redirect(
                $this->url->link(
                    'api/clients/browse',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

        $this->clients->updateStatus($clientId, 0);

        $this->session->data['success'] = $this->language->get('success_deactivate_client');

        $this->redirect(
            $this->url->link(
                'api/clients/browse',
                'token=' . $this->session->data['token'],
                'SSL'
            )
        );
    }

    public function delete()
    {
        $clientId = (isset($this->request->get['client_id']) ? $this->request->get['client_id'] : null);

        if (!$clientId) {
            $this->session->data['errors'] = [$this->language->get('error_not_set_id')];
            $this->redirect(
                $this->url->link(
                    'api/clients/browse',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

        $this->init([
            'api/clients'
        ]);

        $client = $this->clients->getClientById($clientId);

        if (!$client) {
            $this->session->data['errors'] = [$this->language->get('error_not_set_client')];
            $this->redirect(
                $this->url->link(
                    'api/clients/browse',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

        $this->clients->deleteClient($clientId);

        $this->session->data['success'] = $this->language->get('success_remove_client');

        $this->redirect(
            $this->url->link(
                'api/clients/browse',
                'token=' . $this->session->data['token'],
                'SSL'
            )
        );
    }
}
