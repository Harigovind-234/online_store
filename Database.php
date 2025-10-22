<?php
/**
 * Database connection class using private properties.
 * Connection details are hardcoded here.
 */
class Database {
    // Database connection details
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $dbname = "brewhub";
    public $conn;

    public function __construct(){
        // Establish the database connection
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        
        // Check for connection error
        if($this->conn->connect_error){
            die("Connection Failed: " . $this->conn->connect_error);
        }
    }

    /**
     * Retrieves the mysqli connection object.
     * This method is crucial as it is called by the Cart class constructor.
     * @return mysqli
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Executes a simple SQL query.
     */
    public function query($sql) {
        return $this->conn->query($sql);
    }
}
?>