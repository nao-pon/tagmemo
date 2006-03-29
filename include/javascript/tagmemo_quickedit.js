function createTagmemoQuickForm() {
	try {
		/* CSS */
		var style;
		style = document.createElement('link');
		style.href = tagmemo_baseurl + '/include/css/tagmemo_quickedit.css';
		style.rel  = 'stylesheet';
		style.type = 'text/css';
		document.getElementsByTagName('head')[0].appendChild(style);
		
		/* windows.js CSS */
		var style;
		style = document.createElement('link');
		style.href = tagmemo_baseurl + '/include/css/windows_js/default.css';
		style.rel  = 'stylesheet';
		style.type = 'text/css';
		document.getElementsByTagName('head')[0].appendChild(style);
			
		/* Finally create quick form */
		var title = "<a href='" + tagmemo_baseurl + "' target='_blank'>" + decodeURIComponent(tagmemo_sitename) + "</a>";
		var win;
		win = new Window('tagmemo_qp_container', {top:100, right:100, width:300, height:300, zIndex:150, opacity:0.95, resizable: true, title: title, url: tagmemo_quickurl});
		win.show();
		return true;
		
	} catch(e) {
		//alert(e);
		return false;
	}
}

if (!document.getElementsByTagName('frameset')[0])
{
	if (!createTagmemoQuickForm())
		alert("Couldn't show a bookmarklet, please retry.");
}
else
{
	alert("Bookmarklet can't work in a frame page.");
}

