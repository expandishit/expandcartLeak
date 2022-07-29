<?php

class ControllerCommonWebhooks extends Controller {

    public function ebutlerOrderSyncWebhook() {

        $data = (array)json_decode(file_get_contents('php://input'));
        $status_id = $this->config->get('ebutler_order_' . $data['status']);
        $admin_dir = str_replace( 'system/', 'admin/', DIR_SYSTEM );
        require $admin_dir . "controller/module/ebutler.php";
        $ebutler_controller = new ControllerModuleEButler($this->registry);
        $ebutler_controller->updateEbutlerOrderStatus($status_id, $data['_id']);
    }
}