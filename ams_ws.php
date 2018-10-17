<?php

class ams_ws 
{

  private $integratorPassword;
  private $integratorUsername;
  private $ws_root;
  
  
  function __construct( $dev = FALSE ) {
    $ini = parse_ini_file( "/common/settings/common.ini", TRUE );
    $this->integratorUsername = $ini['ams_settings']['ws_username'];
    $this->integratorPassword = $ini['ams_settings']['ws_password'];
    $this->ws_root = $ini['ams_settings']['ws_root'];
  }
  
  function exec( $url, $req_xml ) {
    $ch = curl_init(); 
    curl_setopt( $ch, CURLOPT_URL, $url ); 
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); 
    curl_setopt( $ch, CURLOPT_POST, 1 );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, 'P_INPUT_XML_DOC=' . urlencode($req_xml) );
    //curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/x-www-form-urlencoded' ) );
    $output = curl_exec( $ch ); 
    //die($output);
    curl_close($ch);
    $xml = simplexml_load_string( $output, null, LIBXML_NOCDATA );
    
    return $xml;
  }
  
  
  function insertCustomer( $params = array() ) {
    $url = $this->ws_root . "CENSSAWEBSVCLIB.INSERT_CUSTOMER_XML";
    $xml_tpl = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<insertRequest>
  <integratorUsername>$this->integratorUsername</integratorUsername>    <!-- REQUIRED - Provided by ACGI -->
  <integratorPassword>$this->integratorPassword</integratorPassword>    <!-- REQUIRED - Provided by ACGI -->
  <custType></custType>                        <!-- Defaults to I if not specified -->
  <bypassApproval></bypassApproval>            <!-- If Y, override approval requirement defined for customer type. Default N -->
  <firstName></firstName>                      <!-- REQUIRED -->
  <middleName></middleName>
  <lastName></lastName>                        <!-- REQUIRED -->
  <degreeName></degreeName>                    <!-- MD etc. -->
  <informalName></informalName>                <!-- e.g. Billy vs. William -->
  <companyName></companyName>
  <companyName2></companyName2>
  <tradeName></tradeName>
  <addressType></addressType>
  <street1></street1>
  <street2></street2>
  <city></city>
  <stateCode></stateCode>
  <postalCode></postalCode>
  <countryCode></countryCode>
  <phoneType></phoneType>                      <!-- Valid values defined in AA ("HOME" and "WORK" are commonly used) -->
  <phoneNum></phoneNum>
  <phoneExt></phoneExt>
  <emailType></emailType>                      <!-- Valid values defined in AA ("HOME" and "WORK" are commonly used) -->
  <email></email>                              <!-- emailType is REQUIRED to insert an email -->                     
  <birthdate></birthdate>                      <!-- MM/DD/YYYY    ** Ignored for "C"ompany types -->
  <genderCode></genderCode>                    <!-- M/F  ** Ingnored for "C"ompany types-->
  <sendEmail></sendEmail>                      <!-- Y if the approved customer is to be emailed their login credentials -->
  <loginId></loginId>                          <!-- If given the Individual's login ID is set to this -->          
  <passwd></passwd>                            <!-- If given the Individual's password is set to this -->
</insertRequest>
EOT;
     $xml = simplexml_load_string( $xml_tpl );
     $required_params = array( 
'firstName', 
'lastName' 
);
     $optional_params = array( 
'custType', 
'bypassApproval', 
'middleName', 
'degreeName', 
'informalName', 
'companyName',
'companyName2',
'tradeName',
'addressType',
'street1',
'street2',
'city',
'stateCode',
'postalCode',
'countryCode',
'phoneType',               
'phoneNum',
'phoneExt',
'emailType',
'email', 
'birthdate',
'genderCode',
'sendEmail',
'loginId',
'passwd'
);
    $ok_params = array_merge( $required_params, $optional_params );
    foreach( $params as $key => $value ) {
      if (in_array( $key, $ok_params )) {
        $xml->$key = $value;
      }
      else {
        die( "Invalid parameter '$key' provided; Valid parameters are (required) ".implode(", ", $required_params)." and (optional) ".implode(", ", $optional_params ) );
      }
    }
    foreach( $required_params as $param ) {
      if (empty($params[$param])) die( "Required parameter '$param' is missing.");
    }
    $output = $this->exec( $url, $xml->asXML());
    return $output;
  }
  
  
  function login( $params = array() ) {
    // NOTE: Login is also possible by "alias" + cust_id but we do not assign aliases to customers so I have left that element out of the template
    $url = $this->ws_root . "CENSSAWEBSVCLIB.AUTHENTICATION";
    $loginByLastName = isset($params['last-nm']) && isset($params['cust-id']);
    $loginByUsername = isset($params['username']) && isset($params['password']);
    $loginBySessionID = isset($params['cust-id']) && isset($params['session-id']);
    if (!$loginByLastName && !$loginByUsername && !$loginBySessionID) die( "Insufficient or incorrect paramaters supplied: Must use username & password or cust-id & last-nm or cust-id & session-id." );
    $xml_tpl = <<<EOT
<?xml version="1.0"?>
<authentication-request>
 <cust-id></cust-id>
 <last-nm></last-nm>
 <username></username>
 <password></password>
 <session-id></session-id>
</authentication-request>    
EOT;
    $xml = simplexml_load_string( $xml_tpl );
     foreach( $params as $key => $value ) {
       $xml->$key = $value;
     }
     $result = $this->exec( $url, $xml->asXML() );
     //die( "<pre>".print_r($result,1)."</pre>");

     if ((string) $result->authenticated === 'true') {
       // AA sometimes uses cookies in all-caps, sometimes lowercase, so need set both
       setcookie( "ssisid", (string) $result->session->{'session-id'}, 0, '/', '.informs.org' );
       setcookie( "SSISID", (string) $result->session->{'session-id'}, 0, '/', '.informs.org' );
       setcookie( "ssalogin", "yes", 0, '/', '.informs.org' );
       setcookie( "SSALOGIN", "yes", 0, '/', '.informs.org' );
       setcookie( "p_cust_id", (string) $result->customer->{'cust-id'}, 0, '/', '.informs.org' );
       setcookie( "P_CUST_ID", (string) $result->customer->{'cust-id'}, 0, '/', '.informs.org' );
       setcookie( "BREADCRUMB_SESSION_ID", (string) $result->session->{'breadcrumb-session-id'}, 0, '/', '.informs.org' ); // all-caps only needed
       $arrCustInfo = array();
       $arrCustInfo['loginserno'] = (string) $result->session->{'login-serno'};
       $arrCustInfo['ams_id'] = (string) $result->customer->{'cust-id'};
       $arrCustInfo['custid'] = (string) $result->customer->{'cust-id'};
       $arrCustInfo['first'] = (string) $result->customer->name->{'first-name'};
       $arrCustInfo['last'] = (string) $result->customer->name->{'last-name'};
       $arrCustInfo['middle'] = (string) $result->customer->name->{'middle-name'};
       $arrCustInfo['display_nm'] = (string) $result->customer->name->{'display-name'};
       $arrCustInfo['session_id'] = (string) $result->session->{'session-id'};
       $arrCustInfo['email'] = (string) $result->customer->{'cust-email'};
       $roles = array();
       foreach ( $result->session->roles->role as $role ) $roles[] = (string) $role;
       $arrCustInfo['roles'] = implode(",",$roles);
       $arrCustInfo['success'] = ((string) $result->authenticated == 'true') ? "TRUE" : "FALSE";
       $arrCustInfo['msgtext'] = (string) $result->{'authentication-message'};
       $arrCustInfo['lastConsent'] = (string)$result->customer->globalLastConsentForDataUse;
       $arrCustInfo['consentRequired'] = (string)$result->customer->globalUsageConsentRequired;
       
       $_SESSION['ams_auth'] = TRUE;
       
       return $arrCustInfo;
     }
     else {
       // to do: add some tests to provide more detailed feedback about why the attempted authentication failed
       //print_r( $result );
       return FALSE;
       
     }
  }
  
  // $consent is Y or N, $consentDate is string in YYYY-MM-DD format
  function setConsent( $custId, $consented, $consentDate ) {
/*

<?xml version="1.0" encoding="UTF-8"?>
     <consentDateRequest>
        <integratorUsername></integratorUsername>    <!-- REQUIRED - Provided by ACGI -->
        <integratorPassword></integratorPassword>    <!-- REQUIRED - Provided by ACGI -->
        <custId></custId>
        <consented></consented>                      <!-- Default Y; N indicates consent denied, and Date will be cleared -->                         
        <consentDate></consentDate>                  <!-- YYYY-MM-DD -->
     </consentDateRequest>
*/
    $url = $this->ws_root . "CENSSAWEBSVCLIB.UPDATE_GLOBAL_CONSENT_DATE_XML";
    $xml_tpl = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<consentDateRequest>
  <integratorUsername>$this->integratorUsername</integratorUsername>
  <integratorPassword>$this->integratorPassword</integratorPassword>
  <custId></custId>
  <consented></consented>                       
  <consentDate></consentDate>
</consentDateRequest>
EOT;
    $xml = simplexml_load_string( $xml_tpl );
    $xml->custId = $custId;
    $xml->consented = $consented;
    $xml->consentDate = $consentDate;
    $result = $this->exec( $url, $xml->asXML() );
    return $result;
  }
}

?>