<?php

use ExpandCart\Foundation\Support\Factories\Google\ClientFactory;

class ModelModuleMobileNotifications extends Model
{
    /**
     * @var string
     */
    protected $notificationsTable = 'firebase_notifications';

    /**
     * @var string
     */
    protected $customersTable = 'customer';

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $redirectUri;

    protected $devicesMap = [
        1 => 'android',
        2 => 'ios'
    ];

    /**
     * Set the client id.
     *
     * @param string $clientId
     *
     * @return void
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * Set the client secret.
     *
     * @param string $clientSecret
     *
     * @return void
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * Set the redirect uri.
     *
     * @param string $uri
     *
     * @return void
     */
    public function setRedirectUri($uri)
    {
        $this->redirectUri = $uri;
    }

    /**
     * Return all notifications
     *
     * @param int $start
     * @param int $length
     * @param string $search
     * @param string $orderColumn
     * @param string $orderType
     * @param array $columns
     *
     * @return array|bool
     */
    public function getNotifications($start, $length, $search, $orderColumn, $orderType, $columns)
    {
        $wheres = [];

        if (strlen($search) > 0) {
            foreach ($columns as $column) {
                $wheres[] = sprintf('%s LIKE "%s"', $column, '%' . $search . '%');
            }
        }

        $data = $this->db->query(vsprintf('SELECT * FROM %s %s %s %s', [
            $this->notificationsTable,
            count($wheres) > 0 ? sprintf('WHERE %s', implode(' OR ', $wheres)) : '',
            sprintf('ORDER BY %s %s', $orderColumn, $orderType),
            $length > 0 ? sprintf('LIMIT %d, %d', $start, $length) : ''
        ]));

        if ($data->num_rows) {

            $totalFiltered = $this->db->query("SELECT FOUND_ROWS() as totalFiltered");

            $total = $this->db->query(sprintf('SELECT count(id) as total FROM %s', $this->notificationsTable));

            return [
                'data' => $data->rows,
                'total' => $total->row['total'],
                'totalFiltered' => $totalFiltered->row['totalFiltered']
            ];
        }

        return false;
    }

    /**
     * Return customers to notify them.
     *
     * @param int $start
     * @param int $length
     * @param string $search
     * @param string $orderColumn
     * @param string $orderType
     * @param array $columns
     *
     * @return array|bool
     */
    public function getNotifiableUsers()
    {
        $data = $this->db->query(sprintf('SELECT * FROM %s WHERE firebase_token IS NOT NULL', $this->customersTable));

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Return customers by device type to notify them.
     *
     * @param int $deviceType
     *
     * @return array|bool
     */
    public function getNotifiableUsersByDevice(int $type)
    {
        $type = ($type == 0 ? implode('","', $this->devicesMap) : $this->devicesMap[$type]);

        $data = $this->db->query(sprintf(
            'SELECT * FROM %s WHERE firebase_token IS NOT NULL AND device_type IN ("%s")',
            $this->customersTable,
            $type
        ));

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Insert a new notification row.
     *
     * @param array $data
     *
     * @return int
     */
    public function insertNotification($data)
    {
        $query = $fields = [];
        $query[] = 'INSERT INTO %s SET';
        $fields[] = 'name="%s"';
        $fields[] = 'title="%s"';
        $fields[] = 'body="%s"';
        $fields[] = 'messageUri="%s"';
        $fields[] = 'message_type="%s"';
        $fields[] = "message_attributes='%s'";
        $query[] = vsprintf(implode(',', $fields), [
            $this->db->escape($data['name']),
            $this->db->escape($data['title']),
            $this->db->escape($data['body']),
            $this->db->escape($data['messageUri']),
            $this->db->escape($data['message_type']),
            addslashes(json_encode($data['message_attributes'], JSON_HEX_QUOT | JSON_HEX_APOS)),
        ]);

        $this->db->query(sprintf(implode(' ', $query), $this->notificationsTable));

        return $this->db->getLastId();
    }

    /**
     * Get notification by id.
     *
     * @param int $id
     *
     * @return array|bool
     */
    public function getNotification($id)
    {
        $query = 'SELECT * FROM %s WHERE id=%d';

        $data = $this->db->query(sprintf($query, $this->notificationsTable, $id));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

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
                'redirect' => 'notification',
                'data' => $data,
            ]
        );
    }

    /**
    * Publish a new notification.
     *
     * @param string $code
     * @param string $projectId
     * @param array $data
     *
     * @return array
     */
    public function publishNotification($code, $projectId, $data)
    {
        return ClientFactory::publish(
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri,
            $code,
            $projectId,
            $data
        );
    }

    /**
     * Push notification to specific devices
     *
     * @param array $devices
     * @param array $data
     *
     * @return bool
     */
    public function pushNotification($devices, $data)
    {
        return ClientFactory::pushNotification($devices, $data);
    }

    /**
     * Push notification to specific topic
     *
     * @param array $data
     *
     * @return bool
     */
    public function pushNotificationToTopic($data)
    {
        return ClientFactory::pushNotificationToTopic($data);
    }
}
