/*

(c) Morten Sickel February 2011 licenced under the GNU GPL 2 or later
*/

window.addEventListener('SVGLoad', svginit , false)
window.onload=pageonload;



var pe;            // the periodical executer - must be available
var running=false; // flags if the pe is running - or can check if it exists
var url="ajaxserver.php";  // The url of the program that serves the data to be presented
var lastid=0;         // id of the last fetched dataset
var maxlength=418;    // maximum length of the polyline
var datasetsize=0;        // size of dataset
var comp="eq";        // used by the server 
var oldfileid=-1;     // id of datafile in which the last set did belong
var n=0;              // counts number of datasets fetched in the present file
var charts=new Array();  // array to hold the chart objects
var calid=0;          // used by the server
var stream="Ute";
var prevfrom= new Date();
var prevto=new Date();
var exdays=1;  // days before cookies expires


function formattime(date){
    return(''+(date.getYear()+1900)+'-'+(date.getMonth()+1)+'-'+date.getDate()+' 00:00:00');
}



function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}


function pageonload(event){
//    $('btLoad').onclick=fetchdata;
    Event.observe($('btLoad'),'click',fetchData);
    Event.observe($('btLastWeek'),'click',loadtimespan);
    Event.observe($('btBack'),'click',pagetime);
    Event.observe($('btForward'),'click',pagetime);
    Event.observe($('bt2xBack'),'click',pagetime);
    Event.observe($('bt2xForward'),'click',pagetime);
    Event.observe($('btLastMonth'),'click',loadtimespan);
    Event.observe($('btLastYear'),'click',loadtimespan);
    Event.observe($('btLastDay'),'click',loadtimespan);
    svginit(event);
    $$('.paramchooser').each(function(chooser){
	Event.observe(chooser,'change',setparam);
    });
    // sets default: Fetches data for the last week
    // TODO: use get-parameters
    if($('from').value==''){
    	settimespan(event);
    }
//    alert(document.cookie);
    fetchData(event); 
    
}

function loadtimespan(event){
    settimespan(event);
    fetchData(event);
}

function settimespan(event){
    var date=new Date();
    var ndays=7;
    if (event.target.id=="btLastDay"){
      ndays=1;
    }
    if (event.target.id=="btLastMonth"){
	// Change to go one month back...
	// Subtract on the month value
      ndays=30;
    }
    if(event.target.id==="btLastYear"){
     // correct for leap year...
      ndays=365;
    }
    date.setHours(0,0,0,0); // from midnight
    date.setTime(date.getTime()-ndays*24*3600*1000); // back ndays days
    $('from').value=formattime(date);
    $('to').value='';
}

function stringtodate(string){
    var elems=string.split(/[\s:-]/);
    var strtime = new Date(elems[0],elems[1]-1,elems[2],elems[3],elems[4],elems[5]);
    return(strtime);
}


function savetimes()
    {}
    
    
function pagetime(event){
    var target=event.element();
    var id=target.id;
    var timespan;
    var totime;
    var fromtime=stringtodate($('from').value);
    if( $('to').value==''){
	totime=new Date();
    }else{
	totime=stringtodate($('to').value);
    }
    timespan=totime.getTime()-fromtime.getTime();
    if (id=="btBack"){
	$('to').value=$('from').value;
	fromtime.setTime(fromtime.getTime()-timespan);
	$('from').value=formattime(fromtime);
    }else if (id=="btForward"){
	if(stringtodate($('to').value).getTime()<Date.now()){
	    $('from').value=$('to').value;
	    totime.setTime(totime.getTime()+timespan);
	    $('to').value=formattime(totime);
	}
	// Should not allow times entirely in the future...
    }else if (id=="bt2xBack"){
	fromtime.setTime(totime.getTime()-2*timespan);
	$('from').value=formattime(fromtime);
    }else if (id=="bt2xForward"){
	if(stringtodate($('to').value).getTime()<Date.now()){
	    totime.setTime(fromtime.getTime()+2*timespan);
	    $('to').value=formattime(totime);
	}
    }
    fetchData(event);
}



function setparam(event){
  /*  var target=event.element();
    //alert(target.value);
 //   var chartid=target.parentNode.next('object').id;
    var targetchart;
    charts.each(function(chart){
	// must find the right object to work on
//	if(chart.getsvgid()==chartid){
			targetchart=chart;
	}
    }); 
  //  targetchart.setparameter(target.value);
    */
}

function setmax(event){
	// Called when the max value field is changed for a chart
    var target=event.element();
    var chartid=target.parentNode.next('object').id;
    var targetchart;
    charts.each(function(chart){
	// must find the right object to work on
	if(chart.getsvgid()==chartid){
	    targetchart=chart;
	}
    });
    targetchart.setmax(target.value);
	
}

function svginit(event){
/* 	Sets up the charts. the first parameter is the id of the svg file, the secound is the
	id of a field used for logging 
*/
	for(i=0;i<1;i++){
		charts[i]=new chart('stripchart'+i,'logvalue'+i);
		charts[i].setparameter($('paramchoose'+i).value);
	    //	charts[i].setmax($('stripchart'+i+'max').value);
		charts[i].setmax(100);
	}
}	


function btstartstrip(event){
	if(running){ 
		pe.stop();
		$('btStart').innerHTML='Start';
		comp='eq';
	}else{
		if($('fileid').value != oldfileid){
			
			// Must set up for a new dataset clears up
			charts.each(function(chart){
				chart.resetpnts(); // clears the chart
			});
			oldfileid=$('fileid').value;
			$('p_status').innerHTML='&nbsp;';
			lastid=0;
			n=0;
			comp='gt';
			
		}
		pe=new PeriodicalExecuter(fetchData, 2);
		$('btStart').innerHTML='Pause';
		$('error').innerHTML='';
	}
	running=!(running);
}



var prevsent; // the dataset id it was asked for last time

function fetchData(event){ // This may be called by a periodical executer
    $('spinner').style.visibility="visible";
    var sensorid=$('paramchoose0').value;
    document.cookie="sensorid="+sensorid; 
    param=$H({ // All these values are dependent on the backend server...
 	a: 'tempdata'
	,stream: sensorid
	,from: $('from').value
	,to: $('to').value
	,add: $('adddata').checked
        ,fool_ie: Math.random()
    });
    // simplest way to stop internet explorer from caching
    ajax=new Ajax.Request(url,
			  {method:'get',
			   parameters: param.toQueryString(),
			   onComplete: hHR_receiveddata}
			 );
}

function pad10(input){
    if (input < 10){
        input="0"+input;
    }
    return input;
}

function convertdate(tzdate,defaultval){
    var date= new Date(tzdate);
    if(date.getYear()>100){
    var hours=pad10(date.getHours());
    var minutes=pad10(date.getMinutes());
    var seconds=pad10(date.getSeconds());
    retvalue=''+(date.getYear()+1900)+'-'+(date.getMonth()+1)+'-'+date.getDate()+' '+hours+':'+minutes+':'+seconds;    
    }else{
        retvalue=defaultval;
    }
    return(retvalue);
}


function hHR_receiveddata(response,json){ // The response function to the ajax call
    if(Object.inspect(json)){
        var jsondata=response.responseText.evalJSON();
        if(jsondata.error>''){
            $('error').innerHTML=jsondata.error;
        }
        if (!($('adddata').checked)){
            $('from').value=convertdate(jsondata.starttime,$('from').value);
            $('to').value=convertdate(jsondata.stoptime,$('to').value);
        }
        var dataset=$A(jsondata.datapoints);
        if(dataset.size()>1){
            $('log').innerHTML=dataset.size();
            datasetsize=dataset.size();
        }
        charts.each(function(chart){
                if(!$('adddata').checked){
                    chart.resetpnts();
                }else{
                    chart.pnts=new Array();
                    chart.timestamps=new Array(); 
                    chart.maxvalue=-1E9;
                    chart.minvalue=1E9;
        
                }
    //	    chart.drawstrip();
            chart.setunit(jsondata.unit);
            chart.setstepline(jsondata.stepline);
        });
        /* TODO - check out how to add options...
        var paramid=$('paramchoose1');
        if(paramid.options.length<2){  // sets new parameters to choose for the strip charts
        var ks=$A($H(dataset[0]).keys());
        //	var chs=$$('.paramchooser');
        //	chs.each(function(ch){
        ks.each(function(key){
        paramid.options.push(key);
        });
        //	});
        }*/
        dataset.each(function(val){
                
            charts.each(function(chart){
            chart.addpoint(val); // sends the entire set to each chart, the chart is responsible of selecting the right point
            });
        });
        //$('p_status'+chartid).innerHTML+='|'+pnts.length;
        charts.each(function(chart){
            chart.drawstrip(jsondata.station+" ("+jsondata.unit+")");
        });
        
    }else{
        $('p_status').innerHTML="no JSON object";
    }
    $('spinner').style.visibility="hidden";

}
