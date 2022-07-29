<?php

/**
 * SMEOnline
 * @copyright   Copyright (c) 2015 Premier Technologies Pty Ltd. (http://www.premier.com.au)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
class SMEOnlineCurrencyAmount {

    /**
     * standardize amount based on currency
     * return string: AUD 50.56  |  JPY 51
     */
    function standardizeAmount($amount, $currency) {
        $numberOfDigit = $this->getNumberOfDigitsAfterDecimal($currency);
        if ($numberOfDigit === null) {
            return null;
        }
        return round($amount, $numberOfDigit);
    }

    /**
     * standardlize amount from gateway
     * return number: 50.56 for AUD 5056  |  51 for JPY 51
     */
    function standardizeAmountFromGateway($lowestDenominationAmount, $currency) {
        $numberOfDigit = $this->getNumberOfDigitsAfterDecimal($currency);
        if ($numberOfDigit === null) {
            return null;
        }
        return round($lowestDenominationAmount / pow(10, $numberOfDigit), $numberOfDigit);
    }

    /**
     * get lowest denomination amount
     * return number: 5056 for AUD 50.56  |  51 for JPY 51
     */
    function getLowestDenominationAmount($amount, $currency) {
        $numberOfDigit = $this->getNumberOfDigitsAfterDecimal($currency);
        if ($numberOfDigit === null) {
            return null;
        }
        return round($amount * pow(10, $numberOfDigit));
    }

    /**
     * format amount based on currency
     * return string: "AUD 50.56"  |  "JPY 51"
     */
    function formatAmountCurrency($amount, $currency) {
        $numberOfDigit = $this->getNumberOfDigitsAfterDecimal($currency);
        if ($numberOfDigit === null) {
            return null;
        }
        return $currency . ' ' . number_format(round($amount, $numberOfDigit), $numberOfDigit);
    }

    /**
     * get number of digits after decimal
     * return number: 2 for AUD, 0 for JPY
     */
    function getNumberOfDigitsAfterDecimal($currency) {
        switch (strtoupper($currency)) {
            case 'BHD':
            case 'IQD':
            case 'JOD':
            case 'KWD':
            case 'LYD':
            case 'OMR':
            case 'TND':
                return 3;
            case 'AED':
            case 'AFN':
            case 'ALL':
            case 'AMD':
            case 'ANG':
            case 'AOA':
            case 'ARS':
            case 'AUD':
            case 'AWG':
            case 'AZN':
            case 'BAM':
            case 'BBD':
            case 'BDT':
            case 'BGN':
            case 'BMD':
            case 'BND':
            case 'BOB':
            case 'BRL':
            case 'BSD':
            case 'BTN':
            case 'BWP':
            case 'BZD':
            case 'CAD':
            case 'CDF':
            case 'CFA':
            case 'CFP':
            case 'CHF':
            case 'CNY':
            case 'COP':
            case 'CRC':
            case 'CUP':
            case 'CZK':
            case 'DKK':
            case 'DOP':
            case 'DZD':
            case 'ECS':
            case 'EGP':
            case 'ERN':
            case 'ETB':
            case 'EUR':
            case 'FJD':
            case 'FKP':
            case 'GBP':
            case 'GEL':
            case 'GGP':
            case 'GHS':
            case 'GIP':
            case 'GMD':
            case 'GWP':
            case 'GYD':
            case 'HKD':
            case 'HNL':
            case 'HRK':
            case 'HTG':
            case 'HUF':
            case 'IDR':
            case 'ILS':
            case 'INR':
            case 'IRR':
            case 'JMD':
            case 'KES':
            case 'KGS':
            case 'KHR':
            case 'KPW':
            case 'KYD':
            case 'KZT':
            case 'LAK':
            case 'LBP':
            case 'LKR':
            case 'LRD':
            case 'LSL':
            case 'LTL':
            case 'LVL':
            case 'MAD':
            case 'MDL':
            case 'MGF':
            case 'MKD':
            case 'MMK':
            case 'MNT':
            case 'MOP':
            case 'MRO':
            case 'MUR':
            case 'MVR':
            case 'MWK':
            case 'MXN':
            case 'MYR':
            case 'MZN':
            case 'NAD':
            case 'NGN':
            case 'NIO':
            case 'NOK':
            case 'NPR':
            case 'NZD':
            case 'PAB':
            case 'PEN':
            case 'PGK':
            case 'PHP':
            case 'PKR':
            case 'PLN':
            case 'QAR':
            case 'QTQ':
            case 'RON':
            case 'RSD':
            case 'RUB':
            case 'SAR':
            case 'SBD':
            case 'SCR':
            case 'SDG':
            case 'SEK':
            case 'SGD':
            case 'SHP':
            case 'SLL':
            case 'SOS':
            case 'SRD':
            case 'SSP':
            case 'STD':
            case 'SVC':
            case 'SYP':
            case 'SZL':
            case 'THB':
            case 'TJS':
            case 'TMT':
            case 'TOP':
            case 'TRY':
            case 'TTD':
            case 'TWD':
            case 'TZS':
            case 'UAH':
            case 'USD':
            case 'UYU':
            case 'UZS':
            case 'VEF':
            case 'WST':
            case 'XCD':
            case 'YER':
            case 'ZAR':
            case 'ZMW':
            case 'ZWD':
                return 2;
            case 'BIF':
            case 'BYR':
            case 'CLP':
            case 'CVE':
            case 'DJF':
            case 'GNF':
            case 'ISK':
            case 'JPY':
            case 'KMF':
            case 'KRW':
            case 'PYG':
            case 'RWF':
            case 'UGX':
            case 'VND':
            case 'VUV':
            case 'XAF':
            case 'XOF':
            case 'XPF':
                return 0;
            //Currencies below need to be updated
            case 'GTQ':
            case 'CUC':
            case 'ZWL':
            case 'XDR':
            case 'MGA':
                return 2;
            default:
                return null; //return null if currency code not found
        }
    }

}
