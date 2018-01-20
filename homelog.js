window.onload=pageonload;

var url="ajaxserver.php";  // The url of the program that serves the data to be presented

function pageonload(event){
    $$('.usefield').each(function(chooser){
	Event.observe(chooser,'click',usefield);
    });
    
    $$('.sensorid').each(function(chooser){
	Event.observe(chooser,'click',editid);
    
    });
    }

    
    
function usefield(event){ // This may be called by a periodical executer
    var elementid=this.parentNode.firstChild.firstChild.innerHTML;
    var presentValue=this.innerHTML;
    param=$H({ // All these values are dependent on the backend server...
        a: 'switchuse'
        ,elementid: elementid
        ,present: presentValue
	    ,fool_ie: Math.random()
    });
    // simplest way to stop internet explorer from caching
    ajax=new Ajax.Request(url,
			  {method:'get',
			   parameters: param.toQueryString()});
		//	   onComplete: hHR_receiveddata}
		//	 );
			 
}


function editid(event){
    var elementid=this.innerHTML;
    window.open("maintenance.php?editid="+elementid,"_self")
}
    
