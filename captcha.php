<?php

/*!
This class encapsulates the recaptchalib utility in a class for simplified implementation. It can be used as an instance or statically. 

Example of instance:
(in form)
$captcha = new captcha();
echo $captcha->html( TRUE ); // outputs captcha; parameter specifies whether the context is https (true) or http (false)
(in form handler)
$captcha = new captcha();
$resp = $captcha->checkAnswer(); // return type is boolean indicating validity of response

Example of static usage:
(in form)
echo captcha::static_html( TRUE ); // outputs captcha; parameter specifies whether the context is https (true) or http (false)
(in form handler)
$resp = captcha->static_checkAnswer(); // return type is boolean indicating validity of response
*/

require_once( '/common/utilities/recaptchalib.php' );

class captcha
{
     
  private $private_key;
  private $public_key;
  const RECAPTCHA_INI = '/common/settings/recaptcha.ini';

  public function __construct()
  {
    $ini = parse_ini_file( self::RECAPTCHA_INI, TRUE );
    $this->private_key = $ini['recaptcha_settings']['PrivateKey'];
    $this->public_key = $ini['recaptcha_settings']['PublicKey'];
  }
  
  /*!
  * Please note that this function assumes $_POST["recaptcha_challenge_field"] and $_POST["recaptcha_response_field"] exist
  */
  public function checkAnswer()
  {
    if (!isset( $_SERVER["REMOTE_ADDR"] ))
      die( 'missing $_SERVER["REMOTE_ADDR"]' );
    if (!isset( $_POST["recaptcha_challenge_field"] ))
      die( 'missing $_POST["recaptcha_challenge_field"]' );
    if (!isset( $_POST["recaptcha_response_field"] ))
      die( 'missing $_POST["recaptcha_response_field"]' );
    
    $recaptcha_response = recaptcha_check_answer( $this->private_key, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"] );
    return $recaptcha_response->is_valid;
  }
  
  public function html( $use_ssl = FALSE )
  {
    return recaptcha_get_html( $this->public_key, NULL, $use_ssl );
  }
  
  /*!
  * Please note that this function assumes $_POST["recaptcha_challenge_field"] and $_POST["recaptcha_response_field"] exist
  */
  public static function static_checkAnswer()
  {
    $ini = parse_ini_file( self::RECAPTCHA_INI, TRUE );
    $private_key = $ini['recaptcha_settings']['PrivateKey'];
    if (!isset( $_SERVER["REMOTE_ADDR"] ))
      die( 'missing $_SERVER["REMOTE_ADDR"]' );
    if (!isset( $_POST["recaptcha_challenge_field"] ))
      die( 'missing $_POST["recaptcha_challenge_field"]' );
    if (!isset( $_POST["recaptcha_response_field"] ))
      die( 'missing $_POST["recaptcha_response_field"]' );
    
    $recaptcha_response = recaptcha_check_answer( $private_key, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"] );
    return $recaptcha_response->is_valid;
  }
  
  public static function static_html( $use_ssl = FALSE )
  {
    $ini = parse_ini_file( self::RECAPTCHA_INI, TRUE );
    $public_key = $ini['recaptcha_settings']['PublicKey'];
    return recaptcha_get_html( $public_key, NULL, $use_ssl );
  }

}

?>