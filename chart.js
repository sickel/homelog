/*

$Id: chart.js 707 2011-02-16 14:08:23Z radioecology $
(c) Morten Sickel February 2011 licenced under the GNU GPL 2 or later
*/

function chart(svgobjid,loggerid){
    this.id=svgobjid;
    this.svgobj=document.getElementById(svgobjid).contentDocument; // The svg document
    this.logger=$(loggerid); // writing log messages here
    this.pnts=new Array();   // the measurement points
    this.parameter='';       // to set the parameter to be logged
    this.factor=1;			 // scaling factor
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

function drawstrip(){
    var path=''; // a text string in which the path is constructed
    // resets the over/under indicators:
    //	this.svgobj.getElementById('underload').setAttribute("fill", "none" );
    //	this.svgobj.getElementById('overload').setAttribute("fill", "none" );
    $('maxval').innerHTML=this.maxvalue;
    $('minval').innerHTML=this.minvalue;
    if(this.pnts.length >0){
	var i=0;
	for (i=1;i<= this.pnts.length; i++){
	    yfact=datasetsize/maxlength;
	    path=i/yfact+","+(this.pnts[i-1])*this.factor+" "+path;
	    // to make a horisontal rather than a vertical point, use
	    // path+=i+","+(this.pnts[i-1])*this.factor+" ";
	    if(this.pnts[i-1]<0){
		//				this.svgobj.getElementById('underload').setAttribute("fill", "yellow" );
		    }
	    if(this.pnts[i-1]*this.factor>200){
		//				this.svgobj.getElementById('overload').setAttribute("fill", "red" );
			}
			
	}
    }
    this.svgobj.getElementById('temp1').setAttribute("points", path );
    // updates the polyline in the svg - thereby forcing a redraw.
}

function addpoint(dataset){
    // Just adds data into a temporary array, the plot is redrawn later
    var value=dataset["value"]; // picks out the parameter to be plotted here
    if(value<this.minvalue){this.minvalue=value;}
    if(value>this.maxvalue){this.maxvalue=value;}
    if(!(Object.isUndefined(value))){
	this.pnts.push(value);      // puts the new point at the head /use push to add data at the end
	this.logger.innerHTML=Math.round(value*100)/100; // prints the value
	//if(this.pnts.length>maxlength){
	//    this.pnts.shift(); // pops off at the end if the array is too long. Use shift to cut at the head
	//}
    }
}

function resetpnts(){
	this.pnts=new Array();
}
