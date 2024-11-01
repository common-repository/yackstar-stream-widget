<?php		
$response = wp_remote_post( YACK_URL.'/Account/Login', array(
		'method' => 'POST',
		'timeout' => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'sslverify' => false,
		'headers' => array(),
		'body' => array(
				'ReturnUrl' => '',
				'username' => YACK_USER,
				'password' => YACK_PASS,
				'other' => 3
		),
		'cookies'=>array()
));

if( is_wp_error( $response ) ) {
	delete_option('yackstar_auth_cookie');
	return FALSE;
} else {
	$sess=FALSE;
	foreach($response['cookies'] as $value){
		if($value->name==='SID')
			$sess=TRUE;
	}
	if (isset($response['cookies'])&& ($sess===TRUE)) {
		update_option('yackstar_auth_cookie',$response['cookies']);
	}
	else
	{
		delete_option('yackstar_auth_cookie');
	}
}