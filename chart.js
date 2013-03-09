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
    this.first=0;
    this.last=0;
    this.createtempline=createtempline;
}

function getsvgid(){
    return(this.id);
}


function setmaxvalue(newmax){
    this.factor=this.defaultmax/newmax;
    this.svgobj.getElementById('xfull').firstChild.nodeValue=newmax;
    this.svgobj.getElementById('xhalf').firstChild.nodeValue=newmax/2;
    
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
    $('maxval').innerHTML=this.maxvalue;
    $('minval').innerHTML=this.minvalue;
    valspan=this.maxvalue-this.minvalue;
    yscale=80/valspan;
    var g=svg.getElementById('transformer');
    g.setAttribute('transform',"scale(1,-"+yscale+")");
    var ymove=10+this.maxvalue*yscale*this.factor;
    $('p_status').innerHTML=':'+this.factor;
    svg.getElementById('translater').setAttribute('transform','translate(0,'+ymove+')');
    if(this.maxvalue>0 && this.minvalue < 0){
	var line=createline(0,420,0,0,svg);
	g.appendChild(line);
    }
//    var temps=[-30.-20,-10,10,20,30];
    for(var i=-3;i<4;i++){
	if(10*i>this.minvalue && 10*i < this.maxvalue){
	    line=this.createtempline(i*10,svg,'grey');
	    g.appendChild(line);
	}
    }
    
    if(this.pnts.length >0){
	var i=0;
	var xfact=timespan/maxlength;
	var starttime=this.timestamps[0];
	for (i=1;i<= this.pnts.length-1; i++){
	    xcrd=Math.round((this.timestamps[i]-starttime)/xfact*10)/10;
	    path=xcrd+","+(this.pnts[i-1])*this.factor+" "+path;
	    // to make a horisontal rather than a vertical point, use
	    // path+=i+","+(this.pnts[i-1])*this.factor+" ";		
	}
    }
    this.svgobj.getElementById('temp1').setAttribute("points", path );
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
	this.logger.innerHTML=Math.round(value*100)/100; // prints the value
    }
}

function resetpnts(){
    this.pnts=new Array();
    this.timestamps=new Array();
    this.maxvalue=-1E9;
    this.minvalue=1E9;
    this.first=0;
    this.last=0;
}
