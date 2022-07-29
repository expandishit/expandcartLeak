<?php

class ControllerMultisellerProduct extends ControllerMultisellerBase
{
    public function getTableData()
    {
        $this->load->language('multiseller/multiseller');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            parse_str(html_entity_decode($this->request->post['filter']), $filterData);
            $filterData = $filterData['filter'];
        }

        $colMap = array(
            'id' => 'product_id',
            'name' => 'pd.name',
            'status' => 'mp.product_status',
            'seller' => 'ms.nickname',
            'seller_name' => 'ms.nickname',
            'category' => 'category_name',
            'date_created' => 'p.date_created',
            'date_added' => 'p.date_added',
            'date_modified' => 'p.date_modified'
        );

        $sorts = array('name', 'seller', 'date_added', 'date_modified', 'status');
        $filters = array_diff($sorts, array('status'));

        $this->initializer([
            'products' => 'multiseller/product',
            'sellers' => 'multiseller/seller'
        ]);

        list($sortCol, $sortDir) = $this->getSortParams($sorts, $colMap);
        $filterParams = $this->getFilterParams($filters, $colMap);

        $sellers = $this->sellers->getSellers(
            array(
                'seller_status' => array(MsSeller::STATUS_ACTIVE, MsSeller::STATUS_INACTIVE)
            ),
            array(
                'order_by' => 'ms.nickname',
                'order_way' => 'ASC'
            )
        );
        $seller_id=$this->request->get['seller_id'];
        $results = $this->products->getProducts(
            array('seller_id' => $seller_id),
            array(
                'order_by' => $sortCol,
                'order_way' => $sortDir,
                'filters' => $filterParams,
                'filter_data' => $filterData,
                'offset' => $this->request->post['start'],
                'limit' => $this->request->post['length']
            )
        );

        $total = isset($results[0]) ? $results[0]['total_rows'] : 0;

        $columns = array();

        $this->load->model('tool/image');

        foreach ($results as $result) {
            // image
            if ($result['p.image']) {
                $image = $this->model_tool_image->resize($result['p.image'], 40, 40);
            } else {
                $image = $this->model_tool_image->resize('no_image.jpg', 40, 40);
            }

            $columns[] = array_merge(
                $result,
                array(
                    'checkbox' => "<input type='checkbox' name='selected[]' value='{$result['product_id']}' />",
                    'image' => $image,
                    'name' => $result['pd.name'],
                    'seller' => $result['seller_id'],
                    'seller_name' => $result['ms.nickname'],
                    'category' => $result['category_name'],
                    'status' => isset($result['mp.product_status'])?$this->language->get(
                        'ms_product_status_' . $result['mp.product_status']
                    ):'',
                    'date_added' => date(
                        $this->language->get('date_format_short'), strtotime($result['p.date_added'])
                    ),
                    'date_modified' => date(
                        $this->language->get('date_format_short'), strtotime($result['p.date_modified'])
                    ),
                )
            );
        }
        
        $this->response->setOutput(json_encode(array(
            "draw" => intval($this->request->post['draw']),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $columns,
            'sellers' => $sellers
        )));
    }

    public function index()
    {
        $this->validate(__FUNCTION__);

        if (isset($this->session->data['error'])) {
            $this->data['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        $this->initializer([
            'products' => 'multiseller/product',
            'sellers' => 'multiseller/seller'
        ]);

        $this->data['sellers'] = $this->sellers->getSellers(
            array(
                'seller_status' => array(MsSeller::STATUS_ACTIVE, MsSeller::STATUS_INACTIVE)
            ),
            array(
                'order_by' => 'ms.nickname',
                'order_way' => 'ASC'
            )
        );

        $this->load->language('catalog/product_filter');
        $this->load->language('catalog/product');
        $this->data['seller_id']=$this->request->get['seller_id'];
     
        $this->data['heading_title'] = $this->language->get('ms_catalog_products_breadcrumbs');
        $this->data['token'] = $this->session->data['token'];
        $this->data['heading'] = $this->language->get('ms_catalog_products_heading');
        $this->document->setTitle($this->language->get('ms_catalog_products_heading'));

        $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
            array(
                'text' => $this->language->get('ms_menu_multiseller'),
                'href' => $this->url->link('module/multiseller', '', 'SSL'),
            ),
            array(
                'text' => $this->language->get('ms_catalog_products_breadcrumbs'),
                'href' => $this->url->link('multiseller/product', '', 'SSL'),
            )
        ));

        $this->template = 'multiseller/product/list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    public function jxProductStatus()
    {
        $this->validate(__FUNCTION__);
        $mails = array();

        if (isset($this->request->post['selected'])) {
            foreach ($this->request->post['selected'] as $product_id) {
                $seller = $this->MsLoader->MsSeller->getSeller($this->MsLoader->MsProduct->getSellerId($product_id));

                if ((int)$this->request->post['bulk_product_status'] > 0) {
                    $this->MsLoader->MsProduct->createRecord($product_id, array());
                    switch ($this->request->post['bulk_product_status']) {
                        case MsProduct::STATUS_ACTIVE:
                            if ($seller['ms.seller_status'] != MsSeller::STATUS_ACTIVE) {
                                $this->session->data['error'] = $this->language->get('ms_error_product_publish');
                            } else {
                                $this->MsLoader->MsProduct->changeStatus($product_id, MsProduct::STATUS_ACTIVE);
                                $this->MsLoader->MsProduct->approve($product_id);
                            }
                            break;
                        case MsProduct::STATUS_INACTIVE:
                            $this->MsLoader->MsProduct->changeStatus($product_id, MsProduct::STATUS_INACTIVE);
                            $this->MsLoader->MsProduct->disapprove($product_id);
                            break;
                        case MsProduct::STATUS_DISABLED:
                            $this->MsLoader->MsProduct->changeStatus($product_id, MsProduct::STATUS_DISABLED);
                            $this->MsLoader->MsProduct->disapprove($product_id);
                            break;
                        case MsProduct::STATUS_DELETED:
                            $this->MsLoader->MsProduct->changeStatus($product_id, MsProduct::STATUS_DELETED);
                            $this->MsLoader->MsProduct->disapprove($product_id);
                            break;
                    }

                    if (!isset($this->session->data['error']))
                        $this->session->data['success'] = $this->language->get('ms_success_product_status');
                }

                if ($seller['ms.seller_status'] == MsSeller::STATUS_ACTIVE) {
                    $mails[] = array(
                        'type' => MsMail::SMT_PRODUCT_MODIFIED,
                        'data' => array(
                            'recipients' => $seller['c.email'],
                            'addressee' => $seller['ms.nickname'],
                            'message' => isset($this->request->post['product_message']) ? $this->request->post['product_message'] : '',
                            'product_id' => $product_id
                        )
                    );
                }
            }

            if (isset($this->request->post['bulk_mail'])) {
                $this->MsLoader->MsMail->sendMails($mails);
            }
        } else {
            //$this->session->data['error'] = 'Error changing product status.';
        }
    }

    public function jxProductSeller()
    {
        $json = array();

        $this->validate(__FUNCTION__);

        if ( is_null($this->request->get['seller_id']) ) {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = [$this->language->get('undefined_id_error')];

            $this->response->setOutput(json_encode($response));
            return;
        }

        $product_id = $this->request->get['product_id'];
        $seller = $this->MsLoader->MsSeller->getSeller($this->request->get['seller_id']);
        $this->MsLoader->MsProduct->createRecord($product_id, array('seller_id' => $this->request->get['seller_id']));
        $this->MsLoader->MsProduct->changeSeller($product_id, $this->request->get['seller_id']);
        $json['product_status'] = $this->language->get('ms_product_status_' . $seller['ms.seller_status']);
        switch ($seller['ms.seller_status']) {
            case MsSeller::STATUS_INACTIVE:
            case MsSeller::STATUS_UNPAID:
                $this->MsLoader->MsProduct->changeStatus($product_id, MsProduct::STATUS_INACTIVE);
                $this->MsLoader->MsProduct->disapprove($product_id);
                $json['product_status'] = $this->language->get('ms_product_status_' . MsProduct::STATUS_INACTIVE);
                break;
            case MsSeller::STATUS_DISABLED:
                $this->MsLoader->MsProduct->changeStatus($product_id, MsProduct::STATUS_DISABLED);
                $this->MsLoader->MsProduct->disapprove($product_id);
                break;
            case MsSeller::STATUS_DELETED:
                $this->MsLoader->MsProduct->changeStatus($product_id, MsProduct::STATUS_DELETED);
                $this->MsLoader->MsProduct->disapprove($product_id);
                break;
            case MsSeller::STATUS_UNPAID:
                $this->MsLoader->MsProduct->changeStatus($product_id, MsProduct::STATUS_DELETED);
                $this->MsLoader->MsProduct->disapprove($product_id);
                break;
            default:
                $product = $this->MsLoader->MsProduct->getProduct($product_id);
                $this->MsLoader->MsProduct->changeStatus($product_id, MsProduct::STATUS_ACTIVE);
                $json['product_status'] = $this->language->get('ms_product_status_' . $product['mp.product_status']);
                break;
        }
        // ===
        // Commented so the admin doesn't receieve a new order mail on assigning products.
        // ===
        // if ($seller['ms.seller_status'] == MsSeller::STATUS_ACTIVE) {
        //     $mail = array(
        //             'recipients' => $seller['c.email'],
        //             'addressee' => $seller['ms.nickname'],
        //             'message' => isset($this->request->post['product_message']) ? $this->request->post['product_message'] : '',
        //             'product_id' => $product_id
        //     );
        //     $this->MsLoader->MsMail->sendMail(MsMail::SMT_PRODUCT_MODIFIED,$mail);
        // }
        $json['status'] = 'success';
        $json['title'] = $this->language->get('notification_success_title');
        $json['message'] = $this->language->get('message_updated_successfully');

        $this->response->setOutput(json_encode($json));
    }

    public function delete()
    {
        if (isset($this->request->post['product_id'])) {
            $this->request->post['selected'] = array($this->request->post['product_id']);
        }

        if (isset($this->request->post['selected'])) {
            foreach ($this->request->post['selected'] as $product_id) {
                $this->MsLoader->MsProduct->deleteProduct($product_id);
            }

            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_bulkdelete_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_bulkdelete_error');
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function download(){
       // $download_path = BASE_STORE_DIR .$this->request->get['download'];
        \Filesystem::setPath($this->request->get['download']);
		if (!headers_sent()) {
			if (\Filesystem::isExists()) {
				header('Content-Type: application/octet-stream');
				header('Content-Description: File Transfer');
				header('Content-Disposition: attachment; filename="' . ( basename($this->request->get['download'])) . '"');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . \Filesystem::getSize());
				if (ob_get_level()) ob_end_clean();
				//readfile($download_path, 'rb');
                echo \Filesystem::read();
				exit;
			} 
		}
    }
}
