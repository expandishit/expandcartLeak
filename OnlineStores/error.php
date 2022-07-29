<?php
    $protocol = 'http';

    if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
        $protocol = 'https';
    }

	// Redirect to error page
	$currentUrl="//".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $urlArray = parse_url($currentUrl);

	$url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/index.php?route=error/payment&errmsg=" . (!empty($urlArray['query']) ? $urlArray['query'] : '');
	//echo 'REDIRECT=' . $url;
	header("Location: " . $url);
	exit();
?>