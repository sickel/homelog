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
    this.setunit=setunit;
    this.setvlinespacing=setvlinespacing; // Sets the spacing between vertical lines
    // i.e time markers
    // max time (in days), days between main markers, 
    // submarkers from one main maker to next (one included) 
    this.vlinespc=$A([[0,12,12],[2,24,12],[3,24,6],[5,24,4],[8,24,2],[10,24*2,4],[14,24*7,7],[60,24*7,7]]);
    this.yscale=0;
    this.stepline=false;
    this.svgobj.transpy=transpy;
    this.svgobj.transpx=transpx;
    this.svgobj.onclick=clickhandler;
    this.svgobj.unit=this.unit;
	this.reportvals=$('reportval');   
    this.setstepline=setstepline;
}

function setstepline(type){
    this.stepline=type;
}


function getsvgid(){
    return(this.id);
}

function mousemove(){
}

function setunit(unit){
    this.unit=unit;
    this.svgobj.unit=unit;
}

function transpy(y){ // calculates the real value from y coordinate
//  ycrd=this.ymove-this.pnts[i-1]*this.yscale;
    y=y-23;
    y=y*233/250;
    y=(y-this.ymove)/this.yscale*-1;
    return(Math.round(y*10)/10);
}

function transpx(x){ 
    x=x-45;
    x=x/1.071;
// xcrd=Math.round((this.timestamps[i-1]-this.starttime)/this.xfact*10)/10;
    x=x*this.xfact+this.starttime;
    return(x);
}

function clickhandler(event){
//    event=winevent(event);
    var x = event.clientX;
    var y = event.clientY;   
    if (false) { // grab the x-y pos.s if browser is old IE         
        x = event.offsetX;
        y = event.offsetY;
    }
//    $('p_status').innerHTML=''+x+','+y;
    if(x>40 && x < 496 && y>25 && y<270){	 // Do not report if clicked outside the chart
	var crossbox=this.getElementById('xbox');    
        crossbox.setAttribute('class','outline');
        crossbox.setAttribute('transform','translate('+((x-45)/1.071-5)+','+((y-23)*233/250-5)+')') 
        y=this.transpy(y);
        x=this.transpx(x);
        x=new Date(x);
        $('reportvals').innerHTML=''+y+this.unit+" "+x;
    }
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
    var last=this.vlinespc[this.vlinespc.length-1];
    if(last[1]==hstep){ // We have used the last element
	var fact=span/(last[0]*24*3600*1000);
	hstep=Math.round(fact*last[1]);
	// pstep should be unchanged
    }
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
    this.yscale=220/valspan;
    this.svgobj.yscale=this.yscale;
    this.ymove=5+this.maxvalue*this.yscale;
    this.svgobj.ymove=this.ymove;
    var hl=svg.getElementById('horizlines');
    var lf; // number of units between horizontal lines.
    var spanoom=Math.ceil(Math.log(valspan)/Math.log(10))-2;
    var spanfact=Math.pow(10,spanoom);
    valspan=valspan/spanfact;
    if (valspan > 40){lf = 10;}
    else if(valspan > 20){lf = 5;}
    else if(valspan > 10){lf = 2;}
    else {lf=1;}
    lf=lf*spanfact;
    for(var i=Math.ceil(this.minvalue/lf);i<Math.ceil(this.maxvalue/lf);i++){
	var text=svg.createElementNS("http://www.w3.org/2000/svg",'text');
	var ycrd=this.ymove-i*lf*this.yscale;
	var label=lf*i;
	if(spanoom < 0){
	    label=label.toFixed(-1*spanoom);
	}
	text.appendChild(svg.createTextNode(label));
	text.setAttribute("x",-5);
	text.setAttribute("text-anchor","end");
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
    this.starttime=this.timestamps[0];
    this.svgobj.starttime=this.starttime;
    this.xfact=timespan/maxlength;
    this.svgobj.xfact=this.xfact;
    if(this.pnts.length >0){
	var lastx;
	var i=0;
	for (i=1;i<= this.pnts.length; i++){
	    xcrd=Math.round((this.timestamps[i-1]-this.starttime)/this.xfact*10)/10;
	    ycrd=this.ymove-this.pnts[i-1]*this.yscale;
	    if(this.stepline){
		if(i>1){
		    path=''+lastx+","+ycrd+" "+path;
		}
		lastx=xcrd;
	    }
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
	    var xcrd=Math.round((linetime-this.starttime)/this.xfact*10)/10;
		if(xcrd > 0 && xcrd<maxlength){ // Only want lines within the plotting area...
		    if((nstep==pstep-1) ||(xcrd==0) ){
			line=createline(xcrd,xcrd,10,-220,svg,'black');
			var text=svg.createElementNS("http://www.w3.org/2000/svg",'text');
			var d=new Date(linetime);
		//	   text.appendChild(svg.createTextNode(''+(d.getYear()+1900)+'/'+(d.getMonth()+1)+'/'+(d.getDate()+1)));
			text.appendChild(svg.createTextNode(''+d.getDate()+'/'+(d.getMonth()+1)));
			text.setAttribute("text-anchor","middle");
			text.setAttribute("x",xcrd);
			text.setAttribute("y",25);
			text.setAttribute("font-size",12);
			g.appendChild(text);
			line.setAttribute('stroke-width','1');
			if(timespan > 365*24*3600*1000){ // one year
			    var text=svg.createElementNS("http://www.w3.org/2000/svg",'text');
			    
			    text.appendChild(svg.createTextNode(''+(d.getYear()+1900)));
			    text.setAttribute("text-anchor","middle");
			    text.setAttribute("x",xcrd);
			    text.setAttribute("y",25+15);
			    text.setAttribute("font-size",12);
			    g.appendChild(text); 
			}
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
    g=svg.getElementById('xbox');
    g.setAttribute('class','invisible');
    $('reportvals').innerHTML='';
}
