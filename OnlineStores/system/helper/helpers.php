<?php

if (!function_exists('array_only_keys')) {

    function array_only_keys(array $array, array $keys)
    {
        return array_intersect_key($array, array_flip($keys));
    }
}

function is_simple_valid_phone(string $phone = null)
{
    if (!$phone) return false;

    preg_match('/^[0-9\-\(\)\/\+\s]*$/', $phone, $matches);

    return !empty($matches);
}

function is_valid_email(string $email = null)
{
    if (!$email) return false;

    $isValid = true;
    $atIndex = strrpos($email, "@");
    if (is_bool($atIndex) && !$atIndex) {
        $isValid = false;
    } else {
        $domain = substr($email, $atIndex + 1);
        $local = substr($email, 0, $atIndex);
        $localLen = strlen($local);
        $domainLen = strlen($domain);
        if ($localLen < 1 || $localLen > 64) {
            // local part length exceeded
            $isValid = false;
        } else if ($domainLen < 1 || $domainLen > 255) {
            // domain part length exceeded
            $isValid = false;
        } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
            // local part starts or ends with '.'
            $isValid = false;
        } else if (preg_match('/\\.\\./', $local)) {
            // local part has two consecutive dots
            $isValid = false;
        } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
            // character not valid in domain part
            $isValid = false;
        } else if (preg_match('/\\.\\./', $domain)) {
            // domain part has two consecutive dots
            $isValid = false;
        } else if (!preg_match(
            '/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
            str_replace("\\\\", "", $local)
        )) {
            // character not valid in local part unless
            // local part is quoted
            if (!preg_match(
                '/^"(\\\\"|[^"])+"$/',
                str_replace("\\\\", "", $local)
            )) {
                $isValid = false;
            }
        }
        if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
            // domain not found in DNS
            $isValid = false;
        }
    }
    return $isValid;
}

function ecTargetLog($log){
    if (in_array(STORECODE, ['NRJRCY2271', 'LLNRG922', 'AAWARQ9679', 'QFGVEZ0629', 'VSGCDO6025', 'ESEGU044', 'BUWZUZ4989', 'CBGPX243', 'ORVSQ657', 'KQJGVB4732'], true)){
        ecLog('product_options.txt',$log);
    }
}

function designeditorLog($log){
    if (in_array(STORECODE, ['YJJTL542', 'QAZ123'], true)){
        ecLog('designeditor.txt',$log);
    }
}

function myFatoorahLog($log){
    if (in_array(STORECODE, ['AYBTUE0107', 'HCPXHZ9094', 'MGYBOV5855', 'CBGPX243'], true)){
        ecLog('myfatoorahlogfile.log',$log);
    }
}
function like4cardLog($log){
    if (in_array(STORECODE, ['QGVCIY3824'], true)){
        ecLog('like4cardlogfile.log',$log);
    }
}

function ottuLog($log){
    if (in_array(STORECODE, ['EIHFJX1459'], true)){
        ecLog('ottulogfile.log',$log);
    }
}


function ecLog($filename, $log){
    (new Log($filename))->write(json_encode($log));
}

