<?php
include('connect_db.php');
require 'smarty3/Smarty.class.php';
$smarty = new Smarty;
$smarty->assign('pagetitle','Homelog - stripchart');

$fromvalue=$_GET['from'];
// Must parse the values to make sure they are valid dates
$tst=date_parse($fromvalue);
if($tst['error_count']){$fromvalue='';}
$smarty->assign('fromvalue',$fromvalue);
$tovalue=$_GET['to'];
$tst=date_parse($tovalue);
if($tst['error_count']){$tovalue='';}
$smarty->assign('tovalue',$tovalue);


$selected=$_GET['selected']?$_GET['selected']:"Ute";
$smarty->assign('selected',$selected);
$selid=$_GET['selid']?$_GET['selid']:0;
$smarty->assign('selid',$selid);



$sql="select concat,id from sensorlist order by stationname,priority";
$qry=$dbh->prepare($sql);
$qry->execute();
$sensorset=$qry->fetchAll(PDO::FETCH_ASSOC);
//  print_r($sensorset);
foreach($sensorset as $s){
    $params[$s['id']]=$s['concat'];}
//print_r($params);
$smarty->assign("sensors",$params);


$location="http://www.yr.no/sted/Norge/Akershus/Frogn/Karlsrud";
$smarty->assign("weatherdata","$location/time_for_time_detaljert.html");
$smarty->assign("meteogram", "$location/avansert_meteogram.png");
 
$smarty->assign('vlevel',voltagelevel());
$smarty->display('stripchart.tpl');

?>   
