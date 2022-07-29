<?php

class Amazonconnector {

	/*
	contain product information
	 */
	public $product = [];

	// to get the language index according to amazon country language
	protected $marketplaceLanguageArray = [
					'DE' =>	[
										'Artikelbezeichnung'	=> 'item-name',
										'Artikelbeschreibung' => 'item-description',
										'H?ndler-SKU'					=> 'seller-sku',
										'Preis' 							=> 'price',
										'Menge'								=> 'quantity',
										'image-url'						=> 'image-url',
										'ASIN 1'							=> 'asin1',
									],

					'IN' =>	[
										'item-name'						=> 'item-name',
										'item-description' 		=> 'item-description',
										'seller-sku'					=> 'seller-sku',
										'price' 							=> 'price',
										'quantity'						=> 'quantity',
										'image-url'						=> 'image-url',
										'asin1'								=> 'asin1',
									],
	];
	/*
	contain feed type of product
	 */
	public $feedType;


	public function __construct($registry) {
        $this->registry = $registry;
				$this->config 	= $registry->get('config');
        $this->currency = $registry->get('currency');
        $this->cache 		= $registry->get('cache');
				$this->db 			= $registry->get('db');
				$this->request 	= $registry->get('request');
				$this->session 	= $registry->get('session');
	}

    /**
     * [getListMarketplaceParticipations to get the marketplace participations list]
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    public function getListMarketplaceParticipations($data = array()){
        $results = array();
        $accountDetails = array(
                        'Action'                            => 'ListMarketplaceParticipations',
                        'wk_amazon_connector_access_key_id' => $data['wk_amazon_connector_access_key_id'],
                        'wk_amazon_connector_seller_id'     => $data['wk_amazon_connector_seller_id'],
                        'wk_amazon_connector_secret_key'    => $data['wk_amazon_connector_secret_key'],
                        'wk_amazon_connector_country'       => $data['wk_amazon_connector_country'],
                        );

        $returnData = $this->checkConnection($accountDetails, $XMLdata = true);

				if(isset($returnData->ListMarketplaceParticipationsResult->ListMarketplaces->Marketplace)){
						foreach ($returnData->ListMarketplaceParticipationsResult->ListMarketplaces->Marketplace as $key => $store_details) {
							$makeStoreArray = array();
							$makeStoreArray = (array)$store_details;
								if($makeStoreArray['MarketplaceId'] == $data['wk_amazon_connector_marketplace_id']){
										$results['currency_code'] = $makeStoreArray['DefaultCurrencyCode'];
								}
						}
        }else if(isset($returnData->Error->Message)){
            $results['error_message'] = $returnData->Error->Message;
						$results['error'] = true;
        }else{
						$results['error'] = true;
				}
       return $results;
    }

    /**
     * [checkConnection to check the connection with the amazon site based on country code]
     * @param  [type]  $data    [description]
     * @param  boolean $XMLdata [description]
     * @return [type]           [description]
     */
    public function checkConnection($data, $XMLdata = true)
    {
        $connectionDetails = array();
        $connectionDetails['Action']         = $data['Action'];
        $connectionDetails["Version"]        = "2011-07-01";
        $connectionDetails["Timestamp"]      = gmdate("Y-m-d\TH:i:s\Z");
        $connectionDetails['SignatureVersion'] = '2';
        $connectionDetails['SignatureMethod']= 'HmacSHA256';
        $connectionDetails["AWSAccessKeyId"] = $data['wk_amazon_connector_access_key_id'];
        $connectionDetails['SellerId']       = $data['wk_amazon_connector_seller_id'];
        $connectionDetails['Secretkey']      = $data['wk_amazon_connector_secret_key'];
        $connectionDetails['CountryCode']    = $data['wk_amazon_connector_country'];
        ksort($connectionDetails);

        $curlOptions = $this->setCurlOptions($connectionDetails);
        $curlHandle = curl_init();
        curl_setopt_array($curlHandle, $curlOptions);
				$curlResult = curl_exec($curlHandle);

        if (($curlResult = curl_exec($curlHandle)) == false) {
            $res = false;
        } else {
            $res = true;
        }

        if (!$res) {
            return (false);
        }

        curl_close($curlHandle);

        if ($XMLdata) {
            try {
                $resultXML = new \SimpleXMLElement($curlResult);
                return ($resultXML);
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return ($curlResult);
        }
    }

    /**
     * [setCurlOptions make the amazon call url]
     * @param array $data [description]
     */
    public function setCurlOptions($data = array())
    {
        $type = '/' . trim('Sellers');
        $URL = $this->getAmazonURL($data['CountryCode']);

        $canonicalizedData = [];
        foreach ($data as $datakey => $dataval) {
            if(($datakey != 'CountryCode') && ($datakey != 'Secretkey')){
                $datakey = str_replace("%7E", "~", rawurlencode($datakey));
                $dataval = str_replace("%7E", "~", rawurlencode($dataval));
                $canonicalizedData[] = $datakey . "=" . $dataval;
            }
        }
        $canonicalizedData = implode("&", $canonicalizedData);
        $signString = "POST"."\n".$URL."\n".$type."\n".$canonicalizedData;

        $pacSignature = base64_encode(hash_hmac("sha256", $signString, $data['Secretkey'], true));
        $pacSignature = str_replace("%7E", "~", rawurlencode($pacSignature));
        $curlOptions = [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
        ];

        $curlOptions[CURLOPT_URL] = "https://" . $URL . $type;
        $curlOptions[CURLOPT_POSTFIELDS] = $canonicalizedData . "&Signature=" . $pacSignature;
        $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
        $curlOptions[CURLOPT_VERBOSE] = false;

        return $curlOptions;
    }

     /**
     * [getAmazonURL - get URL by country iso]
     * @return [type] [description]
     */
    public function getAmazonURL($accountCountryCode = false)
    {
        if ($accountCountryCode == 'US') {
            return ('mws.amazonservices.com');
        } elseif ($accountCountryCode == 'CA') {
            //return ('mws.amazonservices.ca');
            return ('mws.amazonservices.com');
        } elseif ($accountCountryCode == 'JP') {
            return ('mws.amazonservices.jp');
        } elseif ($accountCountryCode == 'MX') {
            //return ('mws.amazonservices.com.mx');
            return ('mws.amazonservices.com');
        } elseif ($accountCountryCode == 'CN') {
            return ('mws.amazonservices.com.cn');
        } elseif ($accountCountryCode == 'IN') {
            return ('mws.amazonservices.in');
        } elseif ($accountCountryCode == 'DE' || $accountCountryCode == 'ES' || $accountCountryCode == 'FR' || $accountCountryCode == 'IT' || $accountCountryCode == 'UK' || $accountCountryCode == 'GB' || $accountCountryCode == 'AT') {
            return ('mws-eu.amazonservices.com');
        } else {
            return false;
        }
    }

		/**
		 * [getReportFinal generate the product list]
		 * @param  [type]  $productGeneratedReportId [description]
		 * @param  boolean $accountId                [description]
		 * @return [type]                            [description]
		 */
		public function getReportFinal($productGeneratedReportId, $accountDetails = array()){
				if ($productGeneratedReportId) {
						$getReportContent = '';
						$finalReportArray = array();

						$getReportContent = $this->getReport($productGeneratedReportId, $accountDetails['id']);

						if ($getReportContent != '') {
								//Get report data in array according to country
								if ($accountDetails['wk_amazon_connector_country'] == 'FR') { //France Fr language
										$finalReportArray = $this->getReportDataFRLanguage($getReportContent);
								} elseif ($accountDetails['wk_amazon_connector_country'] == 'ES') { //Spain ES language
										$finalReportArray = $this->getReportDataESLanguage($getReportContent);
								}  elseif ($accountDetails['wk_amazon_connector_country'] == 'DE') { //Germany(Dutch) DE language
										$finalReportArray = $this->getReportDataDELanguage($getReportContent);
								} else { //For EN language
										$finalReportArray = $this->getReportDataENLanguage($getReportContent);
								}
						}
						return $finalReportArray;
				}
		}

    /**
     * [getReportDataENLanguage - Get report data in array if report is in germany language
     * @param  [type] $reportContent [Flat content of report]
     * @return [array]               [report data]
     */
    public function getReportDataDELanguage($reportContent)
    {
        $finalReportArr = array();
        $reportContent = str_replace( array( "\n" , "\t" ) , array( "[NEW*LINE]" , "[tAbul*Ator]" ) , $reportContent );
        $reportArr = explode("[NEW*LINE]", $reportContent);

        if ($reportArr) {
            $reportHeadingArr = explode("[tAbul*Ator]", utf8_encode($reportArr[0]));

            foreach ($reportArr as $reportKey => $reportItem) {
                if (!empty($reportItem) && $reportKey != 0) {
                    // all content except heading part
                    $reportDataArr = explode("[tAbul*Ator]", $reportItem);

                    if ($reportDataArr) {
                        for ($rowi=0; $rowi<count($reportHeadingArr); $rowi++) {
                            if ($reportHeadingArr[$rowi] == 'Artikelbezeichnung') {
                                $finalReportArr[$reportKey]['item-name'] = utf8_encode($reportDataArr[$rowi]);
                            }
                            elseif ($reportHeadingArr[$rowi] == 'Artikelbeschreibung') {
                                $finalReportArr[$reportKey]['item-description'] = utf8_encode($reportDataArr[$rowi]);
                            }
                            elseif ($reportHeadingArr[$rowi] == 'Händler-SKU') {
                                $finalReportArr[$reportKey]['seller-sku'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'Produkt-ID') {
                                $finalReportArr[$reportKey]['product-id'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'Preis') {
                                $finalReportArr[$reportKey]['price'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'Menge') {
                                $finalReportArr[$reportKey]['quantity'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'ASIN 1') {
                                $finalReportArr[$reportKey]['asin1'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'fulfillment-channel') {
                                $finalReportArr[$reportKey]['fulfillment-channel'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'Quantity Available') { //For AFN report
                                $finalReportArr[$reportKey]['Quantity Available'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'asin') { //For AFN report
                                $finalReportArr[$reportKey]['asin'] = $reportDataArr[$rowi];
                            } else {
                                $finalReportArr[$reportKey][$reportHeadingArr[$rowi]] = $reportDataArr[$rowi];
                            }
                        }
                    }
                }
            }
        }
        return $finalReportArr;
    }

    /**
     * [getReportDataENLanguage - Get report data in array if report is in English language
     * @param  [type] $reportContent [Flat content of report]
     * @return [array]               [report data]
     */
    public function getReportDataENLanguage($reportContent)
    {
        $finalReportArr = array();
        $reportContent 	= str_replace( array( "\n" , "\t" ) , array( "[NEW*LINE]" , "[tAbul*Ator]" ) , $reportContent );
        $reportArr 			= explode("[NEW*LINE]", $reportContent);

        if (!empty($reportArr)) {
            $reportHeadingArr = explode("[tAbul*Ator]", $reportArr[0]);

            foreach ($reportArr as $reportKey => $reportItem) {
                if (!empty($reportItem) && $reportKey != 0) {
                    // all content except heading part
                    $reportDataArr = explode("[tAbul*Ator]", $reportItem);

                    if (!empty($reportDataArr)) {
                        for ($rowi = 0; $rowi < count($reportHeadingArr); $rowi++) {
                            if ($reportHeadingArr[$rowi] == 'item-name') {
                                $finalReportArr[$reportKey]['item-name'] = utf8_encode($reportDataArr[$rowi]);
                            }
                            elseif ($reportHeadingArr[$rowi] == 'item-description') {
                                $finalReportArr[$reportKey]['item-description'] = utf8_encode($reportDataArr[$rowi]);
                            }
                            elseif ($reportHeadingArr[$rowi] == 'seller-sku') {
                                $finalReportArr[$reportKey]['seller-sku'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'product-id') {
                                $finalReportArr[$reportKey]['product-id'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'price') {
                                $finalReportArr[$reportKey]['price'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'quantity') {
                                $finalReportArr[$reportKey]['quantity'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'asin1') {
                                $finalReportArr[$reportKey]['asin1'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'fulfillment-channel') {
                                $finalReportArr[$reportKey]['fulfillment-channel'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'Quantity Available') { //For AFN report
                                $finalReportArr[$reportKey]['Quantity Available'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'asin') { //For AFN report
                                $finalReportArr[$reportKey]['asin'] = $reportDataArr[$rowi];
                            } else {
                                $finalReportArr[$reportKey][$reportHeadingArr[$rowi]] = $reportDataArr[$rowi];
                            }
                        }
                    }
                }
            }
        }
        return $finalReportArr;
    }

    /**
     * [getReportDataFRLanguage - Get report data in array if report is in French language
     * @param  [type] $reportContent [Flat content of report]
     * @return [array]               [report data]
     */
    public function getReportDataFRLanguage($reportContent)
    {
        $finalReportArr = array();

        $reportArr = preg_split("/[\n]/", $reportContent);
        if ($reportArr) {
            $reportHeadingArr = preg_split("/[\t]/", $reportArr[0]);
            foreach ($reportArr as $reportKey => $reportItem) {
                if (!empty($reportItem) && $reportKey != 0) {
                    // all content except heading part
                    $reportDataArr = preg_split("/[\t]/", $reportItem);
                    if ($reportDataArr) {
                        for ($rowi=0; $rowi<count($reportHeadingArr); $rowi++) {
                            if ($reportHeadingArr[$rowi] == 'item-name' || $reportHeadingArr[$rowi] == 'nom-produit') {
                                $reportDataArr[$rowi] = mb_convert_encoding($reportDataArr[$rowi], "UTF-8", "HTML-ENTITIES");
                                $finalReportArr[$reportKey]['item-name'] = json_encode($reportDataArr[$rowi]);
                            }
                            elseif ($reportHeadingArr[$rowi] == 'item-description' || $reportHeadingArr[$rowi] == 'description-article') {
                                $reportDataArr[$rowi] = mb_convert_encoding($reportDataArr[$rowi], "UTF-8", "HTML-ENTITIES");
                                $finalReportArr[$reportKey]['item-description'] = json_encode($reportDataArr[$rowi]);
                            }
                            elseif ($reportHeadingArr[$rowi] == 'seller-sku' || $reportHeadingArr[$rowi] == 'sku-vendeur') {
                                $finalReportArr[$reportKey]['seller-sku'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'price' || $reportHeadingArr[$rowi] == 'prix') {
                                $finalReportArr[$reportKey]['price'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'quantity' || preg_match("/\bquantit\b/i", $reportHeadingArr[$rowi])) {
                                $finalReportArr[$reportKey]['quantity'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'asin1') {
                                $finalReportArr[$reportKey]['asin1'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'canal-traitement') {
                                $finalReportArr[$reportKey]['fulfillment-channel'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'Quantity Available'
                                || (preg_match("/\bquantit\b/i", $reportHeadingArr[$rowi]) && preg_match("/\battente\b/i", $reportHeadingArr[$rowi]))) { //For AFN report
                                $finalReportArr[$reportKey]['Quantity Available'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'asin') { //For AFN report
                                $finalReportArr[$reportKey]['asin'] = $reportDataArr[$rowi];
                            } else {
                                $finalReportArr[$reportKey][$reportHeadingArr[$rowi]] = $reportDataArr[$rowi];
                            }
                        }
                    }
                }
            }
        }

        return $finalReportArr;
    }

     /**
     * [getReportDataESLanguage - Get report data in array if report is in Spanish language
     * @param  [type] $reportContent [Flat content of report]
     * @return [array]               [report data]
     */
    public function getReportDataESLanguage($reportContent)
    {
        $finalReportArr = array();

        $reportArr = preg_split("/[\n]/", $reportContent);
        if ($reportArr) {
            $reportHeadingArr = preg_split("/[\t]/", $reportArr[0]);
            foreach ($reportArr as $reportKey => $reportItem) {
                if (!empty($reportItem) && $reportKey != 0) {
                    // all content except heading part
                    $reportDataArr = preg_split("/[\t]/", $reportItem);
                    if ($reportDataArr) {
                        for ($rowi=0; $rowi<count($reportHeadingArr); $rowi++) {
                            if ($reportHeadingArr[$rowi] == 'item-name' || preg_match("/\btulo del producto\b/i", $reportHeadingArr[$rowi])) {
                                $reportDataArr[$rowi] = mb_convert_encoding($reportDataArr[$rowi], "UTF-8", "HTML-ENTITIES");
                                $finalReportArr[$reportKey]['item-name'] = json_encode($reportDataArr[$rowi]);
                            }
                            elseif ($reportHeadingArr[$rowi] == 'item-description' || preg_match("/\bDescripci\b/i", $reportHeadingArr[$rowi])) {
                                $reportDataArr[$rowi] = mb_convert_encoding($reportDataArr[$rowi], "UTF-8", "HTML-ENTITIES");
                                $finalReportArr[$reportKey]['item-description'] = json_encode($reportDataArr[$rowi]);
                            }
                            elseif ($reportHeadingArr[$rowi] == 'seller-sku' || $reportHeadingArr[$rowi] == 'SKU del vendedor') {
                                $finalReportArr[$reportKey]['seller-sku'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'price' || $reportHeadingArr[$rowi] == 'Precio') {
                                $finalReportArr[$reportKey]['price'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'quantity' || $reportHeadingArr[$rowi] == 'Cantidad') {
                                $finalReportArr[$reportKey]['quantity'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'asin1' || $reportHeadingArr[$rowi] == 'ASIN 1') {
                                $finalReportArr[$reportKey]['asin1'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'fulfillment-channel' || preg_match("/\bCanal de gesti\b/i", $reportHeadingArr[$rowi])) {
                                $finalReportArr[$reportKey]['fulfillment-channel'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'Quantity Available') { //For AFN report
                                $finalReportArr[$reportKey]['Quantity Available'] = $reportDataArr[$rowi];
                            }
                            elseif ($reportHeadingArr[$rowi] == 'asin') { //For AFN report
                                $finalReportArr[$reportKey]['asin'] = $reportDataArr[$rowi];
                            } else {
                                $finalReportArr[$reportKey][$reportHeadingArr[$rowi]] = $reportDataArr[$rowi];
                            }
                        }
                    }
                }
            }
        }

        return $finalReportArr;
    }

    /**
     * [loadFile to load amazon mws api file to execute functoinality]
     * @param  [string] $type     [file type Ex order,product etc]
     * @param  [string] $filename [name of the file that need to be loaded]
     */
    public function loadFile($type, $filename)
    {
        $path = DIR_APPLICATION.'../Lib'.'/amazon/';
        include_once($path.'amazon'.$type.'/src/MarketplaceWebService'.$type.'/Samples/'.$filename.'.php');
    }

    public function getReport($generatedReportId, $accountId = false)
    {
        $this->loadFile('', 'GetReportSample');
        $config             = $this->_getAccountConfigData($accountId);
        $config['reportId'] = $generatedReportId;
        $Getreport          = new \Getreport($config);
        $report = $Getreport->invokeGetReport($Getreport->service, $Getreport->request);
        return $report;
    }

    /**
     * [requestReport to get request id before sync product from amazon]
     * @param  [type]  $reportType [description]
     * @param  boolean $accountId  [description]
     * @return [type]              [description]
     */
    public function requestReport($reportType, $accountId = false)
    {
        $this->loadFile('', 'RequestReportSample');
        $config                 = $this->_getAccountConfigData($accountId);
        $config['reportType']   = $reportType;
        $requestReport          = new \Requestreport($config);
        $request                = $requestReport->invokeRequestReport($requestReport->service, $requestReport->request);
        return $request;
    }
    /**
     * [GetReportRequestList to get report request list id based on report id]
     * @param [type] $requestId [description]
     * @param [type] $accountId [description]
     */
    public function GetReportRequestList($requestId, $accountId)
    {
        $this->loadFile('', 'GetReportRequestListSample');
        $config = $this->_getAccountConfigData($accountId);
        $config['requestId'] = $requestId;
        $requestReport = new \GetReportRequestList($config);
        $request = $requestReport->invokeGetReportRequestList($requestReport->service, $requestReport->request);
        return $request;
    }


	/**
	 * [_getAccountConfigData to get module config setting]
	 * @param  boolean $amazon_AccountId [amazon account id]
	 * @return [type]                  [array of config data]
	 */
	public function _getAccountConfigData($amazon_AccountId = false)
	{
		$results = false;
		if($amazon_AccountId){
			if($accountInfo = $this->getAccountDetails(array('account_id' => $amazon_AccountId))){
				if($countryDetails = $this->getAmazonServiceUrlAndMarketplaceId($accountInfo['wk_amazon_connector_country'])){
					$results = [
			                'sellerId'          	=> $accountInfo['wk_amazon_connector_seller_id'],
			                'marketplaceId'     	=> $accountInfo['wk_amazon_connector_marketplace_id'],
			                'accessKeyId'       	=> $accountInfo['wk_amazon_connector_access_key_id'],
			                'secretKey'         	=> $accountInfo['wk_amazon_connector_secret_key'],
			                'applicationName'   	=> 'testmarket',
			                'applicationVersion'	=> '1.0.0',
			                'countryMarketplaceId'=> $countryDetails['marketplaceId'],
			                'serviceUrl' 					=> $countryDetails['serviceUrl']
			            ];
				}
			}
		}
		return $results;
	}

    /**
     * [getAccountDetails to get account complete details]
     * @param  boolean $amazon_AccountId [description]
     * @return [type]                    [description]
     */
    public function getAccountDetails($data = array()){
    		$sql = "SELECT * FROM ".DB_PREFIX."amazon_accounts WHERE 1 ";

        if(!empty($data['account_id'])){
            $sql .= "AND id = '".(int)$data['account_id']."' ";
        }

        if(!empty($data['report_id'])){
            $sql .= "AND wk_amazon_connector_listing_report_id = '".$this->db->escape($data['report_id'])."' ";
        }
				$sql .= " ORDER BY id ASC ";
        $result = $this->db->query($sql)->row;

    	return $result;
    }


    public function getAmazonServiceUrlAndMarketplaceId($accountCountryCode = false)
    {
        $countryCodeInfo = [
            'US'=>['serviceUrl'=>'https://mws.amazonservices.com', 'marketplaceId'=>'ATVPDKIKX0DER'],
            'UK'=>['serviceUrl'=>'https://mws-eu.amazonservices.com', 'marketplaceId'=>'A1F83G8C2ARO7P'],
            'DE'=>['serviceUrl'=>'https://mws-eu.amazonservices.com', 'marketplaceId'=>'A1PA6795UKMFR9'],
            'FR'=>['serviceUrl'=>'https://mws-eu.amazonservices.com', 'marketplaceId'=>'A13V1IB3VIYZZH'],
            'IT'=>['serviceUrl'=>'https://mws-eu.amazonservices.com', 'marketplaceId'=>'APJ6JRA9NG5V4'],
            'JP'=>['serviceUrl'=>'https://mws.amazonservices.jp', 'marketplaceId'=>'A1VC38T7YXB528'],
            'CN'=>['serviceUrl'=>'https://mws.amazonservices.com.cn', 'marketplaceId'=>'AAHKV2X7AFYLW'],
            'CA'=>['serviceUrl'=>'https://mws.amazonservices.com', 'marketplaceId'=>'A2EUQ1WTGCTBG2'],
            'MX'=>['serviceUrl'=>'https://mws.amazonservices.com', 'marketplaceId'=>'A1AM78C64UM0Y8'],
            'IN'=>['serviceUrl'=>'https://mws.amazonservices.in', 'marketplaceId'=>'A21TJRUUN4KGV'],
            'ES'=>['serviceUrl'=>'https://mws-eu.amazonservices.com', 'marketplaceId'=>'A1RKKUPIHCS9HS'],
            'GB'=>['serviceUrl'=>'https://mws.amazonservices.co.uk', 'marketplaceId'=>'A1F83G8C2ARO7P'],

        ];
        return isset($countryCodeInfo[$accountCountryCode]) ? $countryCodeInfo[$accountCountryCode] : false;
    }

    public function __getOcAmazonGlobalOption(){
			$result = $this->db->query("SELECT * FROM `".DB_PREFIX."option` o LEFT JOIN ".DB_PREFIX."option_description od ON o.option_id = od.option_id WHERE od.`name` = 'Amazon Variations' ")->row;

			return $result;
		}

     /**
     * check product parent asin value
     * @param  array $variations
     * @return int|bool
     */
    public function checkParentAsinValue($variations)
    {
        $parentAsin = false;
        foreach ($variations as $value) {
            if (isset($value['MarketplaceASIN']['ASIN'])) {
                $parentAsin = $value['MarketplaceASIN']['ASIN'];
                break;
            }
        }
        return $parentAsin;
    }


    public function getMatchedProduct($productASIN, $accountId = false)
    {
        $this->loadFile('Products', 'GetMatchingProductSample');
        $config                 = $this->_getAccountConfigData($accountId);
        $config['productASIN']  = $productASIN;
        $matchingprodut = new \Getmatchingproduct((object)$config);
        $get_product = $matchingprodut->invokeGetMatchingProduct($matchingprodut->service, $matchingprodut->request);
        return $get_product;
    }

     public function GetMyPriceForASIN($productASIN, $accountId = false)
    {
        $this->loadFile('Products', 'GetMyPriceForASINSample');
        $config                 = $this->_getAccountConfigData($accountId);
        $config['productASIN']  = $productASIN;
        $matchingprodut         = new \GetMyPriceForASIN((object)$config);
        $get_product = $matchingprodut->invokeGetMyPriceForASIN($matchingprodut->service, $matchingprodut->request);
        return $get_product;
    }

    public function GetCompetitivePricingForASIN($productASIN, $accountId = false)
    {
        $this->loadFile('Products', 'GetCompetitivePricingForASINSample');
        $config                 = $this->_getAccountConfigData($accountId);
        $config['productASIN']  = $productASIN;
        $matchingproduct        = new \GetCompetitivePricingForASIN((object)$config);
        $get_product = $matchingproduct->invokeGetCompetitivePricingForASIN($matchingproduct->service, $matchingproduct->request);
        return $get_product;
    }

		public function __getProductFields($product_id = false){
				$result = $this->db->query("SELECT * FROM ".DB_PREFIX."amazon_product_fields WHERE product_id = '".(int)$product_id."' ")->row;
				return $result;
		}

		/**
		 * [_getEbayProductSpecification to get all the ebay specification]
		 * @return [type] [description]
		 */
		public function _getAmazonSpecification(){
			$amazonSpecifications = array();

			$getAmazonAttributeEntry = $this->db->query("SELECT DISTINCT aam.account_group_id, name, account_id FROM ".DB_PREFIX."amazon_attribute_map aam LEFT JOIN ".DB_PREFIX."attribute_group ag ON(aam.account_group_id = ag.attribute_group_id) LEFT JOIN ".DB_PREFIX."attribute_group_description agd ON(ag.attribute_group_id = agd.attribute_group_id) ")->rows;

			if(isset($getAmazonAttributeEntry) && $getAmazonAttributeEntry){
				foreach ($getAmazonAttributeEntry as $key => $specification) {
					$getConditionsValue = $this->db->query("SELECT *, ad.name as attribute_name FROM ".DB_PREFIX."attribute a LEFT JOIN ".DB_PREFIX."attribute_description ad ON (a.attribute_id = ad.attribute_id) LEFT JOIN ".DB_PREFIX."attribute_group ag ON(a.attribute_group_id = ag.attribute_group_id) LEFT JOIN ".DB_PREFIX."attribute_group_description agd ON(ag.attribute_group_id = agd.attribute_group_id) LEFT JOIN ".DB_PREFIX."amazon_attribute_map aam ON((a.attribute_group_id = aam.account_group_id) AND (a.attribute_id = aam.oc_attribute_id)) WHERE aam.account_group_id = '".(int)$specification['account_group_id']."' AND a.attribute_group_id = '".(int)$specification['account_group_id']."'  AND ad.language_id = '".(int)$this->config->get('config_language_id')."' ")->rows;
					$amazonSpecifications[$specification['account_group_id']]['name'] 			= $specification['name'];
					$amazonSpecifications[$specification['account_group_id']]['account_id'] = $specification['account_id'];
					$amazonSpecifications[$specification['account_group_id']]['attributes'] = $getConditionsValue;
				}
			}

			return $amazonSpecifications;
		}

		/**
		 * [getProductSpecification to get opencart product specification]
		 * @param  boolean $product_id [description]
		 * @return [type]              [description]
		 */
		public function getProductSpecification($product_id = false){
			$productSpecification = array();

			$getOcProductSpecification = $this->db->query("SELECT pa.attribute_id FROM " . DB_PREFIX . "product_attribute pa RIGHT JOIN ".DB_PREFIX."amazon_attribute_map aam ON(pa.attribute_id = aam.oc_attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' GROUP BY pa.attribute_id")->rows;

			if(!empty($getOcProductSpecification)){
					foreach ($getOcProductSpecification as $product_attribute) {
						$product_attribute_description_data = array();

						$product_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

						foreach ($product_attribute_description_query->rows as $product_attribute_description) {
							$product_attribute_description_data[$product_attribute_description['language_id']] = array('text' => $product_attribute_description['text']);
						}

						$productSpecification[] = array(
							'attribute_id'                  => $product_attribute['attribute_id'],
							'product_attribute_description' => $product_attribute_description_data
						);
					}
			}
			return $productSpecification;
		}

		/**
		 * [checkSpecificationEntry to get only the Amazon specification [used to hide the specification for opencart attribute]]
		 * @param  array  $data [description]
		 * @return [type]       [description]
		 */
		public function checkSpecificationEntry($data = array()){
			$sql = "SELECT * FROM ".DB_PREFIX."attribute a LEFT JOIN ".DB_PREFIX."attribute_group ag ON(a.attribute_group_id = ag.attribute_group_id) WHERE a.attribute_group_id IN (SELECT account_group_id FROM ".DB_PREFIX."amazon_attribute_map) ";

			if(isset($data['attribute_id'])){
				$sql .= " AND a.attribute_id = '".(int)$data['attribute_id']."'";
			}

			if(isset($data['attribute_group_id'])){
				$sql .= " AND a.attribute_group_id = '".(int)$data['attribute_group_id']."' AND ag.attribute_group_id = '".(int)$data['attribute_group_id']."' ";
			}

			$getSpecificationEntry = $this->db->query($sql)->row;

			return $getSpecificationEntry;
		}

		public function _getAmazonVariation(){
			$results = array();
			$result = $this->db->query("SELECT * FROM `".DB_PREFIX."option` o LEFT JOIN ".DB_PREFIX."option_description od ON(o.option_id = od.option_id) WHERE od.`name` = '".$this->db->escape('Amazon Variations')."' AND od.language_id = '".(int)$this->config->get('config_language_id')."' ")->row;

			if(isset($result['option_id']) && $result['option_id']){
				$query_option_value = $this->db->query("SELECT avm.*, ovd.*, ov.option_id FROM ".DB_PREFIX."amazon_variation_map avm LEFT JOIN ".DB_PREFIX."option_value ov ON(avm.variation_value_id = ov.option_value_id) LEFT JOIN ".DB_PREFIX."option_value_description ovd ON(ov.option_value_id = ovd.option_value_id) WHERE avm.variation_id = '".(int)$result['option_id']."' AND ov.option_id = '".(int)$result['option_id']."' AND ovd.language_id = '".(int)$this->config->get('config_language_id')."' ")->rows;

				$combination_values = array();
				if(!empty($query_option_value)){
					foreach ($query_option_value as $key => $combination_value) {
						$combination_values[] = $combination_value;
					}
				}

				$results = array(
					'option_id' 		=> $result['option_id'],
					'type' 					=>$result['type'],
					'option_name' 	=>$result['name'],
					'option_values' => $combination_values,
					);
			}
			return $results;
		}

		public function _getProductVariation($product_id = false, $type = 'amazon_product_variation'){
				$results = $product_option_value = array();

				$result = $this->db->query("SELECT po.*, od.* FROM ".DB_PREFIX."product_option po LEFT JOIN ".DB_PREFIX."product p ON(po.product_id = p.product_id) LEFT JOIN ".DB_PREFIX."option o ON(po.option_id = o.option_id) LEFT JOIN ".DB_PREFIX."option_description od ON(po.option_id = od.option_id) LEFT JOIN ".DB_PREFIX."amazon_variation_map avm ON (o.option_id = avm.variation_id) WHERE po.product_id = '".(int)$product_id."' AND od.`name` = '".$this->db->escape('Amazon Variations')."' ")->row;

				if(isset($result['option_id']) && $result['option_id']){
						$query = $this->db->query("SELECT pov.*, avm.*, apvm.* FROM ".DB_PREFIX."product_option_value pov LEFT JOIN ".DB_PREFIX."option_value_description ovd ON ((pov.option_value_id = ovd.option_value_id) AND (pov.option_id = ovd.option_id)) LEFT JOIN ".DB_PREFIX."amazon_variation_map avm ON ((pov.option_value_id = avm.variation_value_id) AND (pov.option_id = avm.variation_id)) LEFT JOIN ".DB_PREFIX."amazon_product_variation_map apvm ON((pov.product_option_value_id = apvm.product_option_value_id) AND (pov.option_value_id = apvm.option_value_id)) WHERE pov.option_id = '".(int)$result['option_id']."' AND pov.product_id = '".(int)$product_id."' AND pov.product_option_id = '".(int)$result['product_option_id']."' AND ovd.language_id = '".(int)$this->config->get('config_language_id')."' ")->rows;

					if(!empty($query)){
						foreach ($query as $key => $product_option_entry) {
							if($type == 'amazon_product_variation'){
								$results[] = $product_option_entry['option_value_id'];
							}

							if($type == 'amazon_product_variation_value'){
								$product_option_value[$product_option_entry['option_value_id']] = array(
												'name' 				=> $product_option_entry['value_name'],
												'quantity' 		=> $product_option_entry['quantity'],
												'price' 			=> $product_option_entry['price'],
												'price_prefix'=> $product_option_entry['price_prefix'],
												'id_type' 		=> $product_option_entry['id_type'],
												'id_value' 		=> $product_option_entry['id_value'],
												'sku' 				=> $product_option_entry['sku'],
											);
							}
						}
					}
					if($type == 'amazon_product_variation_value'){
							$results[$result['option_id']] = array(
											'option_id' 		=> $result['option_id'],
											'option_value' 	=> $product_option_value,
											);
							}
				}
				return $results;
			}

			/**
			 * [checkVariationEntry to check amazon variation entry]
			 * @param  array  $data [description]
			 * @return [type]       [description]
			 */
			public function checkVariationEntry($data = array()){
				$sql = "SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.option_id IN (SELECT variation_id FROM ".DB_PREFIX."amazon_variation_map) ";

				if(isset($data['product_id'])){
					$sql .= " AND po.product_id = '" . (int)$data['product_id'] . "' ";
				}

				if(isset($data['option_id'])){
					$sql .= " AND po.option_id = '" . (int)$data['option_id'] . "' ";
				}

				$getVariationEntry = $this->db->query($sql)->row;

				return $getVariationEntry;
			}

			/**
			 * [filterVariationASINEntry to check amazon product variation entry by product ASIN]
			 * @param  array  $data [description]
			 * @return [type]       [description]
			 */
			public function filterVariationASINEntry($data = array()){
				$variationASINEntry = array();

				$option = $this->__getOcAmazonGlobalOption();

				$sql = "SELECT pov.*, avm.*, apvm.*, ovd.*, apm.account_id FROM ".DB_PREFIX."product_option_value pov LEFT JOIN ".DB_PREFIX."option_value_description ovd ON ((pov.option_value_id = ovd.option_value_id) AND (pov.option_id = ovd.option_id)) LEFT JOIN ".DB_PREFIX."amazon_variation_map avm ON ((pov.option_value_id = avm.variation_value_id) AND (pov.option_id = avm.variation_id)) LEFT JOIN ".DB_PREFIX."amazon_product_variation_map apvm ON((pov.product_option_value_id = apvm.product_option_value_id) AND (pov.option_value_id = apvm.option_value_id)) LEFT JOIN ".DB_PREFIX."amazon_product_map apm ON (apvm.product_id = apm.oc_product_id) WHERE pov.option_id = '".(int)$option['option_id']."' AND ovd.language_id = '".(int)$this->config->get('config_language_id')."' ";

				if(isset($data['product_id'])){
					$sql .= " AND apvm.product_id = '" . (int)$data['product_id'] . "' ";
				}

				if(isset($data['account_id'])){
					$sql .= " AND apm.account_id = '" . (int)$data['account_id'] . "' ";
				}

				if(isset($data['product_option_value_id'])){
					$sql .= " AND apvm.product_option_value_id = '" . (int)$data['product_option_value_id'] . "' ";
				}

				if(isset($data['option_value_id'])){
					$sql .= " AND apvm.option_value_id = '" . (int)$data['option_value_id'] . "' ";
				}

				if(isset($data['id_value'])){
					$sql .= " AND apvm.id_value = '" . $data['id_value'] . "' ";
				}

				if(isset($data['main_product_type_value'])){
					$sql .= " AND apvm.main_product_type_value = '" . $data['main_product_type_value'] . "' ";
				}

				$variationASINEntry = $this->db->query($sql)->rows;

				return $variationASINEntry;
			}

			/*------------------------------ Submit feed Section -----------------------------------*/

	    /**
	     * submit feed
	     * @param  array $feedType
	     * @return array
	     */
	    public function submitFeed($feedType, $accountId)
	    {
	        $this->loadFile('', 'SubmitFeedSample');
					$config              = $this->_getAccountConfigData($accountId);
					$config['feedType']  = $feedType;
					$config['product']   = $this->product;
	        $submitFeed 				 = new \Submitfeed((object)$config);
	        $result = $submitFeed->invokeSubmitFeed($submitFeed->service, $submitFeed->request);
	        @fclose($submitFeed->feedHandle);
	        return $result;
	    }

			/**
	     * get feed submission list
	     * @param  int $submissionId
	     * @return object
	     */
	    public function getFeedSubmissionList($feedId, $accountId)
	    {
	        $this->loadFile('', 'GetFeedSubmissionListSample');
					$config              			= $this->_getAccountConfigData($accountId);
	        $config['submissionId']   = $feedId;
	        $feedsubmitlist 					= new \GetFeedSubmissionList((object)$config);
	        $feedsubmitresultDetail 	= $feedsubmitlist->invokeGetFeedSubmissionList($feedsubmitlist->service, $feedsubmitlist->request);

	        return $feedsubmitresultDetail;
	    }


			/**
	     * getFeedSubmissionResult
	     * @param  array $feedType
	     * @return array
	     */
	    public function getFeedSubmissionResult($feedId, $accountId)
	    {
				$this->loadFile('', 'GetFeedSubmissionResultSample');
				$config              = $this->_getAccountConfigData($accountId);
        $config['submissionId']   = $feedId;
        $feedsubmitresult    = new \GetFeedSubmissionResult((object)$config);
        $feedsubmitresultDetail = $feedsubmitresult->invokeGetFeedSubmissionResult($feedsubmitresult->service, $feedsubmitresult->request);

        return $feedsubmitresultDetail;
			}


			/*------------------------------ Import Order Section -----------------------------------*/

			/**
	     * getOrderList to get Orders List
	     * @param  array $data
	     * @return object
	     */
	    public function getOrderList($data, $accountId)
	    {
	        $this->loadFile('Orders', 'ListOrdersSample');
					$config              			= $this->_getAccountConfigData($accountId);
					$config['fromDate'] 			= $data['amazon_order_from'];
	        $config['toDate']   			= $data['amazon_order_to'];
	        $config['recordCount'] 		= $data['amazon_order_maximum'] ? $data['amazon_order_maximum'] : 10;
					$listOrders 							= new \Listorders((object)$config);
					$orders 									= $listOrders->invokeListOrders($listOrders->service, $listOrders->request);

					return $orders;
	    }

			/**
			 * [ListOrders to get list of all orders for specific seller of amazon]
			 * @param [array] $data [data to fetch order like date range]
			 */
			public function ListOrdersByNextToken($data, $accountId)
			{
					$this->loadFile('Orders', 'ListOrdersByNextTokenSample');
					$config              			= $this->_getAccountConfigData($accountId);
					$config['nextToken'] 			= $data['next_token'];
					$listOrdersByNext 				= new \ListOrdersByNextToken((object)$config);
					$amaz_orders 							= $listOrdersByNext->invokeListOrdersByNextToken($listOrdersByNext->service, $listOrdersByNext->request);
					return $amaz_orders;
			}

			/**
	     * [GetOrder to get detail of an order]
	     * @param [integer] $amazonOrderId [amazon order id]
	     */
	    public function GetOrder($amazonOrderId, $accountId)
	    {
	        $this->loadFile('Orders', 'GetOrderSample');
					$config              			= $this->_getAccountConfigData($accountId);
	        $config['amazonOrderId']  = $amazonOrderId;
	        $getOrder 								= new \Getorder((object)$config);
	        $amazon_Order = $getOrder->invokeGetOrder($getOrder->service, $getOrder->request);

					return $amazon_Order;
	    }

			/**
			 * [ListOrderItems to get ordered products of an order]
			 * @param [integer] $amazonOrderId [amazon order id]
			 */
			public function ListOrderItems($amazonOrderId, $accountId)
			{
					$this->loadFile('Orders', 'ListOrderItemsSample');
					$config              			= $this->_getAccountConfigData($accountId);
					$config['orderId']  			= $amazonOrderId;
					$Listorderitems 					= new \Listorderitems((object)$config);
					$ListorderitemsDetail 		= $Listorderitems->invokeListOrderItems($Listorderitems->service, $Listorderitems->request);

					return $ListorderitemsDetail;
			}

			/*------------------------------ Customer Section -----------------------------------*/

			/**
			 * [deleteCustomerEntry to delete amazon customer mapped entry]
			 * @param [integer] $customer_id [customer id]
			 */
			public function deleteCustomerEntry($customer_id)
			{
					$this->db->query("DELETE FROM ".DB_PREFIX."amazon_customer_map WHERE oc_customer_id = '".(int)$customer_id."' ");
			}

			// to get the language indexes based on country code.
			public function getLanguageIndex($country_code = 'IN')
			{
					if(isset($this->marketplaceLanguageArray[$country_code])){
							return $this->marketplaceLanguageArray[$country_code];
					}else{
							return $this->marketplaceLanguageArray['IN'];
					}
			}
			//check attribute group assigned to Any amazon account or not
			public function getAccountByAttributeGroupId($attribute_group_id = false){
					$total_count = 0;
					if($attribute_group_id){
							$getRecord = $this->db->query("SELECT COUNT(*) as TOTAL FROM ".DB_PREFIX."amazon_accounts WHERE wk_amazon_connector_attribute_group = '".(int)$attribute_group_id."' ")->row;
							if(isset($getRecord['TOTAL'])){
									$total_count = $getRecord['TOTAL'];
							}
					}
					return $total_count;
			}
			public function 	showProductInfo($product_id) {
				$record = $this->db->query("SELECT mp.*, ma.wk_amazon_connector_store_name as store_name FROM ".DB_PREFIX."amazon_product_map mp LEFT JOIN ".DB_PREFIX."amazon_accounts ma ON(mp.account_id= ma.id) WHERE mp.oc_product_id='".$product_id."' ")->rows;

				if(isset($record[0]['oc_product_id'])) {
					return $record;

				} else {
					return true;
				}

			}
			//get UnmappedOcproduct with their variations and specification
			public function getOcProductWithCombination($data = array()){
				$product_array = array();
				 $sql = "SELECT p.*, pd.name, pd.description, apf.main_product_type, apf.main_product_type_value FROM ".DB_PREFIX."product p LEFT JOIN ".DB_PREFIX."product_description pd ON (p.product_id = pd.product_id) LEFT JOIN ".DB_PREFIX."amazon_product_fields apf ON(p.product_id = apf.product_id) WHERE pd.language_id = '".(int)$this->config->get('config_language_id')."' AND p.status = '1' AND p.product_id NOT IN (SELECT oc_product_id FROM ".DB_PREFIX."amazon_product_map) ";

				 if(!empty($data['product_ids'])){
					 $sql .= " AND p.product_id IN (".$data['product_ids'].")";
				 }

				 $sql .= " ORDER BY p.product_id ASC ";

		 		 if (isset($data['start']) || isset($data['limit'])) {
		 			 if ($data['start'] < 0) {
		 				 $data['start'] = 0;
		 			 }

		 			 if ($data['limit'] < 1) {
		 				 $data['limit'] = 50;
		 			 }

		 			 $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		 		 }

				 foreach ($this->db->query($sql)->rows as $key => $product) {
					 $combinationArray = $getCombinations = $specificationArray = array();

					 if($getCombinations = $this->_getProductVariation($product['product_id'], $type = 'amazon_product_variation_value')){
							 foreach ($getCombinations as $option_id => $combination_array) {
									 foreach ($combination_array['option_value'] as $key => $combination_value) {
									 		$combinationArray[$option_id][$key] = $combination_value;
									 }
							 }
					 }

					 $specificationArray = $this->getProductSpecification($product['product_id']);
					 if(isset($product['image']) && \Filesystem::isExists('image/' . $product['image'])){
						 	$image = \Filesystem::getUrl('image/' . $product['image']);
					 }else{
						 	$image = \Filesystem::getUrl('image/placeholder.png');
					 }

					 $product_array[] = array(
							 'product_id' => $product['product_id'],
							 'name' 			=> $product['name'],
							 'image' 			=> $image,
							 'model' 			=> $product['model'],
							 'description'=> $product['description'],
							 'sku' 				=> $product['sku'],
							 'quantity'   => $product['quantity'],
							 'price'   		=> $product['price'],
							 'main_type'  => $product['main_product_type'],
							 'main_value' => $product['main_product_type_value'],
							 'combinations'   => $combinationArray,
							 'specifications' => $specificationArray,
					 );
				 }

				 if(isset($data['count']) && !empty($product_array)){
						 if(count($product_array) <= 50){
							 	$totalpages = 1;
						 }else{
							 	$totalpages = ceil(count($product_array)/50);
						 }
						 return $totalpages;
				 }else{
					 	return $product_array;
				 }
			}
}
