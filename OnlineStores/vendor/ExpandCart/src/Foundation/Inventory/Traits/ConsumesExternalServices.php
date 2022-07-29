<?php

namespace ExpandCart\Foundation\Inventory\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use ExpandCart\Foundation\Inventory\Exception\ClientException as InnevtoryHttpClientException;

trait ConsumesExternalServices
{
    protected function makeRequest($method, $requestUrl, $queryParams = [], $formParams = [], $headers = [], $isJsonRequest = false, $isMultipart = false)
    {
        $response = "";

        $client = new Client([
            'base_uri' => $this->baseUri,
            'verify' => $this->httpVerifySslCertificate
        ]);

        // here you can change query Parameters, from Parameters or header for every client
        if (method_exists($this, 'resolveAuthorization')) {
            $this->resolveAuthorization($queryParams, $formParams, $headers);
        }
        try {
            $response = $client->request($method, $requestUrl, [
                ($isJsonRequest ? 'json' : ($isMultipart ? 'multipart' : 'form_params')) => $formParams,
                'headers' => $headers,
                'query' => $queryParams,
                'debug' => false,
            ]);

            $response = $response->getBody()->getContents();
        } catch (RequestException | \Exception $e) {
            $msg = "";
            // Catch all 4XX errors 
            if ($e instanceof RequestException && $e->hasResponse())
                $msg = " the response given " . $e->getResponse()->getBody()->getContents();

            throw new InnevtoryHttpClientException('Unable to complete request: ' . $method . '/' . $this->baseUri . '/' . $requestUrl . $msg);
        }
        if (method_exists($this, 'decodeResponse')) {
            $response = $this->decodeResponse($response);
        }

        return $response;
    }



    //upload zoho images
    protected function uploadOnlineImage($url, $path)
    {

        $auth_token = "?authtoken=" . $this->getAuthParams()['authtoken'];
        //Initialise the cURL var
        $ch = curl_init();

        //set header
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type' => 'multipart/form-data']);

        //Get the response from cURL
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Set the Url
        curl_setopt($ch, CURLOPT_URL, $this->baseUri . $url . $auth_token);

        //Create a POST array with the file in it
        //If the function curl_file_create exists
        if (function_exists('curl_file_create')) {
            //Use the recommended way, creating a CURLFile object.
            $filePath = curl_file_create($path);
        } else {
            //Otherwise, do it the old way.
            //Get the canonicalized pathname of our file and prepend
            //the @ character.
            $filePath = '@' . $path;
            //Turn off SAFE UPLOAD so that it accepts files
            //starting with an @
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
        }

        //Setup our POST fields
        $postFields = array('image' => $filePath);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        // Execute the request
        $response = curl_exec($ch);
        return $response;
    }
}
