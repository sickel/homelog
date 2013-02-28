/*

$Id: stripchart.js 707 2011-02-16 14:08:23Z radioecology $
(c) Morten Sickel February 2011 licenced under the GNU GPL 2 or later
*/

window.addEventListener('SVGLoad', svginit , false)
window.onload=pageonload;



var pe;            // the periodical executer - must be available
var running=false; // flags if the pe is running - or can check if it exists
var url="ajaxserver.php";  // The url of the program that serves the data to be presented
var lastid=0;         // id of the last fetched dataset
var maxlength=400;    // maximum length of the polyline
var comp="eq";        // used by the server 
var oldfileid=-1;     // id of datafile in which the last set did belong
var n=0;              // counts number of datasets fetched in the present file
var charts=new Array();  // array to hold the chart objects
var calid=0;          // used by the server

function pageonload(event){
	// $('btStart').onclick=btstartstrip;
	$$('.maxsetter').each(function(setter){
		Event.observe(setter,'change',setmax);
	}); 
	$$('.paramchooser').each(function(chooser){
		Event.observe(chooser,'change',setparam);
	});
}

function setparam(event){
	var target=event.element();
	//alert(target.value);
	var chartid=target.parentNode.next('object').id;
	var targetchart;
	charts.each(function(chart){
	// must find the right object to work on
		if(chart.getsvgid()==chartid){
			targetchart=chart;
		}
	});
	targetchart.setparameter(target.value);
	
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
		charts[i].setmax($('stripchart'+i+'max').value);
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

function fetchData(event){ // This is called by the periodical executer
	if(lastid!=prevsent){ 
	// if thing goes too slow, the same dataset may be fetched twice. 
		param=$H({ // All these values are dependent on the backend server...
				a: 'nucdata'
				,json:'json'
				,fileid:$('fileid').value
				,specid: lastid
				,calid: calid
				,nucs:'K,Th,U,Cs'
				,comp:comp
				});
		ajax=new Ajax.Request(url,
			{method:'get',
				 parameters: param.toQueryString(),
				 onComplete: hHR_nucdata}
			);
		prevsent=lastid; 
	}
}

function hHR_nucdata(response,json){ // The response function to the ajax call
	if(Object.inspect(json)){
		jsondata=response.responseText.evalJSON();
		if(jsondata.error>''){
			$('error').innerHTML=jsondata.error;
		}
		var dataset=$A(jsondata.data);
		if(dataset.size()>1){
			$('log').innerHTML=n++;
			lastid=dataset[dataset.size()-2].id; // There is one invalid record at the end - an ugly server-end hack
			$('log').innerHTML+='|'+lastid;
		}
		if(jsondata.lastset=='true'){
			$('p_status').innerHTML='End of data set';
			
		}
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
			if(!(Object.isUndefined(val['Cs']))){ // checks if the set is OK
				charts.each(function(chart){
					chart.addpoint(val); // sends the entire set to each chart, the chart is responsible of selecting the right point
				});
			}
		}); 
		//$('p_status'+chartid).innerHTML+='|'+pnts.length;
		charts.each(function(chart){
			chart.drawstrip();
		});
		
	}else{
		 $('p_status').innerHTML="no JSON object";
	}
}