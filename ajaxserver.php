<?php

$dbtype='pgsql';
include('dbconn.php');
require_once $_SERVER['DOCUMENT_ROOT'].'/../libs/dblibs2.php';
$sql='select temp as "value",datetime as "at" from temp_stream where name=? and datetime>? and datetime < ?';
$sql='select temp as "value",to_char(datetime at time zone \'UTC\' ,\'yyyy-mm-dd"T"HH24:MI:SS"Z"\') as "at" from temp_stream where name=? and datetime>? and datetime < ?';

#$sqh=$dbh->prepare($sql);
#$sqh->execute(array($_GET['stream'],$_GET['from'],$_GET['to']);
$data=fetchset($sql,array($_GET['stream'],$_GET['from'],$_GET['to']),PDO::FETCH_ASSOC);
if($_GET['type']=='svg'){

}else{
	print('{
  	"datapoints":');
	print(json_encode($data));
	print('}');
}
?>