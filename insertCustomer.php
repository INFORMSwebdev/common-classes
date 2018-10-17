<?php

// dev: https://online.informs.org/informssvcdev/CENSSAWEBSVCLIB.INSERT_CUSTOMER_XML 
// prod: https://online.informs.org/informssvc/CENSSAWEBSVCLIB.INSERT_CUSTOMER_XML 

class insertCustomer {

  public $xmldoc;
  public $params;
  public static $api_url = "https://online.informs.org/informssvcdev/CENSSAWEBSVCLIB.INSERT_CUSTOMER_XML";  // dev 
  // public $api_url = "https://online.informs.org/informssvc/CENSSAWEBSVCLIB.INSERT_CUSTOMER_XML"; // prod

  public function __construct( $params_in = array() ) {
  
    $params = array();
    $keys = array( 
      'firstName',
      'middleName',
      'lastName',
      'degreeName',                         
      'informalName',                      
      'companyName',
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
      'passwd',
    );
  
    foreach( $keys as $key ) $params[$key] = ($params_in[$key]) ? $params_in[$key] : '';
    
    if (isset($params['email']) && !isset($params['emailType'])) $params['emailType'] = 'HOME';
    if ((isset($params['street1']) || isset($params['street2']) || isset($params['city']) || isset($params['stateCode']) || isset($params['postalCode']) || isset($params['countryCode'])) && !isset($params['addressType'])) $params['addressType'] = 'HOME';
    if ((isset($params['phoneNum']) || isset($params['phoneExt'])) && !isset($params['phonetype'])) $params['phoneType'] = 'HOME';
    $this->$params = $params; 
    
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><insertRequest/>');
    $xml->addChild( 'integratorUsername', 'informs' );
    $xml->addChild( 'integratorPassword', 'IntrnlAMSC0nct' );
    foreach( $params as $key => $value ) $xml->addChild( $key, $value );
    $this->xmldoc = $xml->asXML();
    /*
    $this->xmldoc = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<insertRequest>
  <integratorUsername>informs</integratorUsername>           
  <integratorPassword>IntrnlAMSC0nct</integratorPassword>          
  <firstName>{$params['firstName']}</firstName><!-- REQUIRED -->
  <middleName></middleName>
  <lastName>{$params['lastName']}</lastName><!-- REQUIRED -->
  <degreeName></degreeName>                           
  <informalName></informalName>                       
  <companyName></companyName>
  <addressType>HOME</addressType>
  <street1></street1>
  <street2></street2>
  <city></city>
  <stateCode></stateCode>
  <postalCode></postalCode>
  <countryCode></countryCode>
  <phoneType></phoneType>
  <phoneNum></phoneNum>
  <phoneExt></phoneExt>
  <emailType>HOME</emailType><!-- REQUIRED if email is inserted -->
  <email></email>                  
  <birthdate></birthdate><!-- MM/DD/YYYY -->
  <genderCode></genderCode><!-- M/F -->
  <sendEmail></sendEmail><!-- Y if the new customer is to be emailed their login credentials -->
  <loginId>{$params['loginID']}</loginId>          
  <passwd>{$params['passwd']}</passwd>
</insertRequest>
EOT;*/

  }
  
  public function dupeCheck() {
    echo $this->xmldoc;
  }
  
  public function exec() {
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, self::$api_url );
    curl_setopt( $ch, CURLOPT_POST, 1 );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( array( 'p_input_xml_doc' => $this->xmldoc ) ) );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    $result = curl_exec( $ch );
    return $result;
  }
  
  public function validateEmail() {
    return ($this->params['email'] && filter_var( $this->params['email'], FILTER_VALIDATE_EMAIL ));
  }
}

?>