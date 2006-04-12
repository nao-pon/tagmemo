function createTagmemoQuickForm() {
	try {

		/* Finally create quick form */
		var title = "<a href='" + tagmemo_baseurl + "' target='_blank'>" + tagmemo_sitename + "</a>";
		var win;
		win = new Window('tagmemo_qe_container', {top:100, right:100, width:300, height:250, zIndex:150, opacity:0.95, resizable: true, fixed: true, title: title, footer: tagmemo_version, url: tagmemo_quickurl});
		win.show();
		return true;
		
	} catch(e) {
		return false;
	}
}

if (!document.getElementsByTagName('frameset')[0])
{
	if (!createTagmemoQuickForm())
		alert(tagmemo_msg['retryPlease']);
}
else
{
	alert(tagmemo_msg['notWorkFrame']);
}
