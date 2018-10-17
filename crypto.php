<?php
//*************************************
// Encrypts and decrypts using AES_256_CBC.
// Key and IV need to be 32-character. For the sake of convention, both should be all uppercase.
//
//*************************************
class crypto {

  static function encrypt($string = "", $key = "", $iv = "") {
    return base64_encode(openssl_encrypt($string, "AES-256-CBC", strtoupper($key), OPENSSL_RAW_DATA, strtoupper(substr($iv,0,16))));
  }
  
  static function decrypt($string = "", $key = "", $iv = "") {
    $string = urlencode($string);
    $string = str_replace("+", "%2B",$string);
    $string = urldecode($string);
    return openssl_decrypt(base64_decode($string), "AES-256-CBC", strtoupper($key), OPENSSL_RAW_DATA, strtoupper(substr($iv,0,16)));
  }

}

?>