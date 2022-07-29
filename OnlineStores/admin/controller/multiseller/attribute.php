<?php

class ControllerMultisellerAttribute extends ControllerMultisellerBase
{
    public function getTableData()
    {
        $colMap = array(
            'id' => 'ma.attribute_id',
            'status' => '`ma.enabled`',
            'type' => 'attribute_type'
        );

        $sorts = array('name', 'type', 'sort_order', 'status');
        $filters = array_diff($sorts, array('status', 'sort_order', 'type'));

        $this->initializer([
            'msAttribute' => 'multiseller/attribute'
        ]);

        list($sortCol, $sortDir) = $this->getSortParams($sorts, $colMap);
        $filterParams = $this->getFilterParams($filters, $colMap);

        $results = $this->msAttribute->getAttributes(
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
            $status = $result['ma.enabled'] ? $this->language->get('ms_enabled') : $this->language->get('ms_disabled');
            $columns[] = array_merge(
                $result,
                array(
                    'checkbox' => "<input type='checkbox' name='selected[]' value='{$result['attribute_id']}' />",
                    'name' => $result['mad.name'],
                    'type' => $this->MsLoader->MsAttribute->getTypeText($result['ma.attribute_type']),
                    'sort_order' => $result['ma.sort_order'],
                    'status' => $status,
                )
            );
        }

        $this->response->setOutput(json_encode(array(
            'iTotalRecords' => $total,
            'iTotalDisplayRecords' => $total,
            'aaData' => $columns
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

        $this->data['token'] = $this->session->data['token'];

        $this->data['heading'] = $this->language->get('ms_attribute_heading');
        $this->document->setTitle($this->language->get('ms_attribute_heading'));

        $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
            array(
                'text' => $this->language->get('ms_menu_multiseller'),
                'href' => $this->url->link('module/multiseller', '', 'SSL'),
            ),
            array(
                'text' => $this->language->get('ms_attribute_breadcrumbs'),
                'href' => $this->url->link('multiseller/attribute', '', 'SSL'),
            )
        ));

        $this->template = 'multiseller/attribute/list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    public function create()
    {
        $this->load->model('localisation/language');
        $this->load->model('catalog/attribute_group');
        $this->load->model('tool/image');

        $this->data['attribute'] = FALSE;
        $this->data['attribute_groups'] = $this->model_catalog_attribute_group->getAttributeGroups();
        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        $this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

        $this->data['cancel'] = $this->url->link('multiseller/attribute', '', 'SSL');
        $this->data['token'] = $this->session->data['token'];
        $this->data['heading'] = $this->language->get('ms_attribute_create');
        $this->document->setTitle($this->language->get('ms_attribute_create'));

        $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
            array(
                'text' => $this->language->get('ms_menu_multiseller'),
                'href' => $this->url->link('module/multiseller', '', 'SSL'),
            ),
            array(
                'text' => $this->language->get('ms_attribute_breadcrumbs'),
                'href' => $this->url->link('multiseller/attribute', '', 'SSL'),
            )
        ));

        $this->data['attribute_types'] = [
            MsAttribute::TYPE_CHECKBOX => $this->language->get('ms_type_checkbox'),
            MsAttribute::TYPE_DATE => $this->language->get('ms_type_date'),
            MsAttribute::TYPE_DATETIME => $this->language->get('ms_type_datetime'),
            MsAttribute::TYPE_IMAGE => $this->language->get('ms_type_image'),
            MsAttribute::TYPE_RADIO => $this->language->get('ms_type_radio'),
            MsAttribute::TYPE_SELECT => $this->language->get('ms_type_select'),
            MsAttribute::TYPE_TEXT => $this->language->get('ms_type_text'),
            MsAttribute::TYPE_TEXTAREA => $this->language->get('ms_type_textarea'),
            MsAttribute::TYPE_TIME => $this->language->get('ms_type_time'),
        ];

        $this->template = 'multiseller/attribute/form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    public function update()
    {
        $this->load->model('localisation/language');
        $this->load->model('tool/image');
        $this->load->model('catalog/attribute_group');

        $attribute_id = $this->request->get['attribute_id'];

        $this->data['attribute'] = $this->MsLoader->MsAttribute->getAttribute($attribute_id);
        $this->data['attribute']['attribute_description'] = $this->MsLoader->MsAttribute->getAttributeDescriptions(
            $attribute_id
        );
        $this->data['attribute_groups'] = $this->model_catalog_attribute_group->getAttributeGroups();

        $attrTypes = [
            MsAttribute::TYPE_SELECT,
            MsAttribute::TYPE_RADIO,
            MsAttribute::TYPE_IMAGE,
            MsAttribute::TYPE_CHECKBOX
        ];

        if (
        in_array($this->data['attribute']['attribute_type'],
            $attrTypes
        )
        ) {
            $this->data['attribute']['attribute_values'] = $this->MsLoader->MsAttribute->getAttributeValues(
                $attribute_id
            );

            foreach ($this->data['attribute']['attribute_values'] as &$value) {
                $value['attribute_value_description'] = $this->MsLoader->MsAttribute->getAttributeValueDescriptions(
                    $value['attribute_value_id']
                );

                if (!empty($value['image'])) {
                    $value['thumb'] = $this->model_tool_image->resize($value['image'], 150, 150);
                } else {
                    $value['thumb'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
                }
            }
        }

        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        $this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

        $this->data['cancel'] = $this->url->link('multiseller/attribute', '', 'SSL');
        $this->data['token'] = $this->session->data['token'];
        $this->data['heading'] = $this->language->get('ms_attribute_edit');
        $this->document->setTitle($this->language->get('ms_attribute_edit'));

        $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
            array(
                'text' => $this->language->get('ms_menu_multiseller'),
                'href' => $this->url->link('module/multiseller', '', 'SSL'),
            ),
            array(
                'text' => $this->language->get('ms_attribute_breadcrumbs'),
                'href' => $this->url->link('multiseller/attribute', '', 'SSL'),
            )
        ));

        $this->data['attribute_types'] = [
            MsAttribute::TYPE_CHECKBOX => $this->language->get('ms_type_checkbox'),
            MsAttribute::TYPE_DATE => $this->language->get('ms_type_date'),
            MsAttribute::TYPE_DATETIME => $this->language->get('ms_type_datetime'),
            MsAttribute::TYPE_IMAGE => $this->language->get('ms_type_image'),
            MsAttribute::TYPE_RADIO => $this->language->get('ms_type_radio'),
            MsAttribute::TYPE_SELECT => $this->language->get('ms_type_select'),
            MsAttribute::TYPE_TEXT => $this->language->get('ms_type_text'),
            MsAttribute::TYPE_TEXTAREA => $this->language->get('ms_type_textarea'),
            MsAttribute::TYPE_TIME => $this->language->get('ms_type_time'),
        ];

        $this->template = 'multiseller/attribute/form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    public function delete()
    {
        if (isset($this->request->post['selected'])) {
            foreach ($this->request->post['selected'] as $attribute_id) {
                $this->MsLoader->MsAttribute->deleteAttribute($attribute_id);
            }

            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_deleted_successfully');
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = [];
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

    public function jxSubmitAttribute()
    {
        $json = array();
        $data = $this->request->post;
        unset($data['attribute_value'][0]);

        foreach ($data['attribute_description'] as $lid => $value) {
            if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 128)) {
                $json['errors']["attribute_description[$lid][name]"] = $this->language->get('ms_error_attribute_name');
            }
        }

        if (
            $data['attribute_type'] == MsAttribute::TYPE_SELECT ||
            $data['attribute_type'] == MsAttribute::TYPE_RADIO ||
            $data['attribute_type'] == MsAttribute::TYPE_CHECKBOX ||
            $data['attribute_type'] == MsAttribute::TYPE_IMAGE
        ) {
            if (empty($data['attribute_value'])) {
                $json['errors']['attribute_type'] = $this->language->get('ms_error_attribute_type');
            }
        } else if (
            $data['attribute_type'] != MsAttribute::TYPE_TEXT &&
            $data['attribute_type'] != MsAttribute::TYPE_TEXTAREA
        ) {
            unset($data['text_type']);
            unset($data['attribute_value']);
        }

        if (isset($data['attribute_value'])) {
            foreach ($data['attribute_value'] as $attribute_value_id => $attribute_value) {
                foreach ($attribute_value['attribute_value_description'] as $lid => $attribute_value_description) {
                    if (
                        (utf8_strlen($attribute_value_description['name']) < 1) ||
                        (utf8_strlen($attribute_value_description['name']) > 128)
                    ) {
                        $errorIndex = "attribute_value[$attribute_value_id][attribute_value_description][$lid][name]";
                        $json['errors']["$errorIndex"] = $this->language->get('ms_error_attribute_value_name');
                    }
                }
            }
        }

        if (empty($json['errors'])) {
            if (isset($data['attribute_id']) && !empty($data['attribute_id'])) {
                $attribute_id = $this->MsLoader->MsAttribute->updateAttribute($data['attribute_id'], $data);
                $this->session->data['success'] = $this->language->get('ms_success_attribute_updated');
            } else {
                $attribute_id = $this->MsLoader->MsAttribute->createAttribute($data);
                $this->session->data['success'] = $this->language->get('ms_success_attribute_created');
            }

            $json['redirect'] = str_replace('&amp;', '&', $this->url->link('multiseller/attribute', '', 'SSL'));
        }

        $this->response->setOutput(json_encode($json));
    }
}
