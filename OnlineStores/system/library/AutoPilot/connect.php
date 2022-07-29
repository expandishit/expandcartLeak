<?php

/*

Example Usage:

var_dump(
    autopilot_AddContact(
        "Amr Shawqy",
        "amrodigital@gmail.com",
        "+201288727776",
        array(
            "string--Subscription--Plan" => "Ultimate Plan",
            "boolean--Has--Added--Products" => true,
            "boolean--Has--Received--Orders" => true,
            "string--Subscription--State" => "",
            "date--Trial--Activation--Date" => date('c'),
        )
    )
);

*/

/*

Field Names:

date--Trial--Activation--Date
date--Trial--Extend--Date
date--Trial--Reactivation--Date
date--Customer--At--Date


string--Subscription--Plan
string--Subscription--State
string--Payment--Term
string--Store--Code
string--WHMCS--Client--ID
string--Store--Name
string--First--Name
string--Email
string--Phone
string--Preferred--Language
string--Country
string--Previous--Website
string--Product--Source
string--Registered--Company
string--Selling--Channel


boolean--Has--Added--Products
boolean--Has--Received--Orders
boolean--Has--Received--Test--Orders
boolean--Has--No--Trial
boolean--Has--Invoices--Created
boolean--Has--Invoices--Paid

*/

function autopilot_UpdateContactCustomFields($email, $fields, $phone=null) {
    return;
    $contactDetails = array(
        "Email" => $email,
        "custom" => $fields
    );
    if($phone) {
        $contactDetails['Phone'] = $phone;
    }
    $contactData = array(
        "contact" => $contactDetails
    );

    return CallAutoPilotAPI( 'POST', 'https://api2.autopilothq.com/v1/contact', $contactData );
}

function autopilot_UpdateContactEmail($email, $new_email) {
    return;
    $contactData = array(
        "contact" => array(
            "Email" => $email,
            "_NewEmail" => $new_email
        )
    );

    return CallAutoPilotAPI( 'POST', 'https://api2.autopilothq.com/v1/contact', $contactData );
}

function autopilot_AddContact($name, $email, $phone, $fields) {
    return;
    $contactData = array(
        "contact" => array(
            "FirstName" => $name,
            "Email" => $email,
            "Phone" => $phone,
            "LeadSource" => "ExpandCart API",
            "_autopilot_list" => "contactlist_" . AUTOPILOT_MAIN_LIST_ID,
            "custom" => $fields
        )
    );

    return CallAutoPilotAPI( 'POST', 'https://api2.autopilothq.com/v1/contact', $contactData );
}

function CallAutoPilotAPI($method, $url, $data = false) {
    return;
    $api_token = AUTOPILOT_API_TOKEN;

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_HEADER, FALSE);

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, TRUE);

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

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "autopilotapikey: $api_token",
        "Content-Type: application/json"
    ));

    $result = curl_exec($curl);

    if ($result === false) {
        throw new Exception(curl_error($curl), curl_errno($curl));
    }

    curl_close($curl);

    return $result;
}

//Mail Jet Functions
function mailjet_AddContact($name, $email, $country, $language, $packageid, $phone, $storecode, $storename, $whmcsid) {
    $contactData = array(
        "IsExcludedFromCampaigns" => false,
        "Name" => $name,
        "Email" => $email
    );

    $contactID = 0;

    $contactResult = CallMailJETAPI( 'POST', 'https://api.mailjet.com/v3/REST/contact', $contactData );

    $contactID = json_decode($contactResult)->Data[0]->ID;

    if ($contactID === NULL)
        return;

    $contactProperties = array(
        "ContactID" => $contactID,
        "Data" => array(
            array(
                "Name" => "name",
                "Value" => $name
            ),
            array(
                "Name" => "country",
                "Value" => $country
            ),
            array(
                "Name" => "language",
                "Value" => $language
            ),
            array(
                "Name" => "packageid",
                "Value" => $packageid
            ),
            array(
                "Name" => "phone",
                "Value" => $phone
            ),
            array(
                "Name" => "storecode",
                "Value" => $storecode
            ),
            array(
                "Name" => "storename",
                "Value" => $storename
            ),
            array(
                "Name" => "whmcsid",
                "Value" => $whmcsid
            )
        )
    );

    $contactPropertiesResult = CallMailJETAPI( 'POST', 'https://api.mailjet.com/v3/REST/contactdata', $contactProperties );

    $listData = array(
        "ContactsLists" => array(
            array(
                "ListID" => 40018,
                "Action" => "addnoforce"
            )
        )
    );

    $listResult = CallMailJETAPI( 'POST', "https://api.mailjet.com/v3/REST/contact/$contactID/managecontactslists", $listData );
}

function CallMailJETAPI($method, $url, $data = false) {
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_USERPWD, MJ_APIKEY_PUBLIC . ':' . MJ_APIKEY_PRIVATE);

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, TRUE);

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

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json"
    ));

    $result = curl_exec($curl);

    if ($result === false) {
        throw new Exception(curl_error($curl), curl_errno($curl));
    }

    curl_close($curl);

    return $result;
}

?>