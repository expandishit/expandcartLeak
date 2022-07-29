<?php

/*
|--------------------------------------------------------------------------
| Apple Pay with Stripe
|--------------------------------------------------------------------------
|
| This script is used to verify store domain with stripe to enable Apple pay.
| It Reads the content of "apple-developer-merchantid-domain-association" file
|
*/
require_once('../../Config/master.config.php');
if (file_exists(DIR_ONLINESTORES . 'ecdata/stores/' . STORECODE . '/.well-known/apple-developer-merchantid-domain-association') == false) {
    echo 'not found';exit;
}

echo file_get_contents(DIR_ONLINESTORES . 'ecdata/stores/' . STORECODE . '/.well-known/apple-developer-merchantid-domain-association');
