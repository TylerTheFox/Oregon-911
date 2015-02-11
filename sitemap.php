<?php
header("Content-type: text/xml");
echo'<?xml version=\'1.0\' encoding=\'UTF-8\'?>';
echo("\n");
?><urlset xmlns="https://sitemaps.org/schemas/sitemap/0.9"><?

    include 'phpbb.php';

    if ($_GET['units'] != "1") {
    ?>
    <url>
        <loc>http://cad.oregon911.net/</loc>
        <changefreq>always</changefreq>
    </url>
    <url>
        <loc>http://cad.oregon911.net/call-list</loc>
        <changefreq>always</changefreq>
    </url>
    <url>
        <loc>http://cad.oregon911.net/search</loc>
        <changefreq>always</changefreq>
    </url>
    <url>
        <loc>http://cad.oregon911.net/stastics</loc>
        <changefreq>always</changefreq>
    </url>
    <url>
        <loc>http://cad.oregon911.net/statistics/maps</loc>
        <changefreq>always</changefreq>
    </url>
    <url>
        <loc>http://cad.oregon911.net/statistics/tables</loc>
        <changefreq>always</changefreq>
    </url>
    <url>
        <loc>http://cad.oregon911.net/statistics/graphs</loc>
        <changefreq>always</changefreq>
    </url>
    <url>
        <loc>http://cad.oregon911.net/call</loc>
        <changefreq>always</changefreq>
    </url>
    <url>
        <loc>http://cad.oregon911.net/unitinfo</loc>
        <changefreq>always</changefreq>
    </url>
    <url>
        <loc>http://cad.oregon911.net/station</loc>
        <changefreq>always</changefreq>
    </url>
    <url>
        <loc>http://cad.oregon911.net/agency</loc>
        <changefreq>always</changefreq>
    </url>
    <?

    $sql = "SELECT * FROM `oregon911_cad`.`pdx911_calls` ORDER BY ID";
    $result = $db->sql_query($sql);
    while ($rows = $result->fetch_assoc()) {
    ?>
    <url>
        <loc><?PHP echo(htmlspecialchars("http://cad.oregon911.net/call?call=" . $rows['GUID'] . "&county=" . $rows['county'])); ?></loc>
        <changefreq>always</changefreq>
    </url>
    <?
    }

    /*$sql = "SELECT * FROM `oregon911_cad`.`pdx911_archive` ORDER BY ID";
    $result = $db->sql_query($sql);
    while ($rows = $result->fetch_assoc()) {
    ?>
    <url>
        <loc><?PHP echo(htmlspecialchars("https://cad.oregon911.net/call?call=" . $rows['GUID'] . "&county=" . $rows['county'])); ?></loc>
        <changefreq>never</changefreq>
    </url>
    <?
    }*/

    $sql = "SELECT ABBV, county FROM `oregon911_cad`.`pdx911_stations` WHERE ABBV != 'UNK' ORDER BY ID";
    $result = $db->sql_query($sql);
    while ($rows = $result->fetch_assoc()) {
    ?>
    <url>
        <loc><?PHP echo(htmlspecialchars("https://cad.oregon911.net/station?station=" . $rows['ABBV'] . "&county=" . $rows['county'])); ?></loc>
        <changefreq>always</changefreq>
    </url>
    <?
    }


    $sql = "SELECT UNIT, county FROM `oregon911_cad`.`pdx911_unit_table` ORDER BY ID";
    $result = $db->sql_query($sql);
    while ($rows = $result->fetch_assoc()) {
    ?>
    <url>
        <loc><?PHP echo(htmlspecialchars("https://cad.oregon911.net/unitinfo?unit=" . $rows['unit'] . "&county=" . $rows['county'])); ?></loc>
        <changefreq>always</changefreq>
    </url>
    <?
    }

    $sql = "SELECT AGENCY, county FROM `oregon911_cad`.`pdx911_unit_table` ORDER BY ID";
    $result = $db->sql_query($sql);
    while ($rows = $result->fetch_assoc()) {
    ?>
    <url>
        <loc><?PHP echo(htmlspecialchars("https://cad.oregon911.net/agency?agency=" . $rows['AGENCY'] . "&county=" . $rows['county'])); ?></loc>
        <changefreq>always</changefreq>
    </url>
    <?
    }

    } else {
    $page = ($db->sql_escape($_GET['page'])-1) * 49999;


    $sql = "SELECT * FROM `oregon911_cad`.`pdx911_archive` ORDER BY ID LIMIT $page, 49999";

    $result = $db->sql_query($sql);
    while ($rows = $result->fetch_assoc()) {
    ?>
    <url>
        <loc><?PHP echo(htmlspecialchars("https://cad.oregon911.net/units?call=" . $rows['GUID'] . "&county=" . $rows['county'])); ?></loc>
        <changefreq>never</changefreq>
    </url>
    <?
    }

    }
    ?>

</urlset>
