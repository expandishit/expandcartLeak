<?php
include_once(DIR_SYSTEM . 'library/phpmailer/class.phpmailer.php');

class Mail {
	protected $to;
	protected $from;
	protected $sender;
    protected $readreceipt;
	protected $subject;
	protected $text;
	protected $html;
	protected $attachments = array();
	public $protocol = 'mail';
	public $hostname;
	public $username;
	public $password;
	public $port = 25;
	public $timeout = 5;
	public $newline = "\n";
	public $crlf = "\r\n";
	public $verp = false;
	public $parameter = '';

    protected $replyTo = [];
    protected $bcc_emails = array();
    protected $new_html = '';
    protected $new_text = '';
    protected $bcc_html = '';

    public function setNewHtml($html) {
        $this->new_html = $html;
    }

    public function setNewText($text) {
        $this->new_text = $text;
    }

    public function setBccHtml($html) {
        $this->bcc_html = $html;
    }

    public function setBccEmails($emails = array()) {
        foreach ($emails as $email) {
            $email = trim($email);

            if ($email && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
                $this->bcc_emails[] = $email;
            }
        }
    }

    public function sendBccEmails() {
        if ($this->bcc_emails) {
            if ($this->bcc_html) {
                $this->new_html = $this->bcc_html;
            }

            foreach ($this->bcc_emails as $email) {
                $this->setTo($email);
                $this->send();
            }
        }

        $this->bcc_emails = array();
    }

	public function setTo($to) {
		$this->to = $to;
	}

	public function setFrom($from) {
		$this->from = $from;
	}

	public function setSender($sender) {
		$this->sender = html_entity_decode($sender, ENT_QUOTES, 'UTF-8');
	}

    public function setReadReceipt($readreceipt) {
        $this->readreceipt = $readreceipt;
    }

	public function setSubject($subject) {
		$this->subject = $subject;
	}

	public function setText($text) {
		$this->text = $text;
	}

    public function setReplyTo($email, $name, $fallbackEmail = null)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
            $email = $fallbackEmail;
        }
        $this->replyTo['email'] = $email;
        $this->replyTo['name'] = $name ?: $email;

        return $this;
    }

	public function setHtml($html) {
		$this->html = $html;
	}

	public function addAttachment($filename) {
		$this->attachments[] = $filename;
	}

    public function send($smtp_name='') {

        if (!$this->isValidEmailConfig())return false;

        if ($this->new_html) {
            $this->html = $this->new_html;
        }

        if ($this->new_text) {
            $this->text = $this->new_text;
        }

        if (!$this->to) {
            trigger_error('Error: E-Mail to required!');
            exit();
        }

        if (!$this->from) {
            trigger_error('Error: E-Mail from required!');
            exit();
        }

        if (!$this->sender) {
            trigger_error('Error: E-Mail sender required!');
            exit();
        }

        if (!$this->subject) {
            trigger_error('Error: E-Mail subject required!');
            exit();
        }

        if ((!$this->text) && (!$this->html)) {
            trigger_error('Error: E-Mail message required!');
            exit();
        }

        $mail  = new PHPMailer();
        $mail->CharSet = "UTF-8";

        if (is_array($this->to)) {
            foreach ($this->to as $toTmp){
                $mail->AddAddress($toTmp);
            }
        } else {
            $mail->AddAddress($this->to);
        }

        if(!empty($this->readreceipt)) {
            $mail->ConfirmReadingTo = $this->readreceipt;
        }

        $mail->Subject = $this->subject;
        if ($this->protocol == 'amazon' || strpos($this->hostname, 'amazonaws.com') !== false) {

            $amazon_from = "Support@expandcart.com";
            if($this->parameter)
                $amazon_from = $this->parameter;

            $mail->SetFrom($amazon_from , $this->sender);
            $mail->AddReplyTo($this->from, $this->sender);
            $mail->AddReplyTo($this->from, $this->sender);
        } else {
            if(empty($smtp_name)){
                if(strpos($this->hostname ,'mailjet.com')){
                    $mail->AddReplyTo($this->replyTo['email'], $this->replyTo['name']);
                    $mail->SetFrom($this->replyTo['email'], $this->sender);
                    $mail->AddReplyTo($this->replyTo['email'], $this->sender);
                }else{
                    if ($this->protocol == 'expandcart_relay' && isset($this->replyTo['email'])) {
                        $mail->AddReplyTo($this->replyTo['email'], $this->replyTo['name']);
                    } else {
                        $mail->AddReplyTo($this->from, $this->sender);
                    }
                    $mail->SetFrom($this->from, $this->sender);
                    $mail->AddReplyTo($this->from, $this->sender);
                }
            }
            else if($smtp_name == "zoho"){
                $mail->AddReplyTo($this->from, $this->sender);
                $mail->SetFrom($this->from, $this->sender);
                $mail->AddReplyTo($this->from, $this->sender);
            }
        }

        if (!$this->html) {
            $mail->Body = $this->text;
        } else {
            $mail->MsgHTML($this->html);
            if ($this->text) {
                $mail->AltBody = $this->text;
            } else {
                $mail->AltBody = 'This is a HTML email and your email client software does not support HTML email!';
            }
        }

        foreach ($this->attachments as $attachment) {
            if (file_exists($attachment['file']) || file_exists($attachment)) {
                $mail->AddAttachment($attachment);
            }
        }
        if ($this->protocol == 'mail'){
            $data = $this->configureExpandCartMail();
            $this->protocol = 'expandcart_relay';
            $this->hostname = $data['config_smtp_host'];
            $this->username = $data['config_smtp_username'];
            $this->password = $data['config_smtp_password'];
            $this->port = $data['config_smtp_port'];
            $this->timeout = $data['config_smtp_timeout'];
            $mail->Sender = $data['config_smtp_username'];
            $mail->SetFrom($data['config_smtp_username']);
        }

        if ($this->protocol == 'smtp' || $this->protocol == 'expandcart_relay') {
            $mail->IsSMTP();
            $mail->Host = $this->hostname;
            $mail->Port = $this->port;
            if($this->port == '587'){
                $mail->SMTPAuth = true;
                $nonTlsHosts = [
                    "mail.baklawati.com.tr"
                ];
                if(!in_array($this->hostname , $nonTlsHosts)){
                    $mail->SMTPSecure = "tls";
                }
            } elseif ($this->port == '465') {
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = "ssl";
            }
            if (!empty($this->username)  && !empty($this->password)) {
                $mail->SMTPAuth = true;
                $mail->Host = $this->hostname;
                $mail->Username = $this->username;
                $mail->Password = htmlspecialchars_decode($this->password);
                if (filter_var($mail->Username, FILTER_VALIDATE_EMAIL) !== $mail->Username) {
                    $mail->SetFrom($this->replyTo['email'], $this->sender);
                }
            }
        } elseif ($this->protocol == 'amazon') {
            $mail->IsSMTP();
            $mail->Host = 'email-smtp.us-west-2.amazonaws.com';
            $mail->Port = '587';
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "tls";
            $mail->SMTPAuth = true;
            $mail->Username = 'AKIAJ75JVQIA3NUPLSUQ';
            $mail->Password = 'AhByY/D1Pj8USw/kmA26T8rT3doBqjKaQjcYzJdSWYG4';
        }
        return $mail->Send();
    }


    /**
     * Prepare The Expandcart SMTP data
     * @param array $data
     */
    public function configureExpandCartMail($data = []){
        $expandRelayUsername = [
            "mlsv_a@expandmail.it",
            "mlsv_b@expandmail.it",
            "mlsv_c@expandmail.it",
            "mlsv_d@expandmail.it",
            "mlsv_e@expandmail.it",
            "mlsv_i@expandmail.it",
            "mlsv_m@expandmail.it",
            "mlsv_n@expandmail.it",
            "mlsv_p@expandmail.it",
            "mlsv_o@expandmail.it",
            "mlsv_r@expandmail.it",
            "mlsv_y@expandmail.it",
            "mlsv_z@expandmail.it",
        ];
        $smtp_user = $expandRelayUsername[array_rand($expandRelayUsername,1)];
        $result = $data;
        $result['config_smtp_host'] = SMTP_HOST;
        $result['config_smtp_username'] = $smtp_user ?? SMTP_USER ;
        $result['config_smtp_password'] = SMTP_PASSWORD;
        $result['config_smtp_port'] = SMTP_PORT;
        $result['config_smtp_timeout'] = SMTP_TIMEOUT;

        return $result;

    }


    /**
     * @return bool
     */
    public function isValidEmailConfig(): bool
    {
        return !($this->protocol == 'mail' && empty($this->from));
    }


    public function configureExpandMail()
    {
        $data = $this->configureExpandCartMail();
        $this->protocol = 'expandcart_relay';
        $this->hostname = $data['config_smtp_host'];
        $this->username = $data['config_smtp_username'];
        $this->password = $data['config_smtp_password'];
        $this->port = $data['config_smtp_port'];
        $this->timeout = $data['config_smtp_timeout'];
        $this->from = $data['config_smtp_username'];
    }


}
?>