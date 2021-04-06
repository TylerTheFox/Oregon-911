<?PHP
// Parameters
if (
    isset($_GET['call'])    &&
    isset($_GET['county']) 
    )
{
    $G_callID       = $_GET['call'];
    $G_callCounty   = $_GET['county'];
    
    if (isset($_GET['call']))
    {
        $G_callType = $_GET['type'];
    }
    else
    {
        $G_callType = "F";
    }
}
else
{
    die("Error!");
}

// We need to get basic call information
// So let's preload some data from the api
$InternalUse    = true;
require_once("api/1.0/get/call/index.php");

$callInfo_JSON  = getIncidentData($G_callID, $G_callCounty, $G_callType);
$callInfo       = json_decode($callInfo_JSON);

if ($callInfo == null)
{
    die("Error!");
}

require("static_impl.php");

function generateTwitterMetaData($callInfo)
{
    $twitterFeed = "@Washco_FireMed";
    if ( $callInfo->Header->callCounty == "W" && $callInfo->Header->callType == "P" )
    {
        $twitterFeed = "@Washco_Police";
    } else if ( $callInfo->Header->callCounty == "C" )
    {
        $twitterFeed = "@Clackco_FireMed";
    }
    
    $twitterMeta    =  '        <meta name="twitter:card" content="summary_large_image">' . "\n"
                    .  '        <meta name="twitter:site" content="' . $twitterFeed . '">' . "\n"
                    .  '        <meta name="twitter:creator" content="@Oregon911">' . "\n"
                    .  '        <meta name="twitter:title" content="' . $callInfo->Header->callSummery . '">' . "\n"
                    . '         <meta name="twitter:description" content="ID: '. $callInfo->Header->callNum . ' Address: ' . $callInfo->Header->callAddress . ' Station: ' . $callInfo->Header->respondingStation . ' Units: ' . $callInfo->Header->respondingUnits . '">' . "\n"
                    . '         <meta name="twitter:image:src" content="http://cad.oregon911.net/call?call=' . $callInfo->Header->callNum . '&county=' . $callInfo->Header->callCounty . '&img=Y">' . "\n";
    return $twitterMeta;
}
?>