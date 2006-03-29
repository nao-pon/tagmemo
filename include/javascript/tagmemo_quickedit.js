function createTagmemoQuickForm() {
	try {

		/* Finally create quick form */
		var title = "<a href='" + tagmemo_baseurl + "' target='_blank'>" + decodeURIComponent(tagmemo_sitename) + "</a>";
		var win;
		win = new Window('tagmemo_qp_container', {top:100, right:100, width:300, height:250, zIndex:150, opacity:0.95, resizable: true, title: title, url: tagmemo_quickurl});
		win.show();
		return true;
		
	} catch(e) {
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
