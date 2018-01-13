<?php



include('connect_db.php');

require 'smarty3/Smarty.class.php';
$smarty = new Smarty;
//$smarty->force_compile = true;
//$smarty->debugging = true;
$smarty->caching = false;
$smarty->cache_lifetime = 120;


/*
 The following query will fetch the last measurement for each sensor that is flagged as active
 The age of the measurement is used to flag too old measurements in the frontend. It is easier to calculate it here 
 and then just have a number to handle afterwards.
 the table should have an index on (sensorid,datetime desc) or the query will run slowly when there start to be about 100k records
*/

$sql='SELECT DISTINCT ON (sd.sensorid, sd.type) 
    sd.sensorid,sd.value/sensor.factor as value,sd.datetime,station.name as station
   FROM measure sd
     LEFT JOIN sensor ON sensor.stationid = sd.sensorid AND sd.type = sensor.typeid left join station on sd.stationid=station.id
  WHERE NOT sd.value IS NULL and sd.type=118 and not sd.stationid is null
  ORDER BY sd.sensorid, sd.type, sd.datetime DESC';
;
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
foreach ($data as &$s){
    $value=$s['value'];
    $s['value']=round($value,2);
    $s['vstatus']=levelclass($s['value']);
    $s['unit']='V';
   // print("<tr class=\"$class\"><td class=\"right\">$txt</tr>\n");

}
$smarty->assign('data',$data);
//print_r($data);
date_default_timezone_set('Europe/Oslo');
$smarty->assign("vlevel",100);
$smarty->display('last_voltages.tpl');

?>
