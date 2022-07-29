<?php

use ExpandCart\Foundation\Analytics\Live;

class ControllerAnalyticsLive extends Controller
{
    public function getLastVisitsDetails()
    {
        $live = (new Live())->setModule('Live')->setMethod('getLastVisitsDetails');

        $result = $live->fetch();

        $this->response->setOutput($result);
    }

    public function getCounters()
    {
        $live = (new Live())->setModule('Live')->setMethod('getCounters');

        $result = $live->setLastMinutes(30)->fetch();

        $this->response->setOutput($result);
    }

    public function getMapMarkers()
    {
        $now = new \DateTime("now", new \DateTimeZone("UTC"));

        $beforeXHour = $now->sub(new \DateInterval('PT2H'));

        $live = (new Live())->setMethod('getLastVisitsDetails')->setFilterLimit(0);

        $result = $live->fetch();

        $data = [];

        $generateName = function ($value) {

            $ago = (time()) - $value['lastActionTimestamp'];

            $minutesAgo = round($ago / 60);

            if ($minutesAgo > 120) {

            } else {
                $timeAgo = $minutesAgo;
            }

            return [
                'city' => $value['city'],
                'country' => $value['country'],
                'countryFlag' => 'http://analytics.expandcart.com/' . $value['countryFlag'],
                'browserIcon' => 'http://analytics.expandcart.com/' . $value['browserIcon'],
                'operatingSystemIcon' => 'http://analytics.expandcart.com/' . $value['operatingSystemIcon'],
                'pageTitle' => $value['actionDetails'][0]['pageTitle'],
                'timeAgo' => $timeAgo,
                'visitLocalTime' => $value['visitLocalTime'],
            ];

        };

        // $result = array_reverse($result);

        $desktop = $mobile = 0;

        $topPages = $topReferrals = [];

        foreach ($result as $key => $value) {
            if ($value['latitude'] && $value['longitude'] && $value['location']) {

                if ((time() - $value['lastActionTimestamp']) <= (300 * 1) && !isset($data[$value['visitorId']])) {
                    $data[$value['visitorId']] = [
                        'latLng' => [$value['latitude'], $value['longitude']],
                        'name' => $value['location'],
                        'details' => $generateName($value),
                        'fullDetails' => $value,
                        'style' => [
                            'fill-opacity' => 0.5
                        ]
                    ];

                    if ($value['deviceType'] == 'Desktop') {
                        $desktop++;
                    } else {
                        $mobile++;
                    }

                    $end = end($value['actionDetails']);

                    $topPages[$end['url']]++;

                    reset($value['actionDetails']);

                    if ($value['referrerType'] != 'direct') {
                        $topReferrals[] = $value['referrerUrl'];
                    }
                }
            }
        }

        arsort($topPages);

        $response['data'] = $data;
        $response['online'] = [
            'count' => count($data),
            'desktop' => $desktop,
            'mobile' => $mobile,
        ];
        $response['topPages'] = $topPages;

        $this->response->setOutput(json_encode($response));
    }

    public function getVisitorProfile()
    {
        $live = (new Live())->setMethod('getVisitorProfile');

        $result = $live->setVisitorId(30)->fetch();

        $this->response->setOutput($result);
    }

    public function getMostRecentVisitorId()
    {
        $live = (new Live())->setMethod('getMostRecentVisitorId');

        $result = $live->fetch();

        $this->response->setOutput($result);
    }
}
