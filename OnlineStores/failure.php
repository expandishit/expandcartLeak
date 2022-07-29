<?php

$protocol = 'http';

if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
    $protocol = 'https';
}

header('location:' . $protocol . '://' . $_SERVER['HTTP_HOST'] . '/index.php?route=payment/my_fatoorah/callback&id=' . $_GET['id']);
?>