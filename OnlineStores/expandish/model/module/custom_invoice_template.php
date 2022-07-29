<?php 

class ModelModuleCustomInvoiceTemplate extends Model {
    public function getTemplate($template_id)
    {
        $results = $this->db->query("SELECT * FROM `".DB_PREFIX."cit` WHERE `template_id` = ". (int) $this->db->escape($template_id) )->rows;
        $template = [];
        foreach ($results as $result) {
            $template[] = $result['language_id'];
            $template[$result['language_id']] = $result['html'];
        }
        return $template;
    }

    public function getShortCodes()
    {     
        $orderInfo = [
            '{order.date_added}',
            '{order.time_added}',
            '{order.invoice_no}',
            '{order.invoice_no_barcode}',
            '{order.order_id}',
            '{order.payment_method}',
            '{order.firstname}',
            '{order.lastname}',
            '{order.email}',
            '{order.telephone}',
            '{order.fax}',
            '{order.payment_method}',
            '{order.payment_country}',
            '{order.payment_city}',
            '{order.payment_zone}',
            '{order.payment_address}',
            '{order.shipping_method}',
            '{order.shipping_country}',
            '{order.shipping_city}',
            '{order.shipping_zone}',
            '{order.shipping_address}',
            '{order.store_name}',
            '{order.store_url}',
            '{order.store_address}',
            '{order.store_email}',
            '{order.store_telephone}',
            '{order.store_fax}',
            '{order.store_logo}',
            '{order.comment}'
        ];

        $products = [
            '{product.product_id}',
            '{product.name}',
            '{product.model}',
            '{product.sku}',
            '{product.quantity}',
            '{product.price}',
            '{product.total}',
            '{product.barcode_image}',
            '{product.image}',
            '{product.option}',
            '{product.seller_country}',
            '{product.nickname}',
            '{product.seller_zone}',
            '{product.seller_address}'
        ];

        $totals = [
            '{totals.code}',
            '{totals.title}',
            '{totals.text}',
            '{totals.value}'
        ];

        $vouchers = [
            '{voucher.description}',
            '{voucher.amount}'
        ];

        $codes = [
            'order_info' => $orderInfo,
            'products' => $products,
            'totals' => $totals,
            'vouchers' => $vouchers
        ];

        if (\Extension::isInstalled('delivery_slot')) {
            $codes['delivery_slot'] = [
                '{ds.delivery_date}',
                '{ds.slot_description}',
                '{ds.day_name}'
            ];
        }

        return $codes;
    }
}