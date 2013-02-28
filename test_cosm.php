<?php
/* Uploading data to cosm.com. 
   - to be set as a "more info" under "Cosm will pull data"

   Expecting to receive a json string with a "temp"-array listing the 
   temperatures from the sensors (it may also contain other information, but
   that will be discarded )
   e.g. 
   {"temp":[40.50,20.50,-0.50],
    "address":["2852932640040","28A9D0264006D","2895D76E40050"],
    "millis":130526498}

Morten Sickel, feb 2013
Licensed under gpl 2.0 or later

*/

$server="192.168.0.177"; // Server address, if set up in dns, a name works fine
$where="/json";          // Where on the server the informastion is
include('apikey.php');   // just $apikey="xxxxx....";
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
        $data.=(fgets($fp, 128));
    }
    fclose($fp);
}
$data=split("\n",$data); // To split the header and json
$jsondata=json_decode($data[3]); // the json string is in $data[3]
header("X-ApiKey: $apikey");

/* having more sensors, this conversion should be rewritten to have an 
   array with the ids and make a loop over the values */

$streams=array(
   array("NA",0,0),
   array("Inne",-80,80),
   array("Ute",-80,80));
print('{
  "version":"1.0.0",
  "datastreams":[');
$i=0;
$arry=array();
foreach($streams as $st){
  if($st[1]<$st[2]){
$arry[]=sprintf('{"id":"%s", "current_value":"%.2f"}',$st[0],$jsondata->temp[$i]);
  }
$i++;
}
print(implode(',',$arry));
print("]}");
 