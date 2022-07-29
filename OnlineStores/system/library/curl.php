<?php
class Curl {
	/**
	*
	*/
    public function call($data = []) {

        $url     = $data['url'];
        $fields  = $data['url'];
        $headers = $data['headers']; //Array
        $post    = $data['is_post'] ? true : false; //bool

        $curl     = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 1000);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, $post);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $result    = curl_exec($curl);
        $error     = curl_error($curl);
        $resultArr = json_decode($result,true);

        return ['responds' => $resultArr, 'error' => $error];
    }
}
?>