<?php 

/**
* @class iol_file iol_file.php
* @brief file handling class
*/

class iol_file
{
//test
  private $filename;
  private $path;
  private $xsend;
  
  /**
  * constructor
  * @param $path string REQUIRED
  * @param $filename string default NULL
  * @param $xsend bool default FALSE
  */
  public function __construct( $path, $filename = NULL, $xsend = FALSE )
  {
    $this->filename = $filename;
    $this->path = $path;
    $this->xsend = $xsend;
  }
  
  /**
  * public function approach to performing file downloads
  */
  public function download()
  {
    $this->performDownload( $this->path, $this->filename, $this->xsend );
  }
  
  /**
  * static function approach to performing file downloads
  * @param $path string REQUIRED
  * @param $filename string default NULL
  * @param $xsend bool defailt FALSE
  */
  static function downloadFile( $path, $filename = NULL, $xsend = FALSE )
  {
    self::performDownload( $path, $filename, $xsend );
  }
  
  /**
  * outputs as direct file download
  * @param $path string REQUIRED
  * @param $filename string default NULL
  * @param $xsend bool default FALSE
  */
  private function performDownload( $path, $filename = NULL, $xsend = FALSE )
  {
    // if no filename provided, use the actual filename
    if ( !$filename ) $filename = basename( $path );
    ob_clean();
    // IE fixes
    header( 'Pragma: ' );
    header( 'Cache-Control: ' );
    header( 'Expires: '. gmdate( 'D, d M Y H:i:s', time() + 600 ) . ' GMT' );
    // standard download headers
    header( 'Content-Length: ' . filesize( $path ) );
    header( 'Content-Type: application/octet-stream' ); 
    header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
    header( 'Content-Transfer-Encoding: binary' );
    if ( $xsend )
    {
      header('X-Sendfile: ' . $path );
    }
    else
    {
      readfile( $path );
    }
  }

}

?>
