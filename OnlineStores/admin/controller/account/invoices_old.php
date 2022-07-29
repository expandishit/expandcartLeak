<?php

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class ControllerAccountInvoicesOld extends Controller
{
    public function store()
    {


        if (isset($_GET['downgrade']) && $_GET['downgrade']==1){
            $this->session->data['charge']['status'] = 'downgrade';
            $response = [
                'status' => 'downgrade',
                'message'=> $this->language->get('success_hint'),
            ];
            $this->response->json($response);
            return;
        }

        unset($this->session->data['charge']);

        $this->initializer([
            'account/transaction',
            'account/invoice',
            'account/account'
        ]);

        $debugId = function () {
            assert(strlen($data) == 16);
            $data = random_bytes(16);

            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

            $id = vsprintf('%s%s-%s-%s-%s-%s%s%s-%s', array_merge(str_split(bin2hex($data), 4), [time()]));

            $this->transaction->update(['debug_id' => $id]);

            return $id;
        };

        if (!isset($_GET['auth_token']) || strlen($_GET['auth_token']) < 5) {
            $this->session->data['charge'] = [
                'status' => 'ERR',
                'error' => 'INVALID_TOKEN',
                'errors' => ['Illegal access']
            ];
            $this->response->redirect($this->url->link('account/charge/failed', '', true));
        }

        $this->language->load('billingaccount/plans');

        $decrypt = function ($ciphertext, $key = null) {
            return  base64_decode($ciphertext);

            $key = $key ?: 'EC_' . STORECODE;
            $c = hex2bin($ciphertext);
            $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
            $iv = substr($c, 0, $ivlen);
            $hmac = substr($c, $ivlen, $sha2len = 32);
            $ciphertext_raw = substr($c, $ivlen + $sha2len);
            $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
            $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
            if (hash_equals($hmac, $calcmac)) {
                return $original_plaintext;
            }

            return false;
        };

        $token = json_decode($decrypt($_GET['auth_token']), true);

        if (!isset($token['transaction_id']) || !isset($token['timestamp'])) {
            $this->session->data['charge'] = [
                'status' => 'ERR',
                'error' => 'INVALID_TOKEN',
                'errors' => ['token is invalid or expired'],
                'debug_id' => $debugId()
            ];
            $this->response->redirect($this->url->link('account/charge/failed', '', true));
        }

        /*if ((time() - $token['timestamp']) > 10000) {
            $this->response->json([
                'status' => 'ERR',
                'error' => 'EXPIRED_TOKEN',
                'errors' => ['token had been expired']
            ]);
            return;
        }*/

        $transaction = $this->transaction->selectByTransactionId($token['transaction_id']);

        if (!$transaction) {
            $this->session->data['charge'] = [
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROW',
                'errors' => ['transaction is not defined'],
                'debug_id' => $debugId()
            ];
            $this->response->redirect($this->url->link('account/charge/failed', '', true));
        }

        $transaction = null;
        if (isset($token['transaction_id']) && isset($token['timestamp'])) {
            $transaction = $this->transaction->selectByTransactionId($token['transaction_id']);

            if ($transaction['transaction_status'] == 0) {

//                if ($transaction['payment_method'] !="paypal"){
//                    $sturi = $this->url->link(
//                        'account/invoices/storeTransaction',
//                        sprintf('auth_token=%s', urlencode($_GET['auth_token'])),
//                        true
//                    )->format();
//                    exec(vsprintf('curl --cookie "%s" "%s" > /dev/null &', [
//                        session_name() . '=' . session_id(),
//                        $sturi,
//                    ]), $out);
//                }
//                else{
//                    $this->storeTransaction();
//                }

                $this->storeTransaction();

                $this->transaction->update([
                    'transaction_status' => '1'
                ]);
            }
        }

//        $store_account = $this->account->selectByStoreCode(STORECODE);
//
//        if (
//            ($transaction['payment_method'] == "stripe" && $store_account['payment_method']== "paypal") ||
//            ($store_account['plan_type']== "monthly" && $transaction['plan_type']= "annually" )
//        ){
//            $serviceid= $store_account['service_id'];
//            if ($serviceid){
//                $this->invoice->updateClientProduct((int)$serviceid);
//            }
//        }

        $ajax="";
        if ($transaction['payment_method'] !="paypal" && $_GET['3dsecure'] != 'true'){
            $ajax = "&ajax=1";
        }else{
            $this->session->data['charge']['status'] = 'OK';
            $this->response->redirect($this->url->link(
                'account/charge',
                'auth_token=' . $_GET['auth_token'].$ajax,
                true
            ));
        }

        $this->response->redirect($this->url->link(
            'account/charge/success',
            'auth_token=' . $_GET['auth_token'].$ajax,
            true
        ));
    }

    public function storeTransaction()
    {
        ignore_user_abort(true);
        unset($this->session->data['charge']);

        if (!isset($_GET['auth_token']) || strlen($_GET['auth_token']) < 5) {
            $this->response->json([
                'status' => 'ERR',
                'error' => 'INVALID_TOKEN',
                'errors' => ['Illegal access']
            ]);
            return;
        }

        $this->initializer([
            'account/transaction',
            'account/invoice',
            'account/account'
        ]);

        $debugId = function () {
            assert(strlen($data) == 16);
            $data = random_bytes(16);

            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

            $id = vsprintf('%s%s-%s-%s-%s-%s%s%s-%s', array_merge(str_split(bin2hex($data), 4), [time()]));

            $this->transaction->update(['debug_id' => $id]);

            return $id;
        };

        $decrypt = function ($ciphertext, $key = null) {
            return  base64_decode($ciphertext);

            $key = $key ?: 'EC_' . STORECODE;
            $c = hex2bin($ciphertext);
            $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
            $iv = substr($c, 0, $ivlen);
            $hmac = substr($c, $ivlen, $sha2len = 32);
            $ciphertext_raw = substr($c, $ivlen + $sha2len);
            $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
            $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
            if (hash_equals($hmac, $calcmac)) {
                return $original_plaintext;
            }

            return false;
        };

        $token = json_decode($decrypt($_GET['auth_token']), true);

        if (!isset($token['transaction_id']) || !isset($token['timestamp'])) {
            $this->session->data['charge'] = [
                'status' => 'ERR',
                'error' => 'INVALID_TOKEN',
                'errors' => ['token is invalid or expired'],
                'debug_id' => $debugId()
            ];
            $this->response->redirect($this->url->link('account/charge/failed', '', true));
        }

        $transaction = $this->transaction->selectByTransactionId($token['transaction_id']);
        if ( $transaction['payment_method'] == "paypal"){
            $transaction['transaction_id'] = "BA-".$token['transaction_id'];
        }
        if (!$transaction || !$transaction['transaction_id']) {
            $this->session->data['charge'] = [
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROW',
                'errors' => ['transaction is not defined']
            ];
            $this->response->redirect($this->url->link('account/charge/failed', '', true));
        }

        $transactionStatus = $this->invoice->createTransaction(
            $transaction['payment_method'],
            $transaction['transaction_id'],
            $transaction['amount'],
            (int)$transaction['invoice_id'],
            (float)$token['fees']
        );

        if (!$transactionStatus) {
            $this->session->data['charge'] = [
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['unable to create transaction'],
                'debug_id' => $debugId()
            ];
            $this->response->redirect($this->url->link('account/charge/failed', '', true));
        }

        $account = $this->account->selectByStoreCode(STORECODE);
        $this->invoice->updateTypeAndRelForInvoiceItems($account['service_id'], $transaction['invoice_id']);

        $this->transaction->update([
            'transaction_status' => '1'
        ]);
    }
}
