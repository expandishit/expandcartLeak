<?php


	/* BELOW ARE LIST OF PARAMETERS THAT WILL BE RECEIVED BY MERCHANT FROM PAYMENT GATEWAY */
	/*Variable Declaration*/
	//=========================================================================================
	$ResErrorText = $_REQUEST['ErrorText']; 	  	//Error Text/message
	
	$ResErrorNo   = $_REQUEST['Error'] ? $_REQUEST['Error'] : null; 
	
	//Below Terminal resource Key is used to decrypt the response sent from Payment Gateway.
	//$terminalResKey="9064406025729064";
	
	/* Merchant (ME) checks, if error number is NOT present,then go for Encryption using required parameters */

	/* NOTE - MERCHANT MUST LOG THE RESPONSE RECEIVED IN LOGS AS PER BEST PRACTICE */	

	$protocol = 'http';

	if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
	
		$protocol = 'https';
	
	}

	// // dd($_REQUEST, 'iam whole came back request here' );
	// echo "<pre>";

	// print_r($_REQUEST );

	// echo "</pre>";
	
	// die();

	if( $ResErrorText == null && $ResErrorNo == null)
	{
		$ResPaymentId = $_REQUEST['paymentid'];		//Payment Id
		$ResTrackID   = $_REQUEST['trackid'];       	//Merchant Track ID
		$ResResult    =  $_REQUEST['result'];          //Transaction Result
		$ResPosdate   = $_REQUEST['postdate'];        //Postdate
		$ResTranId    = $_REQUEST['tranid'];           //Transaction ID
		$ResAuth      = $_REQUEST['auth'];               //Auth Code		
		$ResAVR       = $_REQUEST['avr'];                 //TRANSACTION avr					
		$ResRef       = $_REQUEST['ref'];                 //Reference Number also called Seq Number
		$ResAmount    = $_REQUEST['amt'];              //Transaction Amount
		/*IMPORTANT NOTE - MERCHANT SHOULD UPDATE 
					TRANACTION PAYMENT STATUS IN MERCHANT DATABASE AT THIS POSITION 
					AND THEN REDIRECT CUSTOMER ON RESULT PAGE*/
		$ResTranData= $_REQUEST['trandata'];
		
		if($ResTranData == null)
		{
			$ResErrorNo = 1;
		}
		
		// /expandcartdev/OnlineStores ATH 
		header('location:' . $protocol . '://' . $_SERVER['HTTP_HOST'] . '/index.php?route=payment/knet/success&ErrorText=' .$ResErrorText.'&paymentid='.$ResPaymentId.'&trackid='.$ResTrackID.'&Error='.$ResErrorNo.'&trandata='.$ResTranData);
		
		// header('location:' . HTTP_SERVER . 'index.php?route=payment/knet/success&ErrorText=' .$ResErrorText.'&paymentid='.$ResPaymentId.'&trackid='.$ResTrackID.'&Error='.$ResErrorNo.'&trandata='.$ResTranData);
		
		exit();
	}
	else{
	
		header('location:' . $protocol . '://' . $_SERVER['HTTP_HOST'] . '/index.php?route=payment/knet/error&ErrorText=' .$ResErrorText);
	
		// header('location:' . $protocol . '://' . $_SERVER['HTTP_HOST'] . '/expandcartdev/OnlineStores/index.php?route=payment/knet/error&ErrorText=' .$ResErrorText);
	
		// header('location:' . HTTP_SERVER . 'index.php?route=payment/knet/error&ErrorText=' .$ResErrorText);
	
		exit();
	
	}

?>


