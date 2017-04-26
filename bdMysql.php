<?

/**
 *	MySQLi DataBase Manager
 *
 *	@param	DBServer_		address of the DataBase Server
 *	@param	DBUsername_		username to log in the DataBase
 *  @param  DBPassword_		password to log in the DataBase
 *  @param  DBName_			name of the DataBase
 *  @param  status_			status of the connection with the DataBase
 *  @author Ivan Molera
 */
class bdMysql
{

	private $DBServer_;
	private $DBUsername_;
	private $DBPassword_;
	private $DBName_;
	private $status_;


    /**
     * Constructor, initializes some private variables
     *
     * @return nothing
     */
	function __construct()
	{
		$this->DBServer_ 	= "localhost" ;
		$this->DBUsername_ 	= "root" ;
		$this->DBPassword_ 	= "" ;
		$this->DBName_ 		= "alumnos" ;
	}


    /**
     * Connects with the DB
     *
     * @return status of the connection
     */
	function Connecta()
	{
		$this->status_ = mysqli_connect( $this->DBServer_ , $this->DBUsername_ , $this->DBPassword_,$this->DBName_ );
		RETURN $this->status_;
	}


    /**
     * Disconnect with the DB
     *
     * @return status of the connection
     */
	function Desconnecta()
	{
		$this->status_ = mysqli_close();
		RETURN $this->status_;
	}


    /**
     * Petition of a simple SQL query
     *
     * @param query SQL
     * @return result of the query
     */
	function Query( $query )
	{
		RETURN mysqli_query( $this->status_ , $query ) ;
	}


    /**
     * Petition of a SQL query that returns an array of data
     *
     * @param query SQL
     * @return array result of the query
     */
	function aQuery( $query )
	{
		RETURN @mysqli_fetch_array( mysqli_query( $this->status_ , $query ) );
	}
	
	/**
     * Petition of a SQL query that returns the number of rows affected
     *
     * @param query SQL
     * @return array result of the query
     */
	function numRowsQuery( $query )
	{
		RETURN mysqli_num_rows($this->Query($query));
	}

	/**
     * Petition of a SQL query that returns the number of the last insert Id
     *
     * @param query SQL
     * @return array result of the query
     */
	function lastIdQuery(){
		
		RETURN mysqli_insert_id($this->status_);
		
	}

    /**
     * Petition of a SQL query that returns a JSON object
     *
     * @param query SQL
     * @return result of the query in JSON format
     */
	function jsonQuery( $query )
	{
		// Valores por defecto
		$nbrows = 0;
		$msg = "No results";
		$jsonresult = "{}";

		// Hago la consulta
		$result = $this->Query($query);

		// Si hay resultados
		if($result != null)
		{
			// Si es un boolean la consulta era un INSERT o un UPDATE
			if(is_bool($result)) {
				$msg = "";
				$nbrows = $result;
			}
			else {
				// Número de resultados
				$nbrows = mysqli_num_rows($result);

				// Proceso los resultados de la consulta
				if($nbrows > 0)
				{
					while($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
					{
						$row_set[] = $row;
					}
					$msg = "";
					$jsonresult = json_encode($row_set);
				}
			}
		}

		// Monto la respuesta
		$r = "{".$jsonresult."}";
		
		/*$r = "{".
			 "	\"success\": ".$nbrows.",".
	 		 "	\"msg\": \"".$msg."\",".
  	 		 "	\"data\": ".$jsonresult.
	 		 "}";*/
	 
		RETURN $r;
	}

	// Encodes a YYYY-MM-DD into a MM/DD/YYYY string
	function codeDate ($date) {
		$tab = explode ("-", $date);
		$r = $tab[1]."/".$tab[2]."/".$tab[0];
		return $r;
	}
}

?>
