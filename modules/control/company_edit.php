<?php
/*###########################################################
# This program is distributed under the terms and 			#
# conditions of the GPL	and is free to use or modify		#
# 				                  							#
# company_edit.php											#
# Version 0.1.1	10:31 PM Thursday, 2 April 2009 			#
# Edited last by : Glen Vanderhel							#
###########################################################*/

if(isset($VAR['submit'])) {


	/* get pdf printing option */
	if($VAR['pdf_print'] == 1) {
		$html_print = 0;
		$pdf_print  = 1;
	} else {
		$html_print = 1;
		$pdf_print  = 0;
	}

$q = 'UPDATE '.PRFX.'SETUP SET ';

if($VAR['parts_password'] !='') {
	$q .= 'PARTS_PASSWORD		= '. $db->qstr( md5($VAR['parts_password'])).', ';	
}
/* Removes / from messages parsed to database */
$string3= $VAR['welcome'];
$string4=stripslashes($string3);
$string5= $VAR['inv_thank_you'];
$string6=stripslashes($string5);

if($VAR['ups_password'] != '') {
	$q .= 'UPS_PASSWORD		= '. $db->qstr( $VAR['ups_password']		) .', ';
}
		$q .= '
			HTML_PRINT 			= '. $db->qstr( $html_print          	) .',
			PDF_PRINT				= '. $db->qstr( $pdf_print           	) .',
			INVOICE_TAX 			= '. $db->qstr( $VAR['inv_tax']      	) .',
			INV_THANK_YOU 		= '. $db->qstr( $string6  	) .',
			WELCOME_NOTE			= '. $db->qstr( $string4      	).',
			PARTS_LO				= '. $db->qstr( $VAR['parts_lo']			).',
			SERVICE_CODE			= '. $db->qstr( $VAR['service_code']		).',
			PARTS_MARKUP			= '. $db->qstr( $VAR['parts_markup'] 	).',
			PARTS_LOGIN			= '. $db->qstr( $VAR['parts_login']		).',
			UPS_LOGIN 			= '. $db->qstr( $VAR['ups_login']			).',
			UPS_ACCESS_KEY		= '. $db->qstr( $VAR['ups_access_key']	);

	if(!$rs = $db->execute($q)) {
		force_page('core', 'error&error_msg=MySQL Error: '.$db->ErrorMsg().'&menu=1&type=database');
		exit;
	}

unset($q);

/* Removes / from messages parsed to database */
$string= $VAR['company_name'];
$string2=stripslashes($string);

	/* update company information */
	$q = 'UPDATE '.PRFX.'TABLE_COMPANY SET
		  	COMPANY_NAME			= '. $db->qstr( $string2          	).',
		  	COMPANY_ABN			= '. $db->qstr( $VAR['company_abn']	) .',
		  	COMPANY_ADDRESS 	= '. $db->qstr( $VAR['address']		) .',
			COMPANY_CITY 		= '. $db->qstr( $VAR['city']			) .',
			COMPANY_STATE		= '. $db->qstr( $VAR['state']			) .',
			COMPANY_ZIP 			= '. $db->qstr( $VAR['zip']				) .',
			COMPANY_COUNTRY		= '. $db->qstr( $VAR['country']).',
			COMPANY_PHONE		= '. $db->qstr( $VAR['phone']			) .',
			COMPANY_MOBILE		= '. $db->qstr( $VAR['mobile_phone']	) .', 
			COMPANY_TOLL_FREE	= '. $db->qstr( $VAR['toll_free']		) .',
			GMAPS_API_KEY		= '. $db->qstr( $VAR['gmaps_api']		);
		

	
	if(!$rs = $db->execute($q)) {
		force_page('core', 'error&error_msg=MySQL Error: '.$db->ErrorMsg().'&menu=1&type=database');
		exit;
	} else {
		force_page('control', 'company_edit&msg=The Company information was updated');
		exit;
	}

} else {

	/* get current Company information */
	$q = 'SELECT * FROM '.PRFX.'TABLE_COMPANY';
		if(!$rs = $db->execute($q)) {
			force_page('core', 'error&error_msg=MySQL Error: '.$db->ErrorMsg().'&menu=1&type=database');
			exit;
		} else {
			$arr = $rs->GetArray();
		}
	
	/* load setup Information */
	$q = 'SELECT * FROM '.PRFX.'SETUP';
	if(!$rs = $db->execute($q)) {
			force_page('core', 'error&error_msg=MySQL Error: '.$db->ErrorMsg().'&menu=1&type=database');
			exit;
		} else {
			$setup = $rs->GetArray();
		}
	
	/* get country codes */
	$q = 'SELECT * FROM '.PRFX.'COUNTRY';
	if(!$rs = $db->execute($q)) {
		force_page('core', 'error&error_msg=MySQL Error: '.$db->ErrorMsg().'&menu=1&type=database');
		exit;
	}
	$country = $rs->GetArray();
	
	//$arr = stripslashes($arr);
	
	$smarty->assign('country', $country);
	$smarty->assign('setup', $setup);
	$smarty->assign('company', $arr);
	$smarty->display('control/company_edit.tpl');
}
?>