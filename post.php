<?php
/**
* @package Page
*/
// 必要なファイルを一気に取り込むおまじない。
/**
* XOOPS用ファイルの取り込み
*/
require_once '../../mainfile.php';

//GIJOE さんのワンタイムチケット
include_once "./include/gtickets.php" ;

// echo "checkpoint 1 <br>\n";

//値を受けてみるよ。
/*
echo '<pre>';
var_dump($_REQUEST);
echo '</pre>';
*/
$memo_id = empty($_POST["tagmemo_id"]) ? 0 : $_POST["tagmemo_id"] ;
$content = $_POST["tagmemo_memo"];
$public = isset($_POST["public"]) ? $_POST["public"] : 0;
$public = intval($public);
$tags =  $_POST["tagmemo_tag_hidden"];
if(is_array($tags)){
$tags = implode(' ', $tags);
}
// echo "checkpoint 2 <br>\n";
$title="";
if(preg_match("/^([^\n]{0,120})/i", $content, $matches)){
	$title = $matches[0];
}
$title = (strlen($title) > 0) ? $title : "NO TITLE";
//ハンドラをつくってみるよ。
// echo "checkpoint 3 <br>\n";
$tagmemo_handler =& xoops_getmodulehandler("tagmemo");

$module_id = $xoopsModule->mid();

//ユーザーIDをもらおう
if(is_object($xoopsUser)){
	$uid = $xoopsUser->getVar("uid");
} else {
	$uid = 0;
}

if($memo_id != 0){
	if ( ! $xoopsGTicket->check() ) {
		redirect_header(XOOPS_URL.'/',3,$xoopsGTicket->getErrors());
	}
	$memo_obj =& $tagmemo_handler->getMemoObj($memo_id);
 	if(($memo_obj->getVar('uid') != $uid) and $memo["uid"] != 0){
		redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, _NOPERM);
	}else{
		if($uid == 0){
			redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, _NOPERM);
		}else{
			if(!($xoopsUser->isAdmin($module_id))){
				redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, _NOPERM);
			}
		}	
	
	}
}else{
	$memo_obj =& $tagmemo_handler->createMemo();
}
// echo "checkpoint 4 <br>\n";

// echo "checkpoint 5 <br>\n";

//オブジェクトに値を設定してみるよ。
//$memo_obj->setVar('tagmemo_id', $tagmemo_id);
if($memo_id == 0){
$memo_obj->setVar('uid', $uid);
}
$memo_obj->setVar('title', $title);
$memo_obj->setVar('content', $content);
$memo_obj->setVar('timestamp', time());
$memo_obj->setVar('public', $public);
// echo "checkpoint 6 <br>\n";

//放り込め！
$tagmemo_handler->insert($memo_obj, $tags);
// echo "checkpoint 7 <br>\n";

// echo "OK";

// ヘッダを書くおまじない。
//  include XOOPS_ROOT_PATH.'/header.php';
//  include(XOOPS_ROOT_PATH.'/footer.php');

redirect_header(XOOPS_URL.'/modules/tagmemo/', 1, _MD_TAGMEMO_MESSAGE_SAVE);
?>