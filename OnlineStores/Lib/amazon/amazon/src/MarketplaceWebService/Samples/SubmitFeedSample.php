<?php
/**
 *  PHP Version 5
 *
 *  @category    Amazon
 *  @package     MarketplaceWebService
 *  @copyright   Copyright 2009 Amazon Technologies, Inc.
 *  @link        http://aws.amazon.com
 *  @license     http://aws.amazon.com/apache2.0  Apache License, Version 2.0
 *  @version     2009-01-01
 */
/*******************************************************************************

 *  Marketplace Web Service PHP5 Library
 *  Generated: Thu May 07 13:07:36 PDT 2009
 *
 */

/**
 * Submit Feed  Sample
 */

class Submitfeed {

  public $request;
  public $service;
  public $feedHandle;

    public function __construct($amazonmws)
    {
        include_once('.config.inc.php');

        /************************************************************************
         * Uncomment to configure the client instance. Configuration settings
         * are:
         *
         * - MWS endpoint URL
         * - Proxy host and port.
         * - MaxErrorRetry.
         ***********************************************************************/
        // IMPORTANT: Uncomment the approiate line for the country you wish to
        // sell in:

        $serviceUrl = $amazonmws->serviceUrl;

        $config = array(
            'ServiceURL' => $serviceUrl,
            'ProxyHost' => null,
            'ProxyPort' => -1,
            'MaxErrorRetry' => 3,
        );

        /************************************************************************
         * Instantiate Implementation of MarketplaceWebService
         *
         * AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY constants
         * are defined in the .config.inc.php located in the same
         * directory as this sample
         ***********************************************************************/
        $this->service = new MarketplaceWebService_Client (
            $amazonmws->accessKeyId,
            $amazonmws->secretKey,
            $config,
            $amazonmws->applicationName,
            $amazonmws->applicationVersion
        );
        $feed = false;
        $MerchantIdentifier = $amazonmws->sellerId;

        if ($amazonmws->product['ActionType'] == 'AddProduct') {
            if ($amazonmws->product['ProductData']) {

                $feed = <<<EOD
<?xml version="1.0" encoding="iso-8859-1"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
  <Header>
    <DocumentVersion>1.02</DocumentVersion>
    <MerchantIdentifier>$MerchantIdentifier</MerchantIdentifier>
  </Header>
  <MessageType>Product</MessageType>
  <PurgeAndReplace>false</PurgeAndReplace>
EOD;
                $messageId = 1;
                foreach ($amazonmws->product['ProductData'] as $product) {
                    $SKU = $product['sku'];
                    $exportProductType = $product['exportProductType'];
                    $exportProductTypeValue = $product['exportProductTypeValue'];
                    $name = $product['name'];
                    $description = $product['description'];
                    if (isset($product['brand'])) {
                        $brand = $product['brand'];
                    } else {
                        $brand = 'super_brand';
                    }

                    $feed .= <<<EOD
  <Message>
    <MessageID>$messageId</MessageID>
    <OperationType>Update</OperationType>
    <Product>
      <SKU>$SKU</SKU>
      <StandardProductID>
        <Type>$exportProductType</Type>
        <Value>$exportProductTypeValue</Value>
      </StandardProductID>
      <DescriptionData>
        <Title>$name</Title>
        <Brand>$brand</Brand>
        <Description>$description</Description>
      </DescriptionData>
      <ProductData>
       <Health>
         <ProductType>
           <HealthMisc>
             <Ingredients>Example Ingredients</Ingredients>
             <Directions>Example Directions</Directions>
           </HealthMisc>
         </ProductType>
       </Health>
     </ProductData>
    </Product>
  </Message>
EOD;

                    $messageId++;
                }

                $feed .= <<<EOD
</AmazonEnvelope>
EOD;

            }
        } else if ($amazonmws->product['ActionType'] == 'UpdateQuantity') {
            if ($amazonmws->product['ProductData']) {

                $feed = <<<EOD
<?xml version="1.0" ?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
  <Header>
    <DocumentVersion>1.02</DocumentVersion>
    <MerchantIdentifier>$MerchantIdentifier</MerchantIdentifier>
  </Header>
  <MessageType>Inventory</MessageType>
  <PurgeAndReplace>false</PurgeAndReplace>
EOD;
                $messageId = 1;
                foreach ($amazonmws->product['ProductData'] as $product) {
                    $SKU = $product['sku'];
                    $quantity = $product['qty'];

                    $feed .= <<<EOD
  <Message>
    <MessageID>$messageId</MessageID>
    <OperationType>Update</OperationType>
    <Inventory>
      <SKU>$SKU</SKU>
      <Quantity>$quantity</Quantity>
    </Inventory>
  </Message>
EOD;

                    $messageId++;
                }

                $feed .= <<<EOD
</AmazonEnvelope>
EOD;

            }
        } else if ($amazonmws->product['ActionType'] == 'UpdatePrice') {
            if ($amazonmws->product['ProductData']) {

                $feed = <<<EOD
<?xml version="1.0" ?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
  <Header>
    <DocumentVersion>1.02</DocumentVersion>
    <MerchantIdentifier>$MerchantIdentifier</MerchantIdentifier>
  </Header>
  <MessageType>Price</MessageType>
  <PurgeAndReplace>false</PurgeAndReplace>
EOD;
                $messageId = 1;
                foreach ($amazonmws->product['ProductData'] as $product) {
                    $SKU = $product['sku'];
                    $currency_symbol = $product['currency_symbol'];
                    $price = $product['price'];

                    $feed .= <<<EOD
  <Message>
    <MessageID>$messageId</MessageID>
    <OperationType>Update</OperationType>
    <Price>
      <SKU>$SKU</SKU>
      <StandardPrice currency="$currency_symbol">$price</StandardPrice>
    </Price>
  </Message>
EOD;

                    $messageId++;
                }

                $feed .= <<<EOD
</AmazonEnvelope>
EOD;

            }
        } else if ($amazonmws->product['ActionType'] == 'UpdateImage') {
            if ($amazonmws->product['ProductData']) {

                $feed = <<<EOD
<?xml version="1.0" ?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
  <Header>
    <DocumentVersion>1.02</DocumentVersion>
    <MerchantIdentifier>$MerchantIdentifier</MerchantIdentifier>
  </Header>
  <MessageType>ProductImage</MessageType>
EOD;
                $messageId = 1;
                foreach ($amazonmws->product['ProductData'] as $product) {
                    $SKU = $product['sku'];
                    $image_url = $product['image_url'];

                    $feed .= <<<EOD
      <Message>
          <MessageID>$messageId</MessageID>
          <OperationType>Update</OperationType>
          <ProductImage>
            <SKU>$SKU</SKU>
            <ImageType>Main</ImageType>
            <ImageLocation>$image_url</ImageLocation>
          </ProductImage>
      </Message>
EOD;

                    $messageId++;
                }

                $feed .= <<<EOD
</AmazonEnvelope>
EOD;

            }
        } else if ($amazonmws->product['ActionType'] == 'DeleteProduct') {
            if ($amazonmws->product['ProductData']) {

                $feed = <<<EOD
<?xml version="1.0" ?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
  <Header>
    <DocumentVersion>1.02</DocumentVersion>
    <MerchantIdentifier>$MerchantIdentifier</MerchantIdentifier>
  </Header>
  <MessageType>Product</MessageType>
  <PurgeAndReplace>false</PurgeAndReplace>
EOD;
                $messageId = 1;
                foreach ($amazonmws->product['ProductData'] as $product) {
                    $SKU = $product['sku'];

                    $feed .= <<<EOD
  <Message>
    <MessageID>$messageId</MessageID>
    <OperationType>Delete</OperationType>
    <Product>
      <SKU>$SKU</SKU>
    </Product>
  </Message>
EOD;

                    $messageId++;
                }

                $feed .= <<<EOD
</AmazonEnvelope>
EOD;

            }
        }

        $this->feedHandle = @fopen('php://memory', 'rw+');
        fwrite($this->feedHandle, $feed);
        rewind($this->feedHandle);

        $this->request = new MarketplaceWebService_Model_SubmitFeedRequest();
        $this->request->setMerchant($amazonmws->sellerId);
        $this->request->setMarketplaceIdList(array("Id" => array($amazonmws->marketplaceId)));
        $this->request->setFeedType($amazonmws->feedType);

        $this->request->setContentMd5(base64_encode(md5(stream_get_contents($this->feedHandle), true)));
        rewind($this->feedHandle);
        $this->request->setFeedContent($this->feedHandle);

        rewind($this->feedHandle);

    }

  /**
  * Submit Feed Action Sample
  * Uploads a file for processing together with the necessary
  * metadata to process the file, such as which type of feed it is.
  * PurgeAndReplace if true means that your existing e.g. inventory is
  * wiped out and replace with the contents of this feed - use with
  * caution (the default is false).
  *
  * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
  * @param mixed $request MarketplaceWebService_Model_SubmitFeed or array of parameters
  */
  public function invokeSubmitFeed(MarketplaceWebService_Interface $service, $request) {
      try {
          $response = $service->submitFeed($request);
          if ($response->isSetSubmitFeedResult()) {

            $submitFeedResult = $response->getSubmitFeedResult();
            if ($submitFeedResult->isSetFeedSubmissionInfo()) {

              $feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();
              if ($feedSubmissionInfo->isSetFeedSubmissionId()) {
                  return array (
                  'success' => true,
                  'feedSubmissionId' => $feedSubmissionInfo->getFeedSubmissionId(),
                  'feedProcessingStatus' => $feedSubmissionInfo->getFeedProcessingStatus(),
                  );
              }
            }
          }

      } catch (MarketplaceWebService_Exception $ex) {
          return array (
          'error' => true,
          'comment' => $ex->getMessage(),
          );
      }
  }
}
