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

tagmemo_baseurl  = '$base';
tagmemo_quickurl = '$url' + encodeURIComponent(document.title + "\\n" + location.href + "\\n\\n");
tagmemo_sitename = '$sitename';
tagmemo_token    = '$token';

var tagmemo_scr;
tagmemo_scr = document.createElement('script');
tagmemo_scr.src = tagmemo_baseurl + '/include/javascript/prototype/prototype.js';
tagmemo_scr.type = 'text/javascript';
document.getElementsByTagName('head')[0].appendChild(tagmemo_scr);

tagmemo_scr = document.createElement('script');
tagmemo_scr.src = tagmemo_baseurl + '/include/javascript/scriptaculous/effects.js';
tagmemo_scr.type = 'text/javascript';
document.getElementsByTagName('head')[0].appendChild(tagmemo_scr);

tagmemo_scr = document.createElement('script');
tagmemo_scr.src = tagmemo_baseurl + '/include/javascript/windows_js/window.js';
tagmemo_scr.type = 'text/javascript';
document.getElementsByTagName('head')[0].appendChild(tagmemo_scr);

tagmemo_scr      = document.createElement('script');
tagmemo_scr.src  = tagmemo_baseurl + '/include/javascript/tagmemo_quickedit.js';
tagmemo_scr.type = 'text/javascript';
document.getElementsByTagName('head')[0].appendChild(tagmemo_scr);

EOD;
exit();
?>