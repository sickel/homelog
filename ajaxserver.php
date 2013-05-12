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

$sql='select value, to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from measure_qa where sensorid=? and datetime>?';
$params=array($_GET['from']);
define('SERIES_SOUTHAVG','southavg');
$sensors=array('Inne'=>2,
	       'Ute'=>1,
	       'Ute - skygge'=>10,
	       'Fuktighet'=>5,
	       'Fuktighet DHT22'=>9,
	       'Temp DHT22'=>8,
	       'Temp DHT11'=>6,
	       'Temp BHP085'=>3,
	       'Inne - test'=>11,
	       'southavg'=>1,
	       'Forbruk'=>13
	       );
$sensorid=$sensors{$_GET['stream']};

if($sensorid){  
  $unitq=$dbh->prepare('select unit from sensors where id=?');
  $unitq->execute(array($sensorid));
  $unit=$unitq->fetchAll(PDO::FETCH_ASSOC);
  $unit=$unit[0]['unit'];
  array_unshift($params,$sensorid);
}
if($_GET['stream']=='Inne-Ute'){
  $sql="select value,at from tempdiff where datetime >?";
 }elseif($_GET['stream']=='Soloppvarming'){
  $sql="select value,at from tempdiff_sol where datetime >?";
 }elseif($_GET['stream']=='Skygge'){
  $sql="select value,at from shadow where datetime >?";
 }elseif($_GET['stream']=='Trykk'){
  $sql='select value/100 as "value", to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from measure_qa where sensorid=4 and datetime>?';
 array_shift($params);
//  TODO fetch units from database - problem: rescaling...
 $unit='hPa';
}elseif($_GET['stream']=='Trykk - 0m'){
  $sql='select value/100+12*0.45 as "value", to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from measure_qa where sensorid=4 and datetime>?';
  $unit='hPa';
}elseif($_GET['stream']=='Forbruk'){
  $sql='select round(100*kwh/hours)/100 as "value", to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from powerdraw where datetime >?';	
  array_shift($params);  // Throws off the sensorid as that is of no use here
}elseif($_GET['stream']=='southavg'){
  $sql='select value, to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from  daymean where sensorid=? and datetime >?';	
}
if($_GET['to']*1>1){
	$params[]=$_GET['to'];
	$sql.=' and datetime < ?';
}
$sql.=' order by datetime';
// print($sql);
$sqh=$dbh->prepare($sql);
# print_r($params);	
$sqh->execute($params);
$data=$sqh->fetchAll(PDO::FETCH_ASSOC);

if($_GET['type']=='svg'){
// for future use
}else{
// return json
   if($_GET['DEBUG']){
	$data['debug']['sql']=$sql;
	$data['debug']['name']=$_GET['stream'];
	$data['debug']['from']=$_GET['from'];
	$data['params']=$params;
}	
	print('{
  	"datapoints":');
	print(json_encode($data));
	print(",\"unit\":\"$unit\"}");
}
?>
