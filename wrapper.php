<?php 

class wrapper {

  public $params;
  public $footer_address = <<<EOT
<p>INFORMS  &nbsp;&#149;&nbsp;  5521 Research Park Drive, Suite 200 (On the campus of University of Maryland, Baltimore County, Hussman Building), Catonsville, MD 21228 USA</p>
<p>phone:  443-757-3500  &nbsp;&#149;&nbsp; 800-4INFORMS (800-446-3676) &nbsp;&#149;&nbsp; fax: 443-757-3515 &nbsp;&#149;&nbsp; email: <a href="mailto:informs@informs.org">informs@informs.org</a></p>
EOT;

  public function __construct( $params = array() ) {
    $this->params = array(
'site_title' => 'INFORMS Online',
'site_url' => '/',
'page_title' => '',
'show_title_bar' => TRUE,
'custom_utility_menu' => '',
'thin' => FALSE,
'nav_items' => array(),
'meta' => array(),
'css' => array(),
'iecss' => array(),
'js' => array(),
'file' => '',
'content' => ''
    );
    foreach( $params as $param => $value) $this->params[$param] = $value;
  }
  
  public function addNavItem( $label, $url ) 
  {
    $this->params['nav_items'][] = "<a href=\"$url\">$label</a>";
  }
  
  public function cssHTML()
  {
    $html = '<style type="text/css">' . PHP_EOL;
    foreach( $this->params['css'] as $css )
    {
      if (isset($css['text']))
      {
        $html .= $css['text'] . PHP_EOL;
      }
      elseif (isset($css['url']))
      {
        $html .= "@import url('$css[url]');" . PHP_EOL;
      }
    }
    $html .= '</style>' . PHP_EOL;
    return $html;
  }
  
  public function iecssHTML()
  {
    $html = '';
    foreach ($this->params['iecss'] as $iecss_item) 
    { 
    
      if(isset($iecss_item['url']))
      {
        $html = "@import url('{$iecss_item['url']}');";;
      }
      
      if (isset($css_item['text']))
      {
        $html = $iecss_item['text'];
      }
      
      $test = 'IE';
      if(isset($iecss_item['test']))
      {
        $test = $iecss_item['test'];
      }
      
      // need to do separate block for each css item to accomodate differing IE version rules
      $html = <<<EOT
<!--[if $test]>
<style type="text/css">
$html
</style>
<![endif]-->
EOT;
      return $html;
    }

    foreach( $this->params['iecss'] as $iecss )
    {
      if (isset($css['text']))
      {
        $html .= $css['text'];
      }
      elseif (isset($css['url']))
      {
        $html .= "@import url('$css[url]');";
      }
    }
    return $html;
  }
  
  public function jsHTML()
  {
    $html = '';
    foreach( $this->params['js'] as $js )
    {
      if (isset($js['text']))
      {
        $html .= '<script type="text/javascript">' . PHP_EOL;
        $html .= $js['text'] . PHP_EOL;
        $html .= '</script>';
      }
      elseif (isset($js['url']))
      {
        $html .= '<script type="text/javascript" src="' . $js['url'] . '"></script>' . PHP_EOL;
      }
    }
    return $html;
  }
  
  public function metaHTML()
  {
    $html = '';
    foreach ($this->params['meta'] as $meta)
    {
    
    }
    return $html;
  }

  public function setPageTitle($value) {
    $this->params['page_title'] = $value;
  }
  
  public function setSiteTitle($value) {
    $this->params['site_title'] = $value;
  }
  
  public function setThin($value) {
    $this->params['thin'] = $value;
  }

  public function printHeader() 
  { 
    
    $meta_html = "";
    foreach ($this->params['meta'] as $key => $value) 
    {
      $metas .= "<meta name=\"$key\" content=\"$value\" />";
    }
    
    $css_html = '';
    foreach ($this->params['css'] as $css_item) 
    {
      if(isset($css_item['url']))
      {
        $css_html .= "@import url('{$css_item['url']}');";
      }
      elseif (isset($css_item['text']))
      {
        $css_html .= $css_item['text'];
      }
    }
    $css_html = <<<EOT
<style type="text/css">
$css_html
</style>
EOT;

    foreach ($this->params['iecss'] as $iecss_item) 
    {
      $iecss_html = '';
      
      if(isset($iecss_item['url']))
      {
        $iecss_html .= "@import url('{$iecss_item['url']}');";;
      }
      
      if (isset($css_item['text']))
      {
        $iecss_html .= $iecss_item['text'];
      }
      
      $test = 'IE';
      if(isset($iecss_item['test']))
      {
        $test = $iecss_item['test'];
      }
      
      // need to do separate block for each css item to accomodate differing IE version rules
      $css_html .= <<<EOT
<!--[if $test]>
<style type="text/css">
$iecss_html
</style>
<![endif]-->
EOT;
    }

    
    $js_html = '';
    foreach ($this->params['js'] as $js_item) 
    {
      if(isset($js_item['url']))
      {
        $js_html .= '<script type="text/javascript" src="'.$js_item['url'].'"></script>' . PHP_EOL;
      }
      elseif (isset($js_item['text']))
      {
        $js_html .= '<script type="text/javascript">' . PHP_EOL . $js_item['text'] . PHP_EOL . '</script>';
      }
    }
    
    $title_banner = '';
    if ($this->params['site_title'] && $this->params['show_title_bar']) $title_banner = <<<EOT
      <div class="siteName">
        <!-- start editable site name bar -->
        <a href="{$this->params['site_url']}">{$this->params['site_title']}</a>
	<!-- end editable site name bar -->
      </div>
EOT;

    $main_nav = '';
    if (count($this->params['nav_items'])) 
    {
      $main_nav = '<div class="module mainnav">';
      foreach ($this->params['nav_items'] as $nav_item) $main_nav .= '<a href="'.$nav_item['url'].'">'.$nav_item['text'].'</a>';
      $main_nav .= '</div>';
    }
  
    $header = <<<EOT
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>            
  <title>{$this->params['site_title']} - {$this->params['page_title']}</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="description" content="Institute for Operations Research and the Management Sciences" />	
  <meta name="keywords" content="INFORMS, research, operations, management, sciences, journal" />
$meta_html
  <link rel="stylesheet" href="https://www.informs.org/design/user_inform/stylesheets/styles.css" type="text/css" />
  <link rel="icon" href="https://common.informs.org/images/favicon.png" />
$css_html
$js_html
</head>

<!-- Complete page area: START -->
	
<body>
  <div class="wrapper non-cms">
    <div class="container">
      <div class="skip"><a href="#content">skip to content</a></div>

EOT;

    if ($this->params['thin']) {
    $header .= <<<EOT
      <div class="module header thin">
        <div class="logo">
          <a href="http://www.informs.org"><span>INFORMS&reg; Online</span><img border="0" 
          src="https://common.informs.org/images/logo-informs_thin.png" alt="INFORMS&reg; Online - Institute 
          for Operations Research and the Management Sciences" 
          width="141" height="34" /></a>
        </div>
        <div class="utilitymenu">
          <ul>
	    <li><a href="http://www.informs.org">Home</a></li>
	    <li><a href="http://www.informs.org/Membership/Join-INFORMS">Join INFORMS</a></li>
	    <li class="no-pipe"><a href="http://www.informs.org/About-INFORMS/News-Room">News Room</a></li>
	  </ul>
        </div>
      </div>
      {$title_banner}
EOT;
    } else {
    $header .= <<<EOT
      <div class="module header">
        <div class="logo">
          <a href="http://www.informs.org"><span>INFORMS&reg; Online</span><img 
          src="https://common.informs.org/images/IOL-header.jpg" alt="INFORMS&reg; Online - Institute 
          for Operations Research and the Management Sciences" 
          width="299" height="76" /></a>
        </div>
        <div class="utilitymenu">
          <ul>
	    <li><a href="http://www.informs.org">Home</a></li>
	    <li><a href="http://www.informs.org/Membership/Join-INFORMS">Join INFORMS</a></li>
	    <li class="no-pipe"><a href="http://www.informs.org/About-INFORMS/News-Room">News Room</a></li>
	    <li class="member no-pipe"><a href="http://www.informs.org/user/login"><span class="login">Member Login</span></a></li>
	  </ul>
	  <form action="https://www.informs.org/Search">
	    <input type="text" name="search" class="searchfield" />
	    <input type="image" src="https://common.informs.org/images/b-search.gif" name="button" class="search-button" alt="Search" />
	  </form>
        </div>
      </div>
      {$title_banner}
      {$main_nav}
EOT;
    }
    $header .= <<<EOT
      <div class="contentwrap nobg">
        <div class="shadowTrans"></div>
        <div class="content homepage">
          <h1>{$this->params['page_title']}</h1>
EOT;
    echo $header;
    
  }
  
  function printFooter() {
    $year = Date( "Y", time());
    $footer = <<<EOT
        </div>
      </div>
      <div class="module footer">
        {$this->footer_address}
	<ul>
	  <li class="first"><a href="http://www.informs.org/About-Informs/Sitemap">Sitemap</a></li>
	  <li><a href="http://www.informs.org/About-Informs/Terms-of-Use">Terms of Use</a></li>
	  <li><a href="http://www.informs.org/About-Informs/Contact-Us">Contact INFORMS</a></li>
	  <li class="last">INFORMS &copy; $year</li>
	</ul>
      </div>
    </div>
  </div>
</body>
</html>
EOT;
    echo $footer;
  }
  
  public function html( $asString = FALSE )
  {
    $title_bar = '';
    if ($this->params['show_title_bar']) $title_bar = <<<EOT
<div class="siteName"><a href="{$this->params['site_url']}">{$this->params['site_title']}</a></div>
EOT;
    if ($this->params['custom_utility_menu'])
    {
      $utility_menu = $this->params['custom_utility_menu'];
    }
    else
    {
      $utility_menu = <<<EOT
<ul>
  <li><a href="http://www.informs.org">Home</a></li>
  <li><a href="http://www.informs.org/Membership/Join-INFORMS">Join INFORMS</a></li>
  <li class="no-pipe"><a href="http://www.informs.org/About-INFORMS/News-Room">News Room</a></li>
</ul>
EOT;
    }
    $year = date( 'Y', time() );
    $html = <<<EOT
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>            
  <title>{$this->params['page_title']}</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="Content-language" content="en-US" />
  <meta name="description" content="Institute for Operations Research and the Management Sciences" />	
  <meta name="keywords" content="INFORMS, research, operations, management, sciences, journal" />
{$this->metaHTML()}
  <link rel="icon" href="https://common.informs.org/images/favicon.png" />
  <style type="text/css">
    @import url('https://www.informs.org/design/user_inform/stylesheets/styles.css');
  </style>
  <!--[if lt IE 9]>
  <style type="text/css">
    @import url('https://www.informs.org/design/user_inform/stylesheets/ie_lt_9.css');
  </style>
  <![endif]-->
{$this->cssHTML()}
{$this->iecssHTML()}
{$this->jsHTML()}
</head>
<body>
  <div class="wrapper non-cms">
    <div class="container">
      <div class="module header thin">
        <div class="logo">
          <a href="http://www.informs.org"><span>INFORMS&reg; Online</span><img 
          src="https://common.informs.org/images/logo-informs_thin.png" alt="INFORMS&reg; Online - Institute 
          for Operations Research and the Management Sciences" border="0" 
          width="141" height="34" /></a>
        </div>
        <div class="utilitymenu">
          $utility_menu
        </div>
      </div>
      $title_bar
      <div class="content">
        {$this->params['content']}
      </div>
      <div class="module footer">
        {$this->footer_address}
	<ul>
	  <li class="first"><a href="http://www.informs.org/About-Informs/Sitemap">Sitemap</a></li>
	  <li><a href="http://www.informs.org/About-Informs/Terms-of-Use">Terms of Use</a></li>
	  <li><a href="http://www.informs.org/About-Informs/Contact-Us">Contact INFORMS</a></li>
	  <li class="last">INFORMS &copy; $year</li>
	</ul>
      </div>
    </div>
  </div>
</body>
</html>
EOT;
    
    if ($asString) 
    {
      return $html;
    }
    else
    {
      echo $html;
      return TRUE;
    }
  }
  
  public function writeContent( $html = "", $mode = "append" )
  {
    switch ($mode)
    {
      case "prepend":
        $this->content = $html . $this->content;
        break;
      case "replace":
        $this->content = $html;
        break;
      case "append":
      default:
        $this->content = $this->content . $html;
    }
  }
  
  static function wrapHTML( $content, $params = array() )
  {
    // default values
    $vars = array(
      'site_title' => "INFORMS OnLine",
      'header_style' => "full",
      'content_type' => "text/html; charset=utf-8",
      'meta' => array(),
      'css' => array(),
      'js' => array()
    );
    
    $meta = '';
    $css = '';
    $js = '';
    
    if (isset( $params['site_id'] ))
    {
      $xml_file_path = '/var/www/common/data/wrapper.xml';
      $xml = simplexml_load_file( $xml_file_path );
      $qry = "/sites/site[@id='test_site']";
      $nodes = $xml->xpath( $qry );
     
      if (count( $nodes ))
      {
        $site = $nodes[0];
        foreach ($vars as $var => $value)
        {
          if (isset($site->$var)) $vars[$var] = $site->$var;
        }
      }
    }
    foreach ($vars as $var => $value)
    {
      if (isset( $params[$var] ))
      {
        $vars[$var] = $params[$var];
      }
    }
    if (isset( $params['page_title'] ))
    {
      $vars['title'] = (($vars['site_title']) ? $vars['site_title'] . " -- " : "") . $params['page_title'];
    }
    
    echo <<<EOT
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>            
  <title>{$vars['title']}</title>
  <meta http-equiv="content-type" content="{$vars['content_type']}" />
  <meta name="Content-language" content="en-US" />
  <meta name="description" content="Institute for Operations Research and the Management Sciences" />	
  <meta name="keywords" content="INFORMS, research, operations, management, sciences, journal" />
  $meta
  <link rel="icon" href="https://common.informs.org/images/favicon.png" />
  <link rel="stylesheet" href="http://www.informs.org/design/user_inform/stylesheets/styles.css" type="text/css" />
  $css
  $js
</head>
<body>
  <div class="wrapper non-cms">
    <div class="container">
      <div class="module header thin">
        <div class="logo">
          <a href="http://www.informs.org"><span>INFORMS&reg; Online</span><img 
          src="https://common.informs.org/images/logo-informs_thin.png" alt="INFORMS&reg; Online - Institute 
          for Operations Research and the Management Sciences" border="0" 
          width="141" height="34" /></a>
        </div>
        <div class="utilitymenu">
          <ul>
	    <li><a href="http://www.informs.org">informs.org</a></li>
            <li><a href="http://www.informs.org/Membership/Join-INFORMS">Join</a></li>
            <li><a href="https://online.informs.org/informsssa/ssaauthmenu.show_top_menu">Self-Service Center</a></li>
            <li class="no-pipe"><a href="https://online.informs.org/informsssa/ecssashop.show_category">Browse Products</a></li>
	  </ul>
        </div>
      </div>
      <div class="contentwrap">
        <div class="shadowTrans"></div>
        <div class="content">
          $content
        </div>
      </div>
      <div class="module footer">
        <p>INFORMS  &nbsp;&#149;&nbsp;  5521 Research Park Drive, Suite 200 (On the campus of University of Maryland, Baltimore County, Hussman Building), Catonsville, MD 21228 USA</p>
	<p>phone:  443-757-3500  &nbsp;&#149;&nbsp; 800-4INFORMS (800-446-3676) &nbsp;&#149;&nbsp; fax: 443-757-3515 &nbsp;&#149;&nbsp; email: <a href="mailto:informs@informs.org">informs@informs.org</a></p>

	<ul>
	  <li class="first"><a href="/About-Informs/Sitemap">Sitemap</a></li>
	  <li><a href="/About-Informs/Terms-of-Use">Terms of Use</a></li>
	  <li><a href="/About-Informs/Contact-Us">Contact INFORMS</a></li>
	  <li class="last">INFORMS &copy; 2012</li>
	</ul>
      </div><!-- end of footer -->
    </div>
  </div>
</body>
</html>
EOT;
    
  }


}

?>
