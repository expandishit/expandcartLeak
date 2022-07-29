<?php
class MsWhats extends Model
{

    /**
     * Send seller whatsapp messages
     *
     * @param array $messages array each message have those values (type => MsSeller::STATUS_, 
     * data => (seller_mobile, seller_nickname, seller_lastname, seller_firstname, seller_email))
     * @return array $messages
     */
    public function send(array $messages = [])
    {
        return $this->checkIfCanSendSms(function ($app_status) use ($messages) {
            return $this->sendMessages($app_status, $messages);
        });
    }

    private function checkIfCanSendSms($callback)
    {
        $this->load->model('module/whatsapp');
        $this->language->load('sale/customer');
        $app_status = $this->model_module_whatsapp->isInstalled() && (int)$this->config->get('whatsapp_config_notify_seller_on_status_change') === 1;
        return $callback($app_status);
    }

    private function sendMessages(bool $app_status, array $messages)
    {
        return $this->mapMessages($app_status, $messages, function ($message) {
            return $this->sendMessage($message);
        });
    }

    private function mapMessages($app_status, $messages, $callback)
    {
        return array_map(function ($message) use ($app_status, $callback) {
            // validate msg
            if ($app_status === true) {
                if (isset($message['data']['seller_mobile']) && !empty($message['data']['seller_mobile'])) {
                    $message['status'] = $callback($message);
                    if ($message['status'] === true) {
                        $message['message'] = 'successfully send message';
                    } else {
                        $message['message'] = 'validation error for message input';
                    }
                } else {
                    $message['status'] = false;
                    $message['message'] = 'seller_mobile is undefined';
                }
            } else {
                $message['status'] = false;
                $message['message'] = 'service not installed or whatsapp config for seller not enabled';
            }
            return $message;
        }, $messages);
    }

    private function sendMessage(array $message)
    {
        switch ($message['type']) {
            case MsSeller::STATUS_ACTIVE:
            case MsSeller::STATUS_INACTIVE:
            case MsSeller::STATUS_DISABLED:
            case MsSeller::STATUS_DELETED:
            case MsSeller::STATUS_UNPAID:
            case MsSeller::STATUS_NOPAYMENT:
                return $this->model_module_whatsapp->notify_or_not_on_admin_change_seller_status($message);
        }

        return false;
    }
}
