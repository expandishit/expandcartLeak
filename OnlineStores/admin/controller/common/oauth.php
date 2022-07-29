<?php


class ControllerCommonOauth extends Controller
{
    public function knawat()
    {
        $this->logData(['name'=>'oauth first step', 'request body' => $_GET]);
        //create custom client for api
        $this->load->model("api/clients");

        //get current knawat client
        $old_knawat_client = $this->model_api_clients->getClientByTarget('knawat');

        $clientId = $this->model_api_clients->generateClientId();
        $secretKey = $this->model_api_clients->generateSecretKey($clientId);

        if ($clientId && $secretKey)
            $id = $this->model_api_clients->storeCustomClient($clientId, $secretKey, 1, 'knawat');

        if ($id) {


            //install knawat in not exist
            $this->load->model('module/knawat');
            if (!$this->model_module_knawat->isInstalled()) {

                $this->load->model('setting/extension');
                $this->load->model('user/user_group');

                $this->model_setting_extension->install('module', 'knawat');

                if ($this->model_setting_extension->isTrial($this->request->get['extension']))
                    $this->model_setting_extension->removeTrial('knawat');

                $this->model_user_user_group->addPermission($this->user->getId(), 'access', 'module/knawat');
                $this->model_user_user_group->addPermission($this->user->getId(), 'modify', 'module/knawat');
                $this->model_module_knawat->install();
                $this->logData(['name'=>'knawat app installed on expandcart', 'request body' => []]);
            }

            //remove old client if exist
            if($old_knawat_client)
                $this->model_api_clients->deleteClient($old_knawat_client['id']);

            $this->logData(['name'=>'oauth success (first step)', 'request body' => ['code'=>$clientId]]);
            //redirect oauth with code
            header("Location: " . $_GET['redirect_uri'] . "?code=" . $clientId . "&state=" . $_GET['state']);
            exit();
        }

        header("Location: " . $_GET['redirect_uri']);
        exit();
    }


    public function logData($body)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://x-dev.tech/hojoj/api/knawat-hook",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "post",
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json',),
        ));

        $response = curl_exec($curl);

        return json_decode($response);
    }


}
