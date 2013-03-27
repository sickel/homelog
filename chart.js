/*

(c) Morten Sickel February 2011 licenced under the GNU GPL 2 or later
*/

function chart(svgobjid,loggerid){
    this.id=svgobjid;
    this.svgobj=document.getElementById(svgobjid).contentDocument; // The svg document
    this.logger=$(loggerid); // writing log messages here
    this.pnts=new Array();   // the measurement points
    this.timestamps=new Array(); // timestamps of each measurement
    this.parameter='';       // to set the parameter to be logged
    this.factor=1;
    this.defaultmax=250;     // numbers of pixels on the positive side, corresponds to a factor 1
    this.setparameter=setparameter;
    this.setfactor=setfactor;
    this.getsvgid=getsvgid;
    this.setmax=setmaxvalue;
    // 
    this.drawstrip=drawstrip; // draws the chart
    this.addpoint=addpoint;   // adds a point to the chart
    this.resetpnts=resetpnts; // resets the plot
    this.minvalue=1E18;
    this.maxvalue=-1E18;
    this.mintime;
    this.maxtime;
    this.createtempline=createtempline;
    this.unit="&deg;C";
    this.setvlinespacing=setvlinespacing; // Sets the spacing between vertical lines
    // i.e time markers
    // max time (in days), days between main markers, 
    // submarkers from one main maker to next (one included) 
    this.vlinespc=$A([[0,12,12],[2,24,12],[3,24,6],[5,24,4],[8,24,2],[10,24*2,4],[14,24*7,7]]);
    this.yscale=0;
}

function getsvgid(){
    return(this.id);
}


function setmaxvalue(newmax){
    this.factor=this.defaultmax/newmax;
//    this.svgobj.getElementById('xfull').firstChild.nodeValue=newmax;
//    this.svgobj.getElementById('xhalf').firstChild.nodeValue=newmax/2;
    
}

function setvlinespacing(span){ 
    
    var hstep=30*24;
    var pstep=15;
    this.vlinespc.each(function(sps){
	if (span>sps[0]*24*3600*1000){
	    hstep=sps[1];
	    pstep=sps[2];
	}
    }); 
    return {hstep:hstep,
	    pstep:pstep};
   
}


function setfactor(factor){
    this.factor=factor;
}

function setparameter(param){
    this.parameter=param;
    //alert(param);
    this.pnts=new Array();
}

function createline(x1,x2,y1,y2,svg,color){
    color=typeof color !== 'undefined' ? color : 'blue';
    line=svg.createElementNS("http://www.w3.org/2000/svg",'line');
    line.setAttribute('x1',x1);
    line.setAttribute('x2',x2);
    line.setAttribute('y1',y1);
    line.setAttribute('y2',y2);
    line.setAttribute('class','ROIline');
    line.setAttribute('stroke',color);
    line.setAttribute('stroke-width','0.1');
    return line;
}  

function createtempline(temp,svg,color){
    var x=temp*this.factor;
    //line=createline(0,420,x,x,$(this.id),color);
    line=createline(0,420,x,x,svg,color);
    return line;
}

function zpad(n){
    if (n==0){return('00');}
    if(n<10){return('0'+n);}
    return(n);
}

function datestring(ts){
    ts=new Date(ts);
    var hr=zpad(ts.getHours());
    var mn=zpad(ts.getMinutes());
    return(ts.getDate()+'/'+(ts.getMonth()+1)+' '+hr+':'+mn);
  //  return(ts.format("dddd, MMMM Do YYYY, h:mm:ss a"))
    
}

function timestring(ts){
    var hr=zpad(ts.getHours());
    var mn=zpad(ts.getMinutes());
    return(hr+':'+mn);
}

function drawstrip(){
    var lastts=this.timestamps.slice(-1)[0];
    var lastdate=new Date(lastts);
    var timespan=lastts-this.timestamps[0];
    var svg=$(this.id).contentDocument;
    var path=''; // a text string in which the path is constructed
    $('maxval').innerHTML=this.maxvalue+this.unit+' at '+datestring(this.maxtime);
    $('minval').innerHTML=this.minvalue+this.unit+' at '+datestring(this.mintime);
    this.logger.innerHTML=" "+Math.round(this.pnts.slice(-1)[0]*100)/100
	+this.unit+" at "+timestring(lastdate);
    var valspan=this.maxvalue-this.minvalue;
    var yscale=220/valspan;
    var ymove=5+this.maxvalue*yscale;
    var hl=svg.getElementById('horizlines');
    var lf; // number of units between horizontal lines.
    if (valspan > 40){lf = 10;}
    else if(valspan > 20){lf = 5;}
    else if(valspan > 10){lf = 2;}
    else {lf=1;}
    for(var i=Math.ceil(this.minvalue/lf);i<Math.ceil(this.maxvalue/lf);i++){
	var text=svg.createElementNS("http://www.w3.org/2000/svg",'text');
	var ycrd=ymove-i*lf*yscale;
	text.appendChild(svg.createTextNode(lf*i));
	text.setAttribute("x",-25);
	text.setAttribute("y",ycrd+5);
	text.setAttribute("font-size",12);
	hl.appendChild(text);	   
	if(i==0){
	    line=createline(0,420,ycrd,ycrd,svg,'red');
	    
	}else{
	    line=createline(0,420,ycrd,ycrd,svg,'grey')
	}
	line.setAttribute('stroke-width','0.3');
	hl.appendChild(line);
    }
    var starttime=this.timestamps[0];
    var xfact=timespan/maxlength;
    if(this.pnts.length >0){
	var i=0;
	for (i=1;i<= this.pnts.length; i++){
	    xcrd=Math.round((this.timestamps[i-1]-starttime)/xfact*10)/10;
	    ycrd=ymove-this.pnts[i-1]*yscale;
	    path=''+xcrd+","+ycrd+" "+path;
	    // to make a horisontal rather than a vertical point, use
	    // path+=i+","+(this.pnts[i-1])*this.factor+" ";		
	}
    }
    var chartline=this.svgobj.getElementById('temp1');
    chartline.setAttribute("points", path );
    var linetime=new Date(this.timestamps[0]);
    linetime.setHours(0,0,0,0);
    var g=svg.getElementById('xlines');
    linetime=Date.parse(linetime);
    var stoptime=this.timestamps[this.timestamps.length-1];
    var stps=this.setvlinespacing(timespan)
    var hstep=stps.hstep;
    var pstep=stps.pstep;
    var sstep=hstep*3600*1000;
    var partstep=sstep/pstep;
    while(linetime<stoptime){
	for(nstep=0;nstep<pstep;nstep++){
	    linetime+=partstep;
	    var xcrd=Math.round((linetime-starttime)/xfact*10)/10;
		if(xcrd<maxlength){
		    if((nstep==pstep-1) ||(xcrd==0) ){
			line=createline(xcrd,xcrd,10,-220,svg,'black');
			var text=svg.createElementNS("http://www.w3.org/2000/svg",'text');
			var d=new Date(linetime);
			// text.appendChild(svg.createTextNode(''+(d.getYear()+1900)+'/'+(d.getMonth()+1)+'/'+(d.getDate()+1)));
			text.appendChild(svg.createTextNode(''+(d.getMonth()+1)+'/'+
							    (d.getDate())));
			text.setAttribute("x",xcrd-12);
			text.setAttribute("y",25);
			text.setAttribute("font-size",12);
			g.appendChild(text);
			line.setAttribute('stroke-width','1');
		    }else{
			line=createline(xcrd,xcrd,10,-220,svg,'grey');
			line.setAttribute('stroke-width','0.5');
		    }
		g.appendChild(line);		
		}
	}
    }
    // updates the polyline in the svg - thereby forcing a redraw.
     
}

function addpoint(dataset){
    // Just adds data into a temporary array, the plot is redrawn later
    var value=dataset["value"]*1; // picks out the parameter to be plotted here
    var ts=Date.parse(dataset["at"]);
    this.timestamps.push(ts);
    if(value<this.minvalue){
	this.minvalue=value;
	this.mintime=ts;
    }
    if(value>this.maxvalue){
	this.maxvalue=value;
	this.maxtime=ts;}
    if(!(Object.isUndefined(value))){
	this.pnts.push(value);      // puts the new point at the head /use push to add data at the end
//	this.logger.innerHTML=" "+Math.round(value*100)/100+this.unit+" at "+ts.getHour(); // prints the value
    }
}

function resetpnts(){
    this.pnts=new Array();
    this.timestamps=new Array();
    this.maxvalue=-1E9;
    this.minvalue=1E9;
    // Cleans up the horizontal lines:
    var svg=$(this.id).contentDocument;
    var g=svg.getElementById('horizlines');
    while (g.firstChild) {
	g.removeChild(g.firstChild);
    }
    g=svg.getElementById('xlines');
    while (g.firstChild) {
	g.removeChild(g.firstChild);
    }

}
