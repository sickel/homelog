<?php

$dbtype='pgsql';
include('dbconn.php');
// sets the values username, server, database and password

try{
    $connectstring=$dbtype.':host='.$server.';dbname='.$database;
    $dbh = new PDO($connectstring, $username, $password);
    if($dbtype=='pgsql'){
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

  }
  catch(PDOException $e){
    $message=$e->getMessage(); 
    exit( "<p>Cannot connect - $message</p>");
  }


/*
 The following query will fetch the last measurement for each sensor that is flagged as active
 The age of the measurement is used to flag too old measurements in the frontend. It is easier to calculate it here 
 and then just have a number to handle afterwards.
 the table should have an index on (sensorid,datetime desc) or the query will run slowly when there start to be about 100k records
*/

$sql='SELECT DISTINCT ON (sd.sensorid, sd.type) 
    sd.sensorid,sd.value/sensor.factor as value,sd.datetime,station.name
   FROM measure sd
     LEFT JOIN sensor ON sensor.stationid = sd.sensorid AND sd.type = sensor.typeid left join station on sd.stationid=station.id
  WHERE NOT sd.value IS NULL and sd.type=118 and not sd.stationid is null
  ORDER BY sd.sensorid, sd.type, sd.datetime DESC';
;
$sqlh=$dbh->prepare($sql);
$sqlh->execute();
$data=$sqlh->fetchAll(PDO::FETCH_ASSOC);
if(array_key_exists('json',$_GET)){
  header('Content-type: application/json');
  die(json_encode($data));
}
// print_r($data)
?>
<html><head><title>Last value</title>
<meta http-equiv=refresh content='60; url=last_voltage.php'>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="tempdata.css">
<link rel="stylesheet" type="text/css" media="screen,projection,handheld and (min-device width:801px)" href="msi_smarty.css" charset="utf-8">
</head><body>
<?php 
$showage=array_key_exists('age',$_GET)?1:0;
print('<table class="lastlist">');
$age=$showage?"<th>alder (min)</th>":"";
print("<tr><th>Måling</th><th>Verdi</th>$age</tr>\n");
$sensors=array_key_exists('s',$_GET)?$_GET['s']:array();
foreach ($data as $s){
$value=$s['value'];
    if($value > 3.6){$vstatus='OK'; $color='green';}
  elseif(value > 3.4){$vstatus='low'; $color='yellow';}
  else{$vstatus='critical'; $color='red';}
  
    $txt="<a href=\"stripchart.php?selid=${s['sensorid']}\">${s['name']}</a>   </td><td class=\"right $vstatus\"> <b>${s['value']} V</td><td>${s['datetime']}</td>";
    print("<tr class=\"$class\"><td class=\"right\">$txt</tr>\n");

}

print("</table><hr />");

date_default_timezone_set('Europe/Oslo');
print("<p>Oppdatert ".date('d/m/Y H:i:s', time())."</p>");
?>
<p><a href="stripchart.php">Stripchart</a>  <a href="list.php">Last values</a></p>
</body></html>
