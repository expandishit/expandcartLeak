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
        $final_feed_products = array();

        if ($this->config->get('google_base_status'))
        {
            $this->session->data['language'] = $this->config->get('google_base_feed_language');
            
            $this->load->model('catalog/category');
            
            $this->load->model('catalog/product');
            
            $this->load->model('tool/image');

            $this->language->load_json('product/product');

            $white_list_array =array(
                'QAZ123',
                'NADERMOHAMED',
                'KQJGVB4732',
                'KYZEJ865',
                'YJJTL542',
                'LONNRO2910',
                'PMTMGE7889',
                'CCAXVU7000',
                'JJGKSR8375',
                'RZATPJ3934',
                'PCRPNW4489',
                'TBZQFA4820',
            );

            if(in_array(STORECODE,$white_list_array)){
                $product_limit = "10000";
            }else{
                $product_limit = "1000";
            }

            $products = $this->model_catalog_product->getProducts(['limit'=>$product_limit]);
            
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


                    // Product Specific data.
                    $product_link = HTTP_SERVER . 'index.php?route=product/product&product_id='.$product['product_id'];
                    $product_image_link = $product['image'] ? $this->model_tool_image->resize($product['image'], 500, 500) : $this->model_tool_image->resize('no_image.jpg', 500, 500);
                    $product_category = $this->model_catalog_category->getCategory($product['parent_id']);
                    $product_brand = html_entity_decode($product['manufacturer'], ENT_QUOTES, 'UTF-8');
                    $product_currency = $this->config->get('config_currency');
                    $product_tax_special = $this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax'));
                    $product_tax_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
                    if ((float)$product['special'])
                    {
                        $product_price = $this->currency->format($product_tax_special, '', '', false);
                    } 
                    else
                    {
                        $product_price = $this->currency->format($product_tax_price, '', '', false);
                    }
                    if($product['quantity'] > 0 || $product['unlimited']){
                        $stock = "in stock";
                    }else{
                        // if product is out of stock then choose
                        // the status which the admin set for out of stock
                        switch ($product['stock_status_id']){
                            case 5 :
                                $stock = "out of stock";
                                break;
                            case 6 :
                            case 8 :
                                $stock = "preorder";
                                break;
                            case 7 :
                                $stock = "in stock";
                                break;
                            default :
                                $stock = "in stock";
                        }
                    }

                    $product_price =  $this->currency->convert($product_price, $this->currency->getCode(), $this->config->get('config_currency'));

                    $gf_product['g:id']             = mb_convert_encoding($product['product_id'], "UTF-8");
                    $gf_product['g:sku']            = mb_convert_encoding($product['sku'], "UTF-8");
                    $gf_product['g:title']          = mb_convert_encoding($product['name'], "UTF-8");
                    $gf_product['g:description']    = $this->clean(mb_convert_encoding(strip_tags(html_entity_decode($product['description'])), "UTF-8"));
                    $gf_product['g:link']           = mb_convert_encoding($product_link, "UTF-8");
                    $gf_product['g:image_link']     = mb_convert_encoding($product_image_link, "UTF-8");
                    $gf_product['g:availability']   = mb_convert_encoding($stock, "UTF-8");
                    $gf_product['g:price']          = mb_convert_encoding($product_price.' '.$product_currency, "UTF-8");
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

        $this->response->addHeader('Content-Type: application/xml; charset=utf-8');
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

    function clean($input) {
        return preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $input);
    }
}
