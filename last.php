<html><head><title>Last value</title>
<meta http-equiv=refresh content='60; url=last.php'>
<link rel="stylesheet" type="text/css" href="tempdata.css">
</head><body>
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

$sqlh=$dbh->prepare('select distinct on (sensorid) sensorid, sensor.name,value,unit,datetime,extract (epoch from now()-datetime) as since from measure,sensor,type where type.id=sensor.typeid and sensor.id=sensorid  and active=true order by sensorid,datetime desc');
$sqlh->execute();
$data=$sqlh->fetchAll();
print('<ul class="lastlist">');
$sensors=array_key_exists('s',$_GET)?$_GET['s']:array();
foreach ($data as $s){
  if(count($sensors)==0 or in_array($s['sensorid'],$sensors) ){
    $class=$s['since']< 60*20?'default':'olddata';
    $s['since']=round($s['since']/60);
    $txt="${s['name']} <b>${s['value']} ${s['unit']}</b> (${s['since']} minutter siden)";
    if(array_key_exists('showids',$_GET)){
      $txt="(${s['sensorid']}) $txt";
    }	
    print("<li class=\"$class\">$txt</li>\n");
  }
}

?>
</ul>
</body></html>
