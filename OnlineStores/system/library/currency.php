<?php
class Currency {
  	private $code;
  	private $currencies = array();

  	public function __construct($registry) {
		$this->config = $registry->get('config');
		$this->db = $registry->get('db');
		$this->language = $registry->get('language');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');
    $this->cache    = $registry->get('cache');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "currency");

    	foreach ($query->rows as $result) {
      		$this->currencies[$result['code']] = array(
        		'currency_id'   => $result['currency_id'],
        		'code'          => $result['code'],
        		'title'         => $result['title'],
        		'symbol_left'   => $result['symbol_left'],
        		'symbol_right'  => $result['symbol_right'],
        		'decimal_place' => $result['decimal_place'],
				'value'         => $result['value'],
				'status'		=> $result['status']
      		);
    	}

		if (isset($this->request->get['currency']) && (array_key_exists($this->request->get['currency'], $this->currencies))) {
			$this->set($this->request->get['currency']);
    	} elseif ((isset($this->session->data['currency'])) && (array_key_exists($this->session->data['currency'], $this->currencies))) {
      		$this->set($this->session->data['currency']);
    	} elseif ((isset($this->request->cookie['currency'])) && (array_key_exists($this->request->cookie['currency'], $this->currencies))) {
      		$this->set($this->request->cookie['currency']);
    	} else {
      		$this->set($this->config->get('config_currency'));
    	}
  	}

  	public function set($currency) {
    	$this->code = $currency;

    	if (!isset($this->session->data['currency']) || ($this->session->data['currency'] != $currency)) {
      		$this->session->data['currency'] = $currency;
    	}

    	if (!isset($this->request->cookie['currency']) || ($this->request->cookie['currency'] != $currency)) {
	  		setcookie('currency', $currency, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
    	}
  	}

	public function currentValue($number,$format = true)
	{
		# code...
		$currency = $this->code;

		$decimal_place = $this->currencies[$currency]['decimal_place'];
      	$value = $this->currencies[$currency]['value'];

    	if ($value) {
      		$value = (float)$number * $value;
    	} else {
      		$value = $number;
		}

    	return $value; //number format caused different issues

	}
  	public function format($number, $currency = '', $value = '', $format = true) {
		if ($currency && $this->has($currency)) {
      		$symbol_left   = $this->currencies[$currency]['symbol_left'];
      		$symbol_right  = $this->currencies[$currency]['symbol_right'];
      		$decimal_place = $this->currencies[$currency]['decimal_place'];
    	} else {
      		$symbol_left   = $this->currencies[$this->code]['symbol_left'];
      		$symbol_right  = $this->currencies[$this->code]['symbol_right'];
      		$decimal_place = $this->currencies[$this->code]['decimal_place'];

			$currency = $this->code;
    	}

    	if ($value) {
      		$value = $value;
    	} else {
      		$value = $this->currencies[$currency]['value'];
    	}

    	if ($value) {
      		$value = (float)$number * $value;
    	} else {
      		$value = $number;
    	}

    	$string = '';

    	if (($symbol_left) && ($format)) {
      		$string .= $symbol_left;
    	}

		if ($format) {
			$decimal_point = $this->language->get('decimal_point');
		} else {
			$decimal_point = '.';
		}

		if ($format) {
			$thousand_point = $this->language->get('thousand_point');
		} else {
			$thousand_point = '';
		}

    	$string .= number_format(round($value, (int)$decimal_place), (int)$decimal_place, $decimal_point, $thousand_point);

    	if (($symbol_right) && ($format)) {
      		$string .= $symbol_right;
    	}

    	return $string;
  	}

    public function formatForInvoice($number, $currency = '', $value = '', $format = true)
    {
        if ($currency && $this->has($currency)) {
            $symbol_left = $this->currencies[$currency]['symbol_left'];
            $symbol_right = $this->currencies[$currency]['symbol_right'];
            $decimal_place = $this->currencies[$currency]['decimal_place'];
        } else {
            $symbol_left = $this->currencies[$this->code]['symbol_left'];
            $symbol_right = $this->currencies[$this->code]['symbol_right'];
            $decimal_place = $this->currencies[$this->code]['decimal_place'];

            $currency = $this->code;
        }

        if ($value) {
            $value = $value;
        } else {
            $value = $this->currencies[$currency]['value'];
        }

        if ($value) {
            $value = (float)$number * $value;
        } else {
            $value = $number;
        }

        $string = '';

        if (($symbol_left) && ($format)) {
            $string .= $symbol_left . " ";
        }

        if ($format) {
            $decimal_point = $this->language->get('decimal_point');
        } else {
            $decimal_point = '.';
        }

        if ($format) {
            $thousand_point = $this->language->get('thousand_point');
        } else {
            $thousand_point = '';
        }

        $string .= number_format(round($value, (int)$decimal_place), (int)$decimal_place, $decimal_point, $thousand_point);

        if (($symbol_right) && ($format)) {
            $string .= " " . $symbol_right;
        }

        return $string;
    }

    public function formatk($number, $currency = '', $value = '', $format = true) {
        if ($currency && $this->has($currency)) {
            $symbol_left   = $this->currencies[$currency]['symbol_left'];
            $symbol_right  = $this->currencies[$currency]['symbol_right'];
            $decimal_place = $this->currencies[$currency]['decimal_place'];
        } else {
            $symbol_left   = $this->currencies[$this->code]['symbol_left'];
            $symbol_right  = $this->currencies[$this->code]['symbol_right'];
            $decimal_place = $this->currencies[$this->code]['decimal_place'];

            $currency = $this->code;
        }

        if ($value) {
            $value = $value;
        } else {
            $value = $this->currencies[$currency]['value'];
        }

        if ($value) {
            $value = (float)$number * $value;
        } else {
            $value = $number;
        }

        $isKValue = $value > 9999;

        $string = '';

        if (($symbol_left) && ($format)) {
            $string .= $symbol_left;
        }

        if ($format) {
            $decimal_point = $this->language->get('decimal_point');
        } else {
            $decimal_point = '.';
        }

        if ($format) {
            $thousand_point = $this->language->get('thousand_point');
        } else {
            $thousand_point = '';
        }

        if($isKValue) {
            $string .= number_format(round($value/1000.00, (int)$decimal_place), (int)$decimal_place, $decimal_point, $thousand_point);
            $string .= ' ' . $this->language->get('text_k') . ' ';
        } else {
            $string .= number_format(round($value, (int)$decimal_place), (int)$decimal_place, $decimal_point, $thousand_point);
        }


        if (($symbol_right) && ($format)) {
            $string .= $symbol_right;
        }

        return $string;
    }

    /**
     * @param $value
     * @param $from
     * @param $to
     * @return float|int
     */
    public function convert($value, $from, $to)
    {

        if ($from === $to) {
            return $value;
        }

        if (isset($this->currencies[$from])) {
            $from = $this->currencies[$from]['value'];
        } else {
            $from = 0;
        }

        if (isset($this->currencies[$to])) {
            $to = $this->currencies[$to]['value'];
        } else {
            $to = 0;
        }

        return $value * ($to / $from);
    }

  	public function getId($currency = '') {
  		if (!$currency) {
  			return $this->currencies[$this->code]['currency_id'];
  		} elseif ($currency && isset($this->currencies[$currency])) {
  			return $this->currencies[$currency]['currency_id'];
  		} else {
  			return 0;
  		}
  	}

	public function getSymbolLeft($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['symbol_left'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['symbol_left'];
		} else {
			return '';
		}
  	}

	public function getSymbolRight($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['symbol_right'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['symbol_right'];
		} else {
			return '';
		}
  	}

	public function getDecimalPlace($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['decimal_place'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['decimal_place'];
		} else {
			return 0;
		}
  	}

  	public function getCode() {
    	return $this->code;
  	}

    public function getCurrencies() {
        return $this->currencies;
    }

  	public function getValue($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['value'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['value'];
		} else {
			return 0;
		}
  	}

  	public function has($currency) {
    	return isset($this->currencies[$currency]);
  	}

    //Api Currency Converter
    public function gatUSDRate($currency) {
      $ratesData = $this->cache->get('rates');

      if(!$ratesData){

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://openexchangerates.org/api/latest.json?app_id=b4acf38e9fba4d6da20e25ccb7a23aa7',
            CURLOPT_RETURNTRANSFER => true
        ));

        $output = curl_exec($curl);
        $result = json_decode($output, true);

        if(count($result['rates'])){
          $ratesData = $result['rates'];
          $this->cache->set('rates', $result['rates'], 86400);
        }

        curl_close($curl);
      }

      return $ratesData[$currency] ? number_format( (1 / $ratesData[$currency]), 3) : 1;
    }

    //Convert Using gatUSDRate (API call)
    public function convertUsingRatesAPI($amount, $from_currency_code, $to_currency_code){
        $from_currency_code = strtoupper($from_currency_code);

        if( $from_currency_code == $to_currency_code ){
            return round($amount, 2);
        }elseif ( $from_currency_code !== 'USD' ) {
            $currenty_rate     = $this->gatUSDRate($from_currency_code);
            $amount_in_dollars = $currenty_rate * $amount;

            $target_currency_rate = $this->gatUSDRate($to_currency_code);
            $amount_in_account_currency = $amount_in_dollars/$target_currency_rate;
            return round($amount_in_account_currency, 2);
        }else{ //If USD convert it directly to to_currency_code
            $target_currency_rate = $this->gatUSDRate($to_currency_code);
            $amount_in_account_currency  = $amount/$target_currency_rate;
            return round($amount_in_account_currency, 2);
        }
    }
}
?>
