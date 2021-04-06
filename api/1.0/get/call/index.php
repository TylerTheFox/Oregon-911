<?PHP
function getIncidentData($callNum, $callCounty, $callType)
{
    $db             = new DBConnector();
    $keys_str       = "Header.Calllog.Units.Flags.ChangeLog.LastTen";
    $keys           = explode('.', $keys_str);
    $IncidentInfo   = array();
    
    $i_info         = &$IncidentInfo;
    
    foreach ($keys as $key) 
    {
       $i_info[$key] = array();
       $i_info = &$i_info[$key];
    }
    unset($i_info);
    
    $callHeaderData = $db->getCallHeader($callNum, $callCounty, $callType);
    if (count($callHeaderData) != 1)
    {
        return null;
    }
    
    $callLogData = $db->getCallLog($callNum, $callCounty);
    
    
    $IncidentInfo['Header'] = $callHeaderData[0];
    
    if (count($callLogData) != 0)
    {
        $IncidentInfo['Calllog'] = $callLogData;
    }
    else
    {
        $IncidentInfo['Calllog'] = null;
    }
    
    $callUnitsData = $db->getCallUnits($callNum, $callCounty, $callType);
    
    if (count($callUnitsData) != 0)
    {
        $IncidentInfo['Units'] = $callUnitsData;
    }
    else
    {
        $IncidentInfo['Units'] = null;
    }
    
    $callFlagsData = $db->getCallFlags($callNum, $callCounty, $callType);
    
    if (count($callFlagsData) != 0)
    {
        $IncidentInfo['Flags'] = $callFlagsData;
    }
    else
    {
        $IncidentInfo['Flags'] = null;
    }
    
    $callChangeLogData = $db->getCallChangeLog($callNum, $callCounty, $callType);
    
    if (count($callChangeLogData) != 0)
    {
        $IncidentInfo['ChangeLog'] = $callChangeLogData;
    }
    else
    {
        $IncidentInfo['ChangeLog'] = null;
    }
    
    $callLastTenData = $db->getCallLastTenByAddress($callNum, $callCounty, $IncidentInfo['Header']['callAddress']);

    if (count($callLastTenData) != 0)
    {
        $IncidentInfo['LastTen'] = $callLastTenData;
    }
    else
    {
        $IncidentInfo['LastTen'] = null;
    }
    
    return json_encode($IncidentInfo);
}

if (!isset($InternalUse))
{
    require_once("../../../../db/database.php");
    
    // Parameters
    if (isset($_GET['call']))
    {
        $G_num = $_GET['call'];
    }

    if (isset($_GET['county']))
    {
        $G_county = $_GET['county'];
    }

    if (isset($_GET['type']))
    {
        $G_type = $_GET['type'];
    }
    
    print(getIncidentData($G_num, $G_county, $G_type));
}
else
{
    require_once("db/database.php");
}
?>