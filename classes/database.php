<?php
/**
 * @Database
 * 
 * Class for query-ing database.
 *
 * @author Alex Rosu
 * @tag value
 */
 
class Database {
	private $hostname;
	private $database;
	private $username;
	private $password;
	
	private $connection;
	
	public function __construct( $host, $name, $user, $pass) {
		// store variables
		$this->hostname = $host;
		$this->database = $name;
		$this->username = $user;
		$this->password = $pass;
		
		// setup connection
		$this->connection = mysql_connect( $this->hostname, $this->username , $this->password);
		
		// check for valid connection
		if(!$this->connection) {
			die ('Could not connect: ' . mysql_error());
		} else {
			mysql_select_db( $this->database, $this->connection );
			mysql_query("SET NAMES UTF8");
		}
	}
	
	public function __destruct() {
		mysql_close( $this->connection );
	}
	
	// return rows of query
	public function get_rows( $query ) {
		$result = $this->query( $query );
				
		return mysql_num_rows( $result );
	}
	
	// get all rows
	public function get( $query ) {
		$rows = array(); // array to hold rows
		$result = $this->query( $query );
		
		// parse through rows
		while( $row = mysql_fetch_assoc( $result ) ) {
			$rows[] = $row;
	 	}
	 	
	 	// check if lone result
	 	if ( count($rows) == 1 ) {
	 		$rows = $rows[0];
	 	}
	 	
	 	// 
		return $rows;
	}
	
	// query database
	public function query( $query ) {
		return mysql_query( $query, $this->connection );
	}	
}
?>