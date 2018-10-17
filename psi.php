<?php

class psi {

  public function results($examType = 'CAP') {

    $url = "http://svc.goamp.com/ResultsExport/api/Responses";
    $ini = parse_ini_file( "/common/settings/common.ini", TRUE );
    $username = $ini['psi_settings']['psi_user'];
    $password = $ini['psi_settings']['results_password'];
    $data = array(
'Client_code' => 'INFORMS',
'Exam_code' => $examType,
'Exam_type' => $examType
);

    $json_data = json_encode( $data );
    
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) );
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    $json_response = curl_exec($ch);

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ( $status != 200 ) {
      die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($ch) . ", curl_errno " . curl_errno($ch));
    }
    else {
      return $json_response;
    }
  }
}
?>