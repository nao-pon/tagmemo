<?php
/**
* @package Page
*/

require '../../mainfile.php';
//GIJOE さんのワンタイムチケット
include_once "./include/gtickets.php" ;

$token ='token should be created by php here if needed.';

$uid = (empty($_GET['uid']))? 0 : (int)$_GET['uid'];
$url = XOOPS_URL."/modules/tagmemo/quickedit.php?uid=".$uid."&amp;";
$taggeturl = XOOPS_URL."/modules/tagmemo/get_keyword.php?q=";
$base = XOOPS_URL.'/modules/tagmemo';
$sitename = str_replace("'","\'",$xoopsConfig['sitename']." :: ".$xoopsModule->name());

// next version is not a copyright notice. if you'd like to rewrite. It's up to you. 
$version = ":: tagmemo ".$xoopsModule->getInfo('version')." ::";

$msg_close2open = _MD_TAGMEMO_CLOSE2OPEN;
$msg_notWorkFrame = _MD_TAGMEMO_NOTWORKFRAME;
$msg_retryPlease = _MD_TAGMEMO_RETRYPLEASE;

header ("Content-Type: application/x-javascript; charset="._CHARSET);
// set vars for javascript dinamically;
echo <<<EOD

(function(){
var createTagmemoQuickForm = function (cnt) {
	if (document.getElementsByTagName('frameset')[0]) {
		alert(tagmemo_msg['notWorkFrame']);
		return true;
	}
	try {
		if (! tagmemo_scr || tagmemo_scr.loaded < 4 ) {
			if (100 < cnt) return false;
			setTimeout(function(){createTagmemoQuickForm(cnt++);}, 50);
			return true;
		}
		
		var is_ie = false;
		/*@cc_on
		@if (@_jscript_version >= 10)
			is_ie = false;
		@else
			is_ie = true;
		@end
		@*/
		
		/* Finally create quick form */
		var title = "<a href='" + tagmemo_baseurl + "' target='_blank'>" + tagmemo_sitename + "</a>";
		var win;
		win = new Window('tagmemo_qe_container', {top:100, right:100, width:300, height:280, zIndex:150, opacity:0.95, resizable: true, fixed: true, title: title, footer: tagmemo_version, url: tagmemo_quickurl});
		win.show();
		
		$('tagmemo_qe_container').close = function(){ win.destroy(); };
		
		var ifm = $('tagmemo_qe_container_content');
		var i = 0;
		var onload = function(isOnload){
			if (isOnload) {
				onload.support = true;
			}
			if (i++ > 0) {
				win.hide();
			}
		};
		onload.support = false;
		ifm.onload = function(){ onload(true); };
		if (is_ie) {
			ifm.onreadystatechange = function(){ /* for IE */
				if (! onload.support && this.readyState == 'complete') {
					onload(false);
				}
			};
		}

		return true;

	} catch(e) {
		alert(e);
		return false;
	}
};

var tagmemo_qe_container = document.getElementById('tagmemo_qe_container');
if (tagmemo_qe_container) {
	if (confirm('{$msg_close2open}')) {
		tagmemo_qe_container.close();
		tagmemo_qe_container = null;
	}
}

var tagmemo_scr;
if (!tagmemo_qe_container) {
	var tagmemo_baseurl  = '$base';
	var tagmemo_quickurl = '$url'+ 't=' + encodeURIComponent(document.title) + "&amp;u=" + encodeURIComponent(location.href);
	var tagmemo_sitename = '$sitename';
	var tagmemo_token    = '$token';
	var tagmemo_version  = '$version';
	tagmemo_msg = new Array();
	tagmemo_msg['notWorkFrame'] = '{$msg_notWorkFrame}';
	tagmemo_msg['retryPlease'] = '{$msg_retryPlease}';
	
	var head = document.getElementsByTagName('head')[0] || document.documentElement;
	
	if (!document.getElementById('TagmemoScriptPrototype')) {
		tagmemo_scr = document.createElement('script');
		tagmemo_scr.id = 'TagmemoScriptPrototype';
		tagmemo_scr.src = tagmemo_baseurl + '/include/javascript/prototype/prototype.js';
		tagmemo_scr.type = 'text/javascript';
		tagmemo_scr.done = false;
		tagmemo_scr.onload = tagmemo_scr.onreadystatechange = function(){ 
			if ( !tagmemo_scr.done && (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete') ) {
				var tagmemo_scr1, tagmemo_scr2, tagmemo_scr3;
				tagmemo_scr.done = true;
				tagmemo_scr.loaded = 1;

				if (tagmemo_scr1 = document.getElementById('TagmemoScriptEffects'))
					document.body.removeChild(tagmemo_scr1);
				tagmemo_scr1 = document.createElement('script');
				tagmemo_scr1.id = 'TagmemoScriptEffects';
				tagmemo_scr1.src = tagmemo_baseurl + '/include/javascript/scriptaculous/effects.js';
				tagmemo_scr1.type = 'text/javascript';
				tagmemo_scr1.done = false;
				tagmemo_scr1.onload = tagmemo_scr1.onreadystatechange = function(){ 
					if ( !tagmemo_scr1.done && (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete') ) {
						tagmemo_scr1.done = true;
						tagmemo_scr.loaded++;
					}
				};
				head.appendChild(tagmemo_scr1);

				if (tagmemo_scr2 = document.getElementById('TagmemoScriptWindow'))
					document.body.removeChild(tagmemo_scr2);
				tagmemo_scr2 = document.createElement('script');
				tagmemo_scr2.id = 'TagmemoScriptWindow';
				tagmemo_scr2.src = tagmemo_baseurl + '/include/javascript/windows_js/window.js';
				tagmemo_scr2.type = 'text/javascript';
				tagmemo_scr2.done = false;
				tagmemo_scr2.onload = tagmemo_scr2.onreadystatechange = function(){ 
					if ( !tagmemo_scr2.done && (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete') ) {
						tagmemo_scr2.done = true;
						tagmemo_scr.loaded++;
					}
				};
				head.appendChild(tagmemo_scr2);
				
				var tagmemo_stl;
				if (tagmemo_stl = document.getElementById('TagmemoStyleWindow'))
					document.getElementsByTagName('head')[0].removeChild(tagmemo_stl);
				tagmemo_stl = document.createElement('link');
				tagmemo_stl.id = 'TagmemoStyleWindow';
				tagmemo_stl.href = tagmemo_baseurl + '/include/css/windows_js/default.css';
				tagmemo_stl.rel  = 'stylesheet';
				tagmemo_stl.type = 'text/css';
				tagmemo_stl.done = false;
				tagmemo_stl.onload = tagmemo_stl.onreadystatechange = function(){ 
					if ( !tagmemo_stl.done && (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete') ) {
						tagmemo_stl.done = true;
						tagmemo_scr.loaded++;
					}
				};
				head.appendChild(tagmemo_stl);
				
				if (!createTagmemoQuickForm(0)) {
					alert(tagmemo_msg['retryPlease']);
				}
			}
		};
		head.appendChild(tagmemo_scr);
	} else {
		tagmemo_scr = {};
		tagmemo_scr.loaded = 4;
		if (!createTagmemoQuickForm(-1)) {
			alert(tagmemo_msg['retryPlease']);
		}
	}
}
})();
EOD;
exit();
?>