var DumpWindow = Class.create();
DumpWindow.prototype = {
	objSrc,
	objTarget,
	initialize:function(){
	},
	dump:function(src){
		if(src ! = null){
		objSrc = src
		}
		var ret="";
		var nameNode;
		var valueNode;
		var delimitearNode;
		var lineBreak;
		var beginNode;
		var endNode;
		beginNode = document.createTextNode("----------------begin----------------");
		endNode   = document.createTextNode("-----------------end-----------------");
		delimitearNode = document.createTextNode(" : ");
		lineBreak = document.createElement("br");
	
		objTarget.appendChild(beginNode);
		objTarget.appendChild(lineBreak);
	
		lineBreak = null;
		for( var i in objSrc ){
			nameNode = document.createTextNode(i);
			eval ("valueNode = document.createTextNode(objSrc." + i + ");");
			delimitearNode = document.createTextNode(" : ");
			lineBreak = document.createElement("br");
			objTarget.appendChild(nameNode);
			objTarget.appendChild(delimitearNode);
			objTarget.appendChild(valueNode);
			objTarget.appendChild(lineBreak);
			nameNode = null;
			valueNode = null;
			delimitearNode = null;
			lineBreak = null;
		}
		lineBreak = document.createElement("br");
		objTarget.appendChild(endNode);
		objTarget.appendChild(lineBreak);

		return true;
	},
	clear:function(){
		while((objTarget.lastChild != null)){
			objTarget.removeChild(objTarget.lastChild);
		}
	}
}