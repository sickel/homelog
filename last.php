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



$sql='select * from lastmeas_complete';
//print_r($_GET);
if(!(array_key_exists("showall",$_GET) && $_GET['showall']>"")){
    $sql.=" where main = true";
}
//print_r($sql);
$sqlh=$dbh->prepare($sql);
$sqlh->execute();
$data=$sqlh->fetchAll(PDO::FETCH_ASSOC);
if(array_key_exists('json',$_GET)){
  header('Content-type: application/json');
  die(json_encode($data));
}
//print_r($data);

$showage=array_key_exists('age',$_GET);
//print('<table class="lastlist">');
$age=$showage?"<th>alder (min)</th>":"";
//print("<tr><th>Måling</th><th>Verdi</th>$age</tr>\n");
$sensors=array_key_exists('s',$_GET)?$_GET['s']:array();
$smarty->assign('data',$data);
/*foreach ($data as $s){
  if($s['main'] || $_GET['showall']){
    $class=$s['since']< 60*20?'default':'olddata';
    $s['since']=round($s['since']/60);
    $txt="<a href=\"stripchart.php?selid=${s['sensorid']}\">${s['type']} ${s['station']}</a>   </td><td class=\"right\"> <b>${s['value']} ${s['unit']}</td>";
    if($showage){
      //$txt.="<td class=\"center\">${s['since']}</td>";
    }
    if(array_key_exists('showids',$_GET)){
      $txt="(${s['sensorid']}) $txt";
    }	
    print("<tr class=\"$class\"><td class=\"right\">$txt</tr>\n");
  }
}

print("</table><hr />");
*/
date_default_timezone_set('Europe/Oslo');
$smarty->assign('vlevel',voltagelevel());

$smarty->display('last.tpl');
?>
