function createTagmemoQuickForm() {
	try {
		/* Finally create quick form */
		var title = "<a href='" + tagmemo_baseurl + "' target='_blank'>" + tagmemo_sitename + "</a>";
		var win;
		win = new Window('tagmemo_qe_container', {top:100, right:100, width:300, height:280, zIndex:150, opacity:0.95, resizable: true, fixed: true, title: title, footer: tagmemo_version, url: tagmemo_quickurl});
		win.show();

		var ifm = $('tagmemo_qe_container_content');
		var i = 0;
		var onload = function(){
			if (i++ > 0) {
				win.hide();
			}
		};
		ifm.onload = onload;
		if (document.all) ifm.onreadystatechange = function(){ /* for IE */
			if (this.readyState == 'complete') { // onloadが動作しないので代用
				onload();
			}
		};

		return true;

	} catch(e) {
		return false;
	}
}

if (!document.getElementsByTagName('frameset')[0])
{
	if (!createTagmemoQuickForm()) {
		// retry once
		if (!createTagmemoQuickForm()) {
			alert(tagmemo_msg['retryPlease']);
		}
	}
}
else
{
	alert(tagmemo_msg['notWorkFrame']);
}
