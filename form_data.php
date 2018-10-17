<?php

class form_data
{

  function __construct() {}
    
  public function checkedHTML( $attribute, $bool = TRUE )
  {
    if (is_null($this->$attribute)) return '';
    $checked = $this->$attribute == $bool;
    return ($checked) ? ' checked="checked" ' : '';
  }
  
  public function fillFromSession( $pg )
  {
    if (isset($_SESSION[$pg]) && isset($_SESSION[$pg]['form_data']))
    {
      // presence of this variable means user is being sent back to form to correct errors
      foreach ($_SESSION[$pg]['form_data'] as $key => $value)
      {
        $this->setAttribute( $key, filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS) );
      }
      return TRUE;
    }
    else
    {
      return FALSE;
    }
  }
  
  public function setGroupList( $field = NULL, $arr = array() )
  {
    if (!$field || !$arr || !is_array($arr)) return NULL;

    $sel_items = array();
    foreach ($arr as $cd => $descr)
    {
      if ($this->$cd) $sel_items[] = $descr;
    }
    $this->setAttribute( $field, implode( "<br />", $sel_items ) );
  }
  
  /*public function formHTML( $field )
  {
    global $pg;
    if (!isset($this->$field) || !is_array($this->$field)) return FALSE;
    $properties = $this->$field;
    $identifier = $properties['identifier'];
    $type = $properties['type'];
    $value = $properties['value'];
    $label = $properties['label'];
    $html = '<tr><td class="fieldlabel">'.$label.':&#160;</td>';
    $html .= '<td class="fieldcontent">';
    $alert = '&#160;<span class="alert">'.$this->msg( $pg, $field ).'</span>';
    switch ($type)
    {
      case 'checkbox':
        $html .= '<input type="checkbox" name="'.$identifier.'" id="'.$identifier.'" value="1" />';
        $html .= '<label for="'.$identifier.'">'.$label.'</label>';
      case 'text':
      default:
        $html .= '<input type="text" name="'.$identifier.'" value="'.$value.'" />'.$alert;
    }
    $html .= '</td></tr>';
    return $html;
  }*/
  
  static function msg( $page, $element )
  {
    if (!($page || $element)) return NULL;
    if (isset($_SESSION[$page]))
    {
      if (isset($_SESSION[$page][$element]))
      {
        if (isset($_SESSION[$page][$element]['msg']))
        {
          $msg = $_SESSION[$page][$element]['msg'];
          $_SESSION[$page][$element]['msg'] = NULL;
          return $msg;
        }
        else
        {
          return FALSE;
        }
      }
      else
      {
        return FALSE;
      }
    }
    else
    {
      return FALSE;
    }
  }
  
  public function radioChecked( $attribute, $bool = TRUE )
  {
    if (is_null($this->$attribute)) return '';
    $checked = $this->$attribute == $bool;
    return ($checked) ? ' checked="checked" ' : '';
  }
  
  /*public function setValue( $field, $value = NULL )
  {
    if (!isset($this->$field)) $this->$field = array( 'identifier' => $field );
    //$properties &= $this->$field;
    $this->$field['value'] = $value;
  }*/
  
  public function setAttribute( $attribute, $value = NULL )
  {
    $this->$attribute = $value;
  }
  
  public function yesNo( $attribute )
  {
    if ($this->$attribute === '1') return "Yes";
    if ($this->$attribute === '0') return "No";
    return "";
    //return ($this->$attribute) ? "Yes" : "No";
  }
  
  public function saveField( $tbl, $fld, $val, $id )
  {
    if (!$id || !$tbl || !$fld || !$val) return FALSE;
    $jps_db = new jps_db();
    mysql_query( "UPDATE $tbl SET $fld='$val' WHERE id=$id;", $jps_db->link );
    return mysql_affected_rows( $jps_db->link );
  }
  
  public function writeFormField( $params = array() )
  {
    $token = 'FLD_' . md5( mt_rand() );
    $fld_val_js = "$('#".$token." #".$params['field']."').val()";
    $html = '<form id="'.$token.'" method="post" action="javascript:return false;">';
    switch ($params['type'])
    {
      case 'text':
      default:
        $html .= '<input type="text" id="' . $params['field'] . '" ';
        $html .= 'value="'.$params['value'].'" /><input type="image" src="images/save-icon.gif" ';
        $html .= "onclick=\"javascript:saveData('$params[table]','$params[field]', $fld_val_js);\" />";
    }
    return $html . '</form>';
  }
  
}

?>