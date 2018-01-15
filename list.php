<?php

include('connect_db.php');
require 'smarty3/Smarty.class.php';
$smarty = new Smarty;
//$smarty->force_compile = true;
//$smarty->debugging = true;
$smarty->caching = false;
$smarty->cache_lifetime = 120;
$smarty->assign('pagetitle',"Measurement list");

/*
 The following query will fetch the last measurement for each sensor that is flagged as active
 The age of the measurement is used to flag too old measurements in the frontend. It is easier to calculate it here 
 and then just have a number to handle afterwards.
 the table should have an index on (sensorid,datetime desc) or the query will run slowly when there start to be about 100k records
*/

$limit=30;
if(array_key_exists('limit',$_GET)){
    $limit=$_GET['limit']*1;
}
if($limit<=0){
   $limit =30;
}
$sql='select id,sensorid,type,value,datetime,use,aux,payload,stationid,senderid from corr_measure';
$stid=0;
if(array_key_exists('stationid',$_GET)){
    $stid=$_GET["stationid"]*1;
}
$seid=0;
if(array_key_exists('senderid',$_GET)){
    $seid=$_GET["senderid"]*1;
}
if($stid>0){
    $sql.=' where stationid='.$stid;
}elseif ($seid>0){
    $sql.=' where senderid='.$seid;
}
$sql=$sql.' order by id desc limit ';
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
$showage=array_key_exists('age',$_GET)?1:0;
$age=$showage?"<th>alder (min)</th>":"";
$sensors=array_key_exists('s',$_GET)?$_GET['s']:array();
$trclass="";
$smarty->assign('data',$data);
date_default_timezone_set('Europe/Oslo');
$smarty->assign('vlevel',voltagelevel());
$smarty->display('list.tpl');
?>
