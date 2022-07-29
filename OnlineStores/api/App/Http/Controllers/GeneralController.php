<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ExpandCart\Foundation\Analytics\Live;

class GeneralController extends Controller
{

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function getLanguages(Request $request, Response $response)
    {
        return $response->withJson($this->container['languagecodes']);
    }

    public function getCountries(Request $request, Response $response)
    {
        $general = $this->container['general'];
        $countries = $general->getCountries();
        return $response->withJson(['status' => 'ok', 'data' => $countries]);
    }

    public function getCountryCities(Request $request, Response $response, $args)
    {
        $general = $this->container['general'];

        $country_id = isset($args['country_id']) ? $args['country_id'] : 0;

        if(!$country_id)
            return $response->withJson(['status' => 'error', 'error_description' => 'country_id missing!']);

        $cities = $general->getCities($country_id);
        return $response->withJson(['status' => 'ok', 'data' => $cities]);
    }

    public function getGeozones(Request $request, Response $response)
    {
        $general = $this->container['general'];
        $geozones = $general->getGeozones();
        return $response->withJson(['status' => 'ok', 'data' => $geozones]);
    }

    public function getGeozoneCountries(Request $request, Response $response, $args)
    {
        $general = $this->container['general'];

        $geozone_id = isset($args['geozone_id']) ? $args['geozone_id'] : 0;

        if(!$geozone_id)
            return $response->withJson(['status' => 'error', 'error_description' => 'geozone_id missing!']);

        $countries = $general->getGeozoneCountries($geozone_id);
        return $response->withJson(['status' => 'ok', 'data' => $countries]);
    }

    public function getHomeStatistics(Request $request, Response $response)
    {
        $startdate = (isset($_GET['startdate']) && $_GET['startdate']) ? $_GET['startdate'] : date("Y-m-d");
        $enddate   = (isset($_GET['enddate']) && $_GET['enddate']) ? $_GET['enddate'] : date("Y-m-d");

        $general = $this->container['general'];
        $order = $this->container['order'];

        //////////////////////////// Live visits
        $live = (new Live())->setMethod('getLastVisitsDetails');
        $result = $live->fetch();
        $onlineData = [];
        $desktop = $mobile = 0;
        foreach ($result as $key => $value) {
            if ($value['latitude'] && $value['longitude'] && $value['location']) {

                if ((time() - $value['lastActionTimestamp']) <= (300 * 1) && !isset($onlineData[$value['visitorId']])) {
                    $onlineData[$value['visitorId']] = [
                        'latLng' => [$value['latitude'], $value['longitude']],
                        'name' => $value['location']
                    ];

                    if ($value['deviceType'] == 'Desktop') {
                        $desktop++;
                    } else {
                        $mobile++;
                    }
                }
            }
        }

        $live_visits = $general->getLiveVisits();
        //////////////////////////// END Live visits

        /////////////////////////// Orders
        $ordersStatistics = $order->getOrdersStatistics();
        $orders = [
            'today'     => $ordersStatistics['today'],
            'unhandled' => $ordersStatistics['unhandled']
        ];
        ////////////////////////// END Orders

        /////////////////////////// Cards Data
        $cardsData = $general->getHomeCardsData($startdate, $enddate);
        ////////////////////////// END Cards Data

        ///////////////////////// Revenue Chart
        $revenue_chart = $general->getRevenueChart($startdate, $enddate);
        ///////////////////////// End Revenue Chart

        ///////////////////////// Orders Chart
        $orders_chart = $general->getOrdersChart($startdate, $enddate);
        ///////////////////////// End Orders Chart

        $data = [
            'live_visits'   => $live_visits,
            'orders'        => $orders,
            'cards_data'    => $cardsData,
            'revenue_chart' => $revenue_chart,
            'orders_chart'  => $orders_chart
        ];

        return $response->withJson(['status' => 'ok', 'data' => $data]);
    }

	public function getLengths (Request $request, Response $response) {
		$general = $this->container['general'];
        $length_classes = $general->getLengthClasses();
        return $response->withJson(['status' => 'ok', 'data' => $length_classes]);
	}
	
	public function getWeights (Request $request, Response $response) {
		$general = $this->container['general'];
        $weight_classes = $general->getWeightClasses();
        return $response->withJson(['status' => 'ok', 'data' => $weight_classes]);
	}

    public function getStoreInfo(Request $request, Response $response)
    {
        $general = $this->container['general'];
        $info = $general->getStoreData();
        $info['languages'] = $general->getStoreLanguages();
        $installation = $this->container['installation'];
        $info['store_owner_name'] = BILLING_DETAILS_NAME;
        $getting_started = $installation->gettingStartedInfo();
        $info['getting_started']['add_domain'] = $getting_started['ADD_DOMAIN'];
        $info['getting_started']['add_products'] = $getting_started['ADD_PRODUCTS'];
        $info['getting_started']['custom_design'] = $getting_started['CUST_DESIGN'];
        return $response->withJson(['status' => 'ok', 'data' => $info]);
    }

    public function getFillSiteInfo(Request $request, Response $response)
    {
        $general = $this->container['general'];
        $data = $general->getFillStoreInfo();
        return $response->withJson(['status' => 'ok', 'data' => $data]);
    }
	
	public function getTaxClasses (Request $request, Response $response) {
		$general = $this->container['general'];
        $tax_classes = $general->getTaxClasses();
        return $response->withJson(['status' => 'ok', 'data' => $tax_classes]);
	}
	
	public function getStockStatuses (Request $request, Response $response) {
		$general = $this->container['general'];
        $stockStatuses = $general->getStockStatuses();
        return $response->withJson(['status' => 'ok', 'data' => $stockStatuses]);
	}
	
	public function currencyFormat (Request $request, Response $response) {
		$parameters 	= $request->getParsedBody();
		
		if(!isset($parameters['price']) || !isset($parameters['currency_code'])  || !isset($parameters['currency_value']) ){
            return $response->withJson(['status' => 'failed', 'message' => 'Missing parameters , make sure you are passing price,currency_code,currency_value parameters']);
        }
		
		$price   		= $parameters['price'];
		$currency_code  = $parameters['currency_code'];
		$currency_value = $parameters['currency_value'];
		$formate = true ;
		if(isset($parameters['format'])){
			$format    		= $parameters['format'];
		}
		
		$currency = $this->container['registry']->get('currency');
		//$this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value'],false),
        $string = $currency->format($price, $currency_code , $currency_value, $format);
        return $response->withJson(['status' => 'ok', 'data' => $string]);
	}
	
	
}