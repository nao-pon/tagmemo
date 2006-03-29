<?php
/**
* @package Page
*/

require '../../mainfile.php';
//GIJOE さんのワンタイムチケット
include_once "./include/gtickets.php" ;

$token ='token should be created by php here if needed.';

$uname = (empty($_GET['uname']))? "" : $_GET['uname'];
$url = XOOPS_URL."/modules/tagmemo/quickedit.php?uname=".rawurlencode($uname)."&memo=";
$base = XOOPS_URL.'/modules/tagmemo';
$sitename = rawurlencode(mb_convert_encoding($xoopsConfig['sitename']." :: ".$xoopsModule->name(),"UTF-8",_CHARSET));

// set vars for javascript dinamically;
echo <<<EOD

var tagmemo_baseurl  = '$base';
var tagmemo_quickurl = '$url' + encodeURIComponent(document.title + "\\n" + location.href + "\\n\\n");
var tagmemo_sitename = '$sitename';
var tagmemo_token    = '$token';

var tagmemo_qp_container = document.getElementById('tagmemo_qp_container');
if (tagmemo_qp_container)
{
	document.body.removeChild(tagmemo_qp_container);
}

var tagmemo_scr;
if (!document.getElementById('TagmemoScriptPrototype'))
{
	tagmemo_scr = document.createElement('script');
	tagmemo_scr.id = 'TagmemoScriptPrototype';
	tagmemo_scr.src = tagmemo_baseurl + '/include/javascript/prototype/prototype.js';
	tagmemo_scr.type = 'text/javascript';
	document.getElementsByTagName('head')[0].appendChild(tagmemo_scr);
}
if (!document.getElementById('TagmemoScriptEffects'))
{
	tagmemo_scr = document.createElement('script');
	tagmemo_scr.id = 'TagmemoScriptEffects';
	tagmemo_scr.src = tagmemo_baseurl + '/include/javascript/scriptaculous/effects.js';
	tagmemo_scr.type = 'text/javascript';
	document.getElementsByTagName('head')[0].appendChild(tagmemo_scr);
}

if (tagmemo_scr = document.getElementById('TagmemoStyleQuickedit'))
		document.getElementsByTagName('head')[0].removeChild(tagmemo_scr);
tagmemo_scr = document.createElement('link');
tagmemo_scr.id = 'TagmemoStyleQuickedit';
tagmemo_scr.href = tagmemo_baseurl + '/include/css/tagmemo_quickedit.css';
tagmemo_scr.rel  = 'stylesheet';
tagmemo_scr.type = 'text/css';
document.getElementsByTagName('head')[0].appendChild(tagmemo_scr);

if (tagmemo_scr = document.getElementById('TagmemoStyleWindow'))
		document.getElementsByTagName('head')[0].removeChild(tagmemo_scr);
var style;
tagmemo_scr = document.createElement('link');
tagmemo_scr.id = 'TagmemoStyleWindow';
tagmemo_scr.href = tagmemo_baseurl + '/include/css/windows_js/default.css';
tagmemo_scr.rel  = 'stylesheet';
tagmemo_scr.type = 'text/css';
document.getElementsByTagName('head')[0].appendChild(tagmemo_scr);

if (tagmemo_scr = document.getElementById('TagmemoScriptWindow'))
		document.body.removeChild(tagmemo_scr);
tagmemo_scr = document.createElement('script');
tagmemo_scr.id = 'TagmemoScriptWindow';
tagmemo_scr.src = tagmemo_baseurl + '/include/javascript/windows_js/window.js';
tagmemo_scr.type = 'text/javascript';
document.body.insertBefore(tagmemo_scr,document.body.firstChild);

if (tagmemo_scr = document.getElementById('TagmemoScriptQuickedit'))
		document.body.removeChild(tagmemo_scr);
tagmemo_scr      = document.createElement('script');
tagmemo_scr.id = 'TagmemoScriptQuickedit';
tagmemo_scr.src  = tagmemo_baseurl + '/include/javascript/tagmemo_quickedit.js';
tagmemo_scr.type = 'text/javascript';
document.body.insertBefore(tagmemo_scr,document.body.firstChild);

EOD;
exit();
?>