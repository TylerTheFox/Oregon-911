<?PHP

error_reporting(E_ALL ^ E_NOTICE);
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

require_once("loggedin.php");

$CallIDM = 1;
$CAD911 = array();

// Lifeflight
$sql = "SELECT * FROM `oregon911_cad`.`pdx911_lifeflight` ORDER BY ID";
$result = $db->sql_query($sql);
while ($rows = $result->fetch_assoc()) {
    $CAD911['stadic' . $CallIDM] = array(
        'info' => "Name: " . $rows['NAME'] . " Type: " . $rows['UTYPE'] . " Address: " . $rows['ADDRESS'],
        'lat' => $rows['LAT'],
        'lng' => $rows['LON'],
        'iconW' => 11,
        'iconH' => 11,
        'label' => false,
        'labelname' => '',
        'icon' => "images/MISC/lifeflight.png"
    );
    $CallIDM++;
}

// Calls
$sql = "SELECT * FROM `oregon911_cad`.`pdx911_calls` WHERE county != 'M' ORDER BY ID";
$result = $db->sql_query($sql);
while ($rows = $result->fetch_assoc()) {
    $info_string = '<div>' . '<h1> Call: ' . $rows['callSum'] . '</h1>' . '<h3> Address: ' . $rows['address'] . '</h3>' . '<p>Station: ' . $rows['station'] . '</p>' . '<p> Time: ' . $rows['timestamp'] . '</p>' . '<table style="width:100%;"><tr><th>Unit</th><th>Station</th><th>Dispatched</th><th>Enroute<th>Onscene</th><th>Clear</th>';
    $sql = "SELECT * FROM `oregon911_cad`.`pdx911_units` WHERE GUID='" . $rows['GUID'] . "' and county='" . $rows['county'] . "' and county != 'M'";
    $unit_result = $db->sql_query($sql);
    while ($unit_row = $unit_result->fetch_assoc()) {
        $info_string .= '<tr><th>' . $unit_row['unit'] . '</th><th>' . $unit_row['station'] . '</th><th>' . $unit_row['dispatched'] . '</th><th>' . $unit_row['enroute'] . '<th>' . $unit_row['onscene'] . '</th><th>' . $unit_row['clear'] . '</th></tr>';
    }
    $info_string .= '</table>';
    $info_string .= ('<a href="./units?call=' . $rows['GUID'] . '&county=' . $rows['county'] . '" target="_blank">More Info</a></div>');
    //if ($rows['ID'] != 31682) {
    $CAD911['loc' . $rows['ID']] = array(
        'info' => $info_string,
        'lat' => $rows['lat'],
        'lng' => $rows['lon'],
        'iconW' => 32,
        'iconH' => 37,
        'label' => true,
        'labelname' => $rows['callSum'],
        'icon' => $rows['icon']
    );
    //}
}


echo json_encode($CAD911);

function getDistance($unit, $county, $station, $latTo, $lonTo, $db) {
    $latFrom;
    $lonFrom;

    if ((strpos($unit, 'LF') !== false) OR ( strpos($unit, 'LIFE') !== false)) {
        $sql = "SELECT LAT,LON FROM `oregon911_cad`.`pdx911_lifeflight` WHERE ABBV='$station'";
    } else {
        if ((is_numeric($station)) AND ( !empty($station))) {
            $sql = "SELECT LAT,LON,STATION,ABBV FROM `oregon911_cad`.` pdx911_stations` WHERE STATION='$station' and COUNTY='$county'";
        } elseif (!empty($station)) {
            $sql = "SELECT LAT,LON,STATION,ABBV FROM `oregon911_cad`.`pdx911_stations` WHERE ABBV='$station' and COUNTY='$county'";
        } else {
            return 'unk';
        }
    }

    $result = $db->sql_query($sql);
    while ($row = $result->fetch_assoc()) {
        $latFrom = $row['LAT'];
        $lonFrom = $row['LON'];
    }

    if ((empty($latFrom)) OR ( empty($lonFrom))) {
        return 'unk';
    }

    $pi80 = M_PI / 180;
    $latFrom *= $pi80;
    $lonFrom *= $pi80;
    $latTo *= $pi80;
    $lonTo *= $pi80;

    $r = 6372.797; // mean radius of Earth in km
    $dlat = $latTo - $latFrom;
    $dlon = $lonTo - $lonFrom;
    $a = sin($dlat / 2) * sin($dlat / 2) + cos($latFrom) * cos($latTo) * sin($dlon / 2) * sin($dlon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $km = $r * $c;

//Return KM to miles.
    return round($km * 0.621371, 2);
}

?>