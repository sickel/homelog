<?php

  class jsonException extends Exception
  {}

  
  function listsensors($sensors){
    $s=array_keys($sensors);
    $s=array('sensors'=>$s);
    return(json_encode($s));
  }

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


try{

if(!(isset($_GET['a']))){
  throw new jsonException("missing an a-parameter");
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
  $data=array();
  if(!(isset($_GET['stream']))){
    throw new jsonException("missing stream-parameter");
  }
  $stepline=false;
  $splitchar="_";
  $sensor=explode($splitchar,$_GET['stream']);
  $from=explode($splitchar,$_GET['from']);
  $to=explode($splitchar,$_GET['to']);
  for ($i=0;$i<count($sensor);$i++) {
    $sensorid=$sensor[$i];
    $params=array($sensorid,$from[$i]);
    if($_GET['aggtype']=='none' || $_GET['average']=='none'){
        $sql='select value, at from sensormeasurement where sensorid=? and datetime>? ';
        if(strlen($_GET['to'])>0){
            $params[]=$to[$i];
            $sql.=' and datetime <= ?';
        }
        $sql.=' order by datetime';}
    else{
        $data['test']='averaging';
        $validtypes=array('min'=>1,'max'=>1,'avg'=>1);
        $validtimes=array('hour'=>1,'day'=>1);
        if (!(array_key_exists($_GET['aggtype'],$validtypes))){ throw new jsonException("Unknown average type");}
        if (!(array_key_exists($_GET['average'],$validtimes))){ throw new jsonException("Unknown time");}
        
        $innersql="SELECT sensor.id AS sensorid,
        CASE
            WHEN measure.value > 40000000::double precision THEN (measure.value - 4294967296::bigint::double precision) / sensor.factor
            ELSE measure.value / sensor.factor
        END AS value,
        to_char(timezone('UTC'::text, date_trunc('${_GET['average']}',measure.datetime)), 'yyyy-mm-dd\"T\"HH24:MI:SS\"Z\"'::text) AS at, measure.datetime
        FROM sensor,measure
        WHERE sensor.typeid = measure.type AND sensor.senderid = measure.sensorid AND measure.use = true";
        $sql="WITH innersql as ($innersql) select ${_GET['aggtype']}(value) as value, at from innersql where sensorid=? and datetime>? ";
        if(strlen($_GET['to'])>0){
            $params[]=$to[$i];
            $sql.=' and datetime <= ?';
        }
        $sql .= " group by at";
        $sql .= " order by at";
    }
    $data['sql']=$sql;
    $unitq=$dbh->prepare('select unit from sensors where id=?');
    $unitq->execute(array($stream[$i]));
    $unit=$unitq->fetchAll(PDO::FETCH_ASSOC);
    $unit=$unit[0]['unit'];
    $sqh=$dbh->prepare($sql);
    $sqh->execute($params);
    $retdata=$sqh->fetchAll(PDO::FETCH_ASSOC);
    #print_r($retdata);
    $starttime=$retdata[1]['at'];
    $last=end($retdata);
    $stoptime=$last['at'];
    $data['datapoints'][]=$retdata;
    $data['unit'][]=$unit;
    $sql="select station.name from station  left join sensor on station.id=stationid where sensor.id=?";
    $sqh=$dbh->prepare($sql);
    $sqh->execute(array($sensorid));
    $f=$sqh->fetchAll(PDO::FETCH_ASSOC);
    //print_r($f);
    $data['station'][]=$f[0]['name'];
  
  }
  $data['starttime']=$starttime;
  $data['stoptime']=$stoptime;
  $data['stepline']=$stepline;
  if(isset($_GET['DEBUG']) && $_GET['DEBUG']){
    $data['debug']['sql']=$sql;
    $data['debug']['name']=$_GET['stream'];
    $data['debug']['from']=$_GET['from'];
    $data['params']=$params;
  }	
  print(json_encode($data));
  exit();
}
throw new jsonException("Unknown action :${_GET['a']}");
}

catch(jsonException $e){
  echo(json_encode(array('error'=>$e->getMessage())));
}
catch(Exception $e){
  print_r($_GET);
  echo("<br /><br />");
  print_r($params);
  echo("<br /><br />");
  echo($sql);
  echo("<br /><br />");
  echo($e->getMessage());
}
?>
