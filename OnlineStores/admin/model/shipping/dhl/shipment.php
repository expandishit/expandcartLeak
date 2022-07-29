<?php

use Petschko\DHL\Credentials;
use Petschko\DHL\ShipmentDetails;
use Petschko\DHL\ShipmentOrder;
use Petschko\DHL\Sender;
use Petschko\DHL\BusinessShipment;
use Petschko\DHL\Response;
use Petschko\DHL\LabelData;
use Petschko\DHL\Receiver;
use Petschko\DHL\BankData;

class ModelShippingDhlShipment
{
    const IS_COD = 'COD';

    /**
     * @var string
     */
    protected $env;

    /**
     * Available paths based on the environment
     *
     * @var array
     */
    protected $base = [
        'live' => '',
        'test' => '',
    ];

    /**
     * The response object.
     *
     * @var Response
     */
    protected $response;

    /**
     * Array errors.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * The Credentials object.
     *
     * @var Credentials
     */
    protected $credentials;

    /**
     * The Sender object.
     *
     * @var Sender
     */
    protected $sender;

    /**
     * The Receiver object.
     *
     * @var Receiver
     */
    protected $receiver;

    /**
     * The ShipmentDetails object.
     *
     * @var ShipmentDetails
     */
    protected $shipmentDetails;

    /**
     * The BankData object.
     *
     * @var BankData
     */
    protected $bankData;

    /**
     * @var int
     */
    protected $shipmentNumber;

    /**
     * Wsdl Path.
     *
     * @var string
     */
    protected $wsdl;

    /**
     * @var string
     */
    protected $testMode;

    /**
     * @var string
     */
    protected $dhlUser;

    /**
     * @var string
     */
    protected $dhlPassword;

    /**
     * @var string
     */
    protected $ekpNumber;

    /**
     * @var string
     */
    protected $appId;

    /**
     * @var string
     */
    protected $appToken;

    /**
     * @var string
     */
    protected $developerId;

    /**
     * @var string
     */
    protected $developerPassword;

    /**
     * Check if the payment method is set to Cash on delivery.
     *
     * @var bool
     */
    protected $isCod;

    /**
     * Set the environment variable.
     *
     * @param mixed $env
     *
     * @return $this
     */
    public function setEnv($env)
    {
        if ($env == 0 || $env == null || empty($env) || isset($env) == false) {
            $env = 'test';
        } else {
            $env = 'live';
        }

        $this->env = $env;

        return $this;
    }

    /**
     * Return the environment string.
     *
     * @return string
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * Return the base url.
     *
     * @return string
     */
    public function getBase()
    {
        return $this->base[$this->getEnv()];
    }

    /**
     * Return the Response object.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the Response object.
     *
     * @param int $testMode
     *
     * @return $this
     */
    public function setTestMode($testMode)
    {
        $this->testMode = ($testMode == 'thermo' ? Credentials::TEST_THERMO_PRINTER : Credentials::TEST_NORMAL);

        return $this;
    }

    /**
     * Set the Credentials object.
     *
     * @param array $credentialsData
     *
     * @return $this
     */
    public function setCredentials($credentialsData)
    {
        if ($this->env === 'live') {
            $credentials = new Credentials();
            $credentials->setUser($credentialsData['username']);
            $credentials->setSignature($credentialsData['password']);
            $credentials->setEkp($credentialsData['ekp_number']);
            $credentials->setApiUser($credentialsData['app_id']);
            $credentials->setApiPassword($credentialsData['app_token']);
        } else {
            $this->setTestMode($this->env);

            $credentials = new Credentials('test');
            $credentials->setApiUser($credentialsData['developer_id']);
            $credentials->setApiPassword($credentialsData['developer_password']);
        }

        $this->credentials = $credentials;

        return $this;
    }

    /**
     * Set the Sender object.
     *
     * @param array $senderData
     *
     * @return $this
     */
    public function setSender($senderData)
    {
        $sender = new Sender();
        $sender->setName($senderData['name']);
        $sender->setStreetName($senderData['address']);
        $sender->setStreetNumber($senderData['street_number']);
        $sender->setZip($senderData['postalcode']);
        $sender->setCity($senderData['city']);
        $sender->setCountry($senderData['country']);
        $sender->setCountryISOCode($senderData['country_iso']);
        $sender->setPhone($senderData['phone']);
        $sender->setEmail($senderData['email']);
        $sender->setContactPerson($senderData['contact_person']);

        $this->sender = $sender;

        return $this;
    }

    /**
     * Set the Receiver object.
     *
     * @param array $receiverData
     *
     * @return $this
     */
    public function setReceiver($receiverData)
    {
        $receiver = new Receiver();
        $receiver->setName($receiverData['name']);
        $receiver->setStreetName($receiverData['address']);
        $receiver->setStreetNumber($receiverData['street_number']);
        $receiver->setZip($receiverData['postalcode']);
        $receiver->setCity($receiverData['city']);
        $receiver->setCountry($receiverData['country']);
        $receiver->setCountryISOCode($receiverData['country_iso']);
        $receiver->setPhone($receiverData['phone']);
        $receiver->setEmail($receiverData['email']);
        $receiver->setContactPerson($receiverData['contact_person']);

        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Set the Receiver object.
     *
     * @param array $bankDetails
     *
     * @return $this
     */
    public function setBankObject($bankDetails)
    {
        $bankData = new BankData;

        $bankData->setAccountOwnerName($bankDetails['owner_name']);
        $bankData->setBankName($bankDetails['bank_name']);
        $bankData->setIban($bankDetails['iban']);
        $bankData->setNote1($bankDetails['note_1']);
        $bankData->setNote2($bankDetails['note_2']);
        $bankData->setBic($bankDetails['bic']);
        $bankData->setAccountReference($bankDetails['account_reference']);

        $this->bankData = $bankData;

        return $this;
    }

    /**
     * Set the ShipmentDetails object.
     *
     * @param array $shipmentData
     *
     * @return $this
     */
    public function setShippmentDetails($shipmentData)
    {
        $shipmentDetails = new ShipmentDetails($this->credentials->getEkp(10) . '0101');

//        $shipmentDetails->setProduct($shipmentData['product']);

        $shipmentDetails->setShipmentDate($shipmentData['shipment_date']);

        if (isset($shipmentData['total_weight']) && $shipmentData['total_weight'] > 0) {
            $shipmentDetails->setWeight((float)$shipmentData['total_weight']);
        }

        if (isset($shipmentData['total_length']) && $shipmentData['total_length'] > 0) {
            $shipmentDetails->setLength((int)$shipmentData['total_length']);
        }

        if (isset($shipmentData['total_width']) && $shipmentData['total_width'] > 0) {
            $shipmentDetails->setWidth((int)$shipmentData['total_width']);
        }

        if (isset($shipmentData['total_height']) && $shipmentData['total_height'] > 0) {
            $shipmentDetails->setHeight((int)$shipmentData['total_height']);
        }

        if (filter_var($shipmentData['notificationEmail'], FILTER_VALIDATE_EMAIL)) {
            $shipmentDetails->setNotificationEmail($shipmentData['notificationEmail']);
        }

        if ($this->getPaymentType() === self::IS_COD) {

            $this->setBankObject($shipmentData['bankDetails']);

            $shipmentDetails->setBank($this->bankData);
        }

        $this->shipmentDetails = $shipmentDetails;

        return $this;
    }

    /**
     * Set the Receiver object.
     *
     * @param int $isCod
     *
     * @return $this
     */
    public function setPaymentType($isCod)
    {
        $this->isCod = $isCod === self::IS_COD ? self::IS_COD : false;

        return $this;
    }

    /**
     * Return the payment type.
     *
     * @return int
     */
    public function getPaymentType()
    {
        return $this->isCod;
    }

    /**
     * Set the Wsdl link.
     *
     * @param string $wsdl
     *
     * @return $this
     */
    public function setWsdl($wsdl)
    {
        $this->wsdl = $wsdl;

        return $this;
    }

    /**
     * Return the wsdl link.
     *
     * @return string
     */
    public function getWsdl()
    {
        return $this->wsdl;
    }

    /**
     * Return the response errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Set shipment number.
     *
     * @param int $shipmentNumber
     *
     * @return $this
     */
    public function setShipmentNumber($shipmentNumber)
    {
        $this->shipmentNumber = $shipmentNumber;

        return $this;
    }

    /**
     * Get the shipment number.
     *
     * @return int
     */
    public function getShipmentNumber()
    {
        return $this->shipmentNumber;
    }

    /**
     * Create a shipment.
     *
     * @return bool
     */
    public function create()
    {
        $shipmentOrder = new ShipmentOrder();

        $shipmentOrder->setShipmentDetails($this->shipmentDetails);
        $shipmentOrder->setSender($this->sender);
        $shipmentOrder->setReceiver($this->receiver);

        $dhl = new BusinessShipment($this->credentials, ($this->env === 'live' ? false : true));

        $dhl->setCustomAPIURL($this->wsdl);

        $dhl->addShipmentOrder($shipmentOrder);

        $this->response = $dhl->createShipment();

        if($this->response != false) {
            return true;
        }

        $this->errors = $dhl->getErrors();
        return false;
    }

    /**
     * Get shipment details.
     *
     * return bool
     */
    public function getShipment()
    {
        $dhl = new BusinessShipment($this->credentials, ($this->env === 'live' ? false : true));

        $this->response = $dhl->getShipmentLabel($this->getShipmentNumber());

        if($this->response != false) {
            return true;
        }

        $this->errors = $dhl->getErrors();
        return false;
    }
}
