<?php

include('connect_db.php');
require 'smarty3/Smarty.class.php';
$smarty = new Smarty;
//$smarty->force_compile = true;
//$smarty->debugging = true;
$smarty->caching = false;
$smarty->cache_lifetime = 120;
$smarty->assign('pagetitle',"Last measurement");

/*
 The following query will fetch the last measurement for each sensor that is flagged as active
 The age of the measurement is used to flag too old measurements in the frontend. It is easier to calculate it here 
 and then just have a number to handle afterwards.
 the table should have an index on (sensorid,datetime desc) or the query will run slowly when there start to be about 100k records
*/



$sql='select * from lastmeas_complete';
//print_r($_GET);
if(!(array_key_exists("showall",$_GET) && $_GET['showall']>"")){
    $sql.=" where main = true";
}
$sql.=" order by unit";
//print_r($sql);
$sqlh=$dbh->prepare($sql);
$sqlh->execute();
$data=$sqlh->fetchAll(PDO::FETCH_ASSOC);
if(array_key_exists('json',$_GET)){
  header('Content-type: application/json');
  die(json_encode($data));
}
$showage=array_key_exists('age',$_GET);
$age=$showage?"<th>alder (min)</th>":"";
$sensors=array_key_exists('s',$_GET)?$_GET['s']:array();
$smarty->assign('data',$data);
date_default_timezone_set('Europe/Oslo');
$smarty->assign('vlevel',voltagelevel());

$smarty->display('last.tpl');
?>
