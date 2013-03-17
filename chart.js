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
    this.createtempline=createtempline;
    this.unit="&deg;C";
    this.setvlinespacing=setvlinespacing; // Sets the spacing between vertical lines
    // i.e time markers
    // max time (in days), days between main markers, 
    // submarkers from one main maker to next (one included) 
    this.vlinespc=$A([[0,24,12],[3,24,6],[7,24,4],[10,24,2],[15,24*7,7]]);
   
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
    line=createline(0,420,x,x,svg,color);
    return line;
}

function drawstrip(){
    var timespan=this.timestamps.slice(-1)[0]-this.timestamps[0];
    var svg=$(this.id).contentDocument;
    var path=''; // a text string in which the path is constructed
    // resets the over/under indicators:
    //	this.svgobj.getElementById('underload').setAttribute("fill", "none" );
    //	this.svgobj.getElementById('overload').setAttribute("fill", "none" );
    $('maxval').innerHTML=this.maxvalue+this.unit;
    $('minval').innerHTML=this.minvalue+this.unit;
    valspan=this.maxvalue-this.minvalue;
    yscale=80/valspan;
    var g=svg.getElementById('transformer');
    g.setAttribute('transform',"scale(1,-"+yscale+")");
    var ymove=10+this.maxvalue*yscale*this.factor;
//    $('p_status').innerHTML=':'+yscale;
    svg.getElementById('translater').setAttribute('transform','translate(0,'+ymove+')');
    if(this.maxvalue>0 && this.minvalue < 0){
 	var line=createline(0,420,0,0,svg);
	g.appendChild(line);
    }
    var hl=svg.getElementById('horizlines');
    var lf;
    if (valspan > 30){lf = 10;}
    else if(valspan > 10){lf = 5;}
    else if(valspan > 5){lf = 2;}
    else {lf=1;}
    for(var i=Math.ceil(this.minvalue/lf);i<Math.ceil(this.maxvalue/lf);i++){
	var text=svg.createElementNS("http://www.w3.org/2000/svg",'text');
	text.appendChild(svg.createTextNode(lf*i));
	text.setAttribute("x",-20/yscale);
	text.setAttribute("y",-1*lf*i*this.factor+this.factor*2);
	text.setAttribute("font-size",50/(this.factor*yscale));
	text.setAttribute("transform","scale(1,-1)");
	hl.appendChild(text);
	line=this.createtempline(i*lf,svg,'grey');
	hl.appendChild(line);
    }
    var starttime=this.timestamps[0];
    if(this.pnts.length >0){
	var i=0;
	var xfact=timespan/maxlength;
	for (i=1;i<= this.pnts.length-1; i++){
	    xcrd=Math.round((this.timestamps[i]-starttime)/xfact*10)/10;
	    path=xcrd+","+(this.pnts[i-1])*this.factor+" "+path;
	    // to make a horisontal rather than a vertical point, use
	    // path+=i+","+(this.pnts[i-1])*this.factor+" ";		
	}
    }
    var chartline=this.svgobj.getElementById('temp1');
    chartline.setAttribute("points", path );
    var wfact=yscale<10?20:50;
    var swidth=Math.round(wfact/yscale)/10;
    chartline.setAttribute('stroke-width',swidth);
    var linetime=new Date(this.timestamps[0]);
    linetime.setHours(0,0,0,0);
    var g=svg.getElementById('xlines');
    linetime=Date.parse(linetime);
// 	<line x1="100" y1="10" x2="100" y2="-220" />	
    var stoptime=this.timestamps[this.timestamps.length-1];
    var stps=this.setvlinespacing(timespan)
    var hstep=stps.hstep;
    var pstep=stps.pstep;
   // var pstep=12;  // number of partial steps in timestep incl. htstep 
    var sstep=hstep*3600*1000;
    var partstep=sstep/pstep;
    while(linetime<stoptime){
	for(nstep=0;nstep<pstep;nstep++){
	    linetime+=partstep;
	    if(xfact>0){
		var xcrd=Math.round((linetime-starttime)/xfact*10)/10;
		if(xcrd >0){
		    if(nstep==pstep-1){
			line=createline(xcrd,xcrd,10,-220,svg,'black');
			xcrd=Math.round((linetime-starttime)/xfact*10)/10;
			var text=svg.createElementNS("http://www.w3.org/2000/svg",'text');
		    var d=new Date(linetime);
		    // text.appendChild(svg.createTextNode(''+(d.getYear()+1900)+'/'+(d.getMonth()+1)+'/'+(d.getDate()+1)));
		    text.appendChild(svg.createTextNode(''+(d.getMonth()+1)+'/'+
							(d.getDate()+1)));
		    text.setAttribute("x",xcrd-20);
		    text.setAttribute("y",20);
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
    }
    // updates the polyline in the svg - thereby forcing a redraw.
     
}

function addpoint(dataset){
    // Just adds data into a temporary array, the plot is redrawn later
    var value=dataset["value"]*1; // picks out the parameter to be plotted here
    this.timestamps.push(Date.parse(dataset["at"]));
    if(value<this.minvalue){this.minvalue=value;}
    if(value>this.maxvalue){this.maxvalue=value;}
    if(!(Object.isUndefined(value))){
	this.pnts.push(value);      // puts the new point at the head /use push to add data at the end
	this.logger.innerHTML=" "+Math.round(value*100)/100+this.unit+" at "+dataset["at"]; // prints the value
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
