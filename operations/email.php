<?PHP
	include ("../loggedin.php");

	$safeGUID = $db->sql_escape(htmlspecialchars(strip_tags($_GET["call"])));
	$safecounty = $db->sql_escape(htmlspecialchars(strip_tags($_GET["county"])));	
	$safealt = $db->sql_escape(htmlspecialchars(strip_tags($_GET["alt"])));	

	//Array with the data to insert
	$sql_array = array(
		'GUID'    => $safeGUID,
		'county'        => $safecounty,
	);

	$sql = 'SELECT * FROM `oregon911_cad`.`pdx911_calls` WHERE '. $db->sql_build_array('SELECT', $sql_array) . ' UNION ALL SELECT * FROM `oregon911_cad`.`pdx911_archive` WHERE ' . $db->sql_build_array('SELECT', $sql_array);
	
	// Run the query 
	$result = $db->sql_query($sql);

	// $row should hold the data you selected
	$callrow = $db->sql_fetchrow($result);

	$CallType = $callrow['callSum'];
	$Address = $callrow['address'];
	// Be sure to free the result after a SELECT query                        
	$db->sql_freeresult($result);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="initial-scale=1.0">    <!-- So that mobile webkit will display zoomed in -->
    <meta name="format-detection" content="telephone=no"> <!-- disable auto telephone linking in iOS -->

    <title>OR911 Email Template</title>
    <style type="text/css">

        /* Resets: see reset.css for details */
        .ReadMsgBody { width: 100%; background-color: #ebebeb;}
        .ExternalClass {width: 100%; background-color: #ebebeb;}
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height:100%;}
        body {-webkit-text-size-adjust:none; -ms-text-size-adjust:none;}
        body {margin:0; padding:0;}
        table {border-spacing:0;}
        table td {border-collapse:collapse;}
        .yshortcuts a {border-bottom: none !important;}


        /* Constrain email width for small screens */
        @media screen and (max-width: 600px) {
            table[class="container"] {
                width: 95% !important;
            }
        }

        /* Give content more room on mobile */
        @media screen and (max-width: 480px) {
            td[class="container-padding"] {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }
         }

    </style>
</head>
<body style="margin:0; padding:10px 0;" bgcolor="#ebebeb" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<br>

<!-- 100% wrapper (grey background) -->
<table border="0" width="100%" height="100%" cellpadding="0" cellspacing="0" bgcolor="#ebebeb">
  <tr>
    <td align="center" valign="top" bgcolor="#ebebeb" style="background-color: #ebebeb;">

      <!-- 600px container (white background) -->
      <table border="0" width="600" cellpadding="0" cellspacing="0" class="container" bgcolor="#ffffff">
        <tr>
          <td class="container-padding" bgcolor="#ffffff" style="background-color: #ffffff; padding-left: 30px; padding-right: 30px; font-size: 14px; line-height: 20px; font-family: Helvetica, sans-serif; color: #333;">
            <br>

            <!-- ### BEGIN CONTENT ### -->
            <div style="font-weight: bold; font-size: 18px; line-height: 24px; color: #D03C0F">
            Call: <?PHP echo($CallType); ?>
            </div>
			<div style="font-weight: bold; font-size: 13px; line-height: 24px; color: #D03C0F">
            Address: <?PHP echo($Address); ?>
            </div>
			<div style="font-weight: bold; font-size: 13px; line-height: 24px; color: #D03C0F">
            Alert Name: <?PHP echo($safealt); ?>
            </div><br>

			<img src="http://cad.oregon911.net/units.php?call=<?PHP echo($safeGUID); ?>&county=<?PHP echo($safecounty); ?>&img=Y" alt="Call Map" height="300" width="560">
			<hr>
			<?PHP
				// Call ID Table
				echo '<table border="1" style="width:100%;">';
				echo '<tr><th>Unit</th><th>Agency</th><th>Station</th><th>Dispatched</th><th>Enroute<th>Onscene</th><th>Clear</th></tr>';
				$sql = "SELECT * FROM `oregon911_cad`.`pdx911_units` WHERE ". $db->sql_build_array('SELECT', $sql_array);
				$result = $db->sql_query($sql);
				while ($row = $result->fetch_assoc()) {
					echo '<tr><th>' . $row['unit'] . '</th><th>' . $row['agency'] . '</th><th>' . $row['station'] . '</th><th>' . $row['dispatched'] . '</th><th>' . $row['enroute'] . '<th>' . $row['onscene'] . '</th><th>' . $row['clear'] . '</th></tr>';
				}
				echo '</table>';
			?>
			<a href="http://cad.oregon911.net/units.php?call=<?PHP echo($safeGUID); ?>&county=<?PHP echo($safecounty); ?>">Click Here To View</a>
			<br>
            <!-- ### END CONTENT ### -->

          </td>
        </tr>
      </table>
      <!--/600px container -->

    </td>
  </tr>
</table>
<!--/100% wrapper-->
<br>
<br>
</body>
</html>
