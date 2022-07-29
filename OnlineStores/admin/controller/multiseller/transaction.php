<?php

class ControllerMultisellerTransaction extends ControllerMultisellerBase
{
    public function getTableData()
    {
        $colMap = array(
            'id' => 'balance_id',
            'seller' => '`nickname`',
            'description' => 'mb.description',
            'date_created' => 'mb.date_created'
        );

        $sorts = array('id', 'seller', 'amount', 'description', 'date_created');
        $filters = $sorts;

        $this->initializer([
            'multiseller/balance'
        ]);

        list($sortCol, $sortDir) = $this->getSortParams($sorts, $colMap);
        $filterParams = $this->getFilterParams($filters, $colMap);

        $results = $this->balance->getBalanceEntries(
            array(),
            array(
                'order_by' => $sortCol,
                'order_way' => $sortDir,
                'filters' => $filterParams,
                'offset' => $this->request->post['start'],
                'limit' => $this->request->post['length']
            )
        );

        $total = isset($results[0]) ? $results[0]['total_rows'] : 0;

        $columns = array();
        foreach ($results as $result) {

            $description = $result['mb.description'];
            if (mb_strlen($result['mb.description']) > 80) {
                $description = mb_substr($result['mb.description'], 0, 80) . '...';
            }

            $columns[] = array_merge(
                $result,
                array(
                    'id' => $result['balance_id'],
                    'seller' => $result['nickname'],
                    'amount' => $this->currency->format($result['amount'], $this->config->get('config_currency')),
                    'description' => $description,
                    'date_created' => date(
                        $this->language->get('date_format_short'), strtotime($result['mb.date_created'])
                    ),
                )
            );
        }

        $this->response->setOutput(json_encode(array(
            "draw" => intval($this->request->post['draw']),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $columns
        )));
    }

    public function index()
    {
        $this->validate(__FUNCTION__);

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        $this->data['link_create_transaction'] = $this->url->link('multiseller/transaction/create', '', 'SSL');
        $this->data['heading'] = $this->language->get('ms_transactions_heading');
        $this->document->setTitle($this->language->get('ms_transactions_heading'));

        $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
            array(
                'text' => $this->language->get('ms_menu_multiseller'),
                'href' => $this->url->link('module/multiseller', '', 'SSL'),
            ),
            array(
                'text' => $this->language->get('ms_transactions_breadcrumbs'),
                'href' => $this->url->link('multiseller/transaction', '', 'SSL'),
            )
        ));

        $this->template = 'multiseller/transaction/list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    public function create()
    {

        $results = $this->MsLoader->MsSeller->getSellers(
            array(),
            array(
                'order_by' => 'ms.nickname',
                'order_way' => 'ASC',
            )
        );

        foreach ($results as $r) {
            $this->data['sellers'][] = array(
                'name' => "{$r['ms.nickname']} ({$r['c.name']})",
                'seller_id' => $r['seller_id']
            );
        }

        $this->data['heading'] = $this->language->get('ms_transactions_heading');
        $this->document->setTitle($this->language->get('ms_transactions_heading'));

        $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
            array(
                'text' => $this->language->get('ms_menu_multiseller'),
                'href' => $this->url->link('module/multiseller', '', 'SSL'),
            ),
            array(
                'text' => $this->language->get('ms_transactions_breadcrumbs'),
                'href' => $this->url->link('multiseller/transaction', '', 'SSL'),
            ),
            array(
                'text' => $this->language->get('ms_transactions_new'),
                'href' => $this->url->link('multiseller/transaction', '', 'SSL'),
            )
        ));

        $this->data['leftCurrencySymbol'] = $this->currency->getSymbolLeft();
        $this->data['rightCurrencySymbol'] = $this->currency->getSymbolRight();

        $this->template = 'multiseller/transaction/form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    public function jxSave()
    {
        $json = array();
        $data = $this->request->post['transaction'];
        if (!$data['from'] && !$data['to']) {
            $json['errors']['transaction[from]'] = $this->language->get('ms_error_transaction_fromto');
            $json['errors']['transaction[to]'] = $this->language->get('ms_error_transaction_fromto');
        } else if ($data['from'] == $data['to']) {
            $json['errors']['transaction[from]'] = $this->language->get('ms_error_transaction_fromto_same');
            $json['errors']['transaction[to]'] = $this->language->get('ms_error_transaction_fromto_same');
        }

        if ((float)$data['amount'] <= 0) {
            $json['errors']['transaction[amount]'] = $this->language->get('ms_error_transaction_amount');
        }

        if (empty($json['errors'])) {
            if ($data['from']) {
                $this->MsLoader->MsBalance->addBalanceEntry($data['from'], array(
                    'balance_type' => MsBalance::MS_BALANCE_TYPE_GENERIC,
                    'amount' => -$data['amount'],
                    'description' => $data['description']
                ));
            }

            if ($data['to']) {
                $this->MsLoader->MsBalance->addBalanceEntry($data['to'], array(
                    'balance_type' => MsBalance::MS_BALANCE_TYPE_GENERIC,
                    'amount' => $data['amount'],
                    'description' => $data['description']
                ));
            }

            $this->session->data['success'] = $this->language->get('ms_success_transaction_created');
        } else {
        }

        $this->response->setOutput(json_encode($json));
    }
}
