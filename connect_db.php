<?php

$dbtype='pgsql';
include('dbconn.php');
// sets the values username, server, database and password

try{
    $connectstring=$dbtype.':host='.$server.';dbname='.$database;
    $dbh = new PDO($connectstring, $username, $password);
    if($dbtype=='pgsql'){
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

  }
  catch(PDOException $e){
    $message=$e->getMessage(); 
    exit( "<p>Cannot connect - $message</p>");
  }
  
function voltagelevel(){
  global $dbh;
  $sql="select min(value) from lastmeas_complete where unit='V'";
  $qry=$dbh->prepare($sql);
  $qry->execute();
  $value=$qry->fetchAll(PDO::FETCH_ASSOC)[0]['min'];
  return(levelclass($value));
}
  
  
function levelclass($value){
  $vstatus='critical'; $color='red';
  if($value > 3.4){$vstatus='low'; $color='yellow';}
  if($value > 3.6){$vstatus='OK'; $color='green';}
  return($vstatus);
}
  
?>
