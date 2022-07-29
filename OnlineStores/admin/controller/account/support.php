<?php

class ControllerAccountSupport extends Controller
{
    public function index()
    {
        $this->initializer(['account/support', 'setting/setting']);
        $this->language->load('account/support');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('account/charge', '', 'SSL'),
            'separator' => false
        );

        $this->data['action'] = $this->url->link('account/support/submit', '', 'SSL');

        if (!$this->config->get('hubspot') || !isset($this->config->get('hubspot')['contact_id'])) {
            $contact = $this->support->initContactInfo(BILLING_DETAILS_EMAIL);

            if (isset($contact->id)) {
                $this->setting->insertUpdateSettingSecured('integrations', [
                    'hubspot' => [
                        'contact_id' => $contact->id
                    ]
                ]);
            }
        }

        if (isset($this->config->get('hubspot')['contact_id']) == false) {
            $this->response->redirect($this->url->link('account/charge', '', true));
            throw new \Exception('can not get contact id value');
        }

        $this->data['tickets'] = $this->support->listTickets($this->config->get('hubspot')['contact_id'])->results;

        $this->data['statuses'] = $this->support->getStatuses();

        $this->template = 'account/support/list.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    public function submit()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            throw new \Exception('invalid method');
        }

        if (isset($_POST) == false || count($_POST) == 0) {
            throw new \Exception('invalid');
        }

        $this->initializer(['account/support', 'setting/setting']);

        if (isset($this->request->post['ticket']) == false) {
            return $this->response->json([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => [
                    'Invalid credentials',
                ],
            ], 500);
        }

        $ticketData = $this->request->post['ticket'];

        if (isset($ticketData['subject']) == false || strlen($ticketData['subject']) < 3) {
            return $this->response->json([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => [
                    'Subject length must be greater than 3 characters',
                ],
            ], 500);
        }

        if (isset($ticketData['content']) == false || strlen($ticketData['content']) < 10) {
            return $this->response->json([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => [
                    'Ticket content must be greater than 10 characters',
                ],
            ], 500);
        }

        if (!$this->config->get('hubspot') && !isset($this->config->get('hubspot')['contact_id'])) {
            $contact = $this->support->initContactInfo(BILLING_DETAILS_EMAIL);

            if (isset($contact->id)) {
                $this->setting->insertUpdateSettingSecured('integrations', [
                    'hubspot' => [
                        'contact_id' => $contact->id
                    ]
                ]);
            }
        }

        $hubspot = $this->config->get('hubspot');

        if (isset($hubspot['contact_id']) == false) {
            throw new \Exception('can not get contact id value');
        }


        // @NOTE These are internal values in Hubspot, they should be a static value and
        // should not be changed unless these values had been changed in Hubspot
        $ticketData['pipeline'] = '0';
        $ticketData['pipeline_stage'] = '1';
        if ($this->config->get('config_admin_language') === 'en') {
            $ticketData['pipeline'] = '16552687';
            $ticketData['pipeline_stage'] = '56357879';
        }

        $ticket = $this->support->insert($ticketData);

        $this->support->associateTicketWithContact($ticket->id, $hubspot['contact_id']);

        return $this->response->json([
            'status' => 'OK',
            'data' => [],
            'message' => ''
        ]);
    }

    public function view()
    {
        $this->initializer(['account/support']);
        $this->language->load('account/support');

        $this->document->setTitle($this->language->get('heading_title'));

        $ticketId = $this->request->get['ticket_id'];

        if (!preg_match('#^[0-9]+$#', $ticketId)) {
            throw new \Exception('invalid action');
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('account/charge', '', 'SSL'),
            'separator' => false
        );

        $this->data['ticket'] = $ticket = $this->support->getTicket($ticketId);

        $emails = [];
        if (isset($ticket->associations->emails)) {
            $emails = $this->support->getEmails(array_column($ticket->associations->emails->results, 'id'))->results;
        }


        usort($emails, function ($a, $b) {
            return ($a->id < $b->id) && ($a->createdAt < $b->createdAt);
        });

        $this->data['emails'] = $emails;

        // @NOTE These are internal values in Hubspot, they should be a static value and
        // should not be changed unless these values had been changed in Hubspot
//        $pipeline = '0';
//        if ($this->config->get('config_admin_language') === 'en') {
//            $pipeline = '16552687';
//        }

        $statuses = $this->support->getStatuses();

        $this->data['status'] = $statuses[$ticket->properties->hs_pipeline][$ticket->properties->hs_pipeline_stage];
        $this->data['status_code'] = $ticket->properties->hs_pipeline_stage;

        $this->data['action'] = $this->url->link('account/support/submitReplay', '', 'SSL');

        $this->template = 'account/support/view.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    public function submitReplay()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            throw new \Exception('invalid method');
        }

        if (isset($_POST) == false || count($_POST) == 0) {
            throw new \Exception('invalid');
        }

        $this->initializer(['account/support', 'setting/setting']);

        $ticketId = $this->request->post['ticket_id'];

        if (!preg_match('#^[0-9]+$#', $ticketId)) {
            throw new \Exception('invalid action');
        }

        if (isset($this->request->post['ticket']) == false) {
            return $this->response->json([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => [
                    'Invalid credentials',
                ],
            ], 500);
        }

        $ticketData = $this->request->post['ticket'];

//        if (isset($ticketData['subject']) == false || strlen($ticketData['subject']) < 3) {
//            return $this->response->json([
//                'status' => 'ERR',
//                'error' => 'INVALID_CREDENTIALS',
//                'errors' => [
//                    'Subject length must be greater than 3 characters',
//                ],
//            ], 500);
//        }

        if (isset($ticketData['content']) == false || strlen($ticketData['content']) < 10) {
            return $this->response->json([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => [
                    'Ticket content must be greater than 10 characters',
                ],
            ], 500);
        }

        if (!$this->config->get('hubspot') && !isset($this->config->get('hubspot')['contact_id'])) {
            $contact = $this->support->initContactInfo(BILLING_DETAILS_EMAIL);

            if (isset($contact->id)) {
                $this->setting->insertUpdateSettingSecured('integrations', [
                    'hubspot' => [
                        'contact_id' => $contact->id
                    ]
                ]);
            }
        }

        $hubspot = $this->config->get('hubspot');

        if (isset($hubspot['contact_id']) == false) {
            throw new \Exception('can not get contact id value');
        }

        $ticketData['first_name'] = $this->user->getFirstName();
        $ticketData['last_name'] = $this->user->getLastName();
        $ticketData['sender_email'] = $this->user->getEmail();

        $email = $this->support->sendReplyEmail($ticketData);

        $cont = $this->support->associateEmailWithContact($email->id, $hubspot['contact_id']);
        $tick = $this->support->associateEmailWithTicket($email->id, $ticketId);

        return $this->response->json([
            'status' => 'OK',
            'data' => [
                'email' => $email,
                'ticket' => $tick,
                'contact' => $cont,
            ],
            'message' => ''
        ]);
    }
}
