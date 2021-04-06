<?PHP

define('SQL_CALL_INFO', <<<SQL
SELECT 
	GUID 									as 'callNum',
    County  								as 'callCounty', 
    type									as 'callType',
    callSum									as 'callSummery',
    U.address 								as 'callAddress',
    U.agency 								as 'respondingAgencyRaw',
    U.station								as 'respondingStation',
    units									as 'respondingUnits',
    (SELECT 
            DISTRICT
        FROM
            oregon911_cad.`pdx911_stations` AS S
        WHERE
            U.STATION = S.ABBV
                AND U.county = S.county) 	as 'respondingAgency',
    U.lat									as 'callLat',
    U.lon									as 'callLng',
    icon									as 'callIcon',
    timestamp								as 'callTimestamp',
    1 										AS 'callActive'
FROM
    oregon911_cad.`pdx911_calls` AS U
WHERE
    U.GUID 		=  ? 			AND 
    U.county 	=  ? 			AND
    U.county 	!= 'M'	 		AND 
    U.type 		=  ? 
UNION ALL SELECT 
	GUID,
    County, 
    type,
    callSum,
    U.address,
    U.agency,
    U.station,
    units,
    (SELECT 
            DISTRICT
        FROM
            oregon911_cad.`pdx911_stations` AS S
        WHERE
            U.STATION 	= S.ABBV 	AND
            U.county 	= S.county 	AND 
            S.county 	!= 'M'),
    U.lat,
    U.lon,
    icon,
    timestamp,
    0
FROM
        oregon911_cad.`pdx911_archive` AS U
WHERE
    U.GUID 		=  ? 			AND 
    U.county 	=  ? 			AND
    U.county 	!= 'M'	 		AND 
    U.type 		=  ? 
SQL
);

define('SQL_CALLLOG_ENTRIES', <<<SQL
SELECT 
    timestamp,
    username,
    entry,
    REPLACE((SELECT pf_twitter FROM oregon911_net_1.`phpbb_profile_fields_data` WHERE oregon911_net_1.`phpbb_users`.user_id = oregon911_net_1.`phpbb_profile_fields_data`.user_id), "@", "") as Twitter
FROM
    oregon911_net_1.`phpbb_calllog_entries`
	JOIN
    oregon911_net_1.`phpbb_users` ON FK_user_id = oregon911_net_1.`phpbb_users`.user_id
WHERE
	FK_GUID     = ? AND 
	FK_county   = ?
ORDER BY timestamp DESC
LIMIT 15
SQL
);

define('SQL_CALL_UNITS', <<<SQL
SELECT 
    unit,
    
	S.STATION 	AS stationNumber, 
	S.ABBV 		AS stationAbbreviation, 
    S.DISTRICT 	AS stationAgency, 
	S.ADDRESS 	AS stationAddress,
	S.CITY 		AS stationCity,
	S.COUNTY 	AS stationCounty,
	S.LAT 		AS stationLat,
	S.LON 		AS stationLng,
    
    IF(dispatched = "00:00:00", null, dispatched) 	as dispatched,
    IF(enroute = "00:00:00", null, enroute) 		as enroute,
    IF(onscene = "00:00:00", null, onscene) 		as onscene,
    IF(clear = "00:00:00", null, clear) 			as clear
FROM
    `oregon911_cad`.`pdx911_units` AS U
        LEFT JOIN
    `oregon911_cad`.`pdx911_stations` AS S ON U.STATION = ABBV AND U.county = S.county
WHERE
    U.GUID 		= ? 		AND
    U.county 	= ? 		AND 
    U.type 		= ? 		AND 
    U.unit NOT IN (SELECT flag FROM `oregon911_cad`.`pdx911_callSum_flags`)
SQL
);

define('SQL_CALL_FLAGS', <<<SQL
SELECT 
		unit as 'Flag',
        IF(dispatched = "00:00:00", null, dispatched) 	as dispatched,
		IF(clear = "00:00:00", null, clear) 			as clear
FROM
    `oregon911_cad`.`pdx911_units`
WHERE
    GUID 	= ? 			AND 
    county 	= ? 			AND
    unit IN (SELECT flag FROM `oregon911_cad`.`pdx911_callSum_flags`)
SQL
);

define('SQL_CALL_CHANGELOG', <<<SQL
SELECT 
    oregon911_cad.pdx911_records.timestamp,
    oregon911_cad.pdx911_records.callSum 		as 'callSummery',
    oregon911_cad.pdx911_records.address 		as 'callAddress',
    oregon911_cad.pdx911_records.lat 			as 'callLat',
    oregon911_cad.pdx911_records.lon 			as 'callLng',
    oregon911_cad.pdx911_records.update
FROM
    oregon911_cad.pdx911_records
WHERE
    GUID    = ? AND 
    county  = ?
ORDER BY timestamp DESC
SQL
);

define('SQL_CALL_LASTTEN_ADDRESS', <<<SQL
SELECT 
	GUID 									as 'callNum',
    County  								as 'callCounty', 
    type									as 'callType',
    callSum									as 'callSummery',
    address 								as 'callAddress',
    timestamp								as 'callTimestamp'
FROM
    `oregon911_cad`.`pdx911_archive`
WHERE
    county 		!=  'M'  AND  
    GUID 		!=  ?    AND
	county 		=   ?    AND
    address 	=   ?
ORDER BY timestamp DESC
LIMIT 10
SQL
);
?>