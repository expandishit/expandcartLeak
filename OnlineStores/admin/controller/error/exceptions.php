<?php

use ExpandCart\Foundation\Log\Logger;

class ControllerErrorExceptions extends Controller
{
    public function index($message)
    {
        $this->language->load('error/not_found');
 
        $this->document->setTitle($this->language->get('exception_heading'));

        $this->data['exception_heading'] = $this->language->get('exception_heading');

        $code = $message->getCode() ?: 500;

        if (!$message instanceof ExpandCartException) {
            $message = (function ($message) {
                return [
                    'type' => get_class($message),
                    'message' => $message->getMessage(),
                    'line' => $message->getLine(),
                    'file' => $message->getFile(),
                    'trace' => $message->getTrace()
                ];
            })($message);
        }

        $hash = bin2hex(random_bytes(20));
        $filename = time() . '.' . $hash;

        $gaurd = Logger::logGaurd(function ($path) {

            $count = 0;
            $files = [];
            $unshift = [];

            $logFiles = glob($path . 'ex.*.*.json');

            foreach ($logFiles as $file) {
                $count++;
                $files[] = $file;

                if ($count >= 100) {
                    $unshift[] = array_shift($files);
                }
            }

            foreach ($unshift as $ufile) {
                if (file_exists($ufile)) {
                    unlink($ufile);
                }
            }

            return true;
        });

        $log = function ($filename, $content) {

            $file = DIR_LOGS . 'ex.' . $filename . '.json';

            file_put_contents($file, json_encode($content, JSON_PRETTY_PRINT));
        };
        $log($filename, $message);

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('exception_heading'),
            'href'      => $this->url->link('error/not_found', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['debug_hash'] = strtolower(STORECODE) . ':' . $hash;
        $this->data['debug_message'] = 'Please submit the following string to our customer service team';

        if (
            (defined('STAGING_MODE') && STAGING_MODE == 1) ||
            (defined('DEV_MODE') && DEV_MODE == true)
        ) {
            $this->data['trace'] = $message;
        }

        $this->template = 'error/500.expand';
        $this->base = "common/base";
                
        $this->response->setOutput($this->render_ecwig());
    }
}
