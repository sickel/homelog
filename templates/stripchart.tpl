{include file='page_head_noupdate.tpl'}
<div id="main">
<label for="from">From</label><input value="{$fromvalue}" id="from"><br />
<label for="to">To</label><input id="to" value="{$tvalue}">
<button id="btLoad">Load Data</button><input type="checkbox" id="adddata" /> Add data<br />
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
{foreach $sensors as $k=>$v}
<option {if $k==$selid}selected="selected" {/if}label="{$v}" value="{$k}" >{$v}</option>
{/foreach}
</select> <!-- Snitt over :
<select id="average" name="average">
<option value="none">Rådata</option>
<option value="hour">Time</option>
<option value="day">Døgn</option>
</select> -->
<img id="spinner" src="ajax-bar.gif" /><br />
<span id="reportvals"></span><span id="mousex0" ></span>&nbsp;<span id="mousey0" ></span><br />
<!--[if !IE]>-->
  <object style="visibility: visible; overflow: hidden;" data="stripchart.svg" type="image/svg+xml" class="svg" id="stripchart0" name="stripchart" width="530" height="450"> 
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
<a href="{$weatherdata}" target="yr"><img src="{$meteogram}" /></a><br/>
Minimum: <span id="minval"></span><br/>Maximum: <span id="maxval"> </span><br />Last value: <span id="logvalue0"></span> 
<p><a href="" id="jsondata" target="jsondata">JSON</a> <span id="log"> </span> <span id="error" class="errormsg"> </span><span id="p_status"> </span>
</div></div>
{include file='page_foot.tpl'}
