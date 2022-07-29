<?php

class ModelPaymentNbeBank extends Model
{

    public function getMethod($address, $total)
    {
        $this->language->load_json('payment/nbe_bank');

        $settings = $this->config->get('nbe_bank_field_name');
        $current_lang = $this->session->data['language'];
		$this->load->model('localisation/language');
		$language = $this->model_localisation_language->getLanguageByCode($current_lang);
		$current_lang = $language['language_id'];
		if ( !empty($settings['field_name_'.$current_lang]) )
		{
			$title = $settings['field_name_'.$current_lang];
		}
		else
		{
			$title = $this->language->get('text_title');
		}

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('nbe_bank_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('nbe_bank_total') > 0 && $this->config->get('nbe_bank_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('nbe_bank_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'nbe_bank',
                'title' => $title , //$this->language->get('text_title'),
                'sort_order' => $this->config->get('nbe_bank_sort_order')
            );
        }

        return $method_data;
    }
    
    // we replce all this old code by confirm function and transaction function
/*
    public function addTransaction($transaction_data, $amount, $request_data = array())
    {
        if ($transaction_data->TxnResp->ResponseCode == '0') {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "nbe_bank_order_transaction` SET `nbe_bank_order_id` = '" . (int)($transaction_data->TxnResp->Crn1) . "', `transaction_id` = '" . $this->db->escape($transaction_data->TxnResp->TxnNumber) . "', `parent_transaction_id` = '" . $this->db->escape('0') . "', `created` = NOW(), `note` = '" . $this->db->escape($transaction_data->TxnResp->ResponseText) . "', `payment_type` = '" . $this->db->escape($transaction_data->TxnResp->Action) . "', `payment_status` = '" . $this->db->escape('success') . "', `currency_code` = '" . $this->db->escape($this->config->get('config_currency')) . "', `amount` = '" . (double)($amount) . "'");
        } else {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "nbe_bank_order_transaction` SET `nbe_bank_order_id` = '" . (int)($transaction_data->TxnResp->Crn1) . "', `transaction_id` = '" . $this->db->escape($transaction_data->TxnResp->TxnNumber) . "', `parent_transaction_id` = '" . $this->db->escape('0') . "', `created` = NOW(), `note` = '" . $this->db->escape($transaction_data->TxnResp->ResponseText) . "', `payment_type` = '" . $this->db->escape($transaction_data->TxnResp->Action) . "', `payment_status` = '" . $this->db->escape('failure') . "', `currency_code` = '" . $this->db->escape($this->config->get('config_currency')) . "', `amount` = '" . (double)($amount) . "'");
        }


        $nbe_bank_order_transaction_id = $this->db->getLastId();

        if ($request_data) {
            $serialized_data = serialize($request_data);

            $this->db->query("
				UPDATE " . DB_PREFIX . "nbe_bank_order_transaction
				SET call_data = '" . $this->db->escape($serialized_data) . "'
				WHERE nbe_bank_order_transaction_id = " . (int)$nbe_bank_order_transaction_id . "
				LIMIT 1
			");
        }

        return $nbe_bank_order_transaction_id;
    }

    public function sendTransaction($result, $order_info)
    {
        $this->load->library('SMEOnline/SMEOnline_API');
        $this->load->library('SMEOnline/SMEOnline_currencyAmount');
        $this->language->load_json('payment/smeonline');
        $this->load->model('checkout/order');

        $smeCurrencyAmount = new SMEOnlineCurrencyAmount();

        $order_id = $order_info['order_id'];

        $customer_id = $order_info['customer_id'];

        $currency = $this->config->get('config_currency');
        $requestAction = $result->TxnResp->Action;
        $requestAmount = $result->TxnResp->Amount;
        $txnNumber = $result->TxnResp->TxnNumber;

        $message = [];


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

        if (isset($txnNumber)) {
            if ($requestAction == 'capture') {
                $action = 'capture';
                $amount = $smeCurrencyAmount->getLowestDenominationAmount($order_info['total'], $currency);
            } elseif ($requestAction == 'refund') {
                $action = 'refund';
                $amount = $smeCurrencyAmount->getLowestDenominationAmount($requestAmount, $currency);
            }

            $txn->setAction($action);
            $txn->setAmount($amount);
            $txn->setCurrency($currency);

            $txn->setMerchantReference("");
            $txn->setCrn1($order_id);
            if ($order_info['customer_id'] != 0) {
                $txn->setCrn2($order_info['customer_id']);
            } else {
                $txn->setCrn2('');
            }
            $txn->setCrn3('');
            $txn->setBillerCode(null);
            $txn->setSubType('single');
            $txn->setType('internet');
            $txn->setOriginalTxnNumber($txnNumber);

            if ($this->config->get('nbe_bank_test_mode') == '0') {
                $txn->setTestMode(false);
            } elseif ($this->config->get('nbe_bank_test_mode') == '1') {
                $txn->setTestMode(true);
            }

            $user_agent = "SMEOnline:3016:1|OpenCart " . VERSION;
            $txn->setUserAgent($user_agent);
            $result = $txn->processTransaction();

            if (isset($result->TxnResp->ResponseCode)) {
                if ($result->TxnResp->ResponseCode == '0') {

                    if ($result->TxnResp->Action == 'capture') {
                        $dataComment = $this->language->get('text_capture');

                        $dataAmount = $order_info['total'];
                    } else {
                        $dataComment = $this->language->get('text_refund');

                        $dataAmount = $requestAmount;
                    }

                    $comment = (
                        $this->language->get('nbe_heading_title') . ' ' .
                        $dataComment . ' of ' .
                        $smeCurrencyAmount->formatAmountCurrency($dataAmount, $currency) . " " .
                        $this->language->get('text_approved') . "<br>\n" .
                        $this->language->get('text_receipt_number') . ' ' .
                        $result->TxnResp->ReceiptNumber
                    );

                    $data = array(
                        'order_status_id' => ($result->TxnResp->Action == 'capture' ?
                            $this->config->get('nbe_bank_captured_status_id') :
                            $this->config->get('nbe_bank_refunded_status_id')
                        ),
                        'comment' => $comment,
                        'notify' => 0
                    );

                    if ($result->TxnResp->Action == 'capture') {
//                        $this->model_payment_smeonline->addTransaction($result, $txnNumber, $smeCurrencyAmount->standardizeAmount($order_info['total'], $currency), $currency);
                        $this->model_checkout_order->addOrderHistory($order_id, $data);
                    } elseif ($result->TxnResp->Action == 'refund') {
//                        $this->model_payment_smeonline->addTransaction($result, $txnNumber, $requestAmount, $currency);
                        $this->model_checkout_order->addOrderHistory($order_id, $data);
                    }

                    $message['success'] = $data['comment'];
                } else {

                    if ($result->TxnResp->Action == 'capture') {

                        $dataComment = $this->language->get('text_capture');

                        $dataAmount = $order_info['total'];

                    } else {
                        $dataComment = $this->language->get('text_refund');

                        $dataAmount = $requestAmount;
                    }

                    $comment = (
                        $this->language->get('nbe_heading_title') . ' ' .
                        $dataComment . ' of ' .
                        $smeCurrencyAmount->formatAmountCurrency($requestAmount, $currency) . ' ' .
                        $this->language->get('text_declined') . "<br>\n" .
                        $this->language->get('error_decline_reason') . ' ' .
                        $result->TxnResp->ResponseText . ".<br>\n" .
                        $this->language->get('text_receipt_number') . ' ' .
                        $result->TxnResp->ReceiptNumber
                    );

                    $data = array(
                        'order_status_id' => $order_info['order_status_id'],
                        'comment' => $comment,
                        'notify' => 0
                    );

                    if ($result->TxnResp->Action == 'capture') {
//                        $this->model_payment_smeonline->addTransaction($result, $txnNumber, $smeCurrencyAmount->standardizeAmount($order_info['total'], $currency), $currency);
                        $this->model_checkout_order->addOrderHistory($order_id, $data);
                    } elseif ($result->TxnResp->Action == 'refund') {
//                        $this->model_payment_smeonline->addTransaction($result, $txnNumber, $requestAmount, $currency);
                        $this->model_checkout_order->addOrderHistory($order_id, $data);
                    }

                    $message['error'] = $data['comment'];

                }
            } else {
                $message['error'] = $this->language->get('error_request');
            }
        }

        if (isset($message['error'])) {
            return false;
        } else {
            return true;
        }

        return $message;
    }
 */

}
