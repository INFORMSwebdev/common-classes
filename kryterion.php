<?php

class kryterion {

  private $APIPath;
  private $APIURI;
  private $ExamTypeFieldID;
  private $Host;
  private $OkToTestFieldID;
  private $RequestType;
  private $SecurityToken;
  
  public function __construct( $dev = FALSE ) {
    $ini = parse_ini_file( "/common/settings/common.ini", TRUE );
    if ( $dev ) {
      $this->Host = $ini['kryterion_settings']['sandbox_host'];
      $this->SecurityToken = $ini['kryterion_settings']['sandbox_token'];
    }
    else {
      $this->Host = $ini['kryterion_settings']['prod_host'];
      $this->SecurityToken = $ini['kryterion_settings']['prod_token'];
    }
    $this->APIURI = $this->Host . $ini['kryterion_settings']['api_path'];
    $this->OkToTestFieldID = ($dev) ? $ini['kryterion_settings']['okToTestFieldID_dev'] : $ini['kryterion_settings']['okToTestFieldID_prod'];
    $this->ExamTypeFieldID = ($dev) ? $ini['kryterion_settings']['examTypeFieldID_dev'] : $ini['kryterion_settings']['examTypeFieldID_prod'];
  }
  
  // this can also apparently be used to update an existing eligibility
  public function addExamEligibility( $candidateId, $productId, $startDate = NULL, $endDate = NULL ) {
    if(!$candidateId) die( __FUNCTION__ . " method called, missing parameter(s): candidateId" );
    if(!$productId) die( __FUNCTION__ . " method called, missing parameter(s): productId" );
    $dtStartDate = ($startDate) ? new DateTime( $startDate ) : new DateTime();
    $dtEndDate = ($endDate) ? new DateTime( $endDate ) : $dtStartDate->add( new DateInterval('P1Y') );
    $interval = $dtStartDate->diff($dtEndDate);
    if ($interval->d > 7) die( "date range should <= 7 days" );
    if ($dtEndDate < $dtStartDate) die( "endDate must be > startDate" );
    $postdata = (object)[];
    $postdata->requestType = "ADD EXAM ELIGIBILITY";
    $postdata->candidateId = $candidateId;
    $postdata->productId = $productId;
    $postdata->certTypeId = "";
    $postdata->eligibilityStartDate = $dtStartDate->format('Y-m-d H:i:s');
    $postdata->eligibilityEndDate = $dtEndDate->format('Y-m-d H:i:s');
    $postdata->status = "ACTIVE";
    $postdata->authCode = "Yes";
    return $this->exec( $postdata );
  }
  
  public function addItem() {
    // this is a placeholder for a web service we are not currently planning to use
    $postdata = (object)[];
    $postdata->requestType = "ADD ITEM";
    return NULL;
  }
  
  public function addRegistration() {
    // this is a placeholder for a web service we are not currently planning to use
    $postdata = (object)[];
    $postdata->requestType = "ADD REGISTRATION";
    return NULL;
  }
  
  public function addTranscript() {
    // this is a placeholder for a web service we are not currently planning to use
    $postdata = (object)[];
    $postdata->requestType = "ADD TRANSCRIPT";
    return NULL;
  }
  
  public function addUser( $params ) {
    $required_fields = array(
      'login',
      'password',
      'firstName',
      'lastName',
      'email',
      'addressStreet1',
      'addressCity',
      'addressState',
      'addressPostalCode',
      'addressCountry',
    );
    $optional_fields = array(
      'userDefined1',
      'userDefined2',
      'userDefined3',
      'userDefined4',
      'userDefined5',
      'secondaryEmail',
      'notes',
      'cardNumber',
      'addressStreet2',
      'homePhone',
      'returnFormat',
      'sendEmail',
    );
    if (!isset($params['password'])) $params['password'] = $this->generateStrongPassword();
    if (count($missing_keys = array_diff($required_fields, array_keys($params)))) {
      die( __FUNCTION__ . " method called, missing parameter(s): " . implode(", ", $missing_keys) );
    }
    $all_fields = array_merge( $required_fields, $optional_fields );
    $postdata = (object)[];
    $postdata->requestType = "ADD USER";
    foreach( $all_fields as $key ) {
      if (isset($params[$key])) $postdata->{$key} = $params[$key];
    }
    if (!isset($params['sendEmail'])) $postdata->sendEmail = FALSE;
    $postdata->customFields = array();
    $okToTest = (object)[];
    $okToTest->id = $this->OkToTestFieldID;
    $okToTest->optionValue = (isset($params['okToTest'])) ? $params['okToTest'] : 'Yes';
    //$okToTest->intValue = "0";
    //$okToTest->numberValue = "0";
    $postdata->customFields[] = $okToTest;
    $examType = (object)[];
    $examType->id = $this->ExamTypeFieldID;
    $examType->optionValue = isset($params['examType']) ? $params['examType'] : 'Cap';
    //$examType->intValue = "0";
   // $examType->numberValue = "0";
    $postdata->customFields[] = $examType;
    //echo print_r($postdata,1)."<br/><br/>";
    return $this->exec( $postdata ); //print_r( $postdata, 1 );
  }
  
  public function deleteUser( $login ) {
    if (is_array( $login )) $login = $login['login']; // just in case I pass in array with login value instead of simple string
    if(!$login) die( __FUNCTION__ . " method called, missing parameter(s): login" );
    $postdata = (object)[];
    $postdata->requestType = "DELETE USER";
    $postdata->login = $login;
    return $this->exec( $postdata ); 
  }
  
  public function editUser( $params ) {
    $required_fields = array(
      'login',
      'firstName',
      'lastName',
      'email',
      'addressStreet1',
      'addressCity',
      'addressState',
      'addressPostalCode',
      'addressCountry',
    );
    $optional_fields = array(
      'password',
      'userDefined1',
      'userDefined2',
      'userDefined3',
      'userDefined4',
      'userDefined5',
      'secondaryEmail',
      'notes',
      'cardNumber',
      'addressStreet2',
      'homePhone',
      'returnFormat',
      'customFields',
      'sendEmail',
    );
    if (count($missing_keys = array_diff($required_fields, array_keys($params)))) {
      die( __FUNCTION__ . " method called, missing parameter(s): " . implode(", ", $missing_keys) );
    }
    $all_fields = array_merge( $required_fields, $optional_fields );
    $postdata = (object)[];
    $postdata->requestType = "EDIT USER";
    foreach( $all_fields as $key ) {
      if (isset($params[$key])) $postdata->{$key} = (is_array($params[$key])) ? "" : $params[$key];
    }
    if ($postdata->addressState == "NA") $postdata->addressState = "N/A";
    if (!isset($params['sendEmail'])) $postdata->sendEmail = FALSE;
    $postdata->customFields = array();
    $okToTest = (object)[];
    $okToTest->id = $this->OkToTestFieldID;
    //$okToTest->intValue = "0";
    //$okToTest->numberValue = "0";
    //$okToTest->stringValue = "";
    $okToTest->optionValue = (isset($params['okToTest'])) ? $params['okToTest'] : 'No';
    $postdata->customFields[] = $okToTest;
    $examType = (object)[];
    $examType->id = $this->ExamTypeFieldID;
    //$examType->intValue = "0";
    //$examType->numberValue = "0";
    //$examType->stringValue = "";
    $examType->optionValue = isset($params['examType']) ? $params['examType'] : 'Cap';
    $postdata->customFields[] = $examType;
    return $this->exec( $postdata ); //print_r( $postdata, 1 );
  }
  
  private function exec( $DataObject ) { // argument is object
    if (!isset($DataObject->returnFormat)) $DataObject->returnFormat = "JSON";
    $DataObject->securityToken = $this->SecurityToken;
    $post_data = json_encode( $DataObject );
    echo "URL: ". $this->APIURI . "<br/><br/>REQUEST SENT<br/>".$post_data;
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $this->APIURI );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array( "Content-Type: application/json" ) );
    curl_setopt( $ch, CURLOPT_HEADER, FALSE );
    curl_setopt( $ch, CURLOPT_POST, TRUE);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_data );
    $response = curl_exec($ch);
    curl_close($ch);
    echo "<br/>RESPONSE RECEIVED<br/>".$response;
    return json_decode( $response ); // returned item is object
  }
  
  // Borrowed this from https://gist.github.com/tylerhall/521810
  // Generates a strong password of N length containing at least one lower case letter,
  // one uppercase letter, one digit, and one special character. The remaining characters
  // in the password are chosen at random from those four sets.
  //
  // The available characters in each set are user friendly - there are no ambiguous
  // characters such as i, l, 1, o, 0, etc. This, coupled with the $add_dashes option,
  // makes it much easier for users to manually type or speak their passwords.
  //
  // Note: the $add_dashes option will increase the length of the password by
  // floor(sqrt(N)) characters.
  public function generateStrongPassword( $length = 8, $add_dashes = false, $available_sets = 'luds' ) {
    $sets = array();
    if(strpos($available_sets, 'l') !== false)
    	$sets[] = 'abcdefghjkmnpqrstuvwxyz';
    if(strpos($available_sets, 'u') !== false)
    	$sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
    if(strpos($available_sets, 'd') !== false)
    	$sets[] = '23456789';
    if(strpos($available_sets, 's') !== false)
    	$sets[] = '!@#$%&*?';
    $all = '';
    $password = '';
    foreach($sets as $set) {
      $password .= $set[array_rand(str_split($set))];
      $all .= $set;
    }
    $all = str_split($all);
    for($i = 0; $i < $length - count($sets); $i++)
      $password .= $all[array_rand($all)];
    $password = str_shuffle($password);
    if(!$add_dashes)
      return $password;
    $dash_len = floor(sqrt($length));
    $dash_str = '';
    while(strlen($password) > $dash_len) {
      $dash_str .= substr($password, 0, $dash_len) . '-';
      $password = substr($password, $dash_len);
    }
    $dash_str .= $password;
    return $dash_str;
  }
  
  public function getCertInfo() {
    // this is a placeholder for a web service we are not currently planning to use
    $postdata = (object)[];
    $postdata->requestType = "GET CERT INFO";
    return NULL;
  }
  
  public function getClientRegions() {
    // this web service does not appear to work -- returns blank response
    $postdata = (object)[];
    $postdata->requestType = "GET CLIENT REGIONS";
    return $this->exec( $postdata ); 
  }
  
  // getCustomFields: returns custom fields configured for our account
  // out: object
  public function getCustomFields() {
    $postdata = (object)[];
    $postdata->requestType = "GET CUSTOM FIELDS";
    return $this->exec( $postdata ); 
  }
  
  public function getProducts() {
    $postdata = (object)[];
    $postdata->requestType = "GET PRODUCTS";
    return $this->exec( $postdata ); 
  }
  
  public function getRegistrationsByDateRange( $startDate = NULL, $endDate = NULL ) {
    $dtStartDate = ($startDate) ? new DateTime( $startDate ) : new DateTime();
    $dtEndDate = ($endDate) ? new DateTime( $endDate ) : $dtStartDate->add( new DateInterval('P7D') );
    $interval = $dtStartDate->diff($dtEndDate);
    if ($interval->d > 7) die( "date range should <= 7 days" );
    if ($dtEndDate < $dtStartDate) die( "endDate must be > startDate" );
    $postdata = (object)[];
    $postdata->requestType = "GET REGISTRATIONS BY DATE RANGE";
    $postdata->startDate = $dtStartDate->format('Y-m-d H:i:s');
    $postdata->endDate = $dtEndDate->format('Y-m-d H:i:s');
    return $this->exec( $postdata ); 
  }
  
  public function getRegistrationsByProductCode( $productCode, $startDate = NULL, $endDate = NULL ) {
    if(!$productCode) die( __FUNCTION__ . " method called, missing parameter(s): productCode" );
    $dtStartDate = ($startDate) ? new DateTime( $startDate ) : new DateTime();
    $dtEndDate = ($endDate) ? new DateTime( $endDate ) : $dtStartDate->add( new DateInterval('P7D') );
    $interval = $dtStartDate->diff($dtEndDate);
    if ($interval->d > 7) die( "date range should <= 7 days" );
    if ($dtEndDate < $dtStartDate) die( "endDate must be > startDate" );
    $postdata = (object)[];
    $postdata->requestType = "GET REGISTRATIONS BY PRODUCT CODE";
    $postdata->productCode = $productCode;
    $postdata->startDate = $dtStartDate->format('Y-m-d H:i:s');
    $postdata->endDate = $dtEndDate->format('Y-m-d H:i:s');
    return $this->exec( $postdata ); 
  }
  
  public function getRegistrationsByUser( $login ) {
    if (is_array( $login )) $login = $login['login']; // just in case I pass in array with login value instead of simple string
    if(!$login) die( __FUNCTION__ . " method called, missing parameter(s): login" );
    $postdata = (object)[];
    $postdata->requestType = "GET REGISTRATIONS";
    $postdata->login = $login;
    return $this->exec( $postdata ); 
  }
  
  public function getTranscripts() {
    $startDate = new DateTime;
    $startDate->sub( new DateInterval('P7D') );
    return $this->getTranscriptsByDateRange( $startDate->format( 'Y-m-d H:i:s') );
  }
  
  // getTranscriptsByDateRange: given a date range, returns all exam results
  // in: $startDate (string) should be yyyy-mm-dd to make it easy on parser
  // in: $endDate (string) default NULL, if left blank will be set to 7 days after startDate
  // out: array if successful, object if there was web service error (see errorCode and errorMessage properties of object)
  public function getTranscriptsByDateRange( $startDate, $endDate = NULL ) {
    if(!$startDate) die( __FUNCTION__ . " method called, missing parameter(s): startDate" );
    $dtStartDate = new DateTime( $startDate );
    if($endDate) {
      $dtEndDate = new DateTime( $endDate );
    }
    else {
      $dtEndDate = clone $dtStartDate;
      $dtEndDate->add( new DateInterval('P7D') );
    }
    $interval = $dtStartDate->diff($dtEndDate);
    if ($interval->d > 7) die( "date range should <= 7 days" );
    if ($dtEndDate < $dtStartDate) die( "endDate must be > startDate" );
    $postdata = (object)[];
    $postdata->requestType = "GET TRANSCRIPTS BY DATE RANGE";
    $postdata->startDate = $dtStartDate->format('Y-m-d H:i:s');
    $postdata->endDate = $dtEndDate->format('Y-m-d H:i:s');
    return $this->exec( $postdata ); 
  }
  
  public function getTranscriptsByUser( $login ) {
    if (is_array( $login )) $login = $login['login']; // just in case I pass in array with login value instead of simple string
    if(!$login) die( __FUNCTION__ . " method called, missing parameter(s): login" );
    $postdata = (object)[];
    $postdata->requestType = "GET TRANSCRIPTS BY USER";
    $postdata->login = $login;
    return $this->exec( $postdata ); 
  }
  
  public function getUser( $login ) {
    if (is_array( $login )) $login = $login['login']; // just in case I pass in array with login value instead of simple string
    if(!$login) die( __FUNCTION__ . " method called, missing parameter(s): login" );
    $postdata = (object)[];
    $postdata->requestType = "GET USER";
    $postdata->login = $login;
    $response = $this->exec( $postdata );
    return (property_exists( $response, 'errorCode') && $response->errorCode == "WAWSE-00008 - INVALID LOGIN") ? FALSE : $response; 
  }
  
  public function getUserByID( $id ) {
    if (is_array( $id )) $id = $id['id']; // just in case I pass in array with id value instead of simple int
    if(!$id) die( __FUNCTION__ . " method called, missing parameter(s): id" );
    $postdata = (object)[];
    $postdata->requestType = "GET USER BY ID";
    $postdata->userId = $id;
    $response = $this->exec( $postdata );
    return (property_exists( $response, 'errorCode') && $response->errorCode == "WAWSE-00008 - INVALID LOGIN") ? FALSE : $response; 
  }
  
  public function setExamType( $login, $value ) {
    $user = $this->getUser( $login );
    $userArr = (array) $user;
    $userArr['sendEmail'] = FALSE;
    $userArr['examType'] = $value;
    $this->editUser( $userArr );
  }
  
  public function setOkToTest( $login, $value ) {
    $user = $this->getUser( $login );
    $userArr = (array) $user;
    $userArr['sendEmail'] = FALSE;
    $userArr['okToTest'] = $value;
    $this->editUser( $userArr );
  }
  
}

?>