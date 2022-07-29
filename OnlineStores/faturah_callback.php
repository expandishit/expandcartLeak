<?php
function movePage($url){
    header ("Location: $url");
}

$params = array_merge($_GET, array("route" => "payment/faturah/callback"));
$new_query_string = http_build_query($params);

$new_url = str_replace("faturah_callback", "index", $_SERVER['SCRIPT_NAME']) . '?' . $new_query_string;

movePage($new_url);
?>