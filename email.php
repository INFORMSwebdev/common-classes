<?php

class email
{

  public $to;
  public $subject;
  public $body_text;
  public $body_html;
  public $headers = "From: INFORMS <ezioladmin@mail.informs.org>\r\n";
  public $parameters;

  function __construct( $params = array() ) 
  {
    if (isset( $params['to'] )) $this->to = $params['to'];
    if (isset( $params['subject'] )) $this->subject = $params['subject'];
    if (isset( $params['body_text'] )) $this->body_text = $params['body_text'];
    if (isset( $params['body_html'] )) $this->body_html = $params['body_html'];
    if (isset( $params['headers'] )) $this->headers = $params['headers'];
    if (isset( $params['parameters'] )) $this->parameters = $params['parameters'];
    if ($this->body_text && !$this->body_html) $this->convertPlaintextToHTML();
    if ($this->body_html && !$this->body_text) $this->convertHTMLToPlaintext();
  }
  
  public function convertPlaintextToHTML()
  {
    $this->body_html = "<p>" . preg_replace( "/\n\r?\n/", "</p><p>", htmlspecialchars( $this->body_text ) ) . "</p>";
  }
  
  public function convertHTMLToPlaintext()
  {
    $filters = array();
    $replacements = array();
    $filters[] = "/[\\n\\r\\t]/"; // get rid of unwanted line breaks
    $replacements[] = "";
    $filters[] = "/<p>((?:(?!<\/p>).)*)<\/p>/"; // strip p, add lb
    $replacements[] = "$1\r\n\r\n";
    $filters[] = "/<ul>((?:(?!<\/ul>).)*)<\/ul>/"; // strip ul, add lb (only one, because last li will have one)
    $replacements[] = "$1\r\n";
    $filters[] = "/<li>((?:(?!<\/li>).)*)<\/li>/"; // strip li
    $replacements[] = "- $1\r\n";
    $filters[] = "/<\/?[iabu][^>]*>/"; // strip out a, i, b, u tags
    $replacements[] = "";
    $filters[] = "/<br \/>/"; // strip our br
    $replacements[] = "\r\n";
    $this->body_text = preg_replace( $filters, $replacements, $this->body_html );
  }
  
  public function is_valid( $email )
  {
    $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
    $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
    $atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
    $quoted_pair = '\\x5c[\\x00-\\x7f]';
    $domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
    $quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
    $domain_ref = $atom;
    $sub_domain = "($domain_ref|$domain_literal)";
    $word = "($atom|$quoted_string)";
    $domain = "$sub_domain(\\x2e$sub_domain)*";
    $local_part = "$word(\\x2e$word)*";
    $addr_spec = "$local_part\\x40$domain";
    return preg_match("!^$addr_spec$!", $email) ? 1 : 0;
  }
  
  public function send()
  {
    date_default_timezone_set('America/New_York');
    $separator = sha1(date('r', time()));
    $this->headers .= "MIME-Version: 1.0\r\n";
    $this->headers .= 'Content-Type: multipart/alternative;boundary="PHP-alt-'.$separator.'"';
    $body = <<<EOT
--PHP-alt-{$separator}
Content-Type: text/plain

$this->body_text

--PHP-alt-{$separator}
Content-Type: text/html

<html>
<head>
<title>$this->subject</title>
</head>
<body>
<style type="text/css">
p {
    margin-top: 1em !important;
    margin-bottom: 1em !important;
}
</style>
$this->body_html
</body>
</html>

--PHP-alt-{$separator}--

EOT;
    return mail( $this->to, $this->subject, $body, $this->headers, $this->parameters );
  }
  
  public function setAttribute( $key, $value )
  {
    $this->$key = $value;
  }

}

?>