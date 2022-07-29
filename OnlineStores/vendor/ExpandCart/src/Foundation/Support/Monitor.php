<?php

namespace ExpandCart\Foundation\Support;

class Monitor
{
    public function __construct()
    {
        $this->monitor = function_exists('xhprof_enable');
    }

    public function isMonitoring()
    {
        return $this->monitor;
    }

    public function start()
    {
        if ($this->monitor == false) {
            return false;
        }

        xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
    }

    public function report($report)
    {
        if (defined('EC_MONITORING') == false || $this->monitor == false) {
            return false;
        }

        if (isset(EC_MONITORING['stores']) && isset(EC_MONITORING['stores'][STORECODE]) == false) {
            return false;
        }

        $body = bin2hex(gzencode(json_encode([
            'batch' => 'main',
            'id' => STORECODE,
            'ip_address' => $report['ip'],
            'uid' => $report['uid'],
            'time' => microtime(true) - $report['startedAt'],
            'memory' => $report['mem'],
            'trace' => $report['xhprof'],
            'base_request' => $_SERVER['REQUEST_URI'],
            'request' => [
                'request' => $_REQUEST ?? [],
                'server' => $_SERVER ?? []
            ]
        ])));

        $soc = new \ExpandCart\Foundation\Network\UdpSocket;
        $soc->connect(EC_MONITORING['server'], EC_MONITORING['port']);
        $soc->send([
            'headers' => [
                'auth' => time(),
                'method' => 'post',
                'action' => 'monitor',
                'content-encoding' => 'gzip',
                'udp-version' => '1.0'
            ],
            'body' => $body
        ]);

        $soc->close();
    }
}
