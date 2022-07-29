<?php 
/**
*   This code has been re-written to go with the new Google Feed functionality.
*
*   @author Michael
*/
class ControllerFeedGoogleBase extends Controller
{
    public function index()
    {
        if ($this->config->get('google_base_status'))
        {

            $final_feed_products = array();

            $this->session->data['language'] = $this->config->get('google_base_feed_language');
            
            $this->load->model('catalog/category');
            
            $this->load->model('catalog/product');
            
            $this->load->model('tool/image');
            
            $products = $this->model_catalog_product->getProducts();
            
            foreach ($products as $product)
            {
                // An array to hold Google-Friendly information about the product.
                $gf_product = array();

                // Will be left off as there is no way of determining whether this product is a clothing or not.
                // $gf_product['is_clothing'] = false; //set True or False, depending on whether product is clothing.

                // Is the product on sale?
                // $gf_product['is_on_sale'] = false; //set True or False depending on whether product is on sale

                if ($product['description'])
                {

                    $currencies = array(
                        'USD', 
                        'EUR', 
                        'GBP'
                    );

                    if ( in_array($this->currency->getCode(), $currencies) )
                    {
                        $currency_code = $this->currency->getCode();
                        $currency_value = $this->currency->getValue();
                    }
                    else
                    {
                        $currency_code = 'USD';
                        $currency_value = $this->currency->getValue('USD');
                    }

                    // Product Specific data.
                    $product_link = HTTP_SERVER . 'index.php?route=product/product&product_id='.$product['product_id'];
                    $product_image_link = $product['image'] ? $this->model_tool_image->resize($product['image'], 500, 500) : $this->model_tool_image->resize('no_image.jpg', 500, 500);
                    $product_category = $this->model_catalog_category->getCategory($product['parent_id']);
                    $product_brand = html_entity_decode($product['manufacturer'], ENT_QUOTES, 'UTF-8');

                    if ((float)$product['special'])
                    {
                        $product_price =  $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id']), $currency_code, $currency_value, false);
                    } 
                    else
                    {
                        $product_price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id']), $currency_code, $currency_value, false);
                    }

                    $gf_product['g:id']             = mb_convert_encoding($product['product_id'], "UTF-8");
                    $gf_product['g:sku']            = mb_convert_encoding($product['sku'], "UTF-8");
                    $gf_product['g:title']          = mb_convert_encoding($product['name'], "UTF-8");
                    $gf_product['g:description']    = mb_convert_encoding($product['meta_description'], "UTF-8");
                    $gf_product['g:link']           = mb_convert_encoding($product_link, "UTF-8");
                    $gf_product['g:image_link']     = mb_convert_encoding($product_image_link, "UTF-8");
                    $gf_product['g:availability']   = mb_convert_encoding($product['stock_status'], "UTF-8");
                    $gf_product['g:price']          = mb_convert_encoding($product_price, "UTF-8");
                    $gf_product['g:brand']          = mb_convert_encoding($product_brand, "UTF-8");
                    $gf_product['g:mpn']            = mb_convert_encoding($product['mpn'], "UTF-8");

                    // $gf_product['g:google_product_category'] = $product['google_product_category'];          // what's that even?
                    // $gf_product['g:gtin'] = $product['gtin'];        // wasn't found in the product object.

                    if (($gf_product['g:gtin'] == "") && ($gf_product['g:mpn'] == "")) { $gf_product['g:identifier_exists'] = mb_convert_encoding("no", "UTF-8"); };

                    $gf_product['g:condition'] = mb_convert_encoding("NEW", "UTF-8"); //must be NEW or USED

                        //remove this IF block if you don't sell any clothing
                        // Sadly there is no way to determine whether this product is a clothing or not. leaving for future.
                    // if ($gf_product['is_clothing']) {
                    //  $gf_product['g:age_group'] = $product['age_group']; //newborn/infant/toddle/kids/adult
                    //  $gf_product['g:color'] = $product['color'];
                    //  $gf_product['g:gender'] = $product['gender'];
                    //  $gf_product['g:size'] = $product['size'];
                    // }

                    $final_feed_products[] = $gf_product;
                }
            }
        }

        $this->response->addHeader('Content-Type: application/rss+xml; charset=utf-8');
        $this->response->setOutput($this->createXMLOutput($final_feed_products));
    }

    /**
    *   Create XML output from the giving information.
    *
    *   @author Michael.
    *   @param array $feed_products.
    *   @return string $output.
    */
    private function createXMLOutput( array $feed_products)
    {
        $shop_name = $this->config->get('config_name');
        $shop_link = HTTP_SERVER;
        $doc = new DOMDocument('1.0', 'UTF-8');

        $xmlRoot = $doc->createElement("rss");
        $xmlRoot = $doc->appendChild($xmlRoot);
        $xmlRoot->setAttribute('version', '2.0');
        $xmlRoot->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:g', "http://base.google.com/ns/1.0");
         
        $channelNode = $xmlRoot->appendChild($doc->createElement('channel'));
        $channelNode->appendChild($doc->createElement('title', $shop_name));
        $channelNode->appendChild($doc->createElement('link', $shop_link));
        foreach ($feed_products as $product) 
        {
            $itemNode = $channelNode->appendChild($doc->createElement('item'));

            foreach($product as $key=>$value)
            {
                $value = trim($value);

                if ($value != "")
                {
                    if ( is_array($product[$key]) )
                    {
                        $subItemNode = $itemNode->appendChild($doc->createElement($key));

                        foreach( $product[$key] as $key2=>$value2 )
                        {
                            $subItemNode->appendChild($doc->createElement($key2))->appendChild($doc->createTextNode($value2));
                        }
                    }
                    else
                    {
                        $itemNode->appendChild($doc->createElement($key))->appendChild($doc->createTextNode($value));
                    }
                }
                else
                {
                    $itemNode->appendChild($doc->createElement($key));
                }
            }
        }

        $doc->formatOutput = true;
        return $doc->saveXML();
    }
}
