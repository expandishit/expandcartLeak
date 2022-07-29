<?php

namespace ExpandCart\Foundation\Http;

use ExpandCart\Foundation\Exceptions\ExpandCartException;
use ExpandCart\Foundation\Log\Logger;

class Response
{
    protected static $renderer;

    protected static $registry;

    private static function render($template, $data)
    {
        $file = sprintf('/view/%s.expand', $template);
        if (IS_ADMIN) {
            $file = sprintf('%s.expand', $template);
        }

        // $file = file_get_contents($file);

        // $env = new \Twig_Environment(new \Twig_Loader_String());
        $env = new \Twig_Environment(new \Twig_Loader_Filesystem([DIR_TEMPLATE, DIR_APPLICATION]));
        if (IS_ADMIN) {
            $registry = \Container::get('registry');
            $data['base']['base'] = HTTPS_SERVER;
            $env->addExtension(new \Twig_Extension_ExpandcartAdminGlobals($registry));
            $env->addExtension(new \Twig_Extension_ExpandcartAdmin($registry));
        } else {
            $registry = \Container::get('registry');
            $env->addExtension(new \Twig_Extension_Expandcart($registry));
        }

        $template = $env->load($file);

        return $template->render($data);

        return $env->render(
            $file,
            $data
        );
    }

    private static function render__($template, $data)
    {
        $file = sprintf(DIR_APPLICATION . 'view/%s.expand', $template);

        $file = file_get_contents($file);

        $needles = $replacements = [];

        foreach ($data as $k => $v) {
            $needles[] = sprintf("#\{\{\\s*$k\s*}\}#i");
            $replacements[] = is_array($v) ? json_encode($v) : $v;
        }

        return preg_replace($needles, $replacements, $file);
    }

    protected static function setRenderer($renderer)
    {
        self::$renderer = $renderer;

        return new static;
    }

    public static function output($template, $data)
    {
        echo self::render($template, $data);
    }

    public static function error($message)
    {
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

        self::log($filename, $message);

        $trace = null;
        if (
            (defined('STAGING_MODE') && STAGING_MODE == 1) ||
            (defined('DEV_MODE') && DEV_MODE == true)
        ) {
            $trace = $message;
        }

        return self::output(sprintf('error/%d', $code), [
            'debug_hash' => strtolower(STORECODE) . ':' . $hash,
            'debug_message' => 'Please submit the following string to our customer service team',
            'trace' => $trace
        ]);
    }

    public static function log($filename, $content)
    {
        $file = DIR_LOGS . 'ex.' . $filename . '.json';

        file_put_contents($file, json_encode($content, JSON_PRETTY_PRINT));
    }
}
