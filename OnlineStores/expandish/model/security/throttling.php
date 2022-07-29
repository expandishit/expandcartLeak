<?php

class ModelSecurityThrottling extends Model
{
    /**
     * Failed logins attemtps table.
     *
     * @string var
     */
    private $failedLoginsTable = DB_PREFIX . 'failed_logins';

    /**
     * Sscurity settings object.
     *
     * @string var
     */
    private $settings = null;

    /**
     * Store the user ip in the $failedLoginsTable table.
     *
     * @param array $data
     *
     * @return void
     */
    public function banIp($data)
    {
        $query = $fields = [];

        $query[] = 'INSERT INTO ' . $this->failedLoginsTable . ' SET';
        $fields[] = 'ipv4=INET_ATON("' . $data['ipv4'] . '")';
        $fields[] = 'resource="' . $data['resource'] . '"';
        $fields[] = 'attempts="' . $data['attempts'] . '"';
        $fields[] = 'recaptcha_status="' . $data['recaptcha_status'] . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Store the user ip in the $failedLoginsTable table.
     *
     * @param string $ipv4
     *
     * @return void
     */
    public function isBanned($ipv4)
    {
        $query = [];

        $query[] = 'SELECT * FROM ' . $this->failedLoginsTable;
        $query[] = 'WHERE ipv4=INET_ATON("' . $ipv4 . '")';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Get the throttlin settings.
     *
     * @return array|null
     */
    public function getSettings()
    {
        if (!$this->settings) {
            $this->settings = $this->config->get('throttling');
        }

        return $this->settings;
    }

    /**
     * Get the throttlin status.
     *
     * @return bool
     */
    public function throttlingStatus()
    {
        $settings = $this->getSettings();

        if (isset($settings['enable_throttling']) && $settings['enable_throttling'] == 1) {
            return true;
        }

        return false;
    }

    /**
     * Get the reCaptcha status.
     *
     * @return bool
     */
    public function reCaptchaStatus()
    {
        $settings = $this->getSettings();

        if (isset($settings['enable_recaptcha']) && $settings['enable_recaptcha'] == 1) {
            return true;
        }

        return false;
    }

    /**
     * Get the reCaptcha site key.
     *
     * @return string
     */
    public function reCaptchaSiteKey()
    {
        return $this->getSettings()['recaptcha_sitekey'];
    }
}
