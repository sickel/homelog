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

$sql='select * from lastmeas_complete';
# $sql='select distinct on (sensorid) sensorid, sensor.name,value,unit,datetime,extract (epoch from now()-datetime) as since 
# from measure,sensor,type 
# where type.id=sensor.typeid and sensor.id=sensorid';
// $sql.=$_GET['show']=='all'?'':' and active=true ';
// $sql.=' order by sensorid,datetime desc';
// print($sql);
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
<meta http-equiv=refresh content='60; url=last.php'>
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
  if($s['main']){
    $class=$s['since']< 60*20?'default':'olddata';
    $s['since']=round($s['since']/60);
    $txt="${s['type']} ${s['station']}   </td><td class=\"right\"> <b>${s['value']} ${s['unit']}</td>";
    if($showage){
      //$txt.="<td class=\"center\">${s['since']}</td>";
    }
    if(array_key_exists('showids',$_GET)){
      $txt="(${s['sensorid']}) $txt";
    }	
    print("<tr class=\"$class\"><td class=\"right\">$txt</tr>\n");
  }
}

?>
</table>
<hr />
<p><a href="stripchart.php">Stripchart</a></p>
</body></html>
