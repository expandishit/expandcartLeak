<?php

class ControllerModuleFastFinder extends Controller
{
    public function search()
    {
        $this->language->load_json('module/fast_finder');
        $this->load->model('module/fast_finder');
        $searchText = trim($this->request->post['search_text']);
        $products = $this->model_module_fast_finder->search($searchText);
        $json = [];
        if (empty($products)) {
            $json['no_results'] = $this->language->get('fast_finder_text_no_results');
        } else {
            foreach($products as $product)
            {
                $json['data'][] = [
                    'link'          => $this->url->link('product/product&product_id=' . $product['product_id'], '', 'SSL'),
                    'name'          => $product['name'],
                    'description'   => isset($product['description']) ? strip_tags($product['description']) : '',
                    'image'         => \Filesystem::getUrl('image/' . $product['image']),
                    'price'         => isset($product['price']) ? $this->currency->format($product['price'], $this->config->get('config_currency')) : '',
                    'quantity'      => isset($product['quantity']) ? sprintf($this->language->get('fast_finder_text_quantity'), $product['quantity']) : '',
                    'special'       => (isset($product['special']) && $product['special_available']) ? $this->currency->format($product['special'], $this->config->get('config_currency')) : ''
                ];
            }
            $json['show_all']['link'] = $this->url->link('product/search&search=' . $searchText, '', 'SSL');
            $json['show_all']['text'] = $this->language->get('fast_finder_text_show_all');
        }
        $this->response->setOutput(json_encode($json));
    }
}
