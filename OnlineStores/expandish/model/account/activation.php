<?php

class ModelAccountActivation extends Model
{
    /**
     * customer activation table.
     *
     * @var string
     */
    private $customerActivationTable = DB_PREFIX . "customer_activation";

    /**
     * customer table.
     *
     * @var string
     */
    private $customerTable = DB_PREFIX . "customer";

    /**
     * Insert new row in the customer activation table.
     *
     * @param array $data
     *
     * @return void
     */
    public function insertNewActivationCode($data)
    {
        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO `'.$this->customerActivationTable.'` SET';
        $fields[] = 'customer_id=' . $data['customer_id'];
        $fields[] = 'token="' . $data['token'] . '"';
        $fields[] = 'activation_type=' . $data['activation_type'];
        $fields[] = 'activation_status="0"';
        $fields[] = 'created_at="' . date("Y-m-d H:i:s") . '"';

        $queryString[] = implode(',', $fields);

        $this->db->query(implode(' ', $queryString));
    }

    /**
     * Generates a token string.
     *
     * @param int $length
     * @param int $type
     *
     * @return string
     */
    public function generateToken($length = 25, $type = 1)
    {
        if(!$this->config->get('smshare_cfg_code_type') || $this->config->get('smshare_cfg_code_type') === 'numeric'){
            $token = rand(1000,(int)str_repeat(9,$length));
        }else{
            $token = '';
            $keys1 = range('a', 'z');
            $keys2 = range(0, 9);
            $keys = array_merge($keys1,$keys2);
            
            for ($i = 0; $i < $length; $i++) {
                $token .= $keys[array_rand($keys)];
            }
        }

        if ($this->getCustomerActivationByToken($token, $type) === false) {
            return $token;
        }

        return $this->generateToken($length, $type);
    }

    /**
     * Generates a sms token string
     *
     * @return string
     */
    public function generateSmsToken()
    {
        $code_length = (int) $this->config->get('smshare_cfg_code_length');

        if(!$code_length || $code_length < 4 || $code_length > 25)
            $code_length = 4;

        return strtoupper($this->generateToken($code_length, 2));
    }

    /**
     * Validates a given token string.
     *
     * @param string $token
     *
     * @return bool
     */
    public function validateToken($token)
    {
        if (strlen($token) != strlen($this->generateToken())) {
            return false;
        }

        return true;
    }

    /**
     * Validates a given sms token string.
     *
     * @param string $token
     *
     * @return bool
     */
    public function validateSmsToken($token)
    {
        if (strlen($token) != strlen($this->generateSmsToken())) {
            return false;
        }

        return true;
    }

    /**
     * Return a customer row using the token string and type.
     *
     * @param string $token
     * @param int $type
     *
     * @return array|bool
     */
    public function getCustomerActivationByToken($token, $type)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `'.$this->customerActivationTable.'`';
        $queryString[] = 'WHERE token="' . $token . '"';
        $queryString[] = 'AND activation_type="' . $type . '"';
        $queryString[] = 'AND activation_status="0"';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Return a customer row using customer id.
     *
     * @param int $customer_id
     *
     * @return array|bool
     */
    public function getCustomerActivationByCustomerId($customer_id)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `'.$this->customerActivationTable.'`';
        $queryString[] = 'WHERE customer_id="' . $customer_id . '"';
        $queryString[] = 'AND activation_status="0"';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Update the activation status using the activation id.
     *
     * @param int $activationId
     * @param int $status
     *
     * @return void
     */
    public function updateActivationApprovalStatus($activationId, $status)
    {
        $queryString = $fields = [];
        $queryString[] = 'UPDATE `'.$this->customerActivationTable.'` SET';
        $fields[] = 'activation_status="'.$status.'"';

        $queryString[] = implode(',', $fields);
        $queryString[] = 'WHERE id=' . $activationId;

        $this->db->query(implode(' ', $queryString));
    }

    /**
     * when token expired
     * update the token by customer id  to activation customer mail
     */
    public function updateToken($customerid){
        $newToken  = $this->generateToken();
        if ($newToken)
            $this->db->query("UPDATE " .$this->customerActivationTable. " SET created_at = NOW()  , token= '" . $newToken  ."' WHERE customer_id ='" . (int)$customerid  . "'");


        return $newToken;
    }
    public function sendAnotherActivationMail($customer){

        $token = null;
        // update expire token
        $token  =  $this->updateToken($customer['customer_id']);
        if($customer['email'] != ""){
            $this->language->load_json('mail/customer');

            $subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));

            $message = sprintf($this->language->get('text_welcome'), $this->config->get('config_name')) . "\n\n";

            if (isset($token)) {

                $message .= $this->language->get('text_email_activation') . " : \n";
                $activationUrl = $this->url->link('account/activation/activate', 'token=' . $token, 'SSL');
                $message .= $activationUrl . "\n\n";
            } else {
                $message .= $this->url->link('account/login', '', 'SSL') . "\n\n";
            }
            $message .= $this->language->get('text_services') . "\n\n";
            $message .= $this->language->get('text_thanks') . "\n";
            $message .= $this->config->get('config_name');
            $mail = new Mail();
            $mail->protocol = $this->config->get('config_mail_protocol');
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->hostname = $this->config->get('config_smtp_host');
            $mail->username = $this->config->get('config_smtp_username');
            $mail->password = $this->config->get('config_smtp_password');
            $mail->port = $this->config->get('config_smtp_port');
            $mail->timeout = $this->config->get('config_smtp_timeout');
            $mail->setReplyTo(
                $this->config->get('config_mail_reply_to'),
                $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                $this->config->get('config_email')
            );
            $mail->setTo($customer['email']);
            $mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
            $mail->setSender($this->config->get('config_name'));
            $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
            $mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
            $mail->send();
            unset($mail);
            unset($message);

        }

    }

}
