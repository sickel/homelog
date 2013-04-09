<?php

$dbtype='pgsql';
include('dbconn.php');
// sets the values username, server, database and password

$unit="&deg;C";
try{
    $connectstring=$dbtype.':host='.$server.';dbname='.$database;
    $dbh = new PDO($connectstring, $username, $password);
    if($dbtype=='pgsql'){
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

  }
  catch(PDOException $e){
    $message=$e->getMessage(); 
// TODO add on a header to signal error
    exit( "<p>Cannot connect - $message</p>");
  }

  $sql='select temp as "value",to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from temp_stream where name=? and datetime>?';

$params=array($_GET['from']);
if($_GET['stream']=='Inne-Ute'){
  $sql="select value,at from tempdiff where datetime >?";
 }elseif($_GET['stream']=='Trykk'){
  $sql='select value/100 as "value", to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from measure where sensorid=4 and datetime>?';
}elseif($_GET['stream']=='Trykk - 0m'){
  $sql='select value/100+12*0.45 as "value", to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from measure where sensorid=4 and datetime>?';
}elseif($_GET['stream']=='Fuktighet'){
  $sql='select value as "value", to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from measure where sensorid=5 and datetime>?';
}elseif($_GET['stream']=='Forbruk'){
  $sql='select kwh/hours as "value", to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from powerdraw where datetime >?';	
}else{
  array_unshift($params,$_GET['stream']);
}
if($_GET['to']*1>1){
	$params[]=$_GET['to'];
	$sql.=' and datetime < ?';
}
$sql.=' order by datetime';
// print($sql);
$sqh=$dbh->prepare($sql);
	
$sqh->execute($params);
$data=$sqh->fetchAll(PDO::FETCH_ASSOC);

if($_GET['type']=='svg'){
// for future use
}else{
// return json
	print('{
  	"datapoints":');
	print(json_encode($data));
	print(",\"unit\":\"$unit\"}");
}
?>
