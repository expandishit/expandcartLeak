<?php

class ControllerPaymentNbeBank extends Controller
{
    public function index()
    {
        $this->language->load('payment/nbe_bank');
        $this->load->model('checkout/order');
        $this->load->library('SMEOnline/SMEOnline_currencyAmount');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $amount = $order_info['total'];
        $currency = $this->config->get('config_currency');
        $smeCurrencyAmount = new SMEOnlineCurrencyAmount();
        $charged_amount = $smeCurrencyAmount->formatAmountCurrency($amount, $currency);
        $this->data['credit_card_charged_info'] = $this->language->get('credit_card_charged_info') . $charged_amount;

        $data['nbe_bank_api_url'] = $this->config->get('nbe_bank_api_url');

        $data['months'] = array();

        for ($i = 1; $i <= 12; $i++) {
            $data['months'][] = array(
                'text' => strftime('%B', mktime(0, 0, 0, $i, 1, 2000)),
                'value' => sprintf('%02d', $i)
            );
        }

        $today = getdate();

        $data['year_expire'] = array();

        for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
            $data['year_expire'][] = array(
                'text' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
                'value' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i))
            );
        }

        if ($this->config->get('nbe_bank_test_mode') == '0') {
            $data['test_mode'] = false;
        } elseif ($this->config->get('nbe_bank_test_mode') == '1') {
            $data['test_mode'] = true;
        }

        if (file_exists(
            DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' .
            $this->config->get('config_template') . '/template/payment/nbe_bank.tpl'
        )) {
            $this->template = 'customtemplates/' . STORECODE . '/' .
                $this->config->get('config_template') . '/template/payment/nbe_bank.tpl';
        } else {
            $this->template = $this->config->get('config_template') . '/template/payment/nbe_bank.tpl';
            $this->template = 'default/template/payment/nbe_bank.tpl';
        }

        $this->data = $data;

        $this->render();
    }

    public function send()
    {
        $this->load->library('SMEOnline/SMEOnline_API');
        $this->load->library('SMEOnline/SMEOnline_currencyAmount');
        $this->load->model('checkout/order');
        $this->language->load('payment/nbe_bank');
        $response = array();
        if ($this->session->data['order_id']) {
            // Create new order
            $new_order = $this->model_checkout_order->confirm(
                $this->session->data['order_id'],
                $this->config->get('nbe_bank_pending_status_id')
            );

            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            $redirectionUrl = $this->url->link('payment/nbe_bank/confirm', '', 'SSL');

            $api_url = $this->config->get('nbe_bank_api_url');

            if (substr($api_url, -1) == '/') {
                $api_url = $api_url . 'v2';
            } else {
                $api_url = $api_url . '/v2';
            }

            $txn = new SMEOnline_API(
                $this->config->get('nbe_bank_username'),
                $this->config->get('nbe_bank_password'),
                $this->config->get('nbe_bank_merchant_number'),
                $api_url
            );


            if ($this->config->get('nbe_bank_payment_action') == 'payment_only') {
                $txn->setAction("payment");
            } elseif ($this->config->get('nbe_bank_payment_action') == 'preauth_capture') {
                $txn->setAction("preauth");
            }
            $amount = $order_info['total'];
            $currency = $this->config->get('config_currency');
            $smeCurrencyAmount = new SMEOnlineCurrencyAmount();

            $txn->setAmount($smeCurrencyAmount->getLowestDenominationAmount($amount, $currency));

            $txn->setCurrency($currency);
            $txn->setMerchantReference("");
            $txn->setCrn1($order_info['order_id']);
            if ($order_info['customer_id'] != 0) {
                $txn->setCrn2($order_info['customer_id']);
            } else {
                $txn->setCrn2('');
            }
            $txn->setCrn3('');
            $txn->setBillerCode(null);

            if ($this->config->get('nbe_bank_test_mode') == '0') {
                $txn->setTestMode(false);
            } elseif ($this->config->get('nbe_bank_test_mode') == '1') {
                $txn->setTestMode(true);
            }

            $txn->setRedirectionUrl($redirectionUrl);
            $txn->setWebHookUrl(null);

            $user_agent = "SMEOnline:3016:1|OpenCart " . VERSION;
            $txn->setUserAgent($user_agent);
            $result = $txn->createAuthkey();
            print_r($result);exit;

            if (isset($result->AuthKey)) {
                $response['success'] = true;
                $response['AuthKey'] = $result->AuthKey;
            } else {
                $response['success'] = false;
                $response['error'] = $this->language->get('error_request');
            }
        } else {
            $response['redirect'] = $this->url->link('checkout/cart');
        }
        if (ob_get_length()) ob_end_clean();
        $this->response->setOutput(json_encode($response));
    }

    public function confirm()
    {
        $this->load->library('SMEOnline/SMEOnline_API');
        $this->load->library('SMEOnline/SMEOnline_currencyAmount');
        $message = array();
        $this->language->load('payment/nbe_bank');
        $this->load->model('checkout/order');
        $this->load->model('payment/nbe_bank');

        $smeCurrencyAmount = new SMEOnlineCurrencyAmount();

        if ($this->request->get['ResponseCode'] != '0') {
            $message['error'] = $_SERVER['REQUEST_URI'];
        } else {
            $resultKey = $this->request->get['ResultKey'];
            $api_url = $this->config->get('nbe_bank_api_url');

            if (substr($api_url, -1) == '/') {
                $api_url = $api_url . 'v2';
            } else {
                $api_url = $api_url . '/v2';
            }

            $txn = new SMEOnline_API(
                $this->config->get('nbe_bank_username'),
                $this->config->get('nbe_bank_password'),
                $this->config->get('nbe_bank_merchant_number'),
                $api_url
            );
            $user_agent = "SMEOnline:3016:1|OpenCart " . VERSION;
            $txn->setUserAgent($user_agent);
            $result = $txn->getTransactionResult($resultKey);

            if (isset($result->TxnResp->ResponseCode)) {

                $order_info = $this->model_checkout_order->getOrder($result->TxnResp->Crn1);
                $currency = $this->config->get('config_currency');
                $amount = $smeCurrencyAmount->standardizeAmount($order_info['total'], $currency);

                if ($result->TxnResp->ResponseCode == '0') {

                    if (
                        $result->TxnResp->Amount != $smeCurrencyAmount->getLowestDenominationAmount(
                            $order_info['total'], $currency
                        )
                    ) {
                        $this->session->data['error'] = $this->language->get('error_request');
                        $this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
                    }

                    if ($result->TxnResp->Action == 'payment') {

                        $paymentOrPreauth = $this->language->get('text_payment');
                        $updateOrderStatus = $this->config->get('nbe_bank_payment_status_id');

                    } elseif ($result->TxnResp->Action == 'preauth') {

                        $paymentOrPreauth = $this->language->get('text_preauth');
                        $updateOrderStatus = $this->config->get('nbe_bank_preauth_status_id');

                    }

                    $updateOrderMessage = (
                        $this->language->get('nbe_heading_title') . ' ' .
                        $paymentOrPreauth . ' of ' .
                        $smeCurrencyAmount->formatAmountCurrency($amount, $currency) . " " .
                        $this->language->get('text_approved') . "\n" .
                        $this->language->get('text_receipt_number') . ' ' .
                        $result->TxnResp->ReceiptNumber
                    );

                    $this->model_checkout_order->update(
                        $order_info['order_id'],
                        $updateOrderStatus,
                        $updateOrderMessage,
                        true
                    );

                    // TODO replace this method by the confirm payment method directly
//                    $this->model_payment_nbe_bank->addTransaction($result, $amount);

                    $this->model_payment_nbe_bank->sendTransaction(
                        $result,
                        $order_info
                    );

                    $this->redirect($this->url->link('checkout/success'));

                } else {
                    if ($result->TxnResp->Action == 'payment') {
                        $paymentOrPreauth = $this->language->get('text_payment');
                    } elseif ($result->TxnResp->Action == 'preauth') {
                        $paymentOrPreauth = $this->language->get('text_preauth');
                    }

                    $updateOrderMessage = (
                        $this->language->get('nbe_heading_title') . ' ' .
                        $paymentOrPreauth . ' of ' .
                        $smeCurrencyAmount->formatAmountCurrency($amount, $currency) . " " .
                        $this->language->get('text_declined') . "\n" .
                        $this->language->get('error_decline_reason') . ' ' .
                        $result->TxnResp->ResponseText . ".\n" .
                        $this->language->get('text_receipt_number') . ' ' .
                        $result->TxnResp->ReceiptNumber
                    );

                    $this->model_checkout_order->update(
                        $order_info['order_id'],
                        $this->config->get('nbe_bank_failed_status_id'),
                        $updateOrderMessage,
                        true
                    );

//                    $this->model_payment_nbe_bank->addTransaction($result, $amount);

                    $this->model_payment_nbe_bank->sendTransaction(
                        $result,
                        $order_info
                    );

                    $message['error'] = $this->language->get('error_declined') . "<br>"
                        . $this->language->get('error_decline_reason') . ' ' . $result->TxnResp->ResponseText;
                }
            } else {
                $message['error'] = $this->language->get('error_request');
            }
        }
        $this->session->data['error'] = $message['error'];
        $this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
    }
}
