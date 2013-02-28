<?php

$dbtype='pgsql';
include('dbconn.php');
require_once $_SERVER['DOCUMENT_ROOT'].'/../libs/dblibs2.php';
$sqh=$dbh->prepare("select  * from temps order by id desc limit 10");
print_r($dbh->errorInfo());
$sqh->execute();
print_r($sqh->fetchAll());

print_r(json_encode(fetchdata("select  * from temps order by id desc limit 10",'',PDO::FETCH_ASSOC)));

?>