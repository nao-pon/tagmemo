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
	if (tagmemo_scr[0])	document.body.removeChild(tagmemo_scr[0]);
	if (tagmemo_scr[1])	document.body.removeChild(tagmemo_scr[1]);
	if (tagmemo_scr[2])	document.body.removeChild(tagmemo_scr[2]);
	if (tagmemo_scr[3])	document.body.removeChild(tagmemo_scr[3]);
	tagmemo_qp_container = null;
}

var tagmemo_scr = new Array();
tagmemo_scr[0] = document.createElement('script');
tagmemo_scr[0].src = tagmemo_baseurl + '/include/javascript/prototype/prototype.js';
tagmemo_scr[0].type = 'text/javascript';
document.body.insertBefore(tagmemo_scr[0],document.body.firstChild);

tagmemo_scr[1] = document.createElement('script');
tagmemo_scr[1].src = tagmemo_baseurl + '/include/javascript/scriptaculous/effects.js';
tagmemo_scr[1].type = 'text/javascript';
document.body.insertBefore(tagmemo_scr[1],document.body.firstChild);

tagmemo_scr[2] = document.createElement('script');
tagmemo_scr[2].src = tagmemo_baseurl + '/include/javascript/windows_js/window.js';
tagmemo_scr[2].type = 'text/javascript';
document.body.insertBefore(tagmemo_scr[2],document.body.firstChild);

tagmemo_scr[3]      = document.createElement('script');
tagmemo_scr[3].src  = tagmemo_baseurl + '/include/javascript/tagmemo_quickedit.js';
tagmemo_scr[3].type = 'text/javascript';
document.body.insertBefore(tagmemo_scr[3],document.body.firstChild);

EOD;
exit();
?>