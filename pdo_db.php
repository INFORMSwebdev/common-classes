<?php

class pdo_db
{

  public $handle;

  /**
  * Construct PDO object using parameters passed from ini file
  * @param $ini		string	path to ini file
  * @param $block	string	ini block label (optional)
  */
  function __construct( $ini, $block = NULL)
  {
    try
    {
      $ini = parse_ini_file( $ini, (($block) ? TRUE : FALSE) );
      $ini = ($block) ? $ini[$block] : $ini ;
      $dsn = 'mysql:dbname=' . $ini['db_name'] . ';host=' . $ini['db_hostname'];
      $username = $ini['db_username'];
      $password = $ini['db_password'];
      $this->handle = new PDO( $dsn, $username, $password );
    } 
    catch (PDOException $e) 
    {
      die('Connection failed: ' . $e->getMessage());
    }
  }
  
  /**
  * Run a query and return affected rows count
  * @param $sql	string
  * @returns int
  */
  public function exec( $sql )
  {
    $sth = $this->handle->prepare( $sql );
    $sth->execute();
    return $sth->rowCount();
  }
  
  /**
  * Run a safe query and return affected rows count
  * @param $sql	string
  * @param $params array
  * @returns int
  */
  public function exec_safe( $sql, $params )
  {
    $sth = $this->handle->prepare( $sql );
    $i = 0;
    foreach( $params as $param_group )
    {
      $i++;
      $test = 'var'.$i;
      list($placeholder, $$test, $datatype) = $param_group;
      $sth->bindValue( $placeholder, $$test, $datatype );
    }
    $sth->execute();
    return $sth->rowCount();
  }
  
  public static function getPDOType( $var ) {
    $type = 0;
    switch( gettype( $var ) ) {
      case "integer":
        $type = PDO::PARAM_INT;
        break;
      case "NULL":
        $type = PDO::PARAM_NULL;
        break;
      case "string":
      default:
        $type = PDO::PARAM_STR;
    }
  }
  
  /**
  * Run a query and return result as an array
  * @param $sql	string
  * @param $fetch_style int
  * @returns array
  */
  public function query( $sql, $fetch_style = PDO::FETCH_ASSOC )
  {
    $result = array();
    $sth = $this->handle->prepare( $sql );
    $sth->execute();
    $result = $sth->fetchAll( $fetch_style );
    return $result;
  }
  
  public function query_column( $sql ) {
    $result = array();
    $rows = $this->query( $sql, PDO::FETCH_NUM );
    $flat_array = array();
    foreach( $rows as $row ) $result[] = $row[0];
    return $result;
  }
  
  public function query_column_safe( $sql, $params ) {
    $result = array();
    $rows = $this->query_safe( $sql, $params, PDO::FETCH_NUM );
    $flat_array = array();
    foreach( $rows as $row ) $result[] = $row[0];
    return $result;
  }
  
  /**
  * Run a query and return only one item
  * @param $sql	string
  * @param $fetch_style int
  * @returns mixed
  */
  public function query_item( $sql )
  {
    $result = null;
    $row = $this->query_row( $sql, PDO::FETCH_NUM );
    if (isset($row[0])) $result = $row[0];
    return $result;
  }
  
  /**
  * Run a safe query and return only one item
  * @param $sql	string
  * @param $params array (placeholder, var, datatype)
  * @returns mixed
  */
  
  public function query_item_safe( $sql, $params )
  {
    $result = null;
    $row = $this->query_row_safe( $sql, $params, PDO::FETCH_NUM );
    if (isset($row[0])) $result = $row[0];
    return $result;
  }
  
  /**
  * Run a query that returns only one row
  * @param $sql	string
  * @param $fetch_style int
  * @returns array
  */
  public function query_row( $sql, $fetch_style = PDO::FETCH_ASSOC )
  {
    $result = array();
    $rows = $this->query( $sql, $fetch_style );
    if (isset($rows[0])) $result = $rows[0];
    return $result;
  }
  
  /**
  * Run a safe query that returns only one row
  * @param $sql	string
  * @param $params array [array(placeholder, var, datatype)]
  * @param $fetch_style int
  * @returns array
  */
  public function query_row_safe( $sql, $params, $fetch_style = PDO::FETCH_ASSOC )
  {
    $result = array();
    $rows = $this->query_safe( $sql, $params, $fetch_style );
    if (isset($rows[0])) $result = $rows[0];
    return $result;
  }
  
  /**
  * Run a query that safely binds variables and return result as an array
  * @param $sql string
  * @param $params array
  * @param $fetch_style int
  */
  public function query_safe( $sql, $params, $fetch_style = PDO::FETCH_ASSOC )
  {
    $result = array();
    $sth = $this->handle->prepare( $sql );
    foreach( $params as $param_group )
    {
      list($placeholder, $var, $datatype) = $param_group;
      $sth->bindValue( $placeholder, $var, $datatype );
    }
    $sth->execute();
    $result = $sth->fetchAll( $fetch_style );
    return $result;
  }
  
  // ALIASES
  public function execSafe( $sql, $params ) { return $this->exec_safe( $sql, $params ); }
  public function queryColumn( $sql ) { return $this->query_column( $sql ); }
  public function queryColumnSafe( $sql, $params ) { return $this->query_column_safe( $sql, $params ); }
  public function queryItem( $sql ) { return $this->query_item( $sql ); }
  public function queryItemSafe( $sql, $params ) { return $this->query_item_safe( $sql, $params ); }
  public function queryRow( $sql, $fetch_style = PDO::FETCH_ASSOC ) { return $this->query_row( $sql, $fetch_style ); }
  public function queryRowSafe( $sql, $params, $fetch_style = PDO::FETCH_ASSOC ) { return $this->query_row_safe( $sql, $params, $fetch_style ); }
  public function querySafe( $sql, $params, $fetch_style = PDO::FETCH_ASSOC ) { return $this->query_safe( $sql, $params, $fetch_style ); }

}

?>