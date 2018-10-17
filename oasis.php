<?php

class oasis {

  private $ReportName;
  private $SoapPassword;
  private $SoapUrl;
  private $SoapUser;
  
  public function __construct() {
    $ini = parse_ini_file( "/common/settings/common.ini", TRUE );
    $this->SoapPassword = $ini['oasis_settings']['soappassword'];
    $this->SoapUrl = $ini['oasis_settings']['soapurl'];
    $this->SoapUser = $ini['oasis_settings']['soapuser'];
  }

  public function getReportArrayFromResponse( $reportname, $meetingkey ) {

    // xml post structure
    $xml_post_string = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
   <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
     <soap:Header>
       <AuthenticationHeader xmlns="CTT.OASIS.WS">
         <SecurityTokenKey>$this->SoapUser</SecurityTokenKey>
         <SecurityTokenValue>$this->SoapPassword</SecurityTokenValue>
       </AuthenticationHeader>
     </soap:Header>
     <soap:Body>
       <OasisWSGetReport xmlns="CTT.OASIS.WS">
         <meetingKey>$meetingkey</meetingKey>
         <reportName>$reportname</reportName>
       </OasisWSGetReport>
     </soap:Body>
   </soap:Envelope>                     
EOT;

    $headers = array(
      "Host: www.abstractsonline.com",
      "Content-type: text/xml;charset=utf-8",
      "Accept: text/xml",
      "Cache-Control: no-cache",
      "Pragma: no-cache",
      "SOAPAction: CTT.OASIS.WS/OasisWSGetReport", 
      "Content-length: " . strlen($xml_post_string),
    );

    // PHP cURL  for https connection with auth
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_URL, $this->SoapUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $this->SoapUser.":".$this->SoapPassword);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_TIMEOUT, 200);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch); 
    curl_close($ch);

    $response = str_replace("<soap:Body>","",$response);
    $response = str_replace("</soap:Body>","",$response);
    $response = str_replace("&gt;", ">", $response);
    $response = str_replace("&lt;", "<", $response);
   
    die(print_r($response,1));

    if( $response == '' ) {
      die( 'ERROR getting data' );
      //echo 'ERROR getting data, so using sample response';
      //$response = file_get_contents('sample_response');
    }

    $parser = simplexml_load_string($response);

    if( !isset($parser[0]) ) die('ERROR getting data');

    $report_array = $parser[0] -> OasisWSGetReportResponse -> OasisWSGetReportResult -> ReportServiceResponse;
    //echo '<pre>'; print_r($report_array); echo '</pre>';
    return $report_array;
}

} 

?>