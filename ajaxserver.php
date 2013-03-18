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
// TODO add on a header to signal error
    exit( "<p>Cannot connect - $message</p>");
  }

$sql='select temp as "value",datetime as "at" from temp_stream where name=? and datetime>? and datetime < ?';
$sql='select temp as "value",to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from temp_stream where name=? and datetime>? and datetime < ? order by datetime';

$sqh=$dbh->prepare($sql);
$sqh->execute(array($_GET['stream'],$_GET['from'],$_GET['to']));
$data=$sqh->fetchAll(PDO::FETCH_ASSOC);

if($_GET['type']=='svg'){
// for future use
}else{
// return json
	print('{
  	"datapoints":');
	print(json_encode($data));
	print('}');
}
?>