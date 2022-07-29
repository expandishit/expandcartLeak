<?php
// Requires Constants Include Before Usage

function getResponse_AddContact($name, $email, $ipAddress, $fields, $tagNames) {
    $tags = array();

    foreach($tagNames as $tagName) {
        $tags[] = array("tagId" => getResponse_GetTagIdByName($tagName));
    }

    $customFieldValues = array();

    foreach ($fields as $fieldName => $fieldVal) {
        $customFieldValues[] = array("customFieldId" => getResponse_GetFieldIdByName($fieldName), "value" => array($fieldVal));
    }

    $contactData = array(
        "name" => $name,
        "email" => $email,
        "dayOfCycle" => "0",
        "campaign" => array("campaignId" => GETRESPONSE_LIST_ID),
        "tags" => $tags,
        //"scoring" => 1,
        //"ipAddress" => $ipAddress,
        "customFieldValues" => $customFieldValues
    );

    $addContact = '';

    $addContact = CallGetResponseAPI(  '',
        '',
        "POST",
        "https://api.getresponse.com/v3/contacts",
        $contactData,
        GETRESPONSE_API_TOKEN);

    return $addContact;
}

function getResponse_TagContact($email, $tagNames) {
    $contactId = getResponse_GetContactIdByEmail($email);

    $tags = array();

    foreach($tagNames as $tagName) {
        $tags[] = array("tagId" => getResponse_GetTagIdByName($tagName));
    }

    $tagsData = array("tags" => $tags);

    $tagContactByEmail = '';

    if ($contactId != '') {
        $tagContactByEmail = CallGetResponseAPI( '',
            '',
            "POST",
            "https://api.getresponse.com/v3/contacts/$contactId/tags",
            $tagsData,
            GETRESPONSE_API_TOKEN);
    }

    return $tagContactByEmail;
}

function getResponse_UpdateContactCustomField($email, $fields) {
    $contactId = getResponse_GetContactIdByEmail($email);

    $customFieldValues = array();

    foreach ($fields as $fieldName => $fieldVal) {
        $customFieldValues[] = array("customFieldId" => getResponse_GetFieldIdByName($fieldName), "value" => array($fieldVal));
    }

    $customFieldData = array("customFieldValues" => $customFieldValues);

    $updateContactCustomFieldByEmail = '';

    if ($contactId != '') {
        $updateContactCustomFieldByEmail = CallGetResponseAPI( '',
            '',
            "POST",
            "https://api.getresponse.com/v3/contacts/$contactId/custom-fields",
            $customFieldData,
            GETRESPONSE_API_TOKEN);
    }

    return $updateContactCustomFieldByEmail;
}

function getResponse_GetContactIdByEmail($email) {
    $getContactByEmail = CallGetResponseAPI(   '',
        '',
        "GET",
        "https://api.getresponse.com/v3/contacts?query[email]=$email",
        false,
        GETRESPONSE_API_TOKEN);

    $contactObjArray = json_decode($getContactByEmail, true);
    $contactId = '';

    if (sizeof($contactObjArray) > 0) {
        $contactId = $contactObjArray[0]['contactId'];
    }

    return $contactId;
}

function getResponse_GetFieldIdByName($fieldName) {
    $fieldLookup = array(
        "birthdate" => "hkqLn",
        "city" => "hkqOH",
        "comment" => "hkq2P",
        "company" => "hkqtN",
        "country" => "hkqfm",
        "customer_at_date" => "h1kcV",
        "fax" => "hkq3K",
        "gender" => "hkqCJ",
        "home_phone" => "hkq9R",
        "http_referer" => "hkqJ3",
        "language" => "h1kFn",
        "last_login_date" => "h1k7H",
        "mobile_phone" => "hkqIZ",
        "phone" => "hkqSv",
        "postal_code" => "hkqFX",
        "ref" => "hkqxr",
        "signup_date" => "h1kCq",
        "state" => "hkquq",
        "store_code" => "h1ObK",
        "store_name" => "h1kGm",
        "street" => "hkqXa",
        "subscription_plan" => "h1OsP",
        "subscription_state" => "hhmo3",
        "trial_activation_date" => "h1OdN",
        "trial_extended_date" => "h1k9X",
        "trial_reactivation_date" => "h1kwa",
        "url" => "hkqDV",
        "whmcs_client_id" => "h1OH9",
        "work_phone" => "hkqw9",
        "expand_country" => "hH3wK",
        "payment_term" => "hbLh4"
    );

    return $fieldLookup[$fieldName];
}

function getResponse_GetTagIdByName($tagName) {
    $tagLookup = array(
        "trial_activated" => "KDPK",
        "extended_the_trial_period" => "KDuN",
        "purchased_without_a_trial" => "KDkP",
        "activated_a_paid_plan" => "KDGV",
        "added_a_product" => "KDX3",
        "opened_password_forgot_page" => "KD7r",
        "opened_backend_login_page" => "KDIv",
        "opened_password_reset_page" => "KDgF",
        "received_an_order" => "KDl4",
        "received_an_order_in_trial_period" => "KD30",
        "logged_in_to_store_backend" => "KDm5",
        "invoice_paid" => "KJF5",
        "invoice_created" => "KJGb",
        "trial_reactivated" => "KJC7"
    );

    return $tagLookup[$tagName];
}

function CallGetResponseAPI($username, $password, $method, $url, $data = false, $api_token = '') {
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case "DELETE":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    if ($username != '') {
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    if ($api_token == '') {
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    } else {
        $headers = array();
        $headers[] = "X-Auth-Token: $api_token";
        $headers[] = 'Content-Type: application/json';
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }

    $result = curl_exec($curl);

    if ($result === false) {
        throw new Exception(curl_error($curl), curl_errno($curl));
    }

    curl_close($curl);

    return $result;
}

?>