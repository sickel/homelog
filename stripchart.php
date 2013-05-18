
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml" lang="en-US"><head> 

<!--
ååå   // to force utf8
$Id: header.tpl 703 2011-02-15 09:09:07Z radioecology $
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

<label for="from">From</label><input value="2013-02-20 00:00:00" id="from"><br />
<label for="from">To</label><input id="to"><br />
   <button id="btBack">&lt;-</button>
   <button id="btLoad">Load Data</button><button id="btLastWeek">Last Week</button>   <button id="btForward">-&gt;</button>
<br />
<div id="stripdiv0" class="stripchartdiv">

<select class="paramchooser" id="paramchoose0" name="paramchoose0">
	<option label="Inne" value="Inne">Inne</option>
	<option selected="selected" label="Ute" value="Ute">Ute - sørvegg</option>
	<option label="Ute - skygge" value="Ute - skygge">Ute - østvegg</option>
	<option label="Inne-Ute" value="Inne-Ute">Inne-Ute</option>
      <option label="Trykk" value="Trykk">Trykk</option>
      <option label="Trykk - 0m" value="Trykk - 0m">Trykk - 0m</option>
      <option label="Fuktighet" value="Fuktighet">Fuktighet</option>
      <option label="Fuktighet DHT22" value="Fuktighet DHT22">Fuktighet DHT22</option>
      <option label="Temp DHT22" value="Temp DHT22">Temp DHT22</option>
      <option label="Temp DHT11" value="Temp DHT11">Temp DHT11</option>
      <option label="Temp BHP085" value="Temp BHP085">Temp BHP085</option>
      <option label="Forbruk" value="Forbruk">Forbruk</option>
      <option label="Inne - test" value="Inne - test">Inne (test)</option>
      <option label="Soloppvarming" value="Soloppvarming">Soloppvarming</option>
      <option label="Skygge" value="Skygge">Skygge</option>
      <option label="Sørvegg - dagsnitt" value="Sørvegg - døgnsnitt">Sørvegg - dagsnitt</option>
      
	
</select>
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
 </object><br/>
Minimum: <span id="minval"> </span> <br />Maximum: 
<span id="maxval"> </span><br />
Last value: <span id="logvalue0">&nbsp;</span> 
</div>
</div>
<div id="footer">
<p><a href="http://sickel.net/blogg/?p=1506">Information</a> and <a href="https://github.com/sickel/homelog">source code</a></p>
<p> <span id="log"> </span> <span id="error" class="errormsg"> </span><span id="p_status"> </span></p>
<hr>
<ul class="horizmenu">
</ul></div>
</body></html>