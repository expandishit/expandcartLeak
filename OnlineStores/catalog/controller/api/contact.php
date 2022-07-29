<?php
require_once('jwt_helper.php');
class ControllerApiContact extends Controller {
	
    public function index() {

        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');


        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
            return;
        }

        try {

            $data = (array)json_decode(file_get_contents('php://input'));

            $this->language->load('information/contact');

            if (!isset($data['email']) || !$data['email']) {
                $json['error']['email'] = $this->language->get('error_email');
            }
            if (!isset($data['name']) || !$data['name']) {
                $json['error']['name'] = $this->language->get('error_name');
            }
            if (!isset($data['enquiry']) || !$data['enquiry']) {
                $json['error']['enquiry'] = $this->language->get('error_enquiry');
            }

            if (isset($json['error']) && !empty($json['error'])) {
                $json['status'] = 'validation_error';
            } else {
                
                $mail = new Mail();
                $mail->protocol = $this->config->get('config_mail_protocol');
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->hostname = $this->config->get('config_smtp_host');
                $mail->username = $this->config->get('config_smtp_username');
                $mail->password = $this->config->get('config_smtp_password');
                $mail->port = $this->config->get('config_smtp_port');
                $mail->timeout = $this->config->get('config_smtp_timeout');				
                $mail->setTo($this->config->get('config_email'));
                $mail->setFrom('mohamedsy474@gmail.com');
                $mail->setSender( $data['name']);
                $mail->setSubject(
                    html_entity_decode(
                        sprintf($this->language->get('email_subject'), 
                         $data['name']
                    ), ENT_QUOTES, 'UTF-8')
                );
                $mail->setText(strip_tags(html_entity_decode($data['enquiry'], ENT_QUOTES, 'UTF-8')));

                $mail->send();
                if ($this->config->get('custom_email_templates_status')) {
                    $mail->sendBccEmails();
                }

                $json = [
                    'status' => 'ok'
                ];
            }

        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }
}