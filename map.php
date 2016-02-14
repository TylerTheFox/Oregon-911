<?PHP

if (!$_GET['cad'] == "true") {
    exit;
}

error_reporting(E_ALL ^ E_NOTICE);
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

require_once("database.php");

$CallIDM = 1;
$CAD911 = array();

$load = sys_getloadavg();
if (!($load[0] > 80)) {
// Stations
    $sql = "SELECT * FROM `oregon911_cad`.`pdx911_stations` WHERE county != 'M' ORDER BY ID";
    $result = $db->sql_query($sql);
    while ($rows = $result->fetch_assoc()) {
        if ($rows['ABBV'] != "UNK") {
            $station = $rows['ABBV'];
        } else {
            $station = $rows['STATION'];
        }

        /* $OUTPUT = '';
          $OUTPUT .= '<table style="width:100%;">';
          $OUTPUT .= '<tr><th>Date</th><th>Call #</th><th>Call Type</th><th>Address</th></tr>';
          $sql = "SELECT `oregon911_cad`.`pdx911_archive`.* FROM `oregon911_cad`.`pdx911_units` JOIN `oregon911_cad`.`pdx911_archive` ON `oregon911_cad`.`pdx911_units`.GUID = `oregon911_cad`.`pdx911_archive`.GUID AND `oregon911_cad`.`pdx911_units`.county = `oregon911_cad`.`pdx911_archive`.county LEFT JOIN `oregon911_cad`.`pdx911_stations` AS S ON `oregon911_cad`.`pdx911_units`.station = S.ABBV and `oregon911_cad`.`pdx911_units`.county = S.county WHERE onscene != '00:00:00' AND enroute != '00:00:00' AND `oregon911_cad`.`pdx911_units`.county = '" . $rows['COUNTY'] . "' AND S.ABBV = '" . $rows['ABBV'] . "' order by timestamp DESC limit 5";

          // Run the query
          $result2 = $db->sql_query($sql);

          while ($row = $result2->fetch_assoc()) {
          $OUTPUT .= '<tr><th>' . $row['timestamp'] . '</th><th><a href="./units?call=' . $row['GUID'] . '&county=' . $row['county'] . '">' . $row['GUID'] . '</a></th>'
          . '<th><a href="./search?county=' . $row['county'] . '&calltype=' . urlencode($row['callSum']) . '">' . $row['callSum'] . '</a></th>'
          . '</th><th><a href="./search?county=' . $row['county'] . '&address=' . urlencode($row['address']) . '">' . $row['address'] . '</a></th></tr>';
          } */


        $CAD911['stadic' . $CallIDM] = array(
            'info' => "<h1>Fire Station: " . $station . "</h1><h3>Agency: " . $rows['DISTRICT'] . "</h1><p>City: " . $rows['CITY'] . "</p><p>Address: " . $rows['ADDRESS'] . "</p>",
            'lat' => $rows['LAT'],
            'lng' => $rows['LON'],
            'type' => "FS",
            'iconW' => 11,
            'iconH' => 11,
            'label' => false,
            'labelname' => '',
            'icon' => "images/MISC/firedept.png"
        );
        $CallIDM++;
    }
}


// Lifeflight
$sql = "SELECT * FROM `oregon911_cad`.`pdx911_lifeflight` ORDER BY ID";
$result = $db->sql_query($sql);
while ($rows = $result->fetch_assoc()) {
    $CAD911['stadic' . $CallIDM] = array(
        'info' => "Name: " . $rows['NAME'] . " Type: " . $rows['UTYPE'] . " Address: " . $rows['ADDRESS'],
        'lat' => $rows['LAT'],
        'lng' => $rows['LON'],
        'type' => "LF",
        'iconW' => 11,
        'iconH' => 11,
        'label' => false,
        'labelname' => '',
        'icon' => "images/MISC/lifeflight.png"
    );
    $CallIDM++;
}

// Calls
$sql = "SELECT * FROM `oregon911_cad`.`pdx911_calls` WHERE county != 'M' ORDER BY type";
$result = $db->sql_query($sql);
while ($rows = $result->fetch_assoc()) {
    $info_string = '<div>' . '<h1> Call: ' . $rows['callSum'] . '</h1>' . '<h3> Address: ' . $rows['address'] . '</h3>' . '<p>Station: ' . $rows['station'] . '</p>' . '<p> Time: ' . $rows['timestamp'] . '</p><p> Units: ';
    $sql = "Select * from `oregon911_cad`.`pdx911_units` WHERE GUID = '" . $rows['GUID'] . "' AND county = '" . $rows['county'] . "'";
    $result2 = $db->sql_query($sql);
    $UOUTPUT = '';
    while ($unit = $result2->fetch_assoc()) {
     if ($unit['clear'] != '00:00:00') {
         $UOUTPUT .= '<span class="clear" title="Cleared @ ' . $unit['clear'] . '">' . $unit['unit'] . '</span>, ';
     } else if ($unit['onscene'] != '00:00:00') {
         $UOUTPUT .= '<span class="onscene" title="Onscene @ ' . $unit['onscene'] . '">' . $unit['unit'] . '</span>, ';
     } else if ($unit['enroute'] != '00:00:00') {
         $UOUTPUT .= '<span class="enroute" title="Enroute @ ' . $unit['enroute'] . '">' . $unit['unit'] . '</span>, ';
     } else if ($unit['dispatched'] != '00:00:00') {
         $UOUTPUT .= '<span class="dispatched" title="Dispatched @ ' . $unit['dispatched'] . '">' . $unit['unit'] . '</span>, ';
     }
    }
    $UOUTPUT =  substr($UOUTPUT, 0, -2) . "</p>"; 
    
    $info_string .= $UOUTPUT;
    
    $info_string .= ('<a href="./units?call=' . $rows['GUID'] . '&county=' . $rows['county'] . '" target="_blank">More Info</a></div>');
    
    $CAD911['loc' . $rows['ID']] = array(
        'info' => $info_string,
        'lat' => $rows['lat'],
        'lng' => $rows['lon'],
        'type' => $rows['type'],
        'iconW' => 32,
        'iconH' => 37,
        'label' => true,
        'labelname' => $rows['callSum'],
        'icon' => $rows['icon']
    );
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