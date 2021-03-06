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
(method_exists('MyTextSanitizer', 'sGetInstance') and $myts =& MyTextSanitizer::sGetInstance()) || $myts =& MyTextSanitizer::getInstance();

include_once ("./include/wiki_helper.php");

$memo_id = isset($_GET["tagmemo_id"]) ? intval($_GET["tagmemo_id"]) : "";
//$memo_id = isset($_POST["tagmemo_id"]) ? intval($_POST["tagmemo_id"]) : $memo_id;

//ユーザーIDをもらおう
if(is_object($xoopsUser)){
	$uid = $xoopsUser->getVar("uid");
	$isAdmin = $xoopsUser->isAdmin($xoopsModule->getVar('mid'));
} else {
	$uid = 0;
	$isAdmin = false;
}

$memo = array();
$tagmemo_handler =& xoops_getmodulehandler('tagmemo');
$tagmemo_handler->setUid($uid);
if ($tagmemo_handler->isReadonly()) {
	redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, _NOPERM);
}

//echo $tagmemo_handler->_condition_uid ;
if($memo_id != ""){
	$memo_id =intval($memo_id );
	$memo =& $tagmemo_handler->getMemo4Edit($memo_id);
	if (!$memo) {
		redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, 'Such memo does not exist');
	}
	
	if ($memo['public'] == 0 && !$isAdmin) {
		if ($memo['uid'] == 0) {
			//@future password check
			//$password = isset($_POST["password"]) ? $_POST["password"] : "";
			//(method_exists('MyTextSanitizer', 'sGetInstance') and $ts =& MyTextSanitizer::sGetInstance()) || $ts =& MyTextSanitizer::getInstance();
			//if ($ts->stripSlashesGPC($password) != $memo['password'])) {
			//	$memo['check_pw'] = true;
			//}
			redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, _NOPERM);
		} elseif ($memo['owner'] == 0) {
			redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, _NOPERM);
		}
	}
}

// ヘッダを書くおまじない。
/**
* XOOPSのテンプレートのヘッダー
*/
//set template file after including header because not use cache 
include (XOOPS_ROOT_PATH.'/header.php');
$xoopsOption['template_main'] = 'tagmemo_edit.html';

// Wikiヘルパー
$he = & WikiHelper::getInstance();
$xoopsTpl->assign("helper", $he->get());

$xoopsTpl->assign("memo", $memo);
if($memo_id != ""){
	$xoopsTpl->assign("xoopsGTicket_html", $xoopsGTicket->getTicketHtml( __LINE__ ));
}
//　画面の下の方のフッタを書くおまじない。
/**
* XOOPSのテンプレートのフッター
*/
include(XOOPS_ROOT_PATH.'/footer.php');
?>