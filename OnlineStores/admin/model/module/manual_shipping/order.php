<?php

class ModelModuleManualShippingOrder extends Model
{
    /**
     * @var string $table
     */
    protected $table = 'order';

    /**
     * Update order manual shipping gateway id.
     *
     * @param int $orderId
     * @param int $gatewayId
     *
     * @return bool
     */
    public function updateOrderManualGateway(int $orderId, int $gatewayId, $isBundled = false) : bool
    {
        $manualShippingApp = false;
        if (\Extension::isInstalled('manual_shipping')) {
            $options = $this->config->get('manual_shipping');
            if ($options['status'] == 1) {
                $manualShippingApp = 1;
            }
        }

        try {
            if ($manualShippingApp) {
                $this->db->query(vsprintf('UPDATE `%s` SET shipping_gateway_id = %d , manual_shipping_gateway = %d
                    WHERE order_id = %d', [
                    $this->table, $gatewayId, !$isBundled, $orderId
                ]));
            } else {
                $this->db->query(vsprintf('UPDATE `%s` SET shipping_gateway_id = %d
                    WHERE order_id = %d', [
                    $this->table, $gatewayId, $orderId
                ]));
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update order manual shipping gateway id.
     *
     * @param int $orderId
     * @param int $gatewayId
     *
     * @return bool
     */
    public function updateOrderBundledGateway(int $orderId, string $title, string $code) : bool
    {
        try {
            $this->db->query(vsprintf('UPDATE `%s` SET shipping_method = "%s",
                shipping_code = "%s",
                shipping_gateway_id = 0 WHERE order_id = %d', [
                $this->table, $title, $code, $orderId
            ]));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get manual shipping gateway id by order id.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function getManualShippingGatewayId(int $id)
    {
        $manualShippingApp = false;
        if (\Extension::isInstalled('manual_shipping')) {
            $options = $this->config->get('manual_shipping');
            if ($options['status'] == 1) {
                $manualShippingApp = 1;
            }
        }

        if ($manualShippingApp) {
            $data = $this->db->query(sprintf('SELECT * FROM `%s`
            WHERE order_id = %d AND manual_shipping_gateway = 1', $this->table, $id));
        } else {
            $data = $this->db->query(sprintf('SELECT * FROM `%s`
            WHERE order_id = %d', $this->table, $id));
        }

        if ($data->num_rows > 0) {
            return $data->row['shipping_gateway_id'];
        }

        return false;
    }

    /**
     * Get orders by manual shipping gateway id.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function getOrdersByManualShippingGatewayId(int $id)
    {
        $data = $this->db->query(sprintf('SELECT * FROM `%s` WHERE shipping_gateway_id = %d', $this->table, $id));

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Detach a manual shipping gateway from all orders.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function nullOrderManualShippingGateways(int $gatewayId) : bool
    {
        try {
            $this->db->query(vsprintf(
                'UPDATE `%s` SET shipping_gateway_id = NULL WHERE shipping_gateway_id = %d AND manual_shipping_gateway = 1', 
                [$this->table, $gatewayId]
            ));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
