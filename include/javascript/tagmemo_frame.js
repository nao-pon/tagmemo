var TagmemoWindow = Class.create();
TagmemoWindow.prototype = {
	initialize: function(resize, fixs)
	{
		this.resizeElement = resize; //string
		this.fixedElemets  = fixs;   //array
		this.setWindow();
		Event.observe(window, "resize", this.setWindow.bindAsEventListener(this));
	},
	
	setWindow: function()
	{
		if(document.all)
			var baseH = parseInt(document.body.offsetHeight);
		else
			var baseH = parseInt(window.innerHeight);
		
		var fixsH = 0;
		this.fixedElemets.each (
			function(elem)
			{
				fixsH = fixsH + Element.getHeight(elem);
			}
		);
		var resizeH = baseH - fixsH - 5;
		$(this.resizeElement).style.height = (resizeH < 1)? "0px" : resizeH+"px";
	}
}
