<?php

class ams_db
{

  var $conn = FALSE;
  var $odbc_conn = FALSE;

  function __construct( $dev = FALSE )
  {
    $ini = parse_ini_file('/common/settings/common.ini');
    $conn_id = ( $dev ) ? $ini['ORACLE_CONN_ID_DEV'] : $ini['ORACLE_CONN_ID']; 
    $this->conn = oci_connect( $ini['ORACLE_USER'], 
                               $ini['ORACLE_PASS'], 
                               $conn_id, 
                               $ini['ORACLE_CHARSET']);
  }
  
  public function amsGetUserBio( $userID )
  {
    if ($this->conn !== FALSE)
    {
      $sql = "begin CENSSAAPILIB.GET_USER_BIO_INFO(:custid, :prefix, :first, :middle, :last, :suffix, :display_name, :bio_text, :thumbnail_format, :thumbnail_mime_type, :thumbnail, :photo_format, :photo_mime_type, :photo, :success, :msgtext); end; ";
      
      $stmt = oci_parse($this->conn,$sql);
      $vcustid = $userID;
      oci_bind_by_name($stmt,':custid',$vcustid,10);
      oci_bind_by_name($stmt,':prefix',$vprefix,10);
      oci_bind_by_name($stmt,':first',$vfirst,30);
      oci_bind_by_name($stmt,':middle',$vmiddle,20);
      oci_bind_by_name($stmt,':last',$vlast,30);
      oci_bind_by_name($stmt,':suffix',$vsuffix,20);
      oci_bind_by_name($stmt,':display_name',$vdisplay,60);
      $vbio = oci_new_descriptor($this->conn, OCI_D_LOB);
      oci_bind_by_name($stmt,':bio_text',$vbio,-1,OCI_B_CLOB);
      oci_bind_by_name($stmt,':thumbnail_format',$vthumbnailformat,15);
      oci_bind_by_name($stmt,':thumbnail_mime_type',$vthumbnailmimetype,60);
      $vthumbnail = oci_new_descriptor($this->conn, OCI_D_LOB);
      oci_bind_by_name($stmt,':thumbnail',$vthumbnail,-1,OCI_B_BLOB);
      oci_bind_by_name($stmt,':photo_format',$vphotoformat,15);
      oci_bind_by_name($stmt,':photo_mime_type',$vphotomimetype,60);
      $vphoto = oci_new_descriptor($this->conn, OCI_D_LOB);
      oci_bind_by_name($stmt,':photo',$vphoto,-1,OCI_B_BLOB);
      oci_bind_by_name($stmt,':success',$vsuccess,10);
      oci_bind_by_name($stmt,':msgtext',$vmsgtext,32767);
      
      oci_execute($stmt);
      oci_commit($this->conn);
      
      $arrCustInfo['prefix'] = $vprefix;
      $arrCustInfo['first'] = $vfirst;
      $arrCustInfo['middle'] = $vmiddle;
      $arrCustInfo['last'] = $vlast;
      $arrCustInfo['suffix'] = $vsuffix;
      $arrCustInfo['display_name'] = $vdisplay;
      $arrCustInfo['bio_text'] = $vbio;
      $arrCustInfo['thumbnail_format'] = $vthumbnailformat;
      $arrCustInfo['thumbnail_mime_type'] = $vthumbnailmimetype;
      $arrCustInfo['thumbnail'] = $vthumbnail;
      $arrCustInfo['photo_format'] = $vphotoformat;
      $arrCustInfo['photo_mime_type'] = $vphotomimetype;
      $arrCustInfo['photo'] = $vphoto;
      $arrCustInfo['success'] = $vsuccess;
      $arrCustInfo['msgtext'] = $vmsgtext;
      
      return $arrCustInfo;
    }
    else
    {
      return FALSE;
    }
  }
  
  public function amsGetUserContactInfo( $userID )
  {
    if ($this->conn !== FALSE)
    {
      $sql = "begin CENSSAAPILIB.GET_USER_CONTACT_INFO(:custid, :custinfo, :success, :msgtext); end; ";
      $stmt = oci_parse($this->conn,$sql);
      $vcustid = $userID;
      
      oci_bind_by_name($stmt,':custid',$vcustid,10);
      $ccustinfo = oci_new_cursor($this->conn);
      oci_bind_by_name($stmt,':custinfo',$ccustinfo,-1,OCI_B_CURSOR);
      oci_bind_by_name($stmt,':success',$vsuccess,10);
      oci_bind_by_name($stmt,':msgtext',$vmsgtext,32767);
      
      oci_execute($stmt);
      oci_execute($ccustinfo);
      oci_commit($this->conn);
      $arrCustInfo = oci_fetch_assoc($ccustinfo);
      
      return $arrCustInfo;
    }
    else
    {
      return FALSE;
    }
  }
  
  public function amsGetMemberships( $userID )
  {
    $arrMemInfo = array();
    if ($this->conn !== FALSE)
    {
      //SQL (before bindings)
      $sql = "begin MEMSSAAPILIB.GET_USER_MEMBERSHIP(:custid, :meminfo, :success, :msgtext); end; ";
      
      //PARSE (connection and statement)
      $stmt = oci_parse($this->conn,$sql);
      
      $vcustid = $userID;
      
      //init variables (in/out) and binding
      oci_bind_by_name($stmt,':custid',$vcustid,10);
      // Bind the cursor resource to the Oracle argument
      $cmeminfo = oci_new_cursor($this->conn);
      oci_bind_by_name($stmt,':meminfo',$cmeminfo,-1,OCI_B_CURSOR);
      oci_bind_by_name($stmt,':success',$vsuccess,10);
      oci_bind_by_name($stmt,':msgtext',$vmsgtext,32767);
      
      //execute statment
      oci_execute($stmt);
      
      // Execute the cursor
      oci_execute($cmeminfo);
      
      // Everything OK so commit
      oci_commit($this->conn);
      
      // return an array of records
      while ($entry = oci_fetch_assoc($cmeminfo)) 
      {
        $arrMemInfo[] = $entry;
      }
      
      //oci_close($this->conn);
      
      return $arrMemInfo;
    }
    else
    {
      return FALSE;
    }
  }
  
  
  public function amsIsValidINFORMSMember( $ams_id )
  {
    global $errors;
    $sql = "begin MEMSSAAPILIB.GET_USER_MEMBERSHIP(:custid, :meminfo, :success, :msgtext); end; ";
    //oci_free_statement($stmt);
    $stmt = oci_parse( $this->conn, $sql );
    oci_bind_by_name( $stmt, ':custid', $ams_id, 10 );
    $cmeminfo = oci_new_cursor( $this->conn );
    oci_bind_by_name( $stmt, ':meminfo', $cmeminfo, -1, OCI_B_CURSOR );
    oci_bind_by_name( $stmt, ':success', $vsuccess, 10 );
    oci_bind_by_name( $stmt, ':msgtext', $vmsgtext, 32767 );
    
    oci_execute($stmt);
    oci_execute($cmeminfo);
    oci_commit($this->conn);   
    $memberships = array();
    $active = FALSE;
    while ($membership = oci_fetch_assoc($cmeminfo)) 
    {
      $subgroup_id = $membership['SUBGROUP_ID'];
      $status_cd = $membership['STATUS_CD'];
      if ($subgroup_id == "INFORMS" && $status_cd == "ACTIVE") return TRUE;
      if ($subgroup_id == "INFORMS_STAFF" && $status_cd == "ACTIVE") return TRUE;
    }
    /*$errors[] = "<p class=\"warning\">You are in our member database do not appear 
      to be an active INFORMS member.</p>";*/
    return FALSE;
  }
  
  public function amsLogUserInByLastName( $lname, $ams_id )
  {
    
    if ($this->conn !== FALSE)
    {
      $sql = "begin ssassis.validateLoginByLnmCustID(:P_LAST_NM, 
        :P_LOGIN_SERNO, :P_CUST_ID, :P_FIRST_NM, :P_HASH_CD, :P_EMAIL, :P_AUTH_ROLE, 
        :P_RESULT, :P_RESULT_MSG); end;";
      $stmt = oci_parse( $this->conn, $sql );
      // bind inputs
      oci_bind_by_name( $stmt, ':P_LAST_NM', $lname, 200 );
      // bind outputs
      oci_bind_by_name( $stmt, ':P_LOGIN_SERNO', $vloginserno, 30 );
      oci_bind_by_name( $stmt, ':P_CUST_ID', $ams_id, 10 );
      oci_bind_by_name( $stmt, ':P_FIRST_NM', $vfirst, 30 );
      oci_bind_by_name( $stmt, ':P_HASH_CD', $vsessionid, 30 );
      oci_bind_by_name( $stmt, ':P_EMAIL', $vemail, 2000 );
      oci_bind_by_name( $stmt, ':P_AUTH_ROLE', $vroles, 32767 );
      oci_bind_by_name( $stmt, ':P_RESULT', $vsuccess, 10 );
      oci_bind_by_name( $stmt, ':P_RESULT_MSG', $vmsgtext, 32767 );

      oci_execute( $stmt );
      oci_commit( $this->conn );
      if ($vsuccess === "TRUE")
      {
        $active = true;//$this->amsIsValidINFORMSMember( $ams_id ) || in_array( "INFORMS_STAFF", preg_split( '/,/', $vroles ) );
        if ($active) 
        {
          // set AMS authentication cookies
	  setcookie("ssisid", $vsessionid, 0, '/', '.informs.org');
	  setcookie("ssalogin", "yes", 0, '/', '.informs.org');
	  setcookie("p_cust_id", $ams_id, 0, '/', '.informs.org');
	  
	  // some systems might be using all-caps vars
	  setcookie("SSISID", $vsessionid, 0, '/', '.informs.org');
	  setcookie("SSALOGIN", "yes", 0, '/', '.informs.org');
	  setcookie("P_CUST_ID", $ams_id, 0, '/', '.informs.org');

	  $arrCustInfo['loginserno'] = $vloginserno;
	  $arrCustInfo['ams_id'] = $ams_id;
	  $arrCustInfo['first'] = $vfirst;
	  $arrCustInfo['last'] = $lname;
	  $arrCustInfo['session_id'] = $vsessionid;
	  $arrCustInfo['email'] = $vemail;
	  $arrCustInfo['roles'] = $vroles;
	  $arrCustInfo['success'] = $vsuccess;
	  $arrCustInfo['msgtext'] = $vmsgtext;

          $_SESSION['ams_auth'] = TRUE;
          
          return $arrCustInfo;
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
  
  public function amsLogUserIn( $login, $password )
  {
    if ($this->conn !== FALSE)
    {
      $sql = "begin SSASSIS.VALIDATELOGINBYUSERPASS(:login, :password, :loginserno, :custid, :first, :last, :hashcd, :email, :roles, :success, :msgtext); end; ";
      $stmt = oci_parse( $this->conn, $sql );
      // bind inputs
      oci_bind_by_name( $stmt, ':login', $login, 200 );
      oci_bind_by_name( $stmt, ':password', $password, 30 );
      // bind outputs
      oci_bind_by_name( $stmt, ':loginserno', $vloginserno, 20 );
      oci_bind_by_name( $stmt, ':custid', $vcustid, 10 );
      oci_bind_by_name( $stmt, ':first', $vfirst, 30 );
      oci_bind_by_name( $stmt, ':last', $vlast, 30 );
      oci_bind_by_name( $stmt, ':hashcd', $vsessionid, 30 );
      oci_bind_by_name( $stmt, ':email', $vemail, 2000 );
      oci_bind_by_name( $stmt, ':roles', $vroles, 32767 );
      oci_bind_by_name( $stmt, ':success', $vsuccess, 10 );
      oci_bind_by_name( $stmt, ':msgtext', $vmsgtext, 32767 );

      oci_execute( $stmt );
      oci_commit( $this->conn );
      
      if ($vsuccess === "TRUE")
      {

          // set AMS authentication cookies
	  setcookie("ssisid", $vsessionid, 0, '/', '.informs.org');
	  setcookie("ssalogin", "yes", 0, '/', '.informs.org');
	  setcookie("p_cust_id", $vcustid, 0, '/', '.informs.org');
	  
	  // some systems might be using all-caps vars
	  setcookie("SSISID", $vsessionid, 0, '/', '.informs.org');
	  setcookie("SSALOGIN", "yes", 0, '/', '.informs.org');
	  setcookie("P_CUST_ID", $vcustid, 0, '/', '.informs.org');

          $arrCustInfo['login'] = $login;
	  $arrCustInfo['loginserno'] = $vloginserno;
	  $arrCustInfo['ams_id'] = $vcustid;
	  $arrCustInfo['first'] = $vfirst;
	  $arrCustInfo['last'] = $vlast;
	  $arrCustInfo['session_id'] = $vsessionid;
	  $arrCustInfo['email'] = $vemail;
	  $arrCustInfo['roles'] = $vroles;
	  $arrCustInfo['success'] = $vsuccess;
	  $arrCustInfo['msgtext'] = $vmsgtext;
          
          return $arrCustInfo;
	
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
  
  function amsLogUserOut( $p_hash_cd = FALSE )
  {
    if (!$p_hash_cd)
    {
      if (!isset($_COOKIE['ssisid']))
      {
        return false;
      }
      else
      {
        $p_hash_cd = $_COOKIE['ssisid'];
      }
    }
    if ($this->conn !== FALSE)
    {
      $sql = "begin SSASSIS.INVALIDATELOGINBYHASH(:p_hash_cd, :p_result, :p_result_msg); end; ";
        
      //PARSE (connection and statement)
      $stmt = oci_parse( $this->conn, $sql );
      
      //init variables (in/out) and binding
      oci_bind_by_name($stmt, ':p_hash_cd', $p_hash_cd, 30);
      oci_bind_by_name($stmt, ':p_result', $vsuccess, 10);
      oci_bind_by_name($stmt, ':p_result_msg', $vmsgtext, 32767);
      //execute statment
      oci_execute($stmt);
      // Everything OK so commit
      oci_commit($this->conn);
      
      if ($vsuccess === "TRUE")
      {
        setcookie( 'ssisid', '', time() - 42000, '/', '.informs.org' );
        setcookie( 'p_cust_id', '', time() - 42000, '/', '.informs.org' );
        setcookie( 'SSISID', '', time() - 42000, '/', '.informs.org' );
        setcookie( 'P_CUST_ID', '', time() - 42000, '/', '.informs.org' );
        return TRUE;
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
  
  
  function amsValidateLoginByHash( $p_hash_cd )
  {
    if ($this->conn !== FALSE)
    {
      $sql = "begin SSASSIS.VALIDATELOGINBYHASH(:p_hash_cd, :p_login_serno, :p_cust_id, :p_first_nm, :p_last_nm, :p_login_id, :p_email, :p_auth_role, :p_result, :p_result_msg); end; ";
      
      //PARSE (connection and statement)
      $stmt = oci_parse( $this->conn, $sql );
      
      //init variables (in/out) and binding
      oci_bind_by_name( $stmt, ':p_hash_cd',     $p_hash_cd,   30    );
      oci_bind_by_name( $stmt, ':p_login_serno', $vloginserno, 20    );
      oci_bind_by_name( $stmt, ':p_cust_id',     $vcustid,     10    );
      oci_bind_by_name( $stmt, ':p_first_nm',    $vfirst,      30    );
      oci_bind_by_name( $stmt, ':p_last_nm',     $vlast,       30    );
      oci_bind_by_name( $stmt, ':p_login_id',    $vloginid,    30    );
      oci_bind_by_name( $stmt, ':p_email',       $vemail,      2000  );
      oci_bind_by_name( $stmt, ':p_auth_role',   $vroles,      32767 );
      oci_bind_by_name( $stmt, ':p_result',      $vsuccess,    10    );
      oci_bind_by_name( $stmt, ':p_result_msg',  $vmsgtext,    32767 );
      
      //execute statment
      oci_execute($stmt);
      // Everything OK so commit
      oci_commit($this->conn);

      if ($vsuccess === "TRUE")
      {
          $arrCustInfo = array();
	  $arrCustInfo['session_id'] = $p_hash_cd;
          $arrCustInfo['loginserno'] = $vloginserno;
	  $arrCustInfo['ams_id'] = $vcustid;
	  $arrCustInfo['first'] = $vfirst;
	  $arrCustInfo['last'] = $vlast;
	  $arrCustInfo['login'] = $vloginid;
	  $arrCustInfo['email'] = $vemail;
	  $arrCustInfo['roles'] = $vroles;
	  $arrCustInfo['success'] = $vsuccess;
	  $arrCustInfo['msgtext'] = $vmsgtext;
	
	  $_SESSION['ams_auth'] = TRUE;
	  return $arrCustInfo;
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
  
  function createUser( $params = array() ) {
    $cust_id = NULL;
    $vars = array(
      'p_first_nm',
      'p_middle_nm',
      'p_last_nm',
      'p_degree_nm', 
      'p_informal_nm', 
      'p_company_nm',
      'p_addr_ty', 
      'p_street1', 
      'p_street2', 
      'p_city_nm', 
      'p_state_cd', 
      'p_postal_cd', 
      'p_country_cd', 
      'p_phone_ty', 
      'p_phone', 
      'p_cyber_ty', 
      'p_cyber_txt', 
      'p_birthdate', 
      'p_gender', 
    );
    if (!(isset($params['p_first_nm']) && isset($params['p_last_nm']))) return false;
    $sql = <<<EOT
begin 
cencustmastapi.insert_customer( 
    P_CONTEXT => NULL, 
    P_FIRST_NM => :p_first_nm, 
    P_MIDDLE_NM => :p_middle_nm, 
    P_LAST_NM => :p_last_nm, 
    P_DEGREE_NM => :p_degree_nm, 
    P_INFORMAL_NM => :p_informal_nm,
    P_COMPANY_NM => :p_company_nm,
    P_ADDR_TY => :p_addr_ty, 
    P_STREET1 => :p_street1, 
    P_STREET2 => :p_street2, 
    P_CITY_NM => :p_city_nm, 
    P_STATE_CD => :p_state_cd, 
    P_POSTAL_CD => :p_postal_cd, 
    P_COUNTRY_CD => :p_country_cd, 
    P_PHONE_TY => :p_phone_ty, 
    P_PHONE => :p_phone, 
    P_CYBER_TY => :p_cyber_ty, 
    P_CYBER_TXT => :p_cyber_txt, 
    P_BIRTHDATE => :p_birthdate, 
    P_GENDER => :p_gender, 
    P_CUST_ID => :v_cust_id); 
end; 
EOT;
    
    foreach ($vars as $var) if (!isset($params[$var])) $params[$var] = NULL;
    
    $stmt = oci_parse($this->conn, $sql);
    oci_bind_by_name( $stmt, ':p_first_nm', $params['p_first_nm'], 30);
    oci_bind_by_name( $stmt, ':p_middle_nm', $params['p_middle_nm'], 20);
    oci_bind_by_name( $stmt, ':p_last_nm', $params['p_last_nm'], 30);
    oci_bind_by_name( $stmt, ':p_degree_nm', $params['p_degree_nm'], 30);
    oci_bind_by_name( $stmt, ':p_informal_nm', $params['p_informal_nm'], 30);
    oci_bind_by_name( $stmt, ':p_company_nm', $params['p_company_nm'], 256);
    oci_bind_by_name( $stmt, ':p_addr_ty', $params['p_addr_ty'], 10); 
    oci_bind_by_name( $stmt, ':p_street1', $params['p_street1'], 60);
    oci_bind_by_name( $stmt, ':p_street2', $params['p_street2'], 60);
    oci_bind_by_name( $stmt, ':p_city_nm', $params['p_city_nm'], 30); 
    oci_bind_by_name( $stmt, ':p_state_cd', $params['p_state_cd'], 3);
    oci_bind_by_name( $stmt, ':p_postal_cd', $params['p_postal_cd'], 15);
    oci_bind_by_name( $stmt, ':p_country_cd', $params['p_country_cd'], 3);
    oci_bind_by_name( $stmt, ':p_phone_ty', $params['p_phone_ty'], 10);
    oci_bind_by_name( $stmt, ':p_phone', $params['p_phone'], 30);
    oci_bind_by_name( $stmt, ':p_cyber_ty', $params['p_cyber_ty'], 10);
    oci_bind_by_name( $stmt, ':p_cyber_txt', $params['p_cyber_txt'], 2000);
    oci_bind_by_name( $stmt, ':p_birthdate', $params['p_birthdate'], 30);
    oci_bind_by_name( $stmt, ':p_gender', $params['p_gender'],  30);
    oci_bind_by_name($stmt, ':v_cust_id', $cust_id, 10);
    //foreach ($vars as $key => $len) {
     // $value = (isset($params[$key])) ? $params[$key] : NULL;
      //oci_bind_by_name($stmt, ':'.$key, $value, 30);
    //}
    oci_execute($stmt);
    oci_commit($this->conn);
    return $cust_id;
  }
  
  public function amsGetSubscriptions( $userID )
  {
	if ($this->conn !== FALSE)

	  $sql = "begin PUBSSAAPILIB.GET_USER_PACKAGES(:custid, :subinfo, :success, :msgtext); end; ";
	  $stmt = oci_parse( $this->conn, $sql );
	  
	  $vcustid = $userID;
	  oci_bind_by_name($stmt,':custid', $vcustid, 10);
	  $csubinfo = oci_new_cursor($this->conn );
	  oci_bind_by_name($stmt,':subinfo', $csubinfo, -1, OCI_B_CURSOR);
	  oci_bind_by_name($stmt,':success', $vsuccess, 10);
	  oci_bind_by_name($stmt,':msgtext', $vmsgtext, 32767);
	  
	  oci_execute($stmt);
	  oci_execute($csubinfo);
	  
	  while ($entry = oci_fetch_assoc($csubinfo)) 
	  {
		$arrSubInfo[] = $entry;
	  }
	  
	  return $arrSubInfo;

  }
  
  public function updateAttribute( $params ) {
    /*
    =============================================================================
    Name		IN / OUT	Type		Required	Default
    p_cust_id		IN		VARCHAR2(10)	Y	 
    p_attribute_ty	IN		VARCHAR2(30)	Y 
    p_curr_attrcode	IN		VARCHAR2(30)	N		NULL
    p_new_attrcode	IN		VARCHAR2(30)	N		NULL
    p_curr_cvar		IN		VARCHAR2(4000)	N		NULL
    p_new_cvar		IN		VARCHAR2(4000)	N		NULL
    p_curr_nvar		IN		NUMBER(22,6)	N		NULL
    p_new_nvar		IN		NUMBER(22,6);	N		NULL
    p_curr_dvar		IN		DATE		N		NULL
    p_new_dvar		IN		DATE		N		NULL
    p_opt_lock_code	IN		NUMBER		Y
    p_success		OUT		VARCHAR2(32767)	N	 
    p_msg_text		OUT		VARCHAR2(32767)	N	 
 
    This procedure handles the insert/update/delete for the attributes. Each call only processes one record.
    Assumptions:
    The following parameters form a primary key: p_cust_id,_attribute_ty, (p_new_attrcode or p_curr_attrcode). 
    The attrcode depends on if it is an update, insert, or delete. This is outlined below.
    A checksum code must be obtained before a record can be updated or deleted. It can be obtained by first 
    querying for the record that is to be updated or deleted. This can be done calling procedure 
    “cencustattrdtlselapi.get_attributes”.  This checksum which would be submitted as the parameter 
    p_opt_lock_code is needed in order to prevent this procedure from modifying the record if it has been 
    changed by another process while working on it.
    An insert will be performed if :
                     p_opt_lock_code IS NULL
                    AND p_curr_attrcode IS NULL
                    AND p_curr_cvar IS NULL
                    AND p_curr_nvar IS NULL
                    AND p_curr_dvar IS NULL
                    AND p_attribute_ty IS NOT NULL
                    AND p_new_attrcode IS NOT NULL
    A delete will be performed if:
                    p_new_attrcode IS NULL
                    AND p_new_cvar IS NULL
                    AND p_new_nvar IS NULL
                    AND p_new_dvar IS NULL
                    AND p_opt_lock_code IS NOT NULL
    An update will be performed if:
                     p_new_attrcode IS NOT NULL
                     AND p_opt_lock_code IS NOT NULL
    All parameters are case sensitive.
    If the procedure completes without any errors, "SUCCESS" will be passed back in the p_success parameter 
    If the process fails then "FAILURE" will be passed back in the p_success parameter. A message will be passed 
    back in the p_mesg_text to report the description of the failure.
    =============================================================================
    */
  
  
  }

}

?>
