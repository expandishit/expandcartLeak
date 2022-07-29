<?php
	require					"class.PTN_API_v2.php";
	$api					= new PTN_API_v2("demo", 2824);
	$api->ENCRYPT_RESPONSE	= false;
	$api->PUBLIC_KEY		= '';
	$api->PRIVATE_KEY		= '';
	$api->USERNAME			= '';
	$api->PASSWORD			= '';
	$api->IV				= ''; 	// Only enter if using OpenSSL instead of provided AES CTR.
									// Any random 16 character string. Preferred to be unique on each call.
#	$api->FUNCTION			= 'get_OEMList';
#	$api->FUNCTION			= 'get_BrandList';
#	$api->FUNCTION			= 'get_ProductList';
#	$api->FUNCTION			= 'get_SalesTransaction_ByDateRange';
#	$api->FUNCTION			= 'get_FinancialTransaction_ByDateRange';
#	$api->FUNCTION			= 'get_Vouchers';
#	$api->FUNCTION			= 'get_ProductList';
#	$api->FUNCTION			= 'get_ProductAvailability';
#	$api->PARAMETERS		= [ // As defined in documentation
#		''					=> ''
#	];

	$res					= $api->callAPI(true);
	var_dump($res);