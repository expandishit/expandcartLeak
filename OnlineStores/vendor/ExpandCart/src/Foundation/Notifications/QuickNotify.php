<?php

namespace ExpandCart\Foundation\Notifications;

class QuickNotify
{
    /**
     * Database object client.
     *
     * @var \DB
     */
    protected static $dbClient;

    /**
     * The notifications array.
     *
     * @var array
     */
    protected $notifications;

    /**
     * Notification table name.
     *
     * @var string
     */
    protected $notificationTable = 'notification';

    /**
     * Set the database client object.
     *
     * @param \DB $dbClient
     *
     * @return \ExpandCart\Foundation\Notifications\QuickNotify
     */
    public static function setDbClient($dbClient)
    {
        static::$dbClient = $dbClient;

        return new static;
    }

    /**
     * Get the database client object.
     *
     * @return \DB
     */
    public static function getDbClient()
    {
        return static::$dbClient;
    }

    /**
     * Push a new notification to the notifications stack.
     *
     * @param array $notification
     *
     * @return $this
     */
    public function push($notification)
    {
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * Insert the notification into the notification table.
     *
     * @return void
     */
    public function send()
    {
        $db = static::getDbClient();

        if (count($this->notifications) == 0) {
            return false;
        }

        foreach ($this->notifications as $notification) {

            $queryString = $fields = [];
            $queryString[] = 'INSERT INTO ' . $this->notificationTable . ' SET';
            $fields[] = 'event="' . $notification['event'] . '"';
            $fields[] = 'submitter="' . $notification['submitter'] . '"';
            $fields[] = 'notification="' . $notification['message'] . '"';
            $fields[] = 'receiver="' . $notification['reciever'] . '"';

            $queryString[] = implode(',', $fields);

            $db->query(implode(' ', $queryString));
        }
    }
}
