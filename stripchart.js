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

function formattime(date){
    return(''+(date.getYear()+1900)+'-'+(date.getMonth()+1)+'-'+date.getDate()+' 00:00:00');
}

function pageonload(event){
//    $('btLoad').onclick=fetchdata;
    Event.observe($('btLoad'),'click',fetchData);
    Event.observe($('btLastWeek'),'click',loadtimespan);
    svginit(event);
    $$('.paramchooser').each(function(chooser){
	Event.observe(chooser,'change',setparam);
    });
    // sets default: Fetches data for the last week
    // TODO: use get-parameters
    settimespan(event);
    fetchData(event); 
}

function loadtimespan(event){
    settimespan(event);
    fetchData(event);
}

function settimespan(event){
    var date=new Date();
    date.setHours(0,0,0,0); // from midnight
    date.setTime(date.getTime()-7*24*3600*1000); // back one whole week
    $('from').value=formattime(date);
    $('to').value='';
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
    param=$H({ // All these values are dependent on the backend server...
 	a: 'tempdata'
	,stream: $('paramchoose0').value
	,from: $('from').value
	,to: $('to').value
    });
    ajax=new Ajax.Request(url,
			  {method:'get',
			   parameters: param.toQueryString(),
			   onComplete: hHR_receiveddata}
			 );
}

function hHR_receiveddata(response,json){ // The response function to the ajax call
    if(Object.inspect(json)){
	jsondata=response.responseText.evalJSON();
	if(jsondata.error>''){
	    $('error').innerHTML=jsondata.error;
	}
	var dataset=$A(jsondata.datapoints);
	if(dataset.size()>1){
	    $('log').innerHTML=dataset.size();
	    datasetsize=dataset.size();
	}
	charts.each(function(chart){
	    chart.resetpnts();
//	    chart.drawstrip();
	    chart.setunit(jsondata.unit);
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
	    chart.drawstrip();
	});
	
    }else{
	$('p_status').innerHTML="no JSON object";
    }
    $('spinner').style.visibility="hidden";

}