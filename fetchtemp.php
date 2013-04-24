<?php

/* Fetching json data  - storing them in a postgres database 

	Data source, e.g. an arduino temperature logger

  Expecting to receive a json string with a "temp"-array listing the
  temperatures from the sensors (it may also contain other information, but
  that will be discarded )
  e.g.
  {"temp":[40.50,20.50,-0.50],
   "address":["2852932640040","28A9D0264006D","2895D76E40050"],
   "millis":130526498}

  The data source is not supposed to have a real time clock. If timestamps are
  needed, they must be set by the database (or added in in this script)

*/

$url="http://192.168.0.177/json";
$database='wdb';
$dbserver='localhost';
include('dbconn.php');
$dbtype='pgsql';
try{
    $connectstring=$dbtype.':host='.$dbserver.';dbname='.$database;
    $dbh = new PDO($connectstring, $username, $password);
    if($dbtype=='pgsql'){
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

  }
  catch(PDOException $e){
    $message=$e->getMessage(); 
    exit( "<p>Cannot connect - $message</p>");
  }

$server="192.168.0.177";

$sql="select addmeasure(?,(select id::integer from sensor where sensoraddr=?))";
$sh=$dbh->prepare($sql);

while(true){
  $fp = fsockopen($server, 80, $errno, $errstr, 30);
  $data='';
  if (!$fp) {
    echo "$errstr ($errno)<br />\n";
  } else {
    $out = "GET /json HTTP/1.1\r\n";
    $out .= "Host: $server\r\n";
    $out .= "Connection: Close\r\n\r\n";
    fwrite($fp, $out);
    while (!feof($fp)) {
        $data .= fgets($fp, 128);
    }
    fclose($fp);
  }
  $data=split("\n",$data);
  $jsondata=json_decode($data[3]);
  # $sql="insert into temps(temp,sensoraddr)values(?,?)";
  for($i=0;$i<count($jsondata->temp);$i++){
    $sh->execute(array($jsondata->temp[$i],$jsondata->address[$i]));
  }
  sleep(60*15-3); // Waits approx 15 minutes for next reading
}

?>
