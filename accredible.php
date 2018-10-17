<?php

class accredible { 
  
  public $API_KEY;
  public $groups = array();
  
  public function __construct() { 
    $ini = parse_ini_file( "/common/settings/common.ini", TRUE );
    $this->API_KEY = $ini['accredible']['api_key'];
    $this->groups['CAP'] = $ini['accredible']['group_id']['CAP'];
    $this->groups['aCAP'] = $ini['accredible']['group_id']['aCAP'];
  }
  
  public function createCredential( $params = array() ) { 
    $uri = "https://api.accredible.com/v1/credentials";
    //$uri = "https://private-anon-a5b430a6ed-accrediblecredentialapi.apiary-mock.com/v1/credentials";
    $postdata = (object)[];
    $postdata->credential = new credential;
    $postdata->credential->group_name = $params['group_name'];
    $postdata->credential->id = $params['cert_num'];
    $postdata->credential->recipient = new recipient;
    $postdata->credential->recipient->name = $params['name'];
    $postdata->credential->recipient->email = $params['email'];
    $postdata->credential->recipient->id = $params['user_id'];

    $curl_options = array( 'CURLOPT_URL' => $uri, 'CURLOPT_POST' => TRUE, 'CURLOPT_POSTFIELDS' => json_encode($postdata) );
    return $this->exec( $curl_options );
  }
  
  public function deleteCredential( $id ) { 
    $uri = "https://api.accredible.com/v1/credentials/$id";
    return $this->exec( array( 'CURLOPT_URL' => $uri, 'CURLOPT_CUSTOMREQUEST' => "DELETE" ) );
    
  }
  
  public function exec( $curl_options = array() ) {
    if (!isset( $curl_options )) die( "missing $curl_options array" );
    if (!isset( $curl_options['CURLOPT_URL'] )) die( "missing CURLOPT_URL setting" );
    
    // default cURL options
    if (!isset( $curl_options['CURLOPT_RETURNTRANSFER'] )) $curl_options['CURLOPT_RETURNTRANSFER'] = TRUE;
    if (!isset( $curl_options['CURLOPT_HEADER'] )) $curl_options['CURLOPT_HEADER'] = FALSE;
    if (!isset( $curl_options['CURLOPT_HTTPHEADER'] )) $curl_options['CURLOPT_HTTPHEADER'] = array( "Content-Type: application/json", "Authorization: Token token=$this->API_KEY" );
    
    $ch = curl_init();
    /*curl_setopt( $ch, CURLOPT_URL, $curl_options['CURLOPT_URL'] );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
    curl_setopt( $ch, CURLOPT_HEADER, FALSE );
    curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Content-Type: application/json", "Authorization: Token token=$this->API_KEY" ));*/

    foreach( $curl_options as $option => $value ) {
      curl_setopt( $ch, constant($option), $value );
    }

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
  }
  
  public function viewAllCredentials( $params = array( 
                                        'group_id' => NULL,
                                        'email' => NULL,
                                        'recipient_id' => NULL,
                                        'page_size' => NULL,
                                        'page'=> NULL ) ) {
    
    $querystring = http_build_query( $params );
    $uri = "https://api.accredible.com/v1/all_credentials?$querystring";
    return $this->exec( array( 'CURLOPT_URL' => $uri ) );
  }
  
  public function viewCredential( $id ) { 
    $uri = "https://api.accredible.com/v1/credentials/$id";
    return $this->exec( array( 'CURLOPT_URL' => $uri ) );
  }
  
}

class credential {
  public $id; // cert ID
  public $recipient;
  public $group_name;
}

class recipient {
  public $name;
  public $email;
  public $id; // cap_user ID
}

?>