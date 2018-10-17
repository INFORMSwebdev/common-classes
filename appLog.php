<?php

class appLog {
  public function __construct() {

  }
  public static function append( $file, $msg ) {
    $fh = fopen( $file, "a" );
    $timestamp = date('Y-m-d H:i:s');
    fwrite( $fh, $timestamp . PHP_EOL . $msg . PHP_EOL );
    fclose( $fh );
  }
}

?>