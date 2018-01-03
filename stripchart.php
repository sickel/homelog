

<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml" lang="en-US"><head> 

<!--
ååå   // to force utf8
-->

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="createdby" content="Morten A. Kolberg Sickel">

<title>Measurements</title>
<link rel="stylesheet" type="text/css" media="screen,projection,handheld and (min-device width:801px)" href="msi_smarty.css" charset="utf-8">
<link rel="stylesheet" type="text/css" media="screen and (min-resolution:250)" href="handheld.css" charset="utf-8"/> 
   <!-- min-resolution:250 -->
<link rel="stylesheet" type="text/css" media="print" href="print.css"> 
<!-- <link rel="stylesheet" href="handheld.css" type="text/css" media="handheld,screen" /> -->

<!-- <script type="text/javascript" src="svg.js" data-path="."></script> -->
	  <script type="text/javascript" src="/libs/prototype.js"></script>
<!--	  <script src="/libs/moment.js"></script>
	  <script type="text/javascript" src="/libs/momentum.js"></script> -->
	  <script type="text/javascript" src="chart.js"></script>
	  <script type="text/javascript" src="stripchart.js"></script>
	
</head><body>
<!--

(c) Morten Sickel February 2011 licenced under the GNU GPL 2 or later


-->
<div id="main">
<?php
   $fromvalue=$_GET['from'];
// Must parse the values to make sure they are valid dates
$tst=date_parse($fromvalue);
if($tst['error_count']){$fromvalue='';}
$tovalue=$_GET['to'];
$tst=date_parse($tovalue);
if($tst['error_count']){$tovalue='';}
printf('<label for="from">From</label><input value="%s" id="from"><br />
<label for="to">To</label><input id="to" value="%s">',$fromvalue,$tovalue);
?>
   <button id="btLoad">Load Data</button>
<br />
<button id="btBack">&lt;-</button>
<button id="bt2xBack">2x</button>
<button id="btLastDay">Last Day</button>
<button id="btLastWeek">Last Week</button>
<button id="btLastMonth">Last Month</button>
<button id="btLastYear">Last Year</button>
<button id="bt2xForward">2x</button>
<button id="btForward">-&gt;</button><br />
<div id="stripdiv0" class="stripchartdiv">
<select class="paramchooser" id="paramchoose0" name="paramchoose0">
<?php
   $selected=$_GET['selected']?$_GET['selected']:"Ute";
   $selid=$_GET['selid']?$_GET['selid']:0;
   
   

 $dbtype='pgsql';
  include('dbconn.php'); // sets the values username, server, database and password
  //$unit="&deg;C";
  try{
    $connectstring=$dbtype.':host='.$server.';dbname='.$database;
    $dbh = new PDO($connectstring, $username, $password);
    if($dbtype=='pgsql'){
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
  }
  catch(PDOException $e){
    header('HTTP/1.1 500 Internal Server Error');
    $message=$e->getMessage(); 
    exit( "<p>Cannot connect - $message</p>");
  }

  $sql="select concat,id from sensorlist order by stationname,priority";
  $qry=$dbh->prepare($sql);
  $qry->execute();
  $sensorset=$qry->fetchAll(PDO::FETCH_ASSOC);
//  print_r($sensorset);
  foreach($sensorset as $s){
        $params[$s['id']]=$s['concat'];}
//   print_r($sensors);


$paramsold=array("Inne"=>"Inne",
		 "Ute"=>"Ute - sørvegg",
		 "Ute - skygge"=>"Ute - østvegg",
		 "Inne-Ute"=>"Inne-Ute",
		 "Trykk"=>"Trykk",
		 "Trykk - 0m"=>"Trykk - 0m",
		 "Fuktighet" =>"Fuktighet",
		 "Fuktighet DHT22" =>"Fuktighet DHT22",
		 "Temp DHT22" =>"Temp DHT22",
		 "Temp DHT11" =>"Temp DHT11",
		 "Temp BHP085" =>"Temp BHP085",
		 "Forbruk" =>"Forbruk",
		 "Inne - test" =>"Inne - test",
		 "Soloppvarming" =>"Soloppvarming",
		 "Skygge" =>"Skygge",
		 "Sørvegg - døgnsnitt" =>"Sørvegg - døgnsnitt",
		 "Sørvegg - døgnmin" =>"Sørvegg - døgnmin",
		 "Sørvegg - dagmax" =>"Sørvegg - døgnmax");
     foreach($params as $k=>$v){
       print("<option ");
       if($v==$selected || $k==$selid){print 'selected="selected" ';}
       printf('label="%s" value="%s" >%s</option>',$v,$k,$v);
       print("\n");
     }
?></select>
<img id="spinner" src="ajax-bar.gif" />
<span id="reportvals"></span><span id="mousex0" ></span>&nbsp;<span id="mousey0" ></span><br />
<!--[if !IE]>-->
  <object style="visibility: visible; overflow: hidden;" data="stripchart.svg" type="image/svg+xml" class="svg" id="stripchart0" name="stripchart" width="530" height="300"> 
<!--<![endif]-->
<!--[if lt IE 9]>
  <object src="stripchart.svg" classid="image/svg+xml" class="svg"
     width="275" height="430" id="stripchart0" name="stripchart"> 
<![endif]-->
<!--[if gte IE 9]>
  <object data="stripchart.svg" type="image/svg+xml" class="svg"
    width="275" height="430" id="stripchart0" name="stripchart">
<![endif]-->
 </object>
 <?php
// print("<p>$selected</p>");
//   print("<p>$selid</p>");
   
 $location="http://www.yr.no/sted/Norge/Akershus/Frogn/Karlsrud";
 $weatherdata="$location/time_for_time_detaljert.html" ;
 $meteogram= "$location/avansert_meteogram.png";
 printf('<a href="%s" target="yr"><img src="%s" />',$weatherdata,$meteogram);
 ?>
</a><br/>
 Minimum: <span id="minval"> </span> <br />Maximum: 
<span id="maxval"> </span><br />
Last value: <span id="logvalue0">&nbsp;</span> 
</div>
</div>
<div id="footer">
<p><a href="last.php">Last values</a> <a href="list.php">Last value list</a></p>
<?php
if(strpos($_SERVER['REMOTE_ADDR'],'192.168')===0){
  print("<a href=\"kwh.php\">kWh-registration</a><br />");
}
?>

<p><a href="http://sickel.net/blogg/?p=1506">Information</a> and <a href="https://github.com/sickel/homelog">source code</a></p>
<p> <span id="log"> </span> <span id="error" class="errormsg"> </span><span id="p_status"> </span></p>
<hr><ul class="horizmenu">
</ul></div></body></html>
