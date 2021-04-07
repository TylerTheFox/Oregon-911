<?PHP

if (isset($_GET['img']) && $_GET['img'] == "Y") 
{
    $zoom = 14;
    if (isset($_GET['zoom'])) 
    {
        $zoom = $_GET['zoom'];
    }

    $H = 280;
    if (isset($_GET['H'])) 
    {
        $H = $_GET['H'];
    }

    $W = 550;
    if (isset($_GET['W'])) 
    {
        $W = $_GET['W'];
    }

    if ($call_valid)
    {
        $callLat    = $callInfo->Header->callLat;
        $callLng    = $callInfo->Header->callLng;
        $callIcon   = $callInfo->Header->callIcon;
    }
    else
    {
        $callLat    = 0;
        $callLng    = 0;
        $callIcon   = "images/WCCCA/general.png";
    }
    
    $TwitterCardIMG = "http://www.cad.oregon911.net/static/staticmap.php?center=$callLat,$callLng&zoom=$zoom&size=$W\x$H&markers=$callLat,$callLng,$callIcon";
    $im = file_get_contents($TwitterCardIMG);
    header('content-type: image/gif');
    echo $im;
    exit;
}
?>