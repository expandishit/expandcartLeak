<?php

namespace Api\Models;

use Psr\Container\ContainerInterface;

class General
{
    private $load;
    private $registry;
    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->registry = $container['registry'];
        $this->config = $container['config'];
        $this->load = $container['loader'];
        $this->currency = $container['currency'];
    }

    public function getLiveVisits()
    {
        $this->load->model('report/home');
        return $this->registry->get('model_report_home')->liveVisits();
    }

    public function getHomeCardsData($startdate, $enddate)
    {
        $this->load->model('report/home');
        $reportModel = $this->registry->get('model_report_home');

        $data = [
            'recent_customers' => $reportModel->recentCustomersCount($startdate, $enddate),
            'average_order' => $this->currency->formatk($reportModel->averageOrderValue($startdate, $enddate), $this->config->get('config_currency')),
            'returning_customers' => $reportModel->returningCustomersCount($startdate, $enddate),
            'returns' => $reportModel->returnsCount($startdate, $enddate)
        ];

        return $data;
    }

    public function getRevenueChart($startdate, $enddate)
    {
        $data = array(
            'x' => array(),
            'y' => array()
        );

        $this->load->model('report/home');
        $reportModel = $this->registry->get('model_report_home');

        $begin    = new \DateTime($startdate);
        $end      = new \DateTime($enddate);
        $datediff = date_diff($begin, $end);

        if($datediff->days == 0) {
            $all = $reportModel->getSalesRevenue($startdate, $enddate, 'hours');
            for ($i = 0; $i < 24; $i++) {
                $key = -1;
                $key = array_search($i, array_column($all, 'hour'));

                if($i == 0) $data['x'][] = "12 AM";
                elseif($i < 13) $data['x'][] = "$i AM";
                else $data['x'][] = ($i-12) . " PM";

                if($key > -1) {
                    $data['y'][] = $all[$key]['total'];
                } else {
                    $data['y'][] = 0;
                }
            }
        } elseif($datediff->days < 31) {
            $all = $reportModel->getSalesRevenue($startdate, $enddate, 'days');

            $interval = \DateInterval::createFromDateString('1 day');
            $days     = new \DatePeriod($begin, $interval, $end);
            foreach ( $days as $day ) {
                $data['x'][] = $day->format('Y-m-d');
                $y = 0;
                foreach($all as $row) {
                    if($row['date'] == $day->format('Y-m-d')) {
                        $y += $row['total'];
                    }
                }
                $data['y'][] = $y;
            }
        } elseif($datediff->days < 365) {
            $all = $reportModel->getSalesRevenue($startdate, $enddate, 'months');
            $interval = \DateInterval::createFromDateString('1 month');
            $months     = new \DatePeriod($begin, $interval, $end);
            foreach ( $months as $month ) {
                $data['x'][] = $month->format('Y-m-01');
                $y = 0;
                foreach($all as $row) {
                    if($row['date'] == $month->format('Y-m-01')) {
                        $y += $row['total'];
                    }
                }
                $data['y'][] = $y;
            }
        } else {
            $all = $reportModel->getSalesRevenue($startdate, $enddate, 'years');
            $interval = \DateInterval::createFromDateString('1 year');
            $years     = new \DatePeriod($begin, $interval, $end);
            foreach ( $years as $year ) {
                $data['x'][] = $year->format('Y');
                $y = 0;
                foreach($all as $row) {
                    if($row['year'] == $year->format('Y')) {
                        $y += $row['total'];
                    }
                }
                $data['y'][] = $y;
            }
        }
        $data['total'] = $this->currency->formatk(array_sum($data['y']), $this->config->get('config_currency'));

        return $data;
    }

    public function getOrdersChart($startdate, $enddate){
        $data = array(
            'x' => array(),
            'y' => array()
        );

        $this->load->model('report/home');
        $reportModel = $this->registry->get('model_report_home');

        $begin    = new \DateTime($startdate);
        $end      = new \DateTime($enddate);
        $datediff = date_diff($begin, $end);

        if($datediff->days == 0) {
            $all = $reportModel->getOrders($startdate, $enddate, 'hours');
            for ($i = 0; $i < 24; $i++) {
                $key = -1;
                $key = array_search($i, array_column($all, 'hour'));

                if($i == 0) $data['x'][] = "12 AM";
                elseif($i < 13) $data['x'][] = "$i AM";
                else $data['x'][] = ($i-12) . " PM";

                if($key > -1) {
                    $data['y'][] = $all[$key]['total'];
                } else {
                    $data['y'][] = 0;
                }
            }
        } elseif($datediff->days < 31) {
            $all = $reportModel->getOrders($startdate, $enddate, 'days');
            $interval = \DateInterval::createFromDateString('1 day');
            $days     = new \DatePeriod($begin, $interval, $end);
            foreach ( $days as $day ) {
                $data['x'][] = $day->format('Y-m-d');
                $y = 0;
                foreach($all as $row) {
                    if($row['date'] == $day->format('Y-m-d')) {
                        $y += $row['total'];
                    }
                }
                $data['y'][] = $y;
            }
        } elseif($datediff->days < 365) {
            $all = $reportModel->getOrders($startdate, $enddate, 'months');
            $interval = \DateInterval::createFromDateString('1 month');
            $months     = new \DatePeriod($begin, $interval, $end);
            foreach ( $months as $month ) {
                $data['x'][] = $month->format('Y-m-01');
                $y = 0;
                foreach($all as $row) {
                    if($row['date'] == $month->format('Y-m-01')) {
                        $y += $row['total'];
                    }
                }
                $data['y'][] = $y;
            }
        } else {
            $all = $reportModel->getOrders($startdate, $enddate, 'years');
            $interval = \DateInterval::createFromDateString('1 year');
            $years     = new \DatePeriod($begin, $interval, $end);
            foreach ( $years as $year ) {
                $data['x'][] = $year->format('Y');
                $y = 0;
                foreach($all as $row) {
                    if($row['year'] == $year->format('Y')) {
                        $y += $row['total'];
                    }
                }
                $data['y'][] = $y;
            }
        }
        $data['total'] = array_sum($data['y']);

        return $data;
    }

    public function getCountries()
    {
        $this->load->model('localisation/country');
        $countries = $this->registry->get('model_localisation_country')->getCountries();
        return $countries;
    }

    public function getCities($country_id)
    {
        $this->load->model('localisation/zone');
        $zones = $this->registry->get('model_localisation_zone')->getZonesByCountryId($country_id);
        return $zones;
    }

    public function getGeozones()
    {
        $this->load->model('localisation/geo_zone');
        $geozones = $this->registry->get('model_localisation_geo_zone')->getGeoZones();
        return $geozones;
    }

    public function getGeozoneCountries($geozone_id)
    {
        $this->load->model('localisation/geo_zone');
        $countriesZones = $this->registry->get('model_localisation_geo_zone')->getGeozoneCountries($geozone_id);
        return $countriesZones;
    }
	
	public function getLengthClasses(){
		$this->load->model('localisation/length_class');
        $length_classes = $this->registry->get('model_localisation_length_class')->getLengthClasses();
		return $length_classes;
	}
	
	public function getWeightClasses(){
		$this->load->model('localisation/weight_class');
        $weight_classes = $this->registry->get('model_localisation_weight_class')->getWeightClasses();
		return $weight_classes;
	}
    public function getStoreData(){
        $this->load->model('setting/setting');
        $storeData = $this->registry->get('model_setting_setting')->getSetting('config');
        $infoData['name'] = $storeData['config_name'];
        $infoData['email'] = $storeData['config_email'];
        $infoData['phone'] = $storeData['config_telephone'];
        $infoData['default_currency'] = $storeData['config_currency'];
        $infoData['default_store_language'] = $storeData['config_language'];
        return $infoData;
    }

    public function getFillStoreInfo()
    {
        $data = array();

        // Get count of products
        $this->load->model('catalog/product');
        $data['products_count'] = $this->registry->get('model_catalog_product')->getTotalProductsCount();

        // Get current template if exists
        $this->load->model('setting/setting');
        $storeData = $this->registry->get('model_setting_setting')->getSetting('config');
        $data['template'] = $storeData['config_template'];

        // Get domains of store
        $this->load->model('setting/domainsetting');
        $data['domains'] = $this->registry->get('model_setting_domainsetting')->getDomains();
        return $data;
    }

    public function getStoreLanguages(){
        $this->load->model('localisation/language');
        $languages = $this->registry->get('model_localisation_language')->getLanguages();
        //get enabled languages only
        foreach ($languages as $lang){
            if ($lang['status'] != '1')
                unset($languages[$lang['code']]);
        }
        $languages = array_column($languages, 'code');
        return $languages;
    }
	
	public function getTaxClasses(){
		$this->load->model('localisation/tax_class');
        $tax_classes = $this->registry->get('model_localisation_tax_class')->getTaxClasses();
		return $tax_classes;
	}
	
	public function getStockStatuses(){
		$this->load->model('localisation/stock_status');
        $weight_classes = $this->registry->get('model_localisation_stock_status')->getStockStatuses();
		return $weight_classes;
	}
	
	

}
