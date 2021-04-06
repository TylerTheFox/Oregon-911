<?PHP
require_once("config.php");
require_once("queries.php");

class DBConnector 
{
    private $mysqli;

    public function __construct()
    {
        $this->mysqli = new mysqli(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DATABSE);

        /* check connection */
        if ($this->mysqli->connect_errno) 
        {
            die("DB ERROR"); 
        }
    }

    public function __destruct() 
    {
        $this->mysqli->close();
    }
    
    private function ExecRetData($stmt)
    {
        $stmt->execute();
        $result = $stmt->get_result();
        
        //Copy result into a associative array
        $resultArray = $result->fetch_all(MYSQLI_ASSOC);

        return $resultArray;
    }
    
    public function getCallHeader($callNumber, $County, $Type)
    {
        $stmt = $this->mysqli->prepare(SQL_CALL_INFO);
        $stmt->bind_param("ssssss", $callNumber, $County, $Type, $callNumber, $County, $Type);
        
        return $this->ExecRetData($stmt);
    }
    
    public function getCallLog($callNumber, $County)
    {
        $stmt = $this->mysqli->prepare(SQL_CALLLOG_ENTRIES);
        $stmt->bind_param("ss", $callNumber, $County);
        
        return $this->ExecRetData($stmt);
    }
    
    public function getCallUnits($callNumber, $County, $Type)
    {
        $stmt = $this->mysqli->prepare(SQL_CALL_UNITS);
        $stmt->bind_param("sss", $callNumber, $County, $Type);
        
        return $this->ExecRetData($stmt);
    }
    
    public function getCallFlags($callNumber, $County)
    {
        $stmt = $this->mysqli->prepare(SQL_CALL_FLAGS);
        $stmt->bind_param("ss", $callNumber, $County);
        
        return $this->ExecRetData($stmt);
    }
    
    public function getCallChangeLog($callNumber, $County)
    {
        $stmt = $this->mysqli->prepare(SQL_CALL_CHANGELOG);
        $stmt->bind_param("ss", $callNumber, $County);
        
        return $this->ExecRetData($stmt);
    }
    
    public function getCallLastTenByAddress($callNumber, $County, $Address)
    {
        $stmt = $this->mysqli->prepare(SQL_CALL_LASTTEN_ADDRESS);
        $stmt->bind_param("sss", $callNumber, $County, $Address);
        
        return $this->ExecRetData($stmt);
    }
}
?>