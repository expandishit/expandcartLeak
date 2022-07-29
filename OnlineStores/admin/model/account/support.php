<?php

class ModelAccountSupport extends Model
{
    /**
     * @var string
     */
    protected $apiUri = 'https://api.hubapi.com/crm/v3/objects/';

    /**
     * @var string
     */
    protected $apiSecret = HS_SECRET_KEY;

    /**
     * Get the Statuses List.
     *
     *
     * @return Array
     */
    public function getStatuses()
    {
        $this->language->load('account/support');

        return [
            '0' => [
                '1' => $this->language->get('ticket_status_new'),
                '2' => $this->language->get('ticket_status_open'),
                '3' => $this->language->get('ticket_status_pending'),
                '43277302' => $this->language->get('ticket_status_solved'),
                '4' => $this->language->get('ticket_status_closed'),
            ],
            '16552687' => [
                '56357879' => $this->language->get('ticket_status_new'),
                '56357880' => $this->language->get('ticket_status_open'),
                '56357881' => $this->language->get('ticket_status_pending'),
                '56375765' => $this->language->get('ticket_status_solved'),
                '56358074' => $this->language->get('ticket_status_closed'),
            ]
        ];
    }


    /**
     * Get the entry point contact info for the store.
     *
     * @param string $email
     *
     * @return Object
     */
    public function initContactInfo(string $email)
    {
        return $this->sendRequest([
            'resource' => sprintf('contacts/%s', $email),
            'query' => [
                'idProperty' => 'email',
                'email' => $email
            ],
            'dataType' => 'json'
        ]);
    }

    /**
     * List tickets based on the store contact id.
     *
     * @param int $contactId
     *
     * @return Object
     */
    public function listTickets(int $contactId)
    {
        return $this->sendRequest([
            'resource' => 'tickets/search',
            'dataType' => 'json',
            'method' => 'POST',
            'data' => [
                'filters' => [
                    [
                        'propertyName' => 'associations.contact',
                        'operator' => 'EQ',
                        'value' => $contactId
                    ]
                ],
                'sorts' => [
                    [
                        'propertyName' => 'id',
                        'direction' => 'DESCENDING',
                    ]
                ],
                'limit' => 10,
            ]
        ]);
    }

    /**
     * Retrieve ticket info based on ticket id
     *
     * @param int $id
     *
     * @return Object
     */
    public function getTicket(int $id)
    {
        return $this->sendRequest([
            'resource' => sprintf('tickets/%d', $id),
            'query' => [
                'associations' => 'email',
                'properties' => implode(',', [
                    'content',
                    'createdate',
                    'hubspot_owner_id',
                    'subject',
                    'hs_ticket_id',
                    'hs_pipeline',
                    'hs_pipeline_stage',
                    'hs_lastmodifieddate',
                    'hs_object_id',
                    'hs_ticket_priority',
                ])
            ],
            'method' => 'GET',
            'dataType' => 'json'
        ]);
    }

    /**
     * Read batch emails by a set of email ids
     *
     * @param array $ids
     *
     * @return Object
     */
    public function getEmails(array $ids)
    {
        return $this->sendRequest([
            'resource' => 'emails/batch/read',
            'data' => [
                'inputs' => $ids,
                'properties' => [
                    'hs_email_text',
                    'hs_email_subject',
                    'hs_email_from',
                    'hs_email_from_firstname',
                    'hs_email_from_lastname',
                    'hs_email_to_email',
                    'hs_email_to_firstname',
                    'hs_email_to_lastname',
                    'hubspot_owner_id',
                ]
            ],
            'method' => 'POST',
            'dataType' => 'json'
        ]);
    }

    /**
     * Store a new ticket
     *
     * @param array $data
     *
     * @return Object
     */
    public function insert(array $data)
    {
        $properties = [
            'hs_pipeline' => $data['pipeline'],
            'hs_pipeline_stage' => $data['pipeline_stage'],
            'hs_ticket_priority' => 'LOW',
            'subject' => $data['subject'],
            'content' => $data['content'],
        ];

        $data = ['properties' => $properties];

        return $this->sendRequest(['resource' => 'tickets', 'data' => $data, 'method' => 'POST', 'dataType' => 'json']);
    }

    /**
     * Associate ticket with contact
     * Hubspot depends on this relation to relate between the ticket and the contact
     * so you can grep the contact with it's related tickets or search for a ticket
     * based on a contact info
     *
     * @param int $ticketId
     * @param int $contactId
     *
     * @return Object
     */
    public function associateTicketWithContact($ticketId, $contactId)
    {
        return $this->sendRequest([
            'resource' => sprintf('tickets/%d/associations/contact/%d/%s', $ticketId, $contactId, 'ticket_to_contact'),
            'method' => 'PUT',
            'dataType' => 'json'
        ]);
    }

    /**
     * Send a new reply with the `email` type
     * The `hs_email_headers` must be an escaped json encoded string
     *
     * @param array $data
     *
     * @return Object
     */
    public function sendReplyEmail(array $data)
    {
        return $this->sendRequest([
            'resource' => 'emails',
            'data' => [
                'properties' => [
                    'hs_timestamp' => floor(microtime(true) * 1000),
                    'hs_email_direction' => 'INCOMING_EMAIL',
                    'hs_email_subject' => $data['subject'],
                    'hs_email_text' => $data['content'],
                    'hs_email_headers' => ("{
                        \"from\": {
                            \"email\": \"".$data['sender_email']."\",
                            \"firstName\": \"".$data['first_name']."\",
                            \"lastName\": \"".$data['last_name']."\"
                        },
                        \"to\": [
                            {\"email\": \"support@expandcart.com\"}
                        ]
                    }")
                ]
            ],
            'method' => 'POST',
            'dataType' => 'json'
        ]);
    }

    /**
     * Associate email with ticket
     * Hubspot depends on this relation to relate between the email and the ticket
     * so you can grep the ticket with it's related email
     *
     * @param int $emailId
     * @param int $ticketId
     *
     * @return Object
     */
    public function associateEmailWithTicket($emailId, $ticketId)
    {
        return $this->sendRequest([
            'resource' => sprintf('emails/%d/associations/ticket/%d/%s', $emailId, $ticketId, 'email_to_ticket'),
            'method' => 'PUT',
            'dataType' => 'json'
        ]);
    }

    /**
     * Associate email with contact
     * Hubspot depends on this relation to relate between the email and the contact
     *
     * @param int $emailId
     * @param int $ticketId
     *
     * @return Object
     */
    public function associateEmailWithContact($emailId, $contactId)
    {
        return $this->sendRequest([
            'resource' => sprintf('emails/%d/associations/contact/%d/%s', $emailId, $contactId, 'email_to_contact'),
            'method' => 'PUT',
            'dataType' => 'json'
        ]);
    }

    /**
     * a factory method to send a curl request to Hubspot
     * we used the APIs directly instead of the Hubspot SDK
     * which is located in here `https://github.com/HubSpot/hubspot-api-php`
     * becaause a backward compatibility with our composer packages versions
     * specially with GuzzleHttp
     *
     * @param array $options
     *
     * @return Object
     */
    private function sendRequest($options)
    {
        $ch = curl_init();

        $query = [
            'hapikey' => $this->apiSecret
        ];

        if (isset($options['query']) && is_array($options['query'])) {
            $query = array_merge($query, $options['query']);
        }

        $url = sprintf('%s%s?%s', $this->apiUri, $options['resource'], http_build_query($query));

        curl_setopt($ch, CURLOPT_URL, $url);

        $headers = [];

        if (isset($options['headers'])) {
            $headers = $options['headers'];
        }

        if (isset($options['dataType']) && $options['dataType'] == 'json') {
            $headers[] = 'Content-Type: application/json';
        }

        $dataEncoder = 'json_encode';
        if (isset($options['dataType']) && $options['dataType'] != 'json') {
            $dataEncoder = 'http_build_query';
        }

        if (isset($options['method']) && $options['method'] == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        if (isset($options['method']) && $options['method'] == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        }

        if (isset($options['data']) && is_array($options['data'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataEncoder($options['data']));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            dd($cer);
            throw new \Exception($cer);
            $response->error = $cer;
            return $response;
        }

        curl_close($ch);

        $response = json_decode($response);

        return $response;
    }
}
