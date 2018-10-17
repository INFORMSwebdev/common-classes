<?php

class ams_odbc_db
{

  public $conn = FALSE;

  function __construct( $dev = FALSE )
  {
    $ini = parse_ini_file('/common/settings/common.ini');
    $conn_id = ( $dev ) ? $ini['ORACLE_CONN_ID_DEV'] : $ini['ORACLE_CONN_ID']; 
    $this->conn = oci_connect( $ini['ORACLE_ODBC_USER'], 
                               $ini['ORACLE_ODBC_PASS'], 
                               $conn_id, 
                               $ini['ORACLE_CHARSET'] );
  }
  
  public function query( $sql )
  {
    $rows = array();
    if ($this->conn !== FALSE)
    {
      $stmt = oci_parse( $this->conn, $sql );
      oci_execute($stmt);
      oci_commit($this->conn);
      while ($row = oci_fetch_array($stmt,OCI_ASSOC+OCI_RETURN_LOBS+OCI_RETURN_NULLS)) $rows[] = $row;
    }
    return $rows;
  }
  
  public function queryColumn( $sql )
  {
    $column = array();
    $rows = $this->query( $sql );
    if (count($rows))
    {
      $key = key($rows[0]);
      foreach ( $rows as $row ) $column[] = $row[$key];
    }
    return $column;
  }
  
  public function queryColumnSafe( $sql, $params )
  {
    $column = array();
    $rows = $this->querySafe( $sql, $params );
    if (count($rows))
    {
      $key = key($rows[0]);
      foreach ( $rows as $row ) $column[] = $row[$key];
    }
    return $column;
  }
  
  public function queryItem( $sql )
  {
    $item = NULL;
    $row = $this->query_row( $sql );
    if (count($row)) 
    {
      reset($row);
      $first_key = key($row);
      $item = $row[$first_key];
    }
    return $item;
  }
  
  public function queryItemSafe( $sql, $params )
  {
    $item = NULL;
    $row = $this->queryRowSafe( $sql, $params );
    if (count($row)) 
    {
      reset($row);
      $first_key = key($row);
      $item = $row[$first_key];
    }
    return $item;
  }
  
  public function queryRow( $sql )
  {
    $row = array();
    $rows = $this->query( $sql );
    if (count($rows)) $row = $rows[0];
    return $row;
  }
  
  public function queryRowSafe( $sql, $params )
  {
    $row = array();
    $rows = $this->querySafe( $sql, $params );
    if (count($rows)) $row = $rows[0];
    return $row;
  }
  
  public function querySafe( $sql, $params, $fetch_style = OCI_ASSOC )
  {
    $rows = array();
    if ($this->conn !== FALSE)
    {
      $stmt = oci_parse( $this->conn, $sql );
      foreach( $params as $key => $param_group )
      {
        list($placeholder, $var[$key]) = $param_group;
        oci_bind_by_name( $stmt, $placeholder, $var[$key] );
      }
      oci_execute( $stmt );
      oci_fetch_all( $stmt, $rows, null, null, OCI_FETCHSTATEMENT_BY_ROW + $fetch_style );
    }
    return $rows;
  }
  
  // LEGACY ALIASES
  
  public function fetch_item( $sql ) { return $this->queryItem( $sql ); }
  public function fetch_row( $sql ) { return $this->queryRow( $sql ); }
  public function fetch_rows( $sql ) { return $this->query( $sql ); }
  public function query_item( $sql ) { return $this->queryItem( $sql ); }
  public function query_row( $sql ) { return $this->queryRow( $sql ); }

  
}

?>