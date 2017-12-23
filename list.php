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
$limit =30;
$sql='select * from corr_measure order by id desc limit ';
$sql="$sql$limit";
//  print($sql);
$sqlh=$dbh->prepare($sql);
$sqlh->execute();
$data=$sqlh->fetchAll(PDO::FETCH_ASSOC);

if(array_key_exists('json',$_GET)){
  header('Content-type: application/json');
  die(json_encode($data));
}
// print_r($data)
?>
<html><head><title>Last values</title>
<meta http-equiv=refresh content='60; url=list.php'>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="tempdata.css">
<link rel="stylesheet" type="text/css" media="screen,projection,handheld and (min-device width:801px)" href="msi_smarty.css" charset="utf-8">
</head><body>
<?php 
$showage=array_key_exists('age',$_GET)?1:0;
print('<table>');
$age=$showage?"<th>alder (min)</th>":"";
print("<tr><th>Id</th><th>Sensor</th><th>Type</th><th>Verdi</th><th>Tid</th><th>Bruk</th><th>aux</th><th>Nr</th><th>Stasjon</th></tr>\n");
$sensors=array_key_exists('s',$_GET)?$_GET['s']:array();
foreach ($data as $s){
  print("<tr>");
  foreach ($s as $t){
     print("<td class=\"right\">$t</td>");
  }
  print("</tr>\n");
}

print("</table><hr />");

date_default_timezone_set('Europe/Oslo');
print("<p>Oppdatert ".date('d/m/Y H:i:s', time())."</p>");
?>
<p><a href="stripchart.php">Stripchart</a>  <a href="last.php">Last value</a></p>
</body></html>
