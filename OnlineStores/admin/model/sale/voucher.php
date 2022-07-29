<?php

class ModelSaleVoucher extends Model
{
    public function addVoucher($data)
    {
        $queryString = $fields = [];

        $queryString[] = 'INSERT ' . DB_PREFIX . 'voucher SET';
        $fields[] = 'code = "' . $this->db->escape($data['code']) . '"';
        $fields[] = 'from_name = "' . $this->db->escape($data['from_name']) . '"';
        $fields[] = 'from_email = "' . $this->db->escape($data['from_email']) . '"';
        $fields[] = 'to_name = "' . $this->db->escape($data['to_name']) . '"';
        $fields[] = 'to_email = "' . $this->db->escape($data['to_email']) . '"';
        $fields[] = 'voucher_theme_id = "' . (int)$data['voucher_theme_id'] . '"';
        $fields[] = 'message = "' . $this->db->escape($data['message']) . '"';
        $fields[] = 'amount = "' . (float)$data['amount'] . '"';
        $fields[] = 'status = "' . (int)$data['status'] . '"';
        $fields[] = 'date_added = NOW()';
        $queryString[] = implode(',', $fields);

        $this->db->query(implode(' ', $queryString));
        $voucher_id = $this->db->getLastId();
        return $voucher_id;
    }

    public function editVoucher($voucher_id, $data)
    {
        $queryString = $fields = [];

        $queryString[] = 'UPDATE ' . DB_PREFIX . 'voucher SET';
        $fields[] = 'code = "' . $this->db->escape($data['code']) . '"';
        $fields[] = 'from_name = "' . $this->db->escape($data['from_name']) . '"';
        $fields[] = 'from_email = "' . $this->db->escape($data['from_email']) . '"';
        $fields[] = 'to_name = "' . $this->db->escape($data['to_name']) . '"';
        $fields[] = 'to_email = "' . $this->db->escape($data['to_email']) . '"';
        $fields[] = 'voucher_theme_id = "' . (int)$data['voucher_theme_id'] . '"';
        $fields[] = 'message = "' . $this->db->escape($data['message']) . '"';
        $fields[] = 'amount = "' . (float)$data['amount'] . '"';
        $fields[] = 'status = "' . (int)$data['status'] . '"';
        $queryString[] = implode(',', $fields);
        $queryString[] = 'WHERE voucher_id = "' . (int)$voucher_id . '"';

        $this->db->query(implode(' ', $queryString));
    }

    public function deleteVoucher($voucher_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "voucher WHERE voucher_id = '" . (int)$voucher_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "voucher_history WHERE voucher_id = '" . (int)$voucher_id . "'");
    }

    public function getVoucher($voucher_id)
    {
        $queryString = [];
        $queryString[] = 'SELECT DISTINCT * FROM ' . DB_PREFIX . 'voucher';
        $queryString[] = 'WHERE voucher_id = "' . (int)$voucher_id . '"';

        $query = $this->db->query(implode(' ', $queryString));

        return $query->row;
    }

    public function getVoucherByCode($code)
    {
        $queryString = [];
        $queryString[] = 'SELECT DISTINCT * FROM ' . DB_PREFIX . 'voucher';
        $queryString[] = 'WHERE code = "' . $this->db->escape($code) . '"';

        $query = $this->db->query(implode(' ', $queryString));

        return $query->row;
    }

    public function getVouchers($data = array())
    {
        $queryString = $subQuery = $fields = [];

        $subQuery[] = 'SELECT vtd.name FROM ' . DB_PREFIX . 'voucher_theme_description vtd';
        $subQuery[] = 'WHERE vtd.voucher_theme_id = v.voucher_theme_id';
        $subQuery[] = 'AND vtd.language_id = "' . (int)$this->config->get('config_language_id') . '"';

        $fields[] = 'v.voucher_id';
        $fields[] = 'v.code';
        $fields[] = 'v.from_name';
        $fields[] = 'v.from_email';
        $fields[] = 'v.to_name';
        $fields[] = 'v.to_email';
        $fields[] = '(%s) AS theme';
        $fields[] = 'v.amount';
        $fields[] = 'v.status';
        $fields[] = 'v.date_added';

        $fields = sprintf(implode(', ', $fields), implode(' ', $subQuery));

        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "voucher as v";

        if (!empty($data['filter_name'])) {
            $queryString[] = "WHERE from_name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
            $queryString[] = "OR from_email LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
            $queryString[] = "OR to_name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
            $queryString[] = "OR to_email LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sort_data = array(
            'code',
            'from_name',
            'from_email',
            'to_name',
            'to_email',
            'theme',
            'amount',
            'status',
            'date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY v.date_added";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $queryString[] = " DESC";
        } else {
            $queryString[] = " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] != -1) {
                $queryString[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }
        }

        $query = $this->db->query(implode(' ', $queryString));

        return $query->rows;
    }

    public function sendVoucher($voucher_id)
    {
        $voucher_info = $this->getVoucher($voucher_id);

        if ($voucher_info) {
            if ($voucher_info['order_id']) {
                $order_id = $voucher_info['order_id'];
            } else {
                $order_id = 0;
            }

            $this->load->model('sale/order');

            $order_info = $this->model_sale_order->getOrder($order_id);

            // If voucher belongs to an order
            if ($order_info) {
                $this->load->model('localisation/language');

                $language = new Language($order_info['language_directory']);
                $language->load($order_info['language_filename']);
                $language->load('mail/voucher');

                // HTML Mail
                $template = new Template();

                $template->data['title'] = sprintf($language->get('text_subject'), $voucher_info['from_name']);

                $template->data['text_greeting'] = sprintf(
                    $language->get('text_greeting'),
                    $this->currency->format(
                        $voucher_info['amount'],
                        $order_info['currency_code'],
                        $order_info['currency_value'])
                );
                $template->data['text_from'] = sprintf($language->get('text_from'), $voucher_info['from_name']);
                $template->data['text_message'] = $language->get('text_message');
                $template->data['text_redeem'] = sprintf($language->get('text_redeem'), $voucher_info['code']);
                $template->data['text_footer'] = $language->get('text_footer');

                $this->load->model('sale/voucher_theme');

                $voucher_theme_info = $this->model_sale_voucher_theme->getVoucherTheme(
                    $voucher_info['voucher_theme_id']
                );

                if ($voucher_info && \Filesystem::isExists('image/' . $voucher_theme_info['image'])) {
                    $template->data['image'] = \Filesystem::getUrl('image/' . $voucher_theme_info['image']);
                } else {
                    $template->data['image'] = '';
                }

                $template->data['store_name'] = $order_info['store_name'];
                $template->data['store_url'] = $order_info['store_url'];
                $template->data['message'] = nl2br($voucher_info['message']);

                $mail = new Mail();
                $mail->protocol = $this->config->get('config_mail_protocol');
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->hostname = $this->config->get('config_smtp_host');
                $mail->username = $this->config->get('config_smtp_username');
                $mail->password = $this->config->get('config_smtp_password');
                $mail->port = $this->config->get('config_smtp_port');
                $mail->timeout = $this->config->get('config_smtp_timeout');
                $mail->setReplyTo(
                    $this->config->get('config_mail_reply_to'),
                    $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                    $this->config->get('config_email')
                );
                $mail->setTo($voucher_info['to_email']);
                $mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
                $mail->setSender($order_info['store_name']);
                $mail->setSubject(
                    html_entity_decode(
                        sprintf($language->get('text_subject'), $voucher_info['from_name']), ENT_QUOTES, 'UTF-8'
                    )
                );
                if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
                    include_once(DIR_SYSTEM . 'library/custom_email_templates.php');

                    $cet = new CustomEmailTemplates($this->registry);

                    $cet_result = $cet->getEmailTemplate(array(
                        'type' => 'admin',
                        'class' => 'modelcheckoutvoucher',
                        'function' => __FUNCTION__,
                        'vars' => get_defined_vars()
                    ));

                    if ($cet_result) {
                        if ($cet_result['subject']) {
                            $mail->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));
                        }

                        if ($cet_result['html']) {
                            $mail->setNewHtml($cet_result['html']);
                        }

                        if ($cet_result['text']) {
                            $mail->setNewText($cet_result['text']);
                        }

                        if ($cet_result['bcc_html']) {
                            $mail->setBccHtml($cet_result['bcc_html']);
                        }

                        if (
                            (isset($this->request->post['attach_invoicepdf']) &&
                                $this->request->post['attach_invoicepdf'] == 1) || $cet_result['invoice'] == 1
                        ) {
                            $path_to_invoice_pdf = $cet->invoice($order_info, $cet_result['history_id']);

                            $mail->addAttachment($path_to_invoice_pdf);
                        }

                        if (isset($this->request->post['fattachments'])) {
                            if ($this->request->post['fattachments']) {
                                foreach ($this->request->post['fattachments'] as $attachment) {
                                    foreach ($attachment as $file) {
                                        $mail->addAttachment($file);
                                    }
                                }
                            }
                        }

                        $mail->setBccEmails($cet_result['bcc_emails']);
                    }
                }
                $mail->setHtml($template->fetch('mail/voucher.tpl'));
                $mail->send();
                if ($this->config->get('custom_email_templates_status')) {
                    $mail->sendBccEmails();
                }

                // If voucher does not belong to an order
            } else {
                $this->language->load('mail/voucher');

                $template = new Template();

                $template->data['title'] = sprintf($this->language->get('text_subject'), $voucher_info['from_name']);

                $template->data['text_greeting'] = sprintf(
                    $this->language->get('text_greeting'),
                    $this->currency->format(
                        $voucher_info['amount'],
                        $order_info['currency_code'],
                        $order_info['currency_value']
                    )
                );
                $template->data['text_from'] = sprintf($this->language->get('text_from'), $voucher_info['from_name']);
                $template->data['text_message'] = $this->language->get('text_message');
                $template->data['text_redeem'] = sprintf($this->language->get('text_redeem'), $voucher_info['code']);
                $template->data['text_footer'] = $this->language->get('text_footer');

                $this->load->model('sale/voucher_theme');

                $voucher_theme_info = $this->model_sale_voucher_theme->getVoucherTheme(
                    $voucher_info['voucher_theme_id']
                );

                if ($voucher_info && \Filesystem::isExists('image/' . $voucher_theme_info['image'])) {
                    $template->data['image'] = \Filesystem::getUrl('image/' . $voucher_theme_info['image']);
                } else {
                    $template->data['image'] = '';
                }

                $template->data['store_name'] = $this->config->get('config_name');
                $template->data['store_url'] = HTTP_CATALOG;
                $template->data['message'] = nl2br($voucher_info['message']);

                $mail = new Mail();
                $mail->protocol = $this->config->get('config_mail_protocol');
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->hostname = $this->config->get('config_smtp_host');
                $mail->username = $this->config->get('config_smtp_username');
                $mail->password = $this->config->get('config_smtp_password');
                $mail->port = $this->config->get('config_smtp_port');
                $mail->timeout = $this->config->get('config_smtp_timeout');
                $mail->setReplyTo(
                    $this->config->get('config_mail_reply_to'),
                    $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                    $this->config->get('config_email')
                );
                $mail->setTo($voucher_info['to_email']);
                $mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
                $mail->setSender($this->config->get('config_name') ? $this->config->get('config_name')[$this->config->get('config_language')] : 'ExpandCart');
                $mail->setSubject(
                    html_entity_decode(
                        sprintf($this->language->get('text_subject'),
                            $voucher_info['from_name']),
                        ENT_QUOTES,
                        'UTF-8'
                    )
                );
                if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
                    include_once(DIR_SYSTEM . 'library/custom_email_templates.php');

                    $cet = new CustomEmailTemplates($this->registry);

                    $cet_result = $cet->getEmailTemplate(array(
                        'type' => 'admin',
                        'class' => 'modelcheckoutvoucher',
                        'function' => __FUNCTION__,
                        'vars' => get_defined_vars()
                    ));

                    if ($cet_result) {
                        if ($cet_result['subject']) {
                            $mail->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));
                        }

                        if ($cet_result['html']) {
                            $mail->setNewHtml($cet_result['html']);
                        }

                        if ($cet_result['text']) {
                            $mail->setNewText($cet_result['text']);
                        }

                        if ($cet_result['bcc_html']) {
                            $mail->setBccHtml($cet_result['bcc_html']);
                        }

                        if (
                            (isset($this->request->post['attach_invoicepdf']) &&
                                $this->request->post['attach_invoicepdf'] == 1) ||
                            $cet_result['invoice'] == 1
                        ) {
                            $path_to_invoice_pdf = $cet->invoice($order_info, $cet_result['history_id']);

                            $mail->addAttachment($path_to_invoice_pdf);
                        }

                        if (isset($this->request->post['fattachments'])) {
                            if ($this->request->post['fattachments']) {
                                foreach ($this->request->post['fattachments'] as $attachment) {
                                    foreach ($attachment as $file) {
                                        $mail->addAttachment($file);
                                    }
                                }
                            }
                        }

                        $mail->setBccEmails($cet_result['bcc_emails']);
                    }
                }
                $mail->setHtml($template->fetch('mail/voucher.tpl'));
                $mail->send();
                if ($this->config->get('custom_email_templates_status')) {
                    $mail->sendBccEmails();
                }
            }
        }
    }

    public function getTotalVouchers()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "voucher");

        return $query->row['total'];
    }

    public function getTotalVouchersByVoucherThemeId($voucher_theme_id)
    {
        $queryString = [];
        $queryString[] = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . 'voucher';
        $queryString[] = 'WHERE voucher_theme_id = "' . (int)$voucher_theme_id . '"';

        $query = $this->db->query(implode(' ', $queryString));

        return $query->row['total'];
    }

    public function getVoucherHistories($voucher_id)
    {
        $queryString = [];

        $fields = 'vh.order_id, CONCAT(o.firstname, " ", o.lastname) AS customer, vh.amount, vh.date_added';

        $queryString[] = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . 'voucher_history vh';
        $queryString[] = 'LEFT JOIN `' . DB_PREFIX . 'order` o';
        $queryString[] = 'ON (vh.order_id = o.order_id)';
        $queryString[] = 'WHERE vh.voucher_id = "' . (int)$voucher_id . '"';

        $query = $this->db->query(implode(' ', $queryString));

        return $query->rows;
    }

    public function getTotalVoucherHistories($voucher_id)
    {
        $queryString = [];

        $queryString[] = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . 'voucher_history';
        $queryString[] = 'WHERE voucher_id = "' . (int)$voucher_id . '"';

        $query = $this->db->query(implode(' ', $queryString));

        return $query->row['total'];
    }

    public function dtHandler($data)
    {
        $queryString = $subQuery = $fields = [];

        $subQuery[] = 'SELECT vtd.name FROM ' . DB_PREFIX . 'voucher_theme_description vtd';
        $subQuery[] = 'WHERE vtd.voucher_theme_id = v.voucher_theme_id';
        $subQuery[] = 'AND vtd.language_id = "' . (int)$this->config->get('config_language_id') . '"';

        $fields[] = 'v.voucher_id';
        $fields[] = 'v.code';
        $fields[] = 'v.from_name';
        $fields[] = 'v.from_email';
        $fields[] = 'v.to_name';
        $fields[] = 'v.to_email';
        $fields[] = '(%s) AS theme';
        $fields[] = 'v.amount';
        $fields[] = 'v.status';
        $fields[] = 'v.date_added';

        $fields = sprintf(implode(', ', $fields), implode(' ', $subQuery));

        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "voucher as v";

        if (!empty($data['filter_name'])) {
            $queryString[] = "WHERE from_name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
            $queryString[] = "OR from_email LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
            $queryString[] = "OR to_name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
            $queryString[] = "OR to_email LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        $total = $this->db->query(str_replace($fields, 'COUNT(*) as dc', implode(' ', $queryString)))->row['dc'];

        $sort_data = array(
            'code',
            'from_name',
            'from_email',
            'to_name',
            'to_email',
            'theme',
            'amount',
            'status',
            'date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY v.date_added";
        }

        if (isset($data['order']) && ($data['order'] == 'desc')) {
            $queryString[] = " DESC";
        } else {
            $queryString[] = " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] != -1) {
                $queryString[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }
        }

        $queryData = $this->db->query(implode(' ', $queryString));

        $data = array(
            'data' => $queryData->rows,
            'total' => $total,
            'totalFiltered' => $queryData->num_rows
        );

        return $data;
    }
}
