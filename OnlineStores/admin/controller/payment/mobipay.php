<?php
use ExpandCart\Foundation\Support\Facades\Url;
class ControllerPaymentMobipay extends Controller
{
    private $error = array(); 

    public function index()
    {
        $this->language->load('payment/mobipay');
        $this->document->setTitle($this->language->get('heading_title_mobipay'));
        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => Url::addPath(['common', 'home'])->format(),
                'separator' => false
            ],
            [
                'text' => $this->language->get('mn_setting_menu_payment'),
                'href' => Url::addPath(['extension', 'payment'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title_mobipay'),
                'href' => Url::addPath(['payment', 'mobipay'])->format(),
                'separator' => ' :: '
            ],
        ];

        $this->initializer([
            'geo' => 'localisation/geo_zone',
            'statuses' => 'localisation/order_status',
            'mobipay' => 'payment/mobipay/settings'
        ]);

        $this->data['geo_zones'] = $this->geo->getGeoZones();
        $this->data['order_statuses'] = $this->statuses->getOrderStatuses();
        $this->data['successUrl'] = HTTP_CATALOG.'index.php?route=payment/mobipay/success';
        $this->data['failedUrl'] = HTTP_CATALOG.'index.php?route=payment/mobipay/failed';
        $this->data['mobipay'] = $this->mobipay->getSettings();
        $this->template = 'payment/mobipay/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render_ecwig());
    }
    /*======================================================================================================= */
    public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->response->setOutput(json_encode([
                'success' => 0,
                'errors' => [
                    'invalid request'
                ]
            ]));
            return;
        }
        $this->language->load('payment/mobipay');
        if ( ! $this->validate() )
        {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->error;
            $this->response->setOutput(json_encode($result_json));
            return;
        }
        $this->initializer([
            'mobipay' => 'payment/mobipay/settings'
        ]);
        $mobipay = $this->request->post['mobipay'];
        if ($this->mobipay->validate($mobipay) == false) {
            $this->response->setOutput(json_encode([
                'success' => 0,
                'errors' => $this->mobipay->getErrors()
            ]));
            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->checkIfExtensionIsExists('payment', 'mobipay', true);

        $this->tracking->updateGuideValue('PAYMENT');

        $this->mobipay->updateSettings($mobipay);
        $this->response->setOutput(json_encode([
            'success' => 1,
            'success_msg' => $this->language->get('text_settings_success')
        ]));
        return;
    }
    /*======================================================================================================= */
    private function validate()
    {
        if ( ! $this->user->hasPermission('modify', 'payment/mobipay') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        return $this->error ? false : true;
    }
}