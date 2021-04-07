<?PHP
$call_valid = true;

// Parameters
if (
    isset($_GET['call'])    &&
    isset($_GET['county']) 
    )
{
    $G_callID       = $_GET['call'];
    $G_callCounty   = $_GET['county'];
    
    if (isset($_GET['type']) && $_GET['type'] != null)
    {
        $G_callType = strtoupper($_GET['type']);
    }
    else
    {
        $G_callType = "F";
    }
}
else
{
    $call_valid = false;
}

if ($call_valid)
{
    // We need to get basic call information
    // So let's preload some data from the api
    $InternalUse    = true;
    require_once("api/1.0/get/call/index.php");

    $callInfo_JSON  = getIncidentData($G_callID, $G_callCounty, $G_callType);
    $callInfo       = json_decode($callInfo_JSON);

    // This will correct any errors with the type 
    // Try searching each type to see if the call 
    // exists anywhere
    if ($callInfo == null)
    {
        $checkTypes = array("F", "M", "P");
        foreach ($checkTypes as &$type) 
        {
            if ($type != $G_callType)
            {
                $callInfo_JSON  = getIncidentData($G_callID, $G_callCounty, $type);
                $callInfo       = json_decode($callInfo_JSON);
                
                if ($callInfo != null)
                {   
                    // We found it!
                    break;
                }
            }
        }
    }

    // Give up we don't have it.
    if ($callInfo == null)
    {
        $call_valid = false;
    }

    require("static_impl.php");
}

function generateTwitterMetaData($callInfo)
{
    if ($callInfo != null)
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
                        .  '        <meta name="twitter:description" content="ID: '. $callInfo->Header->callNum . ' Address: ' . $callInfo->Header->callAddress . ' Station: ' . $callInfo->Header->respondingStation . ' Units: ' . $callInfo->Header->respondingUnits . '">' . "\n"
                        .  '        <meta name="twitter:image:src" content="http://cad.oregon911.net/call?call=' . $callInfo->Header->callNum . '&county=' . $callInfo->Header->callCounty . '&img=Y">' . "\n";
        return $twitterMeta;
    }
    return "";
}
?>