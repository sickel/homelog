<?php

class jsonException extends Exception
{}

  $dbtype='pgsql';
  include('dbconn.php'); // sets the values username, server, database and password
  //$unit="&deg;C";
  try{
    $connectstring=$dbtype.':host='.$server.';dbname='.$database;
    $dbh = new PDO($connectstring, $username, $password);
    if($dbtype=='pgsql'){
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
  }
  catch(PDOException $e){
    header('HTTP/1.1 500 Internal Server Error');
    $message=$e->getMessage(); 
    exit( "<p>Cannot connect - $message</p>");
  }

  $sql="select concat,id from sensorlist";
  $qry=$dbh->prepare($sql);
  $qry->execute();
  $sensorset=$qry->fetchAll(PDO::FETCH_ASSOC);
//  print_r($sensorset);
  foreach($sensorset as $s){
	$sensors[$s['concat']]=$s['id'];}
//  print_r($sensors);

  
function listsensors($sensors){
  $s=array_keys($sensors);
  $s=array('sensors'=>$s);
  return(json_encode($s));
}


try{

if(!(isset($_GET['a']))){
  throw new jsonException("missing a a-parameter");
}


if($_GET['a']=='switchuse'){
    if($_GET['present']==='1'){
        $newvalue='false';}
    elseif($_GET['present']==='0'){
        $newvalue='false';}
    else{throw new jsonException("unknown present value");}
    $sql="update measure set use = $newvalue where id = ?";
    $updateq=$dbh->prepare($sql);
    $updateq->execute(array($_GET['elementid']*1));
    print(json_encode('OK'));
    exit("\n");
  }
 
        

if($_GET['a']=='sensorlist'){
  print(listsensors($sensors));
  exit("\n");
}

if($_GET['a']=='tempdata'){
  if(!(isset($_GET['stream']))){
    throw new jsonException("missing stream-parameter");
  }
  $stepline=false;
  $sensorid=$_GET['stream'];
  $sensorid=$sensors[$_GET['stream']];
  $sql='select value, at from sensormeasurement where sensorid=? and datetime>? ';
  $params=array($_GET['stream'],$_GET['from']);
 // print_r($sensorid);
//  $sensorid=$_GET['stream'];
  //if($sensorid){ 
  
    $unitq=$dbh->prepare('select unit from sensors where id=?');
    $unitq->execute(array($_GET['stream']));
    $unit=$unitq->fetchAll(PDO::FETCH_ASSOC);
//    print_r($unit);
    $unit=$unit[0]['unit'];
  //  array_unshift($params,$sensorid);
  //}
  if($_GET['stream']=='Inne-Ute'){
    $sql="select value,at from tempdiff where datetime >?";
    $unit="&deg;C";
  }elseif($_GET['stream']=='Soloppvarming'){
    $sql="select value,at from tempdiff_sol where datetime >?";
    $unit="&deg;C";
  }elseif($_GET['stream']=='Skygge'){
    $sql="select value,at from shadow where datetime >?";
    $unit="&deg;C";
  }elseif($_GET['stream']=='Trykk'){
    $sql='select value/100 as "value", to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from measure_qa where sensorid=4 and datetime>?';
    // array_shift($params);
  //  TODO fetch units from database - problem: rescaling...
  $unit='hPa';
  }elseif($_GET['stream']=='Trykk - 0m'){
    $sql='select value/100+12*0.45 as "value", to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from measure_qa where sensorid=4 and datetime>?';
    $unit='hPa';
  }elseif($_GET['stream']=='Forbruk'){
    $sql='select round(100*kwh/hours)/100 as "value", to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from powerdraw where datetime >?';
    $stepline=true;
    $unit="kW";
  }elseif($_GET['stream']=='Sørvegg - døgnsnitt'){
    $sql='select value, to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from  daymean where sensorid=? and datetime >?';	
    $unit="&deg;C";
    $stepline=true;
  }elseif($_GET['stream']=='Sørvegg - døgnmin'){
    $sql='select value, to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from  daymin where sensorid=? and datetime >?';	
    $unit="&deg;C";
    $stepline=true;
  }elseif($_GET['stream']=='Sørvegg - døgnmax'){
    $sql='select value, to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from  daymax where sensorid=? and datetime >?';	
    $unit="&deg;C";
    $stepline=true;
  }
  if($_GET['to']*1>1){
	$params[]=$_GET['to'];
	$sql.=' and datetime <= ?';
  }
  $sql.=' order by datetime';
//  print($sql);
//  print_r($params);	
  $sqh=$dbh->prepare($sql);
  $sqh->execute($params);
  $retdata=$sqh->fetchAll(PDO::FETCH_ASSOC);
  //print_r($data[0]);
  $starttime=$retdata[1]['at'];
  $last=end($retdata);
  $stoptime=$last['at'];
  $data=array('datapoints'=>$retdata);
  $data['starttime']=$starttime;
  $data['stoptime']=$stoptime;
  $data['unit']=$unit;
  $data['stepline']=$stepline;
  if(isset($_GET['DEBUG']) && $_GET['DEBUG']){
    $data['debug']['sql']=$sql;
    $data['debug']['name']=$_GET['stream'];
    $data['debug']['from']=$_GET['from'];
    $data['params']=$params;
  }	
  $sql="select station.name from station  left join sensor on station.id=stationid where sensor.id=?";
  $sqh=$dbh->prepare($sql);
  $sqh->execute(array($_GET['stream']));
  $f=$sqh->fetchAll(PDO::FETCH_ASSOC);
  //print_r($f);
  $data['station']=$f[0]['name'];
  print(json_encode($data));
  exit();
}
throw new jsonException("Unknown action :${_GET['a']}");
}
catch(jsonException $e){
  echo(json_encode(array('error'=>$e->getMessage())));
}
catch(Exception $e){
  echo($e->getMessage());
}
?>
