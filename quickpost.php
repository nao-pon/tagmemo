<?php

/**
* @package Page
*/

/**
* XOOPS用ファイルの取り込み
*/

require_once '../../mainfile.php';
//GIJOE さんのワンタイムチケット
//include_once "./include/gtickets.php" ;

//@todo check for CSRF;
$token = $_POST['tagmemo_quickform_token'];
$content = $_POST["tagmemo_quickform_memo"];
$public = isset($_POST["public"]) ? intval($_POST["public"]) : 0;
$tags =  $_POST["tagmemo_quickform_tags"];

$title="";
if(preg_match("/^([^\n]{0,120})/i", $content, $matches)){
	$title = $matches[0];
}
$title = (strlen($title) > 0) ? $title : "NO TITLE";
$tagmemo_handler =& xoops_getmodulehandler("tagmemo");
if(is_object($xoopsUser)){
	$uid = $xoopsUser->getVar("uid");
} else {
	$uid = 0;
}

$memo_obj =& $tagmemo_handler->createMemo();

$memo_obj->setVar('uid', $uid);
$memo_obj->setVar('title', $title);
$memo_obj->setVar('content', $content);
$memo_obj->setVar('timestamp', time());
$memo_obj->setVar('public', $public);

$tagmemo_handler->insert($memo_obj, $tags, true);

// redirect (post request)
// @todo check if valid url.
$gobackurl = $_POST['tagmemo_quickform_gobackurl'];
header("Location: ".strtr($gobackurl, array("\r"=>'',"\n"=>'')));
exit();

?>