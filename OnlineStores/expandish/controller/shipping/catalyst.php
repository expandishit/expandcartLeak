<?php

class ControllerShippingCatalyst extends Controller
{
    /**
     * This endpoint will be used by catalyst to update order status from their side
     */
    public function updateOrderStatus()
    {
        $result = false;
        $request = json_decode(file_get_contents('php://input'), true);
        $orderId = $request['order_id'];
        $orderStatus = $request['order_status'];
        $message = 'Something went wrong';
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $message = 'Request method should be POST';
        } elseif (empty($orderId)) {
            $message = 'Order id (order number) is required';
        } elseif (empty($orderStatus)) {
            $message = 'Order status is required';
        } elseif (!is_numeric($orderStatus)) {
            $message = 'Order status must be number';
        } else {
            $this->load->model('shipping/catalyst');
            $result = $this->model_shipping_catalyst->updateOrderStatus($orderId, $orderStatus);
            if (!$result) {
                $message = 'Order does not exist';
            } else {
                $message = 'Order status has been updated successfully';
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['success' => $result, 'message' => $message]));
    }
}