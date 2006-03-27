function createTagmemoQuickForm() {
	try {
		/* HTML Check */
		/*
		if (!document.getElementsByTagName('head')[0]) 
		{
			var elem;
			elem = document.createElement('head');
			document.body.appendChild(elem);
		}
		if (!document.getElementsByTagName('body')[0]) 
		{
			var elem;
			elem = document.createElement('body');
			document.body.appendChild(elem);
		}
		*/
		
		/* CSS */
		var style;
		style = document.createElement('link');
		style.href = tagmemo_baseurl + '/include/css/tagmemo_quickedit.css';
		style.rel  = 'stylesheet';
		style.type = 'text/css';
		document.getElementsByTagName('head')[0].appendChild(style);
		
		/* CSS */
		var style;
		style = document.createElement('link');
		style.href = tagmemo_baseurl + '/include/css/windows_js/default.css';
		style.rel  = 'stylesheet';
		style.type = 'text/css';
		document.getElementsByTagName('head')[0].appendChild(style);
			
		/* BaseContainer */
		var container;
		container	   = document.createElement('div');
		
			/* Iframe */
			var elem;
			elem		  = document.createElement('iframe');
			elem.id	      = 'tagmemo_quickform_frame';
			elem.src	  = tagmemo_quickurl;
			elem.setAttribute('frameborder','0');
			elem.setAttribute('border','0');
			elem.setAttribute('allowtransparency','true');
			elem.setAttribute('scrolling','auto');
			
		container.appendChild(elem);
			
		/* Finally create quick form */
		if ($('tagmemo_container'))
		{
			document.getElementsByTagName('body')[0].removeChild($('tagmemo_container'));
		}
		var title = "<a href='" + tagmemo_baseurl + "' target='_blank'>" + decodeURIComponent(tagmemo_sitename) + "</a>";
		var win;
		win = new Window('tagmemo_container', {top:100, left:450, width:380, height:300, zIndex:150, opacity:0.9, resizable: true, title: title});
		win.getContent().innerHTML = container.innerHTML;
		win.show();
		return true;
		
	} catch(e) {
		//alert(e);
		return false;
	}
}
createTagmemoQuickForm();
/*
if (!createTagmemoQuickForm())
{
	setTimeout(createTagmemoQuickForm, 500); 
}
*/