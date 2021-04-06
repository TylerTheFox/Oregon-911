<?PHP
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

if (isset($_GET['img']) && $_GET['img'] == "Y") 
{
    $TwitterCardIMG = "http://www.cad.oregon911.net/static/staticmap.php?center=$callInfo->Header->callLat,$callInfo->Header->callLng&zoom=$zoom&size=$W\x$H&markers=$callInfo->Header->callLat,$callInfo->callLng,$callInfo->Header->callIcon";
    $im = file_get_contents($TwitterCardIMG);
    header('content-type: image/gif');
    echo $im;
    exit;
}
?>