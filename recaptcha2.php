<?php

class recaptcha2 {

  private $Secretkey;
  private $Sitekey;
  private $Verifyurl;
  
  public function __construct() {
    $ini = parse_ini_file( "/common/settings/common.ini", TRUE );
    $this->Sitekey = $ini['recaptcha2_settings']['sitekey'];
    $this->Secretkey = $ini['recaptcha2_settings']['secretkey'];
    $this->Verifyurl = $ini['recaptcha2_settings']['verifyurl'];
  }
  
  public function html( $include_script_ref = TRUE ) {
    $html = "";
    if ($include_script_ref) $html = '<script src="https://www.google.com/recaptcha/api.js"></script>';
    $html .= <<<EOT
<div class="g-recaptcha" data-sitekey="{$this->Sitekey}"></div>
EOT;
    return $html;
  }
  
  public function verify( $g_recaptcha_response ) {
    if (!$g_recaptcha_response) return FALSE;
    $ip = $_SERVER['REMOTE_ADDR'];
    $ch = curl_init();
    $postfields = array(
      "secret" => $this->Secretkey,
      "response" => $g_recaptcha_response,
      "remoteip" => $ip
    );
    curl_setopt($ch, CURLOPT_URL,$this->Verifyurl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields) );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode( curl_exec ($ch) );
    curl_close ($ch);
    return $response->success;
  }
  
}

?>