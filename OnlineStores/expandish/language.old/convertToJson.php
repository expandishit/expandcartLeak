<?php
exit;
error_reporting(E_ALL);
ini_set("display_errors", 1);

foreach (glob("*/*/*.php") as $filename)
{
    $_ = array();
    include $filename;

    $fileInfo = explode("/", $filename);
    $language = substr($fileInfo[0], 0, 2);

    $language = $language == 'tu' ? 'tr' : $language;
    $language = $language == 'ge' ? 'de' : $language;

    //$newFileName = rtrim($fileInfo[2],".php") . "." . $language . ".json";
    $newFileName = substr_replace($fileInfo[2], "", -4) . "." . $language . ".json";
    //var_dump(explode("/", $filename));
    //var_dump($newFileName);
    $json_data = json_encode($_, JSON_UNESCAPED_UNICODE);

    if(!file_exists("../language")) {
        mkdir("../language");
    }
    if(!file_exists("../language/" . $fileInfo[1])) {
        mkdir("../language/" . $fileInfo[1]);
    }

    $newFilePath = "../language/" . $fileInfo[1] . "/" . $newFileName;
    file_put_contents($newFilePath, $json_data);
    echo $newFilePath . "<br/>";
}


$_ = array();
include "turkish/turkish.php";
$json_data = json_encode($_, JSON_UNESCAPED_UNICODE);
$newFilePath = "../language/global.tr.json";
file_put_contents($newFilePath, $json_data);
echo $newFilePath . "<br/>";

$_ = array();
include "german/german.php";
$json_data = json_encode($_, JSON_UNESCAPED_UNICODE);
$newFilePath = "../language/global.de.json";
file_put_contents($newFilePath, $json_data);
echo $newFilePath . "<br/>";

$_ = array();
include "arabic/arabic.php";
$json_data = json_encode($_, JSON_UNESCAPED_UNICODE);
$newFilePath = "../language/global.ar.json";
file_put_contents($newFilePath, $json_data);
echo $newFilePath . "<br/>";

$_ = array();
include "english/english.php";
$json_data = json_encode($_, JSON_UNESCAPED_UNICODE);
$newFilePath = "../language/global.en.json";
file_put_contents($newFilePath, $json_data);
echo $newFilePath . "<br/>";

$_ = array();
include "french/french.php";
$json_data = json_encode($_, JSON_UNESCAPED_UNICODE);
$newFilePath = "../language/global.fr.json";
file_put_contents($newFilePath, $json_data);
echo $newFilePath . "<br/>";

echo "Finished!";