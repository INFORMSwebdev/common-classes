<?php

class paypal {

  private static $PARTNER;
  private static $PWD;
  private static $URI;
  private static $USER;
  private static $VENDOR;
  
  public function __construct() {
    $ini = parse_ini_file( "/common/settings/common.ini", TRUE );
    self::$PARTNER = $ini['paypal_settings']['PARTNER'];
    self::$PWD = $ini['paypal_settings']['PWD'];
    self::$URI = $ini['paypal_settings']['URI_PAYPAL'];
    self::$USER = $ini['paypal_settings']['USER'];
    self::$VENDOR = $ini['paypal_settings']['VENDOR'];
  }
  
  public function processCreditCard( $params ) {
    $errors = array();
    $required_params = array( 
      'amountPaid', 
      'cardNumber', 
      'expMonth', 
      'expYear', 
      'CVV', 
      'InvoiceID', 
      'firstName', 
      'lastName', 
      'streetAddress1',
      'city',
      'state',
      'postalCode',
    );
    if (count($missing_params = array_diff($required_params, array_keys( $params )))) {
      return new paypalCCResponse( array( 'errors' => array('processCreditCard method missing params: '.implode(", ",$missing_params))));
    }
    $expYear = (strlen($params['expYear']) == 4) ? substr($params['expYear'], -2) : $params['expYear'];
    // optional fields
    $comment1 = (isset($params['comment1'])) ? $params['comment1'] : '';
    $streetAddress2 = (isset($params['streetAddress2'])) ? $params['streetAddress2'] : '';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, self::$URI);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 600); // for troubleshooting
    curl_setopt($ch, CURLOPT_TIMEOUT, 600);  // for troubleshooting
    $post = array( 
      'USER'		=>	self::$USER,
      'VENDOR'		=>	self::$VENDOR,
      'PARTNER'		=>	self::$PARTNER,
      'PWD'		=>	self::$PWD,
      'TRXTYPE'		=>	'A', /* authorization */
      'TENDER'		=>	'C', /* credit card */
      'ACCT'		=> 	$params['cardNumber'],
      'EXPDATE'		=> 	$params['expMonth'] . $expYear,
      'CVV2'		=>	$params['CVV'],
      'AMT'		=>	$params['amountPaid'],
      'INVNUM'		=>	$params['InvoiceID'],
      'PONUM'		=>	'',
      'COMMENT1'	=>	$comment1,
      'COMMENT2'	=>	'',
      'VERBOSITY'	=>	'HIGH',
      'BILLTOFIRSTNAME'	=>	$params['firstName'],
      'BILLTOLASTNAME'	=>	$params['lastName'],
      'BILLTOSTREET'	=>	$params['streetAddress1'],
      'BILLTOSTREET2'	=>	$streetAddress2,
      'BILLTOCITY'	=>	$params['city'],
      'BILLTOSTATE'	=>	$params['state'],
      'BILLTOZIP'	=>	$params['postalCode'],
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, urldecode(http_build_query($post)));
    $result = curl_exec($ch);
    curl_close($ch);
    parse_str($result, $auth_res);
    
    if ( strpos( $auth_res['RESPMSG'], "Duplicate trans: 10536" ) > -1 ) {
      $i = 2;
      $origInvNum = $post['INVNUM'];
      while ($i < 10 && $auth_res['RESPMSG'] != 'Approved') {
        $auth_res = NULL;
        $post['INVNUM'] = $origInvNum . "-" . $i;
        $i++;
            $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, self::$URI);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 600); // for troubleshooting
    curl_setopt($ch, CURLOPT_TIMEOUT, 600);  // for troubleshooting
        curl_setopt($ch, CURLOPT_POSTFIELDS, urldecode(http_build_query($post)));
        $result = curl_exec($ch);
        curl_close($ch);
        parse_str($result, $auth_res);
      }
      
    }
    
    if (isset($auth_res['CVV2MATCH']) && $auth_res['CVV2MATCH'] != 'Y') {
      $errors[] = "The CVV code entered is not correct.";
    }
    elseif( $auth_res['RESPMSG'] != 'Approved' || $auth_res['AUTHCODE'] != '111111') {
      $errors[] = "Authorization was rejected. Please contact your bank for further explanation. (Rejection message: {$auth_res['RESPMSG']})";
    }
    
    if (count($errors)) {
      appLog::append( '/var/www/services/logs/cc_auth_failure_tx.txt', print_r($auth_res, 1) );
      return new paypalCCResponse( array( 'errors' => $errors ) );
    }
    else {
      // authorization approved, let's go ahead and capture 
      $amt_charged = $auth_res['AMT'];
      $auth_ref = $auth_res['PNREF'];
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, self::$URI);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
      $post = array( 
      'USER'		=>	self::$USER,
      'VENDOR'		=>	self::$VENDOR,
      'PARTNER'		=>	self::$PARTNER,
      'PWD'		=>	self::$PWD,
      'TRXTYPE'		=>	'D', /* delayed capture */
      'TENDER'		=> 	'C', /* credit card */
      'AMT'		=>	$amt_charged,
      'ORIGID'		=>	$auth_ref,
      'CAPTURECOMPLETE'	=>	'Y',
      'VERBOSITY'	=>	'HIGH'
      );
      curl_setopt($ch, CURLOPT_POSTFIELDS, urldecode(http_build_query($post)));
      $result = curl_exec($ch);
      curl_close($ch);
      parse_str($result, $cap_res);
      
      if (isset($cap_res['RESPMSG']) && $cap_res['RESPMSG'] == 'Approved') {
        $pcrParams = array();
        $pcrParams['errors'] = array();
        $pcrParams['AUTHCODE'] = $auth_res['AUTHCODE'];
        $pcrParams['AUTHREF'] =  $auth_ref;
        $pcrParams['CAPREF'] = $cap_res['PNREF'];
        $pcrParams['amountPaid'] = $amt_charged;
        return new paypalCCResponse( $pcrParams );
      }
      else {
        // auth worked but capture failed
        $errors[] = "Authorization was approved but the 'transaction capture' failed. Please contact your bank for further explanation. (Rejection message: {$cap_res['RESPMSG']})";
        appLog::append( '/var/www/services/logs/cc_capture_failure_tx.txt', print_r($res, 1) );
        return new paypalCCResponse( array( 'errors' => $errors ) );
      }
    }
  }

}

class paypalCCResponse {
  public $AUTHCODE;
  public $AUTHREF;
  public $CAPREF;
  public $errors;
  public $amountPaid;
  public function __construct( $params ) {
    foreach( $params as $key => $value ) {
      $this->$key = $value;
    }
  }
}

?>