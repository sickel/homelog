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
?>
<html><head><title>Last values</title>
<meta http-equiv=refresh content='60; url=list.php'>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="tempdata.css">
<link rel="stylesheet" type="text/css" media="screen,projection,handheld and (min-device width:801px)" href="msi_smarty.css" charset="utf-8">
</head><body>
<p><a href="list.php">Unfiltered</a></p>
<?php 
$showage=array_key_exists('age',$_GET)?1:0;
print('<table>');
$age=$showage?"<th>alder (min)</th>":"";
print("<tr><th>Id</th><th>Sensor</th><th>Type</th><th>Verdi</th><th>Tid</th><th>Bruk</th><th>aux</th><th>Nr</th><th>Stasjon</th><th>Sender</th></tr>\n");
$sensors=array_key_exists('s',$_GET)?$_GET['s']:array();
$trclass="";
foreach ($data as $s){
  $new=true;
  $trclass=($trclass!="odd"?"odd":"even");
  print("<tr class=\"${trclass}\">");
  foreach ($s as $k=>$t){
     if($new){
        $sensorid=$s["sensorid"];
        print("<td class=\"right\"><a href=\"http://sjest/homelog/stripchart.php?selid=${sensorid}\">$t</a></td>");
        $new=false;
     }else{
        if($k=='stationid'){
           $t="<a href=list.php?stationid=$t>$t</a>";
        }elseif($k=='senderid'){
           $t="<a href=list.php?senderid=$t>$t</a>";
           }
        print("<td class=\"right\">$t</td>");
     }
  }
  print("</tr>\n");
}

print("</table><hr />");

date_default_timezone_set('Europe/Oslo');
print("<p>Oppdatert ".date('d/m/Y H:i:s', time())."</p>");
?>
<p><a href="stripchart.php">Stripchart</a>  <a href="last.php">Last value</a></p>
</body></html>
