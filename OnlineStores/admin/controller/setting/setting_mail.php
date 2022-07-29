<?php
class ControllerSettingSettingMail extends Controller
{
    private $error = array();

    public function __construct($registry)
    {
        parent::__construct($registry);
        if(PRODUCTID == 3){
            $this->redirect(
                $this->url->link('error/permission', '', 'SSL')
            );
            exit();
        }
    }
 
    public function index() {
        $this->language->load('setting/setting'); 

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['direction'] = $this->language->get('direction');
        
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $result_json = array(
                'success' => '0',
                'errors' => array(),
                'success_msg' => ''
            );

            if ($this->validate()) {
                if ($this->request->post['config_mail_protocol'] == 'expandcart_relay') {
                        $mail = new Mail();
                        $data = $mail->configureExpandCartMail($this->request->post);
                        $this->model_setting_setting->insertUpdateSetting('config', $data);

                }else{
                    $this->model_setting_setting->insertUpdateSetting('config', $this->request->post);
                }
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            } else {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
            }

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->data['breadcrumbs'] = array(
            array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')),
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/setting_mail'))
        );

        $this->data['action'] = $this->url->link('setting/setting_mail');
        
        $this->data['cancel'] = $this->url->link('setting/setting_mail');

        if ($this->config->get('config_mail_protocol') == 'expandcart_relay') {
            $this->value_from_post_or_config([
                'config_mail_protocol',
                'config_alert_emails',
                'config_alert_mail',
                'config_account_mail',
                'config_mail_reply_to',
            ]);
        }else{
            $this->value_from_post_or_config([
                'config_mail_protocol',
                'config_mail_parameter',
                'config_smtp_host',
                'config_smtp_username',
                'config_smtp_password',
                'config_smtp_port',
                'config_smtp_timeout',
                'config_alert_emails',
                'config_alert_mail',
                'config_account_mail',
            ]);
        }
        $this->template = 'setting/setting_mail.expand';
        $this->base = "common/base";

        $this->response->setOutput($this->render_ecwig());
    }

    private function value_from_post_or_config($array)
    {
        foreach ($array as $elem)
        {
            $this->data[$elem] = $this->request->post[$elem] ?: $this->config->get($elem);
        }
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'setting/setting_mail')) {
            $this->error['error'] = $this->language->get('error_permission');
        }


        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }
            
        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Prepare The Expandcart SMTP data
     * @param $data
     * @return mixed
     */
    private function configureExpandRelayData($data){
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
        $result['config_mail_reply_to'] = $data['config_mail_reply_to'];

        return $result;
    }
}
?>